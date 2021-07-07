<?php
/**
 * Created V/27/02/2015
 * Updated V/18/06/2021
 *
 * Copyright 2011-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Versioning_Model_Upgrade {

	// désactivation des tampons
	// cela permet d'afficher la page au fur et à mesure de l'avancement
	// est incapable de mettre fin à la temporisation de zlib.output_compression
	// n'utilise surtout pas le fichier versioning.log pour Mage::log
	// https://stackoverflow.com/a/25835968
	public function disableAllBuffer() {

		header('Content-Encoding: chunked');
		header('Connection: Keep-Alive');

		ini_set('max_execution_time', 900);
		ini_set('output_buffering', 0);
		ini_set('implicit_flush', 1);
		ini_set('display_errors', 1);
		ob_implicit_flush();
		ignore_user_abort(true);

		try {
			for ($i = 0; $i < ob_get_level(); $i++)
				ob_end_clean();
		}
		catch (Throwable $t) {
			Mage::log($t->getMessage(), Zend_Log::ERR);
		}

		return $this;
	}

	// exécute le processus de mise à jour
	// log les informations de la mise à jour
	public function process($targetRevision, $useflag) {

		$help   = Mage::helper('versioning');
		$system = $help->getSystem();
		$lock   = $help->getLock();
		$log    = $help->getLastLog();

		try {
			// données de l'historique
			// l'ordre à une importance capitale (voir Luigifab_Versioning_Model_History)
			$H = [
				'date'        => date('c'), // 0
				'current_rev' => $system->getCurrentRevision(), // 1
				'target_rev'  => $targetRevision, // 2
				'remote_addr' => $help->getIpAddr(), // 3
				'user'        => Mage::getSingleton('admin/session')->getData('user')->getData('username'), // 4
				'duration'    => microtime(true), // 5
				'status'      => 'Upgrade in progress', // 6
				'branch'      => $system->getCurrentBranch(), // 7
			];

			// ÉTAPE 1
			$this->writeTitle($help->__('1) Locking and configuration check'));

			if (Mage::getSingleton('admin/session')->isAllowed('tools/versioning/upgrade') !== true)
				Mage::throwException('Not authorized');

			if (is_file($lock))
				Mage::throwException('An update is in progress');

			if (is_file($log))
				unlink($log);

			if (!empty($H['branch']))
				$this->writeNotice($help->__('Repository: %s / Branch: %s / Current revision: %s / Requested revision: %s',
					$system->getType(), $H['branch'], $H['current_rev'], $targetRevision));
			else
				$this->writeNotice($help->__('Repository: %s / Current revision: %s / Requested revision: %s',
					$system->getType(), $H['current_rev'], $targetRevision));

			file_put_contents($lock, $H['current_rev'].'/'.$H['target_rev'].' from '.$H['remote_addr'].' by '.$H['user'], LOCK_EX);
			if ($useflag)
				copy($lock, $help->getUpgradeFlag());

			// ÉTAPE 2 et 3
			// avec les événements before et after
			$this->writeEvent('admin_versioning_upgrade_before...');
			Mage::dispatchEvent('admin_versioning_upgrade_before',
				['repository' => $system, 'revision' => $targetRevision, 'controller' => $this]);

			$this->writeTitle($help->__('2) Updating'), true);
			$system->upgradeToRevision($this, $log, $targetRevision);

			$this->writeEvent('admin_versioning_upgrade_after...');
			Mage::dispatchEvent('admin_versioning_upgrade_after',
				['repository' => $system, 'revision' => $targetRevision, 'controller' => $this]);

			$this->writeTitle($help->__('3) Cache'), true);
			$this->clearAllCache();

			// ÉTAPE 4
			$this->writeTitle($help->__('4) Unlocking'));
			unlink($lock);

			$H['duration'] = ceil(microtime(true) - $H['duration']);
			$H['status']   = is_file($log) ? 'Update completed'."\n".trim(file_get_contents($log)) : 'Update completed';

			$result = [
				'url'   => '*/versioning_repository/index',
				'title' => $help->__('Update completed (revision %s)', $targetRevision),
				'error' => false
			];

			Mage::getSingleton('adminhtml/session')->addSuccess($help->__('Update to revision %s completed.', $targetRevision));
		}
		catch (Throwable $t) {

			$H['duration'] = ceil(microtime(true) - $H['duration']);
			$H['status']   = $t->getMessage();

			if (in_array($t->getMessage(), ['Not authorized', 'An update is in progress'])) {

				if ($t->getMessage() == 'An update is in progress') {
					$this->writeError($help->__('Stop! Stop! Stop! An update is in progress.'));
					Mage::getSingleton('adminhtml/session')->addError($help->__('Please wait, an update is in progress.'));
				}
				else {
					$this->writeError($help->__('You are not authorized to perform this operation.'));
					Mage::getSingleton('adminhtml/session')->addError($help->__('You are not authorized to perform this operation.'));
				}

				$result = [
					'url'   => '*/versioning_repository/index',
					'title' => $help->__('Update error (revision %s)', $targetRevision),
					'error' => true
				];
			}
			else {
				if (is_file($log))
					$H['status'] = $t->getMessage()."\n".trim(file_get_contents($log));

				$this->writeError($t->getMessage());
				Mage::getSingleton('adminhtml/session')->addError(nl2br($t->getMessage()));

				$this->writeEvent('admin_versioning_upgrade_after...');
				Mage::dispatchEvent('admin_versioning_upgrade_after',
					['repository' => $system, 'revision' => $targetRevision, 'controller' => $this, 'exception' => $t]);

				$this->writeTitle($help->__('4) Unlocking'), true);
				if (is_file($lock))
					unlink($lock);

				$result = [
					'url'   => '*/versioning_repository/history',
					'title' => $help->__('Update error (revision %s)', $targetRevision),
					'error' => true
				];
			}
		}

		file_put_contents($help->getHistoryLog(), '`'.implode('`,`', $H).'`'."\n", FILE_APPEND | LOCK_EX);
		return $result;
	}

	// affiche une commande ou une information pour savoir ce qu'il se passe
	// ajoute un peu de code HTML pour faire plus jolie
	private function writeTitle($data, $endEvent = false) {
		echo $endEvent ? '</span>'."\n".$data."\n" : "\n".$data."\n";
	}

	private function writeEvent($data) {
		echo '<span class="event">',$data,"\n";
	}

	public function writeError($data) {
		echo '<span class="error">',$data,'</span>',"\n";
	}

	public function writeNotice($data) {
		echo '<span class="notice">',$data,'</span>',"\n";
	}

	public function writeCommand($data) {
		echo '<code>',$data,'</code>',"\n";
	}

	// tente de vider totalement le cache (du moins essaye)
	// utilise les méthodes et événements du core puis supprime tous les répertoires
	// n'utilise surtout pas le fichier versioning.log pour Mage::log
	private function clearAllCache() {

		try {
			Mage::dispatchEvent('adminhtml_cache_flush_all');
			Mage::app()->getCacheInstance()->flush();
		}
		catch (Throwable $t) {
			Mage::log($t->getMessage(), Zend_Log::ERR);
		}

		try {
			Mage::app()->cleanCache();
			Mage::dispatchEvent('adminhtml_cache_flush_system');
		}
		catch (Throwable $t) {
			Mage::log($t->getMessage(), Zend_Log::ERR);
		}

		try {
			Mage::getModel('core/design_package')->cleanMergedJsCss();
			Mage::dispatchEvent('clean_media_cache_after');
		}
		catch (Throwable $t) {
			Mage::log($t->getMessage(), Zend_Log::ERR);
		}

		$dirs = [
			Mage::getBaseDir('var').'/cache/',
			Mage::getBaseDir('var').'/full_page_cache/',
			Mage::getBaseDir('media').'/css/',
			Mage::getBaseDir('media').'/css_secure/',
			Mage::getBaseDir('media').'/js_secure/',
			Mage::getBaseDir('media').'/js/'
		];

		exec('rm -rf '.implode(' ', $dirs));
	}
}