<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Unicate\Rigatoni\Core\Check;
use Symfony\Component\Console\Helper\Table;
use Unicate\Rigatoni\Utils\Formatter;

class CheckCommand extends Command {

    private $check;

    public function __construct(Check $check) {
        $this->check = $check;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('check')
            ->setDescription('Check DB and SQL folder.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $table = new Table($output);
        $table
            ->setHeaders(['Check', 'Info'])
            ->setRows([
                    ['Checking config', Formatter::success($this->check->isConfigOK())],
                    ['Environment', $this->check->getEnvironment()],
                    ['DB connection', Formatter::success($this->check->isDBConnectionOK())],
                    ['DB DSN', $this->check->getDBInfo()['dsn']],
                    ['SQL folder', $this->check->getSqlFolder()],
                    ['SQL folder exists', Formatter::success($this->check->sqlFolderExists())],
                ]
            );
        $table->render();

        return Command::SUCCESS;
    }
}