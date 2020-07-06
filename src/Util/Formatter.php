<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Util;

use Unicate\Rigatoni\Migration\AbstractMigration;

class Formatter {


    public static function success($success) {
        if ($success === true || $success === AbstractMigration::MIGRATION_STATUS_SUCCESS) {
            return '<info>[OK]</info>';
        } else {
            return '<error>[FAILED]</error>';
        }
    }

    public static function status($status) {
        if ($status === AbstractMigration::MIGRATION_STATUS_SUCCESS) {
            return "<info>$status</info>";
        } else if ($status === AbstractMigration::MIGRATION_STATUS_FAILED) {
            return "<error>$status</error>";
        } else {
            return $status;
        }
    }


}