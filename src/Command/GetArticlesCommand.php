<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use App\Entity\Theme;

#[AsCommand(
    name: 'app:get-articles',
    description: 'Récupération des articles des fluxs',
)]

class GetArticlesCommand extends Command
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('nomsThemes', InputArgument::OPTIONAL, 'nom du thème')
            //->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        dd($this->em->getRepository(Theme::class)->findAll());
        $io = new SymfonyStyle($input, $output);
        $nomsThemes = explode(',', $input->getArgument('nomsThemes'));
        if (count($nomsThemes) == 1 && $nomsThemes[0] == '') {
            $ListThemes = $this->em->getRepository(Theme::class)->findAll();
        } else {
            foreach ($nomsThemes as $nomTheme) {
                $recherche = $this->em->getRepository(Theme::class)->findOneBy(['nom' => $nomTheme]);
                if ($recherche) {
                    $ListThemes[] = $recherche;
                } else {
                    $io->error('Le thème ' . $nomTheme . ' n\'existe pas');
                    return Command::FAILURE;
                }
            }
        }
        // si on est en mode test on ne prend que le premier theme
        if ($_ENV['APP_ENV'] == 'test') {
            $ListThemes = [$ListThemes[0]];
        }
        foreach ($ListThemes as $theme) {
            $io->note('Theme: ' . $theme->getNom());
            $fluxs = $theme->getFluxs();
            //si on est en test on prend une seul flux
            if ($_ENV['APP_ENV'] == 'test') {
                $fluxs = $fluxs[0];
            }
            foreach ($fluxs as $flux) {

                $io->note('Flux: ' . $flux->getUrl());
                $flux->loadArticles();
                $this->em->persist($flux);

                $io->success(count($flux->getArticles()) . ' Articles chargés');
                $this->em->flush();
            }
        }

        return Command::SUCCESS;
    }
}
