<?php

namespace MatthiasMullie\Geo;

/**
 * @author Matthias Mullie <geo@mullie.eu>
 */
class Coordinate {
    /**
     * @var float
     */
    public $latitude;

    /**
     * @var float
     */
    public $longitude;

    /**
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct($latitude, $longitude) {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
}
