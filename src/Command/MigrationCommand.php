<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unicate\Rigatoni\Core\Config;
use Unicate\Rigatoni\Migration\MigrationFacade;
use Unicate\Rigatoni\Util\Formatter;

class MigrationCommand extends Command {

    private $facade;
    private $config;

    public function __construct(MigrationFacade $facade, Config $config) {
        $this->facade = $facade;
        $this->config = $config;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('migrate')
            ->setDescription('Executes pending migrations.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->facade->refresh();

        $helper = $this->getHelper('question');
        $output->writeln('');
        $output->writeln('Database <options=bold>' . $this->config->getDbName() . '</>.');
        $output->writeln('Apply all pending migrations');
        $output->writeln('');
        $question = new ConfirmationQuestion('Do you want to continue? [y/n]',
            false,
            '/^(y|j)/i'
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::FAILURE;
        }

        $pending = $this->facade->getPendingMigrations();
        $repeatable = $this->facade->getRepeatableMigrations();
        $migrations = array_merge($pending, $repeatable);

        if (empty($migrations)) {
            $output->writeln('No pending migrations.');
        }

        // Progress
        $progressBar = new ProgressBar($output, count($migrations));
        $progressBar->start();

        // Table Headers
        $table = new Table($output);
        $table->setHeaders(['Type', 'File', 'Status', 'Installed on']);

        // Table output
        foreach ($migrations as $migration) {
            $progressBar->advance();
            $success = $this->facade->applyMigration($migration);
            $table->addRow([
                $migration->getPrefix(),
                $migration->getFile(),
                Formatter::status($migration->getStatus()),
                $migration->getInstalledOn()
            ]);
        }

        $output->writeln('');
        $progressBar->finish();
        $table->render();
        $output->writeln('');

        return Command::SUCCESS;
    }
}