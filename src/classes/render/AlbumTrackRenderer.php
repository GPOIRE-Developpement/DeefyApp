<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\render\AudioTrackRenderer;

class AlbumTrackRenderer extends AudioTrackRenderer {
       public function __construct(AlbumTrack $track) {
	       parent::__construct($track);
       }

       protected function renderCompact(): string {
	       $t = $this->track;
	       return '<div class="track compact">'
		       . htmlspecialchars($t->numero) . '. '
		       . htmlspecialchars($t->titre) . ' - '
		       . htmlspecialchars($t->artiste) . ' ('
		       . htmlspecialchars($t->album) . ') '
		       . '<audio controls src="' . htmlspecialchars($t->fichier) . '"></audio>'
		       . '</div>';
       }

       protected function renderLong(): string {
	       $t = $this->track;
	       $annee = $t->annee ? htmlspecialchars($t->annee) : '';
	       $genre = $t->genre ? htmlspecialchars($t->genre) : '';
	       $duree = $t->duree ? htmlspecialchars($t->duree) : '';
	       return '<div class="track long">'
		       . '<h2>' . htmlspecialchars($t->titre) . '</h2>'
		       . '<ul>'
		       . '<li>Artiste : ' . htmlspecialchars($t->artiste) . '</li>'
		       . '<li>Album : ' . htmlspecialchars($t->album) . '</li>'
		       . '<li>Année : ' . $annee . '</li>'
		       . '<li>Numéro : ' . htmlspecialchars($t->numero) . '</li>'
		       . '<li>Genre : ' . $genre . '</li>'
		       . '<li>Durée : ' . $duree . ' s</li>'
		       . '<li>Fichier : ' . htmlspecialchars($t->fichier) . '</li>'
		       . '</ul>'
		       . '<audio controls src="' . htmlspecialchars($t->fichier) . '"></audio>'
		       . '</div>';
       }
}
