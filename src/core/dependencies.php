<?php

namespace Unicate\Rigatoni\Core;

use Medoo\Medoo;
use Unicate\Rigatoni\Commands\SetupCommand;
use Unicate\Rigatoni\Core\Config;
use Unicate\Rigatoni\Core\Constants;
use Symfony\Component\Console\Application;
use \PDO;

return [

    Config::class =>
        \DI\autowire()->constructor(Constants::CONFIG_FILE),


    Application::class => function (Config $config) {
        return new Application('echo', '1.0.0');
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


 
