<?php

namespace App\Controller;

use App\Service\DatabaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/')]
class UserController extends AbstractController
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    #[Route('user/get/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserById(int $id): Response
    {
        $user = $this->databaseService->queryFromSlave1('SELECT * FROM users WHERE id = ?', [$id]);

        if (empty($user)) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($user[0]);
    }
}