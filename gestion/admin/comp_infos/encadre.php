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
	include "include/editeur_fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	$dbr=db_connect();

	// Ajouter / modifier un encadré

	if(isset($_SESSION["info_doc_id"]))
		$info_doc_id=$_SESSION["info_doc_id"];
	else
	{
		header("Location:index.php");
		exit;
	}

	if(isset($_GET["a"]) && isset($_GET["o"])) // Nouvel élément
	{
		$_SESSION["ordre"]=$ordre=$_GET["o"];
		$_SESSION["ordre_max"]=$_SESSION["cbo"];
		$_SESSION["ajout"]=1;

		$action="Ajouter";
	}
	elseif(isset($_GET["o"])) // Modification
	{
		$_SESSION["ordre"]=$ordre=$_GET["o"];

		$action="Modifier";

		// Récupération des infos actuelles
		$result=db_query($dbr,"SELECT $_DBC_comp_infos_encadre_texte, $_DBC_comp_infos_encadre_txt_align FROM $_DB_comp_infos_encadre
															WHERE $_DBC_comp_infos_encadre_info_id='$info_doc_id'
															AND $_DBC_comp_infos_encadre_ordre='$ordre'");
		$rows=db_num_rows($result);
		if($rows)
		{
			list($texte,$alignement)=db_fetch_row($result,0);
			db_free_result($result);
		}
		else
		{
			db_close($dbr);
			header("Location:index.php");
			exit();
		}
	}

	if(isset($_SESSION["ajout"]) && $_SESSION["ajout"]==1)
		$action="Ajouter";
	else
		$action="Modifier";

	// section exécutée lorsque le formulaire est validé
	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$texte=trim($_POST['new_encadre']);
		$alignement=$_POST['alignement'];

//		if(str_is_clean($texte))
//		{
			// le nouveau texte est ok, on le modifie dans la table "encadre"
			// et on modifie la date de dernière modif de l'article

			if(!isset($_SESSION["ajout"]))
				db_query($dbr,"UPDATE $_DB_comp_infos_encadre SET 	$_DBU_comp_infos_encadre_texte='$texte',
																																		$_DBU_comp_infos_encadre_txt_align='$alignement'
												WHERE $_DBU_comp_infos_encadre_info_id='$info_doc_id'
												AND $_DBU_comp_infos_encadre_ordre='$_SESSION[ordre]'");
			else
			{
				if($_SESSION["ordre"]!=$_SESSION["ordre_max"]) // On n'insère pas l'élément en dernier : décallage
				{
					// 1 - Reconstruction des éléments (comme pour la suppression)
					$a=get_all_elements($dbr, $info_doc_id);
					$nb_elements=count($a);

					for($i=$nb_elements; $i>$_SESSION["ordre"]; $i--)
					{
						$current_ordre=$i-1;
						$new_ordre=$i;
						$current_type=$a["$current_ordre"]["type"]; // le type sert juste à savoir dans quelle table on doit modifier l'élément courant
						$current_id=$a["$current_ordre"]["id"];

						$current_table_name=get_table_name($current_type);
						$col_ordre=$current_table_name["ordre"];
						$col_id=$current_table_name["id"];
						$table=$current_table_name["table"];


						db_query($dbr,"UPDATE $table SET $col_ordre='$new_ordre'
															WHERE $col_id='$current_id'
															AND $col_ordre='$current_ordre'");
					}
				}

				// Insertion du nouvel élément
				db_query($dbr,"INSERT INTO $_DB_comp_infos_encadre VALUES ('$info_doc_id', '$texte', $alignement, '$_SESSION[ordre]')");
			}

			db_close($dbr);

			header("Location:index.php");
			exit;
//		}
//		else
//			$encadre_pas_clean=1;
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_simple();
?>
<div class='main'>
	<?php
		titre_page_icone("$action un encadré", "edit_32x32_fond.png", 30, "L");

		if(isset($encadre_pas_clean))
			message("<center>Erreur : le texte contient des caractères non autorisés.
						<br>Les caractères autorisés sont : a-z A-Z 0-9 - ' ! ? _ : . / @ ( ) les caractères accentués, la virgule et l'espace.</center>", $__ERREUR);

		if(isset($alignement))
		{
			switch($alignement)
			{
				case 0: 	$c0="checked";
							$c1=$c2=$c3="";
							break;

				case 1: 	$c1="checked";
							$c0=$c2=$c3="";
							break;

				case 2: 	$c2="checked";
							$c0=$c1=$c3="";
							break;

				case 3: 	$c3="checked";
							$c0=$c1=$c2="";
							break;

				default: $c0="checked";
							$c1=$c2=$c3="";
							break;
			}
		}
		else
		{
			$c0="checked";
			$c1=$c2=$c3="";
		}

		print("<form method='post' action='$php_self'>\n");
	?>

	<table align='center'>
	<tr>
		<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Données de l'encadré</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Nouveau texte :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><i>Les adresses http(s):// seront automatiquement transformées en liens HTML</i></font>
			<br><textarea  name='new_encadre' rows='10' cols='60' class='input'><?php if(isset($texte)) echo htmlspecialchars($texte, ENT_QUOTES); ?></textarea>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Alignement du texte :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				A gauche <input type='radio' name='alignement' value='0' <?php echo $c0; ?>>
				&nbsp;&nbsp;Centré <input type='radio' name='alignement' value='1' <?php echo $c1; ?>>
				&nbsp;&nbsp;A droite <input type='radio' name='alignement' value='2' <?php echo $c2; ?>>
				&nbsp;&nbsp;Justifié <input type='radio' name='alignement' value='3' <?php echo $c3; ?>>
			</font>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='valider' value='Valider'>
		</form>
	</div>

</div>
<?php
	pied_de_page();
	db_close($dbr);
?>
</body></html>
