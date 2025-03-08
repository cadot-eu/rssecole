<?php

namespace App\Service;

use App\Entity\Article;
use App\Service\EmojiRemover;
use App\Service\RenderArticle;
use Doctrine\ORM\EntityManagerInterface;

class GetContentArticle
{
    public function __construct(private RenderArticle $renderArticle, private EmojiRemover $emojiRemover, private EntityManagerInterface $em, private TempsLecture $tempsLecture) {}

    public function get(Article $article, bool $force = false): Article
    {
        if ($article->getContent() == null  || $force) {
            $datas = $this->renderArticle->render($article);
            if ($datas === false) {
                return $article;
            }
            $datasToSave = [
                'titre' => 'title',
                'image' => 'image',
                'siteName' => 'sitename',
                'author' => 'author',
            ];
            foreach ($datasToSave as $field => $value) {
                if (in_array($field, array_keys($datasToSave)) && $value) {
                    $setter = 'set' . ucfirst($field);
                    $getter = 'get' . ucfirst($value);
                    if (method_exists($article, $setter) && method_exists($datas['readability'], $getter)) {
                        $article->$setter($this->emojiRemover->remove($datas['readability']->$getter()));
                    }
                }
            }
            //on enregistre dans $article->setInfos les autres données de readability sauf content
            $article->setContent($datas['content']);
            $article->setLecturemn($this->tempsLecture->get($article->getContent()));
            $this->em->persist($article);
            $this->em->flush();
        }
        return $article;
    }
}
