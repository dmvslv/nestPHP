<?php
/**
 *
 *
 */

require('../vendor/autoload.php');
require('key.php');
require('../src/Nest.php');

$access = 'c.Th98wHpqCrrJEGAbJEE5GtBmsgbLlPxeC6PQYz0hWrvnySN2m2ybnpU8FshYuR1xunfKyyYeWEhhLSWjtjtCdsm9UBPQI2wFvg9v9LuckU2cvJYBvP1wJj7sFWloYs7oRzBzo2wRlKx5PZG3';

$nest = new \Nest\Api(0, 1);
$nest->setAuth($access)->listener(function($whole, $change, $index) use ($nest) {
    if($index == 1) {
        echo "\ninit\n";
        var_dump($change);
    } else {
        var_dump($change);
    }

});

?>