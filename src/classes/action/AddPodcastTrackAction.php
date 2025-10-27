<?php

namespace iutnc\deefy\action;

use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;

class AddPodcastTrackAction extends Action {
    public function execute(): string {
        session_start();
        
        // Récupérer l'ID de la playlist depuis le paramètre GET
        $playlistId = filter_input(INPUT_GET, 'playlist_id', FILTER_VALIDATE_INT);
        
        if (!$playlistId) {
            return "<h3>Erreur</h3>
                    <p style='color: red;'>ID de playlist invalide ou manquant</p>
                    <p>Utilisez : ?action=add-track&playlist_id=X</p>
                    <p><a href='?action=default'>Retour à l'accueil</a></p>";
        }
        
        // Vérifier que l'utilisateur a accès à cette playlist
        try {
            Authz::checkPlaylistOwner($playlistId);
        } catch (AuthnException $e) {
            return "<h3>Accès refusé</h3>
                    <p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>
                    <p><a href='?action=signin'>Se connecter</a></p>
                    <p><a href='?action=default'>Retour à l'accueil</a></p>";
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $titre = filter_var($_POST["titre"] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $auteur = filter_var($_POST["auteur"] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $date = filter_var($_POST["date"] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $genre = filter_var($_POST["genre"] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $duree = filter_var($_POST["duree"] ?? '', FILTER_VALIDATE_INT);
            
            $titre = trim($titre);
            $auteur = trim($auteur);
            $genre = trim($genre);
            
            if (empty($titre)) {
                return "Erreur : Le titre est obligatoire.";
            }
            
            $fichierNom = '';
            if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
                $nomFichier = $_FILES['fichier']['name'];
                $typeFichier = $_FILES['fichier']['type'];
                $tailleFichier = $_FILES['fichier']['size'];
                
                if (substr($nomFichier, -4) !== '.mp3') {
                    return "Erreur : Seuls les fichiers .mp3 sont autorisés.";
                }
                
                if ($typeFichier !== 'audio/mpeg') {
                    return "Erreur : Type de fichier non autorisé. Seuls les fichiers audio/mpeg sont acceptés.";
                }
                
                if (stripos($nomFichier, '.php') !== false) {
                    return "Erreur : Les fichiers .php ne sont pas autorisés.";
                }
                
                $extension = '.mp3';
                $fichierNom = uniqid('audio_', true) . $extension;
                $cheminDestination = 'audio/' . $fichierNom;
                
                if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $cheminDestination)) {
                    return "Erreur : Impossible de sauvegarder le fichier audio.";
                }
            } else {
                return "Erreur : Vous devez uploader un fichier audio .mp3.";
            }
            
            $track = new PodcastTrack($titre, $fichierNom);
            
            if (!empty($auteur)) {
                $track->setAuteur($auteur);
            }
            if (!empty($date)) {
                // Vérifier que la date est au bon format YYYY-MM-DD
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $track->setDate($date);
                }
            }
            if (!empty($genre)) {
                $track->setGenre($genre);
            }
            if ($duree !== false && $duree > 0) {
                $track->setDuree($duree);
            }
            
            // Sauvegarder la piste en base de données
            $repo = DeefyRepository::getInstance();
            $trackId = $repo->saveTrack($track);
            
            // Ajouter la piste à la playlist
            $repo->addTrackToPlaylist($playlistId, $trackId);
            
            $_SESSION['message'] = "Piste \"" . htmlspecialchars($titre) . "\" ajoutée avec succès !";
            
            header('Location: ?action=display-playlist&id=' . $playlistId);
            exit();
            
        } else if($_SERVER['REQUEST_METHOD'] == 'GET') {
            
            $formulaire = <<<HTML
            <h3>Ajouter une piste podcast</h3>
            <form method="post" action="?action=add-track&playlist_id={$playlistId}" enctype="multipart/form-data">
                <label for="titre">Titre * :</label><br>
                <input type="text" id="titre" name="titre" placeholder="Titre de la piste" required><br><br>
                
                <label for="fichier">Fichier audio * (format .mp3) :</label><br>
                <input type="file" id="fichier" name="fichier" accept=".mp3,audio/mpeg" required><br><br>
                
                <label for="auteur">Auteur :</label><br>
                <input type="text" id="auteur" name="auteur" placeholder="Nom de l'auteur"><br><br>
                
                <label for="date">Date de publication :</label><br>
                <input type="date" id="date" name="date"><br><br>
                
                <label for="genre">Genre :</label><br>
                <input type="text" id="genre" name="genre" placeholder="Podcast, Interview, etc."><br><br>
                
                <label for="duree">Durée (en secondes) :</label><br>
                <input type="number" id="duree" name="duree" placeholder="180" min="1"><br><br>
                
                <button type="submit">Ajouter la piste</button>
            </form>
            <br>
            <a href="?action=display-playlist&id={$playlistId}">Retour à la playlist</a>
HTML;
            return $formulaire;
        } else {
            return "Une erreur est survenue !";
        }
    }
}