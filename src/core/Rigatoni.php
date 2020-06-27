<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

declare(strict_types=1);

namespace Unicate\Rigatoni\Core;

use Medoo\Medoo;
use \PDO;
use \PDOException;
use phpDocumentor\Reflection\Types\Array_;

class Rigatoni {

    const FILE_EXTENSION = '.sql';
    const UP_MIGRATION = 'V';
    const DOWN_MIGRATION = 'U';
    const REPEAT_MIGRATION = 'R';
    const MIGRATION_SEPARATOR = '__';
    const STATEMENT_DELIMITER = ';';
    const MIGRATION_STATUS_SUCCESS = 'SUCCESS';
    const MIGRATION_STATUS_FAILED = 'FAILED';
    const MIGRATION_STATUS_UNDONE = 'UNDONE';
    const MIGRATION_STATUS_PENDING = 'PENDING';

    private $db;
    private $sqlDir = Constants::SQL_DIR;
    private $index;

    public function __construct(Medoo $db) {
        $this->db = $db;
    }

    /**
     * The directory for the file migrations.
     * Default is @param string $path
     * @see Constants::SQL_DIR
     */
    public function setSqlDir(string $path) {
        $this->sqlDir = $path;
    }

    /**
     * Reads files and data from db and starts the sync.
     */
    public function refresh() {
        $fileMigrations = $this->getFileMigrations();
        $dbMigrations = $this->getDBMigrations();
        $this->sync($fileMigrations, $dbMigrations);
    }

    /**
     * Generates array with Migration items from files.
     * @return Migration[]
     */
    public function getFileMigrations() {
        $migrations = array();
        // Scan files in directory
        $files = array_diff(scandir($this->sqlDir), array('..', '.'));
        $file_prefixes = Rigatoni::UP_MIGRATION . Rigatoni::DOWN_MIGRATION . Rigatoni::REPEAT_MIGRATION;
        $file_extension = Rigatoni::FILE_EXTENSION;

        // Iterate over all files
        foreach ($files as $file) {
            $matcher = preg_match('/^([' . $file_prefixes . '])(.*)' . Rigatoni::MIGRATION_SEPARATOR . '(.*)(' . $file_extension . ')/', $file, $file_parts);
            if (!$matcher) {
                continue;
            }
            $migrations[md5($file)] = new Migration(
                $file_parts[1], // Prefix
                $file_parts[2], // Version
                $file
            );
        }
        return $migrations;
    }

    /**
     * Gets array with Migration items from database.
     * @param string $prefix
     * @param string $version
     * @param string status
     * @return Migration[]
     */
    public function getDBMigrations($prefix = '', $version = '', $status = '') {
        $query = [];
        if (!empty ($prefix)) {
            $query['prefix'] = $prefix;
        }
        if (!empty ($version)) {
            $query['version'] = $version;
        }
        if (!empty ($status)) {
            $query['status'] = $status;
        }
        $dbMigrations = $this->db->select(
            'migrations', '*', $query, ['ORDER' => ["version" => "asc"]]
        );
        $migrations = array();
        foreach ($dbMigrations as $entry) {
            $migration = new Migration(
                $entry['prefix'],
                $entry['version'],
                $entry['file']
            );
            $migration->setStatus($entry['status']);
            $migrations[$entry['id']] = $migration;
        }
        return $migrations;
    }

    /**
     * Synchronizes file- and db migrations.
     * @param array $fileMigrations
     * @param array $dbMigrations
     * @return array
     */
    private function sync(array $fileMigrations, array $dbMigrations): array {
        $diff = array_diff_key($fileMigrations, $dbMigrations);
        foreach ($diff as $migration) {
            $this->insertMigration($migration);
        }
        return $diff;
    }

    /**
     * Creates database table for migrations.
     * @return string Error Message
     */
    public function setupMigrations() {
        $this->db->drop('migrations');
        $this->db->create('migrations', [
            'id' => ['VARCHAR(32)', 'NOT NULL', 'PRIMARY KEY'],
            'prefix' => ['CHAR(1)', 'NOT NULL'],
            'version' => ['VARCHAR(32)', 'NULL'],
            'file' => ['VARCHAR(256)', 'NOT NULL'],
            'hash' => ['VARCHAR(256)', 'NULL'],
            'status' => ['VARCHAR(32)', 'NOT NULL'],
            'errors' => ['TEXT', 'NULL'],
            'installed_on' => ['DATETIME', 'NULL']
        ]);
        return $this->db->error();
    }

    /**
     * Inserts migration.
     * @param Migration $migration
     * @return null
     */
    public function insertMigration(Migration $migration): bool {
        $this->db->insert("migrations", [
            "id" => $migration->getId(),
            "prefix" => $migration->getPrefix(),
            "version" => $migration->getVersion(),
            "file" => $migration->getFile(),
            "hash" => $migration->getHash(),
            "status" => $migration->getStatus(),
            "errors" => $migration->getErrors(),
            "installed_on" => $migration->getInstalledOn()
        ]);
        $success = intval($this->db->error()[0]);
        return $success === 0;
    }

    public function updateMigration(Migration $migration): bool {
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

    public function getAppliedMigrations() {
        return $this->db->select('migrations', [
            'file'
        ], [
            'prefix' => Rigatoni::UP_MIGRATION
        ], [
            'ORDER' => ["version" => "asc"]
        ]);
    }

    public function getPendingMigrations() {
        return (array_filter($this->index, function ($val) {
            return $val['isMigration'] === true && $val['isApplied'] === false;
        }));
    }

    public function applyMigration(Migration $migration) : bool {
        // Read SQL file
        $sql = file_get_contents($this->sqlDir . DIRECTORY_SEPARATOR . $migration->getFile());

        // Remove linebreaks and whitespace
        $sql = str_replace(array("\r", "\n"), ' ', $sql);
        $sql = preg_replace('!\s+!', ' ', $sql);

        // Split SQL into lines
        $lines = array_filter(explode(Rigatoni::STATEMENT_DELIMITER, $sql));
        $errors = array();

        // Execute SQL
        foreach ($lines as $line) {
            $this->db->query($line . Rigatoni::STATEMENT_DELIMITER);
            $hasError = intval($this->db->error()[0]) !== 0;
            if ($hasError) {
                $errors[] = $this->db->error()[0] . ' - ' . $this->db->error()[2];
            }
        }
        if (!empty($errors)) {
            $migration->setErrors(implode(PHP_EOL . PHP_EOL, $errors));
            $migration->setStatus(Rigatoni::MIGRATION_STATUS_FAILED);
            $migration->setInstalledOn(null);
        } else {
            $migration->setErrors(null);
            $migration->setStatus(Rigatoni::MIGRATION_STATUS_SUCCESS);
            $migration->setInstalledOn(date("Y-m-d H:i:s"));
        }
        $this->updateMigration($migration);

        return empty($errors);


    }


}