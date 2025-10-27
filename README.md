# 🎧 Deefy — Mini-Projet BUT Informatique S3  
> Application web de gestion de playlists audio (inspirée de Spotify/Deezer)

Projet réalisé dans le cadre de la ressource **Développement Web S3** — IUT Nancy-Charlemagne  
Binôme : *[à compléter]*  
Année universitaire : 2024–2025  

---

## 📋 Présentation du projet

**Deefy** est une application web permettant à un utilisateur authentifié de gérer ses playlists audio personnelles :  
- Créer des playlists  
- Ajouter des pistes audio (podcasts)  
- Afficher la playlist courante  
- Gérer son compte utilisateur  

Le projet s’appuie sur les fonctionnalités développées tout au long des TD du module (TD 14–15) et respecte le modèle **MVC** ainsi qu’une politique d’autorisations stricte.

---

## 🚀 Fonctionnalités

| # | Fonctionnalité | Statut | Détails |
|---|-----------------|:------:|---------|
| **1** | Mes playlists | ✅ **Complet** | Liste des playlists de l’utilisateur affichée sur la page d’accueil.<br>Chaque playlist est cliquable et devient la playlist courante. |
| **2** | Ajouter une piste | ✅ **Complet** | Formulaire d’ajout (titre, description, fichier `.mp3`).<br>Enregistrement en BD et association à la playlist courante. |
| **3** | Créer une playlist vide | ✅ **Complet** | Formulaire de création, sauvegarde en BD, association automatique à l’utilisateur.<br>⚙️ Devient la *playlist courante* après création. |
| **4** | Afficher la playlist courante | ⚙️ **Partiel → en cours d’achèvement** | Affichage fonctionnel par ID.<br>À compléter : action dédiée pour afficher la playlist stockée en session. |
| **5** | S’inscrire | ✅ **Complet** | Formulaire d’inscription (`AddUserAction`).<br>Rôle `STANDARD` (1) par défaut. |
| **6** | S’authentifier | ✅ **Complet** | Formulaire de connexion (`SigninAction`).<br>Stockage de l’utilisateur en session avec `password_verify()`. |

---

## 🔐 Politique d’autorisations

| Fonctionnalité | Autorisation requise |
|-----------------|----------------------|
| 1. Mes playlists | Utilisateur authentifié |
| 2. Ajouter une piste | Utilisateur authentifié **propriétaire de la playlist** |
| 3. Créer une playlist | Utilisateur authentifié |
| 4. Afficher la playlist courante | Utilisateur authentifié |
| 5. S’inscrire | Public |
| 6. S’authentifier | Public |

Les contrôles sont réalisés via la classe `Authz` :

```php
Authz::checkPlaylistOwner($playlist, $user);
