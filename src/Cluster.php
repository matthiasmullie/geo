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
class Cluster {
    /**
     * @var Bounds
     */
    public $bounds;

    /**
     * @var Coordinate
     */
    public $center;

    /**
     * @var int
     */
    public $total = 0;

    /**
     * @var Coordinate[]
     */
    public $coordinates = array();

    /**
     * @param Coordinate $coord
     * @param bool $save
     */
    public function addCoordinate(Coordinate $coord, $save) {
        if ($save) {
            $this->coordinates[] = $coord;
        }

        // initialize the cluster
        if ($this->total == 0) {
            $this->bounds = new Bounds($coord, $coord);
            $this->center = $coord;
            $this->total = 1;

            return;
        }

        // adjust cluster bounds to include this coordinate
        $this->bounds = new Bounds(
            new Coordinate(
                // these shorthand ifs are equivalent to min() and max(), but faster
                $this->bounds->ne->latitude > $coord->latitude ? $this->bounds->ne->latitude : $coord->latitude,
                $this->bounds->ne->longitude > $coord->longitude ? $this->bounds->ne->longitude : $coord->longitude
            ),
            new Coordinate(
                $this->bounds->sw->latitude < $coord->latitude ? $this->bounds->sw->latitude : $coord->latitude,
                $this->bounds->sw->longitude < $coord->longitude ? $this->bounds->sw->longitude : $coord->longitude
            )
        );

        // adjust weighed center
        $this->center = new Coordinate(
            (($this->center->latitude * $this->total) + $coord->latitude) / ($this->total + 1),
            (($this->center->longitude * $this->total) + $coord->longitude) / ($this->total + 1)
        );

        $this->total++;
    }
}
