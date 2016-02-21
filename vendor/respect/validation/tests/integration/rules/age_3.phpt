--FILE--
<?php

require 'vendor/autoload.php';

use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\AllOfException;

try {
    v::age(18)->assert('17 years ago');
} catch (AllOfException $e) {
    echo $e->getFullMessage();
}

?>
--EXPECTF--
- "17 years ago" must be less than or equal to "%d-%d-%d %d:%d:%d"
