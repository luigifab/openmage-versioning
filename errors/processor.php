<?php
/**
 * Created J/12/08/2010
 * Updated L/07/09/2015
 * Version 18
 *
 * Copyright 2011-2015 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/versioning
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

	private $lang = null;
	private $type = null;
	private $config = array();

	private $dataSource = array();
	private $dataTranslated = array();


	// #### Initialisation ########################################################## public ### //
	// = révision : 27
	// » Recherche la liste des langues disponibles en fonction des fichiers CSV (définie la langue en fonction du navigateur)
	// » Charge les fichiers de traductions et les données de configuration
	// » Prend en charge les fichiers utilisateur (dossier config)
	public function init($type) {

		$this->setData('lang', 'en_US');
		$this->setData('type', $type);

		// vérification du répertoire
		if (!is_dir(ROOT.'/errors/config/'))
			mkdir(ROOT.'/errors/config/', 0755);

		// recherche des langues
		// par rapport aux fichiers CSV disponibles
		$files = array_merge(scandir(ROOT.'/errors/config/'), scandir(ROOT.'/errors/locale/'));
		$locales = array();

		foreach ($files as $file) {
			$code = substr($file, 0, -4);
			if ((strpos($file, '.csv') !== false) && !in_array($code, $locales))
				$locales[] = $code;
		}

		// définition de la langue
		// langue à partir du choix de l'utilisateur ($_GET lang fr_FR)
		// langue à partir du navigateur ($_SERVER, 2 ou 4 caractères)
		if (isset($_GET['lang']) && in_array($_GET['lang'], $locales)) {
			$this->setData('lang', $_GET['lang']);
		}
		else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

			$browser = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			$browser = (strlen($browser) >= 5) ? strtolower(substr($browser, 0, 2)).'_'.strtoupper(substr($browser, 3, 2)) :
				strtolower(substr($browser, 0, 2)).'_'.strtoupper(substr($browser, 0, 2));

			$this->setData('lang', (in_array($browser, $locales)) ? $browser : $this->getData('lang'));
		}

		// chargement des traductions
		// le premier fichier contient les traductions configurées dans le back-office
		// le second fichier contient les traductions par défaut (n'écrase pas les valeurs du back-office)
		$this->loadCSV(ROOT.'/errors/config/'.$this->getData('lang').'.csv');
		$this->loadCSV(ROOT.'/errors/locale/'.$this->getData('lang').'.csv');

		setlocale(LC_ALL, $this->getData('lang').'utf8');

		// chargement de la configuration
		// remplace 0 et 1 par false et true
		if (is_file(ROOT.'/errors/config/config.dat') && is_readable(ROOT.'/errors/config/config.dat')) {

			$config = file_get_contents(ROOT.'/errors/config/config.dat');

			if (strlen($config) > 3) {

				$config = explode("\n", $config);

				foreach ($config as $data) {

					if ((strlen($data) > 3) && (strpos($data, '#') !== 0)) {
						list($key, $value) = explode('=', $data);
						$this->config[$key] = (in_array($value, array(0, 1, '0', '1'), true)) ? (($value == 0) ? false : true) : $value;
					}
				}
			}
		}

		// rapport de démo
		if (isset($_GET['demo']))
			$this->setData('report', 123456789);
	}


	// #### Contenu de la page ############################################## i18n ## public ### //
	// = révision : 16
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

		$text = $this->__($this->getData('type').'_content');

		if (($this->getData('type') === 'upgrade') && is_file(ROOT.'/upgrade.flag'))
			$text = preg_replace_callback('#%date\[([^\]]+)\]%#', array($this, 'searchReplaceUpgrade'), $text);
		else if (($this->getData('type') === 'error503') && is_file(ROOT.'/maintenance.flag'))
			$text = preg_replace_callback('#%date\[([^\]]+)\]%#', array($this, 'searchReplaceMaintenance'), $text);
		else if (($this->getData('type') === 'upgrade') || ($this->getData('type') === 'error503'))
			$text = preg_replace_callback('#%date\[([^\]]+)\]%#', array($this, 'searchReplaceDemo'), $text);

		return $text;
	}

	public function searchReplaceUpgrade($matches) {
		$time = filemtime(ROOT.'/upgrade.flag');
		return strftime($matches[1], $time);
	}

	public function searchReplaceMaintenance($matches) {
		$time = filemtime(ROOT.'/maintenance.flag');
		return strftime($matches[1], $time);
	}

	public function searchReplaceDemo($matches) {
		return strftime($matches[1]);
	}

	// #### Génération des adresses ################################################# public ### //
	// = révision : 5
	// » Recherche les adresses des fichiers (traitement particulier pour le favicon.ico)
	// » Prend en charge les fichiers utilisateur (dossier config)
	public function getUrl($file) {

		$base = getenv('SCRIPT_NAME');                                          // /sites/14/web/errors[/503.php] /sites/14/web[/index.php]
		$base = substr($base, 0, strrpos($base, '/'));                          // /sites/14/web/errors           /sites/14/web
		$base = (strpos($base, 'errors') === false) ? $base.'/errors' : $base;  // /sites/14/web/errors           /sites/14/web/errors

		if ($file === 'favicon.ico')
			return substr($base, 0, strrpos($base, '/')).'/favicon.ico';
		else if (strpos($file, '/config/') === false)
			return (is_file(ROOT.'/errors/config/'.$file)) ? $base.'/config/'.$file : $base.'/'.$file;
		else
			return $base.'/'.$file;
	}


	// #### Génération de la page ################################################### public ### //
	// = révision : 4
	// » Définie les entêtes (404/503) de la page et génère la code HTML final
	// » Prend en charge les fichiers utilisateur (dossier config)
	public function renderPage($code) {

		header(($code === 404) ? 'HTTP/1.1 404 Not Found' : 'HTTP/1.1 503 Service Unavailable');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');

		ob_start();
		require_once((is_file(ROOT.'/errors/config/page.phtml')) ? ROOT.'/errors/config/page.phtml' : ROOT.'/errors/page.phtml');
		$html = ob_get_contents();
		ob_end_clean();

		echo str_replace(array("\n\n","\t",'  ',"\n\n",'  '), array("\n",'',' ',"\n",' '), $html);
	}


	// #### Gestion du rapport d'erreur ############################################# public ### //
	// = révision : 15
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
		// data['url'] = if $_SERVER['REQUEST_URI']
		// data['skin'] = if app()->getStore()->getCode();
		$data['url'] = (isset($data['url'])) ? $data['url'] : 'url not available';
		@file_put_contents($directory.$id, $data[0]."\n".$data['url']."\n\n".$data[1]);

		// envoi par email si la configuration le permet
		$email = explode(' ', $this->getConfig('email'));

		if (!empty($email)) {

			$to      = implode(', ', $email);
			$subject = 'Fatal error #'.$id;
			$message = '<pre style="font-size:0.85em; white-space:pre-wrap;"><b>'.$data[0]."</b>\n".$data['url']."\n\n".$data[1].'</pre>';
			$headers = 'Content-type: text/html; charset=utf-8'."\r\n".'From: root'.substr($email[0], strrpos($email[0], '@'));

			mail($to, $subject, $message, $headers);
		}
	}


	// #### Méthodes magiques ####################################################### public ### //
	// = révision : 5
	// » Tout simplement magnifique et simplissime
	public function __($data, $a = null, $b = null, $c = null, $d = null, $e = null) {
		return $this->translate($data, $a, $b, $c, $d, $e);
	}

	public function getConfig($key) {
		return (isset($this->config[$this->type.'_'.$key])) ? $this->config[$this->type.'_'.$key] : false;
	}

	public function getData($key) {
		return (isset($this->{$key})) ? $this->{$key} : null;
	}

	public function setData($key, $value) {
		return $this->{$key} = $value;
	}


	// #### Chargement d'un fichier de traduction ################################## private ### //
	// = révision : 18
	// » Récupère le contenu du fichier de traduction et le sauvegarde dans deux tableaux
	// » Le premier tableau contient le mot anglais et le deuxième contient la traduction
	// » Prend soin de vérifier si le fichier existe avant de faire n'importe quoi
	private function loadCSV($file) {

		if (is_file($file) && is_readable($file)) {

			$ressource = fopen($file, 'r');
			$file = substr($file, strrpos($file, '/') + 1);

			while (($line = fgetcsv($ressource, 5000, ',', '`')) !== false) {

				if (strlen($line[0]) > 1) {
					array_push($this->dataSource, $line[0]);
					array_push($this->dataTranslated, $line[1]);
				}
			}

			fclose($ressource);
		}
	}


	// #### Traduction par phrase clef ############################################# private ### //
	// = révision : 42
	// » Recherche le numéro d'index de la phrase à traduire dans les tableaux de traduction
	// » Renvoi la phrase à traduire inchangée si aucun fichier de traduction n'est chargé ou si la phrase n'est pas trouvée
	// » Ajoute les espaces insécables
	private function translate($words) {

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
		$translation = preg_replace('# ([0-9])#', '&nbsp;$1', $translation);

		return $translation;
	}
}