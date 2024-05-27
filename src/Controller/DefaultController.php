<?php

namespace App\Controller;

use App\Service\DatabaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    #[Route('users', name: 'users', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->databaseService->query('SELECT * FROM users');

        return $this->json($users);
    }
}