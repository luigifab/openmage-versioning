<?php
/**
 * Created S/03/12/2011
 * Updated J/24/03/2016
 * Version 38
 *
 * Copyright 2011-2016 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Versioning_RepositoryController extends Mage_Adminhtml_Controller_Action {

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/versioning');
	}

	// pages
	public function indexAction() {

		$this->setUsedModuleName('Luigifab_Versioning');

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1') {
			Mage::register('versioning', Mage::getModel('versioning/scm_'.Mage::getStoreConfig('versioning/scm/type')));
			$this->loadLayout()->_setActiveMenu('tools/versioning')->renderLayout();
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please configure the module before use it.'));
			$this->_redirect('*/system_config/edit', array('section' => 'versioning'));
		}
	}

	public function statusAction() {

		$this->setUsedModuleName('Luigifab_Versioning');

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1') {
			Mage::register('versioning', Mage::getModel('versioning/scm_'.Mage::getStoreConfig('versioning/scm/type')));
			$this->loadLayout()->_setActiveMenu('tools/versioning')->renderLayout();
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please configure the module before use it.'));
			$this->_redirect('*/system_config/edit', array('section' => 'versioning'));
		}
	}

	public function historyAction() {

		$this->setUsedModuleName('Luigifab_Versioning');

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1') {

			Mage::register('versioning', Mage::getModel('versioning/scm_'.Mage::getStoreConfig('versioning/scm/type')));

			if ($this->getRequest()->getParam('isAjax', false))
				$this->getResponse()->setBody($this->getLayout()->createBlock('versioning/adminhtml_history_grid')->toHtml());
			else
				$this->loadLayout()->_setActiveMenu('tools/versioning')->renderLayout();
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please configure the module before use it.'));
			$this->_redirect('*/system_config/edit', array('section' => 'versioning'));
		}
	}

	// drapeaux
	public function addUpgradeFlagAction() {

		if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {

			$file = Mage::helper('versioning')->getUpgradeFlag();

			if (!is_file($file))
				file_put_contents($file, 'Flag from '.((getenv('REMOTE_ADDR') !== false) ? getenv('REMOTE_ADDR') : 'unknown').' by '.Mage::getSingleton('admin/session')->getUser()->getUsername());
		}

		$this->_redirect('*/*/index');
	}

	public function addMaintenanceFlagAction() {

		if (!Mage::getSingleton('admin/session')->isFirstPageAfterLogin()) {

			$file = Mage::helper('versioning')->getMaintenanceFlag();

			if (!is_file($file))
				file_put_contents($file, 'Flag from '.((getenv('REMOTE_ADDR') !== false) ? getenv('REMOTE_ADDR') : 'unknown').' by '.Mage::getSingleton('admin/session')->getUser()->getUsername());
		}

		$this->_redirect('*/*/index');
	}

	public function delUpgradeFlagAction() {

		$file = Mage::helper('versioning')->getUpgradeFlag();

		if (is_file($file))
			unlink($file);

		$this->_redirect('*/*/index');
	}

	public function delMaintenanceFlagAction() {

		$file = Mage::helper('versioning')->getMaintenanceFlag();

		if (is_file($file))
			unlink($file);

		$this->_redirect('*/*/index');
	}

	// mise à jour
	public function confirmAction() {
		$this->loadLayout();
		$this->renderLayout();
	}

	public function upgradeAction() {

		$this->setUsedModuleName('Luigifab_Versioning');

		$revision = $this->getRequest()->getParam('revision', ''); // string
		$confirm =  $this->getRequest()->getParam('confirm',  ''); // string
		$useflag = ($this->getRequest()->getParam('use_flag', '') === '1'); // boolean

		if (Mage::getStoreConfig('versioning/scm/enabled') !== '1') {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please configure the module before use it.'));
			$this->_redirect('adminhtml/system_config/edit', array('section' => 'versioning'));
			return;
		}
		else if (Mage::getSingleton('admin/session')->isFirstPageAfterLogin() || (strlen($revision) < 1)) {
			$this->_redirect('*/versioning_repository/index');
			return;
		}
		else if ($confirm !== '1') {
			$this->_forward('confirm');
			return;
		}

		$upgrade = Mage::getModel('versioning/upgrade');
		$upgrade->disableAllBuffer();

		$lang = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		echo "\n",'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="',$lang,'" lang="',$lang,'">';
		echo "\n",'<head>';
		echo "\n",'<title>',$this->__('Upgrading'),' - ',Mage::getStoreConfig('design/head/default_title'),'</title>';
		echo "\n",'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo "\n",'<meta http-equiv="Content-Script-Type" content="text/javascript" />';
		echo "\n",'<meta http-equiv="Content-Style-Type" content="text/css" />';
		echo "\n",'<meta http-equiv="Content-Language" content="',$lang,'" />';
		echo "\n",'<link rel="icon" type="image/x-icon" href="',Mage::getDesign()->getSkinUrl('favicon.ico'),'" />';
		// styles
		// http://commons.wikimedia.org/wiki/File:MarsSunset.jpg
		echo "\n",'<style type="text/css">';
		echo "\n", '* { margin:0; padding:0; }';
		echo "\n", 'html { height:100%; cursor:wait; }';
		echo "\n", 'body {';
		echo "\n", ' overflow-x:hidden; overflow-y:scroll; font:1em Verdana, sans-serif;';
		echo "\n", ' background-color:#999; background-size:100% 100%;';
		echo "\n", ' background-image:url(',Mage::getDesign()->getSkinUrl('images/luigifab/versioning/mars-sunset.jpg'),');';
		echo "\n", '}';
		echo "\n", 'p.credits { position:absolute; bottom:1.2em; left:1em; padding:0; font:12px/1.5em Arial,Helvetica,sans-serif; color:#CCC; }';
		echo "\n", 'div.obj { position:absolute; top:6em; left:30px; opacity:0.7; }';
		echo "\n", 'div.obj object { display:block; box-shadow:#444 0 0 0.3em; }';
		echo "\n", 'div.ctn {';
		echo "\n", ' position:absolute; top:0; left:160px; right:-2em; bottom:25%; padding:2em 4em 0 1.5em; overflow-x:hidden; overflow-y:scroll;';
		echo "\n", ' border-radius:0 0 0 1.2em; background-color:white; background-color:rgba(255, 255, 255, 0.2); box-shadow:#444 0 0 0.3em;';
		echo "\n", '}';
		echo "\n", 'p { padding-bottom:0.8em; }';
		echo "\n", 'p em { font-size:0.8em; }';
		echo "\n", 'pre { padding-bottom:1.4em; font:0.8em Verdana, sans-serif; line-height:140%; white-space:pre-wrap; }';
		echo "\n", 'pre code {';
		echo "\n", ' display:inline-block; margin-top:0.2em;';
		echo "\n", ' font:0.85em Verdana, sans-serif; font-style:italic; line-height:16px; color:#222;';
		echo "\n", '}';
		echo "\n", 'pre code span { color:#333; }';
		echo "\n", 'pre > span { display:inline-block; margin-top:0.2em; }';
		echo "\n", 'pre > span + span { margin-top:0; }';
		echo "\n", 'pre > span[class], pre > span.event::first-line { font-size:0.85em; font-style:italic; }';
		echo "\n", 'pre > span.error { color:red; }';
		echo "\n", 'pre > span.event { margin:1em -2em 0; padding:0.5em 5em 0.5em 2em; width:100%; font-size:inherit; background-color:rgba(255,255,255, 0.18); }';
		echo "\n",'</style>';
		echo "\n",'<!--[if IE]><style type="text/css">div.obj { display:none; }</style><![endif]-->';
		// script
		// désactive toutes les touches du clavier et empèche la fermeture de la page
		echo "\n",'<script type="text/javascript">';
		echo "\n", '// disable keys of keyboard';
		echo "\n", 'function disableKeyboard(ev) {';
		echo "\n",  'if (typeof ev !== "undefined") { ev.preventDefault(); ev.stopPropagation(); }';
		echo "\n",  'else { event.cancelBubble = true; event.returnValue = false; }';
		echo "\n", '}';
		echo "\n", '// prevents window or tab closing';
		echo "\n", 'function disableClose(ev) {';
		echo "\n",  'if (typeof ev !== "undefined") { ev.preventDefault(); ev.stopPropagation(); return ""; }';
		echo "\n",  'else { event.cancelBubble = true; event.returnValue = ""; return ""; }';
		echo "\n", '}';
		echo "\n", '// register events';
		echo "\n", 'window.onbeforeunload = disableClose;';
		echo "\n", 'document.onkeydown = disableKeyboard;';
		echo "\n", '// auto scroll page';
		echo "\n", 'function autoScroll() {';
		echo "\n",  'document.getElementById("scroll").scrollTop += 10000;';
		echo "\n", '}';
		echo "\n", 'window.setInterval(autoScroll, 100);';
		echo "\n",'</script>';
		echo "\n",'</head>';

		echo "\n",'<body>';
		echo "\n",'<p class="credits">'.$this->__('Martian sunset by Spirit.').'</p>';
		echo "\n",'<div class="obj"><object data="',Mage::getDesign()->getSkinUrl('images/luigifab/versioning/info.svg'),'" type="image/svg+xml" width="100" height="70" id="state"></object></div>';
		echo "\n",'<div class="ctn" id="scroll">';
		echo "\n",'<p class="first"><strong>',$this->__('Starting upgrade (revision %s)', $revision),'</strong>';
		echo "\n",'<br /><em>',$this->__('Do not touch anything / Do not try to cancel this operation'),'</em></p>',"\n";

		sleep(3);
		// procédure de mise à jour
		echo '<pre>';
		$result = $upgrade->process($revision, $useflag);
		list($colorA, $colorB) = ($result['error']) ? array('red','red') : array('blue','orange');
		echo '</pre>';
		// script
		// changement des couleurs
		echo "\n",'<script type="text/javascript">';
		echo "\n", '// svg animation colors';
		echo "\n", 'try {';
		echo "\n",  'var svg = document.getElementById("state").getSVGDocument();';
		echo "\n",  'svg.getElementById("a").setAttribute("values", "#222;',$colorA,'");';
		echo "\n",  'svg.getElementById("b").setAttribute("values", "',$colorA,';#222");';
		echo "\n",  'svg.getElementById("c").setAttribute("values", "#222;',$colorB,'");';
		echo "\n",  'svg.getElementById("d").setAttribute("values", "',$colorB,';#222");';
		echo "\n", '}';
		echo "\n", 'catch (ee) {';
		echo "\n",  'if (!document.getElementById("state").getSVGDocument()) {';
		echo "\n",   'document.getElementById("state").onload = function () {';
		echo "\n",    'var svg = document.getElementById("state").getSVGDocument();';
		echo "\n",    'svg.getElementById("a").setAttribute("values", "#222;',$colorA,'");';
		echo "\n",    'svg.getElementById("b").setAttribute("values", "',$colorA,';#222");';
		echo "\n",    'svg.getElementById("c").setAttribute("values", "#222;',$colorB,'");';
		echo "\n",    'svg.getElementById("d").setAttribute("values", "',$colorB,';#222");';
		echo "\n",   '};';
		echo "\n",  '}';
		echo "\n", '}';
		echo "\n",'</script>';

		echo "\n",'<p class="last"><strong>',$result['title'],'</strong>';
		echo "\n",'<br /><em>',$this->__('Back to Magento in one second'),'</em></p>';
		echo "\n",'</div>';

		// script
		// redirection vers Magento
		echo "\n",'<script type="text/javascript">';
		echo "\n", '// clear disableClose function, go to Magento backend, re-reregister disableClose function';
		echo "\n", '// register disableClose delayed to prevent close warning in Chrome/Chromium browser';
		echo "\n", 'window.setTimeout(function () {';
		echo "\n",  'window.onbeforeunload = null;';
		echo "\n",  'location.href = "',$this->getUrl($result['url']),'";';
		echo "\n",  'window.setTimeout(function () {';
		echo "\n",   'window.onbeforeunload = disableClose;';
		echo "\n",  '}, 1);';
		echo "\n", '}, 5000);';
		echo "\n",'</script>';
		echo "\n",'</body>';
		echo "\n",'</html>';

		exit(0);
	}
}