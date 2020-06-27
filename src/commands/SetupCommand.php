<?php


namespace Unicate\Rigatoni\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unicate\Rigatoni\Core\Rigatoni;

class SetupCommand extends Command {

    private $rigatoni;

    public function __construct(Rigatoni $rigatoni) {

        $this->rigatoni = $rigatoni;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('setup')
            ->setDescription('DB Install')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            );
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

        $success = $this->rigatoni->setupMigrations();
        $output->writeln($success);

        return Command::SUCCESS;
    }
}