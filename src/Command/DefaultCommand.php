<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultCommand extends Command {


    public function __construct() {
        parent::__construct();
    }

    protected function configure() {
        $this->setName('default')->setDescription('Just saying hello.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $output->write('
 _____  _____ _____       _______ ____  _   _ _____ 
|  __ \|_   _/ ____|   /\|__   __/ __ \| \ | |_   _|
| |__) | | || |  __   /  \  | | | |  | |  \| | | |  
|  _  /  | || | |_ | / /\ \ | | | |  | | . ` | | |  
| | \ \ _| || |__| |/ ____ \| | | |__| | |\  |_| |_ 
|_|  \_\_____\_____/_/    \_\_|  \____/|_| \_|_____|
 
');
    $output->writeln('A simple dish - made of fresh SQL migrations.');
    $output->writeln('');
    $output->writeln('Use ./rigatoni list to show available Command.');
    $output->writeln('');

        return Command::SUCCESS;
    }
}