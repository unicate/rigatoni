<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

declare(strict_types=1);

namespace Nofw\Services;

use Nofw\Core\Config;
use Nofw\Core\Constants;
use PhpMyAdmin\SqlParser\Lexer;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Error;
use Medoo\Medoo;
use \PDO;

class SqlLoader {

    const FILE_EXTENSION = '.sql';
    const UP_MIGRATION = 'V';
    const DOWN_MIGRATION = 'U';
    const REPEAT_MIGRATION = 'R';
    const MIGRATION_SEPARATOR = '__';
    private $config;
    private $db;
    private $files = array();
    private $errors = array();
    private $connection;

    public function __construct(Config $config, Medoo $db) {
        $this->config = $config;
        $this->db = $db;
    }

    public function scanDirectory() {
        return array_diff(scandir(Constants::DB_DIR), array('..', '.'));
    }

    public function fileFilter(array $fileList, string $startStr = ''): array {
        $files = (array_filter($fileList, function (string $val) use ($startStr) {
            $isEndsWith = self::endsWith($val, self::FILE_EXTENSION);
            $isStartsWith = self::startsWith($val, $startStr);
            return (empty($startStr)) ? $isEndsWith : $isStartsWith && $isEndsWith;
        }));
        asort($files);
        return array_values($files);
    }

    public function fileIndex(array $files): array {
        $index = array();
        foreach ($files as $file) {
            $file_prefixes = SqlLoader::UP_MIGRATION . SqlLoader::DOWN_MIGRATION . SqlLoader::REPEAT_MIGRATION;
            $file_extension = SqlLoader::FILE_EXTENSION;
            $matcher = preg_match('/^([' . $file_prefixes . '])(.*)__(.*)(' . $file_extension . ')/', $file, $file_parts);
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
                'isRepeatable' => $prefix === SqlLoader::REPEAT_MIGRATION,
                'isMigration' => $prefix === SqlLoader::UP_MIGRATION,
                'isUndo' => $prefix === SqlLoader::DOWN_MIGRATION
            ];
        }
        uasort($index, function ($a, $b) {
            return strcmp($a['version'] . $a['description'], $b['version'] . $b['description']);
        });

        return $index;
    }


    public static function startsWith(string $haystack, string $needle) {
        return (substr($haystack, 0, strlen($needle))) === $needle;
    }

    public static function endsWith(string $haystack, string $needle) {
        return (substr($haystack, -strlen($needle))) === $needle;
    }


    private function checkFiles() {
        foreach ($this->files as $file) {
            // Read SQL file
            $sql = file_get_contents(Constants::DB_DIR . DIRECTORY_SEPARATOR . $file);

            // Check
            $lexer = new Lexer($sql, false);
            $parser = new Parser($lexer->list);
            $errors = Error::get([$lexer, $parser]);

            // Collect Errors
            foreach ($errors as $error) {
                $this->errors[] = [
                    'file' => $file,
                    'message' => $error
                    //'sql' => $sql
                ];
            }
        }
    }


    public function getFiles(): array {
        return $this->files;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function allValid(): bool {
        return empty($this->errors);
    }

    public function getConnection(): Medoo {
        return $this->db;
    }

    public function setUpMigrations() {
        $this->db->drop('migrations');
        $this->db->create('migrations', [
            'id' => ['INT', 'NOT NULL', 'AUTO_INCREMENT', 'PRIMARY KEY'],
            'version' => ['VARCHAR(32)', 'NOT NULL'],
            'prefix' => ['CHAR(1)', 'NOT NULL'],
            'description' => ['VARCHAR(256)', 'NOT NULL'],
            'file' => ['VARCHAR(256)', 'NOT NULL'],
            'hash' => ['VARCHAR(256)', 'NULL'],
            'status' => ['TINYINT(1)', 'NOT NULL'],
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

}