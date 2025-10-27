<?php

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action\DefaultAction;
use iutnc\deefy\action\DisplayPlaylistAction;
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
            "playlist" => DisplayPlaylistAction::class,
            "display-playlist" => DisplayPlaylistAction::class,
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
        if (isset($_SESSION['user'])) {
            $userMenu = ' | <a href="?action=logout">Se déconnecter</a>';
        }
        
        $menu = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>DeefyApp</title>
</head>
<body>
    <h1>DeefyApp</h1>
    
    <nav>
        <a href="?action=default">Accueil</a> | 
        <a href="?action=add-playlist">Créer une Playlist</a> | 
        <a href="?action=add-user">Inscription</a> | 
        <a href="?action=signin">Connexion</a>{$userMenu}
    </nav>
    
    <hr>
    
    <div>
        {$html}
    </div>
</body>
</html>
HTML;
        echo $menu;
    }
}