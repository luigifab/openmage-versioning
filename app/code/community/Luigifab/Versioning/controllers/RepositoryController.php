<?php
/**
 * Created W/21/12/2011
 * Updated M/08/05/2012
 * Version 16
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

class Luigifab_Versioning_RepositoryController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {

		$this->loadLayout();
		$this->_setActiveMenu('tools/versioning');

		return $this;
	}

	public function indexAction() {

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1') {

			$data = Mage::getModel('versioning/scm_'.Mage::getStoreConfig('versioning/scm/type'));
			$data->getCommitCollection();

			Mage::register('versioning', $data);
			$this->_initAction()->renderLayout();
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('versioning')->__('Please configure the module before use it.'));
			$this->_redirect('adminhtml/system_config/edit', array('section' => 'versioning'));
		}
	}

	public function historyAction() {

		Mage::register('versioning', Mage::helper('versioning')->getHistoryCollection());

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1')
			$this->_initAction()->renderLayout();
		else
			$this->_redirect('versioning/repository');
	}

	public function lastlogAction() {

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1')
			$this->_initAction()->renderLayout();
		else
			$this->_redirect('versioning/repository');
	}

	public function statusAction() {

		if (Mage::getStoreConfig('versioning/scm/enabled') === '1')
			$this->_initAction()->renderLayout();
		else
			$this->_redirect('versioning/repository');
	}

	public function deletehistoryAction() {

		if ((Mage::getStoreConfig('versioning/scm/enabled') === '1') && is_file(Mage::helper('versioning')->getHistoryFile()))
			unlink(Mage::helper('versioning')->getHistoryFile());

		$this->_redirect('versioning/repository');
	}

	public function deletelastlogAction() {

		if ((Mage::getStoreConfig('versioning/scm/enabled') === '1') && is_file(Mage::helper('versioning')->getLastlogFile()))
			unlink(Mage::helper('versioning')->getLastlogFile());

		$this->_redirect('versioning/repository');
	}
}