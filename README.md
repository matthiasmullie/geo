# Geo

[![Build status](https://img.shields.io/github/workflow/status/matthiasmullie/geo/test-suite?style=flat-square)](https://github.com/matthiasmullie/geo/actions/workflows/test.yml)
[![Code coverage](http://img.shields.io/codecov/c/gh/matthiasmullie/geo?style=flat-square)](https://codecov.io/gh/matthiasmullie/geo)
[![Latest version](http://img.shields.io/packagist/v/matthiasmullie/geo?style=flat-square)](https://packagist.org/packages/matthiasmullie/geo)
[![Downloads total](http://img.shields.io/packagist/dt/matthiasmullie/geo?style=flat-square)](https://packagist.org/packages/matthiasmullie/geo)
[![License](http://img.shields.io/packagist/l/matthiasmullie/geo?style=flat-square)](https://github.com/matthiasmullie/geo/blob/master/LICENSE)

I strongly suggest reading some background information at https://www.mullie.eu/geographic-searches/


## Usage

### Find anything in a 10km radius of Kortrijk railway station

The `Geo` class is particularly useful to do simple distance-based calculations:

```php
$geo = new Geo\Geo('km');

// coord of Kortrijk railway station
$coord = new Geo\Coordinate(50.824167, 3.263889);

// calculate bounding box of 10km around this coordinate
$bounds = $geo->bounds($coord, 10);

/*
 * Now pass this on this the database, so it executes a query like:
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
 * Assume we have the database results in a variable called $results
 */

// now weed out entries that fit in the bounding box, but not exactly in
// the radius we want them to be in
foreach ($results as $i => $result) {
    $resultCoord = new Geo\Coordinate($result['lat'], $result['lng']);

    // actual distance between source coordinate & result from DB
    $distance = $geo->distance($coord, $resultCoord);

    // if distance is too large, get rid of the result
    if ($distance > 10) {
        unset($results[$i]);
    }
}
```

### Cluster coordinates

The `Clusterer` class can be useful when dealing with a huge amount of locations.
E.g. you want to display thousands of locations on a map. Google Maps (and
probably others too) can create clusters too, but you'll still have to get all
locations to your frontend. Instead of outputting all coordinates and waiting
for the full list to load, we can do the clustering in PHP already & serve that
frontend of yours a more lightweight selection of coordinates!

```php
$clusterer = new Geo\Clusterer(
    // your viewport: in this case an approximation of bounding box around Belgium
    new Geo\Bounds(
        new Geo\Coordinate(51.474654, 6.344604),
        new Geo\Coordinate(49.482639, 2.471924)
    )
);

// create a matrix of about 12 cells (this may differ from 12, depending on
// the exact measurements of the bounding box)
$clusterer->setNumberOfClusters(12);

// start clustering after 2 locations in the same cell
$clusterer->setMinClusterLocations(2);

// add locations to clusterer
$clusterer->addCoordinate(new Geo\Coordinate(50.824167, 3.263889)); // Kortrijk railway station
$clusterer->addCoordinate(new Geo\Coordinate(51.035278, 3.709722)); // Gent-Sint-Pieters railway station
$clusterer->addCoordinate(new Geo\Coordinate(50.881365, 4.715682)); // Leuven railway station
$clusterer->addCoordinate(new Geo\Coordinate(50.860526, 4.361787)); // Brussels North railway station
$clusterer->addCoordinate(new Geo\Coordinate(50.836712, 4.337521)); // Brussels South railway station
$clusterer->addCoordinate(new Geo\Coordinate(50.845466, 4.357113)); // Brussels Central railway station
$clusterer->addCoordinate(new Geo\Coordinate(51.216227, 4.421180)); // Antwerpen Central railway station

// now get the results...
$clusterer->getClusters(); // returns 1 cluster: all 3 Brussels stations
$clusterer->getCoordinates(); // returns 4 non-clustered coordinates
```


## Installation

Simply add a dependency on `matthiasmullie/geo` to your composer.json file if you use [Composer](https://getcomposer.org/) to manage the dependencies of your project:

```sh
composer require matthiasmullie/geo
```

Although it's recommended to use Composer, you can actually include these files anyway you want.


## License
Geo is [MIT](http://opensource.org/licenses/MIT) licensed.
