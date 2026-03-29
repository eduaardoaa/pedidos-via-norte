<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeolocationController extends Controller
{
    public function reverseGeocode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];

        /*
        |--------------------------------------------------------------------------
        | Cache por coordenada aproximada
        |--------------------------------------------------------------------------
        | Arredondando para 5 casas, evitamos várias consultas para pontos
        | praticamente iguais (ex.: usuário parado no mesmo local).
        */
        $cacheLatitude = round($latitude, 5);
        $cacheLongitude = round($longitude, 5);
        $cacheKey = "reverse_geocode:{$cacheLatitude}:{$cacheLongitude}";

        $result = Cache::remember($cacheKey, now()->addDays(7), function () use ($latitude, $longitude) {
            try {
                $response = Http::acceptJson()
                    ->withHeaders([
                        'User-Agent' => 'Vianorte/1.0',
                    ])
                    ->connectTimeout(2)
                    ->timeout(4)
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'jsonv2',
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'addressdetails' => 1,
                    ]);

                if (! $response->successful()) {
                    return null;
                }

                $json = $response->json();

                if (! is_array($json)) {
                    return null;
                }

                return $json;
            } catch (\Throwable $e) {
                return null;
            }
        });

        if (! $result) {
            return response()->json([
                'message' => 'Não foi possível obter o endereço da localização.',
            ], 422);
        }

        $address = $result['address'] ?? [];

        $street = $address['road']
            ?? $address['pedestrian']
            ?? $address['residential']
            ?? $address['footway']
            ?? $address['path']
            ?? null;

        $number = $address['house_number'] ?? null;

        $neighborhood = $address['suburb']
            ?? $address['neighbourhood']
            ?? $address['quarter']
            ?? $address['city_district']
            ?? null;

        $city = $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['municipality']
            ?? null;

        $state = $address['state'] ?? null;
        $zipcode = $address['postcode'] ?? null;

        $fullAddressParts = array_filter([
            $street ? trim($street . ($number ? ', ' . $number : '')) : null,
            $neighborhood,
            $city,
            $state,
            $zipcode,
        ]);

        return response()->json([
            'street' => $street,
            'number' => $number,
            'neighborhood' => $neighborhood,
            'city' => $city,
            'state' => $state,
            'zipcode' => $zipcode,
            'full_address' => implode(' - ', $fullAddressParts),
        ]);
    }
}