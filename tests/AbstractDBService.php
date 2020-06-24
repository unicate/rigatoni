<?php

namespace Nofw\Tests;

use Medoo\Medoo;
use Nofw\Core\Config;
use Nofw\Core\Constants;
use Nofw\Services\DatabaseService;
use PHPUnit\Framework\TestCase;
use \PDO;
use Psr\Log\LoggerInterface;
use Unicate\Logger\Logger;

class AbstractDBService extends TestCase {

    protected function getDBConnection() {
        $config = new Config(Constants::CONFIG_FILE);
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
    }



}
