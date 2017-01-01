/**
 * Copyright 2011-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * Created J/22/12/2011, updated M/08/11/2016
 * https://redmine.luigifab.info/projects/magento/wiki/versioning
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL).
 */

// dépend de Prototype, versioningIds/versioningCols dans Repository.php
var versioning = {

	// ATTENTION, confirmFlag confirmUpgrade history, pas avant IE 10 !
	// avant IE 10, atob n'existe pas - http://caniuse.com/atob-btoa
	// prise en charge de l'utf-8 avec Webkit - http://stackoverflow.com/q/3626183
	decode: function (data) {
		return decodeURIComponent(escape(window.atob(data)));
	},

	// initialisation
	start: function () {

		if (!document.querySelector('body[class*="adminhtml-versioning-repository-"]'))
			return;

		console.info('versioning.app hello!');

		if (document.getElementById('history_grid')) {
			document.querySelector('table.data tbody tr td a').click();
		}

		if (typeof versioningIds !== 'undefined') {
			versioning.drawGraph(versioningIds, versioningCols);
			versioning.initDiff();
		}
	},


	// #### Confirmation des pages de maintenance ############################### //
	// = révision : 13
	// » Demande de confirmation de mise en maintenance avec l'apijs (si disponible)
	// » Demande de confirmation même si l'apijs n'est pas disponible, avec les mêmes informations
	// » En cas de problème active quand même la page de maintenance ou de mise à jour en redirigeant directement sur l'action
	// » Pour la désactivation, redirige simplement sur l'action
	confirmFlag: function (that, url, title, content, credits) {

		that.blur();

		try {
			if (apijs.version < 520)
				throw new Error('Invalid apijs version');

			url.match(/revision\/([0-9a-z]+)\//);
			apijs.dialog.dialogConfirmation(
				title, // title
				this.decode(content), // text
				versioning.actionConfirmFlag, // callback
				url,   // args
				'notransition versioning ' + document.getElementById('scmtype').textContent // icon
			);

			var elem = document.createElement('p');
			elem.setAttribute('class', 'credits');
			elem.appendChild(document.createTextNode(credits));
			document.getElementById('apijsDialog').appendChild(elem);
		}
		catch (e) {
			console.log(e);
			try {
				if (confirm(this.decode(content).replace(/\[[^\]]+\]/g, ''))) {
					location.href = url;
					that.parentNode.style.visibility = 'hidden';
				}
			}
			catch (ee) {
				console.log(ee);
				location.href = url;
				that.parentNode.style.visibility = 'hidden';
			}
		}
	},

	actionConfirmFlag: function (url) {

		apijs.dialog.styles.remove('lock'); // obligatoire sinon demande de confirmation de quitter la page
		location.href = url;
	},

	cancelFlag: function (that, url) {

		that.blur();
		that.parentNode.style.visibility = 'hidden';

		location.href = url;
	},


	// #### Confirmation de mise à jour ######################################### //
	// = révision : 23
	// » Demande de confirmation de mise à jour avec l'apijs (si disponible)
	// » Dans le cas contraire on laisse le navigateur suivre le lien vers la page de demande de confirmation
	confirmUpgrade: function (url, title, content, credits) {

		try {
			if (apijs.version >= 520) {

				url.match(/revision\/([0-9a-z]+)\//);
				apijs.dialog.dialogFormOptions(
					title.replace('§', RegExp.$1), // title
					this.decode(content), // text
					url,  // action
					versioning.actionConfirmUpgrade, // callback (en deux temps : vérification puis redirection)
					null, // args
					'notransition versioning ' + document.getElementById('scmtype').textContent // icon
				);

				var elem = document.createElement('p');
				elem.setAttribute('class', 'credits');
				elem.appendChild(document.createTextNode(credits));
				document.getElementById('apijsDialog').appendChild(elem);

				window.setTimeout(function () {
					document.querySelector('button.confirm').focus();
				}, 100);

				return false;
			}
		}
		catch (e) {
			console.log(e);
		}
	},

	actionConfirmUpgrade: function (action) {

		if (action === false)
			return true;

		apijs.dialog.styles.remove('lock'); // obligatoire sinon demande de confirmation de quitter la page
		location.href = action + 'confirm/1/' + apijs.serialize(document.getElementById('apijsBox')).replace(/=|&/g, '/');
	},

	valid: function (data) {

		document.querySelector('form div.bbcode').style.visibility = 'hidden'; // cache, ne supprime surtout pas
		document.querySelector('form').removeChild(document.querySelector('form div.buttons'));

		// valider (data = texte)
		if (data.indexOf('http') < 0) {

			var elem = document.createElement('p');
			elem.setAttribute('class', 'saving');
			elem.appendChild(document.createTextNode(data));

			document.querySelector('form').appendChild(elem);
		}
		// annuler (data = url)
		else {
			location.href = data;
		}
	},


	// #### Affichage de l'historique ########################################### //
	// = révision : 10
	// » Affiche les détails d'une mise à jour dans la balise pre (isNaN pour Webkit)
	// » Marque la ligne active du tableau avec la classe current
	history: function (link, content) {

		var elems = document.querySelectorAll('table.data tbody tr'), elem;
		for (elem in elems) if (elems.hasOwnProperty(elem) && !isNaN(elem)) {
			if (elems[elem].hasAttribute('class'))
				elems[elem].setAttribute('class', elems[elem].getAttribute('class').replace(/ ?current/, ''));
		}

		elem = link.parentNode.parentNode;
		elem.setAttribute('class', ((elem.hasAttribute('class')) ? elem.getAttribute('class') : '') + ' current');

		document.querySelector('pre').innerHTML = this.decode(content) + "\n\n";
		return false;
	},


	// #### Représentation des branches ######################################### //
	// = révision : 120
	// » Est basé sur le script de Redmine (revision_graph.js - rev 9835) - http://www.redmine.org/
	// » Utilise Raphael.js 2.2.0 (90,6 ko) pour la création de l'image SVG - http://raphaeljs.com/
	// » Utilise la fonction innerSVG (1,8 ko) pour l'ajout des dégradés - https://code.google.com/p/innersvg/
	// » Commence par rechercher les branches (dans tous les cas, passe la branche actuelle en premier)
	// » Pour chaque commit, crée un point suivi d'une étiquette avec le nom de la branche
	// » Crée ensuite les lignes entres les points
	drawGraph: function (data, cols) {

		var graph, colors = [], k, x, y, pX, pY, elem, grad = 0, names = [], alone,
			commitsHash  = new Hash(data),
			commitsArray = commitsHash.values(),
			rows = commitsHash.keys().length - 1,
			tableRows = $$('#versioning_grid_table tbody tr'),
			offsetTop = 0, graphHeight = 0, topPoint = 0, miHeight = 0;

		// http://stackoverflow.com/a/1129270/2980105
		// mais dans l'ordre inverse
		commitsArray.sort(function (a,b) {
			if (a.row > b.row)
				return -1;
			if (a.row < b.row)
				return 1;
			return 0;
		});

		// recheche de la hauteur et de la position du graphique (avec Prototype > 1.7 ou non)
		// offsetTop = la position du haut du graphique (à partir du haut du document)
		//  topPoint = la position du haut de la ligne du commit dans le tableau (à partir du haut du document)
		if (typeof Element.Layout === 'function') {
			offsetTop   = tableRows.first().getLayout().get('top') - 1;
			graphHeight = tableRows.last().getLayout().get('top') + tableRows.last().getLayout().get('height') - offsetTop - 1;
		}
		else {
			offsetTop   = Position.positionedOffset(tableRows.first())[1] - 1;
			graphHeight = Position.positionedOffset(tableRows.last())[1] + tableRows.last().getDimensions().height - offsetTop - 1;
		}

		// initialisation du graphique (dimensions et positionnement en hauteur)
		graph = new Raphael(document.getElementById('versioning_grid_table').parentNode);
		graph.setSize(197, graphHeight);

		document.querySelector('svg').style.top = offsetTop + 'px';
		document.querySelector('svg defs').innerSVG = '';

		// génération des couleurs
		Raphael.getColor.reset();
		for (k = 0; k <= cols; k++) {
			Raphael.getColor();
			colors.push(Raphael.getColor());
		}

		// Pour chaque commit :
		// » on commence par chercher la position sur Y du point
		// offsetTop = la position du haut du graphique (à partir du haut du document)
		//  topPoint = la position du haut de la ligne du commit dans le tableau (à partir du haut du document)
		//  miHeight = le milieu de la ligne du commit dans le tableau
		//      rows = le nombre de ligne de commit dans le tableau (de 0 à...)
		//       row = le numéro de la ligne du commit dans le tableau, max = la première ligne, 0 = la dernière ligne
		// » on en déduit ensuite la position sur X de ce même point en fonction de la branche du commit
		// » à partir des coordonnées on dessine un point et on en profite pour écrire le nom de la branche
		// » ensuite on dessine les lignes vers les points des parents en calculant le point de la même manière
		commitsArray.each(function (commit) {

			// recherche de la position du point
			// avec Prototype > 1.7 ou non
			if (typeof Element.Layout === 'function') {
				topPoint = tableRows[rows - commit.row].getLayout().get('top') - offsetTop;
				miHeight = tableRows[rows - commit.row].getLayout().get('height') / 2;
			}
			else {
				topPoint = Position.positionedOffset(tableRows[rows - commit.row])[1] - offsetTop;
				miHeight = tableRows[rows - commit.row].getDimensions().height / 2;
			}

			// sur X le 25 correspond à l'espace entre les colonnes, donc entre deux branches
			// sur X le +20 permet de ne pas coller la première branche au bord
			y = topPoint + miHeight;
			x = 25 * commit.col + 20;

			// dessine le point
			graph.circle(x, y, 3.4).attr('fill', colors[commit.col]).attr('stroke', 'none');

			// écrit le texte dans une étiquette
			// en profite pour vérifier la largeur du graphique
			if ((commit.branch.length > 0) && (names.indexOf(commit.branch) < 0)){

				names.push(commit.branch);

				elem = graph.text(x + 13, y - 0.3, commit.branch).attr('fill', colors[commit.col]).attr('text-anchor', 'start');
				graph.path(
					'M ' + (x + 3.2) + ',' + (y - 0.4) + // point de départ au niveau du point
					' L ' + (x + 3.2 + 8) + ',' + (y - 0.4 - 8) + // en haut à gauche
					' L ' + (x + 3.2 + 8 + elem.getBBox().width + 7) + ',' + (y - 0.4 - 8) + // en haut à droite
					' L ' + (x + 3.2 + 8 + elem.getBBox().width + 7) + ',' + (y - 0.4 + 8) + // en bas à droite
					' L ' + (x + 3.2 + 8) + ',' + (y - 0.4 + 8) + // en bas à gauche
					' Z'
				).attr('stroke', colors[commit.col]).attr('fill', 'white').attr('fill-opacity', '0.7').attr('stroke-opacity', '0.2').toFront();
				elem.toFront(); // repasse le texte au dessus de l'étiquette

				if ((k = (x + 3.2 + 8 + elem.getBBox().width + 7)) > 197) {
					document.querySelector('svg').setAttribute('onmouseover', 'this.style.width = "' + k + 'px";');
					document.querySelector('svg').setAttribute('onmouseout', 'this.style.width = "197px";');
					document.querySelector('svg').style.pointerEvents = 'inherit';
				}
			}

			// ligne vers le parent
			commit.parents.each(function (ref, parent) {

				parent = (ref.length > 0) ? commitsHash.get(ref) : undefined;

				// ligne verticale vers un commit
				// ou ligne plus ou moins arrondie vers un commit dans une branche différente
				if (typeof parent === 'object') {

					// recherche de la position du point
					// avec Prototype > 1.7 ou non
					if (typeof Element.Layout === 'function') {
						topPoint = tableRows[rows - parent.row].getLayout().get('top') - offsetTop;
						miHeight = tableRows[rows - parent.row].getLayout().get('height') / 2;
					}
					else {
						topPoint = Position.positionedOffset(tableRows[rows - parent.row])[1] - offsetTop;
						miHeight = tableRows[rows - parent.row].getDimensions().height / 2;
					}

					// sur X le 25 correspond à l'espace entre les colonnes, donc entre deux branches
					// sur X le +20 permet de ne pas coller la première branche au bord
					pY = topPoint + miHeight;
					pX = 25 * parent.col + 20;

					// ligne verticale vers un commit
					if (parent.col === commit.col) {
						graph.path(['M', x, y, 'V', pY]).attr('stroke', colors[commit.col]).attr('stroke-width', 1.7).toBack();
					}
					// ligne plus ou moins arrondie vers un commit dans une branche différente
					else {
						// dégradé manuel de gauche à droite ou dans le sens l'inverse
						// car Raphael.js ne permet pas de définir un dégradé sur un path sur stroke
						if (x > pX) {
							document.querySelector('svg defs').innerSVG += '<linearGradient id="manGrad' + grad + '" x1="0" y1="0" x2="100%" y2="0"><stop offset="0" stop-color="' + colors[parent.col] + '"></stop><stop offset="100%" stop-color="' + colors[commit.col] + '"></stop></linearGradient>';
						}
						else {
							document.querySelector('svg defs').innerSVG += '<linearGradient id="manGrad' + grad + '" x1="0" y1="0" x2="100%" y2="0"><stop offset="0" stop-color="' + colors[commit.col] + '"></stop><stop offset="100%" stop-color="' + colors[parent.col] + '"></stop></linearGradient>';
						}

						// si le commit vers lequel on va n'est pas lié à un autre commit hormis le commit actuel
						// on prolonge au maximum la ligne verticale si la ligne verticale est prolongeable
						alone = false;
						if ((parent.parents.length === 1) && ((y + miHeight * 4) < pY)) {
							alone = true;
							commitsArray.each(function (test) {
								if ((test.revision !== commit.revision) && (test.parents.indexOf(parent.revision) > -1)) {
									alone = false;
									throw $break;
								}
							});
						}

						// dessine la ligne arrondie entre les deux commits
						// soit en prenant la hauteur de la cellule (de y à pY=y+trihauteur) pour la ligne arrondie
						//   suivi d'une ligne verticale (de y=y+trihauteur à pY)
						// soit en prenant la différence de hauteur total (de y à pY)
						if (alone === true) {

							pY = y + 3 * miHeight;
							elem = graph.path(['M', x, y,
								'C', x, y, x, y + (pY - y) / 2, x + (pX - x) / 2, y + (pY - y) / 2,
								'C', x + (pX - x) / 2, y + (pY - y) / 2, pX, pY - (pY - y) / 2, pX, pY]);
							elem.node.setAttribute('stroke', 'url(#manGrad' + grad + ')');
							elem.attr('stroke-width', 1.7).toBack();

							y = y + 3 * miHeight;
							pY = topPoint + miHeight;
							graph.path(['M', pX	, y, 'V', pY]).attr('stroke', colors[parent.col]).attr('stroke-width', 1.7).toBack();
						}
						else {
							elem = graph.path(['M', x, y,
								'C', x, y, x, y + (pY - y) / 2, x + (pX - x) / 2, y + (pY - y) / 2,
								'C', x + (pX - x) / 2, y + (pY - y) / 2, pX, pY - (pY - y) / 2, pX, pY]);
							elem.node.setAttribute('stroke', 'url(#manGrad' + grad + ')');
							elem.attr('stroke-width', 1.7).toBack();
						}

						grad += 1;
					}
				}
				// ligne verticale vers le bas du graphique
				else if (ref.length > 0) {
					graph.path(['M', x, y, 'V', graphHeight]).attr('stroke', colors[commit.col]).attr('stroke-width', 1.7).toBack();
				}
			});

			if (commit.col > 0)
				tableRows[rows - commit.row].setAttribute('class', tableRows[rows - commit.row].getAttribute('class') + ' outside');
		});
	},


	// #### Gestion des cases du diff ########################################### //
	// = révision : 5
	// » Gère l'activation du lien vers la page du diff
	// » Active automatiquement les premières cases
	initDiff: function () {

		var elems = document.querySelectorAll('table.data input[type="radio"]'), elem;
		for (elem in elems) if (elems.hasOwnProperty(elem) && !isNaN(elem)) {
			elems[elem].setAttribute('onchange', 'versioning.goDiff();');
		}

		document.querySelector('table.data tr:last-child input[name="diff1"]').setAttribute('disabled', 'disabled');
		document.querySelector('table.data tr:first-child input[name="diff2"]').setAttribute('disabled', 'disabled');
		document.querySelector('input[name="diff1"]:not([disabled])').checked = true;
		document.querySelector('input[name="diff2"]:not([disabled])').checked = true;
		document.querySelector('td.form-buttons button').setAttribute('class', 'scalable');

		this.goDiff();
	},

	goDiff: function (url) {

		var diff1 = document.querySelector('input[name="diff1"]:checked'),
		    diff2 = document.querySelector('input[name="diff2"]:checked'), onclick;

		if (typeof url === 'string') {
			location.href = url;
		}
		else {
			onclick = document.querySelector('td.form-buttons button').getAttribute('onclick');
			onclick = onclick.replace(/from\/[^\/]+/, 'from/' + diff2.value);
			onclick = onclick.replace(/to\/[^\/]+/, 'to/' + diff1.value);

			document.querySelector('td.form-buttons button').setAttribute('onclick', onclick);
		}
	}
};

if (typeof window.addEventListener === 'function')
	window.addEventListener('load', versioning.start, false);