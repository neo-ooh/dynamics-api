<?php
/**
 * Created by PhpStorm.
 * User: val
 * Date: 19/07/2018
 * Time: 21:43
 */

namespace App\Services;

use App\WeatherRecord;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use JsonException;

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
     * @return WeatherRecord|null
     * @throws GuzzleException
     * @throws JsonException
     */
	private function getRecord($endpoint, string $locale, string $country, string $province, string $city)
	{
		// Check cache for presence
		$cache = new WeatherCacherService();
		$cachedRecord = $cache->get($endpoint['id'], $country, $province, $city, $locale);

        // No record found in cache, pull one
        if (!$cachedRecord) {
            $recordContent = $this->pullRecord($endpoint['url'], $country, $province, $city, $locale);

            if(!$recordContent) {
                // Could not get record, stop here
                return null;
            }

            // Record found, store it
            return $cache->set($endpoint['id'], $country, $province, $city, $locale, $recordContent);
        }

        // We have a record, is it stale ?
        $isStale = $cachedRecord->updated_at->diffInSeconds(Carbon::now()) > config('app.api.lifespan');

        if($isStale) {
            // Pull a new record
            $recordContent = $this->pullRecord($endpoint['url'], $country, $province, $city, $locale);

            if($recordContent) {
                // We have an updated record, remove previous one
                $cache->delete($endpoint['id'], $country, $province, $city, $locale);
                // And store the new one
                $cachedRecord = $cache->set($endpoint['id'], $country, $province, $city, $locale, $recordContent);
            }
        }

		return $cachedRecord;
	}

	private function buildURL(string $url, string $country, string $province, string $city, string $locale)
	{
        // Sanitize city name
        $city = strtolower(str_replace(["'", " "], ["", "-"], $city));

		$url .= "/" . $country;
		$url .= "/" . $province;
		$url .= "/" . $city;
		$url .= "?user_key=" . config('app.api.meteoMediaKey');
		$url .= "&locale=" . $locale;

		return $url;
	}

    /**
     * @param string $url
     * @param string $country
     * @param string $province
     * @param string $city
     * @param string $locale
     * @return array|null
     * @throws GuzzleException
     * @throws JsonException
     */
    protected function pullRecord(string $url, string $country, string $province, string $city, string $locale) {
        // Try to get a new version of the record
        $url = $this->buildURL($url, $country, $province, $city, $locale);

        $recordContent = null;
        $client = new Client();
        try {
            $res = $client->request('GET', $url);

            if ($res->getStatusCode() === 200) {
                $recordContent = json_decode($res->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            }

        } catch (ClientException $e) {
            Log::error($e->getMessage());
        }

        return $recordContent;
    }
}
