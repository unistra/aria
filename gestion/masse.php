<?php
/*
=======================================================================================================
APPLICATION ARIA - UNIVERSITE DE STRASBOURG

LICENCE : CECILL-B
Copyright Universit� de Strasbourg
Contributeur : Christophe Boccheciampe - Janvier 2006
Adresse : cb@dpt-info.u-strasbg.fr

L'application utilise des �l�ments �crits par des tiers, plac�s sous les licences suivantes :

Ic�nes :
- CrystalSVG (http://www.everaldo.com), sous licence LGPL (http://www.gnu.org/licenses/lgpl.html).
- Oxygen (http://oxygen-icons.org) sous licence LGPL-V3
- KDE (http://www.kde.org) sous licence LGPL-V2

Librairie FPDF : http://fpdf.org (licence permissive sans restriction d'usage)

=======================================================================================================
[CECILL-B]

Ce logiciel est un programme informatique permettant � des candidats de d�poser un ou plusieurs
dossiers de candidatures dans une universit�, et aux gestionnaires de cette derni�re de traiter ces
demandes.

Ce logiciel est r�gi par la licence CeCILL-B soumise au droit fran�ais et respectant les principes de
diffusion des logiciels libres. Vous pouvez utiliser, modifier et/ou redistribuer ce programme sous les
conditions de la licence CeCILL-B telle que diffus�e par le CEA, le CNRS et l'INRIA sur le site
"http://www.cecill.info".

En contrepartie de l'accessibilit� au code source et des droits de copie, de modification et de
redistribution accord�s par cette licence, il n'est offert aux utilisateurs qu'une garantie limit�e.
Pour les m�mes raisons, seule une responsabilit� restreinte p�se sur l'auteur du programme, le titulaire
des droits patrimoniaux et les conc�dants successifs.

A cet �gard l'attention de l'utilisateur est attir�e sur les risques associ�s au chargement, �
l'utilisation, � la modification et/ou au d�veloppement et � la reproduction du logiciel par l'utilisateur
�tant donn� sa sp�cificit� de logiciel libre, qui peut le rendre complexe � manipuler et qui le r�serve
donc � des d�veloppeurs et des professionnels avertis poss�dant  des  connaissances informatiques
approfondies. Les utilisateurs sont donc invit�s � charger et tester l'ad�quation du logiciel � leurs
besoins dans des conditions permettant d'assurer la s�curit� de leurs syst�mes et ou de leurs donn�es et,
plus g�n�ralement, � l'utiliser et l'exploiter dans les m�mes conditions de s�curit�.

Le fait que vous puissiez acc�der � cet en-t�te signifie que vous avez pris connaissance de la licence
CeCILL-B, et que vous en avez accept� les termes.

=======================================================================================================
*/
?>
<?php
	session_name("preinsc_gestion");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	unset($_SESSION["mails_masse"]);

	verif_auth();

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>
<div class='main'>
	<?php
		titre_page_icone("Traitement de d�cisions et g�n�ration de documents en masse", "kpersonalizer_32x32_fond.png", 15, "L");
	?>

	<table align='center'>
	<tr>
		<td class='fond_menu2' style='padding:4px;'>
			<font class='Texte_menu2'><b>Vous souhaitez  ... </b></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<?php
				$txt=(isset($_SESSION["composante_entretiens"]) && $_SESSION["composante_entretiens"]==1) ? " (et les dates d'entretiens)" : "";

				print("<a href='masse_traitement.php' target='_self' class='lien_menu_gauche'>- Saisir des d�cisions en masse$txt<br>- Consulter vos saisies ant�rieures<br>- G�n�rer les lettres correspondant � vos saisies en masse</a>\n");
			?>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<a href='masse_recap.php' target='_self' class='lien_menu_gauche'>- G�n�rer les R�capitulatifs de fiches en fonction d'une formation (ou d'une ann�e enti�re) et d'un intervalle de temps</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<a href='masse_formulaire.php' target='_self' class='lien_menu_gauche'>- G�n�rer les Formulaires de Commissions en fonction d'une formation et d'un intervalle de temps</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<a href='masse_listes_entretiens.php' target='_self' class='lien_menu_gauche'>- G�n�rer les listes de candidats convoqu�s aux entretiens, en fonction d'une formation, d'une date et d'une salle</a>
			<br><a href='masse_listes_entretiens_tel.php' target='_self' class='lien_menu_gauche'>- G�n�rer les listes de candidats convoqu�s aux entretiens t�l�phoniques</a>
		</td>
	</tr>
	
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<a href='masse_pdf.php' target='_self' class='lien_menu_gauche'>- G�n�rer les lettres officielles en fonction d'une formation et de la date de saisie de la d�cision<br>&nbsp;&nbsp;(fonction ind�pendante de la saisie en masse)</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<a href='extractions_csv.php' target='_self' class='lien_menu_gauche'>- Exporter des donn�es brutes au format CSV (pour import dans un tableur)</a>
		</td>
	</tr>
	</table>
</div>
<?php
	pied_de_page();
?>
</body></html>
