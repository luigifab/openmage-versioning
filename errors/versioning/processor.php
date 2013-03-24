<?php
/**
 * Created J/12/08/2010
 * Updated S/23/03/2013
 * Version 12
 *
 * Copyright 2010-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Versioning_Processor extends Error_Processor {

	// définition des attributs
	private $preview = false;
	private $dataReady = false;
	private $dataSource = array();
	private $dataTranslated = array();


	// #### Initialisation ############################################### rewrite ## public ### //
	// = révision : 18
	// » Charge le fichier de traduction en fonction de la langue du navigateur
	// » Prend en charge un appel direct pour prévisualisation (détection via le fichier ./readme.txt)
	public function __construct() {

		parent::__construct();

		if (is_file('./readme.txt'))
			$this->preview = true;

		$list = array('fr_FR', 'en_US');
		$lang = 'en_US';

		// gestion de la langue
		if (isset($_GET['lang']) && in_array($_GET['lang'], $list)) {
			$lang = $_GET['lang'];
		}
		else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

			$languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

			foreach ($languages as $language) {

				$language = (strpos($language, ';') !== false) ? substr($language, 0, strpos($language, ';')) : $language;
				$language = (strlen($language) > 5) ? substr($language, 0, 5) : $language;

				$language = (strlen($language) === 5) ? strtolower(substr($language, 0, 2)).'_'.strtoupper(substr($language, 3, 2)) :
					strtolower(substr($language, 0, 2)).'_'.strtoupper(substr($language, 0, 2));

				if (in_array($language, $list)) {
					$lang = $language;
					break;
				}
			}
		}

		// chargement des traductions
		// le premier contient les traductions configurées dans le backend
		// le second contient les traductions par défaut
		$this->loadCSV($this->preview ? './locale/'.$lang.'2.csv' : './errors/versioning/locale/'.$lang.'2.csv');
		$this->loadCSV($this->preview ? './locale/'.$lang.'.csv'  : './errors/versioning/locale/'.$lang.'.csv');

		$_SESSION['lang'] = $lang;
	}


	// #### Définition des pages #################################################### public ### //
	// = révision : 14
	// » Déclare les pages upgrade, report, 503 et 404
	// » Charge les templates et affiche le résultat après avoir supprimé les espaces inutiles
	public function processUpgrade() {

		$this->_sendHeaders(503);

		$baseTemplate = $this->_getTemplatePath('page.phtml');
		$contentTemplate = str_replace('page.phtml', 'upgrade.phtml', $baseTemplate);

		$this->renderPage($baseTemplate, $contentTemplate);
	}

	public function processReport() {

		$this->_sendHeaders(503);

		$baseTemplate = $this->_getTemplatePath('page.phtml');
		$contentTemplate = str_replace('page.phtml', 'report.phtml', $baseTemplate);

		$this->renderPage($baseTemplate, $contentTemplate);
	}

	public function process503() {

		$this->_sendHeaders(503);

		$baseTemplate = $this->_getTemplatePath('page.phtml');
		$contentTemplate = str_replace('page.phtml', '503.phtml', $baseTemplate);

		$this->renderPage($baseTemplate, $contentTemplate);
	}

	public function process404() {

		$this->_sendHeaders(404);

		$baseTemplate = $this->_getTemplatePath('page.phtml');
		$contentTemplate = str_replace('page.phtml', '404.phtml', $baseTemplate);

		$this->renderPage($baseTemplate, $contentTemplate);
	}

	private function renderPage($baseTemplate, $contentTemplate) {

		ob_start();
		require_once($baseTemplate);
		$html = ob_get_contents();
		ob_end_clean();

		echo str_replace(array("\n\n","\t",'  ',"\n\n",'  '), array("\n",'',' ',"\n",' '), $html);
	}


	// #### Adresse des fichiers ############################### rewrite ## protected/public ### //
	// = révision : 6
	// » Recherche les adresses des fichiers
	// » Permet une prévisualisation des pages via un appel direct
	protected function _getFilePath($file, $directories = null) {

		if ($this->preview)
			return str_replace('default', 'versioning', parent::_getFilePath($file, $directories));
		else
			return parent::_getFilePath($file, $directories);
	}

	public function getUrl($file) {
		return str_replace(array('errors/', 'versioning/'), '', $this->getBaseUrl()).$file;
	}

	public function hasUserCssFile() {
		$file = str_replace('page.phtml', 'config/user.css', $this->_getTemplatePath('page.phtml'));
		return (is_file($file)) ? true : false;
	}


	// #### Traduction par phrase clef ############################################## public ### //
	// = révision : 23
	// » Recherche le numéro d'index de la phrase à traduire dans les tableaux de traduction
	// » Renvoie la phrase à traduire inchangée si aucun fichier de traduction n'est chargé ou si la phrase n'a pas été trouvée
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

		return $translation;
	}


	// #### Chargement d'un fichier de traduction ################################## private ### //
	// = révision : 12
	// » Récupère le contenu du fichier de traduction et le sauvegarde dans deux tableaux
	// » Le premier tableau contient le mot anglais et le deuxième contient la traduction
	// » Prend soin de vérifier que le fichier existe avant de faire n'importe quoi
	private function loadCSV($file) {

		if (is_file($file)) {

			$ressource = fopen($file, 'r');

			while (($line = fgetcsv($ressource, 2000, ',', '`')) !== false) {

				if (strlen($line[0]) > 1) {
					array_push($this->dataSource, $line[0]);
					array_push($this->dataTranslated, $line[1]);
				}
			}

			fclose($ressource);
			$this->dataReady = true;
		}
	}
}