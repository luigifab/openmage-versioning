<?php
/**
 * Created V/27/02/2015
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

class Luigifab_Versioning_Model_Upgrade {

	// désactivation des tampons (sauf si zlib.output_compression)
	// cela permet d'afficher la page au fur et à mesure de l'avancement
	// @see https://stackoverflow.com/a/25835968
	public function disableAllBuffer() {

		header('Content-Encoding: chunked');
		header('Connection: Keep-Alive');
		ini_set('max_execution_time', (PHP_VERSION_ID < 80100) ? '900' : 900);
		ini_set('output_buffering', (PHP_VERSION_ID < 80100) ? '0' : 0);
		ini_set('implicit_flush', (PHP_VERSION_ID < 80100) ? '1' : 1);
		ini_set('display_errors', (PHP_VERSION_ID < 80100) ? '1' : 1);
		ob_implicit_flush();
		ignore_user_abort(true);
		set_time_limit(900);

		try {
			while (ob_get_level() > 0)
				ob_end_clean();
		}
		catch (Throwable $t) { }

		return $this;
	}

	// exécute le processus de mise à jour
	// log les informations de la mise à jour
	public function process(string $targetRevision, bool $flag) {

		$helper = Mage::helper('versioning');
		$lock   = Mage::getBaseDir('var').'/versioning.lock';
		$system = $helper->getSystem();
		$log    = $helper->getLastLog();

		try {
			// données de l'historique
			// l'ordre à une importance capitale (voir Luigifab_Versioning_Model_History)
			$H = [
				'date'        => date('c'), // 0
				'current_rev' => $system->getCurrentRevision(), // 1
				'target_rev'  => $targetRevision, // 2
				'remote_addr' => $helper->getIpAddr(), // 3
				'user'        => Mage::getSingleton('admin/session')->getData('user')->getData('username'), // 4
				'duration'    => microtime(true), // 5
				'status'      => 'Upgrade in progress', // 6
				'branch'      => $system->getCurrentBranch(), // 7
			];

			// ÉTAPE 1
			$this->writeTitle($helper->__('1) Locking and configuration check'));

			if (Mage::getSingleton('admin/session')->isAllowed('tools/versioning/upgrade') !== true)
				Mage::throwException('Not authorized');

			if (is_file($lock))
				Mage::throwException('An update is in progress');

			if (is_file($log))
				unlink($log);

			if (!empty($H['branch']))
				$this->writeNotice($helper->__('Repository: %s / Branch: %s / Current revision: %s / Requested revision: %s',
					$system->getType(), $H['branch'], $H['current_rev'], $targetRevision));
			else
				$this->writeNotice($helper->__('Repository: %s / Current revision: %s / Requested revision: %s',
					$system->getType(), $H['current_rev'], $targetRevision));

			file_put_contents($lock, $H['current_rev'].'/'.$H['target_rev'].' from '.$H['remote_addr'].' by '.$H['user'], LOCK_EX);
			if ($flag)
				copy($lock, $helper->getUpgradeFlag());

			// ÉTAPE 2 et 3
			// avec les événements before et after
			$this->writeEvent('admin_versioning_upgrade_before...');
			Mage::dispatchEvent('admin_versioning_upgrade_before',
				['repository' => $system, 'revision' => $targetRevision, 'controller' => $this]);

			$this->writeTitle($helper->__('2) Updating'), true);
			$system->upgradeToRevision($this, $log, $targetRevision);

			$this->writeEvent('admin_versioning_upgrade_after...');
			Mage::dispatchEvent('admin_versioning_upgrade_after',
				['repository' => $system, 'revision' => $targetRevision, 'controller' => $this]);

			$this->writeTitle($helper->__('3) Cache'), true);
			$this->clearAllCache();

			// ÉTAPE 4
			$this->writeTitle($helper->__('4) Unlocking'));
			unlink($lock);

			$H['duration'] = ceil(microtime(true) - $H['duration']);
			$H['status']   = is_file($log) ? 'Update completed'."\n".trim(file_get_contents($log)) : 'Update completed';

			$result = [
				'url'   => '*/versioning_repository/index',
				'title' => $helper->__('Update completed (revision %s)', $targetRevision),
				'error' => false,
			];

			Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('Update to revision %s completed.', $targetRevision));
		}
		catch (Throwable $t) {

			$H['duration'] = ceil(microtime(true) - $H['duration']);
			$H['status']   = $t->getMessage();

			if (in_array($t->getMessage(), ['Not authorized', 'An update is in progress'])) {

				if ($t->getMessage() == 'An update is in progress') {
					$this->writeError($helper->__('Stop! Stop! Stop! An update is in progress.'));
					Mage::getSingleton('adminhtml/session')->addError($helper->__('Please wait, an update is in progress.'));
				}
				else {
					$this->writeError($helper->__('You are not authorized to perform this operation.'));
					Mage::getSingleton('adminhtml/session')->addError($helper->__('You are not authorized to perform this operation.'));
				}

				$result = [
					'url'   => '*/versioning_repository/index',
					'title' => $helper->__('Update error (revision %s)', $targetRevision),
					'error' => true,
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

				$this->writeTitle($helper->__('4) Unlocking'), true);
				if (is_file($lock))
					unlink($lock);

				$result = [
					'url'   => '*/versioning_repository/history',
					'title' => $helper->__('Update error (revision %s)', $targetRevision),
					'error' => true,
				];
			}
		}

		file_put_contents($helper->getHistoryLog(), '`'.implode('`,`', $H).'`'."\n", FILE_APPEND | LOCK_EX);
		return $result;
	}

	// affiche une commande ou une information pour savoir ce qu'il se passe
	// ajoute un peu de code HTML pour faire plus jolie
	protected function writeTitle(string $data, bool $endEvent = false) {
		echo $endEvent ? '</span>'."\n".$data."\n" : "\n".$data."\n";
		return $this;
	}

	protected function writeEvent(string $data) {
		echo '<span class="event">',$data,"\n";
		return $this;
	}

	public function writeError(string $data) {
		echo '<span class="error">',$data,'</span>',"\n";
		return $this;
	}

	public function writeNotice(string $data) {
		echo '<span class="notice">',$data,'</span>',"\n";
		return $this;
	}

	public function writeCommand(string $data) {
		echo '<code>',$data,'</code>',"\n";
		return $this;
	}

	// tente de vider totalement le cache (du moins essaye)
	// utilise les méthodes et événements du core puis supprime tous les répertoires
	// n'utilise surtout pas le fichier versioning.log pour Mage::log
	protected function clearAllCache() {

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
			Mage::getBaseDir('media').'/js/',
		];

		exec('rm -rf '.implode(' ', array_map('escapeshellarg', $dirs)));
		return $this;
	}
}