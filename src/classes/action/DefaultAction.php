<?php

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;

class DefaultAction extends Action {

    public function execute(): string {
        session_start();
        
        $html = '<h2>Bienvenue sur DeefyApp !</h2>';
        $html .= '<p>Votre gestionnaire de playlists audio personnel.</p>';
        
        // Vérifier si l'utilisateur est connecté
        try {
            $user = AuthnProvider::getSignedInUser();
            $html .= '<p>Connecté en tant que : <strong>' . htmlspecialchars($user['email']) . '</strong></p>';
            
            // Afficher les playlists de l'utilisateur
            $repo = DeefyRepository::getInstance();
            $pdo = $repo->getPdo();
            
            $stmt = $pdo->prepare("
                SELECT p.id, p.nom 
                FROM playlist p
                JOIN user2playlist u2p ON p.id = u2p.id_pl
                WHERE u2p.id_user = ?
                ORDER BY p.nom
            ");
            $stmt->execute([$user['id']]);
            $playlists = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (count($playlists) > 0) {
                $html .= '<h3>Mes playlists :</h3><ul>';
                foreach ($playlists as $pl) {
                    $html .= '<li><a href="?action=display-playlist&id=' . $pl['id'] . '">' 
                          . htmlspecialchars($pl['nom']) . '</a></li>';
                }
                $html .= '</ul>';
            } else {
                $html .= '<p>Vous n\'avez pas encore de playlist.</p>';
            }
            
            $html .= '<h3>Actions :</h3><ul>';
            $html .= '<li><a href="?action=add-playlist">Créer une nouvelle playlist</a></li>';
            $html .= '</ul>';
            
        } catch (AuthnException $e) {
            // Utilisateur non connecté
            $html .= '<h3>Que souhaitez-vous faire ?</h3>';
            $html .= '<ul>';
            $html .= '<li><a href="?action=signin">Se connecter</a></li>';
            $html .= '<li><a href="?action=add-user">S\'inscrire</a></li>';
            $html .= '</ul>';
        }
        
        return $html;
    }
}