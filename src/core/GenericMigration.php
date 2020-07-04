<?php

namespace Unicate\Rigatoni\core;

use Medoo\Medoo;
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
     * @return MigrationObject[]
     */
    public function getAllMigrations() {
        $result = $this->db->select(
            'migrations', '*', [
                'ORDER' => ['version' => 'ASC']
            ]
        );
        $this->db->last();
        return ($result === false) ? array() : $this->toMigration($result);
    }

    /**
     * Tries to get exactly one migration from database. However an array is returned
     * @param $prefix
     * @param $version
     * @return array
     * @todo Make shure only exactly one migration is returned.
     */
    public function getMigration($prefix, $version) {
        $result = $this->db->select(
            'migrations', '*',
            [
                'prefix' => $prefix,
                'version' => $version,
                'ORDER' => ['version' => 'ASC']
            ]
        );
        return ($result === false) ? array() : $this->toMigration($result);
    }

    /**
     * Updates exactly one migration in the migration table.
     * Identified by the migration-id.
     * @param MigrationObject $migration
     * @return bool Success
     */
    public function updateMigration(MigrationObject $migration): bool {
        $this->db->update("migrations", [
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
        $success = intval($this->db->error()[0]);
        return $success === 0;
    }

    /**
     * Executes the migration sql against the database and applies the migration.
     * @param MigrationObject $migration
     * @return bool Success
     */
    public function applyMigration(MigrationObject $migration): bool {
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