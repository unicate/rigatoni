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
use \PDO;

class SqlLoader {

    const FILE_EXTENSION = '.sql';
    const UP_MIGRATION = 'V';
    const DOWN_MIGRATION = 'U';
    const REPEAT_MIGRATION = 'R';
    const MIGRATION_SEPARATOR = '__';
    private $config;
    private $files = array();
    private $errors = array();
    private $connection;

    public function __construct(Config $config) {
        $this->config = $config;
        $this->scanDirectory();
        $this->checkFiles();
        $this->checkConnection();

    }

    private function scanDirectory() {
        $this->files = array_diff(scandir(Constants::DB_DIR), array('..', '.'));
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

        $index = [];
        foreach ($files as $file) {
            $file_prefixes = SqlLoader::UP_MIGRATION . SqlLoader::DOWN_MIGRATION . SqlLoader::REPEAT_MIGRATION;
            $file_extension = SqlLoader::FILE_EXTENSION;
            preg_match('/^([' . $file_prefixes . '])(.*)__(.*)(' . $file_extension . ')/', $file, $file_parts);
            if (empty($file_parts)){
                continue;
            }
            $prefix = $file_parts[1];
            $version = $file_parts[2];
            $description = $file_parts[3];
            $extension = $file_parts[4];
            $index[] = [
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

        usort($index, function($a, $b) {
            return $a['version'] > $b['version'];
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

    private function checkConnection() {
        $dbName = $this->config->getDbName();
        $dbHost = $this->config->getDbHost();
        $dbPort = $this->config->getDbPort();
        $dsn = "mysql:dbname=$dbName;host=$dbHost;port=$dbPort";
        try {
            $this->connection = new PDO($dsn, $this->config->getDbUser(), $this->config->getDbPassword());
        } catch (\RuntimeException $e) {
            $this->errors[] = $e->getMessage();
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

    public function getConnection(): PDO {
        return $this->connection;
    }

    public function executeAll() {
        if ($this->allValid()) {
            $db = $this->getConnection();
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            foreach ($this->files as $file) {
                $sql = file_get_contents(Constants::DB_DIR . DIRECTORY_SEPARATOR . $file);
                $sql = preg_replace("/\r|\n/", "", $sql);
                $lines = explode(';', $sql);
                foreach ($lines as $line) {
                    if (!empty($line)) {
                        $prepare = $db->prepare($line);
                        $err = $prepare->errorInfo();

                        //echo $result;
                    }

                }

            }
            return true;
        } else {
            return false;
        }
    }


}