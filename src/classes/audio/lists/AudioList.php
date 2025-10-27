<?php

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\exception\InvalidPropertyNameException;

class AudioList implements \Iterator {
    protected $id;
    protected $nom;
    protected $nbPistes;
    protected $dureeTotale;
    protected $pistes;

    private $position = 0;

    public function __construct($nom, $pistes = []) {
        $this->nom = $nom;
        $this->pistes = $pistes;
        $this->nbPistes = count($pistes);
        $this->dureeTotale = 0;
        $this->id = null;
        foreach ($pistes as $p) {
            $this->dureeTotale += $p->duree ?? 0;
        }
    }

    public function __get($attr) {
        if (!property_exists($this, $attr)) {
            throw new InvalidPropertyNameException("invalid property : $attr");
        }
        return $this->$attr;
    }
    
    public function __set($attr, $value) {
        if (!property_exists($this, $attr)) {
            throw new InvalidPropertyNameException("invalid property : $attr");
        }
        $this->$attr = $value;
    }

    public function rewind(): void {
        $this->position = 0;
    }


    #[\ReturnTypeWillChange]
    public function current() {
        return $this->pistes[$this->position];
    }

    #[\ReturnTypeWillChange]
    public function key() {
        return $this->position;
    }

    public function next(): void {
        ++$this->position;
    }

    public function valid(): bool {
        return isset($this->pistes[$this->position]);
    }
}
