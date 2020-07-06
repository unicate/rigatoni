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

class InfoCommand extends Command {

    private $facade;
    private $config;

    public function __construct(MigrationFacade $facade, Config $config) {
        $this->facade = $facade;
        $this->config = $config;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('info')
            ->setDescription('Shows migration table.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->facade->refresh();

        $output->writeln('');
        $output->writeln('Database <options=bold>' . $this->config->getDbName() . '</>.');
        $output->writeln('');

        $migrations = $this->facade->getAll();

        if (empty($migrations)) {
            $output->writeln('No migrations found.');
        }

        // Table Headers
        $section = $output->section();
        $table = new Table($section);
        $table->setHeaders(['Type', 'File', 'Status', 'Installed on']);
        $table->render();

        foreach ($migrations as $migration) {
            $table->appendRow([
                $migration->getPrefix(),
                $migration->getFile(),
                Formatter::status($migration->getStatus()),
                $migration->getInstalledOn()
            ]);
        }
        $output->writeln('');

        foreach ($migrations as $migration) {

            $errors = $migration->getErrors();
            if (!empty($errors)) {
                $output->writeln('<error>Migration:' . $migration->getFile() . '</error>');
                $output->writeln($errors);
                $output->writeln('');
            }
        }


        return Command::SUCCESS;
    }
}