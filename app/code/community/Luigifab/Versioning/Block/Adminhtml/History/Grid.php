<?php
/**
 * Created V/06/04/2012
 * Updated S/28/02/2015
 * Version 11
 *
 * Copyright 2011-2015 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

		$this->setId('history_grid');
		$this->setDefaultSort('date');
		$this->setDefaultDir('DESC');

		$this->setUseAjax(true);
		$this->setSaveParametersInSession(false);
		$this->setPagerVisibility(true);
		$this->setFilterVisibility(false);
	}

	protected function _prepareCollection() {

		$page = $this->getParam($this->getVarNamePage(), 1);
		$size = $this->getParam($this->getVarNameLimit(), 20);

		$this->setCollection(Mage::getModel('versioning/history')->init($page, $size));
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('from', array(
			'header'   => $this->__('Current revision'),
			'index'    => 'from',
			'align'    => 'center',
			'width'    => '120px',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('to', array(
			'header'   => $this->__('Target revision'),
			'index'    => 'to',
			'align'    => 'center',
			'width'    => '120px',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('branch', array(
			'header'   => $this->__('Branch'),
			'index'    => 'branch',
			'align'    => 'center',
			'width'    => '130px',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('empty', array(
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('remote_addr', array(
			'header'   => $this->__('Remote address'),
			'index'    => 'remote_addr',
			'align'    => 'center',
			'width'    => '150px',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('user', array(
			'header'   => $this->helper('adminhtml')->__('User'),
			'index'    => 'user',
			'align'    => 'center',
			'width'    => '150px',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('date', array(
			'header'   => $this->helper('adminhtml')->__('Date'),
			'index'    => 'date',
			'type'     => 'datetime',
			'align'    => 'center',
			'width'    => '180px',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('duration', array(
			'header'   => $this->__('Duration'),
			'index'    => 'duration',
			'align'    => 'center',
			'width'    => '60px',
			'sortable' => false,
			'filter'   => false,
			'renderer' => 'versioning/adminhtml_widget_duration'
		));

		$this->addColumn('status', array(
			'header'    => $this->helper('adminhtml')->__('Status'),
			'index'     => 'status',
			'renderer'  => 'versioning/adminhtml_widget_status',
			'align'     => 'status',
			'width'     => '125px',
			'filter'    => false,
			'sortable'  => false
		));

		$this->addColumn('action', array(
			'renderer'  => 'versioning/adminhtml_widget_link',
			'align'     => 'center',
			'width'     => '55px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true
		));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row) {
		return null;
	}
}