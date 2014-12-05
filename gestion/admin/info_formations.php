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

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	// Argument chiffré pour un accès direct à la modification d'une formation
	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
	{
		if(isset($params["propspec"]) && ctype_digit($params["propspec"])
			&& db_num_rows(db_query($dbr,"SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_id='$params[propspec]'
													AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'")))
		{
			$mod_propspec_id=$params["propspec"];

			// En cas d'annulation ou de validation, on revient à l'offre de formation (plus rapide)
			$_SESSION["adresse_retour"]="offre.php";
		}
	}

	if(isset($_GET["succes"]))
		$succes=$_GET["succes"];

	if(isset($_GET["e"]) && $_GET["e"]==1)
	{
		unset($_SESSION["formation"]);
		unset($_SESSION["current_annee_nom"]);
		unset($_SESSION["current_spec_nom"]);
	}

	// validation du 1er formulaire (ou choix direct via l'URL) : choix de la formation
	if(isset($mod_propspec_id) || ((isset($_POST["suivant"]) || isset($_POST["suivant_x"])) && isset($_POST["formation"])) || (isset($_SESSION["formation"]) && (!isset($_POST["valider"]) && !isset($_POST["valider_x"]))))
	{
		if(isset($mod_propspec_id))
			$_SESSION["formation"]=$formation=$mod_propspec_id;
		elseif(isset($_POST["formation"]))
			$_SESSION["formation"]=$formation=$_POST["formation"];
		elseif(isset($_SESSION["formation"]))
			$formation=$_SESSION["formation"];

		if($formation=="")
			$formation_vide=1;

		if(!isset($formation_vide))
		{
			$result=db_query($dbr, "SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite, $_DBC_propspec_active,
													 $_DBC_propspec_info
												FROM $_DB_propspec, $_DB_annees, $_DB_specs
											WHERE $_DBC_propspec_id='$_SESSION[formation]'
											AND $_DBC_annees_id=$_DBC_propspec_annee
											AND $_DBC_specs_id=$_DBC_propspec_id_spec");

			if(db_num_rows($result))
			{
				list($_SESSION["current_annee_nom"], $_SESSION["current_spec_nom"], $current_finalite, $current_active, $current_info)=db_fetch_row($result,0);

				$nom_finalite=$tab_finalite[$current_finalite];

				$resultat=1;

				if($_SESSION["current_annee_nom"]=="")
					$nom_formation="$_SESSION[current_spec_nom] $nom_finalite";
				else
					$nom_formation="$_SESSION[current_annee_nom] $_SESSION[current_spec_nom] $nom_finalite";
			}
			else
				$erreur_formation=1;

			db_free_result($result);
		}
	}
	elseif(isset($_SESSION["formation"]) && (isset($_POST["valider"]) || isset($_POST["valider_x"])))	// validation du 2nd formulaire : confirmation
	{
		$propspec_id=$_SESSION["formation"];

		$new_info=$_POST["information"];

		db_query($dbr,"UPDATE $_DB_propspec SET $_DBU_propspec_info='$new_info' WHERE $_DBU_propspec_id='$propspec_id'");

		if(isset($_SESSION["adresse_retour"]) && !empty($_SESSION["adresse_retour"]))
		{
			header("Location:$_SESSION[adresse_retour]?info_succes=1");
			
			unset($_SESSION["formation"]);
			unset($_SESSION["current_annee_nom"]);
			unset($_SESSION["current_spec_nom"]);
		}
		else
			header("Location:$php_self?e=1&succes=1");
	
		unset($_SESSION["adresse_retour"]);
		db_close($dbr);
		exit;
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Informations importantes sur cette formation", "messagebox_warning_32x32_fond.png", 15, "L");

		if(isset($erreur_formation))
			message("Erreur : formation inconnue (erreur probable dans la base de données).", $__ERREUR);

		if(isset($formation_vide))
			message("Erreur : vous devez sélectionner une formation valide", $__ERREUR);

		if(isset($composante_vide))
			message("Erreur : vous devez sélectionner une composante valide", $__ERREUR);

		if(isset($succes))
			message("Les informations ont été mises à jour avec succès.", $__SUCCES);

		print("<form action='$php_self' method='POST' name='form1'>\n");
	?>
	<table style='margin-left:auto; margin-right:auto'>
	<tr>
		<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'><strong>Formation</strong></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Composante : </b></font>
		</td>
		<td class='td-droite fond_menu2'>
			<font class='Texte_menu2'><b><?php echo $_SESSION["composante"]; ?><b></font>
		</td>
	</tr>
	<?php
		// Modification d'une formation existante : choix de la formation à modifier
		if(!isset($resultat))
		{
			print("<tr>
						<td class='td-gauche fond_menu2'>
							<font class='Texte_menu2'><strong>Choix de la formation : </strong></font>
						</td>
						<td class='td-droite fond_menu'>\n");

			$result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_propspec_id_spec,
														$_DBC_specs_nom_court, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_mentions_nom,
														$_DBC_propspec_manuelle
												FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_specs_mention_id=$_DBC_mentions_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_active='1'
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom_court");

			$rows=db_num_rows($result);

			if($rows)
			{
				print("<select size='1' name='formation'>
							<option value=''></option>\n");

				$old_annee="-1";
				$old_mention="-1";

				for($i=0; $i<$rows; $i++)
				{
					list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom, $form_mention, $form_finalite, $form_mention_nom, $manuelle)=db_fetch_row($result, $i);

					$finalite_txt=$tab_finalite[$form_finalite];

					if($form_annee_id!=$old_annee)
					{
						if($i!=0)
							print("</optgroup>
										<option value='' label='' disabled></option>\n");

						if($form_annee_nom=="")
							$form_annee_nom="Années particulières";

						print("<optgroup label='$form_annee_nom'>\n");

						$new_sep_annee=1;

						$old_annee=$form_annee_id;
						$old_mention="-1";
					}
					else
						$new_sep_annee=0;

					if($form_mention!=$old_mention)
					{
						if(!$new_sep_annee)
							print("</optgroup>
										<option value='' label='' disabled></option>\n");

						$val=htmlspecialchars($form_mention_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);

						print("<optgroup label='- $val'>\n");

						$old_mention=$form_mention;
					}

					$manuelle_txt=$manuelle ? "(M) " : "";

					print("<option value='$form_propspec_id' label=\"$manuelle_txt$form_spec_nom $finalite_txt\">$manuelle_txt$form_spec_nom $finalite_txt</option>\n");
				}

				print("</select>\n");
			}
			else
			{
				print("<font class='Texte_important_menu'>
							<strong>Aucune mention / spécialité n'a encore été définie pour cette entité.</strong>
						 </font>\n");

				$no_next=1;
			}

			db_free_result($result);

			print("</td>
					</tr>
					</table>

					<script language='javascript'>
						document.form1.formation.focus()
					</script>

					<div class='centered_icons_box'>
						<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>\n");

				if(!isset($no_next))
					print("<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant' value='Suivant'>\n");

				print("</form>
					</div>\n");
		}
		elseif(isset($resultat) && $resultat==1)
		{
		?>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Formation sélectionnée :</b></font>
		</td>
		<td class='td-droite fond_menu2'>
			<font class='Texte_menu2'><b><?php echo $nom_formation; ?></b></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style='white-space:normal;'>
			<font class='Texte_menu2'>
				<b>Information(s) à transmettre aux candidats :</b>
		</font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				<textarea name='information' class='textArea' cols='80' rows='10'><?php print("$current_info"); ?></textarea>
				<br>
				<br><i>- Ces informations seront affichées dès qu'un candidat sélectionne cette formation</i>
				<br><i>- Préférez des informations simples et courtes afin qu'elles ne soient pas ignorées par les candidats</i>
			</font>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<?php
			if(isset($_GET["succes"]))
				print("<a href='index.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/rew_32x32_fond.png' alt='Retour' border='0'></a>\n");
				
			if(isset($_SESSION["adresse_retour"]) && !empty($_SESSION["adresse_retour"]))
				print("<a href='$_SESSION[adresse_retour]' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");				
			elseif(!isset($_SESSION["ajout"]))
				print("<a href='info_formations.php?e=1' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");

			if(!isset($_GET["succes"]))
				print("<a href='index.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>\n");
		?>
		<input class='icone' type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
		</form>
	</div>

	<script language="javascript">
		document.form1.information.focus()
	</script>

	<?php
		}
		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
