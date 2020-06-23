<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

declare(strict_types=1);

namespace Nofw\Core;

use DI\ContainerBuilder;
use Nofw\commands\DBCommand;
use Nofw\Services\RoutingService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Nofw\commands\DefaultCommand;

class Main {

    public function __construct() {
        // Setup DI Container
        $container = $this->initContainer();

        // CLI Application
        $application = $container->get(Application::class);
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


