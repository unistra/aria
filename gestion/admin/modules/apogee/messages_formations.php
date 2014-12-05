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

   unset($_SESSION["ajout"]);
   unset($_SESSION["msg_id"]);

	$dbr=db_connect();
	
	if(isset($_GET["succes"]) && ctype_digit($_GET["succes"]))
		$succes=$_GET["succes"];

	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
      $result=db_query($dbr, "SELECT $_DBC_propspec_id FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'");
      
      $rows=db_num_rows($result);
      
      if($rows)
      {
         for($i=0; $i<$rows; $i++)
         {
            list($propspec_id)=db_fetch_row($result, $i);
            
            foreach($_MOD_APOGEE_MSG_TYPES as $current_message_type => $current_type_nom)
            {
               db_query($dbr, "DELETE FROM $_module_apogee_DB_messages_formations 
                               WHERE $_module_apogee_DBC_messages_formations_propspec_id='$propspec_id'
                               AND $_module_apogee_DBC_messages_formations_msg_id IN (SELECT $_module_apogee_DBC_messages_msg_id FROM $_module_apogee_DB_messages 
                                                                                            WHERE $_module_apogee_DBC_messages_type='$current_message_type')");
                                                                                            
               if(array_key_exists($propspec_id, $_POST["propspec_msg"]) && array_key_exists($current_message_type, $_POST["propspec_msg"]["$propspec_id"]))
               {
                  if($_POST["propspec_msg"]["$propspec_id"]["$current_message_type"]!="--")
                     db_query($dbr, "INSERT INTO $_module_apogee_DB_messages_formations VALUES ('$propspec_id', '".$_POST["propspec_msg"]["$propspec_id"]["$current_message_type"]."')");
               }
            }         
         }
      }
      
      db_free_result($result);
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
      include "include/menu_editeur_messages.php";

		titre_page_icone("Module Apogée : messages spécifiques aux formations", "edit_32x32_fond.png", 15, "L");

		if(isset($succes))
			message("Messages mis à jour avec succès.", $__SUCCES);

		print("<form name=\"form1\" method=\"POST\" action=\"$php_self\">\n");
	?>

	<table align='center'>
	<tr>
		<td align='left' style='padding-left:20px;'>
			<font class='Texte'><b>Formation(s) : </b></font>
			<select size="1" name="filtre_apogee_propspec">
				<option value="-1">Montrer toutes les formations</option>
				<option value="-1" disabled='1'></option>
				<?php
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
						$old_annee="-1";
						$old_mention="-1";

						for($i=0; $i<$rows; $i++)
						{
							list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom, $form_mention, $form_finalite,
									$form_mention_nom, $form_manuelle)=db_fetch_row($result, $i);

							if($form_annee_id!=$old_annee)
							{
								if($i!=0)
									print("</optgroup>
												<option value='' label='' disabled></option>\n");

								$annee_nom=$form_annee_nom=="" ? "Années particulières" : $form_annee_nom;

								print("<optgroup label='$annee_nom'>\n");

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

							$manuelle_txt=$form_manuelle ? "(M) " : "";

							if($form_annee_nom=="")
								print("<option value='$form_propspec_id' label=\"$manuelle_txt$form_spec_nom $tab_finalite[$form_finalite]\">$manuelle_txt$form_spec_nom  $tab_finalite[$form_finalite]</option>\n");
							else
								print("<option value='$form_propspec_id' label=\"$manuelle_txt$form_annee_nom - $form_spec_nom  $tab_finalite[$form_finalite]\">$manuelle_txt$form_annee_nom - $form_spec_nom  $tab_finalite[$form_finalite]</option>\n");
						}
					}

					db_free_result($result);
				?>
			</select>
			&nbsp;&nbsp;<input type='submit' name='valider_filtre' value='Valider'>&nbsp;&nbsp;&nbsp;<?php print("$filtre_statut"); ?>
		</td>
	</tr>
	</table>

	<br clear="all">

	<?php
		if(isset($filtre) && $filtre==1)
		{
			$result=db_query($dbr, "SELECT $_module_apogee_DBC_messages_msg_id, $_module_apogee_DBC_messages_nom, $_module_apogee_DBC_messages_contenu, 
			                               $_DBC_module_apogee_DBC_messages_type
												FROM $_module_apogee_DB_messages, $_module_apogee_DB_messages_formations
											WHERE $_module_apogee_DBC_messages_msg_id=$_module_apogee_DBC_messages_formation_msg_id
											AND $_module_apogee_DBC_messages_comp_id='$_SESSION[comp_id]'
											AND $_module_apogee_DBC_messages_formations_propspec_id='$_SESSION[filtre_apogee_propspec]'
												ORDER BY $_DBC_module_apogee_DBC_messages_type");

			$rows=db_num_rows($result);

			if($rows)
			{
				print("<br>
						 <table class='layout0' width='98%' align='center' style='margin-bottom:30px;'>");

				for($i=0; $i<$rows; $i++)
				{
					list($msg_id, $msg_nom, $msg_contenu, $msg_type)=db_fetch_row($result, $i);

               print("<tr>
                         <td class='td-gauche fond_menu2'>
                            <font class='Texte_menu2'><b>$_MOD_APOGEE_MSG_TYPES[$msg_type]
                         </td>
                         <td class='td-droite fond_menu'>
                            <textarea name='message_lp' cols='100' rows='12'><?php echo htmlspecialchars(stripslashes($msg_contenu), ENT_QUOTES, $default_htmlspecialchars_encoding); ?></textarea>                      
                         </td> 
			   		       <td align='right' width='20'>
				   			   <a href='suppr_msg.php?pid=$_SESSION[filtre_apogee_propspec]&t=$msg_type' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick.png' alt='Supprimer' border='0'></a>
                         </td>
                      </tr>\n");
				}

				print("</table>\n");
			}

			db_free_result($result);
		}
		else // AFFICHAGE DES MESSAGES POUR TOUTES LES FORMATIONS
		{
			unset($_SESSION["filtre_apogee_msg_nom"]);
			
			$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_module_apogee_DBC_messages_msg_id, $_module_apogee_DBU_messages_type
												FROM $_DB_propspec, $_module_apogee_DB_messages, $_module_apogee_DB_messages_formations
											WHERE $_module_apogee_DBC_messages_formations_propspec_id=$_DBC_propspec_id
											AND $_module_apogee_DBC_messages_formations_msg_id=$_module_apogee_DBC_messages_msg_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_active='1'
												ORDER BY $_DBC_propspec_id, $_module_apogee_DBU_messages_type");
												
			$rows=db_num_rows($result);

         $array_formations_msg=array();

			if($rows)
			{
			   // Première boucle : on liste et on conserve les messages rattachés à chaque formation
				for($i=0; $i<$rows; $i++)
				{
					list($propspec_id, $msg_id, $msg_type)=db_fetch_row($result, $i);

               if(!array_key_exists($propspec_id, $array_formations_msg))
                  $array_formations_msg[$propspec_id]=array("messages" => array());
                  
               $array_formations_msg[$propspec_id]["messages"][$msg_type]=$msg_id;
            }
         }
         
         // Deuxième boucle : on liste les messages existants (id, type, titre)
         $result=db_query($dbr,"SELECT $_module_apogee_DBC_messages_msg_id, $_module_apogee_DBC_messages_nom, $_module_apogee_DBC_messages_type
												FROM $_module_apogee_DB_messages
										  ORDER BY $_module_apogee_DBC_messages_type, $_module_apogee_DBC_messages_nom");
										  
         $rows=db_num_rows($result);

         $array_messages=array();
			
			if($rows)
			{  
            for($i=0; $i<$rows; $i++)
            {
					list($msg_id, $msg_nom, $msg_type)=db_fetch_row($result, $i);
					
					if(!array_key_exists($msg_type, $array_messages))
					   $array_messages["$msg_type"]=array("$msg_id" => "$msg_nom");
               else
					   $array_messages["$msg_type"]["$msg_id"]="$msg_nom";
            }
         }
         
         db_free_result($result);
         
         // Troisième boucle : on liste toutes les formations. Pour chacune on proposera les messages disponibles, pour chaque type
         
         $result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_propspec_annee,$_DBC_annees_annee, $_DBC_specs_nom_court,
													$_DBC_propspec_finalite, $_DBC_specs_mention_id, $_DBC_mentions_nom, $_DBC_propspec_manuelle
												FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_specs_mention_id=$_DBC_mentions_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_active='1'
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_specs_nom_court");
												
			$rows=db_num_rows($result);

			$old_propspec_id="--"; // on initialise à n'importe quoi (sauf vide)
			$old_annee_id="--"; // idem
			$old_mention="--"; // idem
			$j=0; 
			
			if($rows)
			{  
            for($i=0; $i<$rows; $i++)
				{
					list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $mention_nom, $manuelle)=db_fetch_row($result, $i);
						  
					if($i)
					{
						print("<tr>
									<td class='fond_menu2' height='10' colspan='2'></td>
								</tr>\n");
					}

					$nom_finalite=$tab_finalite[$finalite];

					if($annee_id!=$old_annee_id)
					{
						$annee=($annee=="") ? "Années particulières" : $annee;

						// Nombre de mentions dans cette année (pour l'affichage)
						$res_mentions=db_query($dbr, "SELECT count(distinct($_DBC_specs_mention_id)) FROM $_DB_specs
																WHERE $_DBC_specs_id IN
																	(SELECT distinct($_DBC_propspec_id_spec) FROM $_DB_propspec
																		WHERE $_DBC_propspec_annee='$annee_id'
																		AND $_DBC_propspec_active='1'
																		AND $_DBC_propspec_comp_id='$_SESSION[comp_id]')");

						list($count_mentions)=db_fetch_row($res_mentions, 0);

						$count_mentions=($count_mentions=="") ? 0 : $count_mentions;

						if($count_mentions>1)
						{
							$colspan_annee=2;
							$colwidth="50%";
						}
						else
						{
							$colspan_annee=1;
							$colwidth="100%";
						}

						db_free_result($res_mentions);
						
						if($i) // Le premier résultat du tableau est particulier (i=0)
						{
							print("</table>
									</td>
								</tr>
							   </table>\n");
						}

						print("<table align='center' width='90%' style='margin-bottom:20px'>
								 <tr>
									<td class='fond_menu2' colspan='$colspan_annee' style='padding:4px 20px 4px 20px;'>
										<font class='Texte_menu2'><b>$annee</b></font>
									</td>
								 </tr>
								 <tr>
									<td class='fond_menu2' width='$colwidth' valign='top'>
										<table width='100%'>
										<tr>
											<td colspan='3' height='20' align='center'>
												<font class='Texte_menu2'><b>$mention_nom</b></font>
											</td>
										</tr>\n");

						$old_mention="$mention";
						$old_annee_id=$annee_id;
						$j=0;
					}

					if($old_mention!=$mention)
					{
						print("</table>
								</td>\n");
								
						if($j)
							print("</tr>
									 <tr>\n");

						print("<td class='fond_menu2' width='$colwidth' valign='top'>
									<table width='100%'>
									<tr>
										<td colspan='2' height='20' align='center'>
											<font class='Texte_menu2'><b>$mention_nom</b></font>
										</td>
									</tr>\n");

						$j=$j ? 0 : 1;

						$old_mention=$mention;
					}
					else
						$old_mention=$mention;

					$manuelle_txt=$manuelle ? "- Gestion manuelle" : "";

               print("<tr>
							    <td colspan='2' class='td-gauche fond_menu'>
								    <a href='messages_formations?pid=$propspec_id' class='lien_menu_gauche'><b>$spec_nom $nom_finalite</b></a>
								 </td>
							 </tr>\n");

               foreach($_MOD_APOGEE_MSG_TYPES as $message_type => $message_type_nom)
               {
                  print("<tr>
							       <td class='fond_menu' style='padding:2px 5px 2px 5px; width:60px; white-space:nowrap;'>
							         <font class='Texte_menu'>
   							         <strong>$message_type_nom</strong>
                              </font>
                           </td>
                           <td class='fond_menu' style='padding:2px 5px 2px 5px;'>
                              <select name='propspec_msg[$propspec_id][$message_type]'>
                                 <option value='--'>Message par défaut</option>\n");
                  
                  if(array_key_exists($message_type, $array_messages))
                  {
                     foreach($array_messages[$message_type] as $current_message_id => $current_message_nom)
                     {
                        $selected=array_key_exists($propspec_id, $array_formations_msg) && array_key_exists($message_type, $array_formations_msg[$propspec_id]["messages"]) && $array_formations_msg[$propspec_id]["messages"]["$message_type"]==$current_message_id ? "selected='1'" : "";
                     
                        print("<option value='$current_message_id' $selected>".htmlspecialchars(stripslashes($current_message_nom), ENT_QUOTES, $default_htmlspecialchars_encoding)."</option>\n");
                     }
                  }

                  print("</select>
						      </td>
                     </tr>\n");
               }
				}

				print("<tr>
							<td class='fond_menu' height='10' colspan='2'></td>
						</tr>
						</table>
					</td>\n");

				if(!$j && $colspan_annee>1)
					print("<td class='fond_menu'></td>");

				print("</tr>
						 </table>\n");
			}

			db_free_result($result);
		}

		db_close($dbr);
	?>

	</table>
	<div class='centered_icons_box'>
		<?php
			if(isset($succes))
				print("<a href='../../index.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>");
			else
				print("<a href='$php_self' target='_self'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>");
		?>
		<input type='image' class='icone' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' title='[Valider]' name='valider' value='Valider'>
		</form>
	</div>
</div>
<?php
	pied_de_page();
?>

</body></html>
