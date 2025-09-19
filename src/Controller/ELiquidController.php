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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ELiquidController extends AbstractController
{
    #[Route('/eliquid/{id}', name: 'app_e_liquid')]
    public function detail(ELiquid $eliquid): Response
    {
        $response = $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }

    #[Route('/shop', name: 'app_shop')]
    public function shop(EntityManagerInterface $entityManager): Response
    {
        $eliquids = $entityManager->getRepository(ELiquid::class)->findAll();
        $minQuantity = 2;

        $response = $this->render('e_liquid/shop.html.twig', [
            'eliquids' => $eliquids,
            'minQuantity' => $minQuantity,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request, ELiquidRepository $eliquidRepository): Response
    {
        $query = $request->query->get('q', '');
        $eliquids = $eliquidRepository->findBySearchQuery($query);

        $response = $this->render('e_liquid/_search_results.html.twig', [
            'eliquids' => $eliquids,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }

    #[Route('/add-to-cart/package', name: 'app_add_package_to_cart', methods: ['POST'])]
    public function addPackageToCart(
        Request $request,
        EntityManagerInterface $entityManager,
        PackageRepository $packageRepository
    ): Response {
        $packageName = $request->request->get('packageName');
        $package = $packageRepository->findOneBy(['name' => $packageName]);
        $packagePrice = $package->getTotal();

        $session = $request->getSession();
        $cartId = $session->get('cart_id');
        $cart = $cartId ? $entityManager->getRepository(Cart::class)->find($cartId) : null;

        if (!$cart) {
            $cart = new Cart();
            $cart->setTotal(0);
            $entityManager->persist($cart);
            $entityManager->flush();
            $session->set('cart_id', $cart->getId());
        }

        $cartPackage = new CartPackage();
        $cartPackage->setCart($cart);
        $cartPackage->setPackage($package);
        $entityManager->persist($cartPackage);

        $products = $request->request->all('products');
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

        $newTotal = $cart->getTotal() + $packagePrice;
        $cart->setTotal($newTotal);

        $entityManager->flush();

        $response = $this->redirectToRoute('app_cart');
        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }
    #[Route('/eliquid/oni_sweet', name: 'app_oni_sweet')]
    public function oniSweetDetail(ELiquidRepository $eliquidRepository): Response
    {
        $name = 'Oni Sweet';
        $eliquid = $eliquidRepository->findOneBy(['name' => $name]);
        $response = $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }
    #[Route('/eliquid/shigeri', name: 'app_shigeri')]
    public function shigeriDetail(ELiquidRepository $eliquidRepository): Response
    {
        $name = 'Shigeri';
        $eliquid = $eliquidRepository->findOneBy(['name' => $name]);
        $response = $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }
    #[Route('/eliquid/phoenix_sweet', name: 'app_phoenix_sweet')]
    public function phoenixSweetDetail(ELiquidRepository $eliquidRepository): Response
    {
        $name = 'Phoenix Sweet';
        $eliquid = $eliquidRepository->findOneBy(['name' => $name]);
        $response = $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }
    #[Route('/eliquid/ragnarok_x', name: 'app_ragnarok_x')]
    public function RagnarokXDetail(ELiquidRepository $eliquidRepository): Response
    {
        $name = 'Ragnarok X';
        $eliquid = $eliquidRepository->findOneBy(['name' => $name]);
        $response = $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }
    #[Route('/eliquid/shaken', name: 'app_shaken')]
    public function shakenDetail(ELiquidRepository $eliquidRepository): Response
    {
        $name = 'Shaken';
        $eliquid = $eliquidRepository->findOneBy(['name' => $name]);
        $response = $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }
    #[Route('/eliquid/luna', name: 'app_luna')]
    public function lunaDetail(ELiquidRepository $eliquidRepository): Response
    {
        $name = 'Luna';
        $eliquid = $eliquidRepository->findOneBy(['name' => $name]);
        $response = $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }
    #[Route('/eliquid/lycan_pink', name: 'app_lycan_pink')]
    public function lycanPinkDetail(ELiquidRepository $eliquidRepository): Response
    {
        $name = 'Lycan Pink';
        $eliquid = $eliquidRepository->findOneBy(['name' => $name]);
        $response = $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }
    #[Route('/eliquid/uraken', name: 'app_uraken')]
    public function urakenDetail(ELiquidRepository $eliquidRepository): Response
    {
        $name = 'Uraken';
        $eliquid = $eliquidRepository->findOneBy(['name' => $name]);
        $response = $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }
    #[Route('/eliquid/fury', name: 'app_fury')]
    public function furyDetail(EliquidRepository $eliquidRepository): Response
    {
        $name = 'Fury Sweet';
        $eliquid = $eliquidRepository->findOneBy(['name' => $name]);
        $response = $this->render('e_liquid/detail.html.twig', [
            'eliquid' => $eliquid,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }

}
