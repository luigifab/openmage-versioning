<?php
/**
 * Created S/02/06/2012
 * Updated S/09/02/2013
 * Version 13
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

class Luigifab_Versioning_Block_Adminhtml_Downtime extends Mage_Adminhtml_Block_Widget_View_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_downtime';
		$this->_blockGroup = 'versioning';

		$this->_headerText = '<span class="'.Mage::getStoreConfig('versioning/scm/type').'">'.$this->__('Downtime').'</span>';
		$this->_removeButton('edit');
		$this->_removeButton('back');

		if (Mage::getSingleton('admin/session')->isAllowed('tools/versioning')) {
			$this->_addButton('versioning', array(
				'label'   => $this->__('Versioning'),
				'onclick' => "location.href = '".$this->getUrl('*/versioning_repository/index')."';",
				'class'   => 'go'
			));
		}

		$this->_addButton('config', array(
			'label'   => $this->__('Configuration'),
			'onclick' => "location.href = '".$this->getUrl('adminhtml/system_config/edit', array('section' => 'versioning'))."';",
			'class'   => 'go'
		));
	}

	public function getViewHtml() {

		$html = array();
		$html[] = '<ul id="downtime">';

		// préparation
		$maintenanceFlag = is_file(Mage::helper('versioning')->getMaintenanceFlag());
		$upgradeFlag = is_file(Mage::helper('versioning')->getUpgradeFlag());

		$ipFile = BP.'/errors/versioning/config/503.ip';
		$maintenanceIp = (is_file($ipFile) && (strpos(file_get_contents($ipFile), '-'.getenv('REMOTE_ADDR').'-') !== false));
		$maintenanceNobody = (strlen(trim(Mage::getStoreConfig('versioning/downtime/error503_byip'))) < 1);

		$ipFile = BP.'/errors/versioning/config/upgrade.ip';
		$upgradeIp = (is_file($ipFile) && (strpos(file_get_contents($ipFile), '-'.getenv('REMOTE_ADDR').'-') !== false));
		$upgradeNobody = (strlen(trim(Mage::getStoreConfig('versioning/downtime/upgrade_byip'))) < 1);

		// code html pour le drapeau maintenance.flag
		if ($maintenanceFlag) {
			$html[] = '<li>';
			$html[] = '<h3>maintenance.flag</h3>';
			$html[] = '<p>'.$this->__('Flag <strong>present</strong>: <a href="%s">unlock website</a>.', $this->getUrl('*/*/delMaintenanceFlag')).'</p>';
			if ($maintenanceNobody) {
				$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR'));
				$html[] = '<br />'.$this->__('Nobody have access to the frontend.').'</p>';
			}
			else if ($maintenanceIp) {
				$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR'));
				$html[] = '<br />'.$this->__('You have access to the <a %s>frontend</a>.', 'href="'.$this->helper('versioning')->getFrontendUrl().'" onclick="window.open(this.href); return false;"').'</p>';
			}
			else {
				$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR'));
				$html[] = '<br />'.$this->__('You haven\'t access to the frontend.').'</p>';
			}
			$html[] = '</li>';
		}
		else {
			$html[] = '<li>';
			$html[] = '<h3>maintenance.flag</h3>';
			$html[] = '<p>'.$this->__('Flag <em>not present</em>: <a href="%s">lock website</a>.', $this->getUrl('*/*/addMaintenanceFlag')).'</p>';
			if ($upgradeFlag) {
				$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR')).'</p>';
			}
			else {
				$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR'));
				$html[] = '<br />'.$this->__('Everybody have access to the frontend.').'</p>';
			}
			$html[] = '</li>';
		}

		// code html pour le drapeau upgrade.flag
		if ($upgradeFlag) {
			$html[] = '<li>';
			$html[] = '<h3>upgrade.flag</h3>';
			$html[] = '<p>'.$this->__('Flag <strong>present</strong>: <a href="%s">unlock website</a>.', $this->getUrl('*/*/delUpgradeFlag')).'</p>';
			if ($maintenanceFlag) {
				$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR')).'</p>';
			}
			else {
				if ($upgradeNobody) {
					$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR'));
					$html[] = '<br />'.$this->__('Nobody have access to the frontend.').'</p>';
				}
				else if ($upgradeIp) {
					$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR'));
					$html[] = '<br />'.$this->__('You have access to the <a %s>frontend</a>.', 'href="'.$this->helper('versioning')->getFrontendUrl().'" onclick="window.open(this.href); return false;"').'</p>';
				}
				else {
					$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR'));
					$html[] = '<br />'.$this->__('You haven\'t access to the frontend.').'</p>';
				}
			}
			$html[] = '</li>';
		}
		else {
			$html[] = '<li>';
			$html[] = '<h3>upgrade.flag</h3>';
			$html[] = '<p>'.$this->__('Flag <em>not present</em>: <a href="%s">lock website</a>.', $this->getUrl('*/*/addUpgradeFlag')).'</p>';
			if ($maintenanceFlag) {
				$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR')).'</p>';
			}
			else {
				$html[] = '<p class="ip">'.$this->__('Your IP address: <strong>%s</strong>.', getenv('REMOTE_ADDR'));
				$html[] = '<br />'.$this->__('Everybody have access to the frontend.').'</p>';
			}
			$html[] = '</li>';
		}

		$html[] = '</ul>';
		return implode("\n", $html);
	}

	protected function _prepareLayout() {
		//return parent::_prepareLayout();
	}
}