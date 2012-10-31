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
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(!isset($_SESSION["lock"]) || $_SESSION["lock"]==1)
	{
		session_write_close();
		header("Location:precandidatures.php");
		exit();
	}

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}

	$dbr=db_connect();

	$candidat_id=$_SESSION["authentifie"];

	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // modification d'un élément existant : l'identifiant est en paramètre
	{
		if(isset($params["la_id"]) && is_numeric($params["la_id"]))
			$_SESSION["la_id"]=$params["la_id"];

		$_SESSION["la_txt"]=isset($params["la_nom"]) ? "en " . stripslashes($params["la_nom"]) : "";

		if(isset($params["suppr"]) && is_numeric($params["suppr"]))
		{
			$la_dip_id=$params["suppr"];

			if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_langues_dip WHERE $_DBC_langues_dip_id='$la_dip_id' AND $_DBC_langues_dip_langue_id='$_SESSION[la_id]'")))
				db_query($dbr,"DELETE FROM $_DB_langues_dip WHERE $_DBC_langues_dip_id='$la_dip_id' AND $_DBC_langues_dip_langue_id='$_SESSION[la_id]'");

			session_write_close();
			header("Location:precandidatures.php");
			exit();
		}
	}

	if(!isset($_SESSION["la_id"]))
	{
		session_write_close();
		header("Location:precandidatures.php");
		exit();
	}

	if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
	{
		// Diplôme
		$diplome=trim($_POST["diplome"]);
		$annee_obtention=trim($_POST["annee_obtention"]);
		$resultat=trim($_POST["resultat"]);

		// vérification du format de l'année (sauf si le champ est vide)
		if(empty($annee_obtention))
			$annee_obtention=0;
		elseif(!ctype_digit($annee_obtention) || $annee_obtention>date("Y"))
			$annee_format=1;

		if(empty($diplome))
			$champ_vide=1;

		if(!isset($champ_vide) && !isset($annee_format))
		{
			// vérification d'unicité
			if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_langues_dip
														WHERE $_DBC_langues_dip_langue_id='$_SESSION[la_id]'
														AND $_DBC_langues_dip_nom ILIKE '$diplome'
														AND $_DBC_langues_dip_annee='$annee_obtention'")))
				$langue_dip_existe=1;
			else
			{
				// Vérification que la langue associée existe bien
				// TODO : vérification à généraliser pour tous les autres éléments
				if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_langues WHERE $_DBC_langues_id='$_SESSION[la_id]'
																						  	  AND $_DBC_langues_candidat_id='$candidat_id'")))
				{
					db_close($dbr);
					
					session_write_close();
					header("Location:precandidatures.php?err_langue=1");
					exit();
				}
					
				$new_id=db_locked_query($dbr, $_DB_langues_dip, "INSERT INTO $_DB_langues_dip VALUES('##NEW_ID##','$_SESSION[la_id]','$diplome','$annee_obtention','$resultat')");
				db_close($dbr);

				session_write_close();
				header("Location:precandidatures.php");
				exit();
			}
		}
	}
	
	en_tete_candidat();
	menu_sup_candidat($__MENU_FICHE);
?>

<div class='main'>
	<?php
		titre_page_icone("Langues : diplômes obtenus $_SESSION[la_txt]", "edu_languages_32x32_fond.png", 15, "L");

		if(isset($champ_vide))
			message("Formulaire incomplet : tous les champs sont <u>obligatoires</u>", $__ERREUR);
		elseif(isset($annee_format))
			message("Le format de l'année d'obtention est incorrect.", $__ERREUR);
		elseif(isset($langue_dip_existe))
			message("Ce diplôme existe déjà pour cette langue.", $__ERREUR);
		else
			message("Tous les champs sont obligatoires", $__WARNING);

		print("<form action='$php_self' method='POST' name='form1'>\n");
	?>
	
	<table style="margin-left:auto; margin-right:auto;">
	<tr>
		<td class='td-gauche fond_menu2' align='left' nowrap='true'>
			<font class='Texte_menu2'><b>Nom du diplôme de langue :</b></font>
		</td>
		<td class='td-droite fond_menu' align='left' nowrap='true'>
			<input type='text' name='diplome' value='<?php if(isset($diplome)) echo htmlspecialchars(stripslashes($diplome),ENT_QUOTES); ?>' size="25" maxlength="128">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' align='left' nowrap='true'>
			<font class='Texte_menu2'><b>Année d'obtention (YYYY):</b></font>
		</td>
		<td class='td-droite fond_menu' align='left' nowrap='true'>
			<input type='text' name='annee_obtention' value='<?php if(isset($annee_obtention)) echo htmlspecialchars(stripslashes($annee_obtention),ENT_QUOTES); ?>' size="25" maxlength="4">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' align='left' nowrap='true'>
			<font class='Texte_menu2'><b>Résultat / Note / Mention :</b></font>
		</td>
		<td class='td-droite fond_menu' align='left' nowrap='true'>
			<input type='text' name='resultat' value='<?php if(isset($resultat)) echo htmlspecialchars(stripslashes($resultat),ENT_QUOTES); ?>' size="25" maxlength="128">
		</td>
	</tr>
	</table>	
	
	<div class='centered_icons_box'>
		<a href='precandidatures.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
		</form>
	</div>
	
</div>
<?php
	db_close($dbr);
	pied_de_page_candidat();
?>

<script language="javascript">
<!--
document.form1.diplome.focus()
//-->
</script>

</body></html>
