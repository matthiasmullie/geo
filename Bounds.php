<?php

namespace MatthiasMullie\Geo;

/**
 * @author Matthias Mullie <geo@mullie.eu>
 */
class Bounds {
    /**
     * @var Coordinate
     */
    public $ne;

    /**
     * @var Coordinate
     */
    public $sw;

    /**
     * @param Coordinate $ne North-east coordinate (max lat & lng)
     * @param Coordinate $sw South-west coordinate (min lat & lng)
     */
    public function __construct(Coordinate $ne, Coordinate $sw) {
        $this->ne = $ne;
        $this->sw = $sw;
    }
}
