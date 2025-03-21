<?php

// src/Controller/HomeController.php

namespace App\Controller;

use App\Repository\ELiquidRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_redirect_home')]
    public function redirectToHome(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/home', name: 'app_home')]
    public function index(ELiquidRepository $eLiquidRepository): Response
    {
        // Fetch featured e-liquids (for example, the first 6 e-liquids)
        $featuredELiquids = $eLiquidRepository->findBy([], null, 6);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'featuredELiquids' => $featuredELiquids,
        ]);
    }
}
