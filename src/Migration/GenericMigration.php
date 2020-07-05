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

class GenericMigration extends AbstractMigration {

    /**
     * GenericMigration constructor.
     * @param Medoo $db
     * @param Config $config
     */
    public function __construct(Medoo $db, Config $config) {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Get all migrations from database.
     * @return MigrationVO[]
     */
    public function getAllMigrations(): array {
        $result = $this->db->select(
            AbstractMigration::MIGRATION_TABLE_NAME, '*', [
                'ORDER' => ['version' => 'ASC']
            ]
        );
        return ($result === false) ? $this->toMigration([]) : $this->toMigration($result);
    }

    /**
     * Get exactly one migration from database.
     * @param $prefix
     * @param $version
     * @return MigrationVO
     */
    public function getMigration($prefix, $version): MigrationVO {
        $result = $this->db->select(
            AbstractMigration::MIGRATION_TABLE_NAME, '*',
            [
                'prefix' => $prefix,
                'version' => $version,
                'ORDER' => ['version' => 'ASC']
            ]
        );
        return (!empty($result)) ? $this->toMigration($result)[0] : new MigrationVO('', '', '');
    }

    /**
     * Updates exactly one migration in the migration table.
     * Identified by the migration-id.
     * @param MigrationVO $migration
     * @return bool Success
     */
    public function updateMigration(MigrationVO $migration): bool {
        $pdo = $this->db->update(AbstractMigration::MIGRATION_TABLE_NAME, [
            "id" => $migration->getId(),
            "prefix" => $migration->getPrefix(),
            "version" => $migration->getVersion(),
            "file" => $migration->getFile(),
            "hash" => $migration->getHash(),
            "status" => $migration->getStatus(),
            "errors" => $migration->getErrors(),
            "installed_on" => $migration->getInstalledOn()
        ], [
            "id" => $migration->getId()
        ]);
        return $pdo->errorCode() === '00000';
    }

    /**
     * Executes the migration sql against the database and applies the migration.
     * @param MigrationVO $migration
     * @return bool Success
     */
    public function applyMigration(MigrationVO $migration): bool {
        // Read SQL file
        $sql = file_get_contents($this->config->getSQLFolderPath() . '/' . $migration->getFile());

        // Remove linebreaks and whitespace
        $sql = str_replace(array("\r", "\n"), ' ', $sql);
        $sql = preg_replace('!\s+!', ' ', $sql);

        // Split SQL into lines
        $lines = array_filter(explode(self::STATEMENT_DELIMITER, $sql));
        $errors = array();

        // Execute SQL
        foreach ($lines as $line) {
            $this->db->query($line . self::STATEMENT_DELIMITER);
            $hasError = intval($this->db->error()[0]) !== 0;
            if ($hasError) {
                $errors[] = $this->db->error()[0] . ' - ' . $this->db->error()[2];
            }
        }
        if (!empty($errors)) {
            $migration->setErrors(implode(PHP_EOL . PHP_EOL, $errors));
            $migration->setStatus(self::MIGRATION_STATUS_FAILED);
            $migration->setInstalledOn(null);
        } else {
            $migration->setErrors(null);
            $migration->setStatus(self::MIGRATION_STATUS_SUCCESS);
            $migration->setInstalledOn(date("Y-m-d H:i:s"));
        }
        $this->updateMigration($migration);

        return empty($errors);

    }


}