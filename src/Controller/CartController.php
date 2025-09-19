<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\ELiquid;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Cookie;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository
    ): Response
    {
        $session = $request->getSession();
        $cartId = $session->get('cart_id') ?? $request->cookies->get('cart_id');

        if ($cartId) {
            $cart = $cartRepository->find($cartId);
        }

        if (!$cart) {
            $cart = new Cart();
            $entityManager->persist($cart);
            $entityManager->flush();
        }

        $session->set('cart_id', $cart->getId());
        $response = new Response();
        $response->headers->setCookie(
            new Cookie('cart_id', $cart->getId(), strtotime('+5 days'))
        );

        $cartItems = $cartItemRepository->findBy(['cart' => $cart]);
        if (empty($cartItems)) {
            return $this->render('cart/empty_cart.html.twig', [], $response);
        }

        $cart->recalculateTotal($entityManager);

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'cartItems' => $cartItems,
        ], $response);
    }

    #[Route('/add-to-cart', name: 'app_add_to_cart', methods: ['POST'])]
    public function addToCart(
        Request $request,
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository
    ): Response
    {
        $eliquidId = $request->request->getInt('eliquidId');
        $quantity = $request->request->getInt('quantity', 1);

        if ($eliquidId <= 0 || $quantity <= 0) {
            $this->addFlash('error', 'Invalid eliquid or quantity.');
            return $this->redirectToRoute('app_shop');
        }

        $eliquid = $entityManager->getRepository(ELiquid::class)->find($eliquidId);
        if (!$eliquid) {
            $this->addFlash('error', 'ELiquid not found.');
            return $this->redirectToRoute('app_shop');
        }

        $session = $request->getSession();
        $cartId = $session->get('cart_id') ?? $request->cookies->get('cart_id');

        if ($cartId) {
            $cart = $cartRepository->find($cartId);
        }

        if (!$cart) {
            $cart = new Cart();
            $entityManager->persist($cart);
            $entityManager->flush();
        }

        $cartItem = $cartItemRepository->findOneBy([
            'cart' => $cart,
            'eliquid' => $eliquid,
        ]);

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setELiquid($eliquid);
            $cartItem->setQuantity($quantity);
        }

        $entityManager->persist($cartItem);
        $cart->recalculateTotal($entityManager);
        $entityManager->flush();

        $session->set('cart_id', $cart->getId());
        $response = new Response();
        $response->headers->setCookie(
            new Cookie('cart_id', $cart->getId(), strtotime('+5 days'))
        );

        $this->addFlash('success', 'ELiquid added to cart!');
        return $this->redirectToRoute('app_cart', [], $response);
    }

    #[Route('/cart/remove-item/{id}', name: 'cart_remove_item')]
    public function removeItem(
        CartItem $cartItem,
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository
    ): Response
    {
        $cart = $cartItem->getCart();
        $entityManager->remove($cartItem);
        $cart->recalculateTotal($entityManager);
        $entityManager->flush();

        $this->addFlash('success', 'ELiquid removed from cart successfully.');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/redirect', name: 'cart_redirect')]
    public function redirectToCheckout(): Response
    {
        return $this->redirectToRoute('guest_checkout');
    }
}