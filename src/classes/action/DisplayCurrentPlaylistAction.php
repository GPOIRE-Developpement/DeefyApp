<?php

namespace iutnc\deefy\action;

use iutnc\deefy\auth\Authz;
use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\render\AudioListRenderer;

class DisplayCurrentPlaylistAction extends Action {
    public function execute(): string {
        session_start();
        
        // Vérifier qu'une playlist courante existe en session
        if (!isset($_SESSION['current_playlist']) || !isset($_SESSION['current_playlist_id'])) {
            return "<h3>Aucune playlist courante</h3>
                    <p>Aucune playlist n'est actuellement sélectionnée.</p>
                    <p><a href='?action=default'>Voir mes playlists</a></p>";
        }
        
        $playlistId = $_SESSION['current_playlist_id'];
        $playlist = $_SESSION['current_playlist'];
        
        try {
            // Vérifier que l'utilisateur a toujours accès à cette playlist
            Authz::checkPlaylistOwner($playlistId);
            
            // Afficher la playlist avec le renderer
            $renderer = new AudioListRenderer($playlist);
            $result = "<h2>Playlist courante : " . htmlspecialchars($playlist->nom) . "</h2>";
            
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
            // Si l'utilisateur n'a plus accès, supprimer la playlist courante
            unset($_SESSION['current_playlist']);
            unset($_SESSION['current_playlist_id']);
            
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
