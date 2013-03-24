<?php
/**
 * Created S/03/12/2011
 * Updated D/24/03/2013
 * Version 30
 *
 * Copyright 2011-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Versioning_UpgradeController extends Mage_Adminhtml_Controller_action {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/versioning');
	}

	public function confirmAction() {
		$this->loadLayout();
		$this->renderLayout();
	}


	// #### Gestion de la mise à jour ############################## debug ## i18n ## public ### //
	// = révision : 59
	// » Affiche l'état d'avancement de la mise à jour sous la forme d'une page HTML
	// » Désactive toutes les touches du clavier et empèche la fermeture de la page
	// » S'assure de ne pas intervernir juste après l'identification et que l'action de mise à jour a été confirmée
	public function runAction() {

		// *** Préparation ************************************** //
		$revision = $this->getRequest()->getParam('revision');

		if ($this->getRequest()->getParam('confirm') !== 'true') {
			$this->_forward('confirm');
			return;
		}

		if (Mage::getStoreConfig('versioning/scm/enabled') !== '1') {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please configure the module before use it.'));
			$this->_redirect('adminhtml/system_config/edit', array('section' => 'versioning'));
			return false;
		}

		if (Mage::getSingleton('admin/session')->isFirstPageAfterLogin() || (strlen($revision) < 1)) {
			$this->_redirect('*/versioning_repository/index');
			return false;
		}

		$this->disableAllBuffer();

		// *** Sortie HTML ************************************** //
		$lang = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		echo "\n".'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$lang.'" lang="'.$lang.'">';
		echo "\n".'<head>';
		echo "\n".'<title>'.$this->__('Upgrading').' - '.Mage::getStoreConfig('design/head/default_title').'</title>';
		echo "\n".'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo "\n".'<meta http-equiv="Content-Script-Type" content="text/javascript" />';
		echo "\n".'<meta http-equiv="Content-Style-Type" content="text/css" />';
		echo "\n".'<meta http-equiv="Content-Language" content="'.$lang.'" />';
		echo "\n".'<link rel="icon" href="'.Mage::getDesign()->getSkinUrl('images/ajax-loader.gif').'" type="image/x-icon" />';
		// http://commons.wikimedia.org/wiki/File:MarsSunset.jpg
		echo "\n".'<style type="text/css">';
		echo "\n". '* { margin:0; padding:0; }';
		echo "\n". 'html { height:100%; }';
		echo "\n". 'body {';
		echo "\n". ' font:1em Verdana, sans-serif; overflow:hidden;';
		echo "\n". ' background-color:#E6E6E6; background-size:100% 100%;';
		echo "\n". ' background-image:url('.Mage::getDesign()->getSkinUrl('images/luigifab/versioning/mars-sunset.jpg').');';
		echo "\n". '}';
		echo "\n". 'div.obj { position:absolute; top:5em; left:31px; opacity:0.25; }';
		echo "\n". 'div.obj object { display:block; }';
		echo "\n". 'div.ctn {';
		echo "\n". ' position:absolute; top:3em; left:160px; right:-2em; bottom:25%; padding:0.8em 4em 0 1em; overflow-y:scroll;';
		echo "\n". ' border-radius:1.2em; -moz-border-radius:1.2em; -webkit-border-radius:1.2em;';
		echo "\n". ' background-color:white; background-color:rgba(255, 255, 255, 0.2);';
		echo "\n". '}';
		echo "\n". 'p { padding-bottom:0.8em; }';
		echo "\n". 'p em { font-size:0.8em; }';
		echo "\n". 'pre { padding-bottom:1.4em; font:0.8em Verdana, sans-serif; line-height:140%; white-space:pre-wrap; }';
		echo "\n". 'pre code { display:inline-block; margin-top:0.2em; font:0.85em Verdana, sans-serif; font-style:italic; line-height:16px; color:#222; }';
		echo "\n". 'pre code span { color:#333; }';
		echo "\n". 'pre span.notice { font-size:0.85em; font-style:italic; }';
		echo "\n". 'pre span.error { font-size:0.85em; font-style:italic; color:red; }';
		echo "\n".'</style>';
		echo "\n".'<!--[if IE]><style type="text/css">div.obj { display:none; }</style><![endif]-->';
		echo "\n".'<script type="text/javascript">';
		echo "\n". '// disable keys of keyboard';
		echo "\n". 'function disableKeyboard(ev) {';
		echo "\n".  'if (typeof ev !== "undefined") { ev.preventDefault(); ev.stopPropagation(); }';
		echo "\n".  'else { event.cancelBubble = true; event.returnValue = false; }';
		echo "\n". '}';
		echo "\n". '// prevents window or tab closing';
		echo "\n". 'function disableClose(ev) {';
		echo "\n".  'if (typeof ev !== "undefined") { ev.preventDefault(); ev.stopPropagation(); return ""; }';
		echo "\n".  'else { event.cancelBubble = true; event.returnValue = ""; return ""; }';
		echo "\n". '}';
		echo "\n". '// register events';
		echo "\n". 'window.onbeforeunload = disableClose;';
		echo "\n". 'document.onkeydown = disableKeyboard;';
		echo "\n". '// auto scroll page';
		echo "\n". 'function autoScroll() {';
		echo "\n".  'document.getElementById("scroll").scrollTop += 10000;';
		echo "\n". '}';
		echo "\n". 'window.setInterval(autoScroll, 100);';
		echo "\n".'</script>';
		echo "\n".'</head>';

		echo "\n".'<body>';
		echo "\n".'<div class="obj"><object data="'.Mage::getDesign()->getSkinUrl('images/luigifab/versioning/info.svg.php').'" type="image/svg+xml" width="100" height="70" id="state"></object></div>';
		echo "\n".'<div class="ctn" id="scroll">';
		echo "\n".'<p class="first"><strong>'.$this->__('Starting upgrade (revision %s)', $revision).'</strong>';
		echo "\n".'<br /><em>'.$this->__('Do not touch anything / Do not try to cancel this operation').'</em></p>'."\n";

		sleep(1);

		echo '<pre>';
		$data = $this->processUpgrade($revision);
		echo '</pre>';

		echo "\n".'<script type="text/javascript">';
		echo "\n". '// svg animation colors';
		echo "\n". 'try {';
		echo "\n".  'var svg = document.getElementById("state").getSVGDocument();';
		echo "\n".  'svg.getElementById("a").setAttribute("values", "#222;'.$data['exclam'].'");';
		echo "\n".  'svg.getElementById("b").setAttribute("values", "'.$data['exclam'].';#222");';
		echo "\n".  'svg.getElementById("c").setAttribute("values", "#222;'.$data['diamond'].'");';
		echo "\n".  'svg.getElementById("d").setAttribute("values", "'.$data['diamond'].';#222");';
		echo "\n". '}';
		echo "\n". 'catch (ee) {';
		echo "\n".  'if (!document.getElementById("state").getSVGDocument()) {';
		echo "\n".   'document.getElementById("state").onload = function () {';
		echo "\n".    'var svg = document.getElementById("state").getSVGDocument();';
		echo "\n".    'svg.getElementById("a").setAttribute("values", "#222;'.$data['exclam'].'");';
		echo "\n".    'svg.getElementById("b").setAttribute("values", "'.$data['exclam'].';#222");';
		echo "\n".    'svg.getElementById("c").setAttribute("values", "#222;'.$data['diamond'].'");';
		echo "\n".    'svg.getElementById("d").setAttribute("values", "'.$data['diamond'].';#222");';
		echo "\n".   '};';
		echo "\n".  '}';
		echo "\n". '}';
		echo "\n".'</script>';

		sleep(1);

		echo "\n".'<p class="last"><strong>'.$data['title'].'</strong>';
		echo "\n".'<br /><em>'.$this->__('Back to Magento in one second').'</em></p>';
		echo "\n".'</div>';

		echo "\n".'<script type="text/javascript">';
		echo "\n". '// clear disableClose function, go to Magento backend, re-reregister disableClose function';
		echo "\n". '// register disableClose delayed to prevent close warning in Chrome/Chromium browser';
		echo "\n". 'window.setTimeout(function () {';
		echo "\n".  'window.onbeforeunload = null;';
		echo "\n".  'location.href = "'.$this->getUrl($data['url']).'";';
		echo "\n".  'window.setTimeout(function () {';
		echo "\n".   'window.onbeforeunload = disableClose;';
		echo "\n".  '}, 1);';
		echo "\n". '}, '.(($data['diamond'] === 'red') ? 4000 : 1000).');';
		echo "\n".'</script>';
		echo "\n".'</body>';
		echo "\n".'</html>';

		exit(0);
	}


	// #### Gestion de la mise à jour ############################# debug ## i18n ## private ### //
	// = révision : 76
	// » Log les informations du processus de mise à jour
	// » Met à jour le code application, purge le cache et régénère les fichiers minifiés lorsque nécessaire
	// » Informe l'utiliseur en cas de changement de version
	private function processUpgrade($targetRevision) {

		try {
			// *** Préparation ********************************* //
			$helper = Mage::helper('versioning');
			$type = Mage::getStoreConfig('versioning/scm/type');

			$lock = $helper->getLock();
			$flag = $helper->getUpgradeFlag();
			$log = $helper->getLastlogFile();

			$repository = Mage::getModel('versioning/scm_'.$type);
			$currentRevision = $repository->getCurrentRevision();

			$status = array();
			$status['url'] = '*/versioning_repository/index';

			$logger = array();
			$logger['date'] = date('c', time());
			$logger['current_rev'] = $currentRevision;
			$logger['target_rev'] = $targetRevision;
			$logger['remote_addr'] = (getenv('REMOTE_ADDR') !== false) ? getenv('REMOTE_ADDR') : 'unknown';
			$logger['user'] = Mage::getSingleton('admin/session')->getUser()->getUsername();
			$logger['duration'] = microtime(true);
			$logger['status'] = 'Upgrade in progress';

			// *** Numéro de version *************************** //
			$file = file_get_contents(Mage::getModuleDir('etc', 'Luigifab_Versioning').'/config.xml');
			preg_match('#<version>([0-9\.]+)<\/version>#', $file, $version);
			$version = array_pop($version);

			// *** ÉTAPE 1 ************************************* //
			$this->writeTitle($this->__('1) Locking and configuration check'));

			if (Mage::getStoreConfig('versioning/scm/type') === 'git') {
				$logger['branch'] = $repository->getCurrentBranch();
				$this->writeNotice($this->__('Repository: %s / Branch: %s / Current revision: %s / Requested revision: %s', $type, $logger['branch'], $currentRevision, $targetRevision));
			}
			else {
				$logger['branch'] = '';
				$this->writeNotice($this->__('Repository: %s / Current revision: %s / Requested revision: %s', $type, $currentRevision, $targetRevision));
			}

			if (is_file($lock))
				throw new Exception('An upgrade is already underway');

			if (is_file($log))
				unlink($log);

			if (Mage::getStoreConfig('versioning/scm/maintenance') === '1') {
				file_put_contents($lock, $logger['current_rev'].' » '.$logger['target_rev'].' from '.$logger['remote_addr'].' by '.$logger['user']);
				file_put_contents($flag, $logger['current_rev'].' » '.$logger['target_rev'].' from '.$logger['remote_addr'].' by '.$logger['user']);
			}
			else {
				file_put_contents($lock, $logger['current_rev'].' » '.$logger['target_rev'].' from '.$logger['remote_addr'].' by '.$logger['user']);
			}

			// *** Événement before **************************** //
			$this->dispatchEvent('admin_versioning_upgrade_before', $repository, $targetRevision);

			// *** ÉTAPE 2 ************************************* //
			$this->writeTitle($this->__('2) Upgrading'));
			$repository->upgradeToRevision($this, $log, $targetRevision);

			// *** ÉTAPE 3 ************************************* //
			$this->writeTitle($this->__('3) Cache'));
			$messages = array();

			if ($helper->isCompressorInstalled()) {

				// mise à jour du code application
				if ($this->getRequest()->getParam('code') === 'true') {

					array_push($messages, $this->__('upgrading application code'));

					$resource = Mage::getSingleton('core/resource');
					$conn = $resource->getConnection('core_write');

					if (Mage::getStoreConfig('css/general/enabled') === '1')
						$conn->query('UPDATE '.$resource->getTableName('core_config_data').' SET value = "'.date('YmdHis', Mage::getModel('core/date')->timestamp(time())).'" WHERE path = "css/generate/code"');

					if (Mage::getStoreConfig('js/general/enabled') === '1')
						$conn->query('UPDATE '.$resource->getTableName('core_config_data').' SET value = "'.date('YmdHis', Mage::getModel('core/date')->timestamp(time())).'" WHERE path = "js/generate/code"');
				}

				// nettoyage du cache
				array_push($messages, $this->__('cleaning all cache'));
				$this->clearAllCache();

				// régénération des fichiers minifiés
				try {
					array_push($messages, $this->__('regeneration of minified files'));
					$compressor = Mage::getBlockSingleton('compressor/head');
					$compressor->start('css');
					$compressor->start('js');
				}
				catch (Exception $e) {
					Mage::log($e->getMessage());
				}
			}
			else {
				array_push($messages, $this->__('cleaning all cache'));
				$this->clearAllCache();
			}

			$this->writeCommand(implode("\n", $messages));

			// *** Événement after ***************************** //
			$this->dispatchEvent('admin_versioning_upgrade_after', $repository, $targetRevision);

			// *** ÉTAPE 5 ************************************* //
			$this->writeTitle($this->__('4) Unlocking'));
			unlink($lock);

			if (is_file($flag) && ($this->getRequest()->getParam('flag') !== 'true'))
				unlink($flag);

			// *** Numéro de version *************************** //
			$file = file_get_contents(Mage::getModuleDir('etc', 'Luigifab_Versioning').'/config.xml');
			preg_match('#<version>([0-9\.]+)<\/version>#', $file, $newVersion);
			$newVersion = array_pop($newVersion);

			$this->checkModuleVersion($version, $newVersion);

			// *** Finalisation ******************************** //
			$status['exclam'] = 'blue'; $status['diamond'] = 'orange';
			$status['title'] = $this->__('Upgrade completed (revision %s)', $targetRevision);
			$logger['duration'] = ceil(microtime(true) - $logger['duration']);
			$logger['status'] = 'Upgrade completed'."\n".trim(file_get_contents($log));

			if ((Mage::getStoreConfig('versioning/tweak/showlog') === '1') && (Mage::getStoreConfig('versioning/tweak/deletelog') !== '1'))
				$status['url'] = '*/versioning_repository/lastlog';

			if ((Mage::getStoreConfig('versioning/tweak/showlog') !== '1') && (Mage::getStoreConfig('versioning/tweak/deletelog') === '1'))
				unlink($log);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Upgrade to revision %s completed.', $targetRevision));
		}
		catch (Exception $e) {

			// *** Gestion des erreurs ************************* //
			$status['exclam'] = 'red'; $status['diamond'] = 'red';
			$status['title'] = $this->__('Upgrade error (revision %s)', $targetRevision);
			$logger['duration'] = ceil(microtime(true) - $logger['duration']);
			$logger['status'] = (is_file($log) && is_readable($log)) ? $e->getMessage()."\n".trim(file_get_contents($log)) : $e->getMessage();

			if ($e->getMessage() !== 'An upgrade is already underway') {

				$status['url'] = '*/versioning_repository/lastlog';

				$this->writeError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->addError(nl2br($e->getMessage()));

				$this->writeTitle($this->__('4) Unlocking'));
				unlink($lock);
			}
			else {
				$this->writeError($this->__('Stop! Stop! Stop! An upgrade is already underway.'));
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please wait, an upgrade is already underway.'));
			}

			// *** Événement after ***************************** //
			$this->dispatchEvent('admin_versioning_upgrade_after', $repository, $targetRevision, $e);
		}

		$this->writeLog($logger);
		return $status;
	}


	// #### Gestion de l'affichage des commandes ############################ public/private ### //
	// = révision : 17
	// » Affiche une commande ou une information pour savoir ce qu'il se passe
	// » Ajoute un peu de code HTML pour faire plus jolie
	private function writeLog($data) {
		file_put_contents(Mage::helper('versioning')->getHistoryFile(), '`'.implode('`,`', $data).'`'."\n", FILE_APPEND | LOCK_EX);
	}

	private function writeNotice($data) {
		echo '<span class="notice">',$data,'</span>',"\n";
	}

	private function writeError($data) {
		echo '<span class="error">',$data,'</span>',"\n";
	}

	public function writeTitle($data) {
		sleep(1);
		echo "\n",$data,"\n";
	}

	public function writeCommand($data) {
		echo '<code>',$data,'</code>',"\n";
	}


	// #### Désactivation des buffers ##################################### debug ## private ### //
	// = révision : 9
	// » Met fin aux temporisations de sortie activés par Magento
	// » Est incapable de mettre fin à la temporisation de zlib.output_compression
	private function disableAllBuffer() {

		header('Content-Encoding: chunked', true);
		header('Connection: Keep-Alive', true);

		ini_set('max_execution_time', 360);
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


	// #### Changement de version du module ################################ i18n ## private ### //
	// = révision : 2
	// » Prépare une note d'information lorsque la version du module change
	private function checkModuleVersion($v1, $v2) {

		if ($v1 !== $v2) {

			if (version_compare($v1, $v2, '>')) {
				$tmp = $v1;
				$v1 = $v2;
				$v2 = $tmp;
			}

			Mage::getSingleton('adminhtml/session')->addNotice($this->__('Please note that this module (Luigifab/Versioning) has been updated during upgrade process.<br />It changes from version %s to version %s.', $v1, $v2));
		}
	}


	// #### Événement ################################################## dispatch ## private ### //
	// = révision : 2
	// » Déclenche l'événement avec ses paramètres si la configuration le permet
	private function dispatchEvent($event, $repository, $targetRevision, $e = null) {

		if (Mage::getStoreConfig('versioning/scm/events') === '1')
			Mage::dispatchEvent($event, array('repository' => $repository, 'revision' => $targetRevision, 'controller' => $this, 'exception' => $e));
	}


	// #### Nettoyage du cache ############################################ debug ## private ### //
	// = révision : 25
	// » Tente de vider totalement le cache de Magento (du moins essaye)
	// » Utilise les méthodes et événements de Magento puis supprime tous les répertoires
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
		$dir = array(
			Mage::getBaseDir('var').'/cache/',
			Mage::getBaseDir('var').'/full_page_cache/',
			Mage::getBaseDir('media').'/css/',
			Mage::getBaseDir('media').'/css_secure/',
			Mage::getBaseDir('media').'/js_secure/',
			Mage::getBaseDir('media').'/js/',
			Mage::getBaseDir().'/includes/src/'
		);

		exec('rm -rf '.implode(' ', $dir));

		// fichiers minifiés du module luigifab/compressor
		if (is_dir(Mage::getBaseDir('media').'/compressor') && is_writable(Mage::getBaseDir('media').'/compressor')) {

			system('rm -rf '.Mage::getBaseDir('media').'/compressor/*');

			Mage::log(sprintf('Minified files deleted from %s by %s', ((getenv('REMOTE_ADDR') !== false) ? getenv('REMOTE_ADDR') : 'unknown'), Mage::getSingleton('admin/session')->getUser()->getUsername()), Zend_Log::INFO);
		}
	}
}