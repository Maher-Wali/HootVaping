<?php

// src/Controller/PromoCodeController.php

namespace App\Controller;

use App\Entity\PromoCode;
use App\Entity\PromoCodeUser;
use App\Repository\PromoCodeRepository;
use App\Repository\PromoCodeUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PromoCodeController extends AbstractController
{
    #[Route('/apply_code', name: 'app_promo_code_apply')]
    public function applyPromoCode(
        Request $request,
        PromoCodeRepository $promoCodeRepository,
        PromoCodeUserRepository $userPromoCodeRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ): JsonResponse {
        // Get the code from request parameters
        $code = $request->query->get('code');

        if (!$code) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No promo code provided.'
            ]);
        }

        $user = $security->getUser();

        // Check if user is authenticated
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'You need to log in to use a promo code.',
                'redirectUrl' => $this->generateUrl('app_login')
            ]);
        }

        // Find the promo code
        $promoCode = $promoCodeRepository->findOneBy(['code' => $code]);

        if (!$promoCode || !$promoCode->getIsActive()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Promo code is invalid.'
            ]);
        }

        if ($promoCode->getCurrentUses() >= $promoCode->getMaxUses()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Promo code has reached its usage limit.'
            ]);
        }

        if ($userPromoCodeRepository->findOneBy(['user' => $user, 'promoCode' => $promoCode])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'You have already used this promo code.'
            ]);
        }

        // Apply the promo code
        $userPromoCode = new PromoCodeUser();
        $userPromoCode->setUser($user);
        $userPromoCode->setPromoCode($promoCode);

        $entityManager->persist($userPromoCode);

        // Increment promo code usage count
        $promoCode->setCurrentUses($promoCode->getCurrentUses() + 1);
        $entityManager->persist($promoCode);

        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Promo code applied successfully!',
            'discount' => $promoCode->getDiscount() // Only return the discount value
        ]);
    }
}