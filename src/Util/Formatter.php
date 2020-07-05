<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Util;

class Formatter {


    public static function success($success) {
        if ($success === true) {
            return '<info>[OK]</info>';
        } else {
            return '<error>[FAILED]</error>';
        }
    }


}