<?php

namespace src\dao;

class UserDao
{
    private int $id;
    private string $name;
    private string $email;
    private string $password;
    private UserRole $role;

    public function __construct(int $id, string $name, string $email, string $password, string $role)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;

        // Convert string to UserRole
        $this->role = UserRole::fromString($role);
    }

    public static function fromRaw(array $raw): UserDao
    {
        return new UserDao(
            $raw['id'],
            $raw['name'],
            $raw['email'],
            $raw['password'],
            $raw['role'],
        );
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getRole(): UserRole
    {
        return $this->role;
    }
    public function getHashedPassword(): string
    {
        return $this->password;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setRole(string $role): void
    {
        $this->role = UserRole::fromString($role);
    }
}
