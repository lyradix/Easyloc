<?php

namespace EasyLoc\Repository;
use Doctrine\DBAL\Connection;
use EasyLoc\Entity\User;

class SecurityRepository
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Authenticates a user by email and password.
     * 
     * @param string $email
     * @param string $password
     * @return array|null Returns user data if authentication successful, null otherwise
     */
    public function login(string $email, string $password): ?array
    {
        try {
            $user = $this->conn->fetchAssociative('SELECT * FROM dbo.[User] WHERE EMAIL = ?', [$email]);
            
            if (!$user) {
                return null;
            }

            if (!isset($user['PASSWORD']) || $user['PASSWORD'] === null) {
                return null;
            }

            if (password_verify($password, $user['PASSWORD'])) {
                return $user;
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return null;
        }
    }
}