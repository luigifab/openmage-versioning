<?php
/**
 * Created V/27/02/2015
 * Updated M/28/02/2017
 *
 * Copyright 2011-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Model_Upgrade extends Luigifab_Versioning_Helper_Data {

	// attention, ceci est un singleton
	// comme chaque model dans Scm
	private $event = false;


	// met fin aux temporisations de sortie activés par Magento
	// est incapable de mettre fin à la temporisation de zlib.output_compression
	// n'utilise surtout pas le fichier versioning.log pour Mage::log
	public function disableAllBuffer() {

		header('Content-Encoding: chunked', true);
		header('Connection: Keep-Alive', true);

		ini_set('max_execution_time', 450); // 7 minutes 30
		ini_set('output_buffering', false);
		ini_set('implicit_flush', true);
		ini_set('display_errors', true);
		ob_implicit_flush(true);

		try {
			for ($i = 0; $i < ob_get_level(); $i++)
				ob_end_clean();
		}
		catch (Exception $e) {
			Mage::log($e->getMessage(), Zend_Log::ERR);
		}

		return $this;
	}


	// log toutes les informations de la mise à jour (enfin presque)
	// déroule le processus de mise à jour
	public function process($targetRevision, $useFlag) {

		$repository = Mage::getSingleton('versioning/scm_'.Mage::getStoreConfig('versioning/scm/type'));
		$lock = $this->getLock();
		$log = $this->getLastLog();

		try {
			// données de l'historique
			// l'ordre à une importance capitale (voir Luigifab_Versioning_Model_History)
			$H = array(
				'date'        => date('c'), // 0
				'current_rev' => $repository->getCurrentRevision(), // 1
				'target_rev'  => $targetRevision, // 2
				'remote_addr' => getenv('REMOTE_ADDR'), // 3
				'user'        => Mage::getSingleton('admin/session')->getData('user')->getData('username'), // 4
				'duration'    => microtime(true), // 5
				'status'      => 'Upgrade in progress', // 6
				'branch'      => $repository->getCurrentBranch(), // 7
			);

			// ÉTAPE 1
			$this->writeTitle($this->__('1) Locking and configuration check'));

			if (Mage::getSingleton('admin/session')->isAllowed('tools/versioning/upgrade') !== true)
				throw new Exception('Not authorized');

			if (is_file($lock))
				throw new Exception('An update is in progress');

			if (is_file($log))
				unlink($log);

			if (!empty($H['branch']))
				$this->writeNotice($this->__('Repository: %s / Branch: %s / Current revision: %s / Requested revision: %s',
					$repository->getType(), $H['branch'], $H['current_rev'], $targetRevision));
			else
				$this->writeNotice($this->__('Repository: %s / Current revision: %s / Requested revision: %s',
					$repository->getType(), $H['current_rev'], $targetRevision));

			file_put_contents($lock, $H['current_rev'].' ➩ '.$H['target_rev'].' from '.$H['remote_addr'].' by '.$H['user'], LOCK_EX);
			if ($useFlag)
				file_put_contents($this->getUpgradeFlag(), file_get_contents($lock));

			// ÉTAPE 2 et 3
			// avec les événements before et after
			$this->writeEvent('admin_versioning_upgrade_before...');
			Mage::dispatchEvent('admin_versioning_upgrade_before',
				array('repository' => $repository, 'revision' => $targetRevision, 'controller' => $this));

			$this->writeTitle($this->__('2) Updating'));
			$repository->upgradeToRevision($this, $log, $targetRevision);

			$this->writeEvent('admin_versioning_upgrade_after...');
			Mage::dispatchEvent('admin_versioning_upgrade_after',
				array('repository' => $repository, 'revision' => $targetRevision, 'controller' => $this));

			$this->writeTitle($this->__('3) Cache'));
			$this->clearAllCache();

			// ÉTAPE 4
			$this->writeTitle($this->__('4) Unlocking'));
			unlink($lock);

			$H['duration'] = ceil(microtime(true) - $H['duration']);
			$H['status']   = (is_file($log) && is_readable($log)) ? 'Update completed'."\n".trim(file_get_contents($log)) : 'Update completed';

			$result = array(
				'url'   => '*/versioning_repository/index',
				'title' => $this->__('Update completed (revision %s)', $targetRevision),
				'error' => false
			);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Update to revision %s completed.', $targetRevision));
		}
		catch (Exception $e) {

			if (!in_array($e->getMessage(), array('Not authorized', 'An update is in progress'))) {
				$H['duration'] = ceil(microtime(true) - $H['duration']);
				$H['status']   = (is_file($log) && is_readable($log)) ? $e->getMessage()."\n".trim(file_get_contents($log)) : $e->getMessage();
			}
			else {
				$H['duration'] = ceil(microtime(true) - $H['duration']);
				$H['status']   = $e->getMessage();
			}

			if (!in_array($e->getMessage(), array('Not authorized', 'An update is in progress'))) {

				$this->writeError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->addError(nl2br($e->getMessage()));

				$this->writeEvent('admin_versioning_upgrade_after...');
				Mage::dispatchEvent('admin_versioning_upgrade_after',
					array('repository' => $repository, 'revision' => $targetRevision, 'controller' => $this, 'exception' => $e));

				$this->writeTitle($this->__('4) Unlocking'));
				if (is_file($lock))
					unlink($lock);

				$result = array(
					'url'   => '*/versioning_repository/history',
					'title' => $this->__('Update error (revision %s)', $targetRevision),
					'error' => true
				);
			}
			else {
				if (in_array($e->getMessage(), array('An update is in progress'))) {
					$this->writeError($this->__('Stop! Stop! Stop! An update is in progress.'));
					Mage::getSingleton('adminhtml/session')->addError($this->__('Please wait, an update is in progress.'));
				}
				else {
					$this->writeError($this->__('You are not authorized to perform this operation.'));
					Mage::getSingleton('adminhtml/session')->addError($this->__('You are not authorized to perform this operation.'));
				}

				$result = array(
					'url'   => '*/versioning_repository/index',
					'title' => $this->__('Update error (revision %s)', $targetRevision),
					'error' => true
				);
			}
		}

		file_put_contents($this->getHistoryLog(), '`'.implode('`,`', $H).'`'."\n", FILE_APPEND | LOCK_EX);
		return $result;
	}


	// affiche une commande ou une information pour savoir ce qu'il se passe
	// ajoute un peu de code HTML pour faire plus jolie
	private function writeTitle($data) {
		if ($this->event) {
			echo '</span>',"\n",$data,"\n";
			$this->event = false;
		}
		else {
			echo "\n",$data,"\n";
		}
	}

	private function writeEvent($data) {
		$this->event = true;
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


	// tente de vider totalement le cache de Magento (du moins essaye)
	// utilise les méthodes et événements de Magento puis supprime tous les répertoires
	// n'utilise surtout pas le fichier versioning.log pour Mage::log
	private function clearAllCache() {

		try {
			Mage::dispatchEvent('adminhtml_cache_flush_all');
			Mage::app()->getCacheInstance()->flush();
		}
		catch (Exception $e) {
			Mage::log($e->getMessage(), Zend_Log::ERR);
		}

		try {
			Mage::app()->cleanCache();
			Mage::dispatchEvent('adminhtml_cache_flush_system');
		}
		catch (Exception $e) {
			Mage::log($e->getMessage(), Zend_Log::ERR);
		}

		try {
			Mage::getModel('core/design_package')->cleanMergedJsCss();
			Mage::dispatchEvent('clean_media_cache_after');
		}
		catch (Exception $e) {
			Mage::log($e->getMessage(), Zend_Log::ERR);
		}

		// suppression des répertoires
		$dirs = array(
			Mage::getBaseDir('var').'/cache/',
			Mage::getBaseDir('var').'/full_page_cache/',
			Mage::getBaseDir('media').'/css/',
			Mage::getBaseDir('media').'/css_secure/',
			Mage::getBaseDir('media').'/js_secure/',
			Mage::getBaseDir('media').'/js/',
			Mage::getBaseDir().'/includes/src/'
		);

		exec('rm -rf '.implode(' ', $dirs));
	}
}