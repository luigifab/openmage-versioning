<?php
/**
 * Created J/31/05/2012
 * Updated D/10/10/2021
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

class Luigifab_Versioning_Model_Observer {

	// EVENT admin_system_config_changed_section_versioning (adminhtml)
	// Erreur 503 (maintenance.flag)
	// = (*.csv)       Titre de la page / Titre / Contenu texte ou html / Texte avec délai du rechargement automatique
	// = (error503.ip) Désactiver la page à partir des adresses IP suivantes
	// Mise à jour (upgrade.flag)
	// = (*.csv)       Titre de la page / Titre / Contenu texte ou html / Texte avec délai du rechargement automatique
	// = (upgrade.ip)  Désactiver la page à partir des adresses IP suivantes
	// Rapport d'erreur
	// = (*.csv)       Titre de la page / Titre / Contenu texte ou html
	// = (report.ip)   Afficher le rapport à partir des adresses IP suivantes
	// = (config.dat)  Envoyer le rapport par email à
	// Erreur 404 système
	// = (*.csv)       Titre de la page / Titre / Contenu texte ou html
	public function updateConfig() {

		// vérification du répertoire
		$dir = BP.'/errors/config';

		if (!is_dir($dir))
			@mkdir($dir, 0755);
		if (!is_dir($dir) || !is_writable($dir))
			Mage::throwException('Directory <em>errors/config</em> does not exist or is not writable.');

		// récupération de toute la configuration utile
		// enregistre le tout dans un tableau avant d'enregistrer le tout dans les fichiers *.csv, config.ip et config.dat
		$global = [];

		foreach (Mage::app()->getWebsites() as $website) {
			foreach ($website->getGroups() as $group) {
				foreach ($group->getStores() as $store) {
					$locale = Mage::getStoreConfig('general/locale/code', $store->getId());
					$global[$locale] = Mage::getStoreConfig('versioning/downtime', $store->getId());
				}
			}
		}

		$this->updateTranslations($global);
		$this->updateDataConfig($global);
		$this->updateIpConfig($global);
	}


	// $global ici dans l'ordre, mais l'ordre n'a aucune importance
	// fr_FR => Array
	//  [error503_pagetitle] [error503_title] [error503_content] [error503_autoreload] [error503_byip]
	//  [upgrade_pagetitle]  [upgrade_title]  [upgrade_content]  [upgrade_autoreload]  [upgrade_byip]
	//  [report_pagetitle]   [report_title]   [report_content]                         [report_byip]   [report_email]
	//  [report_pagetitle]   [report_title]   [error404_content]

	// pagetitle/title, content, autoreload (*.csv)
	protected function updateTranslations(array $global) {

		$translations = [];

		// extraction des traductions locale par locale
		// il se peut aussi qu'il n'y en ait qu'une seule
		foreach ($global as $locale => $config) {

			// extraction de la configuration pour la locale
			foreach ($config as $key => $value) {

				$value = str_replace('"', '""', trim($value));

				// versioning/downtime/*title
				if (stripos($key, 'title') !== false) {

					if (!empty($value))
						$translations[$locale][] = '"'.$key.'","'.$value.'"';
				}
				// versioning/downtime/*content
				else if (stripos($key, 'content') !== false) {

					if (!empty($value) && (mb_stripos($value, '<') === 0))
						$translations[$locale][] = '"'.$key.'","'.$value.'"';
					else if (!empty($value))
						$translations[$locale][] =  '"'.$key.'","<p>'.str_replace("\n", '<br />', $value).'</p>"';
				}
				// versioning/downtime/*autoreload
				else if (stripos($key, 'autoreload') !== false) {

					if (!empty($value) && (mb_stripos($value, '[') !== false) && (mb_stripos($value, ']') !== false))
						$translations[$locale][] = '"'.$key.'","'.str_replace(['[', ']'], ['<span>', '</span>'], $value).'"';
				}
			}
		}

		// sauvegarde des données (format CSV)
		array_map('unlink', glob(BP.'/errors/config/*.csv'));
		if (count($translations) > 0) {
			foreach ($translations as $locale => $values)
				file_put_contents(BP.'/errors/config/'.$locale.'.csv', implode("\n", $values));
		}
	}

	// email, custom (config.dat)
	protected function updateDataConfig(array $global) {

		$config = [];

		// extraction de la configuration
		foreach (reset($global) as $key => $value) {

			$value = trim($value);
			if (empty($value))
				continue;

			if ((stripos($key, '_email') !== false) || (stripos($key, '_custom') !== false))
				$config[] = $key.'='.str_replace('=', '', $value);
		}

		// sauvegarde des données dans un seul fichier (format CSV)
		if (is_file(BP.'/errors/config/config.dat'))
			@unlink(BP.'/errors/config/config.dat');
		if (count($config) > 0)
			file_put_contents(BP.'/errors/config/config.dat', implode("\n", $config));
	}

	// byip (error503.ip upgrade.ip report.ip)
	protected function updateIpConfig(array $global) {

		$config = [];

		// extraction de la configuration
		foreach (reset($global) as $key => $value) {

			$value = trim($value);
			if (empty($value))
				continue;

			if (stripos($key, '_byip') !== false) {
				$key   = (string) substr($key, 0, strrpos($key, '_')); // (yes)
				$value = array_filter(preg_split('#\s+#', $value));
				$config[$key][] = '-'.implode("-\n-", $value).'-';
			}
		}

		// sauvegarde des données dans plusieurs fichiers (format spécial)
		array_map('unlink', glob(BP.'/errors/config/*.ip'));
		if (count($config) > 0) {
			foreach ($config as $key => $values)
				file_put_contents(BP.'/errors/config/'.$key.'.ip', implode("\n", $values));
		}
	}
}