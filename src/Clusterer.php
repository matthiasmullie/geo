<?php

namespace MatthiasMullie\Geo;

/**
 * Please report bugs on https://github.com/matthiasmullie/geo/issues.
 *
 * @author Matthias Mullie <geo@mullie.eu>
 * @copyright Copyright (c) 2013, Matthias Mullie. All rights reserved.
 * @license MIT License
 */
class Clusterer
{
    /**
     * @var Bounds
     */
    protected $bounds;

    /**
     * Amount of coordinates in one cell needed to start clustering.
     *
     * @var int
     */
    protected $minLocations = 2;

    /**
     * @var int
     */
    protected $numberOfClusters = 50;

    /**
     * @var Cluster[][]
     */
    protected $clusters = array();

    /**
     * @var Coordinate[][][]
     */
    protected $coordinates = array();

    /**
     * @var int
     */
    protected $coefficientLat = 0;

    /**
     * @var int
     */
    protected $coefficientLng = 0;

    /**
     * @var bool
     */
    protected $spanBoundsLat = false;

    /**
     * @var bool
     */
    protected $spanBoundsLng = false;

    /**
     * @var bool
     */
    protected $saveCoordinates = false;

    public function __construct(Bounds $bounds)
    {
        // determine if bounds span 360 to -360 degrees gap
        $this->spanBoundsLng = $bounds->ne->longitude < $bounds->sw->longitude;
        $this->spanBoundsLat = $bounds->ne->latitude < $bounds->sw->latitude;

        $this->bounds = $this->fixBounds($bounds);
        $this->setNumberOfClusters($this->numberOfClusters);
    }

    /**
     * Enables coordinate saving in clusters.
     *
     * Note that while it allows you to retrieve all Coordinate objects
     * in a cluster, this does not scale. At some point, if you keep
     * adding coordinates into clusters, you'll run out of memory
     * because we're saving all those coordinates.
     * If you don't need the exact information of coordinates in a
     * cluster, leave this disabled.
     *
     * @param bool $save
     *
     * @throws Exception
     */
    public function setSaveCoordinates($save)
    {
        if (count($this->clusters) || count($this->coordinates)) {
            throw new Exception('Sorry, it is not possible to change coordinate saving policy after you have already added coordinates.');
        }

        $this->saveCoordinates = $save;
    }

    /**
     * Set the minimum amount of locations before clustering.
     *
     * @param int $limit
     */
    public function setMinClusterLocations($limit)
    {
        // simple sanity check. It doesn't make sense to have clusters with less than 2 locations
        $this->minLocations = (int)($limit > 2 ? $limit : 0); 
    }

    /**
     * Set an approximate amount of clusters.
     * Approximate in that it also depends on the viewport:
     * less square = less clusters.
     *
     * @param int $number
     */
    public function setNumberOfClusters($number)
    {
        if (count($this->clusters) || count($this->coordinates)) {
            throw new Exception('Sorry, it is not possible to change the number of clusters after you have already added coordinates.');
        }

        $this->numberOfClusters = $number;
        $this->createMatrix();
    }

    public function addCoordinate(Coordinate $coordinate)
    {
        list($latIndex, $lngIndex) = $this->findCell($coordinate);
        $coordinateCount = isset($this->coordinates[$latIndex][$lngIndex]) ? count($this->coordinates[$latIndex][$lngIndex]) : 0;

        // cluster already exists, add coordinate to it
        if (isset($this->clusters[$latIndex][$lngIndex])) {
            $this->clusters[$latIndex][$lngIndex]->addCoordinate($coordinate, $this->saveCoordinates);

        // there's no cluster yet, but entry limit reached = cluster now, as long as we have more than one location/coordinate
        } elseif ($coordinateCount >= $this->minLocations - 1 && $coordinateCount > 1) { 
            // initialise cluster with given coordinate
            $this->clusters[$latIndex][$lngIndex] = new Cluster();
            $this->clusters[$latIndex][$lngIndex]->addCoordinate($coordinate, $this->saveCoordinates);

            if ($coordinateCount) {
                // add existing coordinates
                foreach ($this->coordinates[$latIndex][$lngIndex] as $coordinate) {
                    $this->clusters[$latIndex][$lngIndex]->addCoordinate($coordinate, $this->saveCoordinates);
                }

                // save cluster & clear array of individual coordinates (to free up
                // memory, in case we're dealing with lots of coordinates)
                unset($this->coordinates[$latIndex][$lngIndex]);
            }

        // entry limit for clustering not yet reached, save coordinate
        } else {
            $this->coordinates[$latIndex][$lngIndex][] = $coordinate;
        }
    }

    /**
     * @return Coordinate[]
     */
    public function getCoordinates()
    {
        // flatten matrix of coordinates
        $coordinates = $this->coordinates ? call_user_func_array('array_merge', $this->coordinates) : array();

        return $coordinates ? call_user_func_array('array_merge', $coordinates) : array();
    }

    /**
     * @return Cluster[]
     */
    public function getClusters()
    {
        // flatten matrix of clusters
        return $this->clusters ? call_user_func_array('array_merge', $this->clusters) : array();
    }

    /**
     * Based on given bounds, determine matrix size/structure.
     */
    protected function createMatrix()
    {
        $totalLat = $this->bounds->ne->latitude - $this->bounds->sw->latitude;
        $totalLng = $this->bounds->ne->longitude - $this->bounds->sw->longitude;

        $approxMiddle = round(sqrt($this->numberOfClusters));
        // the smaller one wins
        $func = $totalLat > $totalLng ? 'floor' : 'ceil';
        $numLat = $func($totalLat / ($totalLat + $totalLng) * $approxMiddle * 2);
        $numLng = $approxMiddle * 2 - $numLat;

        // this will be used later to calculate exactly which sector a
        // coordinate falls into (see findCell)
        $this->coefficientLat = 1 / $totalLat * $numLat;
        $this->coefficientLng = 1 / $totalLng * $numLng;
    }

    /**
     * Find the lat & lng indices of the matrix cell
     * the given coordinate fits into.
     *
     * @return array
     */
    protected function findCell(Coordinate $coordinate)
    {
        $coordinate = $this->fixCoordinates($coordinate);

        return array(
            floor(($coordinate->latitude - $this->bounds->sw->latitude) * $this->coefficientLat),
            floor(($coordinate->longitude - $this->bounds->sw->longitude) * $this->coefficientLng),
        );
    }

    /**
     * "Fix" coordinates - when leaping from east 360 to west -359, increase
     * the west coordinated by 360 to make calculating easier.
     *
     * @return Coordinate
     */
    protected function fixCoordinates(Coordinate $coordinate)
    {
        // create a new copy of this object, don't just change existing one
        $coordinate = clone $coordinate;

        if ($this->spanBoundsLat && $coordinate->latitude < $this->bounds->sw->latitude) {
            $coordinate->latitude += 180;
        }

        if ($this->spanBoundsLng && $coordinate->longitude < $this->bounds->sw->longitude) {
            $coordinate->longitude += 360;
        }

        return $coordinate;
    }

    /**
     * North and east coordinates can actually be lower than south & west.
     * This will happen when the left side of a map is displaying east and
     * the right side is displaying west. At the center of the map, we'll
     * suddenly have coordinates jumping from 360 to -359.
     * To make calculating things easier, we'll just increase the west
     * (= negative) coordinates by 360, and consider those to now be east
     * (and east as west). Now, coordinates will go from 360 to 361.
     *
     * @return Bounds
     */
    protected function fixBounds(Bounds $bounds)
    {
        // create a new copy of this object, don't just change existing one
        $bounds = clone $bounds;

        if ($this->spanBoundsLat) {
            // workaround for crossover bounds being rounded too aggressively
            if ($bounds->sw->latitude <= 0.0) {
                $bounds->sw->latitude += 180;
            }

            $neLat = max(180 + $bounds->ne->latitude, 180 + $bounds->sw->latitude);
            $bounds->sw->latitude = $bounds->ne->latitude;
            $bounds->ne->latitude = $neLat;
        }
        if ($this->spanBoundsLng) {
            // workaround for crossover bounds being rounded too aggressively
            if ($bounds->sw->longitude <= 0.0) {
                $bounds->sw->longitude += 360;
            }

            $neLng = max(360 + $bounds->ne->longitude, 360 + $bounds->sw->longitude);
            $bounds->sw->longitude = $bounds->ne->longitude;
            $bounds->ne->longitude = $neLng;
        }

        return $bounds;
    }
}
