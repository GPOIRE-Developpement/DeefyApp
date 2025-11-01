<?php

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action\DefaultAction;
use iutnc\deefy\action\DisplayPlaylistAction;
use iutnc\deefy\action\DisplayCurrentPlaylistAction;
use iutnc\deefy\action\AddPlaylistAction;
use iutnc\deefy\action\AddPodcastTrackAction;
use iutnc\deefy\action\AddUserAction;
use iutnc\deefy\action\SigninAction;
use iutnc\deefy\action\LogoutAction;

class Dispatcher {
    protected $action;

    public function __construct($a){
        $this->action = $a;
    }

    public function run():void {
        $actions = array(
            "default" => DefaultAction::class,
            "playlist" => DisplayCurrentPlaylistAction::class,
            "display-playlist" => DisplayPlaylistAction::class,
            "display-current-playlist" => DisplayCurrentPlaylistAction::class,
            "add-playlist" => AddPlaylistAction::class,
            "add-track" => AddPodcastTrackAction::class,
            "add-user" => AddUserAction::class,
            "signin" => SigninAction::class,
            "logout" => LogoutAction::class
        );

        if(isset($this->action) && isset($actions[$this->action])){
            $a = $actions[$this->action];
            $objA = new $a();
            $this->renderPage($objA->execute());
        }else{
            $this->renderPage((new DefaultAction())->execute());
        }
    }

    private function renderPage(string $html){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userMenu = '';
        $playlistMenu = '';
        
        if (isset($_SESSION['user'])) {
            $userMenu = ' | <a href="?action=logout">Se déconnecter</a>';
        }
        
        // Ajouter le lien vers la playlist courante si elle existe
        if (isset($_SESSION['current_playlist'])) {
            $playlistMenu = ' | <a href="?action=playlist">Playlist courante</a>';
        }
        
        $menu = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeefyApp - Gestionnaire de Playlists</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

    <video autoplay muted loop playsinline id="bg-video">
        <source src="VinyleQuiTourne.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la vidéo de fond.
    </video>

    <div id="overlay">
        <h1>🎵 DeefyApp</h1>
        
        <nav>
            <a href="?action=default">🏠 Accueil</a>
            <a href="?action=add-playlist">➕ Créer une Playlist</a>{$playlistMenu}
            <a href="?action=add-user">👤 Inscription</a>
            <a href="?action=signin">🔐 Connexion</a>{$userMenu}
        </nav>

        <div class="content">
            {$html}
        </div>
    </div>
    
    <script>
        // Gestion du lecteur audio personnalisé
        function togglePlay(audioId) {
            const audio = document.getElementById(audioId);
            const btn = event.target;
            
            if (audio.paused) {
                audio.play();
                btn.textContent = '⏸';
            } else {
                audio.pause();
                btn.textContent = '▶';
            }
        }
        
        function toggleMute(audioId) {
            const audio = document.getElementById(audioId);
            const btn = event.target;
            
            audio.muted = !audio.muted;
            btn.textContent = audio.muted ? '🔇' : '🔊';
        }
        
        function changeVolume(audioId, value) {
            const audio = document.getElementById(audioId);
            audio.volume = value / 100;
            
            // Mettre à jour la couleur du slider
            const slider = event.target;
            slider.style.setProperty('--volume-percent', value + '%');
        }
        
        function seek(event, audioId) {
            const audio = document.getElementById(audioId);
            const progressBar = event.currentTarget;
            const clickX = event.offsetX;
            const width = progressBar.offsetWidth;
            const duration = audio.duration;
            
            audio.currentTime = (clickX / width) * duration;
        }
        
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
        
        // Initialiser tous les lecteurs audio
        document.addEventListener('DOMContentLoaded', function() {
            const audios = document.querySelectorAll('.custom-audio-player audio');
            
            audios.forEach(audio => {
                const audioId = audio.id;
                
                // Mettre à jour le temps total quand les métadonnées sont chargées
                audio.addEventListener('loadedmetadata', function() {
                    const totalElement = document.getElementById('total-' + audioId);
                    if (totalElement) {
                        totalElement.textContent = formatTime(audio.duration);
                    }
                });
                
                // Mettre à jour la progression pendant la lecture
                audio.addEventListener('timeupdate', function() {
                    const currentElement = document.getElementById('current-' + audioId);
                    const progressElement = document.getElementById('progress-' + audioId);
                    
                    if (currentElement) {
                        currentElement.textContent = formatTime(audio.currentTime);
                    }
                    
                    if (progressElement && audio.duration) {
                        const percent = (audio.currentTime / audio.duration) * 100;
                        progressElement.style.width = percent + '%';
                    }
                });
                
                // Réinitialiser le bouton play à la fin
                audio.addEventListener('ended', function() {
                    const btn = audio.parentElement.querySelector('.play-btn');
                    if (btn) {
                        btn.textContent = '▶';
                    }
                });
            });
        });
    </script>
</body>
</html>
HTML;
        echo $menu;
    }
}