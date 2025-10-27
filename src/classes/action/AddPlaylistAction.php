<?php

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;

class AddPlaylistAction extends Action {
    public function execute(): string {
        session_start();
        
        // Vérifier que l'utilisateur est connecté
        try {
            $user = AuthnProvider::getSignedInUser();
        } catch (AuthnException $e) {
            return "<h3>Accès refusé</h3>
                    <p style='color: red;'>Vous devez être connecté pour créer une playlist.</p>
                    <p><a href='?action=signin'>Se connecter</a></p>
                    <p><a href='?action=default'>Retour à l'accueil</a></p>";
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST["playlist"] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $name = trim($name);
            
            if (empty($name)) {
                return "<h3>Erreur</h3>
                        <p style='color: red;'>Le nom de la playlist ne peut pas être vide.</p>
                        <p><a href='?action=add-playlist'>Réessayer</a></p>";
            }
            
            // Créer la playlist
            $playlist = new Playlist($name);
            
            // Sauvegarder en base de données
            $repo = DeefyRepository::getInstance();
            $playlistId = $repo->saveEmptyPlaylist($playlist);
            
            // Associer la playlist à l'utilisateur connecté
            $pdo = $repo->getPdo();
            $stmt = $pdo->prepare("INSERT INTO user2playlist (id_user, id_pl) VALUES (?, ?)");
            $stmt->execute([$user['id'], $playlistId]);
            
            // Récupérer la playlist complète depuis la BD pour avoir l'ID
            $playlist = $repo->findPlaylistById($playlistId);
            
            // Stocker comme playlist courante en session
            $_SESSION['current_playlist'] = $playlist;
            $_SESSION['current_playlist_id'] = $playlistId;
            
            $_SESSION['message'] = "Playlist \"" . htmlspecialchars($name) . "\" créée avec succès !";
            
            // Rediriger vers la playlist courante
            header('Location: ?action=playlist');
            exit();
            
        } else if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $formulaire = <<<HTML
            <h3>Créer une nouvelle playlist</h3>
            <form method="post" action="?action=add-playlist">
                <label for="playlist">Nom de votre playlist :</label><br>
                <input type="text" id="playlist" name="playlist" placeholder="Ma super playlist" required><br><br>
                <button type="submit">Créer la playlist</button>
            </form>
            <br>
            <a href="?action=default">Retour à l'accueil</a>
            HTML;
            return $formulaire;
        } else {
            return "Une erreur est survenue !";
        }
    }
}