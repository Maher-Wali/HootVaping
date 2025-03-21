<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderPackageItem;
use App\Entity\Product;
use App\Repository\OrderPackageItemRepository;
use App\Repository\OrderPackageRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/orders', name: 'admin_orders')]
    public function allOrders(
        Request $request,
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        OrderPackageRepository $orderPackageRepository
    ): Response {
        // Get the selected state from the query parameters
        $selectedState = $request->query->get('state');

        // Define all possible states
        $states = ['pending', 'confirmed', 'shipping', 'completed'];

        // Fetch orders based on filter
        if ($selectedState && in_array($selectedState, $states)) {
            $orders = $orderRepository->findBy(['state' => $selectedState]);
        } else {
            $orders = $orderRepository->findBy(['state' => $states]);
        }

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

        return $this->render('admin/orders.html.twig', [
            'orders' => $orderData,
            'states' => $states,
            'selectedState' => $selectedState
        ]);
    }

    #[Route('/admin/order/confirm/{id}', name: 'admin_order_confirm', methods: ['POST'])]
    public function adminConfirmation(int $id, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            $this->addFlash('danger', 'Order not found.');
            return $this->redirectToRoute('admin_orders');
        }

        if ($order->getState() !== 'pending') {
            $this->addFlash('warning', 'Only pending orders can be confirmed.');
            return $this->redirectToRoute('admin_orders');
        }

        // Update order state to "confirmed"
        $order->setState('confirmed');
        $entityManager->persist($order);
        $entityManager->flush();

        $this->addFlash('success', 'Order #'.$id.' has been confirmed.');
        return $this->redirectToRoute('admin_orders');
    }

    #[Route('/admin/order/cancel/{id}', name: 'admin_order_cancel', methods: ['POST'])]
    public function adminCancelOrder(
        Order $order,
        EntityManagerInterface $entityManager,
        OrderPackageRepository $orderPackageRepository,
        OrderPackageItemRepository $orderPackageItemRepository
    ): Response {
        // Check if the order state is "completed"
        if ($order->getState() === 'completed') {
            $this->addFlash('error', 'This order cannot be cancelled as it is completed.');
            return $this->redirectToRoute('admin_orders');
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
                    $product->setAvailableQuantity($product->getAvailableQuantity() + $item->getQuantity());
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
        return $this->redirectToRoute('admin_orders');
    }
}
