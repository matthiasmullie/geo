<?php
namespace MatthiasMullie\Geo;

/**
 * Please report bugs on https://github.com/matthiasmullie/geo/issues
 *
 * @author Matthias Mullie <geo@mullie.eu>
 *
 * @copyright Copyright (c) 2013, Matthias Mullie. All rights reserved.
 * @license MIT License
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
