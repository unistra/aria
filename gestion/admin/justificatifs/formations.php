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

	// récupération de variables

	$dbr=db_connect();

	if(isset($_GET["c"]) && $_GET["c"]==1)
		unset($_SESSION["justif_id"]);

	if(isset($_GET["o"]) && ctype_digit($_GET["o"]))
		$_SESSION["ordre"]=$ordre=$_GET["o"];

	if(isset($_SESSION["filtre_justif"]) && $_SESSION["filtre_justif"]!="-1")
	{
		// Nouvel élément pour une formation déterminée
		$condition_selection="AND $_DBC_justifs_id NOT IN (SELECT distinct($_DBC_justifs_jf_justif_id) FROM $_DB_justifs_jf
																			WHERE $_DBC_justifs_jf_propspec_id='$_SESSION[filtre_justif]') ";
	}
	else
		$condition_selection="";

	if((isset($_POST["Suivant"]) || isset($_POST["Suivant_x"])) && isset($_POST["justif"]) && $_POST["justif"]!="")
	{
		$_SESSION["justif_id"]=$_POST["justif"];

		// Si on filtrait sur une formation le traitement s'arrête là (?)
		if(isset($_SESSION["filtre_justif"]) && $_SESSION["filtre_justif"]!="-1" && isset($_SESSION["ordre"]))
		{
			// Décalage des éléments suivants
			db_query($dbr, "UPDATE $_DB_justifs_jf SET $_DBU_justifs_jf_ordre=$_DBU_justifs_jf_ordre+1
									WHERE $_DBU_justifs_jf_propspec_id='$_SESSION[filtre_justif]'
									AND $_DBU_justifs_jf_ordre>='$_SESSION[ordre]'");

			// TODO : rajouter le formulaire pour la nationalité
			db_query($dbr, "INSERT INTO $_DB_justifs_jf VALUES('$_SESSION[justif_id]', '$_SESSION[filtre_justif]', '$_SESSION[ordre]', '$__COND_NAT_TOUS')");


			db_close($dbr);
			header("Location:index.php");
			exit;
		}
	}
	elseif(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
	{
		$cond_nationalite_globale=$_POST["cond_nat_all"];

		// On liste les formations actuellement rattachées, avec l'ordre
		$res_actuels=db_query($dbr, "SELECT $_DBC_justifs_jf_propspec_id, $_DBC_justifs_jf_ordre
												FROM $_DB_justifs_jf
											  WHERE $_DBC_justifs_jf_justif_id='$_SESSION[justif_id]'");

		$rows_actuels=db_num_rows($res_actuels);

		$array_actuels=array();

		for($i=0; $i<$rows_actuels; $i++)
		{
			list($propspec_id, $ordre)=db_fetch_row($res_actuels, $i);
			$array_actuels[$propspec_id]=$ordre;
		}

		db_free_result($res_actuels);

		if(isset($_POST["toutes_formations"]))
		{
			// Condition de nationalité à appliquer par défaut si non parametrée
			$cond_nationalite_globale=$cond_nationalite_globale=="" ? $__COND_NAT_TOUS : $cond_nationalite_globale;

			$result=db_query($dbr, "SELECT $_DBC_propspec_id FROM $_DB_propspec
											WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_active='1'");

			$rows=db_num_rows($result);

			$requete="";

			for($i=0; $i<$rows; $i++)
			{
				list($propspec_id)=db_fetch_row($result, $i);

				if(array_key_exists($propspec_id, $array_actuels)) // Mise à jour du rattachement
					$requete.="UPDATE $_DB_justifs_jf SET $_DBU_justifs_jf_nationalite='$cond_nationalite_globale'
									WHERE $_DBU_justifs_jf_propspec_id='$propspec_id' 
									AND $_DBU_justifs_jf_justif_id='$_SESSION[justif_id]';";
				else // insertion en dernière position
				{
					list($current_ordre)=db_fetch_row(db_query($dbr,"SELECT max($_DBC_justifs_jf_ordre) FROM $_DB_justifs_jf
																					 WHERE $_DBU_justifs_jf_propspec_id='$propspec_id'"), 0);

					$insert_ordre=$current_ordre=="" ? 0 : $current_ordre+1;
					$requete.="INSERT INTO $_DB_justifs_jf VALUES('$_SESSION[justif_id]', '$propspec_id', '$insert_ordre', '$cond_nationalite_globale');";
				}
			}

			if(!empty($requete))
				db_query($dbr,"$requete");

			db_free_result($result);
		}
		else // Sélection individuelle
		{
			$requete="";

			foreach($_POST as $key => $formation_id)
			{
				if(!strncmp($key, "formation_", 8))
				{
					list($max_ordre)=db_fetch_row(db_query($dbr,"SELECT max($_DBC_justifs_jf_ordre) FROM $_DB_justifs_jf
													  							WHERE $_DBC_justifs_jf_propspec_id='$formation_id'"), 0);

					$new_ordre=$max_ordre=="" ? 0 : ($max_ordre+1);

					if($cond_nationalite_globale!="")
						$cond_nationalite=$cond_nationalite_globale;
					elseif(array_key_exists("nat_$formation_id", $_POST) && $_POST["nat_$formation_id"]!="")
						$cond_nationalite=$_POST["nat_$formation_id"];
					else
						$cond_nationalite=$__COND_NAT_TOUS;

					if(!array_key_exists($formation_id, $array_actuels))
						$requete.="INSERT INTO $_DB_justifs_jf VALUES('$_SESSION[justif_id]', '$formation_id', '$new_ordre', '$cond_nationalite'); ";
					else // Mise à jour
						$requete.="UPDATE $_DB_justifs_jf SET $_DBU_justifs_jf_nationalite='$cond_nationalite'
									  WHERE $_DBU_justifs_jf_propspec_id='$formation_id'
									  AND $_DBU_justifs_jf_justif_id='$_SESSION[justif_id]'; ";

					// Suppression de la formation traitée dans le tableau "actuels"
					unset($array_actuels[$formation_id]);
				}
			}

			if(!empty($requete))
				db_query($dbr,"$requete");

			// On supprime de la base ce qu'il reste dans le tableau $array_actuels
			foreach($array_actuels as $formation_id => $current_ordre)
			{
				db_query($dbr, "DELETE FROM $_DB_justifs_jf WHERE $_DBC_justifs_jf_propspec_id='$formation_id'
																			AND $_DBC_justifs_jf_justif_id='$_SESSION[justif_id]'
																			AND $_DBC_justifs_jf_ordre='$current_ordre'");

				// Décalage des éléments suivants
				db_query($dbr, "UPDATE $_DB_justifs_jf SET $_DBU_justifs_jf_ordre=$_DBU_justifs_jf_ordre-1
									 WHERE $_DBU_justifs_jf_propspec_id='$formation_id'
									 AND $_DBU_justifs_jf_ordre>'$current_ordre'");
			}
		}

		db_close($dbr);
		header("Location:index.php");
		exit;
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		if(isset($_SESSION["filtre_justif_nom"]))
			titre_page_icone("Rattacher un justificatif (formation : $_SESSION[filtre_justif_nom]" , "edit_32x32_fond.png", 2, "L");
		else
			titre_page_icone("Rattacher un justificatif", "edit_32x32_fond.png", 2, "L");
	?>

	<form method='post' action='<?php echo $php_self; ?>'>

	<table cellpadding="0" cellspacing="0" border="0" align="center">
	<tr>
		<td>
			<table align='center'>
			<tr>
				<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
					<font class='Texte_menu2'>
						<b>&#8226;&nbsp;&nbsp;Informations</b>
					</font>
				</td>
			</tr>
			<?php
				// Formation filtrée (et donc forcée)
				if(isset($_SESSION["filtre_justif"]) && $_SESSION["filtre_justif"]!="-1")
				{
					$result=db_query($dbr,"(SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
														FROM $_DB_propspec, $_DB_annees, $_DB_specs
													WHERE $_DBC_propspec_annee=$_DBC_annees_id
													AND $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_propspec_id='$_SESSION[filtre_justif]')");

					if(db_num_rows($result))
					{
						list($propspec_id, $annee, $spec_nom, $finalite)=db_fetch_row($result, 0);

						$formation=$annee=="" ? "$spec_nom $tab_finalite[$finalite]" : "$annee $spec_nom $tab_finalite[$finalite]";

						print("<tr>
									<td class='td-gauche fond_menu2'>
										<font class='Texte_menu2'><b>Formation sélectionnée : </b></font>
									</td>
									<td class='td-droite fond_menu'>
										<font class='Texte_menu'><b>$formation</b></font>
									</td>
								</tr>\n");
					}

					db_free_result($result);
				}
			?>
			<tr>
				<td class='td-gauche fond_menu2' valign='top'>
					<font class='Texte_menu2'><b>Choix du justificatif :</b></font>
				</td>
				<td class='td-droite fond_menu'>
				<?php
					if(!isset($_SESSION["justif_id"]))
					{
						$result=db_query($dbr,"SELECT $_DBC_justifs_id, $_DBC_justifs_intitule
															FROM $_DB_justifs
														WHERE $_DBC_justifs_comp_id='$_SESSION[comp_id]'
														$condition_selection
															ORDER BY $_DBC_justifs_intitule");
						$rows=db_num_rows($result);

						if($rows)
						{
							print("<select name='justif'>
										<option value=''></option>\n");

							for($i=0; $i<$rows; $i++)
							{
								list($select_justif_id, $justif_intitule)=db_fetch_row($result, $i);

								$val=htmlspecialchars($justif_intitule, ENT_QUOTES, $default_htmlspecialchars_encoding);

								$selected=isset($justif_id) && $justif_id==$select_justif_id ? "selected='1'" : "";

								print("<option value='$select_justif_id' $selected>$val</option>\n");
							}

							print("</select>
									<br>\n");

							if(isset($_SESSION["filtre_justif"]) && $_SESSION["filtre_justif"]!="-1")
								print("<font class='Texte_menu'><i>Seuls les justificatifs non reliés à cette formation sont sélectionnables</i></font>\n");
						}
						else
						{
							$no_element=1;
							print("<font class='Texte_menu'>Plus aucun justificatif disponible ou aucun justificatif encore créé.<br></font>\n");
						}
					}
					else
					{
						$result=db_query($dbr,"SELECT $_DBC_justifs_intitule FROM $_DB_justifs
														WHERE $_DBC_justifs_id='$_SESSION[justif_id]'");
						$rows=db_num_rows($result);

						list($justif_intitule)=db_fetch_row($result, 0);
						$val=htmlspecialchars($justif_intitule, ENT_QUOTES, $default_htmlspecialchars_encoding);

						print("<font class='Texte_menu'><b>$val</b></font>\n");
					}
				?>
				</td>
			</tr>

			<?php
				if(!isset($_SESSION["justif_id"]))
				{
					print("</table>
							</td>
						</tr>
						</table>
						
						<div class='centered_icons_box'>
							<a href='index.php' target='_self'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>
							<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='Suivant' value='Valider'>
							</form>
						</div>\n");
				}
				else
				{
			?>
			<tr>
				<td class='td-gauche fond_menu2'>
					<font class='Texte_menu2'>
						<b>Sélectionner toutes les formations</b>
					</font>
				</td>
				<td class='td-droite fond_menu'>
					<font class='Texte_menu'>
						<input type='checkbox' name='toutes_formations' value='1'>
						&nbsp;(<i>si cochée, cette case est prioritaire sur la sélection individuelle</i>)
					</font>
				</td>
			</tr>
			<tr>
				<td class='td-gauche fond_menu2' style='padding-bottom:20px;'>
					<font class='Texte_menu2'>
						<b>Appliquer cette condition de nationalité
						<br>à toutes les formations rattachées :</b>
					</font>
				</td>
				<td class='td-droite fond_menu' style='padding-bottom:20px;'>
					<select name='cond_nat_all'>
						<option value=""></option>
						<option <?php echo "value='$__COND_NAT_TOUS'"; ?>>Nationalité indifférente</option>
						<option <?php echo "value='$__COND_NAT_FR'"; ?>>Candidats Français uniquement</option>
						<option <?php echo "value='$__COND_NAT_NON_FR'"; ?>>Candidats Non Français uniquement</option>
						<option <?php echo "value='$__COND_NAT_HORS_UE'"; ?>>Candidats hors UE</option>
						<option <?php echo "value='$__COND_NAT_UE'"; ?>>Candidats intra-UE uniquement</option>
					</select>
				</td>
			</tr>
			</table>
			<br>

			<?php
				// Nombre max de mentions pour les années de cette composantes (pour affichage)
				$res_mentions=db_query($dbr, "SELECT count(distinct($_DBC_specs_mention_id)) FROM $_DB_specs,$_DB_propspec
														WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
														AND $_DBC_propspec_comp_id ='$_SESSION[comp_id]'
														AND $_DBC_propspec_active='1'
													GROUP BY $_DBC_propspec_annee
													ORDER BY count DESC");

				list($max_mentions)=db_fetch_row($res_mentions, 0);

				$max_mentions=$max_mentions=="" ? 0 : $max_mentions;

				if($max_mentions>1)
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

				$result=db_query($dbr,"(SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_nom_court,
															$_DBC_propspec_finalite, $_DBC_mentions_id, $_DBC_mentions_nom
													FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
												WHERE $_DBC_propspec_annee=$_DBC_annees_id
												AND $_DBC_propspec_id_spec=$_DBC_specs_id
												AND $_DBC_specs_mention_id=$_DBC_mentions_id
												AND $_DBC_propspec_active='1'
												AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
													ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_specs_nom_court)");

				$rows=db_num_rows($result);
				$old_annee="===="; // idem

				if($rows)
				{
					// On liste les formations actuellement rattachées, avec la condition de nationalité
					$res_actuels=db_query($dbr, "SELECT $_DBC_justifs_jf_propspec_id, $_DBC_justifs_jf_nationalite
															FROM $_DB_justifs_jf
														WHERE $_DBC_justifs_jf_justif_id='$_SESSION[justif_id]'");

					$rows_actuels=db_num_rows($res_actuels);

					$array_actuels=array();

					for($i=0; $i<$rows_actuels; $i++)
					{
						list($propspec_id, $nationalite)=db_fetch_row($res_actuels, $i);
						$array_actuels[$propspec_id]=$nationalite;
					}

					db_free_result($res_actuels);

					$old_propspec_id="--"; // on initialise à n'importe quoi (sauf vide)
					$old_annee_id="--"; // idem
					$old_mention="--"; // idem
					$j=0;

					print("<table align='center'>\n");

					for($i=0; $i<$rows; $i++)
					{
						list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $mention_nom)=db_fetch_row($result, $i);

						$nom_finalite=$tab_finalite[$finalite];

						$checked=(array_key_exists($propspec_id, $array_actuels) || (isset($_SESSION["filtre_justif"]) && $_SESSION["filtre_justif"]==$propspec_id)) ? "checked" : "";

						if($annee_id!=$old_annee_id)
						{
							$annee=$annee=="" ? "Années particulières" : $annee;

							if($i) // Le premier résultat du tableau est particulier (i=0)
							{
								print("</table>
										</td>\n");

								if(!$j)
									print("<td class='fond_page' width='$colwidth' valign='top'></td>");

								print("</tr>
										<tr>
											<td class='fond_page' height='10' colspan='4'></td>
										</tr>\n");
							}

							print("<tr>
										<td class='fond_menu2' colspan='$colspan_annee' style='padding:4px 20px 4px 20px;'>
											<font class='Texte_menu2'><b>$annee</b></font>
										</td>
									</tr>
									<tr>
										<td width='$colwidth' valign='top'>
											<table width='100%' style='padding-bottom:10px;'>
											<tr>
												<td class='fond_menu2' colspan='3' align='center' height='20'>
													<font class='Texte_menu2'><b>$mention_nom</b></font>
												</td>
											</tr>\n");

							$old_mention="$mention";
							$old_annee_id=$annee_id;
							$j=0;
						}

						if($old_mention!=$mention)
						{
							if($i)
								print("</table>
										</td>\n");

							if($j)
								print("</tr>
										 <tr>\n");

							print("<td width='$colwidth' valign='top'>
										<table width='100%' style='padding-bottom:10px;'>
										<tr>
											<td class='fond_menu2' colspan='3' height='20' align='center'>
												<font class='Texte_menu2'><b>$mention_nom</b></font>
											</td>
										</tr>\n");

							$j=$j ? 0 : 1;

							$old_mention=$mention;
						}

						print("<tr>
									<td class='td-gauche fond_menu' style='padding:4px 2px 0px 2px;' width='15'>
										<input type='checkbox' name='formation_$propspec_id' value='$propspec_id' $checked style='vertical-align:middle;'>
									</td>
									<td class='td-milieu fond_menu' style='padding:4px 2px 0px 2px;'>
										<font class='Texte_menu'>$spec_nom $nom_finalite</font>
									</td>
									<td class='td-droite fond_menu' style='text-align:right;'>
										<select name='nat_$propspec_id'>
											<option value=''></option>\n");
					?>
											<option <?php echo "value='$__COND_NAT_TOUS'"; if(array_key_exists($propspec_id, $array_actuels) && $array_actuels[$propspec_id]==$__COND_NAT_TOUS) echo "selected=1"; ?>>Nationalité indifférente</option>
											<option <?php echo "value='$__COND_NAT_FR'"; if(array_key_exists($propspec_id, $array_actuels) && $array_actuels[$propspec_id]==$__COND_NAT_FR) echo "selected=1"; ?>>Candidats Français uniquement</option>
											<option <?php echo "value='$__COND_NAT_NON_FR'"; if(array_key_exists($propspec_id, $array_actuels) && $array_actuels[$propspec_id]==$__COND_NAT_NON_FR) echo "selected=1"; ?>>Candidats Non Français uniquement</option>
											<option <?php echo "value='$__COND_NAT_HORS_UE'"; if(array_key_exists($propspec_id, $array_actuels) && $array_actuels[$propspec_id]==$__COND_NAT_HORS_UE) echo "selected=1"; ?>>Candidats hors UE</option>
											<option <?php echo "value='$__COND_NAT_UE'"; if(array_key_exists($propspec_id, $array_actuels) && $array_actuels[$propspec_id]==$__COND_NAT_UE) echo "selected=1"; ?>>Candidats intra-UE uniquement</option>
										</select>
									</td>
								</tr>
					<?php
					}

					db_free_result($result);

					print("</table>
							</td>\n");

					if(!$j)
						print("<td class='fond_page' width='$colwidth' valign='top'></td>\n");

					print("</tr>
							 </table>\n");
				}

				?>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='index.php' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<a href='formations.php?c=1' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type='image' class='icone' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='go_valider' value='Valider'>
		</form>
	</div>

	<?php
		}	// Fin du else(isset($_SESSION[justif_id]))
	?>
</div>

<?php
	pied_de_page();
?>

<script language="javascript">
	document.form1.comp_id.focus()
</script>

</body></html>
