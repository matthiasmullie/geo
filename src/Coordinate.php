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
class Coordinate
{
    /**
     * @var float
     */
    public $latitude;

    /**
     * @var float
     */
    public $longitude;

    /**
     * @var array
     */
    public $data;

    /**
     * @param float $latitude
     * @param float $longitude
     * @param array $data
     */
    public function __construct($latitude, $longitude, $data = array())
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->data = $data;
    }
}
