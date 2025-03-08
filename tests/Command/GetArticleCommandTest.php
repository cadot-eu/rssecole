<?php

namespace App\Tests\Command;

use App\Command\GetArticlesCommand;
use App\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Command\Command;
use App\Entity\Flux;

class GetArticleCommandTest extends WebTestCase
{
    private $commandTester;
    private $em;
    private $theme;
    protected function setUp(): void
    {
        self::bootKernel();

        // Récupération de l'EntityManager
        $this->em = self::getContainer()->get('doctrine')->getManager();
        //on créé un thème
        $this->theme = new Theme();
        $this->theme->setNom('test');
        $flux = new Flux();
        $flux->setUrl('https://www.nogentlerotrou-tourisme.fr/rss');
        $this->theme->addFlux($flux);

        $this->em->persist($this->theme);
        $this->em->flush();
        $command = new GetArticlesCommand($this->em);

        // Configuration de l'application de console et du testeur de commande
        $application = new Application();
        $application->add($command);
        $this->commandTester = new CommandTester($application->find('app:get-articles'));
    }
    public function testBadTheme()
    {
        $this->commandTester->execute(['nomsThemes' => 'ThemeInexistant']);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Le thème ThemeInexistant n\'existe pas', $output);
    }
    public function testGoodThemeAndGetArticles()
    {
        $articles = $this->theme->getFluxs()->get(0)->getArticles();
        $this->commandTester->execute(['nomsThemes' => 'test']);
        $this->assertSame(Command::SUCCESS, $this->commandTester->getStatusCode());
        $afterLoad = $this->theme->getFluxs()->get(0)->getArticles();
        $this->assertEquals($articles, $afterLoad);
    }
}
