<?php

namespace App\Service;

use Redis;

class ChatService
{
    public function __construct(
        private readonly Redis $redis,
        private readonly DatabaseService $databaseService
    ) {}

    public function sendMessage(int $dialogId, int $senderId, string $content)
    {
        // Создание уникального идентификатора для сообщения 101771
        $messageId = $this->redis->incr('message_id');

        // Формирование ключа и данных сообщения
        $messageKey = "message:$messageId";
        $messageData = [
            'dialog_id' => $dialogId,
            'sender_id' => $senderId,
            'content' => $content,
            'timestamp' => date('Y-m-d H:i:s'), // Добавляем текущий timestamp
        ];

        // Сохранение сообщения в Redis как хэш
        $this->redis->hMSet($messageKey, $messageData);

        // Добавление ID сообщения в список сообщений для соответствующего диалога
        $this->redis->rPush("dialog:$dialogId:messages", $messageId);
    }

    public function listMessages(int $dialogId)
    {
        $sql = 'SELECT * FROM messages WHERE dialog_id = :dialog_id ORDER BY timestamp';
        $parameters = [
            'dialog_id' => $dialogId,
        ];

        return $this->databaseService->fetchAll($sql, $parameters);
    }
}
