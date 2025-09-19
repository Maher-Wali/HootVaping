<?php

// src/Controller/HomeController.php

namespace App\Controller;

use App\Repository\ELiquidRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ELiquidRepository $eLiquidRepository): Response
    {
        $featuredELiquids = $eLiquidRepository->findBy([], null, 6);

        $response = $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'featuredELiquids' => $featuredELiquids,
        ]);

        $response->headers->remove('X-Robots-Tag');
        $response->headers->set('X-Robots-Tag', 'index, follow');

        return $response;
    }
}
