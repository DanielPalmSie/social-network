<?php

namespace App\Service;

class ChatService
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function sendMessage(int $dialogId, int $senderId, string $content)
    {
        $sql = 'INSERT INTO messages (dialog_id, sender_id, content) VALUES (:dialog_id, :sender_id, :content)';
        $parameters = [
            'dialog_id' => $dialogId,
            'sender_id' => $senderId,
            'content' => $content,
        ];

        $this->databaseService->execute($sql, $parameters);
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
