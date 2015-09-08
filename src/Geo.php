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
class Geo
{
    /**
     * Earth's mean radii.
     * Note that earth is not exactly round and so calculations will always be
     * slightly off.
     *
     * @see http://en.wikipedia.org/wiki/Earth_radius
     * @var float[]
     */
    protected $radii = array(
        // metric
        'km' => 6371,
        'hm' => 63710, // km * 10
        'dam' => 637100, // km * 100
        'm' => 6371000, // km * 1000
        'dm' => 63710000, // km * 10000
        'cm' => 637100000, // km * 100000
        'mm' => 6371000000, // km * 1000000

        // imperial (https://en.wikipedia.org/wiki/Imperial_units)
        'mi' => 3959,
        'fur' => 31672, // mi * 8
        'ch' => 316720, // mi * 80
        'yd' => 6967840, // mi * 1760
        'ft' => 20903520, // mi * 5280
        'in' => 250842240, // mi * 63360
    );

    /**
     * @var string
     */
    protected $unit;

    /**
     * @var float
     */
    protected $radius;

    /**
     * @param  string[optional] $unit e.g. km (kilometers) or mi (miles)
     * @throws Exception
     */
    public function __construct($unit = 'km')
    {
        $unit = strtolower($unit);

        // doublecheck if given unit is valid
        $units = array_keys($this->radii);
        if (!in_array($unit, $units)) {
            throw new Exception("Distance unit $unit is invalid. Valid units: ".implode(', ', $units));
        }

        $this->unit = $unit;
        $this->setRadius();
    }

    /**
     * Allows to override radius.
     * If null, radius will be reset to the default for the given unit.
     *
     * @param float[optional] $radius
     */
    public function setRadius($radius = null)
    {
        if ($radius === null) {
            $radius = $this->radii[$this->unit];
        }

        $this->radius = $radius;
    }

    /**
     * @see http://en.wikipedia.org/wiki/Great-circle_distance
     * @param  Coordinate $coord1 First coordinate
     * @param  Coordinate $coord2 Second coordinate
     * @return float      Actual distance in human readable format (e.g. km or mi)
     */
    public function distance(Coordinate $coord1, Coordinate $coord2)
    {
        // convert latitude/longitude degrees for both coordinates
        // to radians: radian = degree * π / 180
        $lat1 = deg2rad($coord1->latitude);
        $lng1 = deg2rad($coord1->longitude);
        $lat2 = deg2rad($coord2->latitude);
        $lng2 = deg2rad($coord2->longitude);

        // calculate great-circle distance
        $distance = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lng1 - $lng2));

        // distance in given format
        return $this->radius * $distance;
    }

    /**
     * This calculates the boundary at $distance north, east, south & west from
     * $coord.
     *
     * This can be used to easily query a database for coordinate within certain
     * boundaries, like this:
     *     SELECT *
     *     FROM coordinates
     *     WHERE
     *         lat BETWEEN :swlat AND :nelat
     *         lng BETWEEN :swlng AND :nelng
     *
     * :swlat being $bounds->sw->latitude
     * :swlng being $bounds->sw->longitude
     * :nelat being $bounds->ne->latitude
     * :nelng being $bounds->ne->longitude
     *
     * We only need 2 opposite corners in a rectangle to know all 4 boundaries,
     * in this case the northeast & southwest coordinate. The northwest
     * coordinate, for example, will have the same latitude as the southwest
     * coordinate, and the same latitude as the northeast coordinate.
     *
     * @param  Coordinate $coord    Coordinate to generate bounds for
     * @param  float      $distance Dinstance in human readable format (e.g. km or mi)
     * @return Bounds
     */
    public function bounds(Coordinate $coord, $distance)
    {
        // latitude boundaries
        $neLat = $coord->latitude + rad2deg($distance / $this->radius);
        $swLat = $coord->latitude - rad2deg($distance / $this->radius);

        // longitude boundaries (longitude gets smaller when latitude increases)
        $neLng = $coord->longitude + rad2deg($distance / $this->radius / cos(deg2rad($coord->latitude)));
        $swLng = $coord->longitude - rad2deg($distance / $this->radius / cos(deg2rad($coord->latitude)));

        return new Bounds(
            new Coordinate($neLat, $neLng),
            new Coordinate($swLat, $swLng)
        );
    }
}
