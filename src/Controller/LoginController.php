<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderPackageItem;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\OrderPackageRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/account/details', name: 'app_account_details')]
    public function accountDetails(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Your account details have been updated.');

            return $this->redirectToRoute('app_account_details');
        }

        return $this->render('login/detail.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/account/orders', name: 'app_account_orders')]
    public function accountOrders(
        Request $request,
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        OrderPackageRepository $orderPackageRepository,
    ): Response
    {
        $user = $this->getUser();

        // Fetch orders sorted by date in descending order
        $orders = $orderRepository->findBy(
            ['user' => $user],
            ['date' => 'DESC']  // Add sorting here
        );

        $orderData = [];
        foreach ($orders as $order) {
            // Find packages related to the order
            $orderPackages = $orderPackageRepository->findBy(['order' => $order]);

            $packageData = [];

            foreach ($orderPackages as $orderPackage) {
                // Find items related to the package
                $orderPackageItems = $entityManager->getRepository(OrderPackageItem::class)
                    ->findBy(['orderPackage' => $orderPackage]);

                // Store package details along with its items
                $packageData[] = [
                    'package' => $orderPackage,
                    'items' => $orderPackageItems,
                ];
            }

            // Store order details along with its packages
            $orderData[] = [
                'order' => $order,
                'packages' => $packageData,
            ];
        }

        return $this->render('login/user_orders.html.twig', [
            'orders' => $orderData,
        ]);
    }

}