/**
 * Created J/22/12/2011
 * Updated S/28/04/2012
 * Version 5
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

// initialisation des traductions
function luigifabVersioningInit() {

	apijs.i18n.data.en.versioning_uptitle = "Upgrade to revision §";
	apijs.i18n.data.en.versioning_uptext = "Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.";
	apijs.i18n.data.en.versioning_uptext_code = "[p]Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.[/p][p][label][input type='checkbox' name='code' value='true'] Update the application code[/label][/p]";

	apijs.i18n.data.fr.versioning_uptitle = "Mise à jour vers la révision §";
	apijs.i18n.data.fr.versioning_uptext_code = "[p]Êtes-vous sûr de vouloir lancer le processus de mise à jour ?[br]Attention, cette opération ne peut pas être annulée.[/p][p][label][input type='checkbox' name='code' value='true'] Mettre à jour le code application[/label][/p]";
	apijs.i18n.data.fr.versioning_uptext = "Êtes-vous sûr de vouloir lancer le processus de mise à jour ?[br]Attention, cette opération ne peut pas être annulée.";

	apijs.i18n.data.en.versioning_deltitle = "Deleting";
	apijs.i18n.data.en.versioning_deltext = "Are you sure you want to delete this log?[br]Be careful, you can't cancel this operation.";

	apijs.i18n.data.fr.versioning_deltitle = "Suppression";
	apijs.i18n.data.fr.versioning_deltext = "Êtes-vous sûr de vouloir supprimer cet historique ?[br]Attention, cette opération ne peut pas être annulée.";
}

// demande de confirmation (livraison)
function luigifabVersioningUpgrade(url, go, compressor) {

	if (url === true)
		return true;

	if ((typeof apijs !== 'undefined') && (typeof apijs !== null)) {

		luigifabVersioningInit();

		if (go !== false) {
			location.href = url;
		}
		else {
			var revision = url.match(/revision\/([0-9a-z]+)\//);

			if (compressor === true) {
				apijs.dialogue.dialogFormOptions(
					apijs.i18n.translate('versioning_uptitle', RegExp.$1),
					apijs.i18n.translate('versioning_uptext_code'),
					luigifabVersioningUpgrade, true, url, 'versioning'
				);
				$('box').setAttribute('method', 'get');
				$$('#box button')[0].focus();
			}
			else {
				apijs.dialogue.dialogConfirmation(
					apijs.i18n.translate('versioning_uptitle', RegExp.$1),
					apijs.i18n.translate('versioning_uptext'),
					luigifabVersioningUpgrade, url, 'versioning'
				);
			}
		}

		return false;
	}
	else {
		var revision = url.match(/revision\/([0-9a-z]+)\//);
		return confirm('Are you sure (revision ' + RegExp.$1 + ')?');
	}
}

// demande de confirmation (suppression des historiques)
function luigifabVersioningDelete(url, go) {

	if ((typeof apijs !== 'undefined') && (typeof apijs !== null)) {

		luigifabVersioningInit();

		if (go !== false) {
			location.href = url;
		}
		else {
			apijs.dialogue.dialogConfirmation(
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