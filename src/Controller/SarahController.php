<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\GenerateChaineFromNodes;
use App\Service\GetContentArticle;
use App\Entity\Article;
use App\Entity\Marque;
use Symfony\Component\DomCrawler\Crawler;
use App\Service\AddReperes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\Turbo\TurboStreamResponse;
use App\Entity\Question;

final class SarahController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/', name: 'app_sarah')]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('sarah/index.html.twig', [
            'articles' => $articleRepository->findByPrioriteNonFinis(),
            'articlesFinis' => $articleRepository->findByPrioriteFinis(),
        ]);
    }
    #[Route('/lireArticle/{id}', name: 'lireArticle')]
    public function lireArticle(Article $article, GenerateChaineFromNodes $generate, GetContentArticle $getContent, AddReperes $AddReperes): Response
    {
        $article = $getContent->get($article);
        //suppression des pubs
        $crawler = new Crawler($article->getContent());
        foreach ($crawler->filter('div') as $node) {
            $crawlerInt = new Crawler($node->childNodes);
            $tablo = $generate->get($crawlerInt->filter('div,p,iframe,span,a,img,video,source'));
            //on regarde si on a ce tableau dans $article->getPubs()[0]->getChaine()
            foreach ($article->getFlux()->getPubs() as $pub) {
                if ($pub->getChaine() == $tablo) {
                    //on supprime le div
                    $node->parentNode->removeChild($node);
                }
            }
        }

        $article->setContent($crawler->html());
        return $this->render('theme/lireArticle.html.twig', [
            'article' => $AddReperes->set($article, false),
        ]);
    }
  

    //route MarqueFait
    #[Route('/marquefait/{id}', name: 'MarqueFait')]
    public function marquefait(Marque $marque, AddReperes $AddReperes): TurboStreamResponse
    {
        $marque->setEtat(!$marque->isEtat());
        $this->em->persist($marque);
        $this->em->flush();
        return new TurboStreamResponse($this->renderView('theme/streamVoir.html.twig', [
            'article' => $AddReperes->set($marque->getArticle(), false),
        ]));
    }
    #[Route('/questionfait/{id}', name: 'QuestionFait')]
    public function questionfait(Question $question, AddReperes $AddReperes): TurboStreamResponse
    {
        $question->setEtat(!$question->isEtat());
        $this->em->persist($question);
        $this->em->flush();
        return new TurboStreamResponse($this->renderView('theme/streamVoir.html.twig', [
            'article' => $AddReperes->set($question->getArticle(), false),
        ]));
    }
    #[Route('/articlelu/{id}', name: 'ArticleLu')]
    public function articleLu(Article $article, AddReperes $AddReperes): Response
    {
        if($article->getEtat() !== 'lu'){
        $article->setEtat('lu');
        }
        else{
            $article->setEtat('');
        }
        $this->em->persist($article);
        $this->em->flush();
        $this->addFlash('success', 'success l\'article est marquée comme lu');
        return $this->redirectToRoute('app_sarah');
    }
}
