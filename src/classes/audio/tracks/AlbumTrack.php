<?php
namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;

class AlbumTrack extends AudioTrack {
	protected $artiste;
	protected $album;
	protected $annee;
	protected $numero;

	public function __construct($titre, $fichier, $album, $numero) {
		parent::__construct($titre, $fichier);
		$this->album = $album;
		$this->numero = $numero;
		$this->artiste = null;
		$this->annee = null;
	}

	public function __get(string $arg):mixed{
		if (!property_exists($this, $arg)) {
			throw new InvalidPropertyNameException("invalid property : $arg");
		}
		return $this->$arg;
	}

	public function setArtiste($artiste) { $this->artiste = $artiste; }
	public function setAnnee($annee) { $this->annee = $annee; }

	public function __toString(): string {
		$info = htmlspecialchars($this->titre);
		
		if ($this->artiste) {
			$info .= " - " . htmlspecialchars($this->artiste);
		}
		
		if ($this->album) {
			$info .= " [" . htmlspecialchars($this->album);
			if ($this->annee) {
				$info .= " (" . htmlspecialchars($this->annee) . ")";
			}
			$info .= "]";
		}
		
		if ($this->duree) {
			$info .= " (" . $this->duree . "s)";
		}
		
		if ($this->genre) {
			$info .= " {" . htmlspecialchars($this->genre) . "}";
		}
		
		return $info;
	}
}
