<?php

namespace App\Service;

use DOMDocument;
use App\Entity\Article;
use Symfony\Component\DomCrawler\Crawler;
use App\Entity\Marque;
use App\Entity\Question;

class AddReperes
{
    public function __construct(
        private AddButtonForDiv $addButtonForDiv,
        private AddButtonForP $addButtonForP
    ) {}
    public function set(Article $article, $buttons = true): Article
    {
        if ($buttons == true) {
            $article = $this->addButtonForDiv->set($article);
        };
        $article = $this->addButtonForP->set($article, !$buttons);
        $crawler = new Crawler($article->getContent());

        // Traitement des marques
        foreach ($article->getMarques() as $marque) {
            if ($marque->isEtat() == false) {
                $this->applyMarqueTo($crawler, $marque);
            }
        }

        foreach ($article->getQuestions() as $question) {
            if ($question->isEtat() == false) {
                $this->applyMarqueTo($crawler, $question);
            }
        }
        try {
            $html = $crawler->html();
        } catch (\Exception $e) {
            $html = '';
        }
        //on supprime tout ce qui est après basPub
        //on cherche en partant de la fin getBasPub dans $crawler->html()
        if ($article->getFlux()->getBasPub()) {
            $baspos = strrpos($html, $article->getFlux()->getBasPub());
            if ($baspos !== false) {
                $html = substr($html, 0, $baspos);
            }
        }
        //on compte le nombre de div et on ajoute les </div> manquant en fin
        $crawler = new Crawler($html);
        $divs = $crawler->filter('div');
        $nbdivs = count($divs);
        for ($i = 0; $i < $nbdivs; $i++) {
            $div = $divs->getNode($i);
            if ($div->nextSibling == null) {
                $html .= '</div>';
            }
        }

        $article->setContent($html);


        return $article;
    }

    /**
     * Applique une marque spécifique à un article
     */
    private function applyMarqueTo(Crawler $crawler, $objet): void
    {
        if ($objet instanceof Marque) {
            $marque = $objet;
            $selection = $marque->getSelection();
            $replacementHtml = '<mark id="marque-' . $marque->getId() . '" data-bs-toggle="popover" data-bs-content="' . $marque->getStyle() . '" attr-id="' . $marque->getId() . '" class="bg-' . strtolower($marque->getStyle()) . '">' . $selection . '</mark>';
        } elseif ($objet instanceof Question) {
            $question = $objet;
            $selection = $question->getTexte();
            $replacementHtml = '<mark id="question-' . $question->getId() . '"  attr-id="' . $question->getId() . '" class="bg-question" data-bs-toggle="popover" data-bs-content="' . $question->getQuestion() . '" data-bs-trigger="hover focus">' . $selection . '</mark>';
        }
        $remplaced = false;
        //on vérifie si on marque un paragraphe
        if (substr($selection, 0, 2) == 'P:') {
            $number = substr($selection, 2);
            //on cherche le paragraphe correspondant
            $p = $crawler->filterXPath('//button[@data-number="' . $number . '"]')->getNode(0);
            //on prend le parent et on ajoute la class
            $parent = $p->parentNode;
            if ($objet instanceof Question) {
                $parent->setAttribute('class', 'bg-question ' . $parent->getAttribute('class'));
                $parent->setAttribute('data-bs-toggle', 'popover');
                $parent->setAttribute('data-bs-content', $question->getQuestion());
                $parent->setAttribute('data-bs-trigger', 'hover focus');
                $parent->setAttribute('id', 'question-' . $question->getId());
            } elseif ($objet instanceof Marque) {
                $parent->setAttribute('class', 'bg-' . strtolower($marque->getStyle()) . ' ' . $parent->getAttribute('class'));
                $parent->setAttribute('id', 'marque-' . $marque->getId());
            }
            $remplaced = true;
        } else {
            $crawler->filter('*')->each(function (Crawler $node) use ($selection, $replacementHtml, &$remplaced) {
                if ($remplaced) {
                    return; // Si déjà remplacé, on ignore les nœuds suivants
                }
                $domNode = $node->getNode(0);
                // Parcourir les nœuds texte pour remplacer le contenu
                foreach ($domNode->childNodes as $child) {
                    if ($remplaced) {
                        break; // Si déjà remplacé, on arrête
                    }
                    if ($child->nodeType === XML_TEXT_NODE) {
                        $newContent = preg_replace('/' . preg_quote($selection, '/') . '/', $replacementHtml, $child->nodeValue, 1);
                        if ($newContent !== $child->nodeValue) {
                            // Création d'un fragment HTML pour éviter l'échappement
                            $fragment = $domNode->ownerDocument->createDocumentFragment();
                            libxml_use_internal_errors(true);
                            $fragment->appendXML($newContent);
                            libxml_clear_errors();
                            $domNode->replaceChild($fragment, $child);
                            $remplaced = true;
                            break;
                        }
                    }
                }
            });
        }
    }
}
