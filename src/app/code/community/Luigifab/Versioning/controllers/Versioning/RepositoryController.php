<?php
/**
 * Created S/03/12/2011
 * Updated S/19/11/2022
 *
 * Copyright 2011-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Versioning_Versioning_RepositoryController extends Mage_Adminhtml_Controller_Action {

	protected function _validateSecretKey() {

		$result = parent::_validateSecretKey();

		if (!$result && ($this->getFullActionName() == 'adminhtml_versioning_repository_status')) {
			$this->getRequest()->setParam(Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME, Mage::getSingleton('adminhtml/url')->getSecretKey());
			$result = parent::_validateSecretKey();
		}

		return $result;
	}

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/versioning');
	}

	public function getUsedModuleName() {
		return 'Luigifab_Versioning';
	}

	public function loadLayout($ids = null, $generateBlocks = true, $generateXml = true) {
		parent::loadLayout($ids, $generateBlocks, $generateXml);
		$this->_title($this->__('Tools'))->_title($this->__('Version control'))->_setActiveMenu('tools/versioning');
		return $this;
	}

	public function indexAction() {

		if (Mage::getStoreConfigFlag('versioning/scm/enabled')) {
			Mage::register('current_collection', Mage::helper('versioning')->getSystem()->getCommitsCollection());
			$this->loadLayout()->renderLayout();
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please configure the module before using it.'));
			$this->_redirect('*/system_config/edit', ['section' => 'versioning']);
		}
	}

	public function statusAction() {

		if (Mage::getStoreConfigFlag('versioning/scm/enabled')) {
			$this->loadLayout()->renderLayout();
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please configure the module before using it.'));
			$this->_redirect('*/system_config/edit', ['section' => 'versioning']);
		}
	}

	public function historyAction() {

		if (Mage::getStoreConfigFlag('versioning/scm/enabled')) {
			if ($this->getRequest()->isXmlHttpRequest() || !empty($this->getRequest()->getParam('isAjax')))
				$this->getResponse()->setBody($this->getLayout()->createBlock('versioning/adminhtml_history_grid')->toHtml());
			else
				$this->loadLayout()->renderLayout();
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please configure the module before using it.'));
			$this->_redirect('*/system_config/edit', ['section' => 'versioning']);
		}
	}

	public function addUpgradeFlagAction() {

		if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
			$help = Mage::helper('versioning');
			$file = $help->getUpgradeFlag();
			if (!is_file($file))
				file_put_contents($file, sprintf('Flag from %s by %s', $help->getIpAddr(), Mage::getSingleton('admin/session')->getData('user')->getData('username')));
		}

		$this->_redirect('*/*/index');
	}

	public function addMaintenanceFlagAction() {

		if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
			$help = Mage::helper('versioning');
			$file = $help->getMaintenanceFlag();
			if (!is_file($file))
				file_put_contents($file, sprintf('Flag from %s by %s', $help->getIpAddr(), Mage::getSingleton('admin/session')->getData('user')->getData('username')));
		}

		$this->_redirect('*/*/index');
	}

	public function delUpgradeFlagAction() {

		if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
			$file = Mage::helper('versioning')->getUpgradeFlag();
			if (is_file($file))
				unlink($file);
		}

		$this->_redirect('*/*/index');
	}

	public function delMaintenanceFlagAction() {

		if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
			$file = Mage::helper('versioning')->getMaintenanceFlag();
			if (is_file($file))
				unlink($file);
		}

		$this->_redirect('*/*/index');
	}

	public function upgradeAction() {

		$revision =  $this->getRequest()->getParam('revision');         // string
		$useflag  = ($this->getRequest()->getParam('use_flag') == '1'); // boolean

		if (!Mage::getStoreConfigFlag('versioning/scm/enabled')) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please configure the module before using it.'));
			return $this->_redirect('*/system_config/edit', ['section' => 'versioning']);
		}
		if (empty($revision) || Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {
			return $this->_redirect('*/versioning_repository/index');
		}

		$locale  = substr(Mage::getSingleton('core/locale')->getLocaleCode(), 0, 2);
		$upgrade = Mage::getSingleton('versioning/upgrade')->disableAllBuffer();

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		echo "\n",'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="',$locale,'" lang="',$locale,'">';
		echo "\n",'<head>';
		echo "\n",'<title>',$this->__('Updating'),' - ',Mage::getStoreConfig('design/head/default_title'),'</title>';
		echo "\n",'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo "\n",'<meta http-equiv="Content-Script-Type" content="text/javascript" />';
		echo "\n",'<meta http-equiv="Content-Style-Type" content="text/css" />';
		echo "\n",'<meta http-equiv="Content-Language" content="',$locale,'" />';
		echo "\n",'<link rel="icon" type="image/x-icon" href="',Mage::getDesign()->getSkinUrl('favicon.ico'),'" />';
		// styles
		echo "\n",'<style type="text/css">';
		echo "\n", '* { margin:0; padding:0; }';
		echo "\n", 'html { height:100%; cursor:wait; }';
		echo "\n", 'body {';
		echo "\n", ' overflow-x:hidden; overflow-y:scroll; font:1em Verdana, sans-serif;';
		echo "\n", ' background-color:#999; background-size:100% 100%;';
		echo "\n", ' background-image:url(',Mage::getDesign()->getSkinUrl('images/luigifab/versioning/mars-sunset.jpg'),');';
		echo "\n", '}';
		echo "\n", 'div.obj { position:absolute; top:6em; left:30px; opacity:0.7; }';
		echo "\n", 'div.obj object { display:block; box-shadow:#444 0 0 0.3em; }';
		echo "\n", 'div.content {';
		echo "\n", ' position:absolute; top:0; left:160px; right:-2em; bottom:25%; padding:2em 4em 0 1.5em; overflow-x:hidden; overflow-y:scroll;';
		echo "\n", ' border-radius:0 0 0 1.2em; background-color:white; background-color:rgba(255, 255, 255, 0.25); box-shadow:#444 0 0 0.3em;';
		echo "\n", '}';
		echo "\n", 'p { padding-bottom:0.8em; }';
		echo "\n", 'p em { font-size:0.8em; }';
		echo "\n", 'pre { padding-bottom:1.4em; font:0.8em Verdana, sans-serif; line-height:140%; white-space:pre-wrap; }';
		echo "\n", 'pre code {';
		echo "\n", ' display:inline-block; margin-top:0.25em;';
		echo "\n", ' font:0.85em Verdana, sans-serif; font-style:italic; line-height:16px; color:#222;';
		echo "\n", '}';
		echo "\n", 'pre code span { color:#444; }'; // les commandes
		echo "\n", 'pre > span.notice { font-size:0.85em; font-style:italic; }';
		echo "\n", 'pre > span.error { font-size:0.85em; font-style:italic; color:red; }';
		echo "\n", 'pre > span.event {';
		echo "\n", ' display:block; margin:1em -2em 0; padding:0.5em 5em 0.5em 2em; width:100%;';
		echo "\n", ' font-style:italic; background-color:rgba(255,255,255, 0.18);';
		echo "\n", '}';
		echo "\n", 'pre > span.event::first-line { font-size:0.85em; color:#444; }'; // first-line = nom de l'événement
		echo "\n", 'pre > span.event code:not(:first-of-type) { margin-top:0; }';
		echo "\n",'</style>';
		// script
		// désactive toutes les touches du clavier et empèche la fermeture de la page
		echo "\n",'<script type="text/javascript">';
		echo "\n", '// disable keys of keyboard';
		echo "\n", 'function disableKeyboard(ev) {';
		echo "\n",  'if (typeof ev != "undefined") { ev.preventDefault(); ev.stopPropagation(); }';
		echo "\n",  'else { event.cancelBubble = true; event.returnValue = false; }';
		echo "\n", '}';
		echo "\n", '// prevents window or tab closing';
		echo "\n", 'function disableClose(ev) {';
		echo "\n",  'if (typeof ev != "undefined") { ev.preventDefault(); ev.stopPropagation(); return ""; }';
		echo "\n",  'else { event.cancelBubble = true; event.returnValue = ""; return ""; }';
		echo "\n", '}';
		echo "\n", '// register events';
		echo "\n", 'self.onbeforeunload = disableClose;';
		echo "\n", 'document.onkeydown = disableKeyboard;';
		echo "\n", '// auto scroll page';
		echo "\n", 'function autoScroll() {';
		echo "\n",  'document.getElementById("scroll").scrollTop += 10000;';
		echo "\n", '}';
		echo "\n", 'self.setInterval(autoScroll, 100);';
		echo "\n",'</script>';
		echo "\n",'</head>';
		echo "\n",'<body>';
		echo "\n",'<div class="obj"><object data="',Mage::getDesign()->getSkinUrl('images/luigifab/versioning/info.svg'),'" type="image/svg+xml" width="100" height="70" id="state"></object></div>';
		echo "\n",'<div class="content" id="scroll">';
		echo "\n",'<p class="first"><strong>',$this->__('Starting update (revision %s)', $revision),'</strong>';
		echo "\n",'<br /><em>',$this->__('Do not touch anything / Do not try to cancel this operation'),'</em></p>',"\n";

		sleep(3);

		// procédure de mise à jour
		// action
		echo '<pre lang="mul">';
		$result = $upgrade->process($revision, $useflag);
		echo '</pre>';
		// script
		// changement des couleurs
		echo "\n",'<script type="text/javascript">';
		echo "\n", '// svg animation colors';
		echo "\n", 'try {';
		echo "\n",  'var svg = document.getElementById("state").getSVGDocument();';
		echo "\n",  'svg.getElementById("color").setAttribute("class", "'.($result['error'] ? 'error' : 'success').'");';
		echo "\n", '}';
		echo "\n", 'catch (ee) {';
		echo "\n",  'document.getElementById("state").onload = function () {';
		echo "\n",   'var svg = document.getElementById("state").getSVGDocument();';
		echo "\n",   'svg.getElementById("color").setAttribute("class", "'.($result['error'] ? 'error' : 'success').'");';
		echo "\n",  '};';
		echo "\n", '}';
		echo "\n",'</script>';

		// script
		// redirection vers le back-office
		echo "\n",'<p class="last"><strong>',$result['title'],'</strong>';
		echo "\n",'<br /><em>',$this->__('Back to OpenMage in one second'),'</em></p>';
		echo "\n",'</div>';
		echo "\n",'<script type="text/javascript">';
		echo "\n", '// clear disableClose function, go to backend, register disableClose function';
		echo "\n", '// register disableClose delayed to prevent close warning in Chrome browser';
		echo "\n", 'self.setTimeout(function () {';
		echo "\n",  'self.onbeforeunload = null;';
		echo "\n",  'self.location.href = "',$this->getUrl($result['url']),'";';
		echo "\n",  'self.setTimeout(function () {';
		echo "\n",   'self.onbeforeunload = disableClose;';
		echo "\n",  '}, 1);';
		echo "\n", '}, 5000);';
		echo "\n",'</script>';
		echo "\n",'</body>';
		echo "\n",'</html>';

		exit(0);
	}
}