<?php

$loader = @include __DIR__ . '/../vendor/autoload.php';
if (!$loader) {
    $loader = require __DIR__ . '/../../../../vendor/autoload.php';
}
$loader->add('React\MySQL', __DIR__ . '/../src/');

return $loader;