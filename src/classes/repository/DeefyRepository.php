<?php

namespace iutnc\deefy\repository;

use PDO;
use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\PodcastTrack;

class DeefyRepository {
    
    protected static ?array $config = null;
    protected static ?DeefyRepository $instance = null;
    protected ?PDO $pdo = null;

    public static function setConfig(string $file): void
    {
        self::$config = parse_ini_file($file);
    }

    public static function getInstance(): DeefyRepository
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct()
    {
        if (!self::$config) {
            throw new \Exception("Configuration non définie. Appeler setConfig() d'abord.");
        }
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8',
            self::$config['host'],
            self::$config['database']
        );
        $this->pdo = new PDO($dsn, self::$config['user'], self::$config['password']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function findPlaylistById(int $id): ?Playlist
    {
        $stmt = $this->pdo->prepare("SELECT id, nom FROM playlist WHERE id = ?");
        $stmt->execute([$id]);
        $playlistData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$playlistData) {
            return null;
        }
        
        // Créer l'objet Playlist
        $playlist = new Playlist($playlistData['nom']);
        $playlist->id = $playlistData['id'];
        
        // Charger les pistes associées
        $stmt = $this->pdo->prepare("
            SELECT t.* 
            FROM track t 
            JOIN playlist2track p2t ON t.id = p2t.id_track 
            WHERE p2t.id_pl = ? 
            ORDER BY p2t.no_piste_dans_liste
        ");
        $stmt->execute([$id]);
        $tracksData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Créer les objets tracks et les ajouter à la playlist
        foreach ($tracksData as $trackData) {
            $track = $this->createTrackFromData($trackData);
            if ($track) {
                $playlist->addTrack($track);
            }
        }
        
        return $playlist;
    }
    
    private function createTrackFromData(array $data)
    {
        if ($data['type'] === 'P') {
            // PodcastTrack
            $track = new PodcastTrack($data['titre'], $data['filename']);
            if ($data['auteur_podcast']) $track->setAuteur($data['auteur_podcast']);
            if ($data['date_posdcast']) $track->setDate($data['date_posdcast']);
        } else {
            // AlbumTrack
            $track = new \iutnc\deefy\audio\tracks\AlbumTrack(
                $data['titre'], 
                $data['filename'],
                $data['titre_album'] ?? '',
                $data['numero_album'] ?? 0
            );
            if ($data['artiste_album']) $track->setArtiste($data['artiste_album']);
            if ($data['annee_album']) $track->setAnnee($data['annee_album']);
        }
        
        if ($data['genre']) $track->setGenre($data['genre']);
        if ($data['duree']) $track->setDuree($data['duree']);
        
        return $track;
    }

    public function findAllPlaylists(): array
    {
        $stmt = $this->pdo->prepare("SELECT id, nom FROM playlist");
        $stmt->execute();
        $playlists = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $playlists[] = [
                'id' => $row['id'],
                'nom' => $row['nom']
            ];
        }
        
        return $playlists;
    }

    public function saveEmptyPlaylist(Playlist $playlist): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO playlist (nom) VALUES (?)");
        $stmt->execute([$playlist->nom]);
        return $this->pdo->lastInsertId();
    }

    public function saveTrack($track): int
    {
        $type = ($track instanceof PodcastTrack) ? 'P' : 'A';
        
        if ($type === 'P') {
            $stmt = $this->pdo->prepare("INSERT INTO track (titre, genre, duree, filename, type, auteur_podcast, date_posdcast) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $track->titre,
                $track->genre ?? null,
                $track->duree ?? null,
                $track->fichier,
                $type,
                $track->auteur ?? null,
                $track->date ?? null
            ]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO track (titre, genre, duree, filename, type, artiste_album, titre_album, annee_album, numero_album) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $track->titre,
                $track->genre ?? null,
                $track->duree ?? null,
                $track->fichier,
                $type,
                $track->artiste ?? null,
                $track->album ?? null,
                $track->annee ?? null,
                $track->numero ?? null
            ]);
        }
        
        return $this->pdo->lastInsertId();
    }

    public function addTrackToPlaylist(int $playlistId, int $trackId): void
    {
        $stmt = $this->pdo->prepare("SELECT MAX(no_piste_dans_liste) FROM playlist2track WHERE id_pl = ?");
        $stmt->execute([$playlistId]);
        $maxNo = $stmt->fetchColumn() ?: 0;
        
        $stmt = $this->pdo->prepare("INSERT INTO playlist2track (id_pl, id_track, no_piste_dans_liste) VALUES (?, ?, ?)");
        $stmt->execute([$playlistId, $trackId, $maxNo + 1]);
    }

    public function findPlaylistWithTracks(int $playlistId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, nom FROM playlist WHERE id = ?");
        $stmt->execute([$playlistId]);
        $playlistData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$playlistData) {
            return null;
        }
        
        $stmt = $this->pdo->prepare("
            SELECT t.*, p2t.no_piste_dans_liste 
            FROM track t 
            JOIN playlist2track p2t ON t.id = p2t.id_track 
            WHERE p2t.id_pl = ? 
            ORDER BY p2t.no_piste_dans_liste
        ");
        $stmt->execute([$playlistId]);
        $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'playlist' => $playlistData,
            'tracks' => $tracks
        ];
    }

    public function findTrackById(int $trackId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM track WHERE id = ?");
        $stmt->execute([$trackId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}