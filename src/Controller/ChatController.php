<?php

namespace App\Controller;

use App\Service\ChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/chat/')]
class ChatController extends AbstractController
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    #[Route('dialog/{userId}/send', name: 'send_message', methods: ['POST'])]
    public function sendMessage(int $userId, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $dialogId = $data['dialog_id'];
        $senderId = $data['sender_id'];
        $content = $data['content'];

        $this->chatService->sendMessage($dialogId, $senderId, $content);

        return new JsonResponse(['status' => 'Message sent']);
    }

    #[Route('dialog/{userId}/list', name: 'list_messages', methods: ['GET'])]
    public function listMessages(int $userId, Request $request): Response
    {
        $dialogId = $request->query->get('dialog_id');
        $messages = $this->chatService->listMessages($dialogId);

        return $this->json($messages);
    }
}