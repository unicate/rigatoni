<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unicate\Rigatoni\Core\Config;
use Unicate\Rigatoni\Migration\AbstractMigration;
use Unicate\Rigatoni\Migration\MigrationFacade;
use Unicate\Rigatoni\Util\Formatter;

class UndoCommand extends Command {

    private $facade;
    private $config;

    public function __construct(MigrationFacade $facade, Config $config) {
        $this->facade = $facade;
        $this->config = $config;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('undo')
            ->setDescription('Reverts migrations down to version.')
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'Version to migrate down to.',
                '0'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->facade->refresh();

        $version = $input->getArgument('version');
        $helper = $this->getHelper('question');
        $output->writeln('');
        $output->writeln('Database <options=bold>' . $this->config->getDbName() . '</>.');
        $output->writeln('Undo all migrations down to version \''. $version.'\'.');
        $output->writeln('');
        $question = new ConfirmationQuestion('Do you want to continue? [y/n]',
            false,
            '/^(y|j)/i'
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::FAILURE;
        }

        $migrations = $this->facade->getUndoMigrations($version);

        if (empty($migrations)) {
            $output->writeln('No undo migrations.');
        }

        // Table Headers
        $section = $output->section();
        $table = new Table($section);
        $table->setHeaders(['Type', 'File', 'Status', 'Success']);
        $table->render();

        foreach ($migrations as $migration) {
            $success = $this->facade->applyMigration($migration);
            $table->appendRow([$migration->getPrefix(), $migration->getFile(), $migration->getStatus(), Formatter::success($success === true)]);
            if ($migration->getPrefix() === AbstractMigration::PREFIX_UNDO_MIGRATION) {
                $undoneMigration = $this->facade->getMigration(AbstractMigration::PREFIX_VERSIONED_MIGRATION, $migration->getVersion());
                if (!$undoneMigration->empty()) {
                    $undoneMigration->setStatus(AbstractMigration::MIGRATION_STATUS_UNDONE);
                    $undoneMigration->setErrors(null);
                    $undoneMigration->setInstalledOn(null);
                    $this->facade->updateMigration($undoneMigration);
                    $table->appendRow([$undoneMigration->getPrefix(), $undoneMigration->getFile(), $undoneMigration->getStatus(), Formatter::success($success === true)]);
                }
            }
        }
        $output->writeln('');

        return Command::SUCCESS;
    }
}