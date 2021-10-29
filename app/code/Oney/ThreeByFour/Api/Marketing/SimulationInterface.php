<?php

namespace Oney\ThreeByFour\Api\Marketing;

interface SimulationInterface
{
    /**
     * Get single Simulation
     *
     * @param array $params
     * @return array
     */
    public function getSimulation($params = []);

    /**
     * Get multiple Simulations
     *
     * @return array
     */
    public function getSimulations();

    /**
     * @param float $amount
     *
     * @return self
     */
    public function build($amount);
}
