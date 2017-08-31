<?php

use Symfony\Component\DomCrawler\Crawler;

class Horoscopo extends Service
{
	public $signos = array(
		'acuario' => array('nombre' => 'Acuario', 'rangoFechas' => '22 de enero - 18 de febrero', 'codHtml' => '&#9810;', 'elemento' => 'Aire', 'astro' => 'Urano / Saturno'),
		'aries' => array('nombre' => 'Aries', 'rangoFechas' => '21 de marzo - 21 de abril', 'codHtml' => '&#9800;', 'elemento' => 'Fuego', 'astro' => 'Marte / Plut&oacute;n'),
		'cancer' => array('nombre' => 'C&aacute;ncer', 'rangoFechas' => '22 de junio - 21 de julio', 'codHtml' => '&#9803;', 'elemento' => 'Agua', 'astro' => 'Luna'),
		'capricornio' => array('nombre' => 'Capricornio', 'rangoFechas' => '22 de diciembre - 21 de enero', 'codHtml' => '&#9809;', 'elemento' => 'Tierra', 'astro' => 'Saturno'),
		'escorpion' => array('nombre' => 'Escorpi&oacute;n', 'rangoFechas' => '22 de octubre - 21 de noviembre', 'codHtml' => '&#9807;', 'elemento' => 'Agua', 'astro' => 'Plut&oacute;n / Marte'),
		'geminis' => array('nombre' => 'G&eacute;minis', 'rangoFechas' => '22 de mayo - 21 de junio', 'codHtml' => '&#9802;', 'elemento' => 'Aire', 'astro' => 'Mercurio'),
		'leo' => array('nombre' => 'Leo', 'rangoFechas' => '22 de julio - 21 de agosto', 'codHtml' => '&#9804;', 'elemento' => 'Fuego', 'astro' => 'Sol'),
		'libra' => array('nombre' => 'Libra', 'rangoFechas' => '24 de septiembre - 21 de octubre', 'codHtml' => '&#9806;', 'elemento' => 'Aire', 'astro' => 'Venus'),
		'piscis' => array('nombre' => 'Piscis', 'rangoFechas' => '19 de febrero - 20 de marzo', 'codHtml' => '&#9811;', 'elemento' => 'Agua', 'astro' => ' 	Neptuno / J&uacute;piter'),
		'sagitario' => array('nombre' => 'Sagitario', 'rangoFechas' => '22 de noviembre - 21 de diciembre 	', 'codHtml' => '&#9808;', 'elemento' => ' 	Fuego', 'astro' => 'J&uacute;piter'),
		'tauro' => array('nombre' => 'Tauro', 'rangoFechas' => '22 de abril - 21 de mayo', 'codHtml' => '&#9801;', 'elemento' => 'Tierra', 'astro' => 'Venus / Tierra'),
		'virgo' => array('nombre' => 'Virgo', 'rangoFechas' => '22 de agosto - 23 de septiembre', 'codHtml' => '&#9805;', 'elemento' => 'Tierra', 'astro' => 'Mercurio')
	);

	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{
		if (empty($request->query))
		{
			$response = new Response();
			$response->setCache();
			$response->setResponseSubject("&iquest;Cual es tu signo?");
			$response->createFromTemplate("selectSigno.tpl", array("signos" => $this->signos));
			return $response;
		}
		else
		{
			return $this->searchHoroscopoXsigno($request->query);
		}
	}

	/**
	 * Get the horoscpe by sign
	 */
	private function searchHoroscopoXsigno($query)
	{
		$nombSignos = array('acuario','aries','cancer','capricornio',"escorpion",'geminis','leo','libra','piscis','sagitario','tauro','virgo');
		$horoscopoXsigno = array();
		foreach ($nombSignos as $nombreSigno) {
			$horoscopoXsigno[strtoupper($nombreSigno)] = null;
		}

		// get and clean the argument
		$param = $query;
		$param = str_replace("\n", " ", $param);
		$param = str_replace("\r", "", $param);
		$param = trim(strtoupper($param));
		if ($param == "ESCORPIO") {$param = "ESCORPION";}
		if ($param == "PICIS") {$param = "PISCIS";}

		if (array_search(strtolower($param), $nombSignos) !== false)
		{
			// load from cache if exists
			$cacheFile = $this->utils->getTempDir() . date("Ymd") . "_horoscope1_today.tmp";

			if (file_exists($cacheFile)) {
				$page = file_get_contents($cacheFile);
			}else{
				// get the html code of the page
				$page = file_get_contents("http://www.diariolasamericas.com/contenidos/horoscopo.html");

				// save cache file for today
				file_put_contents($cacheFile, $page);
			}

			// create a crawler from the text file
			$crawler = new Crawler($page);
			$result = $crawler->filter('section.horoscopo article.pt_0');

			foreach ($result as $domElement) {
				$resultSigno = $domElement->nodeValue;
				$signoMostrar = trim(substr($resultSigno, 0, strpos($resultSigno, ':')));
				$signo = preg_replace("/Á/", 'A', $signoMostrar);
				$signo = preg_replace("/É/", 'E', $signo);
				$signo = preg_replace("/Ó/", 'O', $signo);
				$pronostico = substr($resultSigno, strpos($resultSigno, ':')+1);
				$patternRangoFechas = "/\d{1,2}\sde\s\w{1,}\s{0,1}-\s{0,1}\d{1,2}\sde\s\w{1,}\s{0,1}/u";
				$regexpmatch = preg_match($patternRangoFechas, $pronostico, $matches);
				$rango = $matches[0];
				$pronostico = "</br>".preg_replace($patternRangoFechas, '', $pronostico);

				$horoscopoXsigno[$signo] = array('signo' => $signoMostrar, 'rango' => $rango, 'pronostico' => $pronostico);
			}

			$cacheFile = $this->utils->getTempDir() . date("Ymd") . "_horoscope2_today.tmp";

			if (file_exists($cacheFile)) {
				$page = file_get_contents($cacheFile);
			}else{
				// get the html code of the page
				$page = file_get_contents("http://www.televen.com/horoscopo/");

				// save cache file for today
				file_put_contents($cacheFile, $page);
			}

			// create a crawler and get the text file
			$crawler = new Crawler($page);
			$result = $crawler->filter('div#accordion.panel-group div.panel.panel-default');

			foreach ($result as $domElement) {
				$resultSigno = $domElement->nodeValue;
				$signo = strtoupper(trim(substr($resultSigno, 0, strpos($resultSigno, '|'))));
				$signo = preg_replace("/á/", 'A', $signo);
				$signo = preg_replace("/é/", 'E', $signo);
				$signo = preg_replace("/ó/", 'O', $signo);
				if ($signo == "ESCORPIO") {$signo = "ESCORPION";}

				$pronostico = substr($resultSigno, strpos($resultSigno, '|')+1);
				$patternRangoFechas = "/\d{1,2}\sde\s\w{1,}\s{0,1}al\s{0,1}\d{1,2}\sde\s\w{1,}\s{0,1}/u";
				$regexpmatch = preg_match($patternRangoFechas, $pronostico, $matches);
				$rango = $matches[0];
				$pronostico = preg_replace($patternRangoFechas, '', $pronostico);
				$horoscopoXsigno[$signo]['pronostico'] .= '</br></br>'.$pronostico;
			}


			// create a json object to send to the template
			$responseContent = array(
				"signo" => $this->signos[strtolower($param)]['nombre'],
				"codHtmlSigno" => $this->signos[strtolower($param)]['codHtml'],
				"rangoFechSigno" => $this->signos[strtolower($param)]['rangoFechas'],
				"pronostico" => $horoscopoXsigno[$param]['pronostico']
			);

			// create the response
			$response = new Response();
			$response->setCache("day");
			$response->setResponseSubject("Tu horoscopo de hoy");
			$response->createFromTemplate("horoscopoXsigno.tpl", $responseContent);
			return $response;
		}
		else
		{
			$response = new Response();
			$response->setResponseSubject("Escribiste el signo de manera incorrecta");
			$response->createFromTemplate("selectSigno.tpl", array("signos" => $this->signos));
			return $response;
		}
	}
}
