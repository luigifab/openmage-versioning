<?php
/**
 * Created S/03/12/2011
 * Updated V/21/09/2012
 * Version 18
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

class Luigifab_Versioning_Model_Scm_Git extends Mage_Core_Model_Abstract {

	// définition des attributs
	private $version = null;
	private $commitCollection = null;
	private $currentRevision = null;


	// #### Initialisation ############################################# exception ## public ### //
	// = révision : 26
	// » Indique si le gestionnaire de version est installé ou pas ainsi que sa version
	public function _construct() {

		if (Mage::app()->getRequest()->getControllerName() !== 'versioning_repository')
			return;

		if ((Mage::getStoreConfig('versioning/scm/enabled') !== '1') || (Mage::getStoreConfig('versioning/scm/type') !== 'git'))
			throw new Exception('Please <a href="'.Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'versioning')).'" style="text-decoration:none;">configure</a> the module before use it.');

		if (!$this->isSoftwareInstalled())
			throw new Exception('On this system, GIT command is not available.');

		if (is_null($this->getCurrentRevision()))
			throw new Exception('Magento directory is not versioned or you have a <a href="'.Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'versioning')).'" style="text-decoration:none;">configuration</a> problem.');
	}

	public function isSoftwareInstalled() {
		exec('git --version', $data);
		return (preg_match('#([0-9]+\.[0-9]+\.[0-9]+)#', implode($data), $this->version) !== 0) ? true : false;
	}

	public function getSoftwareVersion() {
		return (!is_null($this->version) && !empty($this->version)) ? trim($this->version[0]) : null;
	}

	public function getRepositoryType() {
		return 'git';
	}


	// #### Historique ######################################### exception ## i18n ## public ### //
	// = révision : 45
	// » Génère une collection à partir de l'historique des commits du dépôt
	// » Met en forme les données à partir de la réponse de la commande git log
	// » Utilise GIT_SSH si le fichier de configuration existe
	public function getCommitCollection() {

		$bugtracker = trim(Mage::getStoreConfig('versioning/tweak/bugtracker'));
		$controller = Mage::app()->getRequest()->getControllerName();
		$network = 'ssh: Could not resolve hostname';

		// données du cache
		if (!is_null($this->commitCollection))
			return $this->commitCollection;

		// lecture de l'historique des commits
		// à noter qu'en cas de problème d'encodage avec le contenu des commits :
		// » dans /etc/apache2/envvars décommenter '. /etc/default/locale'
		// » ou utiliser 'export LANG=fr_FR.utf-8'
		$configsh = realpath('./.git/ssh/config.sh');

		if (!is_string($configsh))
			$configsh = realpath('../.git/ssh/config.sh');

		$description = (version_compare($this->getSoftwareVersion(), '1.7.2', '>=')) ? '%B' : '%s%n%b';

		if (is_string($configsh) && is_executable($configsh)) {
			exec('
				export GIT_SSH="'.$configsh.'";
				git fetch 2>&1;
				git log "origin/`git branch | grep "*" | cut -c3-`" --pretty=format:"<log><revno>%h</revno> <branch> %d </branch> <parents> %p </parents> <committer>%an</committer> <timestamp>%ai</timestamp> <message><![CDATA['.$description.']]></message></log>" -'.Mage::getStoreConfig('versioning/scm/number').' | iconv -f UTF8//IGNORE -t UTF-8 -c 2>&1;
			', $data, $val);
		}
		else {
			exec('
				git fetch 2>&1;
				git log "origin/`git branch | grep "*" | cut -c3-`" --pretty=format:"<log><revno>%h</revno> <branch> %d </branch> <parents> %p </parents> <committer>%an</committer> <timestamp>%ai</timestamp> <message><![CDATA['.$description.']]></message></log>" -'.Mage::getStoreConfig('versioning/scm/number').' | iconv -f UTF8//IGNORE -t UTF-8 -c 2>&1;
			', $data, $val);
		}

		$data = implode("\n", $data);
		$data = preg_replace('#<\!\[CDATA\[\s+\]\]>#', '', $data);
		$data = str_replace("\n\n", "\n", $data);

		// traitement de la réponse
		if (($val !== 0) || (strpos($data, '</log>') === false) ||
		    ((strpos($data, 'error: ') !== false) && (strpos($data, $network) !== 0) && ($controller === 'versioning_repository')) ||
		    ((strpos($data, 'fatal: ') !== false) && (strpos($data, $network) !== 0) && ($controller === 'versioning_repository')) ||
		    ((strpos($data, 'error: ') !== false) && ($controller !== 'versioning_repository')) ||
		    ((strpos($data, 'fatal: ') !== false) && ($controller !== 'versioning_repository'))) {

			$data = is_array($data) ? implode("\n", $data) : $data;
			$data = (strpos($data, '<log') !== false) ? substr($data, 0, strpos($data, '<log')) : $data;

			throw new Exception('Can not get commit history, invalid response!'."\n\n".trim($data));
		}
		else {
			if ((strpos($data, $network) === 0) && ($controller === 'versioning_repository'))
				Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('versioning')->__('Unable to update the commit history from the remote repository.<br />This list corresponds to the history of the local repository.'));

			$data = (strpos($data, '<') !== 0) ? substr($data, strpos($data, '<')) : $data;

			$xml = new DOMDocument();
			$xml->loadXML('<root>'.$data.'</root>');

			$this->commitCollection = new Varien_Data_Collection();

			foreach ($xml->getElementsByTagName('log') as $logentry) {

				$revision = trim($logentry->getElementsByTagName('revno')->item(0)->firstChild->nodeValue);
				$branch = trim($logentry->getElementsByTagName('branch')->item(0)->firstChild->nodeValue);
				$parents = trim($logentry->getElementsByTagName('parents')->item(0)->firstChild->nodeValue);
				$author = trim($logentry->getElementsByTagName('committer')->item(0)->firstChild->nodeValue);
				$timestamp = trim($logentry->getElementsByTagName('timestamp')->item(0)->firstChild->nodeValue);
				$description = trim($logentry->getElementsByTagName('message')->item(0)->firstChild->nodeValue);

				// tags
				$tags = array();
				exec('git tag --points-at '.$revision, $tags);

				// branche et parents
				$parents = explode(' ', $parents);

				if (strlen($branch) > 0) {
					if (strpos($branch, ',') !== false)
						$branch = str_replace('origin/', '', substr($branch, strrpos($branch, ',') + 2, -1));
					else if (strpos($branch, 'origin/') !== false)
						$branch = substr($branch, strrpos($branch, 'origin/') + 7, -1);
					else
						$branch = '';
				}
				else {
					$branch = '';
				}

				// bug tracker
				if (strlen($bugtracker) > 0) {
					$author = preg_replace('#<[^>]+>#', '', $author);
					$description = preg_replace('#\#([0-9]+)#', '<a href="'.$bugtracker.'$1" class="issue" onclick="window.open(this.href); return false;">$1</a>', $description);
				}
				else {
					$author = preg_replace('#<[^>]+>#', '', $author);
					$description = preg_replace('#\#([0-9]+)#', '<span class="issue">$1</span>', $description);
				}

				$commitEntry = new Varien_Object();
				$commitEntry->setRevision($revision);
				$commitEntry->setTags($tags);
				$commitEntry->setBranchName($branch);
				$commitEntry->setParents($parents);
				$commitEntry->setAuthor($author);
				$commitEntry->setDate(date('c', strtotime($timestamp)));
				$commitEntry->setDescription(nl2br($description));

				$this->commitCollection->addItem($commitEntry);
			}
		}

		return $this->commitCollection;
	}


	// #### Révision ################################################################ public ### //
	// = révision : 11
	// » Renvoie le numéro de la révision actuelle de la copie locale
	// » Extrait le numéro à partir de la réponse de la commande git log
	public function getCurrentRevision($cache = true) {

		// données du cache
		if (!is_null($this->currentRevision) && $cache)
			return $this->currentRevision;

		// recherche du numéro de révision
		exec('git log --pretty=format:"rev#%h" -1', $data);
		$data = implode($data);
		$data = (strpos($data, 'rev#') !== false) ? trim(substr($data, 4)) : null;

		$this->currentRevision = $data;
		return $this->currentRevision;
	}


	// #### État #################################################################### public ### //
	// = révision : 2
	// » Renvoie l'état de la copie locale à partir de la réponse de la commande git status
	public function getCurrentStatus() {

		exec('git status', $dataStatus);
		array_unshift($dataStatus, '<span>git status</span>');

		return implode("\n", $dataStatus);
	}


	// #### Branche ################################################################# public ### //
	// = révision : 9
	// » Renvoie la branche actuelle
	// » Extrait la branche à partir de la réponse de la commande git branch
	public function getCurrentBranch() {

		exec('git branch | grep "*"', $data);
		$data = implode($data);
		$data = (strpos($data, '*') !== false) ? trim(substr($data, 1)) : null;

		return $data;
	}


	// #### Mise à jour ############################################################# public ### //
	// = révision : 9
	// » Met à jour la copie locale avec git reset (après avoir annulé les éventuelles modifications avec git clean)
	// » Prend soin de vérifier le code de retour de la commande git reset et d'enregistrer les détails de la mise à jour
	// » N'utilise pas GIT_SSH étant donnée que tout est disponible sur le dépôt local
	public function upgradeToRevision($obj, $log, $revision) {

		exec('
			echo "<span>git fetch</span>" >> '.$log.';
			git fetch;

			echo "<span>git clean -f -d</span>" >> '.$log.';
			git clean -f -d >> '.$log.' 2>&1;

			echo "<span>git reset --hard '.$revision.'</span>" >> '.$log.';
			git reset --hard '.$revision.' >> '.$log.' 2>&1;
		', $data, $val);

		$data = trim(file_get_contents($log));
		$obj->writeCommand($data);

		foreach (explode("\n", $data) as $line) {
			if (strpos($line, 'fatal: ') === 0)
				throw new Exception(str_replace('fatal: ', '', $line));
		}

		if ($val !== 0)
			throw new Exception($data);
	}
}