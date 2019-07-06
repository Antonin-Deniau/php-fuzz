<?php

namespace AntoninDeniau\Fuz;

use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

use DI;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;

use AntoninDeniau\Fuz\Mutator;
use AntoninDeniau\Fuz\MutatorInterface;

return [
    LoggerInterface::class => DI\factory(function () {
        $logger = new Logger('fuzzer');
        $logger->pushHandler(new ErrorLogHandler());
        return $logger;
    }),
    MutatorInterface::class => DI\object(Mutator::class),
    Fuzzer::class => function (ContainerInterface $c) {
        return new Fuzzer(
            $c->get(LoggerInterface::class),
            $c->get(MutatorInterface::class)
        );
    },
];
