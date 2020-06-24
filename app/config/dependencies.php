<?php

use Medoo\Medoo;
use Nofw\commands\DBCommand;
use Nofw\Core\Config;
use Nofw\Core\Constants;
use Symfony\Component\Console\Application;

return [

    Config::class =>
        \DI\autowire()->constructor(Constants::CONFIG_FILE),

    Application::class => function (Config $config) {
        $application = new Application('echo', '1.0.0');
        $command = new DBCommand();
        $application->add($command);

        $application->setDefaultCommand($command->getName(), true);

        return $application;
    },
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


 
