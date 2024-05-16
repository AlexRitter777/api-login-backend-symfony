<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security){
        $this->entityManager = $entityManager;
        $this->security = $security;
    }


    #[Route('/api/user/update', name: 'app_api_user_update', methods: ['POST'])]
    public function updateUserName(Request $request): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $username =  $data['username'] ?? null;

        if (!$username ) {
            return new JsonResponse(['message' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found!'], Response::HTTP_BAD_REQUEST);
        }

        $user->setUsername($username);

        $this->entityManager->flush();

        return new JsonResponse(
            [
            'message' => 'Username updated successfully!',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                ],
            ]
        );

    }
}
