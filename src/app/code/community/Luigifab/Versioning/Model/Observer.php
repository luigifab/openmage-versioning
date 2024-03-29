<?php
/**
 * Created J/31/05/2012
 * Updated S/09/12/2023
 *
 * Copyright 2011-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://github.com/luigifab/openmage-versioning
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
	// Error 503 (maintenance.flag)
	// = (*.csv)       Titre de la page / Titre / Contenu texte ou html / Texte avec délai du rechargement automatique
	// = (error503.ip) Désactiver la page à partir des adresses IP suivantes
	// Update (upgrade.flag)
	// = (*.csv)       Titre de la page / Titre / Contenu texte ou html / Texte avec délai du rechargement automatique
	// = (upgrade.ip)  Désactiver la page à partir des adresses IP suivantes
	// Error report
	// = (*.csv)       Titre de la page / Titre / Contenu texte ou html
	// = (report.ip)   Afficher le rapport à partir des adresses IP suivantes
	// = (config.dat)  Envoyer le rapport par email à
	// System error 404
	// = (*.csv)       Titre de la page / Titre / Contenu texte ou html
	public function updateConfig() {

		$dir = BP.'/errors/config';
		if (!is_dir($dir))
			@mkdir($dir, 0755);
		if (!is_dir($dir) || !is_writable($dir))
			Mage::throwException('Directory <em>errors/config</em> does not exist or is not writable.');

		$global = [];
		foreach (Mage::app()->getWebsites() as $website) {
			foreach ($website->getGroups() as $group) {
				foreach ($group->getStores() as $store) {
					$locale = Mage::getStoreConfig('general/locale/code', $store);
					$global[$locale] = Mage::getStoreConfig('versioning/downtime', $store);
				}
			}
		}

		$this->updateTranslations($global);
		$this->updateDataConfig($global);
		$this->updateIpConfig($global);
	}

	// $global
	// fr_FR => Array
	//  [error503_pagetitle] [error503_title] [error503_content] [error503_autoreload] [error503_byip]
	//  [upgrade_pagetitle]  [upgrade_title]  [upgrade_content]  [upgrade_autoreload]  [upgrade_byip]
	//  [report_pagetitle]   [report_title]   [report_content]                         [report_byip]   [report_email]
	//  [report_pagetitle]   [report_title]   [error404_content]

	// *title* *content* *autoreload* (*.csv)
	protected function updateTranslations(array $global) {

		$translations = [];
		foreach ($global as $locale => $config) {

			foreach ($config as $key => $value) {

				$value = str_replace('"', '""', trim($value));

				if (str_contains($key, 'title')) {

					if (!empty($value))
						$translations[$locale][] = '"'.$key.'","'.$value.'"';
				}
				else if (str_contains($key, 'content')) {

					if (!empty($value) && (mb_strpos($value, '<') === 0))
						$translations[$locale][] = '"'.$key.'","'.$value.'"';
					else if (!empty($value))
						$translations[$locale][] = '"'.$key.'","<p>'.str_replace("\n", '<br />', $value).'</p>"';
				}
				else if (str_contains($key, 'autoreload')) {

					if (!empty($value) && str_contains($value, '[') && str_contains($value, ']'))
						$translations[$locale][] = '"'.$key.'","'.str_replace(['[', ']'], ['<span>', '</span>'], $value).'"';
				}
			}
		}

		array_map('unlink', glob(BP.'/errors/config/*.csv'));
		foreach ($translations as $locale => $values)
			file_put_contents(BP.'/errors/config/'.$locale.'.csv', implode("\n", $values));
	}

	// *email* *custom* (config.dat)
	protected function updateDataConfig(array $global) {

		$config = [];
		foreach (reset($global) as $key => $value) {

			$value = trim($value);
			if (empty($value))
				continue;

			if (str_contains($key, 'email')) {
				$value = @unserialize($value, ['allowed_classes' => false]);
				$value = array_map(static function ($val) { return array_pop($val); }, array_values($value));
				$config[] = $key.'='.implode(' ', $value);
			}
			else if (str_contains($key, 'custom')) {
				$config[] = $key.'='.str_replace('=', '', $value);
			}
		}

		$file = BP.'/errors/config/config.dat';
		if (count($config) > 0)
			file_put_contents($file, implode("\n", $config));
		else if (is_file($file))
			unlink($file);
	}

	// *byip* (error503.ip upgrade.ip report.ip)
	protected function updateIpConfig(array $global) {

		$config = [];
		foreach (reset($global) as $key => $value) {

			$value = trim($value);
			if (empty($value))
				continue;

			if (str_contains($key, 'byip')) {
				$key   = substr($key, 0, strrpos($key, '_')); // not mb_substr mb_strrpos
				$value = @unserialize($value, ['allowed_classes' => false]);
				$value = array_map(static function ($val) { return array_pop($val); }, array_values($value));
				$config[$key][] = '-'.implode("-\n-", $value).'-';
			}
		}

		array_map('unlink', glob(BP.'/errors/config/*.ip'));
		foreach ($config as $key => $values)
			file_put_contents(BP.'/errors/config/'.$key.'.ip', implode("\n", $values));
	}
}