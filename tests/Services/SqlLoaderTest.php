<?php

namespace Nofw\Tests\services;

use Nofw\Core\Config;
use Nofw\Core\Constants;
use Nofw\Services\SqlLoader;
use PHPUnit\Framework\TestCase;

class SqlLoaderTest extends TestCase {
    private $loader;

    protected function setUp() {
        $config = new Config(Constants::CONFIG_FILE);
        $this->loader = new SqlLoader($config);
    }


    public function testScanDirectory() {
        $files = $this->loader->getFiles();
        echo 'Files: ' . print_r($files, true);
        $this->assertNotEmpty($files);
    }


    public function testFileFilter() {
        $files = [
            'R__Test-Migration-1.sql',
            'V009__Test-Migration-1.sql',
            '001_test.sql',
            '002_test.sql',
            'V__001_test.slq',
            'V002__Test-Migration-3.sql',
            'bla.txt',
            'U002__Test-Migration-2.sql'
        ];

        $files1 = $this->loader->fileFilter($files);
        $this->assertEquals(['001_test.sql', '002_test.sql', 'R__Test-Migration-1.sql', 'U002__Test-Migration-2.sql', 'V002__Test-Migration-3.sql', 'V009__Test-Migration-1.sql'], $files1);

        $files2 = $this->loader->fileFilter($files, SqlLoader::UP_MIGRATION);
        $this->assertEquals(['V002__Test-Migration-3.sql', 'V009__Test-Migration-1.sql'], $files2);

        $files3 = $this->loader->fileFilter($files, SqlLoader::DOWN_MIGRATION);
        $this->assertEquals(['U002__Test-Migration-2.sql'], $files3);

        $files4 = $this->loader->fileFilter($files, SqlLoader::REPEAT_MIGRATION);
        $this->assertEquals(['R__Test-Migration-1.sql'], $files4);
    }

    public function testFileIndex() {
        $files = [
            'R__Test-Migration-1.sql',
            'V009__Test-Migration-1.sql',
            '001_test.sql',
            '002_test.sql',
            'V__001_test.slq',
            'V002__Test-Migration-3.sql',
            'bla.txt',
            'U002__Test-Migration-2.sql',
            'X002__Test-Migration-2.sql'
        ];

        $files1 = $this->loader->fileIndex($files);
        $this->assertNotNull($files1);
        $this->assertEquals(['R__Test-Migration-1.sql'], $files1);
    }

    public function testStartsWith() {
        $this->assertTrue(
            SqlLoader::startsWith('U__002_bla.sql', 'U__')
        );
        $this->assertFalse(
            SqlLoader::startsWith('D__002_bla.sql', 'U__')
        );
    }

    public function testEndsWith() {
        $this->assertTrue(
            $this->loader->endsWith('U__002_bla.sql', '.sql')
        );
        $this->assertFalse(
            $this->loader->endsWith('D__002_bla.txt', '.sql')
        );
    }

    public function testCheckFiles() {
        $errors = $this->loader->getErrors();
        echo 'Errors: ' . print_r($errors, true);
        $this->assertEmpty($errors);
    }

    public function testAllValid() {
        $allValid = $this->loader->allValid();
        $this->assertTrue($allValid);
    }

    public function testGetConnection() {
        $this->loader->getConnection();
        $errors = $this->loader->getErrors();
        echo 'Errors: ' . print_r($errors, true);
        $this->assertEmpty($errors);
    }

    public function testExecuteAll() {
        $success = $this->loader->executeAll();
        $this->assertTrue($success);
    }


}
