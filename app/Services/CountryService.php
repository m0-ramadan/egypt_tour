<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CountryService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getCountryFromIp($ip)
    {
        try {
            $response = $this->client->get("http://ip-api.com/php/{$ip}");
            if ($response->getStatusCode() == 200) {
                $data = unserialize($response->getBody()->getContents());
                return $data['country'] ?? 'Other';
            }
        } catch (\Exception $e) {
            return 'other';
        }
    }
}
