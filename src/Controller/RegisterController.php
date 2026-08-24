<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Le contenu JSON est invalide.'], 400);
        }

        $email = is_string($data['email'] ?? null) ? $data['email'] : '';
        $plainPassword = is_string($data['password'] ?? null) ? $data['password'] : '';
        $errors = [];
        $user = (new User())->setEmail($email);

        foreach ($validator->validate($user) as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        if (mb_strlen($plainPassword) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if ($errors !== []) {
            return $this->json(['errors' => $errors], 422);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
        ], 201);
    }
}
