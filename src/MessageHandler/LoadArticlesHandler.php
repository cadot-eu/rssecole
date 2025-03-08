<?php

namespace App\MessageHandler;

use App\Message\LoadArticlesMessage;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Event\ArticlesLoadedEvent;
use App\Service\LoadArticles;

#[AsMessageHandler]
class LoadArticlesHandler
{
    private ThemeRepository $themeRepository;
    private EntityManagerInterface $em;
    private EventDispatcherInterface $dispatcher;
    private LoadArticles $loadArticles;

    public function __construct(LoadArticles $loadArticles, ThemeRepository $themeRepository, EntityManagerInterface $em, EventDispatcherInterface $dispatcher)
    {
        $this->themeRepository = $themeRepository;
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->loadArticles = $loadArticles;
    }

    public function __invoke(LoadArticlesMessage $message)
    {
        $theme = $this->themeRepository->find($message->getThemeId());

        if (!$theme) {
            return;
        }

        foreach ($theme->getFluxs() as $flux) {
            $articles = $this->loadArticles->load($flux->getUrl());
            $flux->setArticles($articles);
            $this->em->persist($flux);
            $this->em->flush();
        }

        // Marquer l'opération comme terminée
        $message->setCompletedAt(new \DateTime());  // Définir l'heure de fin

        // Lancer l'événement pour informer qu'on a chargé les articles
        $this->dispatcher->dispatch(new ArticlesLoadedEvent($message->getThemeId()), ArticlesLoadedEvent::NAME);
    }
}
