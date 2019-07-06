<?php

namespace AntoninDeniau\Fuz;

use Exception;
use AntoninDeniau\Fuz\MutatorInterface;
use AntoninDeniau\Fuz\Genome;

class Mutator implements MutatorInterface {
    public $sizeLimit = 100;

    public $fitnessChildRatio = 0.95;

    public $populationSize = 100;
    public $childRatio = 0.8;
    public $mutationRatio = 0.1;

    public $population = [];
    public $previousPopulation = [];

    function getPopulation()
    {
        return $this->population;
    }

    function setInitialPopulation($pop)
    {
        $pop = array_slice($pop,0,$this->populationSize);

        $population = $pop;

        $toAdd = $this->populationSize - count($pop);
        for ($i = 0; $i < $toAdd; $i++) {
            $genomeIndex = rand(0, count($pop) - 1);
            $genome = $pop[$genomeIndex];

            $newGenome = $this->mutate($genome);
            $population[] = $newGenome;
        }

        $this->population = $population;
    }

    function mutate($genome)
    {
        $newData = array_values(unpack('C*', $genome->value));

        $index = rand(0, count($newData) - 1);

        $target = rand(0, 1) === 1 ? 
            rand(0, 255)
            :
            $newData[rand(0, count($newData) - 1)];

        switch (rand(0, 4)) {
            case 0:
                $newData[$index] = ~ $newData[$index];
                break;
            case 1:
                $newData[$index] = $newData[$index] ^ $target;
                break;
            case 2:
                $newData[$index] = $newData[$index] & $target;
                break;
            case 3:
                $newData[$index] = $newData[$index] | $target;
                break;
            case 4:
                $i = rand(1, count($newData) - 1);

                $res = [];
                for ($f = 0; $f < rand(2, 10); $f++) $res[] = $newData[$i];
                $a = array_slice($newData, 0, $i);
                $b = array_slice($newData, $i, count($newData));
                $newData = array_merge($a, $res, $b);
        }

        $newData = implode(array_map("chr", $newData));
        $newData = substr($newData, 0, $this->sizeLimit);
        $fitness = round($genome->fitness * $this->fitnessChildRatio);
        return new Genome($newData, $fitness);
    }

    function evolvePopulation()
    {
        $oldPop = $this->getPopulation();
        $sortByFitness = function ($a, $b) {
            if ($a->fitness === $b->fitness) return 0;

            return ($a->fitness > $b->fitness) ? -1 : 1;
        };
        usort($oldPop, $sortByFitness);

        $parents = array_values(array_slice($oldPop, 0, $this->populationSize * (1 - $this->childRatio)));
        shuffle($parents);

        $parents1 = array_values(array_slice($parents, 0, count($parents) / 2));
        $parents2 = array_values(array_slice($parents, count($parents) / 2, count($parents)));

        $childs = [];
        for ($i = 0; $i < (count($parents) / 2) - 1; $i++) {
            $parent1 = $parents1[$i];
            $parent2 = $parents2[$i];

            $childs[] = $this->breed($parent1, $parent2);
            $childs[] = $this->breed($parent1, $parent2);
        }

        $this->population = array_merge($parents, $childs);
    }


    function breed($parent1, $parent2)
    {
        $data = rand(0,1) === 1 ? 
            $parent1->value . $parent2->value
            :
            $parent2->value . $parent1->value;

        $minlen = strlen(rand(0,1) === 1 ? $parent1->value : $parent2->value);
        $maxlen = strlen($data);

        $len = rand(0, 1) === 1 ? strlen($parent1->value) : strlen($parent2->value);

        $startIndex = rand(0, $maxlen - $len);

        $f1 = $parent1->fitness;
        $f2 = $parent2->fitness;

        $fitness = round(max($f1, $f2) * $this->fitnessChildRatio);
        $newValue = substr($data, $startIndex, $len);

        $child = new Genome($newValue, $fitness);

        if (rand(0, 100) < $this->mutationRatio * 100) {
            return $this->mutate($child);
        } else {
            return $child;
        }
    }

    function getBestGenome()
    {
        $pop = $this->getPopulation();
        $sortByFitness = function ($a, $b) {
            if ($a->fitness === $b->fitness) return 0;

            return ($a->fitness > $b->fitness) ? -1 : 1;
        };
        usort($pop, $sortByFitness);

        return $pop[0];
    }

    function getGenome($hash)
    {
        $genomes = array_values(array_filter($this->population, function ($g) use ($hash) {
            return $g->hash === $hash;
        }));

        if (!isset($genomes[0])) {
            throw new Exception("Cannot find genome: " . $hash);
        } else {
            return $genomes[0];
        }
    }

    function setGenome($genome)
    {
        $hash = $genome->hash;
        $genomes = array_values(array_filter($this->population, function ($g) use ($hash) {
            return $g->hash !== $hash;
        }));

        $this->population = $genomes;
        $this->population[] = $genome;
    }

    function setFitness($hash, $prio)
    {
        $genome = $this->getGenome($hash);
        $genome->setFitness($prio);
        $this->setGenome($genome);
    }
}
