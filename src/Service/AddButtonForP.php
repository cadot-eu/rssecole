<?php

namespace App\Service;

use DOMDocument;
use App\Entity\Article;
use Symfony\Component\DomCrawler\Crawler;

class AddButtonForP
{
    public function set(Article $article, $hidden = false)
    {
        $html = $article->getContent();
        $crawler = new Crawler($html);
        $ps = $crawler->filter('p');
        $number = 0;
        foreach ($ps as $p) {
            $number++;
            //on ajoute un bouton D au début de chaque p
            $pNode = $p->ownerDocument->importNode($p, true);
            $button = $pNode->ownerDocument->createElement('button');
            $button->setAttribute('type', 'button');
            $button->setAttribute('class', 'buttonp btn btn-sm btn-primary');
            $button->setAttribute('data-number', $number);
            $button->setAttribute('data-bs-toggle', 'popover');
            $button->setAttribute('data-bs-content', 'P:' . $number);
            $button->setAttribute('data-bs-trigger', 'hover focus');
            if ($hidden) {
                $button->setAttribute('class', 'pourlire');
            }
            $button->nodeValue = 'P';
            $pNode->insertBefore($button, $pNode->firstChild);
            $p->parentNode->replaceChild($pNode, $p);
        }
        try {
            $html = $crawler->html();
            $article->setContent($html);
        } catch (\Exception $e) {
        }
        return $article->setContent($html);
    }
}
