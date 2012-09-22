<?php
/**
 * Created J/12/08/2010
 * Updated J/20/09/2012
 * Version 7
 *
 * Copyright 2010-2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/versioning
 * http://www.luigifab.info/apijs
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

class Versioning_Processor extends Error_Processor {

	// définition des attributs
	private $debug = false;
	private $dataReady = false;

	private $dataSource = array();
	private $dataTranslated = array();


	// #### Initialisation ############################################### rewrite ## public ### //
	// = révision : 11
	// » Éxécute la fonction construct de Magento
	// » Charge le fichier de traduction en fonction de la langue du navigateur
	public function __construct() {

		parent::__construct();

		// gestion de la langue à partir du navigateur (si la langue n'est pas encore définie)
		$list = array('fr_FR', 'en_US');

		if (strlen($_SERVER['HTTP_ACCEPT_LANGUAGE']) === 5)
			$lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)).'_'.strtoupper(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 3, 2));
		else
			$lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)).'_'.strtoupper(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

		if (isset($_GET['lang']) && in_array($_GET['lang'], $list))
			$_SESSION['lang'] = $_GET['lang'];
		else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && in_array($lang, $list))
			$_SESSION['lang'] = $lang;
		else if (!isset($_SESSION['lang']))
			$_SESSION['lang'] = 'en_US';

		// chargement des traductions
		// le premier contient les traductions par défaut du module
		// le second contient les traductions configurées dans le backend (s'il est disponible)
		$this->loadCSV('./errors/versioning/locale/'.$_SESSION['lang'].'2.csv');
		$this->loadCSV('./errors/versioning/locale/'.$_SESSION['lang'].'.csv');
	}

	public function processUpgrade() {
		$this->_sendHeaders(503);
		$this->_renderPage('upgrade.phtml');
	}


	// #### Chargement d'un fichier de traduction ################################## private ### //
	// = révision : 11
	// » Récupère le contenu du fichier de traduction et le sauvegarde dans deux tableaux
	// » Le premier tableau contient le mot anglais et le deuxième contient la traduction
	// » Prend soin de vérifier si le fichier existe avant de faire n'importe quoi
	private function loadCSV($file) {

		if (is_file($file)) {

			$ressource = fopen($file, 'r');

			while (($line = fgetcsv($ressource, 1500, ',', '`')) !== false) {

				if (strlen($line[0]) > 1) {
					array_push($this->dataSource, $line[0]);
					array_push($this->dataTranslated, $line[1]);
				}
			}

			fclose($ressource);
			$this->dataReady = true;
		}
	}


	// #### Traduction par phrase clef ############################################## public ### //
	// = révision : 22
	// » Recherche le numéro d'index de la phrase à traduire dans les tableaux de traduction
	// » Renvoie la phrase à traduire inchangée si aucun fichier de traduction n'est chargé ou si la phrase n'est pas trouvée
	public function translate($data) {

		// préparation
		$data  = stripslashes($data);
		$index = ($this->dataReady) ? array_search($data, $this->dataSource) : false;
		$args  = func_num_args();

		// chaine de caractères configurable
		if ($args > 1) {

			$array = ($index !== false) ? explode('§', $this->dataTranslated[$index]) : explode('§', $data);
			$translation = ''; $i = 1;

			foreach ($array as $value)
				$translation .= ($i < $args) ? $value.func_get_arg($i++) : $value;
		}

		// chaine de caractères simple
		else {
			$translation = ($index !== false) ? $this->dataTranslated[$index] : $data;
		}

		// mise en place des espaces insécables
		$translation = str_replace(' : ', '&nbsp;: ', $translation);
		$translation = str_replace(' ; ', '&nbsp;; ', $translation);
		$translation = str_replace('« ', '«&nbsp;', $translation);
		$translation = str_replace(' »', '&nbsp;»', $translation);

		$translation = preg_replace('# ([0-9])#', '&nbsp;$1', $translation);
		$translation = str_replace('de&nbsp;', 'de ', $translation);
		$translation = str_replace('of&nbsp;', 'of ', $translation);

		return ($this->debug) ? '§'.$translation.'§' : $translation;
	}
}