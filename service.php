<?php

use Apretaste\Request;
use Apretaste\Response;
use Framework\Crawler;
use Apretaste\Challenges;

class Service
{
	public $signos = [
		'acuario' => ['nombre' => 'Acuario', 'rangoFechas' => '22 de enero - 18 de febrero', 'codHtml' => '&#9810;', 'elemento' => 'Aire', 'astro' => 'Urano / Saturno'],
		'aries' => ['nombre' => 'Aries', 'rangoFechas' => '21 de marzo - 21 de abril', 'codHtml' => '&#9800;', 'elemento' => 'Fuego', 'astro' => 'Marte / Plutón'],
		'cancer' => ['nombre' => 'Cáncer', 'rangoFechas' => '22 de junio - 21 de julio', 'codHtml' => '&#9803;', 'elemento' => 'Agua', 'astro' => 'Luna'],
		'capricornio' => ['nombre' => 'Capricornio', 'rangoFechas' => '22 de diciembre - 21 de enero', 'codHtml' => '&#9809;', 'elemento' => 'Tierra', 'astro' => 'Saturno'],
		'escorpio' => ['nombre' => 'Escorpio', 'rangoFechas' => '22 de octubre - 21 de noviembre', 'codHtml' => '&#9807;', 'elemento' => 'Agua', 'astro' => 'Plutón / Marte'],
		'geminis' => ['nombre' => 'Géminis', 'rangoFechas' => '22 de mayo - 21 de junio', 'codHtml' => '&#9802;', 'elemento' => 'Aire', 'astro' => 'Mercurio'],
		'leo' => ['nombre' => 'Leo', 'rangoFechas' => '22 de julio - 21 de agosto', 'codHtml' => '&#9804;', 'elemento' => 'Fuego', 'astro' => 'Sol'],
		'libra' => ['nombre' => 'Libra', 'rangoFechas' => '24 de septiembre - 21 de octubre', 'codHtml' => '&#9806;', 'elemento' => 'Aire', 'astro' => 'Venus'],
		'piscis' => ['nombre' => 'Piscis', 'rangoFechas' => '19 de febrero - 20 de marzo', 'codHtml' => '&#9811;', 'elemento' => 'Agua', 'astro' => ' 	Neptuno / Júpiter'],
		'sagitario' => ['nombre' => 'Sagitario', 'rangoFechas' => '22 de noviembre - 21 de diciembre 	', 'codHtml' => '&#9808;', 'elemento' => ' 	Fuego', 'astro' => 'Júpiter'],
		'tauro' => ['nombre' => 'Tauro', 'rangoFechas' => '22 de abril - 21 de mayo', 'codHtml' => '&#9801;', 'elemento' => 'Tierra', 'astro' => 'Venus / Tierra'],
		'virgo' => ['nombre' => 'Virgo', 'rangoFechas' => '22 de agosto - 23 de septiembre', 'codHtml' => '&#9805;', 'elemento' => 'Tierra', 'astro' => 'Mercurio'],
	];

	/**
	 * Display the list of signs
	 *
	 * @param Request $request
	 * @param Response $response
	 *
	 * @return void
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _main(Request $request, Response &$response)
	{
		$response->setCache('year');
		$response->setTemplate('home.ejs', ['signos' => $this->signos]);

		// challenges
		Challenges::complete('horoscopo', $request->person->id);
	}

	/**
	 * Get information for a sign
	 *
	 * @param Request $request
	 * @param Response $response
	 *
	 * @throws \Framework\Alert
	 * @author salvipascual
	 */
	public function _ver(Request $request, Response &$response)
	{
		// no allow non-existant signs
		$sign = $request->input->data->sign;
		if (! array_key_exists($sign, $this->signos)) {
			$response->setTemplate('message.ejs', []);
			return;
		}

		// get the forecast for the sign
		$forecast = $this->getDailyForecast();

		// create a json object to send to the template
		$content = [
				'name' => $this->signos[$sign]['nombre'],
				'element' => $this->signos[$sign]['elemento'],
				'planet' => $this->signos[$sign]['astro'],
				'icon' => $this->signos[$sign]['codHtml'],
				'range' => $this->signos[$sign]['rangoFechas'],
				'text' => $forecast[$sign]
		];

		// create the response
		$response->setCache('day');
		$response->setTemplate('signo.ejs', $content);
	}

	/**
	 * Get information for all the signs
	 *
	 * @author salvipascual
	 */
	private function getDailyForecast()
	{
		// get content from cache
		$content = self::loadCache();
		if ($content === null) {
			// get the html code of the page
			Crawler::start('https://www.clarin.com/horoscopo');

			// get horoscopo for each day
			$content = [];
			foreach ($this->signos as $sign => $values) {
				$signoNombre = $values['nombre'];
				$signText = Crawler::filter('#data-'.$signoNombre)->html();
				$content[$sign] = $signText;
			}

			// create the cache
			self::saveCache($content);
		}

		// return the forecast array
		return $content;
	}

	/**
	 * Get cache file name
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public static function getCacheFileName($name): string
	{
		return TEMP_PATH.'cache/horoscopo_'.$name.'_'.date('Ymd').'.tmp';
	}

	/**
	 * Load cache
	 *
	 * @param $name
	 * @param null $cacheFile
	 *
	 * @return bool|mixed
	 */
	public static function loadCache($name = 'cache', &$cacheFile = null)
	{
		$data = null;
		$cacheFile = self::getCacheFileName($name);
		if (file_exists($cacheFile)) {
			$data = unserialize(file_get_contents($cacheFile));
		}
		return $data;
	}

	/**
	 * Save cache
	 *
	 * @param $name
	 * @param $data
	 * @param null $cacheFile
	 */
	public static function saveCache($data, $name = 'cache', &$cacheFile = null)
	{
		$cacheFile = self::getCacheFileName($name);
		file_put_contents($cacheFile, serialize($data));
	}
}
