<?php
/**
 * Created S/03/12/2011
 * Updated S/28/04/2012
 * Version 10
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

class Luigifab_Versioning_Block_Adminhtml_Repository_Grid extends Mage_Adminhtml_Block_Widget_Grid {

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

		$this->setCollection(Mage::registry('versioning')->getCommitCollection());
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('revision', array(
			'header'   => $this->__('Revision'),
			'align'    => 'center',
			'width'    => '85px',
			'index'    => 'revision',
			'sortable' => false,
			'filter'   => false,
			'renderer' => 'versioning/adminhtml_widget_revision',
			'column_css_class' => 'revision'
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

		$this->addColumn('author', array(
			'header'   => $this->__('Author'),
			'align'    => 'center',
			'width'    => '225px',
			'index'    => 'author',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('description', array(
			'header'   => $this->helper('adminhtml')->__('Description'),
			'align'    => 'left',
			'index'    => 'description',
			'sortable' => false,
			'filter'   => false,
			'renderer' => 'versioning/adminhtml_widget_description'
		));

		$this->addColumn('date', array(
			'header'   => $this->helper('adminhtml')->__('Date'),
			'width'    => '185px',
			'align'    => 'center',
			'type'     => 'datetime',
			'index'    => 'date',
			'sortable' => false,
			'filter'   => false
		));

		$this->addColumn('action', array(
			'header'  =>  $this->helper('adminhtml')->__('Action'),
			'width'   => '60px',
			'align'   => 'center',
			'type'    => 'action',
			'getter'  => 'getRevision',
			'actions' => array(
				array(
					'caption' => $this->__('Deliver'),
					'url'     => array('base' => 'versioning/upgrade/run'),
					'field'   => 'revision',
					'onclick' => 'return luigifabVersioningUpgrade(this.href, false, '.((class_exists('Luigifab_Compressor_Block_Head')) ? 'true' : 'false').');'
				)
			),
			'sortable'  => false,
			'filter'    => false,
			'is_system' => true
		));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row) {
		// rien à faire
	}
}