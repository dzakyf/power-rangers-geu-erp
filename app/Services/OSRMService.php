<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OSRMService
{
    private $client;
    private $baseUrl = 'https://router.project-osrm.org/route/v1/driving/';
    private $baseUrlHCGA;
    private $verifySsl;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
        
        $this->baseUrlHCGA = env('OSRM_BASE_URL', 'https://router.project-osrm.org/route/v1/driving/');

        // Konfigurasi SSL verification:
        // Di lingkungan local / development, nonaktifkan verify SSL (false) agar tidak terkena cURL error 60
        // Di production dapat diaktifkan, atau dikontrol via env OSRM_VERIFY_SSL
        $verifyEnv = env('OSRM_VERIFY_SSL');
        if ($verifyEnv !== null) {
            $this->verifySsl = filter_var($verifyEnv, FILTER_VALIDATE_BOOLEAN);
        } else {
            $this->verifySsl = (config('app.env') === 'production');
        }

        $this->client = new Client([
            'timeout' => 15,
            'verify'  => $this->verifySsl,
            'headers' => [
                'User-Agent' => 'HCGA-ERP/1.0',
            ],
        ]);
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

    /**
     * Get route between multiple waypoints.
     *
     * @param array $coordinates [[lng, lat], [lng, lat], ...]
     * @return array { distance_km, duration_min, encoded_polyline, is_fallback }
     */
    public function getRouteHCGA(array $coordinates): array
    {
        $coordStr = implode(';', array_map(function ($c) {
            return $c[0] . ',' . $c[1];
        }, $coordinates));

        $url = $this->baseUrlHCGA . $coordStr . '?overview=full&geometries=polyline&steps=false';

        try {
            $response = $this->client->get($url);
        } catch (\Throwable $e) {
            // Jika terjadi cURL error 60 (SSL certificate problem), coba sekali lagi dengan verify => false
            if (strpos($e->getMessage(), 'cURL error 60') !== false || stripos($e->getMessage(), 'SSL certificate') !== false) {
                try {
                    $response = $this->client->get($url, ['verify' => false]);
                } catch (\Throwable $e2) {
                    Log::warning('OSRMService retry without SSL failed: ' . $e2->getMessage());
                    return $this->calculateHaversineFallback($coordinates);
                }
            } else {
                Log::warning('OSRMService call failed: ' . $e->getMessage() . '. Menggunakan fallback Haversine.');
                return $this->calculateHaversineFallback($coordinates);
            }
        }

        $body = $response ? $response->getBody()->getContents() : '';
        $data = json_decode($body, true);

        if (!isset($data['routes'][0])) {
            Log::warning('OSRM: No route found in response payload. Menggunakan fallback Haversine.');
            return $this->calculateHaversineFallback($coordinates);
        }

        $route = $data['routes'][0];

        return [
            'distance_km'        => round($route['distance'] / 1000, 2),
            'duration_min'       => round($route['duration'] / 60, 1),
            'encoded_polyline'   => $route['geometry'] ?? '',
            'is_fallback'        => false,
        ];
    }

    /**
     * Menghitung estimasi jarak & durasi rute menggunakan rumus Haversine
     * sebagai fallback andal jika server OSRM offline / tidak dapat diakses / SSL bermasalah.
     *
     * @param array $coordinates [[lng, lat], [lng, lat], ...]
     * @return array
     */
    public function calculateHaversineFallback(array $coordinates): array
    {
        if (count($coordinates) < 2) {
            throw new \RuntimeException('Koordinat tidak lengkap untuk menghitung rute');
        }

        $totalKm = 0.0;
        for ($i = 0; $i < count($coordinates) - 1; $i++) {
            $lng1 = (float) $coordinates[$i][0];
            $lat1 = (float) $coordinates[$i][1];
            $lng2 = (float) $coordinates[$i + 1][0];
            $lat2 = (float) $coordinates[$i + 1][1];

            $earthRadius = 6371; // km
            $dLat = deg2rad($lat2 - $lat1);
            $dLng = deg2rad($lng2 - $lng1);

            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                 sin($dLng / 2) * sin($dLng / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $straightDist = $earthRadius * $c;

            // Koreksi rute jalan darat rata-rata ~1.25x dari jarak garis lurus
            $totalKm += ($straightDist * 1.25);
        }

        $distanceKm = round($totalKm, 2);
        // Estimasi waktu berkendara dengan kecepatan rata-rata 50 km/jam
        $durationMin = round(($distanceKm / 50) * 60, 1);

        return [
            'distance_km'        => $distanceKm,
            'duration_min'       => $durationMin,
            'encoded_polyline'   => '',
            'is_fallback'        => true,
        ];
    }
}
