<?php

namespace Unicate\Rigatoni\Tests\core;

use Unicate\Rigatoni\Core\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase {
    protected function setUp() {
        parent::setUp();
    }

    public function testCreateVersionedMigrations() {
        $dir = __DIR__ . '/';
        while (empty(glob($dir . 'vendor', GLOB_ONLYDIR))) {
            $dir .= '../';
            if (substr_count($dir, '../') >= 5) {
                throw new \RuntimeException('Unable to find composer vendor directory, even up to ' . realpath($dir) . '.');
                exit(1);
            }
        }
        $root = realpath($dir);
        putenv('RIGATONI_ROOT=' . $root);

        $config = new Config(Config::getConfigFilePath());
        $dir = $config->getSQLFolderPath();
        $templateV = file_get_contents($dir . '/V001__Test.sql');
        $templateU = file_get_contents($dir . '/U001__Test.sql');


        for ($n = 10; $n < 160; $n++) {
            $version = str_pad($n, 3, "0", STR_PAD_LEFT);
            $templateV1 = str_replace('test_001', 'test_' . $version, $templateV);
            $filename = 'V' . $version . '__Test.sql';
            file_put_contents($dir . '/' . $filename, $templateV1);

            $filename = 'U' . $version . '__Test.sql';
            $templateU1 = str_replace('test_001', 'test_' . $version, $templateU);
            file_put_contents($dir . '/' . $filename, $templateU1);

        }

    }

    public function testCreateRepeatableMigrations() {
        $dir = __DIR__ . '/';
        while (empty(glob($dir . 'vendor', GLOB_ONLYDIR))) {
            $dir .= '../';
            if (substr_count($dir, '../') >= 5) {
                throw new \RuntimeException('Unable to find composer vendor directory, even up to ' . realpath($dir) . '.');
                exit(1);
            }
        }
        $root = realpath($dir);
        putenv('RIGATONI_ROOT=' . $root);

        $config = new Config(Config::getConfigFilePath());
        $dir = $config->getSQLFolderPath();
        $templateR = file_get_contents($dir . '/R__Test.sql');


        for ($n = 10; $n < 20; $n++) {
            $version = str_pad($n, 3, "0", STR_PAD_LEFT);
            $templateR1 = str_replace('r__test', 'r__test_' . $version, $templateR);
            $filename = 'R__Test_' . $version . '.sql';
            file_put_contents($dir . '/' . $filename, $templateR1);
        }

    }

}
