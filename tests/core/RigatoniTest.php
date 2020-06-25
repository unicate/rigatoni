<?php

namespace Unicate\Rigatoni\Tests\Core;

use Unicate\Rigatoni\Core\Config;
use Unicate\Rigatoni\Core\Constants;
use Unicate\Rigatoni\Core\Rigatoni;
use Unicate\Rigatoni\Tests\AbstractDBService;

class RigatoniTest extends AbstractDBService {
    private $rigatoni;

    protected function setUp() {
        $db = $this->getDBConnection();
        $this->rigatoni = new Rigatoni($db);
    }


    public function xtestFileFilter() {
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

        $files1 = $this->rigatoni->fileFilter($files);
        $this->assertEquals(['001_test.sql', '002_test.sql', 'R__Test-Migration-1.sql', 'U002__Test-Migration-2.sql', 'V002__Test-Migration-3.sql', 'V009__Test-Migration-1.sql'], $files1);

        $files2 = $this->rigatoni->fileFilter($files, Rigatoni::UP_MIGRATION);
        $this->assertEquals(['V002__Test-Migration-3.sql', 'V009__Test-Migration-1.sql'], $files2);

        $files3 = $this->rigatoni->fileFilter($files, Rigatoni::DOWN_MIGRATION);
        $this->assertEquals(['U002__Test-Migration-2.sql'], $files3);

        $files4 = $this->rigatoni->fileFilter($files, Rigatoni::REPEAT_MIGRATION);
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

        $files1 = $this->rigatoni->getIndex($files, $appliedMigrations);
        $this->assertEquals([
            'U002__Test-Migration-2.sql',
            'V002__Test-Migration-3.sql',
            'V009__Test-Migration-1.sql',
            'R__Test-Migration-1.sql'
        ], array_keys($files1));

        $this->assertTrue($files1['R__Test-Migration-1.sql']['isApplied']);
        $this->assertFalse($files1['V009__Test-Migration-1.sql']['isApplied']);
    }

    public function testGetPendingMigrations() {
        $this->rigatoni->refresh();
        $pendingMigrations1 = $this->rigatoni->getPendingMigrations();
        $this->assertNotNull($pendingMigrations1);
        $this->assertArrayHasKey('V001__Test.sql', $pendingMigrations1);
        $this->assertArrayHasKey('V002__Test.sql', $pendingMigrations1);
        $this->assertArrayHasKey('V003__Test.sql', $pendingMigrations1);
    }

    public function testGetUndoMigrations() {
        $this->rigatoni->refresh();
        $undoMigrations1 = $this->rigatoni->getUndoMigrations('001');
        $this->assertArrayHasKey('U001__Test.sql', $undoMigrations1);
        $this->assertArrayHasKey('U002__Test.sql', $undoMigrations1);
        $this->assertArrayHasKey('U003__Test.sql', $undoMigrations1);

        $undoMigrations2 = $this->rigatoni->getUndoMigrations('002');
        $this->assertArrayHasKey('U002__Test.sql', $undoMigrations2);
        $this->assertArrayHasKey('U003__Test.sql', $undoMigrations2);
    }

    public function testGetRepeatableMigrations() {
        $this->rigatoni->refresh();
        $repeatableMigrations1 = $this->rigatoni->getRepeatableMigrations();
        $this->assertArrayHasKey('R__Test.sql', $repeatableMigrations1);
    }


    public function testSetUpMigrations() {
        $success = $this->rigatoni->setUpMigrations();
        $this->assertEquals(0, intval($success[0]));

    }

    public function testInsertMigration() {
        $success = $this->rigatoni->insertMigration('0001', 'V', 'Some Desc', 'V0001__file.sql', 'hash', Rigatoni::MIGRATION_STATUS_SUCCESS);
        $this->assertEquals(0, intval($success[0]));

        $success = $this->rigatoni->insertMigration('0002', 'V', 'Some Desc', 'V0002__file.sql', 'hash', Rigatoni::MIGRATION_STATUS_SUCCESS);
        $this->assertEquals(0, intval($success[0]));

        $success = $this->rigatoni->insertMigration('0004', 'V', 'Some Desc', 'V0004__file.sql', 'hash', Rigatoni::MIGRATION_STATUS_SUCCESS);
        $this->assertEquals(0, intval($success[0]));

        $success = $this->rigatoni->insertMigration('0004', 'U', 'Some Desc', 'U0004__file.sql', 'hash', Rigatoni::MIGRATION_STATUS_SUCCESS);
        $this->assertEquals(0, intval($success[0]));

        $success = $this->rigatoni->insertMigration('', 'R', 'Some Desc', 'R__file1.sql', 'hash', Rigatoni::MIGRATION_STATUS_SUCCESS);
        $this->assertEquals(0, intval($success[0]));
    }

    public function xxxtestHashFile() {
        $file = 'test.sql';
        $hash = $this->rigatoni->hashFile($file);
        $this->assertNotEmpty($hash);

    }

    public function xxxtestHashFileCompare() {
        $file = 'test.sql';
        $success = $this->rigatoni->hashFileCompare($file, 'cb74105c53e11874b77add5af061b6fc');
        $this->assertTrue($success);

    }




}
