<?php
/**
 * Created S/03/12/2011
 * Updated S/13/10/2012
 * Version 26
 *
 * Copyright 2011-2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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


	// #### Gestion de la mise à jour ############################## debug ## i18n ## public ### //
	// = révision : 52
	// » Affiche l'état d'avancement de la mise à jour sous la forme d'une page html
	// » Désactive toutes les touches du clavier et empèche la fermeture de la page
	// » S'assure de ne pas intervernir juste après l'identification
	public function runAction() {

		// *** Préparation ************************************** //
		$this->disableAllBuffer();

		$revision = $this->getRequest()->getParam('revision');

		if (Mage::getStoreConfig('versioning/scm/enabled') !== '1') {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please configure the module before use it.'));
			$this->_redirect('adminhtml/system_config/edit', array('section' => 'versioning'));
			return false;
		}

		if (Mage::getSingleton('admin/session')->isFirstPageAfterLogin() || (strlen($revision) < 1)) {
			$this->_redirect('*/versioning_repository');
			return false;
		}

		// *** Sortie HTML ************************************** //
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		echo "\n".'<html xmlns="http://www.w3.org/1999/xhtml">';

		echo "\n".'<head>';
		echo "\n".'<title>'.$this->__('Upgrading').' - '.Mage::getStoreConfig('design/head/default_title').'</title>';
		echo "\n".'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo "\n".'<meta http-equiv="Content-Script-Type" content="text/javascript" />';
		echo "\n".'<meta http-equiv="Content-Style-Type" content="text/css" />';
		echo "\n".'<link rel="icon" href="'.Mage::getDesign()->getSkinUrl('images/ajax-loader.gif').'" type="image/x-icon" />';
		echo "\n".'<style type="text/css">';
		echo "\n". '* { margin:0; padding:0; }';
		echo "\n". 'body { margin:1.8em 3em 3.5em 150px; font:1em Verdana, sans-serif; color:#AAA; overflow-y:scroll; background:url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAAXNSR0IArs4c6QAAABJJREFUCNdjEBQU/M9AFMCpEgBn3AJlgv0NRQAAAABJRU5ErkJggg==") black; }';
		echo "\n". 'div.obj { position:fixed; left:0; right:0; top:3.8em; padding:10px 33px; background-color:black; }';
		echo "\n". 'div.ctn { position:relative; padding:0 1.3em; min-height:8em; border-radius:1.5em; border:1px solid #111; background-color:black; background-clip:padding-box; }';
		echo "\n". 'object { display:block; }';
		echo "\n". 'p { padding:1em 0; }';
		echo "\n". 'p.first { border-bottom:1px dashed #111; }';
		echo "\n". 'p.last { border-top:1px dashed #111; }';
		echo "\n". 'p span { font-size:0.8em; font-style:italic; }';
		echo "\n". 'pre { padding:1.4em 0; font:0.8em Verdana, sans-serif; line-height:1.4em; white-space:pre-wrap; }';
		echo "\n". 'pre code { display:inline-block; margin:0.3em 0 0; font:1.1em monospace; line-height:1.5em; font-style:italic; color:#333; }';
		echo "\n". 'pre code span { color:#555; }';
		echo "\n". 'pre span.notice { font-size:0.85em; font-style:italic; color:#888; }';
		echo "\n". 'pre span.error { font-size:0.85em; font-style:italic; color:red; }';
		echo "\n".'</style>';
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
		echo "\n".  'window.scrollBy(0, 10000)';
		echo "\n". '}';
		echo "\n". 'window.setInterval(autoScroll, 100);';
		echo "\n".'</script>';
		echo "\n".'</head>';

		echo "\n".'<body>';
		echo "\n".'<div class="obj"><object data="'.Mage::getDesign()->getSkinUrl('images/luigifab/versioning/info.svg.php').'" type="image/svg+xml" width="100" height="70" id="state"></object></div>';

		echo "\n".'<div class="ctn">';
		echo "\n".'<p class="first"><strong>'.$this->__('Starting upgrade (revision %s)', $revision).'</strong>';
		echo "\n".'<br /><span>'.$this->__('Do not touch anything / Do not try to cancel this operation').'</span></p>'."\n";

		sleep(1);

		echo '<pre>';
		$data = $this->processUpgrade($revision);
		echo '</pre>';

		echo "\n".'<script type="text/javascript">';
		echo "\n". '// svg animation colors';
		echo "\n". 'try {';
		echo "\n".  'document.getElementById("state").getSVGDocument().getElementById("losange").setAttribute("fill", "'.$data['losange'].'");';
		echo "\n".  'document.getElementById("state").getSVGDocument().getElementById("exclamA").setAttribute("fill", "'.$data['exclam'].'");';
		echo "\n".  'document.getElementById("state").getSVGDocument().getElementById("exclamB").setAttribute("fill", "'.$data['exclam'].'");';
		echo "\n". '}';
		echo "\n". 'catch (ee) { }';
		echo "\n".'</script>';

		sleep(1);

		echo "\n".'<p class="last"><strong>'.$data['title'].'</strong>';
		echo "\n".'<br /><span>'.$this->__('Back to Magento in one second').'</span></p>';
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
		echo "\n". '}, '.(($data['losange'] === 'red') ? 4000 : 1000).');';
		echo "\n".'</script>';
		echo "\n".'</body>';
		echo "\n".'</html>';

		exit(0);
	}


	// #### Gestion de la mise à jour ############################# debug ## i18n ## private ### //
	// = révision : 71
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
			$status['url'] = '*/versioning_repository';

			$logger = array();
			$logger['date'] = date('c', time());
			$logger['current_rev'] = $currentRevision;
			$logger['target_rev'] = $targetRevision;
			$logger['remote_addr'] = (getenv('REMOTE_ADDR') !== false) ? getenv('REMOTE_ADDR') : 'unknown';
			$logger['user'] = Mage::getSingleton('admin/session')->getUser()->getUsername();
			$logger['duration'] = microtime(true);
			$logger['status'] = 'Upgrade in progress';

			// *** Numéro de version *************************** //
			$file = file_get_contents(Mage::getBaseDir('code').'/community/Luigifab/Versioning/etc/config.xml');
			preg_match('#<version>([0-9\.]+)<\/version>#', $file, $version);

			$version = array_pop($version);

			// *** Événement before **************************** //
			if (Mage::getStoreConfig('versioning/scm/events') === '1') {
				Mage::dispatchEvent('admin_versioning_upgrade_before', array(
					'repository' => $repository, 'logger' => $logger, 'status' => $status, 'revision' => $targetRevision
				));
			}

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

			if (is_file($lock)) {
				throw new Exception('An upgrade is already underway');
			}

			if (is_file($log)) {
				unlink($log);
			}

			if (Mage::getStoreConfig('versioning/scm/maintenance') === '1') {
				file_put_contents($lock, $logger['current_rev'].' » '.$logger['target_rev'].' from '.$logger['remote_addr'].' by '.$logger['user']);
				file_put_contents($flag, $logger['current_rev'].' » '.$logger['target_rev'].' from '.$logger['remote_addr'].' by '.$logger['user']);
			}
			else {
				file_put_contents($lock, $logger['current_rev'].' » '.$logger['target_rev'].' from '.$logger['remote_addr'].' by '.$logger['user']);
			}

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

					foreach (array('css', 'js') as $type) {
						$resource = Mage::getSingleton('core/resource');
						$conn = $resource->getConnection('core_write');

						if (Mage::getStoreConfig($type.'/general/enabled') === '1')
							$conn->query('UPDATE '.$resource->getTableName('core_config_data').' SET value = "'.date('YmdHis', Mage::getModel('core/date')->timestamp(time())).'" WHERE path = "'.$type.'/generate/code"');
					}
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

			// *** ÉTAPE 5 ************************************* //
			$this->writeTitle($this->__('4) Unlocking'));
			unlink($lock);

			if (is_file($flag)) {
				if ($this->getRequest()->getParam('flag') !== 'true')
					unlink($flag);
			}

			// *** Numéro de version *************************** //
			$file = file_get_contents(Mage::getBaseDir('code').'/community/Luigifab/Versioning/etc/config.xml');
			preg_match('#<version>([0-9\.]+)<\/version>#', $file, $newVersion);

			$newVersion = array_pop($newVersion);

			if ($newVersion !== $version) {
				if (version_compare($newVersion, $version, '<'))
					Mage::getSingleton('adminhtml/session')->addNotice($this->__('Please note that this module (Luigifab/Versioning) has been updated during upgrade process.<br />It changes from version %s to version %s.', $newVersion, $version));
				else
					Mage::getSingleton('adminhtml/session')->addNotice($this->__('Please note that this module (Luigifab/Versioning) has been updated during upgrade process.<br />It changes from version %s to version %s.', $version, $newVersion));
			}

			// *** Finalisation ******************************** //
			$status['exclam'] = 'blue'; $status['losange'] = 'orange';
			$status['title'] = $this->__('Upgrade completed (revision %s)', $targetRevision);
			$logger['duration'] = ceil(microtime(true) - $logger['duration']);
			$logger['status'] = 'Upgrade completed'."\n".trim(file_get_contents($log));

			if ((Mage::getStoreConfig('versioning/tweak/showlog') === '1') && (Mage::getStoreConfig('versioning/tweak/deletelog') !== '1'))
				$status['url'] = '*/versioning_repository/lastlog';

			if ((Mage::getStoreConfig('versioning/tweak/showlog') !== '1') && (Mage::getStoreConfig('versioning/tweak/deletelog') === '1'))
				unlink($log);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Upgrade to revision %s completed.', $targetRevision));

			// *** Événement after ***************************** //
			if (Mage::getStoreConfig('versioning/scm/events') === '1') {
				Mage::dispatchEvent('admin_versioning_upgrade_after', array(
					'repository' => $repository, 'logger' => $logger, 'status' => $status, 'revision' => $targetRevision
				));
			}
		}
		catch (Exception $e) {

			// *** Gestion des erreurs ************************* //
			$status['exclam'] = 'red'; $status['losange'] = 'red';
			$status['title'] = $this->__('Upgrade error (revision %s)', $targetRevision);
			$logger['duration'] = ceil(microtime(true) - $logger['duration']);
			$logger['status'] = (is_file($log) && is_readable($log)) ? trim(file_get_contents($log)) : $e->getMessage();

			if ($e->getMessage() !== 'An upgrade is already underway') {

				$status['url'] = '*/versioning_repository/lastlog';

				$this->writeError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->addError(nl2br($e->getMessage()));

				$this->writeTitle($this->__('4) Unlocking'));
				unlink($lock);

				if (is_file($flag))
					unlink($flag);
			}
			else {
				$this->writeError($this->__('Stop! Stop! Stop! An upgrade is already underway.'));
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please wait, an upgrade is already underway.'));
			}

			// *** Événement after ***************************** //
			if (Mage::getStoreConfig('versioning/scm/events') === '1') {
				Mage::dispatchEvent('admin_versioning_upgrade_after', array(
					'repository' => $repository, 'logger' => $logger, 'status' => $status, 'revision' => $targetRevision, 'exception' => $e
				));
			}
		}

		$this->writeLog($logger);
		return $status;
	}


	// #### Gestion des commandes ########################################### public/private ### //
	// = révision : 15
	// » Affiche une commande ou une information pour savoir ce qu'il se passe
	// » Ajoute un peu de code HTML pour faire jolie
	private function writeLog($data) {
		file_put_contents(Mage::helper('versioning')->getHistoryFile(), '`'.implode('`,`', $data).'`'."\n", FILE_APPEND | LOCK_EX);
	}

	private function writeTitle($data) {
		sleep(1);
		echo "\n",$data,"\n";
	}

	private function writeNotice($data) {
		echo '<span class="notice">',$data,'</span>',"\n";
	}

	private function writeError($data) {
		echo '<span class="error">',$data,'</span>',"\n";
	}

	public function writeCommand($data) {
		echo '<code>',$data,'</code>',"\n";
	}


	// #### Désactivation des buffers ##################################### debug ## private ### //
	// = révision : 8
	// » Met fin à la temporisation de sortie (buffer) de Magento
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