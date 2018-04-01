<?php
/**
 * Created J/12/08/2010
 * Updated M/27/02/2018
 *
 * Copyright 2011-2018 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://www.luigifab.info/magento/versioning
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

class Processor {

	private $type = null;
	private $locale = null;
	private $config = array();

	private $dataFile = array();
	private $dataSource = array();
	private $dataTranslated = array();


	// #### Initialisation ########################################################## public ### //
	// = révision : 37
	// » Recherche la liste des langues disponibles en fonction des fichiers CSV (définie la langue en fonction du navigateur)
	// » Charge les fichiers de traductions et les données de configuration
	// » Prend en charge les fichiers utilisateur (dossier config)
	public function init($type) {

		$this->setData('type', $type);

		if (!is_dir(ROOT.'/errors/config/'))
			mkdir(ROOT.'/errors/config/', 0755);

		// gestion des langues
		$files  = array_merge(scandir(ROOT.'/errors/config/'), glob(ROOT.'/app/locale/*/Luigifab_Versioning.csv'));
		$result = $this->searchLang($files);

		setlocale(LC_ALL, $result.'utf8');
		$this->setData('locale', $result);

		// le premier fichier contient les traductions configurées dans le back-office
		// le second fichier contient les traductions par défaut (n'écrase pas les valeurs du back-office)
		// le troisième fichier sert si les fichiers précédents sont incomplets
		$this->loadCSV(ROOT.'/errors/config/'.$this->getData('locale').'.csv');
		$this->loadCSV(ROOT.'/app/locale/'.$this->getData('locale').'/Luigifab_Versioning.csv');
		$this->loadCSV(ROOT.'/app/locale/en_US/Luigifab_Versioning.csv');

		// chargement de la configuration
		// remplace 0 et 1 par false et true
		if (is_file(ROOT.'/errors/config/config.dat') && is_readable(ROOT.'/errors/config/config.dat')) {

			$data = file_get_contents(ROOT.'/errors/config/config.dat');
			$data = explode("\n", $data);

			foreach ($data as $config) {

				if ((strlen($config) > 3) && (strpos($config, '#') !== 0)) {
					list($key, $value) = explode('=', $config);
					$this->config[$key] = (in_array($value, array(0, 1, '0', '1'), true)) ? (($value == 0) ? false : true) : $value;
				}
			}
		}

		// rapport de démo
		if (!empty($_GET['demo']))
			$this->setData('report', 123456789);
	}

	private function searchLang($files, $result = 'en_US') {

		$data = array();

		foreach ($files as $file) {
			// app/locale/en_US/Luigifab_Versioning.csv
			if (strpos($file, 'Luigifab_Versioning.csv') !== false) {
				$code = substr($file, strpos($file, 'locale/') + 7);
				$code = substr($code, 0, strpos($code, '/'));
				$data[$code] = $code;
			}
			// en-US.csv
			else if ((strpos($file, '.csv') !== false) && (strpos($file, '.') !== 0)) {
				$code = str_replace('-', '_', substr($file, 0, -4));
				$data[$code] = $code;
			}
		}

		// recherche des préférences dans HTTP_ACCEPT_LANGUAGE
		// https://stackoverflow.com/a/33748742
		$languages = array_reduce(
			(getenv('HTTP_ACCEPT_LANGUAGE') !== false) ? explode(',', getenv('HTTP_ACCEPT_LANGUAGE')) : array(),
			function ($res, $item) {
				list($code, $q) = array_merge(explode(';q=', $item), array(1));
				$res[str_replace('-', '_', $code)] = floatval($q);
				return $res;
			},
			array()
		);

		arsort($languages);
		$languages = array_keys($languages); // la liste triée de HTTP_ACCEPT_LANGUAGE
		$locales = array_keys($data);        // la liste des langues possibles

		// ajoute la langue présente dans l'url en premier
		// car elle est prioritaire
		if (!empty($_GET['lang'])) {
			$code = str_replace('-', '_', $_GET['lang']);
			array_unshift($languages, substr($code, 0, strpos($code, '_')));
			array_unshift($languages, $code);
		}

		// la bonne trouvaille
		foreach ($languages as $language) {

			if (strlen($language) < 2)
				continue;

			if (strpos($language, '_') === false) {
				// par exemple es devient es_ES
				// de manière à prioriser es_ES au lieu d'utiliser es_AR
				if (in_array($language.'_'.strtoupper($language), $locales)) {
					$result = $language.'_'.strtoupper($language);
					break;
				}
				// par exemple es
				foreach ($locales as $locale) {
					if (stripos($locale, $language) === 0) {
						$result = $locale;
						break 2; // car il y a bien 2 foreach
					}
				}
			}
			else {
				// par exemple es_ES ou fil_PH
				if (in_array($language, $locales)) {
					$result = $language;
					break;
				}
			}
		}

		return $result;
	}


	// #### Contenu de la page ############################################## i18n ## public ### //
	// = révision : 17
	// » Renvoi le contenu de la page (éventuellement en utilisant des expressions régulières)
	// » En fonction de la configuration, en fonction du contexte
	public function getPageTitle() {
		return $this->__($this->getData('type').'_pagetitle');
	}

	public function getTitle() {
		return $this->__($this->getData('type').'_title');
	}

	public function getReportId() {
		return $this->getData('report');
	}

	public function getHtmlReload() {
		$text = $this->__($this->getData('type').'_autoreload');
		return (strpos($text, '_autoreload') === false) ? '<p id="reload">'.$text.'</p>' : '';
	}

	public function getHtmlContent() {
		return $this->__($this->getData('type').'_content');
	}


	// #### Génération des adresses ################################################# public ### //
	// = révision : 5
	// » Recherche les adresses des fichiers (traitement particulier pour le favicon.ico)
	// » Prend en charge les fichiers utilisateur (dossier config)
	public function getUrl($file) {

		$base = getenv('SCRIPT_NAME');                                         // /sites/14/web/errors[/503.php] /sites/14/web[/index.php]
		$base = substr($base, 0, strrpos($base, '/'));                         // /sites/14/web/errors           /sites/14/web
		$base = (strpos($base, 'errors') === false) ? $base.'/errors' : $base; // /sites/14/web/errors           /sites/14/web/errors

		if ($file == 'favicon.ico')
			return substr($base, 0, strrpos($base, '/')).'/favicon.ico';
		else if (strpos($file, '/config/') === false)
			return (is_file(ROOT.'/errors/config/'.$file)) ? $base.'/config/'.$file : $base.'/'.$file;
		else
			return $base.'/'.$file;
	}


	// #### Génération de la page ################################################### public ### //
	// = révision : 6
	// » Définie les entêtes (404/503) de la page et génère la code HTML final
	// » Prend en charge les fichiers utilisateur (dossier config)
	public function renderPage($code) {

		header(($code == 404) ? 'HTTP/1.1 404 Not Found' : 'HTTP/1.1 503 Service Unavailable');
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Tue, 07 Nov 1989 20:30:00 GMT'); //echo gmdate('D, d M Y H:i:s', 626472000);
		header('X-UA-Compatible: IE=edge');
		header('X-XSS-Protection: 1; mode=block');
		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: DENY');

		ob_start();
		require_once((is_file(ROOT.'/errors/config/page.phtml')) ? ROOT.'/errors/config/page.phtml' : ROOT.'/errors/page.phtml');
		$html = ob_get_contents();
		ob_end_clean();

		echo str_replace(array("\n\n","\t",'  ',"\n\n",'  '), array("\n",'',' ',"\n",' '), $html);
	}


	// #### Gestion du rapport d'erreur ############################################# public ### //
	// = révision : 17
	// » Enregistre le rapport d'erreur dans le dossier var/report (fait un peu comme Magento à la base)
	// » Envoi ce rapport par email au format HTML si la configuration le permet
	public function saveReport($data) {

		$id = abs(intval(microtime(true) * rand(100, 1000)));
		$this->setData('report', $id);

		// sauvegarde du rapport
		$directory = str_replace('/errors', '', dirname(__FILE__)).'/var/report/';

		if (!is_dir($directory))
			@mkdir($directory, 0777, true);

		// data[0] = $e->getMessage()
		// data[1] = $e->getTraceAsString()
		// data['url']  = 'REQUEST_URI'
		// data['skin'] = app()->getStore()->getData('code');
		$data['url'] = (!empty($data['url'])) ? $data['url'] : 'url not available';
		@file_put_contents($directory.$id, $data[0]."\n".$data['url']."\n\n".$data[1]);

		// envoi par email si la configuration le permet
		$email = explode(' ', $this->getConfig('email'));

		if (!empty($email)) {

			$to = implode(', ', $email);
			$subject = 'Fatal error #'.$id;
			$headers = 'Content-Type: text/html; charset=utf-8'."\r\n".'From: root'.substr($email[0], strrpos($email[0], '@'));
			$message = '<pre style="font-size:0.85em; white-space:pre-wrap;"><strong>'.$data[0]."</strong>\n".
				$data['url']."\n\n".$data[1].'</pre>';

			mail($to, $subject, $message, $headers);
		}
	}


	// #### Méthodes magiques ####################################################### public ### //
	// = révision : 5
	// » Tout simplement magnifique
	public function __($data, $a = null, $b = null, $c = null, $d = null, $e = null) {
		return $this->translate($data, $a, $b, $c, $d, $e);
	}

	public function getConfig($key) {
		return (!empty($this->config[$this->type.'_'.$key])) ? $this->config[$this->type.'_'.$key] : false;
	}

	public function getData($key) {
		return (!empty($this->{$key})) ? $this->{$key} : null;
	}

	public function setData($key, $value) {
		return $this->{$key} = $value;
	}


	// #### Chargement d'un fichier de traduction ################################## private ### //
	// = révision : 21
	// » Récupère le contenu du fichier de traduction et le sauvegarde dans trois tableaux
	// » Le premier tableau contient le nom du fichier, le second le mot anglais et le troisième la traduction
	private function loadCSV($file) {

		if (is_file($file) && is_readable($file)) {

			$ressource = fopen($file, 'r');
			$file = basename($file);

			while (($line = fgetcsv($ressource, 5000, ',', '"')) !== false) {

				if (!empty($line[0]) && !empty($line[1])) {
					array_push($this->dataFile, $file);
					array_push($this->dataSource, $line[0]);
					array_push($this->dataTranslated, $line[1]);
				}
			}

			fclose($ressource);
		}
	}


	// #### Traduction par phrase clef ############################################## public ### //
	// = révision : 47
	// » Recherche le numéro d'index de la phrase à traduire dans les tableaux de traduction
	// » Renvoie la phrase à traduire inchangée si la phrase n'est pas trouvée
	// » Ajoute les espaces insécables
	public function translate($words) {

		if (empty($words))
			return '';

		$words = stripslashes($words);
		$index = array_search($words, $this->dataSource);
		$args  = func_num_args();

		// chaine de caractères configurable
		if ($args > 1) {

			$array = ($index !== false) ? explode('§', $this->dataTranslated[$index]) : explode('§', $words);
			$translation = ''; $i = 1;

			foreach ($array as $value)
				$translation .= ($i < $args) ? $value.func_get_arg($i++) : $value;
		}
		// chaine de caractères simple
		else {
			$translation = ($index !== false) ? $this->dataTranslated[$index] : $words;
		}

		// mise en place des espaces insécables
		$translation = str_replace(' ?', '&nbsp;?', $translation);
		$translation = str_replace(' !', '&nbsp;!', $translation);
		$translation = str_replace(' ;', '&nbsp;;', $translation);
		$translation = str_replace(' :', '&nbsp;:', $translation);
		$translation = str_replace('« ', '«&nbsp;', $translation);
		$translation = str_replace(' »', '&nbsp;»', $translation);

		return $translation;
	}
}