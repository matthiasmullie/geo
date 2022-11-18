<?php

namespace MatthiasMullie\Geo\Tests;

use MatthiasMullie\Geo\Bounds;
use MatthiasMullie\Geo\Clusterer;
use MatthiasMullie\Geo\Coordinate;

class ClustererTest extends CompatTestCase
{
    public function dataProvider()
    {
        return array(
            array(
                'bounds' => new Bounds(
                    // approximation of bounding box around Belgium
                    new Coordinate(51.474654, 6.344604),
                    new Coordinate(49.482639, 2.471924)
                ),
                'coordinates' => array(
                    new Coordinate(50.824167, 3.263889, array('name' => 'Kortrijk')), // Kortrijk railway station
                    new Coordinate(51.035278, 3.709722), // Gent-Sint-Pieters railway station
                    new Coordinate(50.881365, 4.715682), // Leuven railway station
                    new Coordinate(50.860526, 4.361787), // Brussels North railway station
                    new Coordinate(50.836712, 4.337521), // Brussels South railway station
                    new Coordinate(50.845466, 4.357113, array('name' => 'Brussels')), // Brussels Central railway station
                    new Coordinate(51.216227, 4.421180), // Antwerpen Central railway station
                ),
            ),
        );
    }

    public function testClusterCoordinatesException()
    {
        $this->expectException('MatthiasMullie\\Geo\\Exception');

        $clusterer = new Clusterer(new Bounds(new Coordinate(1, 1), new Coordinate(0, 0)));
        $clusterer->setMinClusterLocations(1);
        $clusterer->setNumberOfClusters(10);

        $clusterer->addCoordinate(new Coordinate(0.5, 0.5));
        $clusterer->setSaveCoordinates(true);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testExtraData(Bounds $bounds, $coordinates)
    {
        $clusterer = new Clusterer($bounds);
        $clusterer->setNumberOfClusters(12);
        $clusterer->setMinClusterLocations(3);
        $clusterer->setSaveCoordinates(true);

        foreach ($coordinates as $coordinate) {
            $clusterer->addCoordinate($coordinate);
        }

        $clusters = $clusterer->getClusters();
        $coordinates = $clusterer->getCoordinates();

        $this->assertCount($clusters[0]->total, $clusters[0]->coordinates);
        $this->assertEquals(array('name' => 'Brussels'), $clusters[0]->coordinates[0]->data);

        $this->assertCount(4, $coordinates);
        $this->assertEquals(array('name' => 'Kortrijk'), $coordinates[0]->data);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testClustered(Bounds $bounds, $coordinates)
    {
        $clusterer = new Clusterer($bounds);
        $clusterer->setNumberOfClusters(12);

        // 1 location per cell is enough to form a cluster
        $clusterer->setMinClusterLocations(1);

        foreach ($coordinates as $coordinate) {
            $clusterer->addCoordinate($coordinate);
        }

        // all coordinates should be clustered
        // the 3 Brussels railway stations should form 1 cluster
        $clusters = $clusterer->getClusters();
        $this->assertCount(count($coordinates) - 2, $clusters);
        $this->assertEquals(3, $clusters[3]->total);
        $this->assertCount(0, $clusterer->getCoordinates());
        // cluster must also have empty coordinate array
        $this->assertCount(0, $clusters[0]->coordinates);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testPartiallyClustered(Bounds $bounds, $coordinates)
    {
        $clusterer = new Clusterer($bounds);
        $clusterer->setNumberOfClusters(12);

        // some coordinates should be clustered, as soon as there's > 1 per
        // matrix cell
        $clusterer->setMinClusterLocations(2);

        foreach ($coordinates as $coordinate) {
            $clusterer->addCoordinate($coordinate);
        }

        // the 3 Brussels railway stations should be clustered, rest should be
        // returned as single coordinates
        $clusters = $clusterer->getClusters();
        $this->assertCount(1, $clusters);
        $this->assertEquals(3, $clusters[0]->total);
        $this->assertCount(count($coordinates) - 3, $clusterer->getCoordinates());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testUnclustered(Bounds $bounds, $coordinates)
    {
        $clusterer = new Clusterer($bounds);
        $clusterer->setNumberOfClusters(12);

        // make sure coordinates aren't clustered!
        $clusterer->setMinClusterLocations(99);

        foreach ($coordinates as $coordinate) {
            $clusterer->addCoordinate($coordinate);
        }

        // no clusters, just all coordinates
        $this->assertCount(0, $clusterer->getClusters());
        $this->assertSameSize($coordinates, $clusterer->getCoordinates());
    }
}
