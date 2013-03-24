/**
 * Copyright 2011-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * File created J/22/12/2011, updated D/24/03/2013, version 30
 * https://redmine.luigifab.info/projects/magento/wiki/versioning
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL).
 *
 * JSLint: Prototype $break $ $$ Element Position SKIN_URL apijs luigifab startVersioning
 * sloppy: true, white: true, browser: true, devel: true, plusplus: true, maxerr: 1000
 */

// initialisation des traductions
function luigifabVersioningInit() {

	apijs.i18n.data.en.versioning_uptitle = "Upgrade to revision §";
	apijs.i18n.data.en.versioning_uptext = "Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.";

	apijs.i18n.data.en.versioning_uptext_compressor_upgradeflag = "[p]Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.[/p][ul][li][label][input type='checkbox' name='code' value='true'] Update the application code (?§)[/label][/li][li][label][input type='checkbox' name='flag' value='true'] Do not leave website downtime mode (upgrade.flag)[/label][/li][/ul]";
	apijs.i18n.data.en.versioning_uptext_compressor = "[p]Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.[/p][ul][li][label][input type='checkbox' name='code' value='true'] Update the application code (?§)[/label][/li][/ul]";
	apijs.i18n.data.en.versioning_uptext_upgradeflag = "[p]Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.[/p][ul][li][label][input type='checkbox' name='flag' value='true'] Do not leave website downtime mode (upgrade.flag)[/label][/li][/ul]";

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


// Demande de confirmation (suppression des historiques)
// apijs.dialog.dialogConfirmation(string title, string text, function callback, mixed callbackParams, string icon)
// confirm(string text)
function luigifabVersioningDelete(url) {

	try {
		if ((apijs !== null) && (typeof apijs === 'object') && (typeof apijs.core === 'object')) {

			luigifabVersioningInit();

			apijs.dialog.dialogConfirmation(
				apijs.i18n.translate('versioning_deltitle'), apijs.i18n.translate('versioning_deltext'),
				function (param) { location.href = param; }, url, 'versioning'
			);

			return false;
		}

		return confirm('Are you sure ?');
	}
	catch (e) {
		console.log('luigifabVersioningDelete: ' + e);
		return confirm('Are you sure ?');
	}
}

// Demande de confirmation (mise à jour)
// apijs.dialog.dialogFormOptions(string title, string text, string action, function callback, mixed callbackParams, string icon)
function luigifabVersioningUpgrade(url, compressorInstalled, compressorEnabled, flagEnabled) {

	try {
		if ((apijs !== null) && (typeof apijs === 'object') && (typeof apijs.core === 'object')) {

			luigifabVersioningInit();

			var text, appcode = '', date = new Date();
			appcode = appcode.concat(date.getFullYear());
			appcode = (date.getMonth() < 10) ? appcode.concat('0', date.getMonth()) : appcode.concat(date.getMonth());
			appcode = (date.getDate() < 10) ?  appcode.concat('0', date.getDate())  : appcode.concat(date.getDate());
			appcode = (date.getHours() < 10) ? appcode.concat('0', date.getHours()) : appcode.concat(date.getHours());
			appcode = (date.getMinutes() < 10) ? appcode.concat('0', date.getMinutes()) : appcode.concat(date.getMinutes());
			appcode = (date.getSeconds() < 10) ? appcode.concat('0', date.getSeconds()) : appcode.concat(date.getSeconds());

			url.match(/revision\/([0-9a-z]+)\//);

			if ((compressorInstalled === true) && (flagEnabled === true)) {
				text = apijs.i18n.translate('versioning_uptext_compressor_upgradeflag', appcode);
			}
			else if (compressorInstalled === true) {
				text = apijs.i18n.translate('versioning_uptext_compressor', appcode);
			}
			else if (flagEnabled === true) {
				text = apijs.i18n.translate('versioning_uptext_upgradeflag');
			}
			else {
				apijs.dialog.dialogFormOptions(
					apijs.i18n.translate('versioning_uptitle', RegExp.$1),
					apijs.i18n.translate('versioning_uptext') + " [input type='hidden' name='confirm' value='true']",
					url, function () { return true; }, null, 'versioning'
				);
			}

			if ((compressorInstalled === true) || (flagEnabled === true)) {

				apijs.dialog.dialogFormOptions(
					apijs.i18n.translate('versioning_uptitle', RegExp.$1),
					text + " [input type='hidden' name='confirm' value='true']",
					url, function () { return true; }, null, 'versioning big'
				);

				if (!compressorEnabled) {
					$$('#box input[name="code"]').first().setAttribute('disabled', 'disabled');
					$$('#box input[name="code"]').first().up().addClassName('disabled');
				}
			}

			$('box').setAttribute('method', 'get');
			$$('#box button').first().focus();

			return false;
		}
	}
	catch (e) {
		console.log('luigifabVersioningUpgrade: ' + e);
	}
}

// Validation de la demande de confirmation sans apijs
function luigifabConfirmUpgrade(text) {

	$('confirmForm').down('p').style.visibility = 'hidden';
	$('confirmForm').down('ul').style.visibility = 'hidden';
	$('confirmForm').select('button').invoke('addClassName', 'disabled');
	$('confirmForm').select('button').invoke('setAttribute', 'disabled', 'disabled');

	var elem = document.createElement('p');
	elem.setAttribute('class', 'saving');
	elem.appendChild(document.createTextNode(text));

	$('confirmForm').appendChild(elem);
}


// Génération du graphique SVG pour BZR et GIT
// Testé avec Magento Community 1.4 1.5 1.6 1.7
// Testé sur Chromium 25, Firefox 19, Opera 12.14, Safari 5.1
// Support des images SVG avec animations SMIL : Chrome 4+, Firefox 4+, Opera 9+, Safari 4+
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

if (typeof window.addEventListener === 'function')
	window.addEventListener('load', startVersioning, false);

function startVersioning() {

	if ($('versioningGrid') && ($$('td.graph').length > 0)) {
		luigifab.branchmanager = new luigifab.core.branchmanager();
		luigifab.branchmanager.init();
	}
}


// #### Gestion de la représentation des branches ########################### //
// = révision : 50
// » Crée une balise object qui contiendra la représentation des branches grâce à une image SVG
// » Construit l'image SVG en fonction de la liste de commits, des étiquettes et des branches
// » En cas de pépin retour à l'état initial
luigifab.core.branchmanager = function () {

	this.init = function () {

		var elem = document.createElement('object');
		elem.setAttribute('data', SKIN_URL.substr(0, SKIN_URL.indexOf('adminhtml/')) + 'adminhtml/default/default/images/luigifab/versioning/branch.svg.php');
		elem.setAttribute('type', 'image/svg+xml');
		elem.setAttribute('width', 120);
		elem.setAttribute('style', 'opacity:0;');
		elem.setAttribute('onload', 'luigifab.branchmanager.create();');
		elem.setAttribute('id', 'svggraph');

		$('page:main-container').appendChild(elem);
		luigifab.cache = {};
	};

	this.create = function () {

		try {
			// recherche l'accès au graphique SVG
			// et initialise de toutes les variables
			var svgdoc = $('svggraph').getSVGDocument().getElementById('root'),
				allbranches = $$('input.branch').pluck('value').uniq(),
				scm = $('scmtype').firstChild.nodeValue.replace(/^\s+|\s+$/g, ''),
				colors = ['', 'black', 'blue', 'red', 'limegreen', 'chocolate', 'orange', 'hotpink', 'silver', 'khaki'],
				elemText, elemSpan, elemCircle, elemLine, elemAnim,
				currentBranch, currentColumn, currentCommit, previousCommit, currentTag, currentHeight, fullHeight = 0,
				currentParents, anim,
				testBranch, testCommit,
				points = {}, show = {},
				branchNames = [];

			if (scm === 'git')
				allbranches = allbranches.remove('');

			// mise à jour de la taille du graphique
			this.setSize(20 * allbranches.length + 90);

			// création des points et des lignes, pour chaque commit
			// utilise une seule et même ligne par branche
			$$('#versioningGrid tbody tr').each(function (elemTR) {

				currentBranch  = luigifab.branchmanager.findBranch(scm, elemTR);
				currentColumn  = allbranches.indexOf(currentBranch) + 1;
				previousCommit = (typeof currentCommit === 'string') ? currentCommit : false;
				currentCommit  = elemTR.down('input.revision').getAttribute('value');
				currentTag     = elemTR.down('input.tags').getAttribute('value');
				currentHeight  = elemTR.getHeight();

				// point au millieu de la case courante
				points.circleX = 20 * currentColumn;
				points.circleY = fullHeight + currentHeight / 2;

				// ligne du point précédent vers le millieu de la case courante
				points.lineX = 20 * currentColumn;
				points.lineY = fullHeight + currentHeight / 2;

				fullHeight += currentHeight;

				// texte (nom de la branche)
				if ((branchNames[currentColumn] !== true) && (scm === 'git')) {

					elemText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
					elemText.setAttribute('x', points.circleX + 7);
					elemText.setAttribute('y', points.circleY + 2);
					elemText.setAttribute('fill', colors[currentColumn]);
					elemText.setAttribute('fill-opacity', 0);
					elemText.setAttribute('class', 'branch-' + currentBranch);
					elemText.setAttribute('style', 'font:0.65em sans-serif;');

						elemSpan = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
						elemSpan.appendChild(document.createTextNode(currentBranch));

					elemText.appendChild(elemSpan);

						elemAnim = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
						elemAnim.setAttribute('attributeName', 'fill-opacity');
						elemAnim.setAttribute('dur', '0.15s');
						elemAnim.setAttribute('begin', 'anim' + currentCommit + '.end-0.1s');
						elemAnim.setAttribute('to', 1);
						elemAnim.setAttribute('fill', 'freeze');

					elemText.appendChild(elemAnim);
					svgdoc.appendChild(elemText);

					branchNames[currentColumn] = true;
				}

				// texte (étiquettes)
				if (currentTag.length > 0) {

					elemText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
					elemText.setAttribute('x', points.circleX + 7 + 50);
					elemText.setAttribute('y', points.circleY + 2);
					elemText.setAttribute('fill', colors[currentColumn]);
					elemText.setAttribute('fill-opacity', 0);
					elemText.setAttribute('class', 'tag');
					elemText.setAttribute('style', 'font:0.65em sans-serif; font-style:italic;');

						elemSpan = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
						elemSpan.appendChild(document.createTextNode(currentTag));

					elemText.appendChild(elemSpan);

						elemAnim = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
						elemAnim.setAttribute('attributeName', 'fill-opacity');
						elemAnim.setAttribute('dur', '0.15s');
						elemAnim.setAttribute('begin', 'anim' + currentCommit + '.end-0.1s');
						elemAnim.setAttribute('to', 1);
						elemAnim.setAttribute('fill', 'freeze');

					elemText.appendChild(elemAnim);
					svgdoc.appendChild(elemText);
				}

				// point
				elemCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
				elemCircle.setAttribute('cx', points.circleX);
				elemCircle.setAttribute('cy', points.circleY);
				elemCircle.setAttribute('r', 0);
				elemCircle.setAttribute('fill', colors[currentColumn]);
				elemCircle.setAttribute('class', 'branch-' + currentBranch); // pour connaître à quelle branche appartient ce commit
				elemCircle.setAttribute('id', 'commit-' + currentCommit);    // pour connaître le nom du commit

					elemAnim = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
					elemAnim.setAttribute('attributeName', 'r');
					elemAnim.setAttribute('dur', '0.14s');
					elemAnim.setAttribute('begin', 'anim' + previousCommit + '.end');
					elemAnim.setAttribute('to', 3.3);
					elemAnim.setAttribute('fill', 'freeze');
					elemAnim.setAttribute('id', 'anim' + currentCommit);

				elemCircle.appendChild(elemAnim);
				svgdoc.appendChild(elemCircle);

				// ligne
				elemLine = svgdoc.getElementById('branch-' + currentBranch);

				if (elemLine !== null) {

					elemLine.setAttribute('class', 'branch commit-' + currentCommit); // pour connaître le dernier commit de la branche
					elemLine.setAttribute('x2', points.lineX);

						elemAnim = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
						elemAnim.setAttribute('attributeName', 'y2');
						elemAnim.setAttribute('dur', '0.15s');
						elemAnim.setAttribute('begin', 'anim' + previousCommit + '.end+0.1s');
						elemAnim.setAttribute('to', points.lineY);
						elemAnim.setAttribute('fill', 'freeze');

					elemLine.appendChild(elemAnim);

					if (!show[currentColumn]) {

						elemAnim = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
						elemAnim.setAttribute('attributeName', 'stroke-opacity');
						elemAnim.setAttribute('dur', '0.15s');
						elemAnim.setAttribute('begin', 'anim' + previousCommit + '.end+0.1s');
						elemAnim.setAttribute('to', 1);
						elemAnim.setAttribute('fill', 'freeze');
						elemLine.appendChild(elemAnim);

						show[currentColumn] = true;
					}
				}
				else {
					elemLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
					elemLine.setAttribute('x1', points.lineX);
					elemLine.setAttribute('y1', points.lineY);
					elemLine.setAttribute('x2', points.lineX);
					elemLine.setAttribute('y2', points.lineY);
					elemLine.setAttribute('stroke', colors[currentColumn]);
					elemLine.setAttribute('stroke-width', 2);
					elemLine.setAttribute('stroke-opacity', 0);
					elemLine.setAttribute('class', 'branch commit-' + currentCommit); // pour connaître le dernier commit de la branche
					elemLine.setAttribute('id', 'branch-' + currentBranch);           // pour connaître le nom de la branche
					svgdoc.appendChild(elemLine);

					show[currentColumn] = false;
				}
			});

			// création des lignes interbranches, pour chaque commit
			$$('#versioningGrid tbody tr').each(function (elemTR) {

				currentParents = elemTR.down('input.parents').value.split(' ');

				if (currentParents.length > 0) {

					currentBranch = luigifab.branchmanager.findBranch(scm, elemTR);
					currentCommit = elemTR.down('input.revision').getAttribute('value');

					// pour chaque commit dit parent
					// et dans le cas ou l'on n'est pas sur la même branche
					currentParents.each(function (currentParent) {

						anim = $$('input.parents.rev-' + currentParent);

						if (anim.length > 0) {

							elemTR = anim.first().up().up();
							testBranch = luigifab.branchmanager.findBranch(scm, elemTR);

							if (testBranch !== currentBranch) {

								// ligne du point du commit courant vers le point du commit parent
								points.lineAx = svgdoc.getElementById('commit-' + currentCommit).getAttribute('cx');
								points.lineAy = svgdoc.getElementById('commit-' + currentCommit).getAttribute('cy');
								points.lineAcolor = svgdoc.getElementById('commit-' + currentCommit).getAttribute('fill');
								points.lineBcolor = svgdoc.getElementById('commit-' + currentParent).getAttribute('fill');
								points.lineBx = svgdoc.getElementById('commit-' + currentParent).getAttribute('cx');
								points.lineBy = svgdoc.getElementById('commit-' + currentParent).getAttribute('cy');

								anim = (points.lineAx < points.lineBx) ? 'anim' + currentCommit + '.end+0.1s' :
									'anim' + currentParent + '.end+0.01s'; // 0.14s-0.15s

								// ligne
								elemLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
								elemLine.setAttribute('x1', points.lineAx);
								elemLine.setAttribute('y1', points.lineAy);
								elemLine.setAttribute('x2', points.lineAx);
								elemLine.setAttribute('y2', points.lineAy);
								elemLine.setAttribute('stroke', 'url(#gradient-' + points.lineAcolor + '-' + points.lineBcolor + ')');
								elemLine.setAttribute('stroke-width', 2);
								elemLine.setAttribute('stroke-opacity', 0);

									elemAnim = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
									elemAnim.setAttribute('attributeName', 'x2');
									elemAnim.setAttribute('dur', '0.15s');
									elemAnim.setAttribute('begin', anim);
									elemAnim.setAttribute('to', points.lineBx);
									elemAnim.setAttribute('fill', 'freeze');

								elemLine.appendChild(elemAnim);

									elemAnim = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
									elemAnim.setAttribute('attributeName', 'y2');
									elemAnim.setAttribute('dur', '0.15s');
									elemAnim.setAttribute('begin', anim);
									elemAnim.setAttribute('to', points.lineBy);
									elemAnim.setAttribute('fill', 'freeze');

								elemLine.appendChild(elemAnim);

									elemAnim = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
									elemAnim.setAttribute('attributeName', 'stroke-opacity');
									elemAnim.setAttribute('dur', '0.15s');
									elemAnim.setAttribute('begin', anim);
									elemAnim.setAttribute('to', 1);
									elemAnim.setAttribute('fill', 'freeze');

								elemLine.appendChild(elemAnim);
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

				// dans le cas ou le commit parent n'existe pas
				if ((testCommit.first().value.length > 0) && ($$('input.parents.rev-' + testCommit.first().value).length < 1)) {

					elemAnim = document.createElementNS('http://www.w3.org/2000/svg', 'animate');
					elemAnim.setAttribute('attributeName', 'y2');
					elemAnim.setAttribute('dur', '0.10s');
					elemAnim.setAttribute('begin', 'anim' + currentCommit + '.end+0.1s');
					elemAnim.setAttribute('to', $('svggraph').getHeight());
					elemAnim.setAttribute('fill', 'freeze');

					elemLine.appendChild(elemAnim);
				}
			});

			// démarrage de l'animation
			svgdoc.getElementById('anim' + $$('#versioningGrid tbody tr input.revision').first().value).beginElementAt(0.5);
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
			$('svggraph').getSVGDocument().getElementById('root').setAttribute('style', 'background-color:rgba(0, 0, 0, 0.05);');
		}

		// hauteur du graphique SVG par rapport au tableau
		// avec prototype 1.7+ (pour Element.Layout) ou non
		if (typeof Element.Layout === 'function') {

			firstTD = new Element.Layout($$('td.graph').first(), true);
			lastTD = new Element.Layout($$('td.graph').last(), true);
			height = lastTD.get('height');

			$('svggraph').setAttribute('height', lastTD.get('top') - firstTD.get('top') + height + 3);

			style = (Prototype.Browser.WebKit) ? ('top:' + (firstTD.get('top') + 1) + 'px; left:' + (firstTD.get('left') + 2) + 'px;') :
					('top:' + firstTD.get('top') + 'px; left:' + (firstTD.get('left') + 1) + 'px;');
		}
		else {
			firstTD = Position.positionedOffset($$('td.graph').first());
			lastTD = Position.positionedOffset($$('td.graph').last());
			height = $$('td.graph').last().getDimensions();

			$('svggraph').setAttribute('height', (lastTD[1] - firstTD[1] + height.height - 2));

			style = (Prototype.Browser.WebKit) ? ('top:' + (firstTD[1] + 1) + 'px; left:' + (firstTD[0] + 2) + 'px;') :
					('top:' + firstTD[1] + 'px; left:' + (firstTD[0] + 1) + 'px;');
		}

		$('svggraph').setAttribute('style', style);
	};

	this.findBranch = function (scm, elemTR) {

		var branch = elemTR.down('input.branch').getAttribute('value'),
		    commit = elemTR.down('input.revision').getAttribute('value');

		if (scm === 'git') {

			// si le nom de la branche n'est pas disponible
			// on va chercher le nom dans le commit « précédent » jusqu'à le trouver
			// sauf s'il est en cache
			if (branch.length < 1) {

				if (typeof luigifab.cache[commit] === 'string')
					return luigifab.cache[commit];

				$$('#versioningGrid tbody tr input.parents').each(function (parent) {

					if (parent.value === commit) {
						branch = parent.up().down('input.branch').value;
						if (branch.length < 1) {
							branch = luigifab.branchmanager.findBranch(scm, parent.up().up());
							throw $break;
						}
					}
				});

				if (branch.length < 1) {

					$$('#versioningGrid tbody tr input.parent-' + commit).each(function (parent) {
						branch = parent.up().down('input.branch').value;
						if (branch.length < 1) {
							branch = luigifab.branchmanager.findBranch(scm, parent.up().up());
							throw $break;
						}
					});
				}

				luigifab.cache[commit] = branch;
			}
		}

		return branch;
	};
};