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
class Bounds
{
    /**
     * @var Coordinate
     */
    public $ne;

    /**
     * @var Coordinate
     */
    public $sw;

    /**
     * @param Coordinate $ne North-east coordinate
     * @param Coordinate $sw South-west coordinate
     */
    public function __construct(Coordinate $ne, Coordinate $sw)
    {
        $this->ne = $ne;
        $this->sw = $sw;
    }
}
