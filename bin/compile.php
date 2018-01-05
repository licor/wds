<?php
ini_set("phar.readonly", 'Off'); 

$cwd = getcwd();
chdir($cwd);

require __DIR__.'/../src/bootstrap.php';

use Wds\Compiler;

error_reporting(-1);
ini_set('display_errors', 1);

try {
    $compiler = new Compiler();
    $compiler->compile();
} catch (\Exception $e) {
    echo 'Failed to compile phar: ['.get_class($e).'] '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine().PHP_EOL;
    exit(1);
}