<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

declare(strict_types=1);

namespace Unicate\Rigatoni\Core;


class Config {
    /**
     * Name of the json configuration file.
     */
    public static $configFileName = 'rigatoni.json';
    private $currentEnv;
    private $dbHost;
    private $dbPort;
    private $dbName;
    private $dbUser;
    private $dbPassword;
    private $rootDirectory;
    private $sqlDirectory;

    public function __construct(string $jsonFilePath) {
        $json = file_get_contents($jsonFilePath);
        $config_array = json_decode($json, true);
        $key = $config_array['current_env'];

        if (!array_key_exists($key, $config_array['env'])) {
            throw new \RuntimeException('Invalid environment key: ' . $key);
        }
        $config = $config_array['env'][$key];
        $this->currentEnv = $key;
        $this->dbHost = $config['db_host'];
        $this->dbPort = $config['db_port'];
        $this->dbName = $config['db_name'];
        $this->dbUser = $config['db_user'];
        $this->dbPassword = $config['db_pwd'];
        $this->sqlDirectory = $config['sql_dir'];
        $this->rootDirectory = self::getEnvRootDirectory();
    }

    public function getCurrentEnv(): string {
        return $this->currentEnv;
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

    public function getSqlDirectory(): string {
        return $this->sqlDirectory;
    }

    public function getRootDirectory(): string {
        return $this->rootDirectory;
    }

    public static function getEnvRootDirectory(): string {
        return getenv('RIGATONI_ROOT');
    }

    public function getConfigFileName(): string {
        return self::$configFileName;
    }

    public static function getConfigFilePath() {
        return self::getEnvRootDirectory() . '/' . self::$configFileName;
    }


}