<?php
namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;

class AudioListRenderer implements Renderer {
    protected $list;
    public function __construct(AudioList $list) {
        $this->list = $list;
    }
    public function render(int $selector = null): string {
        $html = '<div class="audiolist">';
        $html .= '<h2>' . htmlspecialchars($this->list->nom) . '</h2>';
        foreach ($this->list->pistes as $track) {
            if (method_exists($track, '__toString')) {
                $html .= '<div class="track">' . htmlspecialchars($track->__toString()) . '</div>';
            } else {
                $html .= '<div class="track">' . htmlspecialchars(json_encode($track)) . '</div>';
            }
        }
        $html .= '<div class="summary">';
        $html .= $this->list->nbPistes . ' pistes, durée totale : ' . $this->list->dureeTotale . ' s';
        $html .= '</div></div>';
        return $html;
    }
}
