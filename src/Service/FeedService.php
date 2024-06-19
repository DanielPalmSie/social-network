<?php

namespace App\Service;

use Redis;

class FeedService
{
    private Redis $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('redis', 6379);
    }

    public function getFeed(int $userId, int $start = 0, int $count = 10): array
    {
        $postEntries = $this->redis->zrevrange("feed:$userId", $start, $start + $count - 1, ['withscores' => TRUE]);
        $posts = [];
        foreach ($postEntries as $postId => $timestamp) {
            $posts[] = ['post_id' => $postId, 'timestamp' => $timestamp];
        }
        return $posts;
    }
}