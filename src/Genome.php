<?php

namespace AntoninDeniau\Fuz;

class Genome {
    function __construct($value, $fitness)
    {
        $this->setValue($value);
        $this->setFitness($fitness);
        $this->hash = uniqid();
    }

    function setFitness($fitness)
    {
        if ($fitness <= 0) { $this->fitness = 0; } 
        else { $this->fitness = $fitness; }
    }

    function setValue($value)
    {
        $this->value = $value;
    }
}
