<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

declare(strict_types=1);

namespace Unicate\Rigatoni\Migrations;

use Medoo\Medoo;
use Unicate\Rigatoni\Core\Config;
use \PDO;
use \PDOException;


abstract class AbstractMigration {
    public const PREFIX_VERSIONED_MIGRATION = 'V';
    public const PREFIX_REPEATABLE_MIGRATION = 'R';
    public const PREFIX_UNDO_MIGRATION = 'U';

    public const MIGRATION_SEPARATOR = '__';
    public const FILE_EXTENSION = '.sql';
    public const STATEMENT_DELIMITER = ';';
    public const MIGRATION_TABLE_NAME = 'migration';

    public const MIGRATION_STATUS_SUCCESS = 'SUCCESS';
    public const MIGRATION_STATUS_PENDING = 'PENDING';
    public const MIGRATION_STATUS_FAILED = 'FAILED';
    public const MIGRATION_STATUS_UNDONE = 'UNDONE';

    /**
     * @var Medoo $db DB-Connection.
     */
    protected $db;

    /**
     * @var Config $config Configuration object.
     */
    protected $config;


    /**
     * Converts an array of database records to an array ob Migration objects.
     * @param array $list
     * @return MigrationVO[]
     * @see MigrationVO
     */
    public function toMigration(array $list): array {
        $migrations = array();
        foreach ($list as $entry) {
            $migration = new MigrationVO($entry['prefix'], $entry['version'], $entry['file']);
            $migration->setStatus($entry['status']);
            $migration->setErrors($entry['errors']);
            $migration->setInstalledOn($entry['installed_on']);
            $migrations[] = $migration;
        }
        return $migrations;
    }




}