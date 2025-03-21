<?php

namespace App\Controller;

use App\Entity\CartPackage;
use App\Entity\CartPackageItem;
use App\Entity\Contact;
use App\Entity\Order;
use App\Entity\OrderPackage;
use App\Entity\OrderPackageItem;
use App\Entity\Product;
use App\Entity\PromoCode;
use App\Form\GuestCheckoutType;
use App\Form\UserType;
use App\Repository\CartRepository;
use App\Repository\CartPackageRepository;
use App\Repository\CartPackageItemRepository;
use App\Repository\OrderPackageItemRepository;
use App\Repository\OrderPackageRepository;
use App\Repository\PromoCodeUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseStatusCodeSame;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

final class OrderController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(
        Request $request,
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository,
        CartPackageRepository $cartPackageRepository,
        CartPackageItemRepository $cartPackageItemRepository,
        OrderPackageRepository $orderPackageRepository,
        OrderPackageItemRepository $orderPackageItemRepository,
        PromoCodeUserRepository $promoCodeUserRepository,
        Security $security,
        SessionInterface $session
    ): Response {

        $user = $security->getUser();
        $cartId = $session->get('cart_id') ?? $request->cookies->get('cart_id');

        if (!$cartId) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart');
        }

        $cart = $cartRepository->find($cartId);
        if (!$cart || $cartPackageRepository->count(['cart' => $cart]) === 0) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart');
        }

        // Create a new order
        $order = new Order();
        if ($user) {
            $order->setUser($user);
            $order->setAddress($user->getAddress());

            $bestPromoCode = $this->findBestPromoCodeForUser($user, $promoCodeUserRepository);
            $discount = $bestPromoCode ? $bestPromoCode->getDiscount() : 0;
        } else{
            $contact = $session->get('guest_contact');
            $address = $session->get('guest_address');

            if (!$contact || !$address) {
                $this->addFlash('error', 'Missing contact or address information. Please complete checkout again.');
                return $this->redirectToRoute('guest_checkout');
            }

            $entityManager->persist($contact);
            $entityManager->persist($address);
            $entityManager->flush();

            $order->setContact($contact);
            $order->setAddress($address);
            $discount = 0;
        }

        $cart->recalculateTotal($entityManager);
        $order->setTotal($cart->getTotal() * (100 - $discount) / 100);

        $order->setDate(new \DateTime()); // Assigns the current date

        $entityManager->persist($order);

        // Convert CartPackages to OrderPackages
        $cartPackages = $cartPackageRepository->findBy(['cart' => $cart]);
        foreach ($cartPackages as $cartPackage) {
            $orderPackage = new OrderPackage();
            $orderPackage->setOrder($order);
            $orderPackage->setPackageName($cartPackage->getPackage()->getName());

            $entityManager->persist($orderPackage);

            // Convert CartPackageItems to OrderPackageItems
            $cartPackageItems = $cartPackageItemRepository->findBy(['cartPackage' => $cartPackage]);
            foreach ($cartPackageItems as $cartPackageItem) {
                $orderPackageItem = new OrderPackageItem();
                $orderPackageItem->setOrderPackage($orderPackage);
                $orderPackageItem->setProductName($cartPackageItem->getProduct()->getName());
                $orderPackageItem->setQuantity($cartPackageItem->getQuantity());

                $entityManager->persist($orderPackageItem);
            }
        }

        // Save the order and cleanup
        $entityManager->flush();

        $orderPackages = $orderPackageRepository->findBy(['order' => $order]);
        $orderPackageItems = [];
        foreach ($orderPackages as $orderPackage) {
            $items = $orderPackageItemRepository->findBy(['orderPackage' => $orderPackage]);
            $orderPackageItems = array_merge($orderPackageItems, $items);
        }

        if (!$user) {
            $session->set('order_id', $order->getId());
        }

        // Render the response (ensure cookies are included if user is a guest)
        $response ??= new Response();
        return $this->render('order/index.html.twig', [
            'order' => $order,
            'orderPackages' => $orderPackages,
            'orderPackageItems' => $orderPackageItems,
            'discount' => $discount,
        ], $response);
    }

    #[Route('/order/guest_checkout', name: 'guest_checkout')]
    public function guestCheckout(Request $request, SessionInterface $session): Response
    {
        $form = $this->createForm(GuestCheckoutType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Create Contact instance
            $contact = new Contact();
            $contact->setFirstname($data['firstname']);
            $contact->setLastname($data['lastname']);
            $contact->setEmail($data['email']);
            $contact->setPhoneNumber($data['phone_number']);

            // Address comes from the embedded form
            $address = $data['address']; // Symfony automatically maps it

            // Store in session
            $session->set('guest_contact', $contact);
            $session->set('guest_address', $address);

            return $this->redirectToRoute('app_checkout');
        }

        return $this->render('order/guest_checkout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/order/profile_completion', name: 'profile_completion')]
    public function completeProfile(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('guest_checkout');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password only if a new one was provided
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword(password_hash($plainPassword, PASSWORD_BCRYPT));
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profile completed successfully! You can now proceed to checkout.');

            return $this->redirectToRoute('app_checkout');
        }

        return $this->render('order/profile_completion.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/order/confirm', name: 'order_confirm')]
    public function confirmOrder(
        EntityManagerInterface $entityManager,
        Security $security,
        SessionInterface $session,
        Request $request,
        CartRepository $cartRepository,
        OrderPackageRepository $orderPackageRepository,
        PromoCodeUserRepository $promoCodeUserRepository
    ): Response {
        $user = $security->getUser();
        $order = null;

        if ($user) {
            $order = $entityManager->getRepository(Order::class)->findOneBy([
                'user' => $user,
                'state' => "creating",
            ]);
        } else {
            $orderId = $session->get('order_id');
            if ($orderId) {
                $order = $entityManager->getRepository(Order::class)->find($orderId);
            }
        }

        if (!$order) {
            $this->addFlash('error', 'No order found to confirm.');
            return $this->redirectToRoute('app_cart');
        }

        // Change order state to "pending"
        $order->setState('pending');
        $entityManager->persist($order);

        // Fetch all OrderPackages linked to the order
        $orderPackages = $orderPackageRepository->findBy(['order' => $order]);

        // Update product stock - FIXED SECTION
        foreach ($orderPackages as $orderPackage) {
            $orderPackageItems = $entityManager->getRepository(OrderPackageItem::class)->findBy(['orderPackage' => $orderPackage]);

            foreach ($orderPackageItems as $orderPackageItem) {
                // Find all products with the given name (both Product and ELiquid)
                $products = $entityManager->getRepository(Product::class)
                    ->findBy(['name' => $orderPackageItem->getProductName()]);

                foreach ($products as $product) {
                    $newStock = max(0, $product->getQuantity() - $orderPackageItem->getQuantity());
                    $product->setQuantity($newStock);
                    $entityManager->persist($product);
                }
            }
        }

        // Mark the promo code as used (if applicable)
        if ($user) {
            // Find the best promo code for the user
            $bestPromoCode = $this->findBestPromoCodeForUser($user, $promoCodeUserRepository);

            if ($bestPromoCode) {
                // Find the PromoCodeUser instance for this user and promo code
                $promoCodeUser = $promoCodeUserRepository->findOneBy([
                    'user' => $user,
                    'promoCode' => $bestPromoCode,
                    'used' => false,
                ]);

                if ($promoCodeUser) {
                    // Mark the promo code as used
                    $promoCodeUser->setUsed(true);
                    $entityManager->persist($promoCodeUser);
                }
            }
        }

        $cartId = $session->get('cart_id') ?? $request->cookies->get('cart_id');
        if ($cartId) {
            $cart = $cartRepository->find($cartId);

            if ($cart) {
                // Fetch all CartPackages linked to the cart
                $cartPackages = $entityManager->getRepository(CartPackage::class)->findBy(['cart' => $cart]);

                foreach ($cartPackages as $cartPackage) {
                    // Fetch all CartPackageItems linked to this CartPackage
                    $cartPackageItems = $entityManager->getRepository(CartPackageItem::class)->findBy(['cartPackage' => $cartPackage]);
                    foreach ($cartPackageItems as $item) {
                        // Remove CartPackageItem
                        $entityManager->remove($item);
                    }
                    // Remove CartPackage after its items are deleted
                    $entityManager->remove($cartPackage);
                }
            }
        }

        // Persist changes
        $entityManager->flush();

        $this->addFlash('success', 'Order confirmed! Your products will be shipped soon.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/order/{id}/cancel', name: 'app_order_cancel', methods: ['POST'])]
    public function cancelOrder(
        Order $order,
        EntityManagerInterface $entityManager,
        OrderPackageRepository $orderPackageRepository,
        OrderPackageItemRepository $orderPackageItemRepository,
        Security $security
    ): Response {
        // Security check: ensure user can only cancel their own orders
        if (($security->getUser() && $order->getUser() !== $security->getUser()) ||
            (!$security->getUser() && $order->getContact() === null)) {
            throw $this->createAccessDeniedException('You cannot cancel this order.');
        }

        // Check if order can be cancelled
        if ($order->getState() === 'shipping' || $order->getState() === 'completed') {
            $this->addFlash('error', 'This order cannot be cancelled.');
            return $this->redirectToRoute('app_orders');
        }

        // Get all OrderPackages for this order
        $orderPackages = $orderPackageRepository->findBy(['order' => $order]);

        foreach ($orderPackages as $orderPackage) {
            // Get all OrderPackageItems for this package
            $orderPackageItems = $orderPackageItemRepository->findBy(['orderPackage' => $orderPackage]);

            foreach ($orderPackageItems as $item) {
                // Find the corresponding product and restore its quantity
                $product = $entityManager->getRepository(Product::class)->findOneBy([
                    'name' => $item->getProductName()
                ]);

                if ($product) {
                    // Restore the product quantities
                    $product->setQuantity($product->getQuantity() + $item->getQuantity());
                    $entityManager->persist($product);
                }

                // Remove the OrderPackageItem
                $entityManager->remove($item);
            }

            // Remove the OrderPackage
            $entityManager->remove($orderPackage);
        }

        // Set order state to cancelled before removal (optional, if you want to keep cancelled orders)
        $order->setState('cancelled');

        // Remove the order
        $entityManager->remove($order);

        // Persist all changes
        $entityManager->flush();

        $this->addFlash('success', 'Order has been cancelled successfully.');
        return $this->redirectToRoute('app_account_orders');
    }

    private function findBestPromoCodeForUser($user, PromoCodeUserRepository $promoCodeUserRepository): ?PromoCode
    {
        $promoCodesUser = $promoCodeUserRepository->findBy([
            'user' => $user,
            'used' => false,
        ]);

        $bestPromoCode = null;
        $maxDiscount = 0;

        foreach ($promoCodesUser as $promoCodeUser) {
            $promoCode = $promoCodeUser->getPromoCode();
            if ($promoCode->getIsActive() && $promoCode->getDiscount() > $maxDiscount) {
                $maxDiscount = $promoCode->getDiscount();
                $bestPromoCode = $promoCode;
            }
        }

        return $bestPromoCode;
    }


}
