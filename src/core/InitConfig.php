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

class InitConfig {


    public function __construct() {

    }

    public function initConfig() {
        $data = [
            'current_env' => 'dev_1',
            'env' => [
                'dev_1' => [
                    'db_host' => '127.0.0.1',
                    'db_port' => '8889',
                    'db_name' => 'rigatoni_test',
                    'db_user' => 'rigatoni_app',
                    'db_pwd' => '123456',
                    'sql_dir' => '/db'
                ],
                'dev_2' => [
                    'db_host' => '127.0.0.1',
                    'db_port' => '8889',
                    'db_name' => 'The-DB-Name',
                    'db_user' => 'The-DB-User',
                    'db_pwd' => 'The-DB-Password',
                    'sql_dir' => '/relative/path/to/sql'
                ]
            ]
        ];
        $file = Config::getConfigFilePath();
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents($file, $json) >= 1;
    }


}