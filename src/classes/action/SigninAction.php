<?php

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class SigninAction extends Action {
    
    public function execute(): string {
        session_start();
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var($_POST["email"] ?? '', FILTER_SANITIZE_EMAIL);
            $password = $_POST["password"] ?? '';
            
            try {
                $user = AuthnProvider::signin($email, $password);
                
                $_SESSION['user'] = $user;
                
                return "<h3>Authentification réussie</h3>
                        <p>Bienvenue " . htmlspecialchars($user['email']) . " !</p>
                        <p>Vous êtes maintenant connecté.</p>
                        <p><a href='?action=default'>Retour à l'accueil</a></p>";
                
            } catch (AuthnException $e) {
                return "<h3>Erreur d'authentification</h3>
                        <p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>
                        <p><a href='?action=signin'>Réessayer</a></p>";
            }
            
        } else {
            $formulaire = <<<HTML
            <h3>Connexion</h3>
            <form method="post" action="?action=signin">
                <label for="email">Email :</label><br>
                <input type="email" id="email" name="email" required><br><br>
                
                <label for="password">Mot de passe :</label><br>
                <input type="password" id="password" name="password" required><br><br>
                
                <button type="submit">Connexion</button>
            </form>
            <br>
            <p><small>Pour tester : user1@mail.com / user1</small></p>
HTML;
            return $formulaire;
        }
    }
}
