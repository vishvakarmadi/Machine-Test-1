<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to fetch weather data from WeatherAPI.com.
 * Falls back to mock data if the API key is missing or the request fails.
 */
class WeatherService
{
    /**
     * Retrieve current weather and 5-day forecast for a given query (city or coordinates).
     *
     * @param string $query City name or latitude,longitude coordinates
     * @return array<string, mixed> Structured weather data
     */
    public function getWeather(string $query): array
    {
        $apiKey = config('services.weather.key');
        $baseUrl = config('services.weather.base_url', 'https://api.weatherapi.com/v1');

        if (! empty($apiKey)) {
            try {
                $response = Http::timeout(5)
                    ->retry(2, 100)
                    ->get("{$baseUrl}/forecast.json", [
                        'key' => $apiKey,
                        'q' => $query,
                        'days' => 5,
                        'aqi' => 'no',
                        'alerts' => 'no',
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->formatApiResponse($data);
                }

                Log::warning('WeatherAPI request failed, falling back to mock mode.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (\Exception $e) {
                Log::error('WeatherService Exception, falling back to mock mode.', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        // Fallback or demo mode when API Key is empty or API fails
        return $this->generateMockWeather($query);
    }

    /**
     * Format the API response to match our frontend structure.
     */
    protected function formatApiResponse(array $data): array
    {
        $forecastDays = [];

        if (isset($data['forecast']['forecastday'])) {
            foreach ($data['forecast']['forecastday'] as $day) {
                $forecastDays[] = [
                    'date' => $day['date'],
                    'temp_max_c' => (float) $day['day']['maxtemp_c'],
                    'temp_max_f' => (float) $day['day']['maxtemp_f'],
                    'temp_min_c' => (float) $day['day']['mintemp_c'],
                    'temp_min_f' => (float) $day['day']['mintemp_f'],
                    'condition' => $day['day']['condition']['text'],
                    'icon' => $day['day']['condition']['icon'],
                ];
            }
        }

        return [
            'success' => true,
            'source' => 'api',
            'city' => $data['location']['name'] ?? 'Unknown',
            'country' => $data['location']['country'] ?? 'Unknown',
            'temperature_c' => (float) ($data['current']['temp_c'] ?? 0),
            'temperature_f' => (float) ($data['current']['temp_f'] ?? 0),
            'condition' => $data['current']['condition']['text'] ?? 'Unknown',
            'icon' => $data['current']['condition']['icon'] ?? '',
            'humidity' => (int) ($data['current']['humidity'] ?? 0),
            'wind_kph' => (float) ($data['current']['wind_kph'] ?? 0),
            'feelslike_c' => (float) ($data['current']['feelslike_c'] ?? 0),
            'feelslike_f' => (float) ($data['current']['feelslike_f'] ?? 0),
            'is_day' => (int) ($data['current']['is_day'] ?? 1),
            'uv' => (float) ($data['current']['uv'] ?? 0),
            'cloud' => (int) ($data['current']['cloud'] ?? 0),
            'pressure_mb' => (float) ($data['current']['pressure_mb'] ?? 1013),
            'visibility_km' => (float) ($data['current']['vis_km'] ?? 10),
            'date_time' => $data['location']['localtime'] ?? now()->toDateTimeString(),
            'forecast' => $forecastDays,
        ];
    }

    /**
     * Generate mock weather data if the API key is missing or failed.
     * Returns deterministic data based on the city/coordinates hash.
     */
    protected function generateMockWeather(string $query): array
    {
        $cleanQuery = strtolower(trim($query));
        $hash = abs(crc32($cleanQuery));

        // Is it coordinate-based? e.g. "12.9716,77.5946"
        $isCoords = preg_match('/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/', $cleanQuery);
        
        $city = 'Bangalore';
        $country = 'India';

        if ($isCoords) {
            $city = 'Current Location';
            $country = 'Detected Coordinates';
        } else {
            // Title case the queried city name
            $city = ucwords($cleanQuery);
            $country = 'India';

            // Custom defaults for major standard cities
            if (str_contains($cleanQuery, 'bangalore') || str_contains($cleanQuery, 'bengaluru')) {
                $city = 'Bangalore';
                $country = 'India';
            } elseif (str_contains($cleanQuery, 'chennai')) {
                $city = 'Chennai';
                $country = 'India';
            } elseif (str_contains($cleanQuery, 'mumbai')) {
                $city = 'Mumbai';
                $country = 'India';
            } elseif (str_contains($cleanQuery, 'delhi')) {
                $city = 'Delhi';
                $country = 'India';
            } elseif (str_contains($cleanQuery, 'london')) {
                $city = 'London';
                $country = 'United Kingdom';
            } elseif (str_contains($cleanQuery, 'new york')) {
                $city = 'New York';
                $country = 'United States';
            } elseif (str_contains($cleanQuery, 'tokyo')) {
                $city = 'Tokyo';
                $country = 'Japan';
            } elseif (str_contains($cleanQuery, 'sydney')) {
                $city = 'Sydney';
                $country = 'Australia';
            } elseif (str_contains($cleanQuery, 'paris')) {
                $city = 'Paris';
                $country = 'France';
            }
        }

        // Define a set of conditions
        $conditions = [
            ['text' => 'Sunny', 'icon' => '//cdn.weatherapi.com/weather/64x64/day/113.png', 'is_day' => 1],
            ['text' => 'Partly cloudy', 'icon' => '//cdn.weatherapi.com/weather/64x64/day/116.png', 'is_day' => 1],
            ['text' => 'Cloudy', 'icon' => '//cdn.weatherapi.com/weather/64x64/day/119.png', 'is_day' => 1],
            ['text' => 'Overcast', 'icon' => '//cdn.weatherapi.com/weather/64x64/day/122.png', 'is_day' => 1],
            ['text' => 'Patchy rain possible', 'icon' => '//cdn.weatherapi.com/weather/64x64/day/176.png', 'is_day' => 1],
            ['text' => 'Light drizzle', 'icon' => '//cdn.weatherapi.com/weather/64x64/day/266.png', 'is_day' => 1],
            ['text' => 'Heavy rain', 'icon' => '//cdn.weatherapi.com/weather/64x64/day/308.png', 'is_day' => 1],
            ['text' => 'Thundery outbreaks possible', 'icon' => '//cdn.weatherapi.com/weather/64x64/day/389.png', 'is_day' => 1],
            ['text' => 'Clear', 'icon' => '//cdn.weatherapi.com/weather/64x64/night/113.png', 'is_day' => 0],
        ];

        // Pick a base profile based on city name to keep it realistic
        $baseTemp = 20.0;
        $humidity = 60;
        $windSpeed = 10.0;
        $uv = 4.0;
        $conditionIndex = $hash % count($conditions);

        if ($country === 'India') {
            if ($city === 'Chennai') {
                $baseTemp = 32.5;
                $humidity = 78;
                $windSpeed = 14.2;
                $uv = 9.0;
                $conditionIndex = $hash % 3; // Mostly Sunny/Partly Cloudy
            } elseif ($city === 'Delhi') {
                $baseTemp = 36.0;
                $humidity = 35;
                $windSpeed = 8.5;
                $uv = 10.0;
                $conditionIndex = $hash % 2; // Sunny/Partly Cloudy
            } elseif ($city === 'Mumbai') {
                $baseTemp = 30.0;
                $humidity = 82;
                $windSpeed = 15.0;
                $uv = 8.0;
                $conditionIndex = ($hash % 4) + 1; // More cloudy/patchy rain
            } else { // Bangalore / Default India
                $baseTemp = 26.5;
                $humidity = 65;
                $windSpeed = 12.0;
                $uv = 7.0;
                $conditionIndex = $hash % 5;
            }
        } elseif ($city === 'London') {
            $baseTemp = 13.5;
            $humidity = 85;
            $windSpeed = 18.0;
            $uv = 2.0;
            $conditionIndex = ($hash % 5) + 2; // More rainy/overcast
        } elseif ($city === 'Tokyo') {
            $baseTemp = 18.0;
            $humidity = 55;
            $windSpeed = 9.0;
            $uv = 5.0;
            $conditionIndex = $hash % 6;
        }

        // Add some deterministic variance based on hash
        $variance = (($hash % 100) - 50) / 10.0; // -5.0 to +5.0
        $tempC = round($baseTemp + $variance, 1);
        $tempF = round(($tempC * 9 / 5) + 32, 1);

        $feelsLikeC = round($tempC + (($humidity > 70) ? 2.0 : -1.0), 1);
        $feelsLikeF = round(($feelsLikeC * 9 / 5) + 32, 1);

        $selectedCondition = $conditions[$conditionIndex];
        
        // Generate a 5-day forecast starting from today
        $forecast = [];
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $currentDayOfWeek = (int) now()->format('w');

        for ($i = 0; $i < 5; $i++) {
            $futureDate = now()->addDays($i);
            $dayHash = abs(crc32($city . $futureDate->toDateString()));
            
            // Forecast temperature should vary reasonably
            $dayVariance = (($dayHash % 60) - 30) / 10.0; // -3.0 to +3.0
            $maxC = round($tempC + $dayVariance + 2.0, 1);
            $minC = round($tempC + $dayVariance - 4.0, 1);
            
            $maxF = round(($maxC * 9 / 5) + 32, 1);
            $minF = round(($minC * 9 / 5) + 32, 1);

            $dayCondition = $conditions[$dayHash % count($conditions)];

            $forecast[] = [
                'date' => $futureDate->toDateString(),
                'temp_max_c' => $maxC,
                'temp_max_f' => $maxF,
                'temp_min_c' => $minC,
                'temp_min_f' => $minF,
                'condition' => $dayCondition['text'],
                'icon' => $dayCondition['icon'],
            ];
        }

        return [
            'success' => true,
            'source' => 'mock',
            'city' => $city,
            'country' => $country,
            'temperature_c' => $tempC,
            'temperature_f' => $tempF,
            'condition' => $selectedCondition['text'],
            'icon' => $selectedCondition['icon'],
            'humidity' => $humidity,
            'wind_kph' => $windSpeed,
            'feelslike_c' => $feelsLikeC,
            'feelslike_f' => $feelsLikeF,
            'is_day' => $selectedCondition['is_day'],
            'uv' => $uv,
            'cloud' => ($hash % 40) + ($selectedCondition['text'] === 'Sunny' ? 0 : 40),
            'pressure_mb' => 1008.0 + ($hash % 15),
            'visibility_km' => ($selectedCondition['text'] === 'Heavy rain' ? 4.0 : 10.0),
            'date_time' => now()->format('Y-m-d H:i'),
            'forecast' => $forecast,
        ];
    }
}
