<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Theme;
use App\Entity\Flux;
use App\Repository\FluxRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\LoadArticlesMessage;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\UX\Turbo\TurboStreamResponse;
use Psr\Log\LoggerInterface;
use App\Entity\Article;
use App\Service\RenderArticle;
use App\Service\EmojiRemover;
use App\Entity\Marque;
use App\Repository\MarqueRepository;
use App\Entity\Question;
use Symfony\Component\DomCrawler\Crawler;
use App\Entity\Pub;
use App\Repository\PubRepository;
use App\Service\GetContentArticle;
use App\Service\GenerateChaineFromNodes;
use App\Repository\ArticleRepository;
use App\Service\LoadArticles;

#[Route('/')]
final class FluxController extends AbstractController
{
    private $em;
    private $themeRepository;
    private $fluxRepository;
    private  $renderArticle;
    private $emojiRemover;
    public function __construct(EntityManagerInterface $em, ThemeRepository $themeRepository, FluxRepository $fluxRepository, RenderArticle $renderArticle, EmojiRemover $emojiRemover)
    {
        $this->em = $em;
        $this->themeRepository = $themeRepository;
        $this->fluxRepository = $fluxRepository;
        $this->renderArticle = $renderArticle;
        $this->emojiRemover = $emojiRemover;
    }
    #[Route('/deplacer/{flux}/{theme}', name: 'deplacer_flux')]
    public function deplacerFlux(Flux $flux, Theme $theme = null): TurboStreamResponse
    {
        $flux->getTheme()->removeFlux($flux);
        $theme->addFlux($flux);
        $this->em->persist($flux);
        $this->em->flush();
        $this->addFlash('success', 'Flux deplacer vers ' . $theme->getNom());
        return new TurboStreamResponse($this->renderView('theme/stream.html.twig', [
            'themes' => $this->themeRepository->findAll()
        ]));
    }


    #[Route('loadArticlesFlux/{flux}', name: 'loadArticlesFlux')]
    public function loadArticlesFlux(Flux $flux, Request $request, ArticleRepository $articleRepository, LoadArticles $loadArticles): TurboStreamResponse|Response
    {
        $loader = new LoadArticles();
        $articles = $loader->load($flux->getUrl());
        if ($articles == false) {
            $this->addFlash('error', 'Ce flux ne contient aucun article');
        } else {
            $count = 0;
            foreach ($articles as $index => $article) {
                if ($articleRepository->findOneBy(['url' => $article->getUrl()]) == null) {
                    $flux->addArticle($article);
                    $count++;
                }
            }
            $this->em->persist($flux);
            $this->em->flush();
            $this->addFlash('success', $count . ' nouveaux articles');
        }
        return new TurboStreamResponse($this->renderView('theme/stream.html.twig', [
            'themes' => $this->themeRepository->findAll()
        ]));
    }


    #[Route('/supprimer_flux/{flux}', name: 'supprimerFlux')]
    public function delete(Flux $flux, Request $request): TurboStreamResponse
    {
        $this->em->remove($flux);
        $this->em->flush();
        $this->addFlash('success', 'Flux supprimé');
        return new TurboStreamResponse($this->renderView('theme/stream.html.twig', [
            'themes' => $this->themeRepository->findAll()
        ]));
    }

    #[Route('/voirArticlesByFlux/{flux}', name: 'voirArticlesByFlux')]
    public function voirArticlesByFlux(Flux $flux, GetContentArticle $get): Response
    {
        //on charge les articles
        foreach ($flux->getArticles() as $article) {
            $article = $get->get($article);
        }
        return $this->render('theme/voirArticlesByFlux.html.twig', [
            'flux' => $flux
        ]);
    }

    #[Route('/FluxPub/{id}', name: 'FluxPub')]
    public function FluxPub(Request $request, PubRepository $pubRepository, Article $article, GenerateChaineFromNodes $generate): Response
    {
        $flux = $article->getFlux();
        $data = json_decode($request->getContent(), true);
        $selection = $data['selection'] ?? null;
        $old = $pubRepository->findOneBy(['html' => $selection, 'flux' => $flux]);
        if (!$old) {
            //on vérifie que l'on a pas une marque avec ce texte
            $pub = new Pub();
            $pub->setHtml($selection);
            //on fait un crawler
            $crawler = new Crawler($selection);
            //on boucle sur les enfants du crawler
            $pub->setChaine($generate->get($crawler->filter('div,p,iframe,span,a,img,video,source')));
            $flux->addPub($pub);
            $this->em->persist($flux);
            $this->em->flush();
            $this->addFlash('success', 'Pub ajoutée');
        } else {
            $this->addFlash('error', 'Cette pub existe deja');
        }
        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }
    #[Route('/FluxBas/{id}', name: 'FluxBas')]
    public function FluxBas(Request $request, Article $article): Response
    {
        $flux = $article->getFlux();
        $data = json_decode($request->getContent(), true);
        $texte = $data['selection'] ?? null;
        $flux->setBasPub($texte);
        $this->em->persist($flux);
        $this->em->flush();
        $this->addFlash('success', 'Bas pub ajoutée (' . $flux->getBasPub() . ')');
        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }
    #[Route('/viderBasPub/{id}', name: 'viderBasPub')]
    public function viderBasPub(Article $article): Response
    {
        $flux = $article->getFlux();
        $flux->setBasPub(null);
        $this->addFlash('success', 'success le bas pub est supprimée');
        $this->em->persist($flux);
        $this->em->flush();
        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }
    #[Route('/viderPub/{id}', name: 'viderPubs')]
    public function vider(Article $article): Response
    {
        $flux = $article->getFlux();
        $flux->removePubs();
        $this->addFlash('success', 'success les pubs sont supprimées');
        $this->em->persist($flux);
        $this->em->flush();
        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }
    //on supprime la dernière pub
    #[Route('/viderDernierePub/{id}', name: 'viderDernierePub')]
    public function viderDernierePub(Article $article): Response
    {
        $flux = $article->getFlux();
        $flux->removeLastPub();
        $this->addFlash('success', 'success la derniere pub est supprimée');
        $this->em->persist($flux);
        $this->em->flush();
        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }
    #[Route('/fluxLu/{id}', name: 'fluxLu')]
    public function fluxLu(Flux $flux): Response
    {
        foreach ($flux->getArticles() as $article) {
            if ($article->getPriorite() == 0) {
                $article->setImage(null);
                $article->setContent(null);
                $article->setAuthor(null);
                $article->setInfos(null);
                $article->setEtat('rejeté');
            }
        }
        $this->addFlash('success', 'success le flux est marqué comme lu');
        $this->em->persist($flux);
        $this->em->flush();
        return $this->redirectToRoute('theme');
    }
}
