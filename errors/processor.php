<?php
/**
 * Created J/12/08/2010
 * Updated L/20/01/2020
 *
 * Copyright 2011-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/versioning
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

array_walk_recursive($_GET, static function (&$val) { $val = trim($val); });
array_walk_recursive($_POST, static function (&$val) { $val = trim($val); });

class Processor {

	private $type;
	private $config = [];
	private $dataSource = [];
	private $dataTranslated = [];


	public function init(string $type) {

		$ip = empty(getenv('HTTP_X_FORWARDED_FOR')) ? false : explode(',', getenv('HTTP_X_FORWARDED_FOR'));
		$ip = empty($ip) ? getenv('REMOTE_ADDR') : array_pop($ip);
		$ip = (preg_match('#^::f{4}:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $ip) === 1) ? mb_substr($ip, 7) : $ip;

		$this->setData('ip', $ip);
		$this->setData('type', $type);

		if (!is_dir('./config/'))
			@mkdir('./config/', 0755);

		// gestion des locales
		$files  = array_merge(scandir('./config/', SCANDIR_SORT_NONE), glob('../app/locale/*/Luigifab_Versioning.csv'));
		$result = $this->searchLocale($files);

		setlocale(LC_ALL, $result.'utf8');
		$this->setData('locale', $result);

		// le premier fichier contient les traductions configurées dans le back-office
		// le second fichier contient les traductions par défaut (n'écrase pas les valeurs du back-office)
		// le troisième fichier sert si les fichiers précédents sont incomplets
		$this->loadCSV('./config/'.$this->getData('locale').'.csv');
		$this->loadCSV('../app/locale/'.$this->getData('locale').'/Luigifab_Versioning.csv');
		$this->loadCSV('../app/locale/en_US/Luigifab_Versioning.csv');

		// chargement de la configuration
		// remplace 0 et 1 par false et true
		$file = './config/config.dat';
		if (is_file($file)) {

			$data = trim(file_get_contents($file));
			$data = explode("\n", $data);

			foreach ($data as $config) {
				if ((mb_strlen($config) > 3) && (mb_stripos($config, '#') !== 0)) {
					[$key, $value] = explode('=', $config);
					$this->config[$key] = in_array($value, [0, 1, '0', '1'], true) ? ($value != 0) : $value;
				}
			}
		}

		// rapport de démo
		if (!empty($_GET['demo'])) {
			$this->setData('report', 123456789);
			$this->setData('report_content', 'Maecenas turpis ex fermentum vel condimentum a pulvinar sit amet enim. Vivamus tristique dolor odio ut scelerisque velit faucibus ac.');
		}
	}


	public function getPageTitle() {
		return $this->__($this->getData('type').'_pagetitle');
	}

	public function getTitle() {
		return $this->__($this->getData('type').'_title');
	}

	public function getHtmlContent() {
		return $this->__($this->getData('type').'_content');
	}

	public function getHtmlReload() {
		$text = $this->__($this->getData('type').'_autoreload');
		return (mb_stripos($text, '_autoreload') === false) ? '<p id="reload">'.$text.'</p>' : '';
	}


	public function getUrl(string $file) {

		$base = getenv('SCRIPT_NAME');                                             // /sites/14/web/errors[/503.php] /sites/14/web[/index.php]
		$base = mb_substr($base, 0, mb_strripos($base, '/'));                      // /sites/14/web/errors           /sites/14/web
		$base = (mb_stripos($base, 'errors') === false) ? $base.'/errors' : $base; // /sites/14/web/errors           /sites/14/web/errors

		if ($file == 'favicon.ico')
			return mb_substr($base, 0, mb_strripos($base, '/')).'/favicon.ico';
		else if (mb_stripos($file, '/config/') === false)
			return is_file('./config/'.$file) ? $base.'/config/'.$file : $base.'/'.$file;
		else
			return $base.'/'.$file;
	}

	public function renderPage(int $code) {

		header(($code == 404) ? 'HTTP/1.1 404 Not Found' : 'HTTP/1.1 503 Service Unavailable');
		header('Content-Type: text/html; charset=utf-8');
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Tue, 07 Nov 1989 20:30:00 GMT');
		header('X-XSS-Protection: 1; mode=block');
		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: DENY');
		header_remove('Pragma');
		header_remove('Set-Cookie');

		ob_start();
		require_once(is_file('./config/page.php') ? './config/page.php' : './page.php');
		echo str_replace(["\n\n", "\t", '  ', "\n\n", '  '], ["\n", '', ' ', "\n", ' '], ob_get_clean());
	}

	public function saveReport(array $data) {

		$id  = ceil(microtime(true) * random_int(100, 999));
		$dir = str_replace('/errors', '', __DIR__).'/var/report/';
		if (!is_dir($dir))
			@mkdir($dir, 0755, true);

		// data[0] = $e->getMessage()
		// data[1] = $e->getTraceAsString()
		// data['url'] = 'REQUEST_URI'
		// data['skin'] = app()->getStore()->getData('code');
		// data['script_name'] = 'SCRIPT_NAME'
		$text = [
			'- - - -',
			'REMOTE_ADDR '.$this->getData('ip'),
			empty(getenv('HTTP_USER_AGENT')) ? 'HTTP_USER_AGENT not available' : 'HTTP_USER_AGENT '.getenv('HTTP_USER_AGENT'),
			empty(getenv('HTTP_REFERER')) ? 'HTTP_REFERER not available' : 'HTTP_REFERER '.getenv('HTTP_REFERER'),
			empty($data['url']) ? 'REQUEST_URI not available' : 'REQUEST_URI '.$data['url'],
			'- - - -',
			$data[1],
			'- - - -',
			'GET '.(empty($_GET) ? 'empty' : implode(' ', array_keys($_GET))),
			'POST '.(empty($_POST) ? 'empty' : implode(' ', array_keys($_POST))),
			'FILES '.(empty($_FILES) ? 'empty' : implode(' ', array_keys($_FILES))),
			'COOKIE '.(empty($_COOKIE) ? 'empty' : implode(' ', array_keys($_COOKIE)))];

		//array_unshift($text, str_repeat("\n%s", count($text)));
		//$text = call_user_func_array('sprintf', $text);
		$text = sprintf(str_repeat("\n%s", count($text)), ...$text);
		$data = $data[0];

		@file_put_contents($dir.$id, $data.$text);

		$this->setData('report', $id);
		$this->setData('report_content', '<strong>'.$data.'</strong>'.htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE));

		$email = explode(' ', $this->getConfig('email'));
		if (!empty($email)) {
			$subject = 'Fatal error #'.$id;
			$headers = 'Content-Type: text/html; charset=utf-8'."\r\n".'From: root'.mb_substr($email[0], mb_strripos($email[0], '@'));
			$message = '<pre><strong>'.$data.'</strong>'.$text.'</pre>';
			mail(implode(', ', $email), $subject, $message, $headers);
		}
	}

	public function canShowReport() {

		if (!empty($txt = $this->getData('report_content'))) {

			if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE']))
				return $txt;

			$ips = './config/report.ip';
			if (is_file($ips) && (mb_stripos(file_get_contents($ips), '-'.$this->getData('ip').'-') !== false))
				return $txt;
		}

		return false;
	}


	// configuration et données
	public function getConfig(string $key) {
		return empty($this->config[$this->type.'_'.$key]) ? false : $this->config[$this->type.'_'.$key];
	}

	public function getData(string $key) {
		return empty($this->{$key}) ? null : $this->{$key};
	}

	public function setData(string $key, $value) {
		$this->{$key} = $value;
		return $this;
	}


	// language et traduction
	private function searchLocale(array $files, string $result = 'en_US', array &$available = []) {

		foreach ($files as $file) {
			// app/locale/en_US/Luigifab_Versioning.csv
			if (mb_stripos($file, 'Luigifab_Versioning.csv') !== false) {
				$locale = mb_substr($file, mb_stripos($file, 'locale/') + 7);
				$locale = (string) mb_substr($locale, 0, mb_stripos($locale, '/'));
				$available[$locale] = $locale;
			}
			// en-US.csv ou en_US.csv
			else if ((mb_stripos($file, '.csv') !== false) && (mb_stripos($file, '.') !== 0)) {
				$locale = (string) str_replace('-', '_', mb_substr($file, 0, -4));
				$available[$locale] = $locale;
			}
		}

		// recherche des préférences dans HTTP_ACCEPT_LANGUAGE
		// https://stackoverflow.com/a/33748742
		$preferred = array_reduce(
			empty(getenv('HTTP_ACCEPT_LANGUAGE')) ? [] : explode(',', getenv('HTTP_ACCEPT_LANGUAGE')),
			static function ($result, $item) {
				[$code, $q] = array_merge(explode(';q=', $item), [1]);
				$result[str_replace('-', '_', $code)] = (float) $q;
				return $result;
			},
			[]
		);

		arsort($preferred);
		$preferred = array_keys($preferred); // la liste triée des locales de HTTP_ACCEPT_LANGUAGE
		$available = array_keys($available); // la liste des locales possibles

		// ajoute la locale présente dans l'url en premier car elle est prioritaire
		if (!empty($_GET['lang'])) {
			$code = str_replace('-', '_', $_GET['lang']);
			if (mb_stripos($code, '_') !== false)
				array_unshift($preferred, mb_substr($code, 0, mb_stripos($code, '_')));
			array_unshift($preferred, $code);
		}

		// cherche la locale à utiliser
		foreach ($preferred as $code) {

			// es ou fil
			if ((mb_strlen($code) >= 2) && (mb_stripos($code, '_') === false)) {
				// es devient es_ES de manière à prioriser es_ES au lieu d'utiliser es_XX
				if (in_array($code.'_'.mb_strtoupper($code), $available)) {
					$result = $code.'_'.mb_strtoupper($code);
					break;
				}
				// es
				foreach ($available as $locale) {
					if (mb_stripos($locale, $code) === 0) {
						$result = $locale;
						break 2; // car il y a bien 2 foreach
					}
				}
			}
			// es_ES ou fil_PH
			else if (in_array($code, $available)) {
				$result = $code;
				break;
			}
		}

		return $result;
	}

	private function loadCSV(string $file) {

		if (is_file($file)) {

			$resource = fopen($file, 'rb');

			while (($line = fgetcsv($resource, 5000)) !== false) {
				if (!empty($line[0]) && !empty($line[1])) {
					$this->dataSource[] = $line[0];
					$this->dataTranslated[] = $line[1];
				}
			}

			fclose($resource);
		}
	}

	public function __(string $text, ...$values) {

		if (empty($text))
			return '';

		$text  = stripslashes($text);
		$index = array_search($text, $this->dataSource);

		if (!empty($values)) {
			$final = '';
			$parts = is_numeric($index) ? explode('§', $this->dataTranslated[$index]) : explode('§', $text);
			foreach ($parts as $i => $part)
				$final .= empty($values[$i]) ? $part : $part.$values[$i];
		}
		else {
			$final = is_numeric($index) ? $this->dataTranslated[$index] : $text;
		}

		return str_replace([' ?', ' !', ' ;', ' :'], ['&nbsp;?', '&nbsp;!', '&nbsp;;', '&nbsp;:'], $final);
	}
}