<?php
/**
 * Created V/06/04/2012
 * Updated V/03/08/2012
 * Version 4
 *
 * Copyright 2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {

		parent::__construct();

		$this->setId('versioningGrid');
		$this->setDefaultSort('date');
		$this->setDefaultDir('DESC');

		$this->setSaveParametersInSession(false);
		$this->setPagerVisibility(false);
		$this->setFilterVisibility(false);
	}

	protected function _prepareCollection() {

		$this->setCollection(Mage::getModel('versioning/history')->getCollection());
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('date', array(
			'header'   => $this->helper('adminhtml')->__('Date'),
			'width'    => '185px',
			'align'    => 'center',
			'type'     => 'datetime',
			'index'    => 'date',
			'sortable' => false,
			'filter'   => false
		));

		if (Mage::getStoreConfig('versioning/scm/type') === 'git') {
			$this->addColumn('branch', array(
				'header'   => $this->__('Branch'),
				'align'    => 'center',
				'width'    => '100px',
				'index'    => 'branch',
				'sortable' => false,
				'filter'   => false
			));
		}

		$this->addColumn('current_revision', array(
			'header'   => $this->__('Current revision'),
			'align'    => 'center',
			'width'    => '120px',
			'index'    => 'current_revision',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('target_revision', array(
			'header'   => $this->__('Target revision'),
			'align'    => 'center',
			'width'    => '120px',
			'index'    => 'target_revision',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('remote_addr', array(
			'header'   => $this->__('Remote address'),
			'align'    => 'center',
			'width'    => '110px',
			'index'    => 'remote_addr',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('user', array(
			'header'   => $this->helper('adminhtml')->__('User'),
			'align'    => 'center',
			'width'    => '120px',
			'index'    => 'user',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('duration', array(
			'header'   => $this->__('Duration'),
			'align'    => 'center',
			'width'    => '110px',
			'index'    => 'duration',
			'sortable' => false,
			'filter'   => false,
			'renderer' => 'versioning/adminhtml_widget_duration'
		));

		$this->addColumn('status', array(
			'header'   => $this->helper('adminhtml')->__('Status'),
			'align'    => 'left',
			'index'    => 'status',
			'sortable' => false,
			'filter'   => false,
			'renderer' => 'versioning/adminhtml_widget_status'
		));

		$this->addColumn('details', array(
			'header'   => $this->helper('adminhtml')->__('Details'),
			'align'    => 'center',
			'width'    => '60px',
			'index'    => 'details',
			'sortable' => false,
			'filter'   => false,
			'renderer' => 'versioning/adminhtml_widget_details'
		));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row) {
		// rien à faire
	}
}