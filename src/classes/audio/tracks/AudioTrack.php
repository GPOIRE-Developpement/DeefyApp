<?php
namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;

class AudioTrack {
	protected $titre;
	protected $auteur;
	protected $date;
	protected $genre;
	protected $duree;
	protected $fichier;

	public function __construct($titre, $fichier) {
		$this->titre = $titre;
		$this->fichier = $fichier;
		$this->auteur = null;
		$this->date = null;
		$this->genre = null;
		$this->duree = null;
	}

	public function __get(string $arg): mixed{
		if (!property_exists($this, $arg)) {
			throw new InvalidPropertyNameException("invalid property : $arg");
		}
		return $this->$arg;
	}

	public function setDuree($duree) {
		if ($duree < 0) {
			throw new InvalidPropertyValueException("invalid property value for duree");
		}
		$this->duree = $duree;
	}

	public function setAuteur($auteur) { $this->auteur = $auteur; }
	public function setDate($date) { $this->date = $date; }
	public function setGenre($genre) { $this->genre = $genre; }

	public function __toString(): string {
		$info = htmlspecialchars($this->titre);
		if ($this->auteur) {
			$info .= " - " . htmlspecialchars($this->auteur);
		}
		if ($this->duree) {
			$info .= " (" . $this->duree . "s)";
		}
		if ($this->genre) {
			$info .= " [" . htmlspecialchars($this->genre) . "]";
		}
		return $info;
	}
}
