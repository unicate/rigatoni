<?php

namespace Unicate\Rigatoni\Tests\services;

use Unicate\Rigatoni\Core\Config;
use Unicate\Rigatoni\Core\Constants;
use Unicate\Rigatoni\Services\SqlLoader;
use Unicate\Rigatoni\Tests\AbstractDBService;

class SqlLoaderTest extends AbstractDBService {
    private $loader;

    protected function setUp() {
        $config = new Config(Constants::CONFIG_FILE);
        $db = $this->getDBConnection();
        $this->loader = new SqlLoader($config, $db);
    }


    public function testScanDirectory() {
        $files = $this->loader->scanDirectory();
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

        $appliedMigrations = [
            'R__Test-Migration-1.sql',
            'V002__Test-Migration-3.sql',
        ];

        $files1 = $this->loader->fileIndex($files, $appliedMigrations);
        $this->assertEquals([
            'U002__Test-Migration-2.sql',
            'V002__Test-Migration-3.sql',
            'V009__Test-Migration-1.sql',
            'R__Test-Migration-1.sql'
        ], array_keys($files1));

        $this->assertTrue($files1['R__Test-Migration-1.sql']['isApplied']);
        $this->assertFalse($files1['V009__Test-Migration-1.sql']['isApplied']);
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

 // PHLOX!!!

    public function testSetUpMigrations() {
        $success = $this->loader->setUpMigrations();
        $this->assertEquals(0, intval($success[0]));

    }

    public function testInsertMigration() {
        $success = $this->loader->insertMigration('0001', 'V', 'Some Desc', 'V0001__file.sql', 'hash', 1);
        $this->assertEquals(0, intval($success[0]));

        $success = $this->loader->insertMigration('0002', 'V', 'Some Desc', 'V0002__file.sql', 'hash', 1);
        $this->assertEquals(0, intval($success[0]));

        $success = $this->loader->insertMigration('0004', 'V', 'Some Desc', 'V0004__file.sql', 'hash', 1);
        $this->assertEquals(0, intval($success[0]));

        $success = $this->loader->insertMigration('0004', 'U', 'Some Desc', 'U0004__file.sql', 'hash', 1);
        $this->assertEquals(0, intval($success[0]));

        $success = $this->loader->insertMigration('', 'R', 'Some Desc', 'R__file1.sql', 'hash', 1);
        $this->assertEquals(0, intval($success[0]));
    }

    public function testHashFile() {
        $file = 'test.sql';
        $hash = $this->loader->hashFile($file);
        $this->assertNotEmpty($hash);

    }
    public function testHashFileCompare() {
        $file = 'test.sql';
        $success = $this->loader->hashFileCompare($file, 'cb74105c53e11874b77add5af061b6fc');
        $this->assertTrue($success);

    }

    public function testGetPendingMigrations() {
        $success = $this->loader->getPendingMigrations();
        $this->assertNotEmpty($success);
    }


}
