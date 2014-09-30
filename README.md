# Geo

I strongly suggest reading some background information at http://www.mullie.eu/geographic-searches/

## Example usage: find anything in a 10km radius of Kortrijk railway station:

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

## License
Geo is [MIT](http://opensource.org/licenses/MIT) licensed.
