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

class Rigatoni {

    const FILE_EXTENSION = '.sql';
    const UP_MIGRATION = 'V';
    const DOWN_MIGRATION = 'U';
    const REPEAT_MIGRATION = 'R';
    const MIGRATION_SEPARATOR = '__';
    const MIGRATION_STATUS_SUCCESS = 'SUCCESS';
    const MIGRATION_STATUS_FAILED = 'FAILED';
    const MIGRATION_STATUS_UNDONE = 'UNDONE';

    private $db;
    private $sqlDir = Constants::SQL_DIR;
    private $index;

    public function __construct(Medoo $db) {
        $this->db = $db;
        $this->refresh();
    }

    public function refresh() {
        $files = array_diff(scandir($this->sqlDir), array('..', '.'));
        $appliedMigrations = $this->getAppliedMigrations();
        $this->index = $this->getIndex($files, $appliedMigrations);
    }

    public function getIndex(array $files, array $appliedMigrations): array {
        $index = array();
        foreach ($files as $file) {
            $file_prefixes = Rigatoni::UP_MIGRATION . Rigatoni::DOWN_MIGRATION . Rigatoni::REPEAT_MIGRATION;
            $file_extension = Rigatoni::FILE_EXTENSION;
            $matcher = preg_match('/^([' . $file_prefixes . '])(.*)' . Rigatoni::MIGRATION_SEPARATOR . '(.*)(' . $file_extension . ')/', $file, $file_parts);
            if (!$matcher) {
                continue;
            }
            $prefix = $file_parts[1];
            $version = $file_parts[2];
            $description = $file_parts[3];
            $extension = $file_parts[4];
            $index[$file] = [
                'filename' => $file,
                'prefix' => $prefix,
                'description' => $description,
                'version' => $version,
                'extension' => $extension,
                'isRepeatable' => $prefix === Rigatoni::REPEAT_MIGRATION,
                'isMigration' => $prefix === Rigatoni::UP_MIGRATION,
                'isUndo' => $prefix === Rigatoni::DOWN_MIGRATION,
                'isApplied' => in_array($file, $appliedMigrations)
            ];
        }
        uasort($index, function ($a, $b) {
            return strcmp($a['version'] . $a['description'], $b['version'] . $b['description']);
        });
        return $index;
    }

    public function setSqlDir(string $path) {
        $this->sqlDir = $path;
    }

    public function setUpMigrations() {
        $this->db->drop('migrations');
        $this->db->create('migrations', [
            'id' => ['INT', 'NOT NULL', 'AUTO_INCREMENT', 'PRIMARY KEY'],
            'version' => ['VARCHAR(32)', 'NULL'],
            'prefix' => ['CHAR(1)', 'NOT NULL'],
            'description' => ['VARCHAR(256)', 'NOT NULL'],
            'file' => ['VARCHAR(256)', 'NOT NULL'],
            'hash' => ['VARCHAR(256)', 'NULL'],
            'status' => ['VARCHAR(32)', 'NOT NULL'],
            'installed_on' => ['DATETIME', 'NOT NULL']
        ]);
        return $this->db->error();
    }

    public function insertMigration($version, $prefix, $description, $file, $hash, $status) {
        $this->db->insert("migrations", [
            "version" => $version,
            "prefix" => $prefix,
            "description" => $description,
            "file" => $file,
            "hash" => $hash,
            "status" => $status,
            "installed_on" => date("Y-m-d H:i:s")
        ]);
        return $this->db->error();
    }


    public function getAppliedMigrations() {
        return $this->db->select('migrations', [
            'file'
        ], [
            'prefix' => Rigatoni::UP_MIGRATION,
            'status' => Rigatoni::MIGRATION_STATUS_SUCCESS
        ], [
            'ORDER' => ["version" => "asc"]
        ]);
    }

    public function getPendingMigrations() {
        return (array_filter($this->index, function ($val) {
            return $val['isMigration'] === true && $val['isApplied'] === false;
        }));
    }

    public function getUndoMigrations($version) {
        return (array_filter($this->index, function ($val) use ($version) {
            return $val['isUndo'] === true && intval($val['version'] >= intval($version));
        }));
    }

    public function getRepeatableMigrations() {
        return (array_filter($this->index, function ($val) {
            return $val['isRepeatable'] === true;
        }));
    }


}