<?php

namespace App\Controller;

use App\Service\AsyncService;
use App\Service\DatabaseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;

#[Route('api/')]
class PostController extends AbstractController
{
    public function __construct(
        private readonly DatabaseService $databaseService,
        private readonly AsyncService $asyncService,
        private readonly Security $security
    )
    {}

    #[Route('post', name: 'add_post', methods: ['POST'])]
    public function createPost(Request $request): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $userId = $user->getUserIdentifier();

        $data = json_decode($request->getContent(), true);

        $content = $data['content'];
        $createdAt = new \DateTime();

        // Сохранение поста в базу данных
        $sql = "
            INSERT INTO posts (user_id, content, created_at, updated_at)
            VALUES (:user_id, :content, :created_at, :updated_at)
            RETURNING id
        ";
        $params = [
            'user_id' => $userId,
            'content' => $content,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $createdAt->format('Y-m-d H:i:s')
        ];
        $result = $this->databaseService->query($sql, $params);
        $postId = $result[0]['id'];

        $message = [
            'userId' => $userId,
            'postId' => $postId,
            'timestamp' => strtotime($createdAt->format('Y-m-d H:i:s'))
        ];

        $this->asyncService->publishToExchange(AsyncService::ADD_POST, json_encode($message));

        return new JsonResponse(['status' => 'Post created!', 'post_id' => $postId], JsonResponse::HTTP_CREATED);
    }
}