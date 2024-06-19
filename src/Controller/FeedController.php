<?php

namespace App\Controller;

use App\Service\FeedService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('api/')]
class FeedController extends AbstractController
{
    public function __construct(
        private readonly FeedService $feedService,
        private readonly Security $security
    ) {}

    #[Route('feed', name: 'get_feed', methods: ['GET'])]
    public function getFeed(): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $userId = $user->getUserIdentifier();
        $feed = $this->feedService->getFeed($userId, 0, 10);
        return new JsonResponse($feed);
    }
}