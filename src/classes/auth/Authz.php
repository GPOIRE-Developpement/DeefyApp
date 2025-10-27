<?php

namespace iutnc\deefy\auth;

use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;

class Authz {
    
    const ROLE_USER = 1;
    const ROLE_ADMIN = 100;
    
    /**
     * Vérifie que l'utilisateur connecté a le rôle attendu
     * @param int $expectedRole Le rôle minimum requis
     * @throws AuthnException Si l'utilisateur n'a pas le bon rôle
     */
    public static function checkRole(int $expectedRole): void
    {
        $user = AuthnProvider::getSignedInUser();
        
        if ((int)$user['role'] < $expectedRole) {
            throw new AuthnException("Accès refusé : rôle insuffisant");
        }
    }
    
    /**
     * Vérifie que la playlist appartient à l'utilisateur connecté ou que l'utilisateur est admin
     * @param int $playlistId L'ID de la playlist
     * @throws AuthnException Si l'utilisateur n'est pas propriétaire et n'est pas admin
     */
    public static function checkPlaylistOwner(int $playlistId): void
    {
        $user = AuthnProvider::getSignedInUser();
        $userId = (int)$user['id'];
        $userRole = (int)$user['role'];
        
        // Si l'utilisateur est admin, accès autorisé
        if ($userRole === self::ROLE_ADMIN) {
            return;
        }
        
        // Sinon, vérifier que l'utilisateur est propriétaire de la playlist
        $repo = DeefyRepository::getInstance();
        $pdo = $repo->getPdo();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user2playlist WHERE id_user = ? AND id_pl = ?");
        $stmt->execute([$userId, $playlistId]);
        $count = $stmt->fetchColumn();
        
        if ($count === 0) {
            throw new AuthnException("Accès refusé : vous n'êtes pas propriétaire de cette playlist");
        }
    }
}
