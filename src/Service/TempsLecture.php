<?php

namespace App\Service;

use DOMDocument;
use App\Entity\Article;

class TempsLecture
{
    static function get(string $content, int $vitesse = 200): int
    {
        $content = strip_tags($content);
        $wordCount = str_word_count($content);
        $readingTime = ceil($wordCount / $vitesse);
        return $readingTime;
    }
}
