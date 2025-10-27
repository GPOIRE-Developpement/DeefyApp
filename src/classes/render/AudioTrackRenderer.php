<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\AudioTrack;

abstract class AudioTrackRenderer implements Renderer {
       protected $track;
       public function __construct(AudioTrack $track) {
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
       abstract protected function renderCompact(): string;
       abstract protected function renderLong(): string;
}
