<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Migration;

use Medoo\Medoo;
use Unicate\Rigatoni\Core\Config;
use \PDO;
use \PDOException;

class UndoMigration extends AbstractMigration {

    /**
     * UndoMigration constructor.
     * @param Medoo $db
     * @param Config $config
     */
    public function __construct(Medoo $db, Config $config) {
        $this->db = $db;
        $this->config = $config;
    }

    public function getAll() {
        $result = $this->db->select(
            AbstractMigration::MIGRATION_TABLE_NAME, '*',
            [
                'prefix' => AbstractMigration::PREFIX_UNDO_MIGRATION,
                'ORDER' => ['version' => 'DESC']
            ]
        );
        return ($result === false) ? array() : $this->toMigration($result);
    }

    public function getDownToVersion($version) {
        $result = $this->db->select(
            AbstractMigration::MIGRATION_TABLE_NAME, '*',
            [
                'prefix' => AbstractMigration::PREFIX_UNDO_MIGRATION,
                'version[>=]' => $version,
                'ORDER' => ['version' => 'DESC']
            ]
        );
        return ($result === false) ? array() : $this->toMigration($result);
    }
}