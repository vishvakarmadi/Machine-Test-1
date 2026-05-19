<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Handles weather search and coordinate-based lookups
class WeatherController extends Controller
{
    /**
     * Create a new WeatherController instance.
     *
     * @param WeatherService $weatherService The weather service instance
     */
    public function __construct(
        protected WeatherService $weatherService
    ) {}

    /**
     * Search weather by city name.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city' => ['required', 'string', 'max:100'],
        ]);

        $data = $this->weatherService->getWeather($validated['city']);

        return response()->json($data);
    }

    /**
     * Retrieve weather details by coordinates.
     */
    public function coords(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $query = "{$validated['lat']},{$validated['lon']}";
        $data = $this->weatherService->getWeather($query);

        return response()->json($data);
    }
}
