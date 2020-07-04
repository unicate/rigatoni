<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Migrations;

use Medoo\Medoo;
use Unicate\Rigatoni\Core\Config;
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
        $this->db->drop(AbstractMigration::MIGRATION_TABLE_NAME);
        $pdo = $this->db->create(AbstractMigration::MIGRATION_TABLE_NAME, [
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