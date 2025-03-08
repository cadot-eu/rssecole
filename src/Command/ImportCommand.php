<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\DBAL\Connection;

#[AsCommand(
    name: 'app:import',
    description: 'Add a short description for your command',
)]
class ImportCommand extends Command
{
    private Connection $postgresConnection;

    public function __construct(Connection $postgresConnection)
    {
        parent::__construct();
        $this->postgresConnection = $postgresConnection;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Chemin vers le fichier SQL
        $filePath = 'datacomplet.db.sql';

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            $io->error("The file $filePath does not exist.");
            return Command::FAILURE;
        }

        // Lire le fichier SQL
        $sql = file_get_contents($filePath);

        // Diviser les instructions SQL par ligne
        $statements = explode(";\n", $sql);

        // Exécuter chaque instruction SQL
        foreach ($statements as $statement) {
            if (strpos($statement, 'INSERT INTO sqlite_sequence') === false) {
                $statement = trim($statement);
                if (empty($statement)) {
                    continue; // Ignorer les lignes vides
                }

                try {
                    $this->postgresConnection->executeStatement($statement);
                } catch (\Exception $e) {
                    $io->warning('Skipping invalid SQL statement: ' . $statement);
                    $io->warning('Error: ' . $e->getMessage());
                    //return Command::FAILURE;
                }
            }
        }

        $io->success('Data imported successfully.');
        return Command::SUCCESS;
    }
}
