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
	// Ajout - Modification - Suppression des mentions

	session_name("preinsc_gestion");
	session_start();

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";


	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION["niveau"], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		session_write_close();
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	// Ajout, Modification ou suppression
	if(array_key_exists("a", $_GET) && ctype_digit($_GET["a"]))
		$_SESSION["ajout_mention"]=$_GET["a"]==1 ? 1 : 0;
	elseif(!isset($_SESSION["ajout_mention"]))
		$_SESSION["ajout_mention"]=0;

	if(array_key_exists("s", $_GET) && ctype_digit($_GET["s"]))
		$_SESSION["suppression"]=$_GET["s"]==1 ? 1 : 0;
	elseif(!isset($_SESSION["suppression"]))
		$_SESSION["suppression"]=0;

	if(array_key_exists("m", $_GET) && ctype_digit($_GET["m"]))
		$_SESSION["modification"]=$_GET["m"]==1 ? 1 : 0;
	elseif(!isset($_SESSION["modification"]))
		$_SESSION["modification"]=0;

	if(isset($_GET["succes"]))
		$succes=$_GET["succes"];

	$dbr=db_connect();

	if((isset($_POST["modifier"]) || isset($_POST["modifier_x"])) && array_key_exists("mention_id", $_POST) && ctype_digit($_POST["mention_id"]))
	{
		$mention_id=$_POST["mention_id"];
		$_SESSION["modification"]=1;
	}

	if((isset($_POST["supprimer"]) || isset($_POST["supprimer_x"])) && array_key_exists("mention_id", $_POST) && ctype_digit($_POST["mention_id"]))
	{
		$mention_id=$_POST["mention_id"];
		$_SESSION["suppression"]=1;
	}

	if(isset($_POST["conf_supprimer"]) || isset($_POST["conf_supprimer_x"]))
	{
		$mention_id=$_POST["mention_id"];

		if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_mentions WHERE $_DBU_mentions_id='$mention_id'"))==1)
		{
			db_query($dbr,"DELETE FROM $_DB_mentions WHERE $_DBU_mentions_id='$mention_id'");

			header("Location:$php_self?succes=1");
		}
		else
			header("Location:$php_self?erreur_suppr=1");

		db_close($dbr);

		exit();
	}
	elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		if(isset($_POST["mention_id"]))
			$mention_id=$_POST["mention_id"];

		$new_nom=ucfirst(trim($_POST["nom"]));
		$new_nom_court=ucfirst(trim($_POST["nom_court"]));
		$new_composante=$_POST["composante"];

		if($new_nom=="" || $new_nom_court=="")
			$champs_vides=1;

		// récupération des valeurs courantes, en cas de modification
		if($_SESSION["ajout_mention"]==0 && isset($mention_id))
		{
			$result=db_query($dbr,"SELECT $_DBC_mentions_nom, $_DBC_mentions_nom_court, $_DBC_mentions_comp_id
												FROM $_DB_mentions
											WHERE $_DBC_mentions_id='$mention_id'");
			$rows=db_num_rows($result);

			if(!$rows)
			{
				$_SESSION["modification"]=1;
				$mention_id_existe_pas=1;
			}
			else
			{
				list($current_nom, $current_nom_court, $current_comp_id)=db_fetch_row($result,0);
				db_free_result($result);

				// si la composante change (peu probable), on regarde si la mention existe déjà (ou pas) dans la nouvelle
				if($current_comp_id!=$new_composante)
				{
					if(db_num_rows(db_query($dbr,"SELECT $_DBC_mentions_id FROM $_DB_mentions
																WHERE $_DBC_mentions_nom ILIKE '$new_nom'
															AND $_DBC_mentions_nom_court ILIKE '$new_nom_court'
															AND $_DBC_mentions_comp_id='$new_composante'")))
						$nom_existe=1;
				}
				// sinon, on regarde dans la composante actuelle
				elseif($current_nom!=$new_nom || $current_nom_court!=$new_nom_court)
				{
					if(db_num_rows(db_query($dbr,"SELECT $_DBC_mentions_id FROM $_DB_mentions
																WHERE $_DBC_mentions_nom ILIKE '$new_nom'
															AND $_DBC_mentions_nom_court ILIKE '$new_nom_court'
															AND $_DBC_mentions_comp_id='$current_comp_id'
															AND $_DBC_mentions_id!='$mention_id'")))
						$nom_existe=1;
				}
			}
		}
		// En cas d'ajout : vérification d'unicité
		elseif(db_num_rows(db_query($dbr,"SELECT $_DBC_mentions_id FROM $_DB_mentions
														 WHERE ($_DBC_mentions_nom ILIKE '$new_nom'
													 	 		  OR $_DBC_mentions_nom_court ILIKE '$new_nom_court')
													 AND $_DBC_mentions_comp_id='$new_composante'")))
				$nom_existe=1;

		if(!isset($champs_vides) && !isset($nom_existe))
		{
			if($_SESSION["ajout_mention"]==0 && isset($mention_id))
				db_query($dbr,"UPDATE $_DB_mentions SET $_DBU_mentions_nom='$new_nom',
																		$_DBU_mentions_nom_court='$new_nom_court',
																		$_DBU_mentions_comp_id='$new_composante'
										WHERE $_DBU_mentions_id='$mention_id'");
			else
				$new_mention_id=db_locked_query($dbr, $_DB_mentions, "INSERT INTO $_DB_mentions VALUES('##NEW_ID##', '$new_nom', '$new_nom_court', '$new_composante')");

			db_close($dbr);
			header("Location:$php_self?succes=1");

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
		if($_SESSION["ajout_mention"]==1)
			titre_page_icone("Ajouter une mention", "add_32x32_fond.png", 30, "L");
		elseif(isset($_SESSION["action"]) && $_SESSION["action"]=="modification")
			titre_page_icone("Modifier une mention existante", "edit_32x32_fond.png", 30, "L");
		elseif(isset($_SESSION["action"]) && $_SESSION["action"]=="suppression")
			titre_page_icone("Supprimer une mention", "trashcan_full_34x34_slick_fond.png", 30, "L");
		else
			titre_page_icone("Gestion des mentions", "", 30, "L");

		// Messages d'erreur et de succès

		if(isset($mention_id_existe_pas) || isset($_GET["erreur_suppr"]))
			message("Erreur : l'identifiant demandé est incorrect (problème de cohérence de la base ?)", $__ERREUR);

		if(isset($champs_vides))
			message("Erreur : les champs en <strong>gras</strong> sont <strong>obligatoires</strong>.", $__ERREUR);

		if(isset($nom_existe))
			message("Erreur : une mention portant ce nom (ou ce nom court) existe déjà.", $__ERREUR);

		if(isset($succes) && $succes==1)
		{
			if($_SESSION["modification"]==1)
			{
				message("La mention a été modifiée avec succès.", $__SUCCES);
				$_SESSION["modification"]=0;
			}
			elseif($_SESSION["ajout_mention"]==1)
			{
				message("La mention a été créée avec succès.", $__SUCCES);
				$_SESSION["ajout_mention"]=0;
			}
			elseif($_SESSION["suppression"]==1)
			{
				message("La mention a été supprimée avec succès.", $__SUCCES);
				$_SESSION["suppression"]=0;
			}
		}

		print("<form action='$php_self' method='POST' name='form1'>\n");

		if($_SESSION["ajout_mention"]==0 && $_SESSION["modification"]==0 && $_SESSION["suppression"]==0)  // Choix de la mention à modifier
		{
			$result=db_query($dbr,"SELECT $_DBC_mentions_id, $_DBC_mentions_nom, $_DBC_mentions_nom_court,
													$_DBC_mentions_comp_id, $_DBC_composantes_nom
												FROM $_DB_mentions, $_DB_composantes
											WHERE $_DBC_mentions_comp_id=$_DBC_composantes_id
											AND $_DBC_mentions_comp_id='$_SESSION[comp_id]'
												ORDER BY $_DBC_mentions_comp_id, $_DBC_mentions_nom ASC");

			$rows=db_num_rows($result);

			if($rows)
			{
				print("<table cellpadding='4' cellspacing='0' border='0' align='center'>
						<tr>
							<td class='fond_menu2'>
								<font class='Texte_menu2' style='font-weight:bold;'>Mention : </font>
							</td>
							<td class='fond_menu'>
								<select name='mention_id' size='1'>
									<option value=''></option>\n");

				$old_comp="";

				for($i=0; $i<$rows; $i++)
				{
					list($mention_id,$mention_nom,$mention_nom_court,$comp_id, $comp_nom)=db_fetch_row($result,$i);

					if(in_array($_SESSION["niveau"], array("$__LVL_SUPER_RESP","$__LVL_ADMIN")))
						if($old_comp!=$comp_id)
						{
							if($i!=0)
								print("</optgroup>
											<option value='' label='' disabled></option>\n");

							$val=htmlspecialchars($comp_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);

							print("<optgroup label='$val'>\n");

							$old_comp=$comp_id;
						}

					$val=htmlspecialchars($mention_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);

					print("<option value='$mention_id' label=\"$val\">$val</option>\n");
				}

				print("		</optgroup>
							</select>
							</td>
						</tr>
						</table>\n");
			}
			else
			{
				$no_elements=1;
				message("Aucune mention n'a encore été créée.", $__INFO);
			}

			print("<div class='centered_icons_box'>
						<a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
						<a href='$php_self?a=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/add_32x32_fond.png' alt='Ajouter' title='[Ajouter une Mention]' border='0'></a>\n");

			if(!isset($no_elements))
				print("<input type='image' class='icone' src='$__ICON_DIR/edit_32x32_fond.png' alt='Modifier' name='modifier' value='Modifier' title='[Modifier une Mention]'>
						 <input type='image' class='icone' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Supprimer' name='supprimer' value='Supprimer' title='[Supprimer une Mention]'>\n");

			print("</form>
					</div>
					<script language='javascript'>
						document.form1.mention_id.focus()
					</script>\n");
		}
		elseif($_SESSION["suppression"]==1)
		{
			print("<form action='$php_self' method='POST' name='form1'>
						<input type='hidden' name='mention_id' value='$mention_id'>");

			$result=db_query($dbr,"SELECT $_DBC_mentions_nom FROM $_DB_mentions WHERE $_DBC_mentions_id='$mention_id'");

			list($nom_mention)=db_fetch_row($result,0);

			db_free_result($result);

			message("<center>
							<strong>Attention : </strong> toutes les spécialités (et formations) liées à cette mention seront <strong>supprimées</strong>.
							<br>Souhaitez vous vraiment supprimer la mention \"$nom_mention\" ?
						</center>", $__QUESTION);

			print("<div class='centered_icons_box'>
						<a href='$php_self?s=0' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' title='[Annuler la suppression]' border='0'></a>
						<input type='image' class='icone' src='$__ICON_DIR/trashcan_full_34x34_slick_fond.png' alt='Supprimer' title='[Confirmer la suppression]' name='conf_supprimer' value='Supprimer'>
						</form>
					 </div>\n");
		}
		elseif((isset($mention_id) && $_SESSION["modification"]==1) || $_SESSION["ajout_mention"]==1) // ajout ou modification (on récupère les infos actuelles)
		{
			if($_SESSION["ajout_mention"]==1)
			{
				if(!isset($new_nom)) // un seul test devrait suffire ...
					$new_nom=$new_nom_court=$new_composante="";
			}
			else
			{
				$result=db_query($dbr,"SELECT $_DBC_mentions_nom, $_DBC_mentions_nom_court, $_DBC_mentions_comp_id
													FROM $_DB_mentions
												WHERE $_DBC_mentions_id='$mention_id'");

				list($new_nom,$new_nom_court,$new_composante)=db_fetch_row($result,0);

				db_free_result($result);
			}

			print("<form action='$php_self' method='POST' name='form1'>\n");

			if(isset($mention_id))
			{
				print("<input type='hidden' name='mention_id' value='$mention_id'>\n");
			}
	?>

	<table align='center'>
	<tr>
		<td colspan='2' class='td-gauche fond_menu2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Informations</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Nom de la mention : </b></font></td>
		<td class='td-droite fond_menu'><input type='text' name='nom' value='<?php if(isset($new_nom)) echo htmlspecialchars($new_nom, ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='40'></td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Nom court : </b></font></td>
		<td class='td-droite fond_menu'><input type='text' name='nom_court' value='<?php if(isset($new_nom_court)) echo htmlspecialchars($new_nom_court, ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='40'></td>
	</tr>

	<?php
		if(in_array($_SESSION["niveau"], array("$__LVL_SUPER_RESP","$__LVL_ADMIN")))
		{
	?>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Composante</b></font></td>
		<td class='td-droite fond_menu'>
			<select name='composante'>
				<?php
					$result2=db_query($dbr, "SELECT $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_univ_id,
																$_DBC_universites_nom
														FROM $_DB_composantes, $_DB_universites
													WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
														ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");

					$old_univ="";

					$rows2=db_num_rows($result2);

					for($i=0; $i<$rows2; $i++)
					{
						list($comp_id, $comp_nom, $univ_id, $univ_nom)=db_fetch_row($result2,$i);

						if($univ_id!=$old_univ)
							print("<option disabled>--- $univ_nom ---</option>\n");

						if(isset($new_composante) && $new_composante==$comp_id)
							$selected="selected";
						elseif(isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==$comp_id)
							$selected="selected";
						else
							$selected="";

						print("<option value='$comp_id' $selected>$comp_nom</option>\n");

						$old_univ=$univ_id;
					}

					db_free_result($result2);
				?>
			</select>
		</td>
	</tr>
	<?php
		}
		else
			print("<input type='hidden' name='composante' value='$_SESSION[comp_id]'>");
	?>
	</table>

	<script language='javascript'>
		document.form1.nom.focus()
	</script>

	<div class='centered_icons_box'>
		<a href='<?php echo "$php_self?m=0&a=0"; ?>' target='_self' class='lien_bleu_12'><img class='icone' src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" class='icone' src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
		</form>
	</div>

	<?php
		}
		db_close($dbr);
	?>

</div>
<?php
	pied_de_page();
?>
</body></html>

