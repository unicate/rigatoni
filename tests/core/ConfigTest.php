<?php

namespace Unicate\Rigatoni\Tests\core;

use Unicate\Rigatoni\Core\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase {
    protected function setUp() {
        parent::setUp();
    }

    public function testConfig() {
        $filePath = [
            __DIR__ . '/../autoload.php',
            __DIR__ . '/../../rigatoni.json',
            __DIR__ . '/../composer.json',
            __DIR__ . '/../../composer.json'
        ];

        foreach ($filePath as $file) {
            if (file_exists($file)) {
                putenv('RIGATONI_ROOT=' . dirname(realpath($file)));
                break;
            }
        }
        $jsonFile = Config::getConfigFilePath();
        $config = new Config($jsonFile);
        $this->assertEquals('127.0.0.1', $config->getDbHost());
    }

    public function testEnv() {
        putenv('RIGATONI_ROOT=/some/path/to/root/dir');
        $root = getenv('RIGATONI_ROOT');
        $this->assertEquals('/some/path/to/root/dir', $root);
    }

    public function testFindVendor() {
        $dir = __DIR__ . '/';
        while (empty(glob($dir . 'vendor', GLOB_ONLYDIR))) {
            $dir .= '../';
            if (substr_count($dir, '../') >= 5) {
                throw new \RuntimeException('Unable to find vendor directory, even up to ' . realpath($dir) . '.');
            }
        }

        $root = realpath($dir);
        $this->assertNotNull($root);
    }

}
