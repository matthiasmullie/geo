<?php

namespace MatthiasMullie\Geo\Tests;

use MatthiasMullie\Geo\Bounds;
use MatthiasMullie\Geo\Coordinate;
use MatthiasMullie\Geo\Geo;

class GeoTest extends CompatTestCase
{
    public static function dataProviderDistance()
    {
        return array(
            array(
                // Kortrijk railway station
                'coord1' => new Coordinate(50.824167, 3.263889),
                // Gent-Sint-Pieters railway station
                'coord2' => new Coordinate(51.035278, 3.709722),
                'unit' => 'km',
                'distance' => 39.080894334596429,
            ),
            array(
                // SFO airport
                'coord1' => new Coordinate(37.615223, -122.389977),
                // JFK airport
                'coord2' => new Coordinate(40.639722, -73.778889),
                'unit' => 'mi',
                'distance' => 2580.8205510675743,
            ),
        );
    }

    public static function dataProviderBounds()
    {
        return array(
            array(
                // Kortrijk railway station
                'coord' => new Coordinate(50.824167, 3.263889),
                'unit' => 'km',
                'distance' => 10,
                'bounds' => new Bounds(
                    new Coordinate(50.914099160592, 3.4062537646061),
                    new Coordinate(50.734234839408, 3.1215242353939)
                ),
            ),
            array(
                // Gent-Sint-Pieters railway station
                'coord' => new Coordinate(51.035278, 3.709722),
                'unit' => 'km',
                'distance' => 10,
                'bounds' => new Bounds(
                    new Coordinate(51.125210160592, 3.8527343839376),
                    new Coordinate(50.945345839408, 3.5667096160624)
                ),
            ),
            array(
                // SFO airport
                'coord' => new Coordinate(37.615223, -122.389977),
                'unit' => 'mi',
                'distance' => 10,
                'bounds' => new Bounds(
                    new Coordinate(37.759945858078, -122.20727553192),
                    new Coordinate(37.470500141922, -122.57267846808)
                ),
            ),
            array(
                // JFK airport
                'coord' => new Coordinate(40.639722, -73.778889),
                'unit' => 'mi',
                'distance' => 10,
                'bounds' => new Bounds(
                    new Coordinate(40.784444858078, -73.588168056428),
                    new Coordinate(40.494999141922, -73.969609943572)
                ),
            ),
        );
    }

    /**
     * @dataProvider dataProviderDistance
     */
    public function testDistance($coord1, $coord2, $unit, $expected)
    {
        $geo = new Geo($unit);
        $distance = $geo->distance($coord1, $coord2);
        $this->assertEquals($expected, $distance);
    }

    /**
     * @dataProvider dataProviderBounds
     */
    public function testBounds($coord, $unit, $distance, $expected)
    {
        $geo = new Geo($unit);
        $bounds = $geo->bounds($coord, $distance);
        $this->assertEqualsWithDelta($expected, $bounds, 0.00001);
    }
}
