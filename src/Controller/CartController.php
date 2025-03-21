<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartPackage;
use App\Entity\PromoCode;
use App\Entity\PromoCodeUser;
use App\Repository\CartPackageItemRepository;
use App\Repository\CartPackageRepository;
use App\Repository\PromoCodeUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
        CartPackageRepository $cartPackageRepository,
        CartPackageItemRepository $cartPackageItemRepository,
        PromoCodeUserRepository $promoCodeUserRepository,
        Security $security // Inject security to get the user
    ): Response
    {
        $session = $request->getSession();
        $cookies = $request->cookies;
        $user = $security->getUser(); // Get the logged-in user
        $cart = null;

        // Case 1: Logged-in user -> Retrieve or create cart
        if ($user) {
            $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
            if (!$cart) {
                $cart = new Cart();
                $cart->setUser($user);
                $entityManager->persist($cart);
                $entityManager->flush();
            }
            $session->set('cart_id', $cart->getId());

            $bestPromoCode = $this->findBestPromoCodeForUser($user, $promoCodeUserRepository);
        }
        // Case 2: Guest user -> Check session, then cookie, then create new cart
        else {
            $cartId = $session->get('cart_id') ?? $cookies->get('cart_id');

            if ($cartId) {
                $cart = $entityManager->getRepository(Cart::class)->find($cartId);
            }

            // If no cart exists, create a new one for the guest
            if (!$cart) {
                $cart = new Cart();
                $entityManager->persist($cart);
                $entityManager->flush();
            }

            // Store cart ID in session and cookie
            $session->set('cart_id', $cart->getId());

            $response = new Response();
            $response->headers->setCookie(
                new Cookie('cart_id', $cart->getId(), strtotime('+5 days')) // Store for 30 days
            );
        }

        // Retrieve CartPackages and their items
        $cartPackages = $cartPackageRepository->findBy(['cart' => $cart]);
        if(empty($cartPackages)){
            return $this->render('cart/empty_cart.html.twig');
        }
        $packageItems = [];

        $cart->recalculateTotal($entityManager);


        foreach ($cartPackages as $cartPackage) {
            $packageItems[$cartPackage->getId()] = $cartPackageItemRepository->findBy([
                'cartPackage' => $cartPackage
            ]);
        }

        // Render the response (ensure cookies are included if user is a guest)
        $response ??= new Response();
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'cartPackages' => $cartPackages,
            'packageItems' => $packageItems,
            'bestPromoCode' => $bestPromoCode ?? null,
        ], $response);
    }




    #[Route('/add-to-cart', name: 'app_add_package_to_cart', methods: ['POST'])]
    public function addToCart(Request $request, SessionInterface $session): Response
    {
        // Retrieve package name and selected product quantities
        $packageName = $request->request->get('packageName');
        $products = $request->request->all('products'); // Associative array with product names as keys and quantities as values

        if (!$packageName || empty($products)) {
            $this->addFlash('error', 'Invalid package selection.');
            return $this->redirectToRoute('app_shop'); // Redirect back to shop if form data is invalid
        }

        // Retrieve the cart from the session (or initialize an empty array if it doesn't exist)
        $cart = $session->get('cart', []);

        // Store the package with its products and quantities
        $cart[$packageName] = [
            'name' => $packageName,
            'products' => $products,
        ];

        // Save the updated cart back to session
        $session->set('cart', $cart);

        // Optional: Flash message for user feedback
        $this->addFlash('success', 'Package added to cart!');

        return $this->redirectToRoute('app_cart'); // Redirect to cart page
    }

    #[Route('/cart/remove-package/{id}', name: 'cart_remove_package')]
    public function removePackage(CartPackage $cartPackage, EntityManagerInterface $entityManager): Response
    {
        $cart = $cartPackage->getCart();

        // Remove the CartPackage
        $entityManager->remove($cartPackage);

        // Recalculate cart total
        $cart->recalculateTotal($entityManager);

        $entityManager->flush();

        $this->addFlash('success', 'Package removed from cart successfully');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/redirect', name: 'cart_redirect')]
    public function redirectToCheckout(Security $security): Response
    {
        $user = $security->getUser();

        // Case 1: Guest user → Redirect to guest checkout page
        if (!$user) {
            return $this->redirectToRoute('guest_checkout');
        }

        // Case 2: Logged-in user → Check if user details are complete
        if (!$user->getAddress() || !$user->getPhoneNumber()) {
            return $this->redirectToRoute('profile_completion');
        }

        // Case 3: All information is filled → Proceed to checkout
        return $this->redirectToRoute('app_checkout');
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
