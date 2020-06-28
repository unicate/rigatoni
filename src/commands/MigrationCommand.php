<?php


namespace Unicate\Rigatoni\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unicate\Rigatoni\core\Migration;
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
                InputOption::VALUE_REQUIRED,
                'The version'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->rigatoni->refresh();

        $action = $input->getArgument('action');
        $version = $input->getOption('v');
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'Migrate ' . $action . '?',
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::FAILURE;
        }


        if ($action == 'undo' && !(empty($version))) {
            $migrations = $this->rigatoni->getUndoMigrations($version);
        } else {
            // Default pending migrations
            $pending = $this->rigatoni->getPendingMigrations();
            $repeatable = $this->rigatoni->getRepeatableMigrations();
            $migrations = array_merge($pending, $repeatable);
        }

        if (empty($migrations)) {
            $output->writeln('No pending migrations.');
        }

        foreach ($migrations as $migration) {
            $success = $this->rigatoni->applyMigration($migration);
            $success = ($success === true) ? Rigatoni::MIGRATION_STATUS_SUCCESS : Rigatoni::MIGRATION_STATUS_FAILED;
            $output->writeln('Undo-Migration: ' . $migration->getFile() . ' -> ' . $success);
            if ($migration->getPrefix() === Rigatoni::DOWN_MIGRATION) {
                $undoneMigrations = $this->rigatoni->getMigration(Rigatoni::UP_MIGRATION, $migration->getVersion());
                if (!empty($undoneMigrations)) {
                    ($undoneMigrations[0])->setStatus(Rigatoni::MIGRATION_STATUS_PENDING);
                    ($undoneMigrations[0])->setErrors(null);
                    ($undoneMigrations[0])->setInstalledOn(null);
                    $this->rigatoni->updateMigration($undoneMigrations[0]);
                    $output->writeln('Undone Migration: ' .  ($undoneMigrations[0])->getFile() . ' -> ' . $success);
                }

            }
        }

        return Command::SUCCESS;
    }
}