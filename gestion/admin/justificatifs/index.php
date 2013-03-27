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

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	unset($_SESSION["cbo"]);
	unset($_SESSION["position"]);
	unset($_SESSION["ordre"]);
	unset($_SESSION["ajout"]);
	unset($_SESSION["justif_id"]);
	unset($_SESSION["suppr_justif_id"]);

	$dbr=db_connect();

	// Statut du filtre
	if(array_key_exists("filtre_justif", $_SESSION) && $_SESSION["filtre_justif"]!=-1)
	{
		$filtre=1;
		$filtre_statut="<font class='Texte_important'><b>(sélection activée)</b></font>";
	}
	elseif(isset($_GET["pid"]) && ctype_digit($_GET["pid"])
			 && db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_id='$_GET[pid]'
														AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'")))
	{
		$filtre=1;
		$_SESSION["filtre_justif"]=$_GET["pid"];
		$filtre_statut="<font class='Texte_important'><b>(sélection activée)</b></font>";
	}
	else
	{
		$filtre=0;
		$filtre_statut="<font class='Texte'><b>(sélection désactivée)</b></font>";
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		include "include/menu_editeur_v3.php";

		titre_page_icone("Justificatifs", "edit-select-all_32x32_blanc.png", 1, "L");
	?>
				
	<table cellpadding='8' border='0' align='left' cellspacing='0'>
	<tr>
		<td align='left' style='padding-left:20px;'>
			<font class='Texte'><b>Formation(s) : </b></font>
			<select size="1" name="filtre_justif">
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
			$result=db_query($dbr, "SELECT $_DBC_justifs_id, $_DBC_justifs_intitule, $_DBC_justifs_titre, $_DBC_justifs_texte,
														$_DBC_justifs_jf_nationalite, $_DBC_justifs_jf_ordre
												FROM $_DB_justifs, $_DB_justifs_jf
											WHERE $_DBC_justifs_jf_justif_id=$_DBC_justifs_id
											AND $_DBC_justifs_comp_id='$_SESSION[comp_id]'
											AND $_DBC_justifs_jf_propspec_id='$_SESSION[filtre_justif]'
												ORDER BY $_DBC_justifs_jf_ordre");

			$rows=db_num_rows($result);
			$_SESSION["cbo"]=$nb_elem_corps=$rows;

			if($rows)
			{
				print("<br>
						 <table class='layout0' width='98%' align='center' style='margin-bottom:30px;'>");

				for($i=0; $i<$rows; $i++)
				{
					list($justif_id, $justif_intitule, $justif_titre, $justif_texte, $justif_nationalite, $justif_ordre)=db_fetch_row($result, $i);

					// variable pour les liens (move_element.php, etc)
					if($justif_ordre!=0)
						$j=$justif_ordre-1; // élément précédent

					if($justif_ordre!=($nb_elem_corps-1))
						$k=$justif_ordre+1; // élément suivant

					// nouvelle ligne dans le tableau pour l'élément en cours
					print("<tr>
								<td width='50' style='white-space:nowrap'>
									<input type='radio' style='vertical-align:middle;' name='position_insertion_corps' value='$justif_ordre'>
									<a href='edit_rattachement.php?jid=$justif_id' class='lien2'><img src='$__ICON_DIR/edit_16x16.png' border='0'></a>\n");

					show_up_down3($justif_ordre,$nb_elem_corps);

					// traitement du paragraphe

					$txt=nl2br($justif_texte);

					$texte=empty($txt) ? $justif_titre : "<b>$justif_titre</b><br>$txt";

					$txt_taille='10'; // à ajouter dans les paramètres ?
					$txt_gras=0;
					$txt_italique=0;

					$font_size="font-size:$txt_taille" . "px;";

					$weight=$txt_gras ? "font-weight:bold;" : "";
					$style=$txt_italique ? "font-style:italic;" : "";


					//  style='$font_size $weight $style'

					print("</td>
								<td align='justify' style='padding-bottom:20px'>
									<font class='Texte'>
										$texte
										<br>
									</font>
								</td>
								<td align='right' width='20'>
									<a href='suppr_justif.php?pid=$_SESSION[filtre_justif]&o=$justif_ordre' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick.png' alt='Supprimer' border='0'></a>
								</td>
							</tr>\n");
				}

				print("<tr>
							<td align='left' nowrap='true' colspan='3'>
								<input type='radio' name='position_insertion_corps' value='$justif_ordre'>
							</td>
						</tr>
						</table>\n");
			}

			db_free_result($result);
		}
		else // AFFICHAGE DES JUSTIFICATIFS POUR TOUTES LES FORMATIONS
		{
			unset($_SESSION["filtre_justif_nom"]);
			
			// Détermination des justificatifs communs à toutes les formations existantes de cette composante

         $result=db_query($dbr, "SELECT $_DBC_justifs_jf_justif_id
												FROM $_DB_justifs_jf, $_DB_justifs
											WHERE $_DBC_justifs_jf_justif_id=$_DBC_justifs_id
											AND $_DBC_justifs_jf_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																		   					WHERE $_DBC_propspec_active='1'
																								AND $_DBC_propspec_comp_id='$_SESSION[comp_id]')
												GROUP BY $_DBC_justifs_jf_justif_id
											HAVING count($_DBC_justifs_jf_propspec_id)=(SELECT count(*) FROM $_DB_propspec
																										WHERE $_DBC_propspec_active='1'
																										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]')");

			$rows=db_num_rows($result);

			$liste_communs=array();

			for($i=0; $i<$rows; $i++)
			{
				list($justif_id)=db_fetch_row($result, $i);
				$liste_communs[$justif_id]=$justif_id;
			}
			
			db_free_result($result);

			// Formations n'ayant aucun justificatif rattaché
			$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_propspec_annee,$_DBC_annees_annee, $_DBC_specs_nom_court,
													$_DBC_propspec_finalite, $_DBC_specs_mention_id, $_DBC_mentions_nom, $_DBC_propspec_manuelle
												FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_specs_mention_id=$_DBC_mentions_id
											AND $_DBC_propspec_active='1'
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_id NOT IN (SELECT distinct($_DBC_justifs_jf_propspec_id)
																					FROM $_DB_justifs_jf)
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_specs_nom_court");

			$rows=db_num_rows($result);

			if($rows)
			{
				print("<table border='0' align='center' cellpadding='4'>
						<tr>
						<td align='left'>
							<font class='Texte'>
								<b><u>Formation(s) sans aucun justificatif rattaché : </u></b><br>\n");

				for($i=0; $i<$rows; $i++)
				{
					list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $mention_nom, $manuelle)=db_fetch_row($result, $i);

					$formation_vide=$annee=="" ? "$spec_nom $tab_finalite[$finalite]" : "$annee $spec_nom $tab_finalite[$finalite]";

					print("- $formation_vide<br>\n");
				}

				print("</font>
						</td>
					</tr>
					</table>\n");
			}

			print("<div class='centered_box'>
						<font class='Texte'>
							<b>Les justificatifs <font class='Textevert' style='vertical-align:top;'>en vert</font> sont rattachés à <u>toutes les formations</u></b>
						</font>
					</div>\n");

			db_free_result($result);

			// Sélection des justificatifs rattachés à chaque formation
			$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_propspec_annee,$_DBC_annees_annee, $_DBC_specs_nom_court,
													$_DBC_propspec_finalite, $_DBC_specs_mention_id, $_DBC_mentions_nom, $_DBC_propspec_manuelle,
													$_DBC_justifs_id, $_DBC_justifs_jf_ordre, $_DBC_justifs_intitule, $_DBC_justifs_jf_nationalite
												FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions, $_DB_justifs, $_DB_justifs_jf
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_specs_mention_id=$_DBC_mentions_id
											AND $_DBC_justifs_jf_propspec_id=$_DBC_propspec_id
											AND $_DBC_justifs_jf_justif_id=$_DBC_justifs_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_active='1'
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_specs_nom_court, $_DBC_justifs_jf_ordre");

			$rows=db_num_rows($result);

			$old_propspec_id="--"; // on initialise à n'importe quoi (sauf vide)
			$old_annee_id="--"; // idem
			$old_mention="--"; // idem
			$j=0;

			if($rows)
			{
				for($i=0; $i<$rows; $i++)
				{
					list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $mention_nom, $manuelle,
							$justif_id, $justif_ordre, $justif_intitule, $justif_nationalite)=db_fetch_row($result, $i);

					if($propspec_id!=$old_propspec_id)
					{
						if($i)
						{
							// Fin des justificatifs pour la formation : on liste maintenant les fichiers rattachés
							// Attention, l'identifiant de la formation est $old_propspec_id et non $propspec_id :)
							$res_fichiers=db_query($dbr,"SELECT $_DBC_justifs_fichiers_nom
																		FROM $_DB_justifs_fichiers, $_DB_justifs_ff
																	WHERE $_DBC_justifs_ff_fichier_id=$_DBC_justifs_fichiers_id
																	AND $_DBC_justifs_ff_propspec_id='$old_propspec_id'
																	ORDER BY $_DBC_justifs_fichiers_nom");

							$rows_fichiers=db_num_rows($res_fichiers);

							for($f=0; $f<$rows_fichiers; $f++)
							{
								list($nom_fichier)=db_fetch_row($res_fichiers, $f);

								print("<tr>
											<td class='fond_menu' style='padding-left:5px; padding-right:5px;' colspan='2'></td>
											<td class='fond_menu' style='padding:2px 5px 2px 5px;' colspan='2'>
												<font class='Texte_menu'>&nbsp;&nbsp;+ Fichier \"<i>$nom_fichier</i>\"</font>
											</td>
											</tr>\n");
							}

							db_free_result($res_fichiers);

							print("<tr>
										<td class='fond_menu2' height='10' colspan='4'></td>
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
												<td colspan='4' height='20' align='center'>
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
											<td colspan='4' height='20' align='center'>
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
									<td colspan='4' class='td-gauche fond_menu'>
										<a href='index.php?pid=$propspec_id' class='lien_menu_gauche'><b>$spec_nom $nom_finalite</b></a>
									</td>
								</tr>
								<tr>
									<td class='fond_menu' style='padding-left:5px; padding-right:5px; width:16px;'></td>
									<td class='fond_menu' style='padding-left:5px; padding-right:5px; width:16px;'>\n");

						// Affichage des flèches

						if($i!=($rows-1))
						{
							list($next_propspec_id)=db_fetch_row($result, $i+1);

							if($next_propspec_id==$propspec_id)
								print("<a href='move_element.php?pid=$propspec_id&co=$justif_ordre&dir=1' target='_self'><img src='$__ICON_DIR/down_16x16_menu.png' alt='Descendre' border='0'></a> ");
						}


						print("	</td>
									<td class='fond_menu' style='padding:2px 5px 2px 5px;'>\n");

						if(array_key_exists($justif_id, $liste_communs))
							print("<font class='Textevert_menu'>&nbsp;&nbsp;- $justif_intitule</font>\n");
						else
							print("<font class='Texte_menu'>&nbsp;&nbsp;- $justif_intitule</font>\n");

						print("	</td>
									<td class='fond_menu' align='right' width='20' style='padding-right:10px;'>
										<a href='suppr_justif.php?pid=$propspec_id&o=$justif_ordre' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' border='0'></a>
									</td>
								</tr>\n");
					}
					else
					{
						print("<tr>
									<td class='fond_menu' style='padding-left:5px; padding-right:5px; width:16px;'>
										<a href='move_element.php?pid=$propspec_id&co=$justif_ordre&dir=0' target='_self'><img src='$__ICON_DIR/up_16x16_menu.png' alt='Monter' border='0'></a>
									</td>
									<td class='fond_menu' style='padding-left:5px; padding-right:5px; width:16px;'>");

						if($i!=($rows-1))
						{
							list($next_propspec_id)=db_fetch_row($result, $i+1);

							if($next_propspec_id==$propspec_id)
								print("<a href='move_element.php?pid=$propspec_id&co=$justif_ordre&dir=1' target='_self'><img src='$__ICON_DIR/down_16x16_menu.png' alt='Descendre' border='0'></a> ");
						}

						print("</td>
									<td class='fond_menu' style='padding:2px 5px 2px 5px;'>\n");

						if(array_key_exists($justif_id, $liste_communs))
							print("<font class='Textevert_menu'>&nbsp;&nbsp;- $justif_intitule</font>\n");
						else
							print("<font class='Texte_menu'>&nbsp;&nbsp;- $justif_intitule\n</font>");

						print("</td>
								<td class='fond_menu' align='right' width='20' style='padding-right:10px;'>
									<a href='suppr_justif.php?pid=$propspec_id&o=$justif_ordre' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' border='0'></a>
								</td>
							</tr>\n");
					}

					$old_propspec_id=$propspec_id;
				}

				// Fichiers attachés pour la dernière formation de la liste
				$res_fichiers=db_query($dbr,"SELECT $_DBC_justifs_fichiers_nom
															FROM $_DB_justifs_fichiers, $_DB_justifs_ff
														WHERE $_DBC_justifs_ff_fichier_id=$_DBC_justifs_fichiers_id
														AND $_DBC_justifs_ff_propspec_id='$propspec_id'
														ORDER BY $_DBC_justifs_fichiers_nom");

				$rows_fichiers=db_num_rows($res_fichiers);

				for($f=0; $f<$rows_fichiers; $f++)
				{
					list($nom_fichier)=db_fetch_row($res_fichiers, $f);

					print("<tr>
								<td class='fond_menu' style='padding-left:5px; padding-right:5px;' colspan='2'></td>
								<td class='fond_menu' style='padding:2px 5px 2px 5px;' colspan='2'>
									<font class='Texte_menu'>&nbsp;&nbsp;+ Fichier \"<i>$nom_fichier</i>\"</font>
								</td>
								</tr>\n");
				}

				db_free_result($res_fichiers);

				print("<tr>
							<td class='fond_menu' height='10' colspan='4'></td>
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
	</form>
</div>
<?php
	pied_de_page();
?>

</body></html>
