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

	$dbr=db_connect();

	// edition d'un 'choix' pour un élément du constructeur de dossiers

	// élément
	if(!isset($_SESSION["element_id"]))
	{
		header("Location:index.php");
		exit;
	}

	if(isset($_GET["a"])) // Nouveau choix
	{
		$_SESSION["ajout_choix"]=1;
		$action="Ajouter";
	}
	elseif(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // Modification / suppression / changement d'ordre
	{
		// Modification
		if(isset($params["e"]) && $params["e"]==1 && isset($params["cid"]) && ctype_digit($params["cid"]))
		{
			$_SESSION["cid"]=$params["cid"];
			$action="Modifier";

			// Récupération des infos actuelles
			$result=db_query($dbr,"SELECT $_DBC_dossiers_elems_choix_texte FROM $_DB_dossiers_elems_choix
											WHERE $_DBC_dossiers_elems_choix_id='$_SESSION[cid]'");
			$rows=db_num_rows($result);

			if($rows)
			{
				list($texte)=db_fetch_row($result,0);
				db_free_result($result);
			}
			else // mauvais paramètres
			{
				db_free_result($result);
				db_close($dbr);
				header("Location:element.php");
				exit();
			}
		}
		// Suppression
		elseif(isset($params["s"]) && $params["s"]==1 && isset($params["cid"]) && ctype_digit($params["cid"]))
		{
			if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_dossiers_elems_choix
													WHERE $_DBC_dossiers_elems_choix_id='$params[cid]'")))

				db_query($dbr,"UPDATE $_DB_dossiers_elems_choix SET $_DBU_dossiers_elems_choix_ordre=$_DBU_dossiers_elems_choix_ordre-1
									WHERE $_DBU_dossiers_elems_choix_elem_id='$_SESSION[element_id]'
									AND $_DBU_dossiers_elems_choix_ordre > (SELECT $_DBC_dossiers_elems_choix_ordre FROM $_DB_dossiers_elems_choix
																						WHERE $_DBC_dossiers_elems_choix_id='$params[cid]');
									DELETE FROM $_DB_dossiers_elems_choix WHERE $_DBC_dossiers_elems_choix_id='$params[cid]'");
			
			db_close($dbr);
			header("Location:element.php");
			exit();
			
		}
		// Changement d'ordre
		elseif(isset($params["dir"]) && ($params["dir"]==1 || $params["dir"]==0) && isset($params["o"]) && ctype_digit($params["o"]) 
			 && isset($params["cid"]) && ctype_digit($params["cid"]))
		{
			// Récupération des infos actuelles
			if($params["dir"]==0 && $params["o"]!=0) // décalage vers le bas (incrémentation de l'ordre)
				$new_ordre_cond=$params["o"]+1;
			elseif($params["dir"]==1) // // décalage vers le haut (décrémentation de l'ordre) (ajouter un test ?)
				$new_ordre_cond=$params["o"]-1;

			if(isset($new_ordre_cond))
				db_query($dbr,"UPDATE $_DB_dossiers_elems_choix SET $_DBU_dossiers_elems_choix_ordre='$params[o]'
									WHERE $_DBU_dossiers_elems_choix_ordre='$new_ordre_cond'
									AND $_DBU_dossiers_elems_choix_elem_id='$_SESSION[element_id]';

									UPDATE $_DB_dossiers_elems_choix SET $_DBU_dossiers_elems_choix_ordre='$new_ordre_cond'
									WHERE $_DBU_dossiers_elems_choix_ordre='$params[o]'
									AND $_DBU_dossiers_elems_choix_id='$params[cid]'");

			db_close($dbr);
			header("Location:element.php");
			exit();
		}
		else // mauvais paramètres
		{
			db_close($dbr);
			header("Location:element.php");
			exit();
		}
	}

	// section exécutée lorsque le formulaire est validé
	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$texte=trim($_POST['new_texte']);
			
		if($texte=="")
			$texte_vide=1;
		else
		{
			if(!isset($_SESSION["ajout_choix"]))
				db_query($dbr,"UPDATE $_DB_dossiers_elems_choix SET $_DBU_dossiers_elems_choix_texte='$texte'
									WHERE $_DBC_dossiers_elems_choix_id='$_SESSION[cid]'");
			else
			{
				// détermination de l'ordre max
				$res_ordre=db_query($dbr, "SELECT max($_DBC_dossiers_elems_choix_ordre)+1 FROM $_DB_dossiers_elems_choix
													WHERE $_DBC_dossiers_elems_choix_elem_id='$_SESSION[element_id]'");

				list($new_ordre)=db_fetch_row($res_ordre, 0);

				if($new_ordre=="") $new_ordre="1";

				db_free_result($res_ordre);

				// Insertion du nouvel élément
				$new_cid=db_locked_query($dbr, $_DB_dossiers_elems_choix, "INSERT INTO $_DB_dossiers_elems_choix VALUES ('##NEW_ID##', '$_SESSION[element_id]','$texte', '$new_ordre')");
			}

			db_close($dbr);

			header("Location:element.php");
			exit;
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Constructeur de dossiers : $action un choix pour l'élément '$_SESSION[element_intitule]'", "edit_32x32_fond.png", 15, "L");
/*
		if(isset($para_pas_clean))
			message("<center>Erreur : le texte contient des caractères non autorisés.
						<br>Les caractères autorisés sont : a-z A-Z 0-9 - ' ! ? _ : . / @ ( ) les caractères accentués, la virgule et l'espace.</center>", $__ERREUR);
*/

		if(isset($texte_vide))
			message("Erreur : le texte ne doit pas être vide", $__ERREUR);

		$texte=isset($texte) ? htmlspecialchars(stripslashes($texte),ENT_QUOTES, $default_htmlspecialchars_encoding) : "";

		print("<form method='post' action='$php_self'>

				<table align='center'>
				<tr>
					<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
						<font class='Texte_menu2'>
							<b>&#8226;&nbsp;&nbsp;Données du 'choix'</b>
						</font>
					</td>
				</tr>
				<tr>
					<td class='td-gauche fond_menu2'>
						<font class='Texte_menu2'><b>Texte :</b></font>
					</td>
					<td class='td-droite fond_menu'>
						<textarea  name='new_texte' rows='2' cols='60' class='input'>$texte</textarea>
					</td>
				</tr>
				</table>

				<div class='centered_icons_box'>
					<a href='element.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
					<input type='image' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider' value='Valider'>
					<form>
				</div>\n");

		db_close($dbr);
	?>

</div>
<?php
	pied_de_page();
?>
</body></html>
