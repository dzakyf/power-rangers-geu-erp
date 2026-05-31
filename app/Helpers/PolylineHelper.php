<?php

namespace App\Helpers;

class PolylineHelper
{
    public static function decode(string $encoded): array
    {
        $points = [];
        $index = 0;
        $len = strlen($encoded);
        $lat = 0;
        $lng = 0;

        while ($index < $len) {
            $result = 0;
            $shift = 0;
            do {
                $byte = ord($encoded[$index++]) - 63;
                $result |= ($byte & 0x1f) << $shift;
                $shift += 5;
            } while ($byte >= 0x20);
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $dlat;

            $result = 0;
            $shift = 0;
            do {
                $byte = ord($encoded[$index++]) - 63;
                $result |= ($byte & 0x1f) << $shift;
                $shift += 5;
            } while ($byte >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;

            $points[] = [round($lat * 1e-5, 6), round($lng * 1e-5, 6)];
        }

        return $points;
    }

    public static function encode(array $points): string
    {
        $encoded = '';
        $prevLat = 0;
        $prevLng = 0;

        foreach ($points as $point) {
            $lat = round($point[0] * 1e5);
            $lng = round($point[1] * 1e5);
            $dlat = $lat - $prevLat;
            $dlng = $lng - $prevLng;
            $prevLat = $lat;
            $prevLng = $lng;
            $encoded .= self::encodeValue($dlat);
            $encoded .= self::encodeValue($dlng);
        }

        return $encoded;
    }

    private static function encodeValue(int $value): string
    {
        $value = ($value < 0) ? ~($value << 1) : ($value << 1);
        $result = '';
        while ($value >= 0x20) {
            $result .= chr((0x20 | ($value & 0x1f)) + 63);
            $value >>= 5;
        }
        $result .= chr($value + 63);
        return $result;
    }
}
