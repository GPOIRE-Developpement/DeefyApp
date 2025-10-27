<?php
namespace iutnc\deefy\audio\lists;

use iutnc\deefy\exception\InvalidPropertyNameException;

class Album extends AudioList {
    protected $artiste;
    protected $dateSortie;

    public function __construct($nom, $pistes, $artiste = null, $dateSortie = null) {
        if (!is_array($pistes) || count($pistes) === 0) {
            throw new \Exception("Un album doit avoir une liste de pistes non vide");
        }
        parent::__construct($nom, $pistes);
        $this->artiste = $artiste;
        $this->dateSortie = $dateSortie;
    }

    public function setArtiste($artiste) { $this->artiste = $artiste; }
    public function setDateSortie($date) { $this->dateSortie = $date; }

    public function __get($attr) {
        if (!property_exists($this, $attr)) {
            throw new InvalidPropertyNameException("invalid property : $attr");
        }
        return $this->$attr;
    }
}
