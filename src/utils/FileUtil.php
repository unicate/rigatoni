<?php


namespace Unicate\Rigatoni\utils;


use PhpMyAdmin\SqlParser\Lexer;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Error;
use Unicate\Rigatoni\Core\Constants;

class FileUtil {


    public static function startsWith(string $haystack, string $needle) {
        return (substr($haystack, 0, strlen($needle))) === $needle;
    }

    public static function endsWith(string $haystack, string $needle) {
        return (substr($haystack, -strlen($needle))) === $needle;
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

    private function checkFiles() {
        foreach ($this->files as $file) {
            // Read SQL file
            $sql = file_get_contents(Constants::SQL_DIR . DIRECTORY_SEPARATOR . $file);

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

}