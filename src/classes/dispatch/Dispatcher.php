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
    <title>DeefyApp</title>
     <link rel="stylesheet" href="index.css">
</head>
<body>

    <video autoplay muted loop playsinline id="bg-video">
        <source src="VinyleQuiTourne.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la vidéo de fond.
    </video>

    <div id="overlay">
        <h1>DeefyApp</h1>
        
        <nav>
            <a href="?action=default">Accueil</a> | 
            <a href="?action=add-playlist">Créer une Playlist</a>{$playlistMenu} | 
            <a href="?action=add-user">Inscription</a> | 
            <a href="?action=signin">Connexion</a>{$userMenu}
        </nav>

        <div class="content">
            {$html}
        </div>
    </div>
</body>
</html>
HTML;
        echo $menu;
    }
}