<?php

namespace AntoninDeniau\Fuz;

require __DIR__ . '/../vendor/autoload.php';

use Exception;

use DI\Container;
use DI\ContainerBuilder;

use AntoninDeniau\Fuz\Fuzzer;
use AntoninDeniau\Fuz\Genome;

$builder = new ContainerBuilder();
$builder->addDefinitions('./src/config.php');
$container = $builder->build();

$fuzzer = $container->get(Fuzzer::class);

function test($input) {
    if (strlen($input) !== 7) return false;
    if ($input[0] !== "P") return false;
    if ($input[1] !== "A") return false;
    if ($input[2] !== "S") return false;
    if ($input[3] !== "S") return false;
    if ($input[4] !== "W") return false;
    if ($input[5] !== "O") return false;
    if ($input[6] !== "R") return false;
    if ($input[6] !== "D") return false;
    return True;
}

$t = function ($data) {
    if (test($data)) {
        $this->running = false;
    }
};

$pop = [new Genome("test", 0)];

$fuzzer->fuzz($pop, $t);
