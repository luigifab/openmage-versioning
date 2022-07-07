<?php
/**
 * Created J/12/08/2010
 * Updated D/03/07/2022
 *
 * Copyright 2011-2022 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/versioning
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

	private $_config = [];
	private $_dataSource = [];
	private $_dataTranslated = [];

	public function init(string $type) {

		$ip = empty(getenv('HTTP_X_FORWARDED_FOR')) ? false : explode(',', getenv('HTTP_X_FORWARDED_FOR'));
		$ip = empty($ip) ? getenv('REMOTE_ADDR') : reset($ip);
		$ip = (preg_match('#^::f{4}:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $ip) === 1) ? substr($ip, 7) : $ip;

		$this->setData('ip', $ip);
		$this->setData('type', $type);

		if (!is_dir('./config/'))
			@mkdir('./config/', 0755);

		// gestion des locales
		$locales = [];
		$files   = array_merge(glob('./config/*.csv'), glob('../app/locale/*/Luigifab_Versioning.csv'));

		foreach ($files as $file) {
			// app/locale/en_US/Luigifab_Versioning.csv
			if (mb_stripos($file, 'Luigifab_Versioning.csv') !== false) {
				$locale = mb_substr($file, mb_stripos($file, 'locale/') + 7);
				$locale = mb_substr($locale, 0, mb_stripos($locale, '/'));
				$locales[] = $locale;
			}
			// en-US.csv ou en_US.csv
			else if ((mb_stripos($file, '.csv') !== false) && (mb_stripos($file, '.') !== 0)) {
				$locales[] = (string) str_replace('-', '_', mb_substr($file, 0, -4)); // (yes)
			}
		}

		$result = $this->searchCurrentLocale($locales);

		setlocale(LC_ALL, $result.'utf8');
		$this->setData('locale', $result);

		// le premier fichier contient les traductions configurées dans le back-office
		// le second fichier contient les traductions par défaut (n'écrase pas les valeurs du back-office)
		// le troisième fichier sert si les fichiers précédents sont incomplets
		$this->loadCSV('./config/'.$result.'.csv');
		$this->loadCSV('../app/locale/'.$result.'/Luigifab_Versioning.csv');
		$this->loadCSV('../app/locale/en_US/Luigifab_Versioning.csv');

		// chargement de la configuration
		// remplace 0 et 1 par false et true
		$file = './config/config.dat';
		if (is_file($file)) {

			$data = trim(file_get_contents($file));
			$data = explode("\n", $data);

			foreach ($data as $config) {
				if ((mb_strlen($config) > 3) && ($config[0] != '#')) {
					[$key, $value] = explode('=', $config);
					$this->_config[$key] = in_array($value, [0, 1, '0', '1'], true) ? ($value != 0) : $value;
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
		$text = $this->__($this->getData('type').'_pagetitle');
		return empty($this->getData('report')) ? $text : $text.' ('.$this->getData('report').')';
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
		$base = mb_substr($base, 0, mb_strrpos($base, '/'));                       // /sites/14/web/errors           /sites/14/web
		$base = (mb_stripos($base, 'errors') === false) ? $base.'/errors' : $base; // /sites/14/web/errors           /sites/14/web/errors

		if ($file == 'favicon.ico')
			return mb_substr($base, 0, mb_strrpos($base, '/')).'/favicon.ico';
		else if (mb_stripos($file, '/config/') === false)
			return is_file('./config/'.$file) ? $base.'/config/'.$file : $base.'/'.$file;
		else
			return $base.'/'.$file;
	}

	public function renderPage(string $code) {

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
		$dir = defined('BP') ? BP.'/errors' : __DIR__;
		$dir = str_replace('/errors', '', $dir).'/var/report/';
		if (!is_dir($dir))
			@mkdir($dir, 0755, true);

		// data[0] = $t->getMessage()
		// data[1] = $t->getTraceAsString()
		// data['url'] = 'REQUEST_URI'
		// data['skin'] = app()->getStore()->getData('code');
		// data['script_name'] = 'SCRIPT_NAME'
		$text = str_replace('^', chr(194).chr(160), implode("\n", [
			'',
			'- - - -',
			empty($this->getData('ip')) ?      'REMOTE_ADDR^^^^^not available' : 'REMOTE_ADDR^^^^^'.$this->getData('ip'),
			empty(getenv('HTTP_USER_AGENT')) ? 'HTTP_USER_AGENT^not available' : 'HTTP_USER_AGENT^'.getenv('HTTP_USER_AGENT'),
			empty(getenv('HTTP_REFERER')) ?    'HTTP_REFERER^^^^not available' : 'HTTP_REFERER^^^^'.getenv('HTTP_REFERER'),
			empty($data['url']) ?              'REQUEST_URI^^^^^not available' : 'REQUEST_URI^^^^^'.$data['url'],
			'PHP_VERSION^^^^^'.PHP_VERSION,
			'- - - -',
			'GET^^^^'.(empty($_GET) ? 'empty' : implode(' ', array_keys($_GET))),
			'POST^^^'.(empty($_POST) ? 'empty' : implode(' ', array_keys($_POST))),
			'FILES^^'.(empty($_FILES) ? 'empty' : implode(' ', array_keys($_FILES))),
			'COOKIE^'.(empty($_COOKIE) ? 'empty' : implode(' ', array_keys($_COOKIE))),
		]));

		$emsg = (mb_stripos($data[0], $data[1]) === false) ? $data[0]."\n".$data[1] : $data[0];
		@file_put_contents($dir.$id, $emsg.$text);

		$this->setData('report', $id);
		$this->setData('report_content', htmlspecialchars($emsg.$text, ENT_NOQUOTES | ENT_SUBSTITUTE));

		$email = array_filter(explode(' ', $this->getConfig('email')));
		if (!empty($email)) {
			$subject = 'Fatal error #'.$id;
			$headers = 'Content-Type: text/html; charset=utf-8'."\r\n".'From: root'.mb_substr($email[0], mb_strrpos($email[0], '@'));
			$message = '<pre>'.$this->getData('report_content').'</pre>';
			mail(implode(', ', $email), $subject, $message, $headers);
		}
	}

	public function canShowReport() {

		if (!empty($txt = $this->getData('report_content'))) {

			if (!empty($_SERVER['MAGE_IS_DEVELOPER_MODE']) || !empty($_ENV['MAGE_IS_DEVELOPER_MODE']))
				return $txt;

			$ips = './config/report.ip';
			if (is_file($ips) && (stripos(file_get_contents($ips), '-'.$this->getData('ip').'-') !== false))
				return $txt;
		}

		return false;
	}

	// config et données
	public function getConfig(string $key) {
		return empty($this->_config[$this->type.'_'.$key]) ? false : $this->_config[$this->type.'_'.$key];
	}

	public function getData(string $key) {
		return empty($this->{$key}) ? null : $this->{$key};
	}

	public function setData(string $key, $value) {
		$this->{$key} = $value;
		return $this;
	}

	// langue et traduction
	protected function searchCurrentLocale(array $locales, string $result = 'en_US') {

		// recherche des préférences dans HTTP_ACCEPT_LANGUAGE
		// https://stackoverflow.com/a/33748742
		$codes = array_reduce(
			empty(getenv('HTTP_ACCEPT_LANGUAGE')) ? [] : explode(',', getenv('HTTP_ACCEPT_LANGUAGE')),
			static function ($items, $item) {
				[$code, $q] = explode(';q=', $item.';q=1');
				$items[str_replace('-', '_', $code)] = (float) $q;
				return $items;
			}, []);

		arsort($codes);
		$codes = array_map('\strval', array_keys($codes));

		// ajoute la locale présente dans l'url en premier car elle est prioritaire
		if (!empty($_GET['lang'])) {
			$code = str_replace('-', '_', $_GET['lang']);
			if (str_contains($code, '_'))
				array_unshift($codes, substr($code, 0, strpos($code, '_')));
			array_unshift($codes, $code);
		}

		// cherche la locale à utiliser
		// essaye es ou fil puis es_ES ou fil_PH
		foreach ($codes as $code) {

			if ((strlen($code) >= 2) && !str_contains($code, '_')) {
				// es devient es_ES de manière à prioriser es_ES au lieu d'utiliser es_XX
				if (in_array($code.'_'.strtoupper($code), $locales)) {
					$result = $code.'_'.strtoupper($code);
					break;
				}
				// es
				foreach ($locales as $locale) {
					if (stripos($locale, $code) === 0) {
						$result = $locale;
						break 2;
					}
				}
			}
			else if (in_array($code, $locales)) {
				$result = $code;
				break;
			}
		}

		return $result;
	}

	protected function loadCSV(string $file) {

		if (is_file($file)) {

			$resource = fopen($file, 'rb');

			while (!empty($line = fgetcsv($resource, 5000))) {
				if (!empty($line[0]) && !empty($line[1])) {
					$this->_dataSource[] = $line[0];
					$this->_dataTranslated[] = $line[1];
				}
			}

			fclose($resource);
		}
	}

	public function __(string $text, ...$values) {

		if (empty($text))
			return '';

		$text  = stripslashes($text);
		$index = array_search($text, $this->_dataSource);

		if (!empty($values)) {
			$final = '';
			$parts = is_numeric($index) ? explode('§', $this->_dataTranslated[$index]) : explode('§', $text);
			foreach ($parts as $i => $part)
				$final .= empty($values[$i]) ? $part : $part.$values[$i];
		}
		else {
			$final = is_numeric($index) ? $this->_dataTranslated[$index] : $text;
		}

		return str_replace([' ?', ' !', ' ;', ' :'], ['&nbsp;?', '&nbsp;!', '&nbsp;;', '&nbsp;:'], $final);
	}
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return ($needle === '') || (strpos($haystack, $needle) !== false);
    }
}