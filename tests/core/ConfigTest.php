<?php

namespace Unicate\Rigatoni\Tests\core;

use Unicate\Rigatoni\Core\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase {
    protected function setUp() {
        parent::setUp();
    }

    public function testConfig() {
        $jsonFile = '/../../rigatoni.json';
        if (!file_exists($jsonFile)) {

        }

        $config = new Config($jsonFile);

        $this->assertEquals('127.0.0.1', $config->getDbHost());


    }

    public function testWriteJson() {
        $data = [
            'current_env' => 'dev_1',
            'env' => [
                'dev_1' => [
                    'db_host' => '127.0.0.1',
                    'db_port' => '8889',
                    'db_name' => 'rigatoni_test',
                    'db_user' => 'rigatoni_app',
                    'db_pwd' => '123456',
                    'sql_dir' => './db'
                ],
                'dev_2' => [
                    'db_host' => 'The-DB-Host',
                    'db_name' => 'The-DB-Name',
                    'db_port' => 'The-DB-Port',
                    'db_user' => 'The-DB-User',
                    'db_pwd' => 'The-DB-Password',
                    'sql_dir' => '/path/to/sql'
                ]
            ]
        ];
        $file = __DIR__ . '/../../rigatoni.json';
        $json = json_encode($data, JSON_PRETTY_PRINT);
        //$f = file_put_contents($file, $json);


        //$this->assertNotEmpty($f);


    }

    /**
     * @depends testWriteJson
     */
    public function testReadJson() {

        $file = __DIR__ . '/../../rigatoni.json';
        $json = file_get_contents($file);

        $data = json_decode($json, true);
        $current = $data['current_env'];
        $dbHost = $data['env'][$current]['db_host'];

        $this->assertEquals('127.0.0.1', $dbHost);


    }



    public function testEnv() {
        putenv('RIGATONI_ROOT=/Users/raoul/Projects/Web/unicate/rigatoni');
        $root = getenv('RIGATONI_ROOT');
        $this->assertEquals('/Users/raoul/Projects/Web/unicate/rigatoni', $root);
    }

    public function testGetRootDir() {
        $filePath = [
            __DIR__ . '/../rigatoni.json',
            __DIR__ . '/../../rigatoni.json',
            __DIR__ . '/../composer.json',
            __DIR__ . '/../../composer.json'
        ];
        foreach ($filePath as $file) {
            if (file_exists($file)) {
                putenv('RIGATONI_ROOT='. dirname(realpath($file)));
                break;
            }
        }

        $root = getenv('RIGATONI_ROOT');
        $this->assertEquals('/Users/raoul/Projects/Web/unicate/rigatoni', $root);



    }
}
