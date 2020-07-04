<?php


namespace Unicate\Rigatoni\core;

use Medoo\Medoo;

class Check {
    private $db;
    private $config;

    public function __construct(Medoo $db, Config $config) {
        $this->db = $db;
        $this->config = $config;
    }

    public function isConfigOK() {
        return (!empty($this->config));
    }

    public function isDBConnectionOK() {
        return (!empty($this->config));
    }

    public function getEnvironment() {
        return $this->config->getCurrentEnv();
    }

    public function getDBInfo() {
        return $this->db->info();
    }

    public function getSqlFolder() {
        return $this->config->getSQLFolderPath();
    }

    public function sqlFolderExists() {
        return is_dir($this->config->getSQLFolderPath());
    }


}