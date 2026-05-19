<?php

use App\Services\WeatherService;

test('it validates missing city parameter in weather search', function () {
    $response = $this->getJson(route('weather.search'));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['city']);
});

test('it validates empty city parameter in weather search', function () {
    $response = $this->getJson(route('weather.search', ['city' => '']));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['city']);
});

test('it returns successful weather data for a valid city search', function () {
    $response = $this->getJson(route('weather.search', ['city' => 'Bangalore']));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'source',
            'city',
            'country',
            'temperature_c',
            'temperature_f',
            'condition',
            'icon',
            'humidity',
            'wind_kph',
            'feelslike_c',
            'feelslike_f',
            'is_day',
            'uv',
            'cloud',
            'pressure_mb',
            'visibility_km',
            'date_time',
            'forecast' => [
                '*' => [
                    'date',
                    'temp_max_c',
                    'temp_max_f',
                    'temp_min_c',
                    'temp_min_f',
                    'condition',
                    'icon',
                ],
            ],
        ])
        ->assertJsonPath('success', true)
        ->assertJsonPath('city', 'Bangalore')
        ->assertJsonPath('country', 'India');
});

test('it validates missing coordinates parameters', function () {
    $response = $this->getJson(route('weather.coords'));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lat', 'lon']);
});

test('it validates invalid latitude and longitude ranges', function () {
    $response = $this->getJson(route('weather.coords', [
        'lat' => 100.5,
        'lon' => 200.1,
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lat', 'lon']);
});

test('it returns successful weather data for valid coordinates', function () {
    $response = $this->getJson(route('weather.coords', [
        'lat' => 12.9716,
        'lon' => 77.5946,
    ]));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'source',
            'city',
            'country',
            'temperature_c',
            'temperature_f',
            'condition',
            'icon',
            'humidity',
            'wind_kph',
            'feelslike_c',
            'feelslike_f',
            'is_day',
            'uv',
            'cloud',
            'pressure_mb',
            'visibility_km',
            'date_time',
            'forecast',
        ])
        ->assertJsonPath('success', true)
        ->assertJsonPath('city', 'Current Location');
});
