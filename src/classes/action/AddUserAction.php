<?php

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class AddUserAction extends Action {
    public function execute(): string {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var($_POST["email"] ?? '', FILTER_SANITIZE_EMAIL);
            $password = $_POST["password"] ?? '';
            $passwordConfirm = $_POST["password_confirm"] ?? '';
            
            $email = trim($email);
            
            if ($password !== $passwordConfirm) {
                return "<h3>Erreur d'inscription</h3>
                        <p style='color: red;'>Les mots de passe ne correspondent pas</p>
                        <p><a href='?action=add-user'>Réessayer</a></p>";
            }
            
            try {
                AuthnProvider::register($email, $password);
                
                return "<h3>Inscription réussie</h3>
                        <p>Votre compte a été créé avec succès !</p>
                        <p>Email : " . htmlspecialchars($email) . "</p>
                        <p><a href='?action=signin'>Se connecter</a></p>";
                
            } catch (AuthnException $e) {
                return "<h3>Erreur d'inscription</h3>
                        <p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>
                        <p><a href='?action=add-user'>Réessayer</a></p>";
            }
            
        } else {
            $formulaire = <<<HTML
            <h3>Inscription</h3>
            <form method="post" action="?action=add-user">
                <label for="email">Email :</label><br>
                <input type="email" id="email" name="email" required><br><br>
                
                <label for="password">Mot de passe (min 10 caractères) :</label><br>
                <input type="password" id="password" name="password" minlength="10" required><br><br>
                
                <label for="password_confirm">Confirmer le mot de passe :</label><br>
                <input type="password" id="password_confirm" name="password_confirm" minlength="10" required><br><br>

                <button type="submit">S'inscrire</button>
            </form>
HTML;
            return $formulaire;
        }
    }
}