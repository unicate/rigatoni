<?php

namespace Unicate\Rigatoni\core;

use Medoo\Medoo;
use \PDO;
use \PDOException;

class VersionedMigration extends AbstractMigration {

    /**
     * VersionedMigration constructor.
     * @param Medoo $db
     * @param Config $config
     */
    public function __construct(Medoo $db, Config $config) {
        $this->db = $db;
        $this->config = $config;
    }

    public function getAll() {
        $result = $this->db->select(
            'migrations', '*',
            [
                'prefix' => AbstractMigration::PREFIX_VERSIONED_MIGRATION,
                'ORDER' => ['version' => 'ASC']
            ]
        );
        return ($result === false) ? array() : $this->toMigration($result);
    }

    public function getAllPending() {
        $result = $this->db->select(
            'migrations', '*',
            [
                'prefix' => AbstractMigration::PREFIX_VERSIONED_MIGRATION,
                'status' => AbstractMigration::MIGRATION_STATUS_PENDING,
                'ORDER' => ['version' => 'ASC']
            ]
        );
        return ($result === false) ? array() : $this->toMigration($result);
    }

}