<?php


namespace Unicate\Rigatoni\commands;

use Nofw\Core\Constants;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use \PDO;
use \PDOException;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Formatter;

class DBCommand extends Command {

    private $sqlLoader;

    public function __construct(SqlLoader $sqlLoader) {
        $this->sqlLoader = $sqlLoader;
    }

    protected function configure() {
        $this
            ->setName('db:install')
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
        $question = new ConfirmationQuestion('Continue with this action?', false);

        if (!$helper->ask($input, $output, $question)) {
            return Command::FAILURE;
        }


        return Command::SUCCESS;
    }
}