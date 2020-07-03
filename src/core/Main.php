<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

declare(strict_types=1);

namespace Unicate\Rigatoni\Core;

use DI\ContainerBuilder;
use Medoo\Medoo;
use Unicate\Rigatoni\Commands\InitCommand;
use Unicate\Rigatoni\Commands\MigrationCommand;
use Unicate\Rigatoni\Commands\SetupCommand;
use Unicate\Rigatoni\Services\RoutingService;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use \PDO;

class Main {


    public function __construct() {
        // Setup DI Container
        $container = $this->initContainer();

        // CLI Application
        $application = $container->get(Application::class);
        $application->add($container->get(InitCommand::class));
        if (file_exists(Config::getConfigFilePath())) {
            $application->add($container->get(SetupCommand::class));
            $application->add($container->get(MigrationCommand::class));
        }



        $application->run();
    }

    private function initContainer(): ContainerInterface {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->addDefinitions(self::getContainerDefinition());
        return $containerBuilder->build();
    }

    private static function getContainerDefinition() {
        $rootDirectory = getenv('RIGATONI_ROOT');
        return [
            Config::class =>
                \DI\autowire()->constructor($rootDirectory . '/rigatoni.json'),

            Medoo::class => function (Config $config) {
                $dbConfig = [
                    'database_type' => 'mysql',
                    'server' => $config->getDbHost(),
                    'port' => $config->getDbPort(),
                    'database_name' => $config->getDbName(),
                    'username' => $config->getDbUser(),
                    'password' => $config->getDbPassword(),
                    'charset' => 'utf8',
                    "logging" => true,
                    'prefix' => '',
                    'option' => [
                        PDO::ATTR_CASE => PDO::CASE_NATURAL
                    ]
                ];
                return new Medoo($dbConfig);
            },
        ];
    }


}

new Main();


