
<?php
require_once 'Renderer.php';
require_once 'PodcastTrack.php';

class PodcastRenderer implements Renderer {
	private $track;
	public function __construct(PodcastTrack $track) {
		$this->track = $track;
	}
	public function render(int $selector): string {
		switch ($selector) {
			case self::LONG:
				return $this->renderLong();
			case self::COMPACT:
			default:
				return $this->renderCompact();
		}
	}
	private function renderCompact(): string {
		$t = $this->track;
		return '<div class="podcast compact">'
			. htmlspecialchars($t->titre) . ' - '
			. htmlspecialchars($t->auteur) . ' '
			. '<audio controls src="' . htmlspecialchars($t->fichier) . '"></audio>'
			. '</div>';
	}
	private function renderLong(): string {
		$t = $this->track;
		$date = $t->date ? htmlspecialchars($t->date) : '';
		$genre = $t->genre ? htmlspecialchars($t->genre) : '';
		$duree = $t->duree ? htmlspecialchars($t->duree) : '';
		return '<div class="podcast long">'
			. '<h2>' . htmlspecialchars($t->titre) . '</h2>'
			. '<ul>'
			. '<li>Auteur : ' . htmlspecialchars($t->auteur) . '</li>'
			. '<li>Date : ' . $date . '</li>'
			. '<li>Genre : ' . $genre . '</li>'
			. '<li>Durée : ' . $duree . ' s</li>'
			. '<li>Fichier : ' . htmlspecialchars($t->fichier) . '</li>'
			. '</ul>'
			. '<audio controls src="' . htmlspecialchars($t->fichier) . '"></audio>'
			. '</div>';
	}
}
