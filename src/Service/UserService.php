<?php

namespace App\Service;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Security\User\User;
use PDO;

class UserService
{
    private UserPasswordHasherInterface $passwordHasher;
    private DatabaseService $databaseService;

    public function __construct(UserPasswordHasherInterface $passwordHasher, DatabaseService $databaseService)
    {
        $this->passwordHasher = $passwordHasher;
        $this->databaseService = $databaseService;
    }

    public function registerUser($username, $plainPassword, $roles)
    {
        $hashedPassword = $this->passwordHasher->hashPassword(
            new User($username, '', $roles),
            $plainPassword
        );

        $sql = 'INSERT INTO users (username, password, roles) VALUES (:username, :password, :roles)';
        $parameters = [
            'username' => $username,
            'password' => $hashedPassword,
            'roles' => implode(',', $roles),
        ];

        $this->databaseService->execute($sql, $parameters);
    }
}