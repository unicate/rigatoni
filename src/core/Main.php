<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

declare(strict_types=1);

namespace Unicate\Rigatoni\Core;

use DI\ContainerBuilder;
use Unicate\Rigatoni\Commands\MigrationCommand;
use Unicate\Rigatoni\Commands\SetupCommand;
use Unicate\Rigatoni\Services\RoutingService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class Main {

    public function __construct() {
        // Setup DI Container
        $container = $this->initContainer();

        // CLI Application
        $application = $container->get(Application::class);
        $command = $container->get(SetupCommand::class);
        $application->add($command);
        //$application->setDefaultCommand($command->getName(), true);

        $application->add($container->get(MigrationCommand::class));

        $application->run();
    }

    private function initContainer(): ContainerInterface {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->addDefinitions(Constants::DEPENDENCY_FILE);
        return $containerBuilder->build();
    }


}

new Main();


