<?php
/*
=======================================================================================================
APPLICATION ARIA - UNIVERSITE DE STRASBOURG

LICENCE : CECILL-B
Copyright Université de Strasbourg
Contributeur : Christophe Boccheciampe - Janvier 2006
Adresse : cb@dpt-info.u-strasbg.fr

L'application utilise des éléments écrits par des tiers, placés sous les licences suivantes :

Icônes :
- CrystalSVG (http://www.everaldo.com), sous licence LGPL (http://www.gnu.org/licenses/lgpl.html).
- Oxygen (http://oxygen-icons.org) sous licence LGPL-V3
- KDE (http://www.kde.org) sous licence LGPL-V2

Librairie FPDF : http://fpdf.org (licence permissive sans restriction d'usage)

=======================================================================================================
[CECILL-B]

Ce logiciel est un programme informatique permettant à des candidats de déposer un ou plusieurs
dossiers de candidatures dans une université, et aux gestionnaires de cette dernière de traiter ces
demandes.

Ce logiciel est régi par la licence CeCILL-B soumise au droit français et respectant les principes de
diffusion des logiciels libres. Vous pouvez utiliser, modifier et/ou redistribuer ce programme sous les
conditions de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA sur le site
"http://www.cecill.info".

En contrepartie de l'accessibilité au code source et des droits de copie, de modification et de
redistribution accordés par cette licence, il n'est offert aux utilisateurs qu'une garantie limitée.
Pour les mêmes raisons, seule une responsabilité restreinte pèse sur l'auteur du programme, le titulaire
des droits patrimoniaux et les concédants successifs.

A cet égard l'attention de l'utilisateur est attirée sur les risques associés au chargement, à
l'utilisation, à la modification et/ou au développement et à la reproduction du logiciel par l'utilisateur
étant donné sa spécificité de logiciel libre, qui peut le rendre complexe à manipuler et qui le réserve
donc à des développeurs et des professionnels avertis possédant  des  connaissances informatiques
approfondies. Les utilisateurs sont donc invités à charger et tester l'adéquation du logiciel à leurs
besoins dans des conditions permettant d'assurer la sécurité de leurs systèmes et ou de leurs données et,
plus généralement, à l'utiliser et l'exploiter dans les mêmes conditions de sécurité.

Le fait que vous puissiez accéder à cet en-tête signifie que vous avez pris connaissance de la licence
CeCILL-B, et que vous en avez accepté les termes.

=======================================================================================================
*/
?>
<?php
	session_name("preinsc_gestion");
	session_start();

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	// EN-TETE SIMPLIFIEE (pas de composante, pas de menu, rien
	en_tete_simple();

	// MENU SUPERIEUR SIMPLIFIE
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("[Aide] Candidature : décision de la commission pédagogique", "help-browser_32x32_fond.png", 15, "L");
	?>

	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte'>
			<b>Saisir une décision rendue par la Commission Pédagogique et/ou une date de convocation à un entretien</b>
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			Cette page est accessible uniquement lorsque le voeu concerné est verrouillé et que la candidature est
			recevable.
		</p>

		<font class='Texte_16'><u><b>Fonctionnalités et options</b></u></font>

		<p class='Texte' style='padding-bottom:15px;'>
			<u><b>Sélection de la décision</b></u> : décision partielle ou finale rendue par la commission (les choix
			disponibles sont configurables dans le menu Administration). Chaque décision pourra être rattachée à un ou
			plusieurs modèles de lettres, en fonction des besoin de votre établissement :
		</p>

		<font class='Texte'>
		<table cellpadding='2' cellspacing='0' border='1' align='center' width='100%'>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Admis (1)</td>
			<td align='justify' valign='top'>Admission définitive du candidat</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Admis après recours (1)</td>
			<td align='justify' valign='top'>Admission définitive du candidat après la validation d'un recours</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Admis après entretien (1)</td>
			<td align='justify' valign='top'>Admission définitive du candidat après entretien complémentaire</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Admis depuis la liste complémentaire (1)</td>
			<td align='justify' valign='top'>Admission définitive du candidat par passage de la liste complémentaire vers la liste principale</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Admis sous réserve (2)</td>
			<td align='justify' valign='top'>Le candidat est admis si la réserve imposée est vérifiée (par exemple : obtention du diplôme en cours de préparation)</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Convocable à l'entretien (3)</td>
			<td align='justify' valign='top'>Le candidat devra se présenter pour un entretien complémentaire</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Dossier transmis (2)</td>
			<td align='justify' valign='top'>
				Le candidat n'est pas admis dans la formation demandée, mais la commission a proposé son admission dans
				une autre formation (année N-1 par exemple).
				<br>- Si la formation "cible" est proposée dans le même établissement, elle doit être saisie dans le champ
				"Transmission => Nouvelle formation". La candidature est alors automatiquement créée dans	l'interface et
				pourra être traitée indépendamment si besoin.
				<br>- Dans le cas contraire, elle peut être saisie en toute lettre dans le champ situé juste en dessous et
				le dossier devra être transféré manuellement dans l'établissement cible.
				<br><br><b>Remarque</b> : cette décision n'est utile que si le candidat est <b>admis</b> (sous réserve
				ou non) dans la formation cible, elle est inutile sinon. Un modèle de lettre spécial peut être créé pour
				cette décision, indiquant à la fois au candidat qu'il a été refusé pour cette formation mais qu'il peut
				toutefois accepter le transfert proposé par la commission (cf. macros de l'Editeur de lettres).
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Désistement</td>
			<td align='justify' valign='top'>
				Le candidat n'a finalement pas souhaité poursuivre sa candidature. Cette décision est surtout utile pour
				produire une lettre de confirmation à destination du dossier et/ou du candidat.
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; En attente (2)</td>
			<td align='justify' valign='top'>
				La commission ne s'est pas définitivement prononcée sur la candidature, des élements complémentaires
				peuvent être demandés au candidat (nuance par rapport à "Admis sous réserve").
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Liste complémentaire</td>
			<td align='justify' valign='top'>
				Le candidat est placé sur liste complémentaire. Si un rang est indiqué dans le champ correspondant, les
				candidats présents sur la suite de la liste seront décalés. Dans le cas contraire, le candidat est
				automatiquement placé en queue de liste (le tri peut se faire ultérieurement, mais attention à votre
				modèle de lettre si le rang y figure).
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Liste complémentaire après entretien</td>
			<td align='justify' valign='top'>
				Suite à l'entretien passé par le candidat, ce dernier est placé sur liste complémentaire. Les remarques
				concernant la décision "Liste complémentaire" s'appliquent également.
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Non traitée</td>
			<td align='justify' valign='top'>
				Etat par défaut lorsque la décision n'a pas été saisie. Aucune validation n'est nécessaire.
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Refus (2)</td>
			<td align='justify' valign='top'>Le candidat est refusé.</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Refus après entretien (2)</td>
			<td align='justify' valign='top'>Suite à l'entretien passé par le candidat, ce dernier est refusé.</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Refus après recours (2)</td>
			<td align='justify' valign='top'>Le candidat a déposé un recours mais il n'a pas été accepté.</td>
		</tr>
		</table>
		</font>

		<p class='Texte'>	
			(1) aucun champs supplémentaire n'est requis, vous pouvez valider directement le formulaire
			<br>(2) un motif ou une réserve doit obligatoirement être saisi pour pouvoir valider cette décision
			<br>(3) vous devez saisir la date, l'heure et le lieu de l'entretien
		</p>
		<p class='Texte'>
			<u><b>Transmission : choix de la nouvelle formation</b></u> : la Commission Pédagogique peut refuser une candidature
			tout en proposant un choix plus adapté au candidat. Lorsque c'est le cas, on parle de transmission de dossier
			(décision "Dossier Transmis") et c'est ici que vous devez indiquer la formation proposée par la Commission. Si
			la formation se trouve dans une autre composante, utilisez le champ libre prévu à cet effet, il faudra alors
			transférer les pièces du dossier à la composante cible.
		</p>
		<p class='Texte'>
			<u><b>Rang sur liste complémentaire</b></u> : lorsque le candidat est placé sur liste complémentaire et que vous
			connaissez le rang sur cette liste, vous devez l'indiquer ici. Si vous laissez ce champ vide, le candidat sera
			automatiquement placé en queue de liste. Note : il faut avoir sélectionné la décision "Liste complémentaire"
			(après entretien ou non) pour que ce champ soit pris en compte.
		</p>
		<p class='Texte'>
			<u><b>Entretien</b></u> : pour certaines formations proposées par votre établissement, les candidats doivent
			passer un entretien complémentaire. Ces champs servent à entrer la date et l'heure de la convocation.
			<br>Note 1 : si vous	laissez vides les champs "Salle" et "Lieu", les valeurs par défaut seront utilisées (cf.
			configuration de la composante)
			<br>Note 2 : si la formation ne nécessite aucun entretien, ces champs n'apparaîtront pas (cf. configuration
			de la formation).
		</p>
		<p class='Texte'>
			<u><b>Confirmation du candidat</b></u> : si vos modèles de lettres possèdent un talon réponse demandant au
			candidat de confirmer sa candidature, vous pourrez indiquer la réponse à cet endroit.
		</p>
		<p class='Texte'>
			<u><b>Forcer la date des lettres</b></u> : si la date des lettres générées par l'interface n'est pas correcte ou
			si vous traitez une candidature avant la date de la Commission Pédagogique, vous pouvez forcer la date de la
			lettre pour cette candidature. </font><font class='Texte_important'>Ce champ doit être utilisé avec prudence</font>.
			<font class='Texte'>
		</p>
		<p class='Texte'>
			<u><b>Motifs de refus, de réserve ou de mise en attente</b></u> : en fonction de la décision saisie, vous devez
			indiquer le ou les motifs relatifs à cette dernière. Si un motif n'apparait pas dans la liste prédéfinie (cases
			à cocher), vous pouvez utiliser le champ libre dans la partie droite. C'est notamment le cas des réserves et des
			mises en attente.
			<br>Note : pour ajouter des motifs, consultez l'aide du menu Configuration.
		</p>
		<p class='Texte'>
			<u><b>Validation</b></u> : l'icône verte sous le formulaire vous permet de valider la saisie. En cas d'erreur,
			l'interface restera sur cet écran et vous indiquera pourquoi la validation a échoué (par exemple : absence de
			motif dans le cas d'un refus).
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
