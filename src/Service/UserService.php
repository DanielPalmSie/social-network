<?php

namespace App\Service;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Security\User\User;
use PDO;

class UserService
{
    private $passwordHasher;
    private $pdo;

    public function __construct(UserPasswordHasherInterface $passwordHasher, PDO $pdo)
    {
        $this->passwordHasher = $passwordHasher;
        $this->pdo = $pdo;
    }

    public function registerUser($username, $plainPassword, $roles)
    {
        // Hash the plain password
        $hashedPassword = $this->passwordHasher->hashPassword(
            new User($username, '', $roles), // Passing a new User object
            $plainPassword
        );

        // Save $username, $hashedPassword, and $roles to the database
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password, roles) VALUES (:username, :password, :roles)');
        $stmt->execute([
            'username' => $username,
            'password' => $hashedPassword,
            'roles' => implode(',', $roles), // Convert roles array to comma-separated string
        ]);
    }
}