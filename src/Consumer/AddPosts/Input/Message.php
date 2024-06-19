<?php

namespace App\Consumer\AddPosts\Input;

class Message
{
    private int $userId;
    private int $postId;
    private int $timestamp;

    public function __construct(int $userId, int $postId, int $timestamp)
    {
        $this->userId = $userId;
        $this->postId = $postId;
        $this->timestamp = $timestamp;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}