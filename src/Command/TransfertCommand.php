<?php

namespace App\Command;

use App\Entity\Theme;
use Doctrine\Common\Collections\Expr\Value;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Entity\Flux;
use Doctrine\ORM\EntityManager;
use App\Entity\Article;
use App\Entity\Marque;
use App\Entity\Question;

#[AsCommand(
    name: 'app:transfert',
    description: 'Export data from SQLite to PostgreSQL with detailed verifications.',
)]
class TransfertCommand extends Command
{
    private $sqliteConnection;

    private $postgresEntityManager;


    public function __construct(Connection $sqliteConnection, EntityManager $postgresEntityManager)

    {

        $this->sqliteConnection = $sqliteConnection;

        $this->postgresEntityManager = $postgresEntityManager;

        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int

    {
        $io = new SymfonyStyle($input, $output);
        $themes = $this->sqliteConnection->executeQuery("SELECT * FROM theme");
        foreach ($themes->fetchAllAssociative() as $theme) {
            $newTheme = new Theme();
            $newTheme->setNom($theme['nom']);
            $this->testPersist($newTheme);
            $this->testFlush($newTheme);
            $fluxs = $this->sqliteConnection->executeQuery("SELECT * FROM flux WHERE theme_id = ?", [$theme['id']]);
            foreach ($fluxs->fetchAllAssociative() as $flux) {
                $newFlux = new Flux();
                $newFlux->setUrl($flux['url']);
                $newFlux->setDomaine($flux['domaine']);
                $newFlux->setNom($flux['nom']);
                $newFlux->setBasPub($flux['bas_pub']);
                $articles = $this->sqliteConnection->executeQuery("SELECT * FROM article WHERE flux_id = ?", [$flux['id']]);
                foreach ($articles->fetchAllAssociative() as $article) {
                    $newArticle = new Article();
                    $newArticle->setEtat($article['etat']);
                    $newArticle->setPriorite($article['priorite']);
                    $newArticle->setInfos(json_decode($article['infos'], true));
                    $newArticle->setSitename($article['sitename']);
                    $newArticle->setAuthor($article['author']);
                    $newArticle->setContent($article['content']);
                    $newArticle->setImage($article['image']);
                    $newArticle->setNotes($article['notes']);
                    $newArticle->setTitre($article['titre']);
                    $newArticle->setUrl($article['url']);
                    $newArticle->setLecturemn($article['lecturemn']);
                    $newArticle->setFlux($newFlux);
                    $marques = $this->sqliteConnection->executeQuery("SELECT * FROM marque WHERE article_id = ?", [$article['id']]);
                    foreach ($marques->fetchAllAssociative() as $marque) {
                        $newMarque = new Marque();
                        $newMarque->setStyle($marque['style']);
                        $newMarque->setSelection($marque['selection']);
                        $newMarque->setEtat($marque['etat']);
                        $newArticle->addMarque($newMarque);
                        $newMarque->setArticle($newArticle);
                        $this->testPersist($newMarque);
                    }
                    $questions = $this->sqliteConnection->executeQuery("SELECT * FROM question WHERE article_id = ?", [$article['id']]);
                    foreach ($questions->fetchAllAssociative() as $question) {
                        $newQuestion = new Question();
                        $newQuestion->setTexte($question['texte']);
                        $newQuestion->setEtat($question['etat']);
                        $newQuestion->setQuestion($question['question']);
                        $newQuestion->setReponse($question['reponse']);
                        $newArticle->addQuestion($newQuestion);
                        $this->testPersist($newQuestion);
                    }
                    $newFlux->addArticle($newArticle);
                    $this->testPersist($newArticle);
                    $this->testFlush($newArticle);
                }
                $newTheme->addFlux($newFlux);
                $this->testPersist($newFlux);
                $this->testFlush($newFlux);
            }
            $this->testPersist($newTheme);
            $this->testFlush($newTheme);
        }


        return Command::SUCCESS;
    }
    function testPersist($object)
    {
        try {
            $this->postgresEntityManager->persist($object);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n" . $object->__toString() . "\n";
            die();
        }
    }
    function testFlush($object)
    {
        try {
            $this->postgresEntityManager->flush();
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n" . $object->__toString() . "\n";

            die();
        }
    }
}
