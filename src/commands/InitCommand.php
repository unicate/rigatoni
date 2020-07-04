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
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Unicate\Rigatoni\Core\InitConfig;
use Unicate\Rigatoni\Utils\Formatter;
use Unicate\Rigatoni\Core\Config;

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
        $output->writeln('');
        $output->writeln('Create config file \'' . Config::getConfigFilePath() . '\'.');
        $output->writeln('This will overwrite the existing config file.');
        $output->writeln('');
        $question = new ConfirmationQuestion('Do you want to continue? [y/n]',
            false,
            '/^(y|j)/i'
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::FAILURE;
        }

        $success = $this->initConfig->initConfig();
        $output->writeln('');
        $output->writeln('Config file created. ' . Formatter::success($success));
        $output->writeln('');

        return Command::SUCCESS;
    }
}