<?php
/**
 * Created V/27/02/2015
 * Updated J/14/05/2015
 * Version 51
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

class Luigifab_Versioning_Model_Upgrade extends Luigifab_Versioning_Helper_Data {

	private $event = false;


	// #### Désactivation des buffers ############################################### public ### //
	// = révision : 11
	// » Met fin aux temporisations de sortie activés par Magento
	// » Est incapable de mettre fin à la temporisation de zlib.output_compression
	// » N'utilise surtout pas le fichier versioning.log pour Mage::log
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


	// #### Gestion de la mise à jour ####################################### i18n ## public ### //
	// = révision : 87
	// » Log toutes les informations de la mise à jour
	// » Déroule le processus de mise à jour
	public function process($targetRevision, $useFlag) {

		$repository = Mage::getModel('versioning/scm_'.Mage::getStoreConfig('versioning/scm/type'));
		$lock = $this->getLock();
		$log = $this->getLastLog();

		try {
			// données de l'historique
			// l'ordre à une importance capitale (voir Luigifab_Versioning_Model_History)
			$H = array(
				'date'        => date('c', time()), // 0
				'current_rev' => $repository->getCurrentRevision(), // 1
				'target_rev'  => $targetRevision, // 2
				'remote_addr' => (getenv('REMOTE_ADDR') !== false) ? getenv('REMOTE_ADDR') : 'unknown', // 3
				'user'        => Mage::getSingleton('admin/session')->getUser()->getUsername(), // 4
				'duration'    => microtime(true), // 5
				'status'      => 'Upgrade in progress', // 6
				'branch'      => $repository->getCurrentBranch(), // 7
			);

			// ÉTAPE 1
			$this->writeTitle($this->__('1) Locking and configuration check'));

			if (is_file($log))
				unlink($log);

			if (is_string($H['branch']) && (strlen($H['branch']) > 0))
				$this->writeNotice($this->__('Repository: %s / Branch: %s / Current revision: %s / Requested revision: %s',
					$repository->getType(), $H['branch'], $H['current_rev'], $targetRevision));
			else
				$this->writeNotice($this->__('Repository: %s / Current revision: %s / Requested revision: %s',
					$repository->getType(), $H['current_rev'], $targetRevision));

			if (is_file($lock))
				throw new Exception('An upgrade is already underway');

			if ($useFlag)
				file_put_contents($this->getUpgradeFlag(), $H['current_rev'].' » '.$H['target_rev'].' from '.$H['remote_addr'].' by '.$H['user']);

			file_put_contents($lock, $H['current_rev'].' » '.$H['target_rev'].' from '.$H['remote_addr'].' by '.$H['user']);

			// ÉTAPE 2 et 3
			// avec les événements before et after
			$this->writeEvent('admin_versioning_upgrade_before...');
			Mage::dispatchEvent('admin_versioning_upgrade_before',
				array('repository' => $repository, 'revision' => $targetRevision, 'controller' => $this));

			$this->writeTitle($this->__('2) Upgrading'));
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
			$H['duration'] = ($H['duration'] < 1000) ? $H['duration'] : 1;
			$H['status']   = (is_file($log) && is_readable($log)) ? 'Upgrade completed'."\n".trim(file_get_contents($log)) : 'Upgrade completed';

			$result = array(
				'url'   => '*/versioning_repository/index',
				'title' => $this->__('Upgrade completed (revision %s)', $targetRevision),
				'error' => false
			);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Upgrade to revision %s completed.', $targetRevision));
		}
		catch (Exception $e) {

			$H['duration'] = ceil(microtime(true) - $H['duration']);
			$H['duration'] = ($H['duration'] < 1000) ? $H['duration'] : 1;
			$H['status']   = (is_file($log) && is_readable($log)) ? $e->getMessage()."\n".trim(file_get_contents($log)) : $e->getMessage();

			if ($e->getMessage() !== 'An upgrade is already underway') {

				$result = array(
					'url'   => '*/versioning_repository/history',
					'title' => $this->__('Upgrade error (revision %s)', $targetRevision),
					'error' => true
				);

				$this->writeError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->addError(nl2br($e->getMessage()));

				$this->writeEvent('admin_versioning_upgrade_after...');
				Mage::dispatchEvent('admin_versioning_upgrade_after',
					array('repository' => $repository, 'revision' => $targetRevision, 'controller' => $this, 'exception' => $e));

				$this->writeTitle($this->__('4) Unlocking'));

				if (is_file($lock))
					unlink($lock);
			}
			else {
				$result = array(
					'url'   => '*/versioning_repository/index',
					'title' => $this->__('Upgrade error (revision %s)', $targetRevision),
					'error' => true
				);

				$this->writeError($this->__('Stop! Stop! Stop! An upgrade is already underway.'));
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please wait, an upgrade is already underway.'));
			}
		}

		file_put_contents($this->getHistoryLog(), '`'.implode('`,`', $H).'`'."\n", FILE_APPEND | LOCK_EX);
		return $result;
	}


	// #### Gestion de l'affichage des commandes ############################ private/public ### //
	// = révision : 20
	// » Affiche une commande ou une information pour savoir ce qu'il se passe
	// » Ajoute un peu de code HTML pour faire plus jolie
	private function writeEvent($data) {
		$this->event = true;
		echo '<span class="event">',$data,"\n";
	}

	private function writeTitle($data) {

		if ($this->event) {
			echo '</span>',"\n\n",$data,"\n";
			$this->event = false;
		}
		else {
			echo "\n",$data,"\n";
		}
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


	// #### Nettoyage du cache ##################################################### private ### //
	// = révision : 27
	// » Tente de vider totalement le cache de Magento (du moins essaye)
	// » Utilise les méthodes et événements de Magento puis supprime tous les répertoires
	// » N'utilise surtout pas le fichier versioning.log pour Mage::log
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