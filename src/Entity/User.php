<?php

namespace EasyLoc\Entity;

class User
{
    private int $id;
    private string $username;
    private string $email;
    private string $password;
    private string $role;
    // private \DateTime $createdAt;
    // private ?\DateTime $lastLogin;
    // private bool $isActive;

    public function __construct()
    {
        // $this->createdAt = new \DateTime();
        // $this->lastLogin = null;
        // $this->isActive = true;
        $this->role = 'ROLE_USER';
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    // public function getCreatedAt(): \DateTime
    // {
    //     return $this->createdAt;
    // }

    // public function getLastLogin(): ?\DateTime
    // {
    //     return $this->lastLogin;
    // }

    // public function isActive(): bool
    // {
    //     return $this->isActive;
    // }

    // Setters
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setUsername(string $username): self
    {
        if (strlen($username) < 3) {
            throw new \InvalidArgumentException('Username must be at least 3 characters long');
        }
        $this->username = $username;
        return $this;
    }

    public function setEmail(string $email): self
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        $this->email = $email;
        return $this;
    }

    public function setPassword(string $password): self
    {
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters long');
        }
        
        // Hash the password using PASSWORD_DEFAULT (currently uses bcrypt)
        $this->password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        return $this;
    }

    public function setRole(string $role): self
    {
        $allowedRoles = ['ROLE_USER', 'ROLE_ADMIN'];
        if (!in_array($role, $allowedRoles)) {
            throw new \InvalidArgumentException('Invalid role');
        }
        $this->role = $role;
        return $this;
    }

    // public function setLastLogin(\DateTime $lastLogin = null): self
    // {
    //     $this->lastLogin = $lastLogin;
    //     return $this;
    // }

    // public function setIsActive(bool $isActive): self
    // {
    //     $this->isActive = $isActive;
    //     return $this;
    // }

    // Password verification
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    // Update last login
    // public function updateLastLogin(): self
    // {
    //     $this->lastLogin = new \DateTime();
    //     return $this;
    // }

    // Helper method to check if password needs rehash
    public function passwordNeedsRehash(): bool
    {
        return password_needs_rehash($this->password, PASSWORD_DEFAULT, ['cost' => 12]);
    }
}