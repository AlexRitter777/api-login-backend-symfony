<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class ApiLoginController extends AbstractController
{


    private $passwordHasher;
    private $jwtManager;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,

    ){
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
    }



    #[Route('/api/login', name: 'app_api_login')]
    public function login(Request $request, ManagerRegistry $doctrine): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
        }

        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);



        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            //or Exception + Exception listener => Json response???
            return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtManager->create($user);


        return new JsonResponse([
            'accessToken' => $token,
            'message' => 'success',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
            ],
        ]);
    }


    //return current User

}
