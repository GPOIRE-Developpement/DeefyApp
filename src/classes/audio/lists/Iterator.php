<?php
namespace iutnc\deefy\audio\lists;

interface Iterator {
    public function current();
    public function next();
    public function key();
    public function valid();
    public function rewind();
}
