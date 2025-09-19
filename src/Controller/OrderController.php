<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\ELiquid;
use App\Form\GuestCheckoutType;
use App\Repository\CartRepository;
use App\Repository\CartItemRepository;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class OrderController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(
        Request $request,
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        OrderItemRepository $orderItemRepository,
        SessionInterface $session
    ): Response {
        $cartId = $session->get('cart_id') ?? $request->cookies->get('cart_id');

        if (!$cartId) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart');
        }

        $cart = $cartRepository->find($cartId);
        if (!$cart || $cartItemRepository->count(['cart' => $cart]) === 0) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart');
        }

        $guestData = $session->get('guest_data');
        if (!$guestData) {
            $this->addFlash('error', 'Missing guest information. Please complete checkout again.');
            return $this->redirectToRoute('guest_checkout');
        }

        // Create a new order
        $order = new Order();
        $order->setCustomerName($guestData['customerName']);
        $order->setPhoneNumber($guestData['phoneNumber']);
        $order->setAddress($guestData['address']);
        $order->setDate(new \DateTime());
        $order->setTotal($cart->getTotal());

        $entityManager->persist($order);

        // Convert CartItems to OrderItems
        $cartItems = $cartItemRepository->findBy(['cart' => $cart]);
        foreach ($cartItems as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);
            $orderItem->setProductName($cartItem->getELiquid()->getName());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setPrice($cartItem->getELiquid()->getPrice());

            $entityManager->persist($orderItem);
        }

        $entityManager->flush();

        $orderItems = $orderItemRepository->findBy(['order' => $order]);

        $session->set('order_id', $order->getId());

        return $this->render('order/index.html.twig', [
            'order' => $order,
            'orderItems' => $orderItems,
        ]);
    }

    #[Route('/order/guest_checkout', name: 'guest_checkout')]
    public function guestCheckout(Request $request, SessionInterface $session): Response
    {
        $form = $this->createForm(GuestCheckoutType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $guestData = [
                'customerName' => $data['customerName'],
                'phoneNumber' => $data['phoneNumber'],
                'address' => $data['address'],
            ];

            $session->set('guest_data', $guestData);

            return $this->redirectToRoute('app_checkout');
        }

        return $this->render('order/guest_checkout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/order/confirm', name: 'order_confirm')]
    public function confirmOrder(
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        Request $request,
        CartRepository $cartRepository,
        OrderItemRepository $orderItemRepository
    ): Response {
        $orderId = $session->get('order_id');
        if (!$orderId) {
            $this->addFlash('error', 'No order found to confirm.');
            return $this->redirectToRoute('app_cart');
        }

        $order = $entityManager->getRepository(Order::class)->find($orderId);
        if (!$order) {
            $this->addFlash('error', 'No order found to confirm.');
            return $this->redirectToRoute('app_cart');
        }

        // Change order state to "pending"
        $order->setState('pending');

        // Update product stock
        $orderItems = $orderItemRepository->findBy(['order' => $order]);
        foreach ($orderItems as $orderItem) {
            $eliquids = $entityManager->getRepository(ELiquid::class)
                ->findBy(['name' => $orderItem->getProductName()]);
            foreach ($eliquids as $eliquid) {
                $newStock = max(0, $eliquid->getQuantity() - $orderItem->getQuantity());
                $eliquid->setQuantity($newStock);
                $entityManager->persist($eliquid);
            }
        }

        // Clear cart
        $cartId = $session->get('cart_id') ?? $request->cookies->get('cart_id');
        if ($cartId) {
            $cart = $cartRepository->find($cartId);
            if ($cart) {
                $cartItems = $entityManager->getRepository(CartItem::class)->findBy(['cart' => $cart]);
                foreach ($cartItems as $item) {
                    $entityManager->remove($item);
                }
                $entityManager->remove($cart);
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Order confirmed! Your products will be shipped soon.');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/order/{id}/cancel', name: 'app_order_cancel', methods: ['POST'])]
    public function cancelOrder(
        Order $order,
        EntityManagerInterface $entityManager,
        OrderItemRepository $orderItemRepository
    ): Response {
        if ($order->getState() === 'shipping' || $order->getState() === 'completed') {
            $this->addFlash('error', 'This order cannot be cancelled.');
            return $this->redirectToRoute('app_orders');
        }

        $orderItems = $orderItemRepository->findBy(['order' => $order]);
        foreach ($orderItems as $item) {
            $eliquid = $entityManager->getRepository(ELiquid::class)->findOneBy([
                'name' => $item->getProductName()
            ]);
            if ($eliquid) {
                $eliquid->setQuantity($eliquid->getQuantity() + $item->getQuantity());
                $entityManager->persist($eliquid);
            }
            $entityManager->remove($item);
        }

        $order->setState('cancelled');
        $entityManager->remove($order);
        $entityManager->flush();

        $this->addFlash('success', 'Order has been cancelled successfully.');
        return $this->redirectToRoute('app_home');
    }
}