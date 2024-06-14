<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('api/register', name: 'register_user', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['username']) || empty($data['password']) || empty($data['roles'])) {
            return new JsonResponse(['message' => 'Missing data'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->userService->registerUser($data['username'], $data['password'], $data['roles']);
            return new JsonResponse(['message' => 'User successfully registered'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('api/user/search', name: 'search_users', methods: ['GET'])]
    public function searchUsers(Request $request): Response
    {
        $firstNamePrefix = $request->query->get('firstName');
        $lastNamePrefix = $request->query->get('lastName');

        if ($firstNamePrefix === null || $lastNamePrefix === null) {
            return $this->json(['message' => 'Both firstName and lastName parameters are required'], Response::HTTP_BAD_REQUEST);
        }

        $users = $this->userService->searchUsersFromSlave2($firstNamePrefix, $lastNamePrefix);

        return $this->json($users);
    }
}