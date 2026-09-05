<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class InfoIpController extends Controller
{
    public function getCountryAndCurrency(Request $request)
    {
        $ip = $request->ip();

        $client = new Client();

        try {

            $ipApiUrl = "http://ip-api.com/json/{$ip}";
            $ipResponse = $client->get($ipApiUrl);
            $ipData = json_decode($ipResponse->getBody(), true);

            if (($ipData['status'] ?? null) !== 'success' || empty($ipData['country'])) {
                return response()->json(['error' => 'Unable to detect country from IP'], 400);
            }

            $countryName = $ipData['country'];

            $countryApiUrl = "https://restcountries.com/v3.1/name/{$countryName}?fullText=true";
            $countryResponse = $client->get($countryApiUrl);
            $countryData = json_decode($countryResponse->getBody(), true);

            if (empty($countryData)) {
                return response()->json(['error' => 'Country not found'], 404);
            }

            $currencyCode = array_key_first($countryData[0]['currencies']);
            $currencyName = $countryData[0]['currencies'][$currencyCode]['name'] ?? 'Unknown';
            $currencySymbol = $countryData[0]['currencies'][$currencyCode]['symbol'] ?? '';

            $response = [

                'country' => $countryName,
                'currency' => [
                    'code' => $currencyCode,
                    'name' => $currencyName,
                    'symbol' => $currencySymbol,
                ],
            ];

            return response()->json($response, 200);
        } catch (RequestException $e) {
            return response()->json(['error' => 'Failed to fetch data: ' . $e->getMessage()], 500);
        }
    }
}
