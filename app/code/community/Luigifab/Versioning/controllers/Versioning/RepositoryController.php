<?php
/**
 * Created W/21/12/2011
 * Updated V/03/08/2012
 * Version 25
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

class Luigifab_Versioning_Versioning_RepositoryController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {
		$this->loadLayout()->_setActiveMenu('tools/versioning');
		return $this;
	}

	public function indexAction() {

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1') {

			$data = Mage::getModel('versioning/scm_'.Mage::getStoreConfig('versioning/scm/type'));
			$data->getCommitCollection();

			if (is_file(Mage::helper('versioning')->getUpgradeFlag()) || is_file(Mage::helper('versioning')->getMaintenanceFlag()))
				Mage::getSingleton('adminhtml/session')->addNotice($this->__('Website is in downtime mode. Remember to <a href="%s">unlock website</a>.', Mage::helper('adminhtml')->getUrl('*/versioning_downtime/index')));

			Mage::register('versioning', $data);
			$this->_initAction()->renderLayout();
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('versioning')->__('Please configure the module before use it.'));
			$this->_redirect('adminhtml/system_config/edit', array('section' => 'versioning'));
		}
	}

	public function historyAction() {

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1') {

			if (is_file(Mage::helper('versioning')->getUpgradeFlag()) || is_file(Mage::helper('versioning')->getMaintenanceFlag()))
				Mage::getSingleton('adminhtml/session')->addNotice($this->__('Website is in downtime mode. Remember to <a href="%s">unlock website</a>.', Mage::helper('adminhtml')->getUrl('*/versioning_downtime/index')));

			$this->_initAction()->renderLayout();
		}
		else {
			$this->_redirect('*/*/index');
		}
	}

	public function lastlogAction() {

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1') {

			if (is_file(Mage::helper('versioning')->getUpgradeFlag()) || is_file(Mage::helper('versioning')->getMaintenanceFlag()))
				Mage::getSingleton('adminhtml/session')->addNotice($this->__('Website is in downtime mode. Remember to <a href="%s">unlock website</a>.', Mage::helper('adminhtml')->getUrl('*/versioning_downtime/index')));

			$this->_initAction()->renderLayout();
		}
		else {
			$this->_redirect('*/*/index');
		}
	}

	public function statusAction() {

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1') {

			if (is_file(Mage::helper('versioning')->getUpgradeFlag()) || is_file(Mage::helper('versioning')->getMaintenanceFlag()))
				Mage::getSingleton('adminhtml/session')->addNotice($this->__('Website is in downtime mode. Remember to <a href="%s">unlock website</a>.', Mage::helper('adminhtml')->getUrl('*/versioning_downtime/index')));

			$this->_initAction()->renderLayout();
		}
		else {
			$this->_redirect('*/*/index');
		}
	}

	public function deletehistoryAction() {

		if ((Mage::getStoreConfig('versioning/scm/enabled') === '1') && is_file(Mage::helper('versioning')->getHistoryFile())) {

			// log rotation
			$i = strrpos(Mage::helper('versioning')->getHistoryFile(), '/');
			$logDir = substr(Mage::helper('versioning')->getHistoryFile(), 0, $i + 1);
			$logName = substr(Mage::helper('versioning')->getHistoryFile(), $i + 1);

			$i = count(glob($logDir.$logName.'*')) - 1;
			$currName = str_replace('.csv', '.csv.'.$i, $logName);

			if ($i > 0) {
				do {
					$i--;
					$nextName = str_replace('.csv', '.csv.'.$i, $logName);
					rename($logDir.$nextName, $logDir.$currName);
					$currName = $nextName;
				}
				while ($i > 0);
				rename($logDir.$logName, $logDir.$nextName);
			}
			else {
				rename($logDir.$logName, $logDir.$currName);
			}

			//unlink(Mage::helper('versioning')->getHistoryFile());
		}

		$this->_redirect('*/*/index');
	}

	public function deletelastlogAction() {

		if ((Mage::getStoreConfig('versioning/scm/enabled') === '1') && is_file(Mage::helper('versioning')->getLastlogFile()))
			unlink(Mage::helper('versioning')->getLastlogFile());

		$this->_redirect('*/*/index');
	}
}