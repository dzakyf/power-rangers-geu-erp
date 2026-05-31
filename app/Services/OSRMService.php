<?php

namespace App\Services;

use GuzzleHttp\Client;

class OSRMService
{
    private $client;
    private $baseUrl = 'https://router.project-osrm.org/route/v1/driving/';

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
    }

    /**
     * Get route between multiple waypoints.
     *
     * @param array $coordinates [[lng, lat], [lng, lat], ...]
     * @return array { distance_km, duration_min, encoded_polyline }
     */
    public function getRoute(array $coordinates): array
    {
        $coordStr = implode(';', array_map(function ($c) {
            return $c[0] . ',' . $c[1];
        }, $coordinates));

        $url = $this->baseUrl . $coordStr . '?overview=full&geometries=polyline&steps=false';

        $response = $this->client->get($url);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['routes'][0])) {
            throw new \RuntimeException('OSRM: No route found');
        }

        $route = $data['routes'][0];

        return [
            'distance_km'        => round($route['distance'] / 1000, 2),
            'duration_min'       => round($route['duration'] / 60, 1),
            'encoded_polyline'   => $route['geometry'],
        ];
    }
}
