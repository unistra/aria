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

	include "../../../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

   // includes spécifiques au module
	include "include/db.php"; // db.php appellera également update_db.php pour la mise à jour du schéma
	include "include/vars.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

   if($_SESSION['niveau']!=$__LVL_ADMIN)
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	$dbr=db_connect();
	
	if(isset($_GET["succes"]))
		$succes=$_GET["succes"];

	if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
	{
		$msg_id=$_POST["msg_id"];
		$resultat=1;
	}
	elseif((isset($_POST["valider"]) || isset($_POST["valider_x"]))
		  && isset($_POST['msg_nom']) && isset($_POST['msg_type']) && isset($_POST['msg_contenu']))
	{
		$dbr=db_connect();

		$msg_nom=trim($_POST['msg_nom']);
		$msg_type=trim($_POST['msg_type']);
		$msg_contenu=$_POST['msg_contenu'];

		if(!isset($_SESSION["ajout"]) && isset($_POST["msg_id"]) && $_POST["msg_id"]!="") // Modification
		{
			$new_id=$_POST["msg_id"];

			// unicité
			if(db_num_rows(db_query($dbr,"SELECT * FROM $_module_apogee_DB_messages
													WHERE ($_module_apogee_DBC_messages_nom ILIKE '$msg_nom'
														OR ($_module_apogee_DBC_messages_contenu!='' AND $_module_apogee_DBC_messages_contenu ILIKE '$msg_contenu'))
													AND $_module_apogee_DBC_messages_msg_id!='$new_id'
													AND $_module_apogee_DBC_messages_comp_id='$_SESSION[comp_id]'")))
				$msg_existe="1";
		}
		elseif(isset($_SESSION["ajout"])
				 && db_num_rows(db_query($dbr,"SELECT * FROM $_module_apogee_DB_messages
															WHERE ($_module_apogee_DBC_messages_nom ILIKE '$msg_nom'
																OR ($_module_apogee_DBC_messages_contenu!='' AND $_module_apogee_DBC_messages_contenu ILIKE '$msg_contenu'))
															AND $_module_apogee_DBC_messages_comp_id='$_SESSION[comp_id]'")))
			$msg_existe="1";

		// vérification des champs
		if($msg_nom=="")
			$nom_vide=1;

		if($msg_contenu=="" && $msg_contenu=="")
			$contenu_vide=1;

		if((isset($new_id) && $new_id!="") || isset($_SESSION["ajout"]))
		{
			if(!isset($msg_existe) && !isset($nom_vide) && !isset($contenu_vide)) // on peut poursuivre
			{
				// Modification
				if(!isset($_SESSION["ajout"]) && isset($new_id))
					db_query($dbr,"UPDATE $_module_apogee_DB_messages SET	$_module_apogee_DBU_messages_nom='$msg_nom',
                                                                     $_module_apogee_DBU_messages_contenu='$msg_contenu',
                                                                     $_module_apogee_DBU_messages_type='$msg_type'
										WHERE $_module_apogee_DBU_messages_msg_id='$new_id'");
				else
					$new_id=db_locked_query($dbr, $_module_apogee_DB_messages, "INSERT INTO $_module_apogee_DB_messages VALUES ('##NEW_ID##', '$_SESSION[comp_id]', '$msg_nom', '$msg_contenu', '$msg_type')");

				db_close($dbr);

				header("Location:$php_self?succes=1");
				exit;
			}
			else
				db_close($dbr);
		}
		else
			$erreur_selection=1;
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		if(isset($_GET["a"]) || isset($_SESSION["ajout"]))
		{
			$_SESSION["ajout"]=1;
			titre_page_icone("Créer un message", "add_32x32_fond.png", 15, "L");
			$message="Après avoir créé cet élément, n'oubliez pas de le rattacher aux formations adéquates.";
		}
		else
		{
			titre_page_icone("Modifier un message existant", "edit_32x32_fond.png", 15, "L");
			$message="Attention : la modification affectera toutes les formations rattachées à ce message.";
		}

		if(isset($nom_vide))
			message("Erreur : le champ 'Nom' ne doit pas être vide.", $__ERREUR);

		if(isset($contenu_vides))
			message("Erreur : le contenu du message ne doit pas être vide.", $__ERREUR);

		if(isset($msg_existe))
			message("Erreur : ce nom est déjà utilisé par un autre message.", $__ERREUR);

		if(isset($succes))
		{
			if(!isset($_SESSION["ajout"]))
				message("Le message a été modifié avec succès.", $__SUCCES);
			else
				message("Le massage a été créé avec succès.", $__SUCCES);
		}

		message("$message", $__INFO);

		$dbr=db_connect();

		if(!isset($resultat) && !isset($_GET["a"]) && !isset($_SESSION["ajout"])) // choix de l'élément à modifier
		{
			$result=db_query($dbr, "SELECT $_module_apogee_DBC_messages_msg_id, $_module_apogee_DBC_messages_nom, $_module_apogee_DBC_messages_type
												FROM $_module_apogee_DB_messages
											WHERE $_module_apogee_DBC_messages_comp_id='$_SESSION[comp_id]'
												ORDER BY $_module_apogee_DBC_messages_type ASC");

			$rows=db_num_rows($result);

			print("<form action='$php_self' method='POST' name='form1'>
					 <div class='centered_box'>
						<font class='Texte'>Message à modifier : </font>
						<select name='msg_id' size='1'>\n");

			$old_univ="";
			$old_type="";

			for($i=0; $i<$rows; $i++)
			{
				list($msg_id, $msg_nom, $msg_type)=db_fetch_row($result,$i);

				$value=htmlspecialchars($msg_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);

            if($msg_type!=$old_type)
            {
               if($i)
                  print("</optgroup>\n");
					
				   print("<optgroup label='$_MOD_APOGEE_MSG_TYPES[$msg_type]'>\n");
					
				   $old_type=$msg_type;
            }
            
				print("<option value='$msg_id' label=\"$value\">$value</option>\n");
			}

			db_free_result($result);

			print("</optgroup>
				    </select>
					</div>
					<div class='centered_icons_box'>\n");

			if(isset($succes))
				print("<a href='messages_formations.php' target='_self'><img src='$__ICON_DIR/rew_32x32_fond.png' alt='Retour' border='0'></a>\n");
			else
				print("<a href='messages_formations.php' target='_self'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>\n");

			print("<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant' value='Suivant'>
					</form>
					</div>\n");

			if(isset($erreur_selection))
				message("Erreur de sélection du message", $__ERREUR);
		}
		else // Message choisi, on récupère les infos actuelles
		{
			if(isset($_GET["a"]) || isset($_SESSION["ajout"]))
			{
				if(!isset($msg_nom)) // un seul test devrait suffire
					$msg_nom=$msg_contenu=$msg_type="";
			}
			else
			{
				$result=db_query($dbr,"SELECT $_module_apogee_DBC_messages_nom, $_module_apogee_DBC_messages_contenu, $_module_apogee_DBC_messages_type
													FROM $_module_apogee_DB_messages
												WHERE $_module_apogee_DBC_messages_msg_id='$msg_id'");

				list($msg_nom, $msg_contenu, $msg_type)=db_fetch_row($result,0);

				db_free_result($result);
			}

			print("<form name='form1' enctype='multipart/form-data' method='POST' action='$php_self'>\n");

			if(isset($msg_id))
				print("<input type='hidden' name='msg_id' value='$msg_id'>\n");
	?>
		<table align='center'>
		<tr>
			<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
				<font class='Texte_menu2'>
					<b>&#8226;&nbsp;&nbsp;Informations</b>
				</font>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Nom du message :</b></font>
			</td>
			<td class='td-droite fond_menu'>
				<input type='text' name='msg_nom' value='<?php if(isset($msg_nom)) echo htmlspecialchars(stripslashes($msg_nom), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='196' size='70'>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Type de message (dépend de la catégorie de candidat) :</b></font>
			</td>
			<td class='td-droite fond_menu'>
			   <select name='msg_type' size='1'>
			      <option value='<?php echo "$_MOD_APOGEE_MSG_PRIMO"."'"; if(isset($msg_type) && $msg_type==$_MOD_APOGEE_MSG_PRIMO) echo "selected='1'"; ?>>Primo-Entrants</option>
			      <option value='<?php echo "$_MOD_APOGEE_MSG_REINS"."'"; if(isset($msg_type) && $msg_type==$_MOD_APOGEE_MSG_REINS) echo "selected='1'"; ?>>Réinscriptions</option>
			      <option value='<?php echo "$_MOD_APOGEE_MSG_RESERVE"."'"; if(isset($msg_type) && $msg_type==$_MOD_APOGEE_MSG_RESERVE) echo "selected='1'"; ?>>Admis Sous Réserve</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Contenu du message</b></font>
			</td>
			<td class='td-droite fond_menu'>
				<textarea name='msg_contenu' rows='20' cols='80'><?php
					if(isset($msg_contenu)) echo htmlspecialchars(stripslashes($msg_contenu), ENT_QUOTES, $default_htmlspecialchars_encoding);
				?></textarea>
			</td>
		</tr>
		</table>

		<div class='centered_icons_box'>
			<?php
				if(!isset($_SESSION["ajout"]))
					print("<a href='gestion_message.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");
				elseif(isset($_GET["succes"]))
					print("<a href='messages_formations.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");
					
				if(!isset($succes))
					print("<a href='messages_formations.php' target='_self'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>\n");
			?>

			<input type='image' class='icone' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='valider' value='Valider'>
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
<script language="javascript">
	document.form1.msg_id.focus()
</script>

</body></html>
