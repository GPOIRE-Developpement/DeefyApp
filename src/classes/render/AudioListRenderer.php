<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\audio\tracks\AlbumTrack;

class AudioListRenderer implements Renderer {
    protected $list;
    public function __construct(AudioList $list) {
        $this->list = $list;
    }
    public function render(?int $selector = null): string {
        $html = '<div class="audiolist">';
        $html .= '<h2>' . htmlspecialchars($this->list->nom) . '</h2>';
        
        // Afficher chaque piste avec son lecteur audio
        foreach ($this->list->pistes as $index => $track) {
            $html .= '<div class="audio-track">';
            $html .= '<div class="track-number">#' . ($index + 1) . '</div>';
            $html .= '<div class="track-info">';
            
            // Informations de la piste
            $html .= '<h3 class="track-title">' . htmlspecialchars($track->titre) . '</h3>';
            
            if ($track->auteur) {
                $html .= '<p class="track-artist">🎤 ' . htmlspecialchars($track->auteur) . '</p>';
            }
            
            $details = [];
            if ($track->genre) {
                $details[] = '<span class="badge secondary">' . htmlspecialchars($track->genre) . '</span>';
            }
            if ($track->duree) {
                $minutes = floor($track->duree / 60);
                $secondes = $track->duree % 60;
                $details[] = '<span class="track-duration">⏱️ ' . sprintf('%d:%02d', $minutes, $secondes) . '</span>';
            }
            if ($track->date) {
                $details[] = '<span class="track-date">📅 ' . htmlspecialchars($track->date) . '</span>';
            }
            
            if (!empty($details)) {
                $html .= '<div class="track-details">' . implode(' ', $details) . '</div>';
            }
            
            // Lecteur audio personnalisé avec le bon chemin
            $audioPath = 'audio/' . htmlspecialchars($track->fichier);
            $audioId = 'audio-' . $index;
            $html .= '<div class="custom-audio-player">';
            $html .= '<audio id="' . $audioId . '" preload="metadata">';
            $html .= '<source src="' . $audioPath . '" type="audio/mpeg">';
            $html .= '</audio>';
            
            // Bouton play/pause uniquement
            $html .= '<div class="audio-controls">';
            $html .= '<button class="play-btn" onclick="togglePlay(\'' . $audioId . '\')">▶</button>';
            $html .= '</div>';
            $html .= '</div>';
            
            $html .= '</div>'; // track-info
            $html .= '</div>'; // audio-track
        }
        
        // Résumé de la playlist
        $html .= '<div class="summary">';
        $dureeMinutes = floor($this->list->dureeTotale / 60);
        $dureeSecondes = $this->list->dureeTotale % 60;
        $html .= '<strong>' . $this->list->nbPistes . ' piste(s)</strong> - ';
        $html .= 'Durée totale : <strong>' . sprintf('%d:%02d', $dureeMinutes, $dureeSecondes) . '</strong>';
        $html .= '</div>';
        
        $html .= '</div>'; // audiolist
        return $html;
    }
}
