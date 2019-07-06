<?php

namespace AntoninDeniau\Fuz;

interface MutatorInterface {
    function setInitialPopulation($initialPopulation);
    function getPopulation();
    function setFitness($hash, $fitness);
    function evolvePopulation();
    function getBestGenome();
}
