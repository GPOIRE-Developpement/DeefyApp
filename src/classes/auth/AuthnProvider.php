<?php

namespace iutnc\deefy\auth;

use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;

class AuthnProvider {

    public static function signin(string $email, string $password): array
    {
        $repo = DeefyRepository::getInstance();
        $pdo = $repo->getPdo();
        
        $stmt = $pdo->prepare("SELECT id, email, passwd, role FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new AuthnException("Identifiants invalides");
        }
        
        if (!password_verify($password, $user['passwd'])) {
            throw new AuthnException("Identifiants invalides");
        }
        
        return [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
    }

    public static function register(string $email, string $password): void
    {
        if (strlen($password) < 10) {
            throw new AuthnException("Le mot de passe doit contenir au moins 10 caractères");
        }
        
        $repo = DeefyRepository::getInstance();
        $pdo = $repo->getPdo();
        
        $stmt = $pdo->prepare("SELECT id FROM User WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new AuthnException("Un compte existe déjà avec cet email");
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $pdo->prepare("INSERT INTO User (email, passwd, role) VALUES (?, ?, 1)");
        $stmt->execute([$email, $hashedPassword]);
    }
    
    public static function getSignedInUser(): array
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION['user'])) {
            throw new AuthnException("Aucun utilisateur authentifié");
        }
        
        return $_SESSION['user'];
    }
}