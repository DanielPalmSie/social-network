<?php

namespace App\Command;

use App\Service\DatabaseService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Redis;

class LoadFeedDataCommand extends Command
{
    protected static $defaultName = 'app:load-feed-data';

    private $redis;
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        parent::__construct();

        $this->redis = new Redis();
        $this->redis->connect('redis', 6379);

        $this->databaseService = $databaseService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Loads the latest 1000 posts into Redis feed cache.')
            ->setHelp('This command allows you to load the latest 1000 posts into the Redis feed cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Получение последних 1000 постов
        $sql = "
            SELECT p.id, p.user_id, p.created_at
            FROM posts p
            ORDER BY p.created_at DESC
            LIMIT 1000
        ";
        $posts = $this->databaseService->fetchAll($sql, []);

        // Очистка существующих данных в Redis
        $this->redis->flushDB();

        // Добавление постов в кеш Redis
        foreach ($posts as $post) {
            $userId = $post['user_id'];
            $postId = $post['id'];
            $createdAt = strtotime($post['created_at']);

            // Используем zAdd с тремя аргументами: ключ, оценка и элемент
            $this->redis->zAdd("feed:$userId", $createdAt, $postId);

            // Если количество постов больше 1000, удаляем старые
            $this->redis->zRemRangeByRank("feed:$userId", 0, -17);
        }

        $output->writeln('Feed data loaded successfully.');

        return Command::SUCCESS;
    }
}