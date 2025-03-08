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
use App\Service\GenerateChaineFromNodes;
use App\Service\GetContentArticle;
use App\Service\AddButtonForDiv;
use App\Service\AddButtonForP;
use App\Service\AddReperes;

#[Route('/')]
final class ArticleController extends AbstractController
{
    private $em;
    private $themeRepository;
    private $fluxRepository;
    private  $renderArticle;
    private $emojiRemover;
    private $addButtonForDiv;
    private $addButtonForP;
    public function __construct(EntityManagerInterface $em, ThemeRepository $themeRepository, FluxRepository $fluxRepository, RenderArticle $renderArticle, EmojiRemover $emojiRemover, AddButtonForDiv $addButtonForDiv, AddButtonForP $addButtonForP)
    {
        $this->em = $em;
        $this->themeRepository = $themeRepository;
        $this->fluxRepository = $fluxRepository;
        $this->renderArticle = $renderArticle;
        $this->emojiRemover = $emojiRemover;
        $this->addButtonForDiv = $addButtonForDiv;
        $this->addButtonForP = $addButtonForP;
    }

    #[Route('/viderArticles/{id}', name: 'viderArticles')]
    public function viderArticles(String $id): TurboStreamResponse
    {
        $theme = $this->themeRepository->findOneBy(['id' => $id]);
        $base = count($theme->getArticlesSansPriorite());
        foreach ($theme->getFluxs() as $flux) {
            $flux->removeArticlesSansPriorite();
            $this->em->persist($flux);
        }
        $fin = count($theme->getArticlesSansPriorite());
        $this->em->flush();
        $this->addFlash('success ', ($base - $fin) . ' articles sont été supprimés');
        return new TurboStreamResponse($this->renderView('theme/stream.html.twig', [
            'themes' => $this->themeRepository->findAll()
        ]));
    }

    #[Route('/voirArticle/{id}', name: 'voirArticle')]
    public function voirArticle(Article $article, GenerateChaineFromNodes $generate, GetContentArticle $getContent, AddReperes $AddReperes): Response
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
        try {
            $html = $crawler->html();
            $article->setContent($html);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression des pubs');
        }





        return $this->render('theme/voirArticle.html.twig', [
            'article' => $AddReperes->set($article),
        ]);
    }

    #[Route('/ArticlePriorite/{id}/{priorite}', name: 'ArticlePriorite')]
    public function ArticlePriorite(Request $request, MarqueRepository $marqueRepository, Article $article, int $priorite): Response
    {
        $article->setPriorite($priorite);
        $this->em->persist($article);
        $this->em->flush();

        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }
    #[Route('/garder/{id}', name: 'ArticleGarder')]
    public function ArticleGarder(Request $request, MarqueRepository $marqueRepository, Article $article): Response
    {
        $article->setPriorite(5);
        $this->em->persist($article);
        $this->em->flush();

        return $this->redirectToRoute('voirArticlesByFlux', ['flux' => $article->getFlux()->getId()]);
    }
    #[Route('/ArticleMarque/{id}/{mark}', name: 'ArticleMarque')]
    public function ArticleMarque(Request $request, MarqueRepository $marqueRepository, Article $article, string $mark, AddReperes $AddReperes): TurboStreamResponse
    {
        $data = json_decode($request->getContent(), true);
        $selection = $data['selection'] ?? null;

        $old = $marqueRepository->findOneBy(['style' => $mark, 'selection' => $selection, 'article' => $article]);
        if (!$old) {
            //on vérifie que l'on a pas une marque avec ce texte
            $marque = new Marque();
            $marque->setStyle($mark);
            $marque->setSelection($selection);
            $article->addMarque($marque);
            if (!$article->getPriorite())
                $article->setPriorite(5);
            $this->em->persist($article);
            $this->em->flush();
            $this->addFlash('success', 'Marque ajoutée');
        } else {
            //on remove la marque
            $article->removeMarque($old);
            $this->em->persist($article);
            $this->em->flush();
        }

        return new TurboStreamResponse($this->renderView('theme/streamArticle.html.twig', [
            'article' => $AddReperes->set($article)
        ]));
    }
    #[Route('/Articlequestion/{id}', name: 'ArticleQuestion')]
    public function ArticleQuestion(Request $request, MarqueRepository $marqueRepository, Article $article): Response
    {
        $data = json_decode($request->getContent(), true);
        $selection = $data['selection'] ?? null;
        $questiondata = $data['question'] ?? null;

        $question = new Question();
        $question->setTexte($selection);
        $question->setQuestion($questiondata);
        $article->addQuestion($question);
        if (!$article->getPriorite())
            $article->setPriorite(5);
        $this->em->persist($article);
        $this->em->flush();
        $this->addFlash('success', 'Question ajoutée');
        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }





    #[Route('/vider/{id}/{type}', name: 'vider')]
    public function vider(Article $article, string $type): Response
    {
        $remove = 'remove' . ucfirst($type);
        $article->$remove();
        $this->addFlash('success', $type . ' supprimées');
        $this->em->persist($article);
        $this->em->flush();

        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }

    //suppression d'une marque
    #[Route('/viderMarque/{id}', name: 'viderMarque')]
    public function viderMarque(Marque $marque): Response
    {
        $article = $marque->getArticle();
        $article->removeMarque($marque);
        $this->addFlash('success', 'success la marque est supprimée');
        $this->em->persist($article);
        $this->em->flush();
        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }
    //vider question
    #[Route('/viderQuestion/{id}', name: 'viderQuestion')]
    public function viderQuestion(Question $question): Response
    {
        $article = $question->getArticle();
        $article->removeQuestion($question);
        $this->addFlash('success', 'success la question est supprimée');
        $this->em->persist($article);
        $this->em->flush();
        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }

    #[Route('/addNote/{id}', name: 'addNote')]
    public function addNote(Article $article, Request $request): Response
    {
        $article->setNotes($request->request->get('notes'));
        if (!$article->getPriorite())
            $article->setPriorite(5);
        $this->em->persist($article);
        $this->em->flush();
        $this->addFlash('success', 'Note ajoutée');
        return $this->redirectToRoute('voirArticle', ['id' => $article->getId()]);
    }
}
