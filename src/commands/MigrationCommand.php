<?php


namespace Unicate\Rigatoni\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unicate\Rigatoni\Core\Rigatoni;

class MigrationCommand extends Command {

    private $rigatoni;

    public function __construct(Rigatoni $rigatoni) {

        $this->rigatoni = $rigatoni;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('migrate')
            ->setDescription('DB Install')
            ->addArgument(
                'action',
                InputArgument::OPTIONAL,
                'undo')
            ->addOption(
                'v',
                null,
                InputOption::VALUE_NONE,
                'The version'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->rigatoni->refresh();

        $action = $input->getArgument('action');
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'Migrate ' . $action . '?',
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::FAILURE;
        }


        if ($action == 'undo') {
            $migrations = $this->rigatoni->getDBMigrations(
                Rigatoni::DOWN_MIGRATION,
                '', // any version
                Rigatoni::MIGRATION_STATUS_PENDING
            );
        } else {
            // Default up migrations
            $migrations = $this->rigatoni->getDBMigrations(
                Rigatoni::UP_MIGRATION,
                '', // any version
                Rigatoni::MIGRATION_STATUS_PENDING
            );
        }

        if (empty($migrations)) {
            $output->writeln('No pending migrations.');
        }

        foreach ($migrations as $migration) {
            $success = $this->rigatoni->applyMigration($migration);
            $success = ($success === true) ? Rigatoni::MIGRATION_STATUS_SUCCESS : Rigatoni::MIGRATION_STATUS_FAILED;
            $output->writeln('Migration: ' . $migration->getFile() . ' -> ' . $success);
        }


        return Command::SUCCESS;
    }
}