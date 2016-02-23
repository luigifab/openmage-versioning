<?php
/**
 * Created S/03/12/2011
 * Updated S/20/02/2016
 * Version 33
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

class Luigifab_Versioning_Block_Adminhtml_Repository extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {

		parent::__construct();

		$this->_controller = 'adminhtml_repository';
		$this->_blockGroup = 'versioning';
		$this->_headerText = (!is_null($branch = Mage::registry('versioning')->getCurrentBranch())) ?
			$this->__('Commit history (<span id="scmtype">%s</span>, %s)', Mage::getStoreConfig('versioning/scm/type'), $branch) :
			$this->__('Commit history (<span id="scmtype">%s</span>)', Mage::getStoreConfig('versioning/scm/type'));

		$this->_removeButton('add');

		if (is_file($this->helper('versioning')->getMaintenanceFlag())) {
			$this->_addButton('maintenance_flag', array(
				'label'   => $this->__('Remove maintenace page'),
				'onclick' => "versioning.cancelFlag(this, '".$this->getUrl('*/*/delMaintenanceFlag')."');",
				'class'   => 'delpage delete'
			));
		}
		else {
			$this->_addButton('maintenance_flag', array(
				'label'   => $this->__('Enable maintenace page'),
				'onclick' => "versioning.confirmFlag(this, '".$this->getUrl('*/*/addMaintenanceFlag')."', this.textContent, '".addslashes($this->helper('versioning')->getMaintenanceInfo(true))."', '".addslashes($this->__('Martian sunset by Spirit.'))."');"
			));
		}

		if (is_file($this->helper('versioning')->getUpgradeFlag())) {
			$this->_addButton('upgrade_flag', array(
				'label'   => $this->__('Remove upgrade page'),
				'onclick' => "versioning.cancelFlag(this, '".$this->getUrl('*/*/delUpgradeFlag')."');",
				'class'   => 'delpage delete'
			));
		}
		else {
			$this->_addButton('upgrade_flag', array(
				'label'   => $this->__('Enable upgrade page'),
				'onclick' => "versioning.confirmFlag(this, '".$this->getUrl('*/*/addUpgradeFlag')."', this.textContent, '".addslashes($this->helper('versioning')->getUpgradeInfo(true))."', '".addslashes($this->__('Martian sunset by Spirit.'))."');"
			));
		}

		$this->_addButton('history', array(
			'label'   => $this->__('Upgrades log'),
			'onclick' => "setLocation('".$this->getUrl('*/*/history')."');",
			'class'   => 'go'
		));

		$this->_addButton('status', array(
			'label'   => $this->__('Repository status'),
			'onclick' => "setLocation('".$this->getUrl('*/*/status')."');",
			'class'   => 'go'
		));
	}

	public function getGridHtml() {

		$commits = Mage::registry('versioning')->getCommitCollection();
		$count = count($commits) - 1;
		$space = $current = 0;
		$hash  = '';

		// comptage dans l'ordre inverse
		// le commit le plus récent = count($commits) - 1
		// le commit le plus ancien = 0
		foreach ($commits as $commit) {

			$hash .= "\n";
			$hash .= '"'.$commit->getRevision().'": {';
			$hash .=  '"revision": "'.$commit->getRevision().'",';
			$hash .=  '"parents": ["'.implode('","', $commit->getParents()).'"],';
			$hash .=  '"refs": "'.implode(' ', $commit->getRefs()).'",';
			$hash .=  '"col": '.$commit->getSpace().',';
			$hash .=  '"row": '.$count--;
			$hash .= '},';

			$space = ($commit->getSpace() > $space) ? $commit->getSpace() : $space;

			if ($commit->getRevision() === $commit->getCurrentRevision())
				$current = $commit->getSpace();
		}

		return $this->getChildHtml('grid').' <script type="text/javascript">var versioningIds = {'.substr($hash, 0, -1).'}, versioningCols = '.$space.', versioningCurrentCol = '.$current.';</script>';

	}

	public function getHeaderCssClass() {
		return 'icon-head '.parent::getHeaderCssClass().' '.Mage::getStoreConfig('versioning/scm/type');
	}
}