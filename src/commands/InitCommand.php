<?php


namespace Unicate\Rigatoni\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unicate\Rigatoni\Core\AbstractMigration;
use Unicate\Rigatoni\core\MigrationFacade;
use Unicate\Rigatoni\Core\InitConfig;

class InitCommand extends Command {

    private $initConfig;

    public function __construct(InitConfig $initConfig) {
        $this->initConfig = $initConfig;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('init')
            ->setDescription('InitConfig');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'InitConfig Project overwrite config?',
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::FAILURE;
        }

        $success= $this->initConfig->initConfig();
        $success = ($success === true) ? AbstractMigration::MIGRATION_STATUS_SUCCESS : AbstractMigration::MIGRATION_STATUS_FAILED;
        $output->writeln($success);

        return Command::SUCCESS;
    }
}