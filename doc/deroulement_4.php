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
	session_name("preinsc");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/db.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	en_tete_candidat_simple();
	menu_sup_simple();
?>

<div class='main'>
	<div class='centered_box'>
		<font class='Texte3'>
			<b>Dépôt de dossiers de précandidature
			<br><br>I - Déroulement d'une précandidature en ligne (4/5)</b>
		</font>
	</div>

	<div style='width:80%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<font class='Texte3'>
			<u><b>Etape 4 </b> : Verrouillage et justificatifs</u>
		</font>
		<font class='Texte'>
			<br><br>Une fois le délai imparti écoulé, chaque voeu est <strong>automatiquement verrouillé</strong>, la formation en question ne peut alors plus être 
			modifiée sur votre fiche.
			<br><br>
			<b>Attention :</b>
		</font>
		<font class='Texte_important'>
			<br>Votre cursus ne pourra plus être modifié après le verrouillage d'une formation (toutes composantes confondues). Veillez à bien compléter votre fiche
			<b>avant</b> le verrouillage des formations choisies !
		</font>
		<font class='Texte'>
			<br><br>
			Cependant, certains éléments pourront toujours être modifiés (comme les éléments de votre identité, en cas de changement d'adresse par exemple).
			<br><br>
			Vous recevrez alors, au plus tard 24 heures après le verrouillage d'un voeu, un courriel de notification vous invitant à consulter la <b>messagerie interne de l'application</b>.
			C'est via cette messagerie que vous pourrez consulter la liste des pièces justificatives à envoyer <b>par voie postale</b> au
			service de scolarité de chaque composante concernée par vos demandes.
			<br><br>La messagerie interne est accessible depuis votre fiche (menu "Messagerie").
		</font>
		<br><br>
		<b>Remarques :</b>
		<font class='Texte_important'>
			<br>- Vous devrez impérativement envoyer l'intégralité des pièces demandées, et faire remplir les éventuels formulaires par l'équipe pédagogique de votre
			établissement actuel.
			<br>- Pour les diplômes en cours de préparation, les justificatifs devront être envoyés dès leur obtention (n'attendez pas pour envoyer le reste des pièces demandées)
			<br>- Sauf instruction contraire d'une scolarité, vous devrez envoyer un exemplaire de vos justificatifs <b>pour chaque formation demandée</b> (et donc pour
			chaque courriel reçu).
			<br>- <b>Aucun dossier incomplet ne sera traité</b>.
			<br><br><br>
		</font>
	</div>
	<div class='centered_box' style='padding-bottom:30px;'>
		<a href='deroulement_3.php' class='lien_bleu_12'><img class='icone icone_texte_d' src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' border='0'></a>
		<a href='deroulement_3.php' class='lien_bleu_12' style='padding-right:50px;'><b>Etape 3 : Délai de modification de votre fiche</b></a>
		<a href='documentation.php' class='lien_bleu_10'>Retour au sommaire</a>
		<a href='deroulement_5.php' class='lien_bleu_12' style='padding-left:50px;'><b>Etape 5 : Suivi et décision</b></a>
		<a href='deroulement_5.php' class='lien_bleu_12'><img class='icone icone_texte_g' src='<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>' border='0'></a>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

