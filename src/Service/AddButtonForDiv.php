<?php

namespace App\Service;

use DOMDocument;
use App\Entity\Article;
use Symfony\Component\DomCrawler\Crawler;

class AddButtonForDiv
{
    public function set(Article $article)
    {
        $html = $article->getContent();
        $crawler = new Crawler($html);
        $divs = $crawler->filter('div');
        foreach ($divs as $div) {
            //on ajoute un bouton D au début de chaque div
            $divNode = $div->ownerDocument->importNode($div, true);
            $button = $divNode->ownerDocument->createElement('button');
            $button->setAttribute('type', 'button');
            $button->setAttribute('class', 'buttondiv btn btn-sm btn-primary');
            $button->nodeValue = 'D';
            $divNode->insertBefore($button, $divNode->firstChild);
            $div->parentNode->replaceChild($divNode, $div);
        }
        try {
            $html = $crawler->html();
            $article->setContent($html);
        } catch (\Exception $e) {
        }
        return $article->setContent($html);
    }
}
