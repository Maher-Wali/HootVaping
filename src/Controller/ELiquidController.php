<?php

namespace App\Controller;


use App\Entity\CartPackageItem;
use App\Entity\CartPackage;
use App\Entity\ELiquid;
use App\Entity\Cart;
use App\Repository\ELiquidRepository;
use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\JsonResponse;


final class ELiquidController extends AbstractController
{
    #[Route('/eliquid/{id}', name: 'app_e_liquid')]
    public function detail(ELiquid $eliquid): Response
    {
        return $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);
    }

    #[Route('/shop/pack_{name}', name: 'app_shop')]
    public function shop(EntityManagerInterface $entityManager, string $name, PackageRepository $packageRepository): Response
    {
        $package = $packageRepository->findOneBy(['name' => $name]);
        $maxQuantity =$package->getQuantity();
        $eliquids = $entityManager->getRepository(ELiquid::class)->findAll();
        return $this->render('e_liquid/shop.html.twig', [
            'eliquids' => $eliquids,
            'name' => $name,
            'maxQuantity' => $maxQuantity,
        ]);
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request, ELiquidRepository $eliquidRepository): Response
    {
        $query = $request->query->get('q', '');
        $eliquids = $eliquidRepository->findBySearchQuery($query);

        // Always return the _search_results template for AJAX requests
        return $this->render('e_liquid/_search_results.html.twig', [
            'eliquids' => $eliquids,
        ]);
    }

    #[Route('/add-to-cart/package', name: 'app_add_package_to_cart', methods: ['POST'])]
    public function addPackageToCart(
        Request $request,
        EntityManagerInterface $entityManager,
        PackageRepository $packageRepository
    ): Response
    {
        // Get the package and its price
        $packageName = $request->request->get('packageName');
        $package = $packageRepository->findOneBy(['name' => $packageName]);
        $packagePrice = $package->getTotal();

        // Get or create cart
        $session = $request->getSession();
        $cartId = $session->get('cart_id');

        if ($cartId) {
            $cart = $entityManager->getRepository(Cart::class)->find($cartId);
        }

        if (!isset($cart)) {
            $cart = new Cart();
            $cart->setTotal(0);
            $entityManager->persist($cart);
            $entityManager->flush();
            $session->set('cart_id', $cart->getId());
        }

        // Create CartPackage
        $cartPackage = new CartPackage();
        $cartPackage->setCart($cart);
        $cartPackage->setPackage($package);
        $entityManager->persist($cartPackage);

        // Create CartPackageItems for products with quantity > 0
        $products = $request->request->all('products');  // Changed this line
        foreach ($products as $productId => $quantity) {
            if ($quantity > 0) {
                $product = $entityManager->getRepository(ELiquid::class)->find($productId);

                if ($product) {
                    $cartPackageItem = new CartPackageItem();
                    $cartPackageItem->setCartPackage($cartPackage);
                    $cartPackageItem->setProduct($product);
                    $cartPackageItem->setQuantity((int)$quantity);
                    $entityManager->persist($cartPackageItem);
                }
            }
        }

        // Update cart total
        $newTotal = $cart->getTotal() + $packagePrice;
        $cart->setTotal($newTotal);

        $entityManager->flush();

        return $this->redirectToRoute('app_cart');
    }
}
