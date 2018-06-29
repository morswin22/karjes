<?php

require_once "parsedown.php";

class ParsedownExtended extends Parsedown {

    private $regex = '/{([-:.\/_a-zA-Z\d][^{}]+)}/m';

    public function textExtended($text) {
        preg_match_all($this->regex, $text, $matches);
        if (isset($matches[1])) {
            foreach($matches[1] as $id => $match) {
                $links = explode(',',$match);
                foreach($links as $linkId => $link) {
                    $links[$linkId] = trim($link);
                }
                $matchHTML = '<div id="carousel'.$id.'" class="carousel slide w-75 mx-auto" data-ride="carousel">';
                $matchHTML.= '<ol class="carousel-indicators">';
                foreach($links as $linkId => $link) {
                    if ($linkId == 0) {$e = ' class="active"';} else {$e = '';}
                    $matchHTML.= '<li data-target="#carousel'.$id.'" data-slide-to="0"'.$e.'></li>';
                }
                $matchHTML.= '</ol>';
                $matchHTML.= '<div class="carousel-inner">';
                foreach($links as $linkId => $link) {
                    if ($linkId == 0) {$e = ' active';} else {$e = '';}
                    $matchHTML.= '<div class="carousel-item'.$e.'">';
                    $matchHTML.= '<img class="d-block w-100 mx-0" src="'.$link.'" alt="#' . ($linkId + 1) . ' slide">';
                    $matchHTML.= '</div>';
                }
                $matchHTML.= '</div>';
                $matchHTML.= '<a class="carousel-control-prev" href="#carousel'.$id.'" role="button" data-slide="prev">';
                $matchHTML.= '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
                $matchHTML.= '<span class="sr-only">Previous</span>';
                $matchHTML.= '</a>';
                $matchHTML.= '<a class="carousel-control-next" href="#carousel'.$id.'" role="button" data-slide="next">';
                $matchHTML.= '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
                $matchHTML.= '<span class="sr-only">Next</span>';
                $matchHTML.= '</a>';
                $matchHTML.= '</div>';
                $text = preg_replace($this->regex,$matchHTML,$text,1);
            }
        }
        return $this->text($text);
    }

}

?>