/**
 * Created J/22/12/2011
 * Updated J/20/09/2012
 * Version 15
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
 *
 * JSLint
 * - Prototype $ $$ Event Element Position SKIN_URL apijs luigifab startVersioning
 * - jslint sloppy: true, white: true, browser: true, devel: true, plusplus: true, maxerr: 1000
 */

// initialisation des traductions
function luigifabVersioningInit() {

	apijs.i18n.data.en.versioning_uptitle = "Upgrade to revision §";
	apijs.i18n.data.en.versioning_uptext = "Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.";

	apijs.i18n.data.en.versioning_uptext_compressor_upgradeflag = "[p]Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.[/p][ul][li][label][input type='checkbox' name='code' value='true'] Update the application code (?§)[/label][/li][li][label][input type='checkbox' name='flag' value='true'] Do not leave the website maintenance mode (upgrade.flag)[/label][/li][/ul]";

	apijs.i18n.data.en.versioning_uptext_compressor = "[p]Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.[/p][ul][li][label][input type='checkbox' name='code' value='true'] Update the application code (?§)[/label][/li][/ul]";
	apijs.i18n.data.en.versioning_uptext_upgradeflag = "[p]Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.[/p][ul][li][label][input type='checkbox' name='flag' value='true'] Do not leave the website maintenance mode (upgrade.flag)[/label][/li][/ul]";

	apijs.i18n.data.fr.versioning_uptitle = "Mise à jour vers la révision §";
	apijs.i18n.data.fr.versioning_uptext = "Êtes-vous sûr de vouloir lancer le processus de mise à jour ?[br]Attention, cette opération ne peut pas être annulée.";

	apijs.i18n.data.fr.versioning_uptext_compressor_upgradeflag = "[p]Êtes-vous sûr de vouloir lancer le processus de mise à jour ?[br]Attention, cette opération ne peut pas être annulée.[/p][ul][li][label][input type='checkbox' name='code' value='true'] Mettre à jour le code application (?§)[/label][/li][li][label][input type='checkbox' name='flag' value='true'] Ne pas sortir du mode de maintenance (upgrade.flag)[/label][/li][/ul]";

	apijs.i18n.data.fr.versioning_uptext_compressor = "[p]Êtes-vous sûr de vouloir lancer le processus de mise à jour ?[br]Attention, cette opération ne peut pas être annulée.[/p][ul][li][label][input type='checkbox' name='code' value='true'] Mettre à jour le code application (?§)[/label][/li][/ul]";
	apijs.i18n.data.fr.versioning_uptext_upgradeflag = "[p]Êtes-vous sûr de vouloir lancer le processus de mise à jour ?[br]Attention, cette opération ne peut pas être annulée.[/p][ul][li][label][input type='checkbox' name='flag' value='true'] Ne pas sortir du mode de maintenance (upgrade.flag)[/label][/li][/ul]";

	apijs.i18n.data.en.versioning_deltitle = "Deleting";
	apijs.i18n.data.en.versioning_deltext = "Are you sure you want to delete this log?[br]Be careful, you can't cancel this operation.";
	apijs.i18n.data.fr.versioning_deltitle = "Suppression";
	apijs.i18n.data.fr.versioning_deltext = "Êtes-vous sûr de vouloir supprimer cet historique ?[br]Attention, cette opération ne peut pas être annulée.";
}

// demande de confirmation (livraison)
function luigifabVersioningUpgrade(url, go, compressor, upgradeflag) {

	if (url === true)
		return true;

	if ((typeof apijs !== null) && (typeof apijs.core === 'object')) {

		luigifabVersioningInit();

		if (go !== false) {
			location.href = url;
		}
		else {
			var date = new Date();
			url.match(/revision\/([0-9a-z]+)\//);

			if ((compressor === true) && (upgradeflag === true)) {
				apijs.dialog.dialogFormOptions(
					apijs.i18n.translate('versioning_uptitle', RegExp.$1),
					apijs.i18n.translate('versioning_uptext_compressor_upgradeflag', date.getFullYear() + '' + date.getMonth() + '' + date.getDate() + '' + date.getHours() + '' + date.getMinutes() + '' + date.getSeconds()),
					luigifabVersioningUpgrade, true, url, 'versioning'
				);
				$('box').setAttribute('method', 'get');
				$$('#box button').first().focus();
			}
			else if (compressor === true) {
				apijs.dialog.dialogFormOptions(
					apijs.i18n.translate('versioning_uptitle', RegExp.$1),
					apijs.i18n.translate('versioning_uptext_compressor', date.getFullYear() + '' + date.getMonth() + '' + date.getDate() + '' + date.getHours() + '' + date.getMinutes() + '' + date.getSeconds()),
					luigifabVersioningUpgrade, true, url, 'versioning'
				);
				$('box').setAttribute('method', 'get');
				$$('#box button').first().focus();
			}
			else if (upgradeflag === true) {
				apijs.dialog.dialogFormOptions(
					apijs.i18n.translate('versioning_uptitle', RegExp.$1),
					apijs.i18n.translate('versioning_uptext_upgradeflag'),
					luigifabVersioningUpgrade, true, url, 'versioning'
				);
				$('box').setAttribute('method', 'get');
				$$('#box button').first().focus();
			}
			else {
				apijs.dialog.dialogConfirmation(
					apijs.i18n.translate('versioning_uptitle', RegExp.$1),
					apijs.i18n.translate('versioning_uptext'),
					luigifabVersioningUpgrade, url, 'versioning'
				);
			}
		}

		return false;
	}
	else {
		url.match(/revision\/([0-9a-z]+)\//);
		return confirm('Are you sure (revision ' + RegExp.$1 + ') ?');
	}
}

// demande de confirmation (suppression des historiques)
function luigifabVersioningDelete(url, go) {

	if ((typeof apijs !== null) && (typeof apijs.core === 'object')) {

		luigifabVersioningInit();

		if (go !== false) {
			location.href = url;
		}
		else {
			apijs.dialog.dialogConfirmation(
				apijs.i18n.translate('versioning_deltitle'),
				apijs.i18n.translate('versioning_deltext'),
				luigifabVersioningUpgrade, url, 'versioning'
			);
		}

		return false;
	}
	else {
		return confirm('Are you sure?');
	}
}


// génération du graphique SVG pour BZR et GIT
// testé avec Magento Community 1.4 1.5 1.6 1.7 / Magento Enterprise 1.10 1.11
// testé avec Firefox 15 / Chromium 21 / Safari 5.1 / Opera 12 / IE 9
// ne fonctionne pas avec IE 8 même avec l'extension Adobe SVG Viewer
String.prototype.trim = function () {
	return this.replace(/^\s+|\s+$/g, '');
};
Array.prototype.remove = function (obj) {
	var a = [], i;
	for (i = 0; i < this.length; i++) {
		if (this[i] !== obj)
			a.push(this[i]);
	}
	return a;
};

if ((typeof luigifab !== 'object') || (luigifab === null))
	var luigifab = { core: {} };

Event.observe(window, 'load', startVersioning);

function startVersioning() {

	if ($('versioningGrid') && ($$('#versioningGrid td.graph').length > 0)) {
		luigifab.branchmanager = new luigifab.core.branchmanager();
		luigifab.branchmanager.init();
	}
}


// #### Gestion de la représentation des branches BZR/GIT ################### //
// = révision : 41
// » Crée une balise object qui contiendra la représentation des branches grâce à une image SVG
// » Construit l'image SVG en fonction de la liste de commits, des tags et des branches
// » En cas de pépin retour à l'état initial
luigifab.core.branchmanager = function () {

	this.init = function () {

		var elem = document.createElement('object');
		elem.setAttribute('data', SKIN_URL.substr(0, SKIN_URL.indexOf('adminhtml/')) + 'adminhtml/default/default/images/luigifab/versioning/branch.svg.php');
		elem.setAttribute('type', 'image/svg+xml');
		elem.setAttribute('width', 120);
		elem.setAttribute('style', 'position:absolute;');
		elem.setAttribute('onload', 'luigifab.branchmanager.create();');
		elem.setAttribute('id', 'svggraph');

		$('page:main-container').appendChild(elem);
		luigifab.cache = {};
	};

	this.create = function () {

		try {
			// recherche de l'accès au graphique SVG
			// et initialisation de toutes les variables
			var svgdoc = $('svggraph').getSVGDocument().getElementById('root'),
				allbranches = $$('input.branch').pluck('value').uniq(),
				scm = $('scmtype').firstChild.nodeValue.trim(),
				elemText, elemSpan, elemCircle, elemLine,
				currentBranch, currentColumn, currentCommit, currentTag, currentHeight, currentParents, elemTD, testBranch, testCommit,
				colors = ['', 'black', 'blue', 'red', 'limegreen', 'chocolate', 'orange', 'hotpink', 'silver', 'khaki'],
				points = {}, branchNames = [], fullHeight = 0;

			if (scm === 'git')
				allbranches = allbranches.remove('');

			// mise à jour de la taille du graphique
			this.setSize(20 * allbranches.length + 90);

			// création des points et des lignes, pour chaque commit
			// utilise une seule ligne par branche
			$$('#versioningGrid tbody tr').each(function (elemTR) {

				currentBranch = (scm === 'git') ? luigifab.branchmanager.findBranch(elemTR) : elemTR.down('input.branch').getAttribute('value');
				currentColumn = allbranches.indexOf(currentBranch) + 1;
				currentCommit = elemTR.down('input.revision').getAttribute('value').replace(/\./g, '-');
				currentTag    = elemTR.down('input.tags').getAttribute('value');
				currentHeight = elemTR.getHeight();

				// point au millieu de la case courante
				points.circleX = 20 * currentColumn;
				points.circleY = fullHeight + currentHeight / 2;

				// ligne du point précédent vers le millieu de la case courante
				points.lineX = 20 * currentColumn;
				points.lineY = fullHeight + currentHeight / 2;

				fullHeight += currentHeight;
				elemTR.down('td.graph').style.color = 'transparent';

				// texte (nom de la branche)
				if ((branchNames[currentColumn] !== true) && (scm === 'git')) {

					elemText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
					elemText.setAttribute('x', points.circleX + 7);
					elemText.setAttribute('y', points.circleY + 2);
					elemText.setAttribute('fill', colors[currentColumn]);
					elemText.setAttribute('class', 'branch-' + currentBranch);
					elemText.setAttribute('style', 'font:0.65em sans-serif;');

						elemSpan = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
						elemSpan.appendChild(document.createTextNode(currentBranch));

					elemText.appendChild(elemSpan);
					svgdoc.appendChild(elemText);

					branchNames[currentColumn] = true;
				}

				// texte (tags)
				if (currentTag.length > 0) {

					elemText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
					elemText.setAttribute('x', points.circleX + 7 + 50);
					elemText.setAttribute('y', points.circleY + 2);
					elemText.setAttribute('fill', colors[currentColumn]);
					elemText.setAttribute('class', 'tag');
					elemText.setAttribute('style', 'font:0.65em sans-serif; font-style:italic;');

						elemSpan = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
						elemSpan.appendChild(document.createTextNode(currentTag));

					elemText.appendChild(elemSpan);
					svgdoc.appendChild(elemText);
				}

				// point
				elemCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
				elemCircle.setAttribute('cx', points.circleX);
				elemCircle.setAttribute('cy', points.circleY);
				elemCircle.setAttribute('r', 3.2);
				elemCircle.setAttribute('fill', colors[currentColumn]);
				elemCircle.setAttribute('class', 'branch-' + currentBranch); // pour connaître à quelle branche appartient ce commit
				elemCircle.setAttribute('id', 'commit-' + currentCommit);    // pour connaître le nom du commit
				svgdoc.appendChild(elemCircle);

				// ligne
				elemLine = svgdoc.getElementById('branch-' + currentBranch);

				if (elemLine !== null) {
					elemLine.setAttribute('class', 'branch commit-' + currentCommit); // pour connaître le dernier (plus ancien) commit de la branche
					elemLine.setAttribute('x2', points.lineX);
					elemLine.setAttribute('y2', points.lineY);
				}
				else {
					elemLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
					elemLine.setAttribute('x1', points.lineX);
					elemLine.setAttribute('y1', points.lineY);
					elemLine.setAttribute('x2', points.lineX);
					elemLine.setAttribute('y2', points.lineY);
					elemLine.setAttribute('stroke', colors[currentColumn]);
					elemLine.setAttribute('stroke-width', 2);
					elemLine.setAttribute('class', 'branch commit-' + currentCommit); // pour connaître le dernier (plus ancien) commit de la branche
					elemLine.setAttribute('id', 'branch-' + currentBranch);           // pour connaître le nom de la branche
					svgdoc.appendChild(elemLine);
				}
			});

			// création des lignes interbranches, pour chaque commit
			$$('#versioningGrid tbody tr').each(function (elemTR) {

				currentParents = elemTR.down('input.parents').value;

				if (currentParents.length > 0) {

					currentBranch = (scm === 'git') ? luigifab.branchmanager.findBranch(elemTR) : elemTR.down('input.branch').getAttribute('value');
					currentCommit = elemTR.down('input.revision').getAttribute('value').replace(/\./g, '-');

					// pour chaque commit dit parent
					// et dans le cas ou l'on n'est pas sur la même branche
					currentParents.split(' ').each(function (currentParent) {

						currentParent = currentParent.replace(/\./g, '-');
						elemTD = $$('input.parents.rev-' + currentParent);

						if (elemTD.length > 0) {

							elemTR = elemTD.first().up().up();
							testBranch = (scm === 'git') ? luigifab.branchmanager.findBranch(elemTR) : elemTR.down('input.branch').getAttribute('value');

							if (testBranch !== currentBranch) {

								// ligne du point du commit courant vers le point du commit parent
								points.lineAx = svgdoc.getElementById('commit-' + currentCommit).getAttribute('cx');
								points.lineAy = svgdoc.getElementById('commit-' + currentCommit).getAttribute('cy');
								points.lineAcolor = svgdoc.getElementById('commit-' + currentCommit).getAttribute('fill');
								points.lineBcolor = svgdoc.getElementById('commit-' + currentParent).getAttribute('fill');
								points.lineBx = svgdoc.getElementById('commit-' + currentParent).getAttribute('cx');
								points.lineBy = svgdoc.getElementById('commit-' + currentParent).getAttribute('cy');

								// ligne
								elemLine = document.createElementNS('http://www.w3.org/2000/svg', 'path');
								elemLine.setAttribute('d', 'M' + points.lineAx + ',' + points.lineAy + 'L' + points.lineBx + ',' + points.lineBy);
								elemLine.setAttribute('stroke', 'url(#gradient-' + points.lineAcolor + '-' + points.lineBcolor + ')');
								elemLine.setAttribute('stroke-width', 2);
								svgdoc.appendChild(elemLine);
							}
						}
					});
				}
			});

			// prolongation des lignes sans parent, pour chaque branche
			allbranches.each(function (branch) {

				elemLine = svgdoc.getElementById('branch-' + branch);
				currentCommit = elemLine.getAttribute('class');
				currentCommit = currentCommit.substring(currentCommit.indexOf('-') + 1);

				testCommit = $$('input.parents.rev-' + currentCommit);
				testCommit = (testCommit.length > 0) ? testCommit.first().value : false;

				// dans le cas ou le commit parent n'existe pas
				if ((testCommit !== false) && (testCommit.length > 0) && ($$('input.parents.rev-' + testCommit).length < 1))
					elemLine.setAttribute('y2', $('svggraph').getHeight());
			});
		}
		catch (e) {
			$('svggraph').remove();
			alert('Woups! ' + e);
		}
	};

	this.setSize = function (width) {

		var firstTD, lastTD, height, style;

		// largeur du graphique SVG et de la colonne du tableau
		if (width > 120) {
			$$('col')[1].width = width + 3;
			$('svggraph').setAttribute('width', width);
		}

		if (Prototype.Browser.Opera) {
			$('svggraph').setAttribute('width', parseInt($('svggraph').getAttribute('width'), 10) + 10);
		}

		// hauteur du graphique SVG par rapport au tableau
		// avec prototype 1.7+ (pour Element.Layout) ou non
		if (typeof Element.Layout === 'function') {

			firstTD = new Element.Layout($$('td.graph').first(), true);
			lastTD = new Element.Layout($$('td.graph').last(), true);
			height = lastTD.get('height');

			$('svggraph').setAttribute('height', (lastTD.get('top') - firstTD.get('top') + height + 3));

			if (Prototype.Browser.WebKit)
				style = 'position:absolute; top:' + (firstTD.get('top') + 1) + 'px; left:' + (firstTD.get('left') + 2) + 'px;';
			else
				style = 'position:absolute; top:' + firstTD.get('top') + 'px; left:' + (firstTD.get('left') + 1) + 'px;';
		}
		else {
			firstTD = Position.positionedOffset($$('td.graph').first());
			lastTD = Position.positionedOffset($$('td.graph').last());
			height = $$('td.graph').last().getDimensions();

			$('svggraph').setAttribute('height', (lastTD[1] - firstTD[1] + height.height - 2));

			if (Prototype.Browser.WebKit)
				style = 'position:absolute; top:' + (firstTD[1] + 1) + 'px; left:' + (firstTD[0] + 2) + 'px;';
			else
				style = 'position:absolute; top:' + firstTD[1] + 'px; left:' + (firstTD[0] + 1) + 'px;';
		}

		$('svggraph').setAttribute('style', style);
	};

	this.findBranch = function (elemTR) {

		var branch = elemTR.down('input.branch').getAttribute('value'), commit = elemTR.down('input.revision').getAttribute('value').replace(/\./g, '-');

		// si le nom de la branche n'est pas disponible
		// on va chercher le nom dans le commit « précédent » jusqu'à le trouver
		// sauf s'il est en cache
		if (branch.length < 1) {

			if (typeof luigifab.cache[commit] === 'string')
				return luigifab.cache[commit];

			$$('#versioningGrid tbody tr input.parent-' + commit).each(function (parent) {

				branch = parent.up().down('input.branch').value;

				if (branch.length < 1)
					branch = luigifab.branchmanager.findBranch(parent.up().up());
			});

			luigifab.cache[commit] = branch;
		}

		return branch;
	};
};