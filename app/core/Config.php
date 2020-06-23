<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

declare(strict_types=1);

namespace Nofw\Core;

use Symfony\Component\Dotenv\Dotenv;

class Config {

    private $appVersion;
    private $logLevel;
    private $dbHost;
    private $dbPort;
    private $dbName;
    private $dbUser;
    private $dbPassword;

    public function __construct(string $envFile) {
        $dotenv = new Dotenv();
        $dotenv->load($envFile);

        $this->appVersion = $_ENV['APP_VERSION'];
        $this->logLevel = $_ENV['LOG_LEVEL'];
        $this->dbHost = $_ENV['DB_HOST'];
        $this->dbPort = $_ENV['DB_PORT'];
        $this->dbName = $_ENV['DB_NAME'];
        $this->dbUser = $_ENV['DB_USER'];
        $this->dbPassword = $_ENV['DB_PWD'];
    }

    public function getAppVersion(): string {
        return $this->appVersion;
    }

    public function getLogLevel(): string {
        return $this->logLevel;
    }

    public function getDbHost(): string {
        return $this->dbHost;
    }

    public function getDbPort(): string {
        return $this->dbPort;
    }

    public function getDbName(): string {
        return $this->dbName;
    }

    public function getDbUser(): string {
        return $this->dbUser;
    }

    public function getDbPassword(): string {
        return $this->dbPassword;
    }


}