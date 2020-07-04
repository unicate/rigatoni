<?php


namespace Unicate\Rigatoni\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unicate\Rigatoni\Core\AbstractMigration;
use Unicate\Rigatoni\core\MigrationFacade;
use Unicate\Rigatoni\core\MigrationObject;
use Unicate\Rigatoni\utils\Formatter;

class MigrationCommand extends Command {

    private $facade;

    public function __construct(MigrationFacade $facade) {
        $this->facade = $facade;
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
        $this->facade->refresh();

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
            $migrations = $this->facade->getUndoMigrations($version);
        } else {
            // Default pending migrations
            $pending = $this->facade->getPendingMigrations();
            $repeatable = $this->facade->getRepeatableMigrations();
            $migrations = array_merge($pending, $repeatable);
        }

        if (empty($migrations)) {
            $output->writeln('No pending migrations.');
        }

        $section = $output->section();
        $table = new Table($section);
        $table->setHeaders(['Type', 'VersionedMigration', 'Success']);
        $table->render();



        foreach ($migrations as $migration) {
            $success = $this->facade->applyMigration($migration);
            //$success = ($success === true) ? Rigatoni::MIGRATION_STATUS_SUCCESS : Rigatoni::MIGRATION_STATUS_FAILED;
            //$output->writeln('Undo-VersionedMigration: ' . $migration->getFile() . ' -> ' . Formatter::success($success === true));
            $table->appendRow([$migration->getPrefix(),$migration->getFile(), Formatter::success($success === true)]);
            if ($migration->getPrefix() === AbstractMigration::PREFIX_UNDO_MIGRATION) {
                $undoneMigrations = $this->facade->getMigration(AbstractMigration::PREFIX_VERSIONED_MIGRATION, $migration->getVersion());
                if (!empty($undoneMigrations)) {
                    ($undoneMigrations[0])->setStatus(AbstractMigration::MIGRATION_STATUS_PENDING);
                    ($undoneMigrations[0])->setErrors(null);
                    ($undoneMigrations[0])->setInstalledOn(null);
                    $this->facade->updateMigration($undoneMigrations[0]);
                    //$output->writeln('Undone VersionedMigration: ' .  ($undoneMigrations[0])->getFile() . ' -> ' . Formatter::success($success === true));
                    $table->appendRow(['Undone', $migration->getFile(), Formatter::success($success === true)]);
                }

            }
        }

        return Command::SUCCESS;
    }
}