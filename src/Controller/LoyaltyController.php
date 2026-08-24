<?php

namespace App\Controller;

use App\Entity\LoyaltyAccount;
use App\Entity\User;
use App\Repository\LoyaltyAccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class LoyaltyController extends AbstractController
{
    #[Route('/api/loyalty', name: 'api_loyalty_get', methods: ['GET'])]
    public function getAccount(LoyaltyAccountRepository $repository): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        $account = $repository->findOneBy(['user' => $user]);

        if (!$account) {
            return $this->json(['message' => 'Aucun compte fidélité.'], 404);
        }

        return $this->accountResponse($account);
    }

    #[Route('/api/loyalty', name: 'api_loyalty_create', methods: ['POST'])]
    public function createAccount(
        LoyaltyAccountRepository $repository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $user = $this->getAuthenticatedUser();
        $existingAccount = $repository->findOneBy(['user' => $user]);

        if ($existingAccount) {
            return $this->json(['message' => 'Le compte fidélité existe déjà.'], 409);
        }

        $account = (new LoyaltyAccount())->setUser($user);
        $entityManager->persist($account);
        $entityManager->flush();

        return $this->accountResponse($account, 201);
    }

    private function getAuthenticatedUser(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }

    private function accountResponse(LoyaltyAccount $account, int $status = 200): JsonResponse
    {
        return $this->json([
            'id' => $account->getId(),
            'email' => $account->getUser()?->getEmail(),
            'points' => $account->getPoints(),
        ], $status);
    }
}
