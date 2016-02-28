<?php
/**
 * Created S/03/12/2011
 * Updated D/28/02/2016
 * Version 29
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

class Luigifab_Versioning_Model_Scm_Git extends Mage_Core_Model_Abstract {

	private $version = null;
	private $revision = null;
	private $items = null;


	// #### Initialisation ########################################################## public ### //
	// = révision : 30
	// » Indique si le gestionnaire de version est installé
	// » Le tout à partir de la réponse de la commande 'git'
	public function isSoftwareInstalled() {
		exec('git --version', $data);
		return (preg_match('#([0-9]+\.[0-9]+\.[0-9]+)#', implode($data), $this->version) !== 0);
	}

	public function getSoftwareVersion() {
		if (is_null($this->version))
			$this->isSoftwareInstalled();
		return (!empty($this->version)) ? trim($this->version[0]) : null;
	}

	public function getType() {
		return 'git';
	}


	// #### Historique ############################################################## public ### //
	// = révision : 68
	// » Génère une collection à partir de l'historique des commits du dépôt
	// » Met en forme les données à partir de la réponse de pleins de commandes
	// » Utilise GIT_SSH si le fichier de configuration existe
	public function getCommitCollection() {

		if (!is_null($this->items))
			return $this->items;

		// lecture de l'historique des commits
		if (!is_dir('./.git/') && !is_dir('../.git/'))
			throw new Exception('The .git directory does not exist!');

		$configsh = realpath('./.git/ssh/config.sh');
		if (!is_string($configsh))
			$configsh = realpath('../.git/ssh/config.sh');

		$desc = (version_compare($this->getSoftwareVersion(), '1.7.2', '>=')) ? '%B' : '%s%n%b';

		if (is_string($configsh) && is_executable($configsh)) {
			exec('
				export GIT_SSH="'.$configsh.'";
				git fetch 2>&1;
				git log "origin/`git branch | grep "*" | cut -c3-`" --all --pretty=format:"<log><revno>%h</revno><refs> %d </refs><parents> %p </parents><committer>%an</committer><timestamp>%ai</timestamp><message><![CDATA['.$desc.']]></message></log>" -'.Mage::getStoreConfig('versioning/scm/number').' | iconv -f UTF8//IGNORE -t UTF-8 -c 2>&1;
			', $data, $val);
		}
		else {
			exec('
				git fetch 2>&1;
				git log "origin/`git branch | grep "*" | cut -c3-`" --all --pretty=format:"<log><revno>%h</revno><refs> %d </refs><parents> %p </parents><committer>%an</committer><timestamp>%ai</timestamp><message><![CDATA['.$desc.']]></message></log>" -'.Mage::getStoreConfig('versioning/scm/number').' | iconv -f UTF8//IGNORE -t UTF-8 -c 2>&1;
			', $data, $val);
		}

		// nettoyage du résultat
		$data = implode("\n", $data);
		$data = preg_replace('#<\!\[CDATA\[\s+\]\]>#', '', $data);
		$data = str_replace("\n\n", "\n", $data);

		// traitement de la réponse
		if (($val !== 0) || (strpos($data, '</log>') === false) || (strpos($data, 'error: ') !== false) || (strpos($data, 'fatal: ') !== false)) {

			$data = (is_array($data)) ? implode("\n", $data) : $data;
			$data = (strpos($data, '<log') !== false) ? substr($data, 0, strpos($data, '<log')) : $data;
			$data = '<u>Response:</u>'."\n".$data;

			$config = '<u>The git/config file:</u>'."\n".htmlspecialchars(file_get_contents(is_file('./.git/config') ? './.git/config' : '../.git/config'));

			throw new Exception('Can not get commit history, invalid response!'."\n\n".trim($data)."\n\n".trim($config));
		}
		else {
			$data = (strpos($data, '<') !== 0) ? substr($data, strpos($data, '<')) : $data;
			$xml = new DOMDocument();
			$xml->loadXML('<root>'.$data.'</root>');

			// extraction des données
			$this->items = new Varien_Data_Collection();

			foreach ($xml->getElementsByTagName('log') as $logentry) {

				$revision = trim($logentry->getElementsByTagName('revno')->item(0)->firstChild->nodeValue);
				$parents = trim($logentry->getElementsByTagName('parents')->item(0)->firstChild->nodeValue);
				$refs = trim($logentry->getElementsByTagName('refs')->item(0)->firstChild->nodeValue);
				$author = trim($logentry->getElementsByTagName('committer')->item(0)->firstChild->nodeValue);
				$timestamp = trim($logentry->getElementsByTagName('timestamp')->item(0)->firstChild->nodeValue);
				$description = trim($logentry->getElementsByTagName('message')->item(0)->firstChild->nodeValue);

				if (strlen($refs) > 2) {
					$refs = substr($refs, 1, -1);
					$refs = str_replace(array('origin/','HEAD',' ','->'), '', $refs);
					$refs = preg_replace('#,{2,}#', ',', $refs);
					$refs = trim($refs, ' ,');
					$refs = explode(',', $refs);
					$refs = array_unique($refs);
				}
				else {
					$refs = array();
				}

				$item = new Varien_Object();
				$item->setCurrentRevision($this->getCurrentRevision());
				$item->setRevision($revision);
				$item->setParents(explode(' ', $parents));
				$item->setRefs($refs);
				$item->setDate(date('c', strtotime($timestamp)));
				$item->setAuthor(preg_replace('#<[^>]+>#', '', $author));
				$item->setDescription(htmlspecialchars($description));

				$this->items->addItem($item);
			}

			// recherche du numéro de branche pour chaque commit
			$this->populateCols($this->items);
		}

		return $this->items;
	}

	private function populateCols($collection) {

		// » http://www.redmine.org/projects/redmine/repository/entry/trunk/app/helpers/repositories_helper.rb
		// heads.sort! { |head1, head2| head1.to_s <=> head2.to_s }
		// space = nil
		// heads.each do |head|
		//   if commits_by_scmid.include? head.scmid
		//     space = index_head((space || -1) + 1, head, commits_by_scmid)
		//   end
		// end
		// # when no head matched anything use first commit
		// space ||= index_head(0, commits.first, commits_by_scmid)

		$space = -1;

		foreach ($collection as $commit) {

			foreach ($commit->getRefs() as $ref) {

				if (strpos($ref, 'tag:') === false)
					$space = $this->indexCols($space + 1, $commit, $collection);
			}
		}

		if ($space < 0)
			$space = $this->indexCols(0, $collection->getFirstItem(), $collection);

		return $space; // recalculé par Luigifab_Versioning_Block_Adminhtml_Repository::getGridHtml
	}

	private function indexCols($space, $commit, $collection) {

		// » http://www.redmine.org/projects/redmine/repository/entry/trunk/app/helpers/repositories_helper.rb
		// def index_head(space, commit, commits_by_scmid)
		//   stack = [[space, commits_by_scmid[commit.scmid]]]
		//   max_space = space
		//   until stack.empty?
		//     space, commit = stack.pop
		//     commit[:space] = space if commit[:space].nil?
		//     space -= 1
		//     commit[:parent_scmids].each_with_index do |parent_scmid, parent_index|
		//       parent_commit = commits_by_scmid[parent_scmid]
		//       if parent_commit and parent_commit[:space].nil?
		//         stack.unshift [space += 1, parent_commit]
		//       end
		//     end
		//     max_space = space if max_space < space
		//   end
		//   max_space
		// end

		// » la même chose avec les commentaires de donove
		// » http://www.developpez.net/forums/d1510217/autres-langages/autres-langages/ruby/traduction-ruby-php-graphique-git/
		// def index_head(space, commit, commits_by_scmid)
		//   # commits_by_scmid serait ta variable $collection dans ta version PHP
		//   # Avec la valeur de commit.scmid comme référence
		//   stack = [ [space, commits_by_scmid[commit.scmid]] ]
		//   max_space = space
		//   # until est une boucle
		//   # Tant que stack (pile) n'est pas empty (vide) on continue la boucle
		//   until stack.empty?
		//     # On retire le dernier élément de stack (pile)
		//     space, commit = stack.pop
		//     # Si commit[:space] vaut nil
		//     # on attribue la valeur space à commit[:space]
		//     # nil est comme null en pHP qui veut dire absence de valeur
		//     commit[:space] = space if commit[:space].nil?
		//     # On retire 1 à space
		//     space -= 1
		//     # commit est un Hash comme un tableau associatif en PHP
		//     # On prend la clef :parent_scmids qui pourrait être une chaîne de caractère
		//     # et on parcourt cette collection avec each_with_index
		//     commit[:parent_scmids].each_with_index do |parent_scmid, parent_index|
		//       # On cherche l'index dans la collection commits_by_scmid
		//       parent_commit = commits_by_scmid[parent_scmid]
		//       # Si parent_commit est différent de nil (null en PHP) ou false
		//       # Et si parent_commit[:space] est nil (null en PHP)
		//       if parent_commit and parent_commit[:space].nil?
		//         stack.unshift [space += 1, parent_commit]
		//       end
		//     end
		//     # Si space est supérieur à max_space on redéfinit max_space avec la valeur space
		//     max_space = space if max_space < space
		//   end
		//   # On renvoit le valeur de max_space
		//   max_space
		// end

		$stack = array(array($space, $commit));
		$max = $space;

		while (!empty($stack)) {

			list($space, $commit) = array_pop($stack);

			if (!is_int($commit->getSpace()))
				$commit->setSpace($space);

			$space -= 1;

			foreach ($commit->getParents() as $rev) {

				$parent = $collection->getItemByColumnValue('revision', $rev);

				if (!is_object($parent)) {
					$space += 1;
					$fake = new Varien_Object();
					$fake->setRevision($rev);
					$fake->setParents(array());
					array_unshift($stack, array($space, $fake));
				}
				else if (is_object($parent) && !is_int($parent->getSpace())) {
					$space += 1;
					array_unshift($stack, array($space, $parent));
				}

				$max = ($space > $max) ? $space : $max;
			}
		}

		return $max;
	}


	// #### Révision, état et branche ############################################### public ### //
	// = révision : 24
	// » Renvoi le numéro de la révision actuelle de la copie locale (à partir de la réponse de la commande 'git log')
	// » Renvoi l'état de la copie locale à partir de la réponse des commandes 'git status' et 'git diff'
	// » Renvoi la branche actuelle à partir de la réponse de la commande 'git branch'
	public function getCurrentRevision() {

		if (!is_null($this->revision))
			return $this->revision;

		exec('git rev-parse --short HEAD', $data);
		$data = implode($data);
		$data = (strlen($data) > 0) ? trim($data) : null;

		$this->revision = $data;
		return $this->revision;
	}

	public function getCurrentDiff($from = null, $to = null) {

		// --diff-filter=[(A|C|D|M|R|T|U|X|B)...[*]]
		//   Select only files that are Added (A), Copied (C), Deleted (D), Modified (M), Renamed (R),
		//   have their Type changed (T), are Unmerged (U), are Unknown (X), or have had their pairing Broken (B)
		if (version_compare($this->getSoftwareVersion(), '1.8.4', '>='))
			$command = 'git diff -U1 --diff-filter=MTUXB --ignore-all-space --ignore-blank-lines';
		else if (version_compare($this->getSoftwareVersion(), '1.5.0', '>='))
			$command = 'git diff -U1 --diff-filter=MTUXB --ignore-all-space';
		else
			$command = 'git diff -U1 --diff-filter=MTUXB';

		if (!is_null($from) && !is_null($to))
			$command .= ' '.$from.'..'.$to;

		$i = 0;
		exec($command, $lines);

		foreach ($lines as &$line) {

			if (strlen($line) < 1)
				unset($lines[$i]);
			else if (strpos($line, '--- a/') === 0)
				unset($lines[$i]);
			else if (strpos($line, '+++ b/') === 0)
				unset($lines[$i]);
			else if ($line === '\\ No newline at end of file')
				unset($lines[$i]);
			else if (strpos($line ,'diff --git a') === 0)
				$line = "\n".'<strong>=== modified file '.substr(htmlspecialchars($line), 13, strpos($line, ' b/') - 13).'</strong>'; //13 strlen('diff --git a/')
			else if ($line[0] === '+')
				$line = '<ins>'.htmlspecialchars($line).' </ins>';
			else if ($line[0] === '-')
				$line = '<del>'.htmlspecialchars($line).' </del>';
			else
				$line = htmlspecialchars($line);

			$i++;
		}

		return '<span>'.$command.'</span>'."\n".str_replace("\t", '    ', implode("\n", $lines));
	}

	public function getCurrentStatus() {
		exec('git status', $data);
		return '<span>git status</span>'."\n".htmlspecialchars(implode("\n", $data));
	}

	public function getCurrentBranch() {
		exec('git branch | grep "*" | cut -c3-', $data);
		return (strlen(implode($data)) > 0) ? trim(implode($data)) : null;
	}


	// #### Mise à jour ############################################################# public ### //
	// = révision : 11
	// » Met à jour la copie locale avec 'git reset' (après avoir annulé les éventuelles modifications avec 'git clean')
	// » Prend soin de vérifier le code de retour de la commande 'git reset' et d'enregistrer les détails de la mise à jour
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

		$data  = trim(file_get_contents($log));
		$lines = explode("\n", $data);

		$obj->writeCommand($data);

		foreach ($lines as $line) {
			if (strpos($line, 'fatal: ') === 0)
				throw new Exception(str_replace('fatal: ', '', $line));
		}

		if ($val !== 0)
			throw new Exception($data);
	}
}