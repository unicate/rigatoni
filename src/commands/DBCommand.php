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

        $dsn = 'mysql:dbname=nofw;host=127.0.0.1;port=8889';
        $user = 'nofw_app';
        $password = '123456';

        try {
            $db = new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            $output->writeln('Connection failed: ' . $e->getMessage());
        }


        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);




       $success = $db->exec($sql);
        //$success = $db->query(), PDO);
        if ($success === false) {
            //$output->writeln('Fail');
        } else{
            //$output->writeln('OK' . print_r($success->errorInfo(),true));
        }



        return Command::SUCCESS;
    }
}