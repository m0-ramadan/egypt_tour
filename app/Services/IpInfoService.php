<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class IpInfoService
{
    public function getCountryAndCurrency(string $ip): array
    {
        $client = new Client();

        try {
            // IP API
            $ipApiUrl = "http://ip-api.com/json/{$ip}";
            $ipResponse = $client->get($ipApiUrl);
            $ipData = json_decode($ipResponse->getBody(), true);

            if (($ipData['status'] ?? null) !== 'success') {
                return [];
            }

            $countryName = $ipData['country'] ?? null;
            if (!$countryName) {
                return [];
            }

            // Country API
            $countryApiUrl = "https://restcountries.com/v3.1/name/{$countryName}?fullText=true";
            $countryResponse = $client->get($countryApiUrl);
            $countryData = json_decode($countryResponse->getBody(), true);

            if (empty($countryData)) {
                return [];
            }

            $currencyCode = array_key_first($countryData[0]['currencies']);
            $currency = $countryData[0]['currencies'][$currencyCode] ?? [];

            return [
                'country' => $countryName,
                'currency' => [
                    'code'   => $currencyCode,
                    'name'   => $currency['name'] ?? 'Unknown',
                    'symbol' => $currency['symbol'] ?? '',
                ],
            ];
        } catch (RequestException $e) {
            return [];
        }
    }
}
