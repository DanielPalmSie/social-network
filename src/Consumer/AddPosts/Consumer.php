<?php

namespace App\Consumer\AddPosts;


use App\Consumer\AddPosts\Input\Message;
use App\Service\DatabaseService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Redis;

class Consumer implements ConsumerInterface
{
    private Redis $redis;

    public function __construct(private readonly DatabaseService $databaseService)
    {
        $this->redis = new Redis();
        $this->redis->connect('redis', 6379);
    }

    public function execute(AMQPMessage $msg): int
    {
        $data = json_decode($msg->body, true);
        $message = new Message($data['userId'], $data['postId'], $data['timestamp']);

        $userId = $message->getUserId();
        $postId = $message->getPostId();
        $timestamp = $message->getTimestamp();

        // Получение списка друзей (замените на реальный метод получения друзей)
        $friends = $this->getFriends($userId);

        foreach ($friends as $friendId) {
            // Обновление кеша в Redis
            $this->redis->zAdd("feed:$friendId", $timestamp, $postId);
            // Обрезка до 1000 элементов
            $this->redis->zremrangebyrank("feed:$friendId", 0, -1001);
        }

        return self::MSG_ACK;
    }

    private function reject(string $error): int
    {
        echo "Incorrect message: $error";

        return self::MSG_REJECT;
    }

    private function getFriends(int $userId): array
    {
        $sql = "SELECT friend_id FROM friends WHERE user_id = :user_id";
        $friends = $this->databaseService->fetchAll($sql, ['user_id' => $userId]);


        return array_column($friends, 'friend_id');
    }
}