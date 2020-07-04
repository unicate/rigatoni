<?php


namespace Unicate\Rigatoni\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unicate\Rigatoni\Core\Rigatoni;
use Unicate\Rigatoni\Core\Init;

class InitCommand extends Command {

    private $rigatoni;

    public function __construct(Init $init) {
        $this->init = $init;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('init')
            ->setDescription('Init');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'Init Project overwrite config?',
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::FAILURE;
        }

        $success= $this->init->initConfig();
        $success = ($success === true) ? Rigatoni::MIGRATION_STATUS_SUCCESS : Rigatoni::MIGRATION_STATUS_FAILED;
        $output->writeln($success);

        return Command::SUCCESS;
    }
}