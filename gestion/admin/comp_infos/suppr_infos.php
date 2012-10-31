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
	
	include "../../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("../../login.php");
	
	if(!in_array($_SESSION['niveau'], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}
	
	// Suppression d'une lettre

	if(isset($_SESSION["info_doc_id"]))
		$info_doc_id=$_SESSION["info_doc_id"];
	else	// pas de numéro d'article : retour à l'index
	{
		header("Location:index.php");
		exit;
	}

	// section exécutée lorsque le formulaire est validé
	if(isset($_POST["confirmer"]) || isset($_POST["confirmer_x"]))
	{
		$dbr=db_connect();

		// D'abord, on vérifie que le document existe bien

		if(!db_num_rows(db_query($dbr,"SELECT * FROM $_DB_comp_infos WHERE $_DBC_comp_infos_id='$info_doc_id'")))
		{
			$err_file=realpath(__FILE__);
			$line=__LINE__;
			if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
			{
				mail($GLOBALS[__EMAIL_ADMIN], $GLOBALS[__ERREUR_SUJET], "Erreur dans $err_file, ligne $line\n'Lettre n°$art_id introuvable.'\nLogin : $_SESSION[auth_user]\nRows : $rows\n");
				die("Erreur : base de données incohérente. Un courriel a été envoyé à l'administrateur.");
			}
			else
				die("Erreur : base de données incohérente. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
		}

		db_query($dbr,"DELETE FROM $_DB_comp_infos WHERE $_DBC_comp_infos_id='$info_doc_id'");

		unset($_SESSION["info_doc_id"]);

		header("Location:index.php");
		exit;
	}
	else	// extraction de quelques données en vue de la confirmation de la suppression
	{
		$dbr=db_connect();
	
		// D'abord, on vérifie que la page existe
	
		if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_comp_infos WHERE $_DBC_comp_infos_id='$info_doc_id'")))
			db_close($dbr);
		else
		{
			db_close($dbr);

			header("Location:index.php");
			exit;
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Supprimer tout le contenu de la page d'information", "trashcan_full_32x32_slick_fond.png", 30, "L");

		message("La suppression est <b>définitive</b>. Tous les éléments (paragraphes, encadrés, ...) seront également supprimés.", $__WARNING);
		message("Souhaitez-vous vraiment supprimer le contenu de la page d'information ?", $__QUESTION);

		print("<form method='post' action='$php_self'>\n

				 <div class='centered_icons_box'>
					<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>
					<input type='image' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Confirmer' name='confirmer' value='Confirmer'>
				 </div>

				</form>");
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
