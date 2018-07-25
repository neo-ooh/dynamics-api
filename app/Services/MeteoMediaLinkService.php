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

	const ENDPOINT_OBS = ["id" => "obs", "url" => self::OBS_URL];
	const ENDPOINT_LNG = ["id" => "lng", "url" => self::LNG_URL];


	public function getNow(string ...$params) {
		return $this->getRecord(self::ENDPOINT_OBS, ...$params);
	}

	public function getNext(string ...$params) {
		return $this->getRecord(self::ENDPOINT_LNG, ...$params);
	}

	/**
	 * @param        $endpoint
	 * @param string $locale
	 * @param string $country
	 * @param string $province
	 * @param string $city
	 * @return The|mixed|void
	 */
	private function getRecord($endpoint, string $locale, string $country, string $province, string $city) {
		// Start by checking cache for presence
		$cache = new CacherService();
		$cachedRecord = $cache->get($endpoint['id'], $country, $province, $city, $locale);

		// Cached record was found, let's return it
		if($cachedRecord != null)
			return $cachedRecord;

		// No cached record, let's retrieve a new one
		$url = $this->buildURL($endpoint['url'], $country, $province, $city, $locale);

		$client = new Client();
		$res = $client->request('GET', $url);

		// Error
		if($res->getStatusCode() != 200)
			return; //TODO DO SOMETHING TO HANDLE ERRORS

		// Here's our response
		$response = $res->getBody()->getContents();

		// Let's cache it!
		$cache->set($endpoint['id'], $country, $province, $city, $locale, $response);

		return json_decode($response, true);
	}

	private function buildURL(string $url, string $country, string $province, string $city, string $locale) {
		$url .= "/" . $country;
		$url .= "/" . $province;
		$url .= "/" . $city;
		$url .= "?user_key=" . $_ENV['METEO_MEDIA_KEY'];
		$url .= "&locale=" . $locale;

		return $url;
	}
}
