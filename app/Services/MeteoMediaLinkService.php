<?php
/**
 * Created by PhpStorm.
 * User: val
 * Date: 19/07/2018
 * Time: 21:43
 */

namespace App\Services;

use GuzzleHttp\Client;

class MeteoMediaLinkService
{
	const METEO_MEDIA_URL = "http://wx.api.pelmorex.com/weather";
	const OBS_URL = self::METEO_MEDIA_URL . "/Observations";
	const LNG_URL = self::METEO_MEDIA_URL . "/LongTermForecasts";
	const HLY_URL = self::METEO_MEDIA_URL . "/HourlyForecasts";

	const ENDPOINT_OBS = ["id" => "obs", "url" => self::OBS_URL];
	const ENDPOINT_LNG = ["id" => "lng", "url" => self::LNG_URL];
    const ENDPOINT_HLY = ["id" => "hly", "url" => self::HLY_URL];

	public function getNow(string ...$params)
	{
		return $this->getRecord(self::ENDPOINT_OBS, ...$params);
	}

	public function getNext(string ...$params)
	{
		return $this->getRecord(self::ENDPOINT_LNG, ...$params);
	}

    public function getHourly(string ...$params)
    {
        return $this->getRecord(self::ENDPOINT_HLY, ...$params);
    }

	/**
	 * @param        $endpoint
	 * @param string $locale
	 * @param string $country
	 * @param string $province
	 * @param string $city
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	private function getRecord($endpoint, string $locale, string $country, string $province, string $city)
	{
//		\Log::info("Fetching record for ".$endpoint['id']." ".$country." ".$province." ".$city." ".$locale);
		// Check cache for presence
		$cache = new WeatherCacherService();
		$cachedRecord = $cache->get($endpoint['id'], $country, $province, $city, $locale);

		// Cached record was found, let's return it
		if ($cachedRecord != null) {
//			\Log::info("WeatherRecord found in DDB was ok.");
			return $cachedRecord;
		}

//		\Log::info("Fetching new record from API");

		// No cached record, let's retrieve a new one
		$url = $this->buildURL($endpoint['url'], $country, $province, $city, $locale);

		$client = new Client();
		$res = $client->request('GET', $url);

		// Error
		if ($res->getStatusCode() != 200) {
			return null;
		}

		// Here's our response
		$response = $res->getBody()->getContents();

		// Let's cache it!
		$cache->set($endpoint['id'], $country, $province, $city, $locale, $response);

		return json_decode($response, true);
	}

	private function buildURL(string $url, string $country, string $province, string $city, string $locale)
	{
		$url .= "/" . $country;
		$url .= "/" . $province;
		$url .= "/" . $city;
		$url .= "?user_key=" . config('app.api.meteoMediaKey');
		$url .= "&locale=" . $locale;

		return $url;
	}
}
