<?php

namespace AntoninDeniau\Fuz;

use Exception;
use AntoninDeniau\Fuz\MutatorInterface;
use AntoninDeniau\Fuz\Utils;
use Psr\Log\LoggerInterface;

class Fuzzer
{
	protected $errorOutput = "./results/";
	protected $inputOutput = "./new_content/";

    function __construct(
            LoggerInterface $logger,
            MutatorInterface $mutator
    )
    {
        $this->logger = $logger;
        $this->mutator = $mutator;
    }

	private function write_error_file($e, $location, $mutatedValue)
    {
        $hash = md5($location);
        $d = <<<EOF
Location: {$location}
Hash: {$hash}
Message: {$e->getMessage()}
Input:
{$mutatedValue}

Trace:
{$e->getTraceAsString()}
EOF;

        file_put_contents($this->errorOutput . "/" . md5($location), $d);
    }

	private function write_input_file($data)
    {
        file_put_contents($this->inputOutput . "/" . md5($data), $data);
    }


    function getTuples($trace)
    {
        $res = [];
        $previous = null;

        foreach ($trace as $f => $val) {
            $hash = Utils::hash($f);

            foreach ($val as $index => $count) {
                $id = $hash . "-" . $index;

                if ($previous !== null) {
                    $res[] = $previous . ";" . $id;
                }

                $previous = $id;
            }
        }

        return $res;
    }

    function fuzz($po, $test)
    {
        $this->running = true;

        // En version 2.6
        //xdebug_set_filter(XDEBUG_FILTER_CODE_COVERAGE, XDEBUG_PATH_BLACKLIST, [__DIR__ . "/vendor/"]);
        $this->mutator->setInitialPopulation($po);

        $errorBucket = [];
        $pathBucket = [];

        $generation = 0;
        $this->logger->info("Start fuzzing...");
        while ($this->running) {
            $population = $this->mutator->getPopulation();

            foreach ($population as $genome) {
                $value = $genome->value;
                $fitness = $genome->fitness;
                $hash = $genome->hash;

                //$this->logger->debug("=> " . var_export($value, true));

                try {
                    xdebug_start_code_coverage(XDEBUG_CC_DEAD_CODE);
                    $test($value);
                } catch (Exception $e) {
                    $this->logger->info($e->getMessage());

                    $f = $e->getTrace()[0]["file"];
                    $l = $e->getTrace()[0]["line"];
                    $location = $f . ":" . $l;

                    if (!in_array($location, $errorBucket)) {
                        $errorBucket[] = $location;

                        $this->write_error_file($e, $location, $value);
                    }
                }

                $coverage = xdebug_get_code_coverage();
                $tuples = $this->getTuples($coverage);

                $toAdd = array_diff($tuples, $pathBucket);

                if (count($toAdd) !== 0) {
                    $pathBucket = array_merge($toAdd, $pathBucket);

                    $this->mutator->setFitness($hash, count($toAdd) * 100);

                    !$this->inputOutput ?: $this->write_input_file($value);
                }

                xdebug_stop_code_coverage();
            }

            $this->mutator->evolvePopulation();
            if ($generation % 1000 === 0) {
                $this->logger->info("Evolving... (generation $generation)");
                $bestGenome = $this->mutator->getBestGenome();
                $this->logger->info("Current best genome (Fitness: " . $bestGenome->fitness . "):" . $bestGenome->value);
            }
            $generation++;
        }
    }
}
