<?php

namespace App\Service;

use Redis;

class FeedService
{
    public function __construct(
        private readonly Redis $redis,
        private readonly DatabaseService $databaseService
    ) {}

    public function getFeed(int $userId, int $start = 0, int $count = 10): array
    {
        $key = "feed:$userId";

        if ($this->redis->zCard($key) === 0) {
            $this->loadFeedFromDatabase($userId);
        }

        $postEntries = $this->redis->zRevRange($key, $start, $start + $count - 1, ['withscores' => true]);
        $posts = [];
        foreach ($postEntries as $postId => $timestamp) {
            $posts[] = ['post_id' => $postId, 'timestamp' => $timestamp];
        }

        return $posts;
    }

    private function loadFeedFromDatabase(int $userId): void
    {
        $sql = "
            SELECT p.id, p.user_id, p.created_at
            FROM posts p
            JOIN friends f ON p.user_id = f.friend_id
            WHERE f.user_id = :user_id
            ORDER BY p.created_at DESC
            LIMIT 1000
        ";
        $params = ['user_id' => $userId];
        $posts = $this->databaseService->fetchAll($sql, $params);

        foreach ($posts as $post) {
            $postId = $post['id'];
            $createdAt = strtotime($post['created_at']);
            $this->redis->zAdd("feed:$userId", $createdAt, $postId);
        }
    }
}