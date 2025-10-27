<?php

namespace iutnc\deefy\action;

use iutnc\deefy\auth\Authz;
use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;

class DisplayPlaylistAction extends Action {
    public function execute(): string {
        session_start();
        
        // Récupérer l'ID de la playlist depuis le paramètre GET
        $playlistId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$playlistId) {
            return "<h3>Erreur</h3>
                    <p style='color: red;'>ID de playlist invalide ou manquant</p>
                    <p>Utilisez : ?action=display-playlist&id=X</p>
                    <p><a href='?action=default'>Retour à l'accueil</a></p>";
        }
        
        try {
            // Contrôle d'accès : vérifier que l'utilisateur est propriétaire ou admin
            Authz::checkPlaylistOwner($playlistId);
            
            // Récupérer la playlist avec ses pistes
            $repo = DeefyRepository::getInstance();
            $playlist = $repo->findPlaylistById($playlistId);
            
            if (!$playlist) {
                return "<h3>Erreur</h3>
                        <p style='color: red;'>Playlist introuvable</p>
                        <p><a href='?action=default'>Retour à l'accueil</a></p>";
            }
            
            // Afficher la playlist avec le renderer
            $renderer = new AudioListRenderer($playlist);
            $result = "<h2>Playlist : " . htmlspecialchars($playlist->nom) . "</h2>";
            
            if(isset($_SESSION['message'])){
                $result .= '<div style="color: green; font-weight: bold; margin-bottom: 10px;">' 
                        . htmlspecialchars($_SESSION['message']) . '</div>';
                unset($_SESSION['message']);
            }
            
            $result .= $renderer->render(2);
            $result .= '<br><br>';
            $result .= '<p><a href="?action=add-track&playlist_id=' . $playlistId . '">Ajouter une piste à cette playlist</a></p>';
            $result .= '<p><a href="?action=default">Retour à l\'accueil</a></p>';
            
            return $result;
            
        } catch (AuthnException $e) {
            return "<h3>Accès refusé</h3>
                    <p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>
                    <p><a href='?action=signin'>Se connecter</a></p>
                    <p><a href='?action=default'>Retour à l'accueil</a></p>";
        } catch (\Exception $e) {
            return "<h3>Erreur</h3>
                    <p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>
                    <p><a href='?action=default'>Retour à l'accueil</a></p>";
        }
    }
}