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

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if($_SESSION['niveau']!=$__LVL_ADMIN)
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	$dbr=db_connect();
	
	if(isset($_GET["succes"]) && ctype_digit($_GET["succes"]))
		$succes=$_GET["succes"];

	if(isset($_POST["modifier"]) || isset($_POST["modifier_x"]))
		$comp_id=isset($_POST["comp_id"]) ? $_POST["comp_id"] : "";
	elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$comp_id=isset($_POST["comp_id"]) ? $_POST["comp_id"] : "";

		$univ_lettre_code_apogee=mb_strtoupper(trim($_POST['lettre_code_apogee']));
		$prefixe_opi=mb_strtoupper(trim($_POST["prefixe_opi"]));

      $message_primo=trim($_POST['message_primo']);
      $message_lp=trim($_POST['message_lp']);
      $message_reserve=trim($_POST['message_reserve']);
      
      $adr_conditions=trim($_POST['adr_conditions']);
      $adr_primo=trim($_POST['adr_primo']);
      $adr_reins=trim($_POST['adr_reins']);
      $adr_rdv=trim($_POST['adr_rdv']);

      if(isset($_POST["all_comp_config"]) && ctype_digit($_POST["all_comp_config"])) // Mise à jour de la config (hors messages) pour toutes les composantes de cette université
      {
         $univ_id=$_POST["all_comp_config"];
         
         $res_composantes=db_query($dbr, "SELECT $_DBC_composantes_id FROM $_DB_composantes WHERE $_DBC_composantes_univ_id='$univ_id'");
         
         $nb_composantes=db_num_rows($res_composantes);
         
         for($i=0; $i<$nb_composantes; $i++)
         {
            list($composante_id)=db_fetch_row($res_composantes, $i);
   
            // La configuration existe pour cette composante : mise à jour         
            if(db_num_rows(db_query($dbr,"SELECT * FROM $_module_apogee_DB_config WHERE $_module_apogee_DBC_config_comp_id='$composante_id'")))
               db_query($dbr,"UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_code='$univ_lettre_code_apogee',
                                                                   $_module_apogee_DBU_config_prefixe_opi='$prefixe_opi',
                                                                   $_module_apogee_DBU_config_adr_primo='$adr_primo',
                                                                   $_module_apogee_DBU_config_adr_reins='$adr_reins',
                                                                   $_module_apogee_DBU_config_adr_rdv='$adr_rdv',
                                                                   $_module_apogee_DBU_config_adr_conditions='$adr_conditions'
                              WHERE $_module_apogee_DBU_config_comp_id='$composante_id'");
            else // insertion complète
               db_query($dbr,"INSERT INTO $_module_apogee_DB_config VALUES ('$composante_id','$univ_lettre_code_apogee','$prefixe_opi','$message_primo','$message_lp','$message_reserve','$adr_primo','$adr_reins','$adr_rdv','$adr_conditions')");

            write_evt($dbr, $__EVT_ID_G_ADMIN, "MOD_APOGEE : modification de la configuration - Composante id#$composante_id", "", $composante_id);
         }

         db_free_result($res_composantes);
      }
      
      if(isset($_POST["all_comp_msg"]) && ctype_digit($_POST["all_comp_msg"])) // Mise à jour des messages (uniquement) pour toutes les composantes de cette université
      {
         $univ_id=$_POST["all_comp_msg"];
         
         $res_composantes=db_query($dbr, "SELECT $_DBC_composantes_id FROM $_DB_composantes WHERE $_DBC_composantes_univ_id='$univ_id'");
         
         $nb_composantes=db_num_rows($res_composantes);
         
         for($i=0; $i<$nb_composantes; $i++)
         {
            list($composante_id)=db_fetch_row($res_composantes, $i);
   
            // La configuration existe pour cette composante : mise à jour         
            if(db_num_rows(db_query($dbr,"SELECT * FROM $_module_apogee_DB_config WHERE $_module_apogee_DBC_config_comp_id='$composante_id'")))
               db_query($dbr,"UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_message_primo='$message_primo',
                                                                   $_module_apogee_DBU_config_message_lp='$message_lp',
                                                                   $_module_apogee_DBU_config_message_reserve='$message_reserve'
                              WHERE $_module_apogee_DBU_config_comp_id='$composante_id'");
            else // insertion complète
               db_query($dbr,"INSERT INTO $_module_apogee_DB_config VALUES ('$composante_id','$univ_lettre_code_apogee','$prefixe_opi','$message_primo','$message_lp','$message_reserve','$adr_primo','$adr_reins','$adr_rdv','$adr_conditions')");

            write_evt($dbr, $__EVT_ID_G_ADMIN, "MOD_APOGEE : modification des messages - Composante id#$composante_id", "", $composante_id);
         }

         db_free_result($res_composantes);
      }
      
      if(!isset($_POST["all_comp_config"]) && !isset($_POST["all_comp_msg"])) // Modification uniquement pour cette composante
      {
		   if(db_num_rows(db_query($dbr,"SELECT * FROM $_module_apogee_DB_config WHERE $_module_apogee_DBC_config_comp_id='$comp_id'")))
			   db_query($dbr,"UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_code='$univ_lettre_code_apogee',
				  																	 $_module_apogee_DBU_config_prefixe_opi='$prefixe_opi',
                                                                $_module_apogee_DBU_config_message_primo='$message_primo',
                                                                $_module_apogee_DBU_config_message_lp='$message_lp',
                                                                $_module_apogee_DBU_config_message_reserve='$message_reserve',
                                                                $_module_apogee_DBU_config_adr_primo='$adr_primo',
                                                                $_module_apogee_DBU_config_adr_reins='$adr_reins',
                                                                $_module_apogee_DBU_config_adr_rdv='$adr_rdv',
                                                                $_module_apogee_DBU_config_adr_conditions='$adr_conditions'
									WHERE $_module_apogee_DBU_config_comp_id='$comp_id'");
         else
			   db_query($dbr,"INSERT INTO $_module_apogee_DB_config VALUES ('$comp_id','$univ_lettre_code_apogee','$prefixe_opi','$message_primo','$message_lp','$message_reserve','$adr_primo','$adr_reins','$adr_rdv','$adr_conditions')");

         write_evt($dbr, $__EVT_ID_G_ADMIN, "MOD_APOGEE : modification de la configuration - Composante id#$comp_id", "", $comp_id);
      }		

		// Si la composante modifiée est celle courante, on met à jour les variables de session de l'utilisateur

		if($comp_id==$_SESSION["comp_id"])
   		$_SESSION["comp_lettre_apogee"]=$univ_lettre_code_apogee;

		db_close($dbr);

		header("Location:$php_self?succes=1");
		exit;
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Configuration du module Apogée", "edit_32x32_fond.png", 15, "L");

		if(isset($succes))
			message("Configuration mise à jour avec succès.", $__SUCCES);

		print("<form name=\"form1\" method=\"POST\" action=\"$php_self\">\n");

		// choix de la composante à modifier
		if(!isset($comp_id))
		{
         message("Pour appliquer la même configuration à toutes les composantes d'une université, sélectionnez la composante modèle, cochez la case \"Appliquer à toutes les composantes\" puis validez le formulaire.
                  <br>S'il y a plusieurs universités sur l'interface, répétez la procédure avec une composante de chacune d'elles.", $__INFO);      
		
         $result=db_query($dbr, "SELECT $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_univ_id, $_DBC_universites_nom
                                       FROM $_DB_composantes, $_DB_universites
                                    WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
                                    ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");
			
			$rows=db_num_rows($result);
			
			if($rows)
			{
				print("<table cellpadding='4' cellspacing='0' border='0' align='center'>
						<tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><strong>Sélection de la composante : </strong></font>
							</td>
							<td class='td-droite fond_menu'>
								<select name='comp_id' size='1'>
									<option value=''></option>\n");

				for($i=0; $i<$rows; $i++)
				{
				   list($comp_id, $comp_nom, $comp_univ_id, $univ_nom)=db_fetch_row($result,$i);

               if($comp_univ_id!=$old_univ)
               {
                  if($i!=0)
                     print("</optgroup>
                            <option value='' label='' disabled></option>\n");

                  print("<optgroup label='".htmlspecialchars(stripslashes($univ_nom), ENT_QUOTES, $default_htmlspecialchars_encoding)."'>\n");
               }

               $value=htmlspecialchars($comp_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);

               $selected=(isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==$comp_id) ? "selected='1'" : "";

               print("<option value='$comp_id' label=\"$value\" $selected>$value</option>\n");

               $old_univ=$comp_univ_id;
				}

				db_free_result($result);

				print("		</optgroup>
							</select>
							</td>
						</tr>
						</table>

						<div class='centered_icons_box'>
							<a href='$__GESTION_DIR/admin/index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
							<input type='image' class='icone' src='$__ICON_DIR/edit_32x32_fond.png' alt='Modifier' name='modifier' value='Modifier' title='[Modifier]'>
							</form>
						</div>

						<script language='javascript'>
							document.form1.comp_id.focus()
						</script>\n");
			}
			else
			{
				message("<center>
								Aucune composante n'est actuellement configurée.
								<br>Vous devez créer une composante avant de pouvoir configurer le module.
							</center>\n", $__INFO);

				print("<div class='centered_icons_box'>
							<a href='$__GESTION_DIR/admin/index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
							</form>
						 </div>\n");
			}
		}
		else
		{
         message("Les codes renseignés sur cette page sont <strong>uniquement</strong> utilisés pour générer :
                  <br>- les <strong>Codes OPI</strong> des candidats admis pour les transferts vers APOGEE (inscriptions intégrales en ligne).
                  <br>- les <strong>Numéros d'autorisation d'inscription</strong> (en présentiel, avec retrait de dossier) pour les candidats admis (macro <strong>%CODE%</strong>)
                  <br><br><strong>Ces codes et messages peuvent être spécifiques à chaque composante. Si un message est vide, il ne sera pas envoyé à la catégorie de candidats correspondante !</strong>", $__INFO);

			$result=db_query($dbr,"SELECT $_DBC_composantes_nom, $_DBC_composantes_univ_id FROM $_DB_composantes WHERE $_DBC_composantes_id='$comp_id'");

			list($composante_nom, $universite_id)=db_fetch_row($result,0);

			db_free_result($result);

			// Code existant ?

			$res_code=db_query($dbr, "SELECT $_module_apogee_DBC_config_code, $_module_apogee_DBC_config_prefixe_opi, $_module_apogee_DBC_config_message_primo,
                                          $_module_apogee_DBC_config_message_lp,  $_module_apogee_DBC_config_message_reserve, 
                                          $_module_apogee_DBC_config_adr_primo, $_module_apogee_DBC_config_adr_reins, $_module_apogee_DBC_config_adr_rdv, 
                                          $_module_apogee_DBC_config_adr_conditions
                                   FROM $_module_apogee_DB_config
											  WHERE $_module_apogee_DBC_config_comp_id='$comp_id'");

			if(db_num_rows($res_code))
				list($code_apogee, $prefixe_opi, $message_primo, $message_lp, $message_reserve, $adr_primo, $adr_reins, $adr_rdv, $adr_conditions)=db_fetch_row($res_code, 0);
			else
				$code_apogee=$prefixe_opi=$message_primo=$message_lp=$message_reserve=$adr_primo=$adr_reins=$adr_rdv=$adr_conditions="";

			print("<input type='hidden' name='comp_id' value='$comp_id'>\n");
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Composante sélectionnée :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
			   <strong>
   				<?php echo htmlspecialchars(stripslashes($composante_nom), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>
   			</strong>
			</font>
			<br><input type='checkbox' name='all_comp_config' value='<?php echo $universite_id; ?>' style='vertical-align:bottom;'>
         <font class='Texte_menu'>
            Cocher pour appliquer la configuration (hors messages par défaut) à toutes les composantes de cette université
         </font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Préfixe des codes OPI (Primo-Entrants) :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='prefixe_opi' value='<?php if(isset($prefixe_opi)) echo htmlspecialchars(stripslashes($prefixe_opi), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='3' size='16'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Première lettre permettant la génération<br>des numéros d'autorisation (prise de RDV) :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='lettre_code_apogee' value='<?php if(isset($code_apogee)) echo htmlspecialchars(stripslashes($code_apogee), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='16' size='16'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Site pour les conditions générales d'utilisations (%ADR_COND%) :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='adr_conditions' value='<?php if(isset($adr_conditions)) echo htmlspecialchars($adr_conditions, ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='256' size='60'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Site pour les étudiants Primo Entrants (%ADR_PRIMO%) :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='adr_primo' value='<?php if(isset($adr_primo)) echo htmlspecialchars($adr_primo, ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='256' size='60'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Site pour les Réinscription (%ADR_REINS%) :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='adr_reins' value='<?php if(isset($adr_reins)) echo htmlspecialchars($adr_reins, ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='256' size='60'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Site pour les inscriptions sur rendez-vous (%ADR_RDV%) :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='adr_rdv' value='<?php if(isset($adr_rdv)) echo htmlspecialchars($adr_rdv, ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='256' size='60'>
		</td>
	</tr>
	<tr> 
      <td class='td-gauche fond_page' colspan='2' height='10'></td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' style='padding-top:4px; padding-bottom:4px' colspan='2'>
         <font class='Texte'>
            <strong>Messages par défaut (ces messages peuvent également être propres à chaque formation)</strong>
            <br><input type='checkbox' name='all_comp_msg' value='<?php echo $universite_id; ?>' style='vertical-align:bottom;'>
            <font class='Texte_menu'>
               Cocher pour copier ces messages par défaut à toutes les composantes de cette université (les messages spécifiques aux formations ne seront pas remplacés).
            </font>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Message envoyé à un candidat<br>Primo-entrant <u>pour chaque admission</u> : </b></font>
      </td>
      <td class='td-droite fond_menu'>
         <textarea name='message_primo' cols='100' rows='12'><?php if(isset($message_primo)) echo htmlspecialchars(stripslashes($message_primo), ENT_QUOTES, $default_htmlspecialchars_encoding); ?></textarea>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Message envoyé à un candidat ayant<br>déjà été inscrit (laisser-passer)<br><u>pour chaque admission</u> : </b></font>
      </td>
      <td class='td-droite fond_menu'>
         <textarea name='message_lp' cols='100' rows='12'><?php if(isset($message_lp)) echo htmlspecialchars(stripslashes($message_lp), ENT_QUOTES, $default_htmlspecialchars_encoding); ?></textarea>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Message envoyé à un candidat "admis sous réserve"<br><u>pour chaque admission</u> : </b></font>
      </td>
      <td class='td-droite fond_menu'>
         <textarea name='message_reserve' cols='100' rows='12'><?php if(isset($message_reserve)) echo htmlspecialchars(stripslashes($message_reserve), ENT_QUOTES, $default_htmlspecialchars_encoding); ?></textarea>
      </td>
   </tr>
	</table>

	<div class='centered_icons_box'>
		<?php
			if(isset($succes))
				print("<a href='index.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>");
			else
				print("<a href='$php_self?m=0&a=0' target='_self'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>");
		?>
		<input type='image' class='icone' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' title='[Valider]' name='valider' value='Valider'>
		</form>
	</div>

	<script language="javascript">
		document.form1.lettre_code_apogee.focus()
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
