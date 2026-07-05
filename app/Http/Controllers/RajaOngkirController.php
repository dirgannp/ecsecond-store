<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Helper;
use Illuminate\Http\Request;

class RajaOngkirController extends Controller
{
    public function __construct(protected RajaOngkirService $rajaOngkir)
    {
    }

    public function destinations(Request $request)
    {
        if (!$this->rajaOngkir->isConfigured()) {
            return response()->json([
                'message' => 'Live RajaOngkir is not configured.',
                'data' => [],
            ], 422);
        }

        $validated = $request->validate([
            'search' => 'required|string|min:3|max:100',
        ]);

        $destinations = collect($this->rajaOngkir->searchDomesticDestinations($validated['search']))
            ->map(function ($destination) {
                $labelParts = array_filter([
                    $destination['subdistrict_name'] ?? $destination['district_name'] ?? null,
                    $destination['city_name'] ?? null,
                    $destination['province_name'] ?? null,
                    $destination['zip_code'] ?? null,
                ]);

                return [
                    'id' => $destination['id'] ?? $destination['destination_id'] ?? null,
                    'label' => implode(', ', $labelParts),
                ];
            })
            ->filter(fn ($destination) => filled($destination['id']) && filled($destination['label']))
            ->values();

        return response()->json([
            'data' => $destinations,
        ]);
    }

    public function rates(Request $request)
    {
        if (!$this->rajaOngkir->isConfigured()) {
            return response()->json([
                'message' => 'Live RajaOngkir is not configured.',
                'data' => [],
            ], 422);
        }

        $validated = $request->validate([
            'destination_id' => 'required|integer',
        ]);

        $weight = Helper::cartWeight();
        $rates = collect($this->rajaOngkir->calculateDomesticCost(
            (int) $validated['destination_id'],
            $weight,
            implode(':', $this->rajaOngkir->allowedCourierCodes())
        ))
            ->filter(function ($rate) {
                return in_array(strtolower((string) ($rate['code'] ?? '')), $this->rajaOngkir->allowedCourierCodes(), true);
            })
            ->map(function ($rate) {
                return [
                    'courier_code' => strtolower((string) ($rate['code'] ?? '')),
                    'courier_name' => strtolower((string) ($rate['code'] ?? '')) === 'lion'
                        ? 'Lion Parcel'
                        : strtoupper((string) ($rate['code'] ?? '')),
                    'service' => $rate['service'] ?? '',
                    'description' => $rate['description'] ?? '',
                    'cost' => (float) ($rate['cost'] ?? 0),
                    'etd' => $rate['etd'] ?? '',
                ];
            })
            ->values();

        return response()->json([
            'data' => $rates,
        ]);
    }
}
