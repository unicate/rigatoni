<?php


namespace Unicate\Rigatoni\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unicate\Rigatoni\core\FacadeInterface;
use Unicate\Rigatoni\core\MigrationFacade;
use Unicate\Rigatoni\utils\Formatter;

class SetupCommand extends Command {

    private $facade;

    public function __construct(MigrationFacade $facade) {
        $this->facade = $facade;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('setup')
            ->setDescription('DB Install');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'Setup migrations in database. Existing table \'migrations\' will be dropped ?',
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::FAILURE;
        }

        $success = $this->facade->setup();
        $output->writeln(Formatter::success($success));

        return Command::SUCCESS;
    }
}