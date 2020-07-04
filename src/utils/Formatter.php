<?php
/**
 * @author https://unicate.ch
 * @copyright Copyright (c) 2020
 * @license Released under the MIT license
 */

namespace Unicate\Rigatoni\Utils;


use PhpMyAdmin\SqlParser\Lexer;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Error;
use Unicate\Rigatoni\Core\Constants;

class Formatter {


    public static function success($success) {
        if ($success === true) {
            return '<info>[OK]</info>';
        } else {
            return '<error>[FAILED]</error>';
        }
    }


}