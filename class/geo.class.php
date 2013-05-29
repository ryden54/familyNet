<?php
class Geo
{
	protected $apiList = array(
				'reverseGeocodingGoogle', 'reverseGeocodingGeonames'
			);

	protected $lat, $long;
	protected $label = false;

	/**
	 *
	 * @param array $exifGps
	 * @throws Exception
	 */
	public function __construct(array $exifGps) {
		$this->lat = $this->getGps($exifGps['GPSLatitude'], $exifGps['GPSLatitudeRef']);
		$this->long = $this->getGps($exifGps['GPSLongitude'], $exifGps['GPSLongitudeRef']);

		foreach ($this->apiList As $api) {
			try {
				$this->label = $this->$api();
				break;
			} catch (Exception $e) {

			}
		}
		if ($this->label === false) {
			throw new Exception("Failed to reverse geocode");
		}
	}

	public function getLabel() {
		return $this->label;
	}

	protected function reverseGeocodingGoogle() {
		$url =
				"http://maps.googleapis.com/maps/api/geocode/json?latlng=" . str_replace(',', '.', $this->lat) . ","
						. str_replace(',', '.', $this->long) . "&sensor=true";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_ENCODING, "UTF-8");
		$curlData = curl_exec($curl);
		curl_close($curl);
		$address = json_decode($curlData);
		if (isset($address->results) && is_array($address->results)) {
			$location = array();
			foreach ($address->results As $r) {
				foreach ($r->address_components AS $c) {
					if (in_array($c->types[0], array(
						'route', 'locality', 'country'
					)) === true) {
						$location[] = $c->long_name;
					}
				}

				break;
			}
			if (sizeof($location) > 0) {
				$location = utf8_encode(implode(', ', $location));
				return $location;
			}
		}
		throw new Exception("Failed to reverse geocode");

	}

	protected function reverseGeocodingGeonames() {
		$url =
				"http://api.geonames.org/findNearbyPlaceNameJSON?formatted=true&lat=" . str_replace(',', '.', $this->lat) . "&lng="
						. str_replace(',', '.', $this->long) . "&username=cyril.labbe&style=full";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_ENCODING, "UTF-8");
		$curlData = curl_exec($curl);
		curl_close($curl);
		$address = @json_decode($curlData);

		if (isset($address->geonames) === true && isset($address->geonames[0]) === true) {
			$location = $address->geonames[0]->name . ', ' . $address->geonames[0]->countryName;
			return $location;
		}
		throw new Exception("Failed to reverse geocode");
	}

	public function getGps($exifCoord, $hemi) {

		$degrees = count($exifCoord) > 0 ? $this->gps2Num($exifCoord[0]) : 0;
		$minutes = count($exifCoord) > 1 ? $this->gps2Num($exifCoord[1]) : 0;
		$seconds = count($exifCoord) > 2 ? $this->gps2Num($exifCoord[2]) : 0;

		$flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

		return $flip * ($degrees + $minutes / 60 + $seconds / 3600);

	}

	public function gps2Num($coordPart) {

		$parts = explode('/', $coordPart);

		if (count($parts) <= 0)
			return 0;

		if (count($parts) == 1)
			return $parts[0];

		return floatval($parts[0]) / floatval($parts[1]);
	}

}
