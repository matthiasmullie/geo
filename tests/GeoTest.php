<?php

use MatthiasMullie\Geo;

class GeoTest extends PHPUnit_Framework_TestCase
{
    public function dataProviderDistance()
    {
        return array(
            array(
                // Kortrijk railway station
                'coord1' => new Geo\Coordinate(50.824167, 3.263889),
                // Gent-Sint-Pieters railway station
                'coord2' => new Geo\Coordinate(51.035278, 3.709722),
                'unit' => 'km',
                'distance' => 39.080894334596429,
            ),
            array(
                // SFO airport
                'coord1' => new Geo\Coordinate(37.615223, -122.389977),
                // JFK airport
                'coord2' => new Geo\Coordinate(40.639722, -73.778889),
                'unit' => 'mi',
                'distance' => 2580.8205510675743,
            ),
        );
    }

    public function dataProviderBounds()
    {
        return array(
            array(
                // Kortrijk railway station
                'coord' => new Geo\Coordinate(50.824167, 3.263889),
                'unit' => 'km',
                'distance' => 10,
                'bounds' => new Geo\Bounds(
                    new Geo\Coordinate(50.914099160592, 3.4062537646061),
                    new Geo\Coordinate(50.734234839408, 3.1215242353939)
                ),
            ),
            array(
                // Gent-Sint-Pieters railway station
                'coord' => new Geo\Coordinate(51.035278, 3.709722),
                'unit' => 'km',
                'distance' => 10,
                'bounds' => new Geo\Bounds(
                    new Geo\Coordinate(51.125210160592, 3.8527343839376),
                    new Geo\Coordinate(50.945345839408, 3.5667096160624)
                ),
            ),
            array(
                // SFO airport
                'coord' => new Geo\Coordinate(37.615223, -122.389977),
                'unit' => 'mi',
                'distance' => 10,
                'bounds' => new Geo\Bounds(
                    new Geo\Coordinate(37.759945858078, -122.20727553192),
                    new Geo\Coordinate(37.470500141922, -122.57267846808)
                ),
            ),
            array(
                // JFK airport
                'coord' => new Geo\Coordinate(40.639722, -73.778889),
                'unit' => 'mi',
                'distance' => 10,
                'bounds' => new Geo\Bounds(
                    new Geo\Coordinate(40.784444858078, -73.588168056428),
                    new Geo\Coordinate(40.494999141922, -73.969609943572)
                ),
            ),
        );
    }

    /**
     * @test
     * @dataProvider dataProviderDistance
     */
    public function distance($coord1, $coord2, $unit, $expected)
    {
        $geo = new Geo\Geo($unit);
        $distance = $geo->distance($coord1, $coord2);
        $this->assertEquals($expected, $distance);
    }

    /**
     * @test
     * @dataProvider dataProviderBounds
     */
    public function bounds($coord, $unit, $distance, $expected)
    {
        $geo = new Geo\Geo($unit);
        $bounds = $geo->bounds($coord, $distance);
        $this->assertEquals($expected, $bounds);
    }
}
