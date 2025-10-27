<?php
namespace iutnc\deefy\audio\lists;

use iutnc\deefy\exception\InvalidPropertyNameException;

class Playlist extends AudioList {
    public function addTrack($track) {
        foreach ($this->pistes as $p) {
            if ($p === $track) return;
        }
        $this->pistes[] = $track;
        $this->nbPistes++;
        $this->dureeTotale += $track->duree ?? 0;
    }

    public function removeTrack($index) {
        if (isset($this->pistes[$index])) {
            $this->dureeTotale -= $this->pistes[$index]->duree ?? 0;
            array_splice($this->pistes, $index, 1);
            $this->nbPistes = count($this->pistes);
        }
    }

    public function addTracks($tracks) {
        foreach ($tracks as $track) {
            $found = false;
            foreach ($this->pistes as $p) {
                if ($p === $track) { $found = true; break; }
            }
            if (!$found) {
                $this->pistes[] = $track;
                $this->nbPistes++;
                $this->dureeTotale += $track->duree ?? 0;
            }
        }
    }

    public function __get($attr) {
        if (!property_exists($this, $attr)) {
            throw new InvalidPropertyNameException("invalid property : $attr");
        }
        return $this->$attr;
    }
}
