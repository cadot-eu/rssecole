<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class GenerateChaineFromNodes
{
    public function get(Crawler $crawler): array
    {
        $nodeNamePlus = [
            'img' => 'src',
            'iframe' => 'src',
            'video' => 'src',
            'audio' => 'src',
            'a' => 'href',
            'link' => 'href',
        ];
        $result = [];
        foreach ($crawler as $node) {
            $chaine = '';
            $chaine .= $node->nodeName . $node->getAttribute('class');
            if (isset($nodeNamePlus[$node->nodeName])) {
                $chaine .= $node->getAttribute($nodeNamePlus[$node->nodeName]);
            }
            $result[] = $chaine;
        }
        return $result;
    }
}
