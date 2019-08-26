<?php

use Symfony\Component\DomCrawler\Crawler;

class Service
{
	public $signos = [
		'acuario' => ['nombre' => 'Acuario', 'rangoFechas' => '22 de enero - 18 de febrero', 'codHtml' => '&#9810;', 'elemento' => 'Aire', 'astro' => 'Urano / Saturno'],
		'aries' => ['nombre' => 'Aries', 'rangoFechas' => '21 de marzo - 21 de abril', 'codHtml' => '&#9800;', 'elemento' => 'Fuego', 'astro' => 'Marte / Plutón'],
		'cancer' => ['nombre' => 'Cáncer', 'rangoFechas' => '22 de junio - 21 de julio', 'codHtml' => '&#9803;', 'elemento' => 'Agua', 'astro' => 'Luna'],
		'capricornio' => ['nombre' => 'Capricornio', 'rangoFechas' => '22 de diciembre - 21 de enero', 'codHtml' => '&#9809;', 'elemento' => 'Tierra', 'astro' => 'Saturno'],
		'escorpion' => ['nombre' => 'Escorpión', 'rangoFechas' => '22 de octubre - 21 de noviembre', 'codHtml' => '&#9807;', 'elemento' => 'Agua', 'astro' => 'Plutón / Marte'],
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
	 * @author salvipascual
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function _main(Request $request, Response $response)
	{
		$response->setCache(360);
		$response->setTemplate("home.ejs", ["signos" => $this->signos]);
	}

	/**
	 * Get information for a sign
	 *
	 * @author salvipascual
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function _ver(Request $request, Response $response)
	{
		// no allow non-existant signs
		$sign = $request->input->data->sign;
		if( ! array_key_exists($sign, $this->signos)) {
			return $response->setTemplate('message.ejs', []);
		}

		// get the forecast for the sign
		$forecast = $this->getDailyForecast();

		// create a json object to send to the template
		$content = [
			"name" => $this->signos[$sign]['nombre'],
			"element" => $this->signos[$sign]['elemento'],
			"planet" => $this->signos[$sign]['astro'],
			"icon" => $this->signos[$sign]['codHtml'],
			"range" => $this->signos[$sign]['rangoFechas'],
			"text" => $forecast[$sign]
		];

		// create the response
		$response->setCache('day');
		$response->setTemplate("signo.ejs", $content);
		return $response;
	}

	/**
	 * Get information for all the signs
	 * 
	 * @author salvipascual
	 */
	private function getDailyForecast() 
	{
		// get content from cache
		$cache = Utils::getTempDir() . "horoscopo_" . date("Ymd") . ".cache";
		if(file_exists($cache)) $content = unserialize(file_get_contents($cache));

		// crawl the data from the web
		else {
			// get the html code of the page
			$page = file_get_contents("http://www.diariolasamericas.com/contenidos/horoscopo.html");

			// create a crawler from the text file
			$content = [];
			$crawler = new Crawler($page);
			$crawler->filter('section.horoscopo article.pt_0')->each(function($item) use(&$content){
				$signo = $item->filter('div.hname > span.color')->text();
				$signo = preg_replace("/Á/", 'A', $signo);
				$signo = preg_replace("/É/", 'E', $signo);
				$signo = preg_replace("/Ó/", 'O', $signo);
				$signo = strtolower($signo);
				$content[$signo] = $item->filter('div.htext')->text();
			});

			// create the cache
			file_put_contents($cache, serialize($content));
		}

		// return the forecast array
		return $content;
	}
}
