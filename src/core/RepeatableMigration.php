<?php


namespace Unicate\Rigatoni\core;


use Medoo\Medoo;
use \PDO;
use \PDOException;

class RepeatableMigration extends AbstractMigration {

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
                'prefix' => AbstractMigration::PREFIX_REPEATABLE_MIGRATION,
                'ORDER' => ['version' => 'ASC']
            ]
        );
        return ($result === false) ? array() : $this->toMigration($result);
    }
}