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
use App\Service\AddButtonForDiv;

#[Route('/')]
final class ThemeController extends AbstractController
{
    private $em;
    private $themeRepository;
    private $fluxRepository;
    private  $renderArticle;
    private $emojiRemover;
    private $addButtonForDiv;
    public function __construct(EntityManagerInterface $em, ThemeRepository $themeRepository, FluxRepository $fluxRepository, RenderArticle $renderArticle, EmojiRemover $emojiRemover, AddButtonForDiv $addButtonForDiv)
    {
        $this->em = $em;
        $this->themeRepository = $themeRepository;
        $this->fluxRepository = $fluxRepository;
        $this->renderArticle = $renderArticle;
        $this->emojiRemover = $emojiRemover;
        $this->addButtonForDiv = $addButtonForDiv;
    }
    #[Route('admin', name: 'theme', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $themes = $this->themeRepository->findAll();
        return $this->render('theme/index.html.twig', [
            'themes' => $themes
        ]);
    }
    #[Route('/update-themes', name: 'update_themes')]
    public function updateThemes(): TurboStreamResponse
    {
        return $this->render('theme/stream.html.twig', [
            'themes' => $this->themeRepository->findAll()
        ], new TurboStreamResponse());
    }

    #[Route('/ajouter', name: 'ajouterTheme', methods: ['POST'])]
    public function ajouterTheme(Request $request): TurboStreamResponse
    {
        $post = $request->request;
        //on vérifie que le nom n'existe pas
        if ($theme = $this->themeRepository->findOneBy(['nom' => $post->get('nom')])) {
            $this->addFlash('error', 'Ajout dans le thème existant');
        } else {
            $theme = new Theme();
            $theme->setNom($post->get('nom'));
        }
        $tableau = explode(';', $post->get('url'));
        if ($post->get('url') != '')
            foreach ($tableau as $num => $url) {
                //on vérifie que l'on a pas cet url
                if ($this->fluxRepository->findOneBy(['url' => $url])) {
                    $this->addFlash('info', 'Flux' . $url . 'existe deja');
                    break;
                }
                $f = new Flux();
                $f->setUrl($url);
                $theme->addFlux($f);
            }
        $this->em->persist($theme);
        $this->em->flush();
        $this->addFlash('success', 'Nouveau theme ajouté (' . $theme->getNom() . ')');
        return new TurboStreamResponse($this->renderView('theme/stream.html.twig', [
            'themes' => $this->themeRepository->findAll()
        ]));
    }


    #[Route('loadArticles/{theme}', name: 'theme_loadArticles')]
    public function loadArticles(Theme $theme, MessageBusInterface $bus, LoggerInterface $logger)
    {
        try {
            $message = new LoadArticlesMessage($theme->getId());
            $logger->info('Dispatching LoadArticlesMessage', ['themeId' => $theme->getId()]);
            $bus->dispatch($message);
            $this->addFlash('success', 'Le chargement des articles a été lancé en arrière-plan.');
        } catch (\Exception $e) {
            $logger->error('Error dispatching message', ['error' => $e->getMessage()]);
            $this->addFlash('error', 'Erreur lors du lancement du chargement');
        }
        return new TurboStreamResponse($this->renderView('theme/stream.html.twig', [
            'themes' => $this->themeRepository->findAll()
        ]));
    }




    #[Route('/voirArticlesByTheme/{theme}', name: 'voirArticlesByTheme')]
    public function voirArticlesByTheme(Theme $theme): Response
    {
        return $this->render('theme/voirArticlesByTheme.html.twig', [
            'theme' => $theme
        ]);
    }
    //route pour exporter les flux dans un json
    #[Route('/exportThemes', name: 'exportThemes')]
    public function exportThemes(): Response
    {
        $themes = $this->themeRepository->findAll();
        $jsonThemes = [];
        foreach ($themes as $theme) {
            $fluxs = $theme->getFluxs();
            $tabloFlux = [];
            foreach ($fluxs as $flux)
                $tabloFlux[] = $flux->getUrl();
            $jsonThemes[$theme->getNom()] = $tabloFlux;
        }
        $json = json_encode($jsonThemes);
        return new Response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="themes.json"'
        ]);
    }
    //route pour les importer
    #[Route('/importThemes', name: 'importThemes', methods: ['POST'])]
    public function importThemes(Request $request): Response
    {
        //on décode fichier json
        $file = $request->files->get('json');
        $json = json_decode($file->getContent(), true);
        foreach ($json as $nomTheme => $fluxs) {
            if (sizeof($fluxs) > 0) {
                $theme = new Theme();
                $theme->setNom($nomTheme);
                foreach ($fluxs as $flux) {
                    $f = new Flux();
                    $f->setUrl($flux);
                    $theme->addFlux($f);
                }
                $this->em->persist($theme);
                $this->em->flush();
            }
        }
        return $this->redirectToRoute('theme');
    }
}
