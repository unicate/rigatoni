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

class RepeatableMigration extends GenericMigration {

    /**
     * VersionedMigration constructor.
     * @param Medoo $db
     * @param Config $config
     */
    public function __construct(Medoo $db, Config $config) {
        parent::__construct($db, $config);
    }

    public function getAll(): array {
        $result = $this->db->select(
            AbstractMigration::MIGRATION_TABLE_NAME, '*',
            [
                'prefix' => AbstractMigration::PREFIX_REPEATABLE_MIGRATION,
                'ORDER' => ['version' => 'ASC', 'file' => 'ASC']
            ]
        );
        return ($result === false) ? array() : $this->toMigration($result);
    }
}