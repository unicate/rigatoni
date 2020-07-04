<?php

namespace Unicate\Rigatoni\core;

use Medoo\Medoo;
use \PDO;
use \PDOException;

class SetupMigration extends AbstractMigration {

    /**
     * SetupMigration constructor.
     * @param Medoo $db
     * @param Config $config
     */
    public function __construct(Medoo $db, Config $config) {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Drops existing table and creates new empty one.
     * @return bool
     */
    public function createTable() : bool {
        $this->db->drop('migrations');
        $pdo = $this->db->create('migrations', [
            'id' => ['VARCHAR(32)', 'NOT NULL', 'PRIMARY KEY'],
            'prefix' => ['CHAR(1)', 'NOT NULL'],
            'version' => ['VARCHAR(32)', 'NULL'],
            'file' => ['VARCHAR(256)', 'NOT NULL'],
            'hash' => ['VARCHAR(256)', 'NULL'],
            'status' => ['VARCHAR(32)', 'NOT NULL'],
            'errors' => ['TEXT', 'NULL'],
            'installed_on' => ['DATETIME', 'NULL']
        ]);
        return $pdo->errorCode() === '00000';
    }
}