<?php

namespace App\Controller;

use App\Entity\Package;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PackageController extends AbstractController
{
    #[Route('/package', name: 'app_packages')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $packages = $entityManager->getRepository(Package::class)->findAll();

        return $this->render('package/index.html.twig', [
            'packages' => $packages,
        ]);
    }
    #[Route('/package/{name}', name: 'app_package')]
    public function shop(string $name, EntityManagerInterface $entityManager): Response
    {
        return $this->redirectToRoute('app_shop', ['name' => $name]);
    }
}
