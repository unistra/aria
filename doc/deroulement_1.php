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
			<br><br>I - Déroulement d'une précandidature en ligne (1/5)</b>
		</font>
	</div>

	<div style='width:80%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<font class='Texte'>
			<br>Le dépôt d'une précandidature en ligne se déroule en plusieurs étapes :
			<br><br>
		</font>
		<font class='Texte3'>
			<u><b>Etape 1 </b> : Enregistrement : obtention de votre identifiant et de votre mot de passe</u>
		</font>
		<font class='Texte'>
			<br><br>Après avoir lu et accepté les conditions de la page d'accueil, vous devez remplir le formulaire d'enregistrement afin d'obtenir votre
			identifiant et votre mot de passe.
			<br><br>
			Vous devez en particulier fournir certaines données vous concernant :
			<br>&#8226;&nbsp;&nbsp;votre nom, prénom et date de naissance
			<br>&#8226;&nbsp;&nbsp;votre lieu de naissance et votre nationalité (données importantes car les justificatifs à fournir sont différents selon les cas)
		</font>
		<font class='Texte_important'>
			<br>&#8226;&nbsp;&nbsp;<b>une adresse électronique (<i>email</i>) valide</b> : de nombreuses correspondances seront envoyées à cette adresse
			<br>&#8226;&nbsp;&nbsp;<b>une adresse postale permanente valide</b>, pour les éventuels courriers officiels d'admission ou de refus
		</font>
		<font class='Texte'>
			<br><br>
			Après validation du formulaire, vous recevrez un identifiant et un mot de passe à l'adresse électronique indiquée. Ces informations sont
			<b>strictement confidentielles</b> : mémorisez les et <b>ne les divulguez à personne</b> car celà pourrait engager votre responsabilité.
			<br><br>
			Si vous avez déjà déposé un dossier sur cette interface (année précédente, par exemple), il n'est pas nécessaire de vous enregistrer de nouveau : vous pouvez
			réutiliser vos identifiants, mais n'oubliez pas de mettre ensuite votre fiche à jour (adresse postale, cursus, ...).
		</font>
	</div>
	<div class='centered_box' style='padding-bottom:30px;'>
		<a href='documentation.php' class='lien_bleu_12'><img class='icone icone_texte_d' src='<?php echo "$__ICON_DIR/rew_32x32_fond.png"; ?>' border='0'></a>
		<a href='documentation.php' class='lien_bleu_12' style='padding-right:50px;'><b>Sommaire</b></a>
		<a href='deroulement_2.php' class='lien_bleu_12' style='padding-left:50px;'><b>Etape 2 : Présentation de l'interface de saisie</b></a>
		<a href='deroulement_2.php' class='lien_bleu_12'><img class='icone icone_texte_g' src='<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>' border='0'></a>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

