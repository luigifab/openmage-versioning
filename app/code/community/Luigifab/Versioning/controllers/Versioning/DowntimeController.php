<?php
/**
 * Created S/02/06/2012
 * Updated V/12/10/2012
 * Version 5
 *
 * Copyright 2012-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Versioning_DowntimeController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {
		$this->loadLayout()->_setActiveMenu('tools/downtime');
		return $this;
	}

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('tools/downtime');
	}

	public function indexAction() {

		$helper = Mage::helper('versioning');

		if ((Mage::getStoreConfig('versioning/scm/enabled') === '1') && $helper->checkIndexPhp() && $helper->checkLocalXml()) {
			$this->_initAction()->renderLayout();
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('versioning')->__('Please configure the module before use it.'));
			$this->_redirect('adminhtml/system_config/edit', array('section' => 'versioning'));
		}
	}

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
}