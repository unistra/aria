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

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";	

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();
		
	if(isset($_POST["act"]) && $_POST["act"]==1)
	{
		if(isset($_POST["go"]) || isset($_POST["go_x"]))
		{
			$current_periode=$_POST["periode"];
			$periode_txt="$current_periode - " . ($current_periode+1);

			$propspec_id=$_POST["formation"];

			$classement=$_POST["classement"];						

			$option_resultat=$_POST["option_resultat"];
			// $option_non_traites=$_POST["option_non_traites"];

			$option_infos_complementaires=$_POST["option_infos_complementaires"];

			$resultat=1;		

			// Test pour savoir quelles couleurs utiliser
			if($current_periode==$__PERIODE)
				$annee_courante=1;
			else
				$annee_courante=0;
		}
	}
	else
	{
		$current_periode=$__PERIODE;
		$periode_txt="$current_periode - " . ($current_periode+1);
	}
			
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Précandidatures : listes par formation", "kpercentage_32x32_fond.png", 15, "L");

		message("<b>Toutes</b> les précandidatures sont prises en compte dans ces listes, y compris les précandidatures non recevables.", $__INFO);

		if(!isset($resultat))
		{
			print("<form action='$php_self' method='POST'>
						<input type='hidden' name='act' value='1'>\n");
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2' nowrap='true' colspan='2' style='padding:4px;'>
			<font class='Texte_menu2'><b>Critères de sélection</b></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Formation</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='formation' size='1'>
				<?php
					$dbr=db_connect();

					$requete_droits_formations=requete_auth_droits($_SESSION["comp_id"]);

					$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
															$_DBC_propspec_manuelle
												FROM $_DB_propspec, $_DB_annees, $_DB_specs
											WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_annee=$_DBC_annees_id
											$requete_droits_formations
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom");
					$rows=db_num_rows($result);

					$prev_annee="--"; // variable initialisée à n'importe quoi

					// TODO : revoir l'utilisation de la table annee (intégration de annees.id dans proprietes_specialites_v2, par exemple) et répercuter les changements ici
					for($i=0; $i<$rows; $i++)
					{
						list($propspec_id, $annee, $nom,$finalite, $manuelle)=db_fetch_row($result,$i);

						if($annee!=$prev_annee)
						{
							if($i!=0)
								print("</optgroup>\n");

							if(empty($annee))
								print("<optgroup label='Années particulières'>\n");
							else
								print("<optgroup label='$annee'>\n");

							$prev_annee=$annee;
						}

						$formation_txt=$annee=="" ? "$nom $tab_finalite[$finalite]" : "$annee $nom $tab_finalite[$finalite]";

						if(isset($_SESSION["filtre_propspec"]) && $_SESSION["filtre_propspec"]==$propspec_id)
							$selected="selected=1";
						else
							$selected="";

						$manuelle_txt=$manuelle ? "(M)" : "";

						print("<option value='$propspec_id' label=\"$formation_txt $manuelle_txt\" $selected>$formation_txt $manuelle_txt</option>\n");
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Tri du résultat</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='classement' size='1'>
				<option value='nom_cursus'>Par nom, avec cursus</option>
				<option value='nom'>Par nom, sans cursus</option>
				<option value='diplome'>Par diplôme (dernier obtenu)</option>
				<option value='lieu'>Par ville du dernier diplôme obtenu</option>
				<option value='statut'>Par statut de la précandidature</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Période</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='periode' size='1'>
				<?php
					$result=db_query($dbr,"SELECT min($_DBC_cand_periode), max($_DBC_cand_periode) FROM $_DB_cand
													WHERE $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																								WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')");
					$rows=db_num_rows($result);

					if($rows)
					{
						list($minY,$maxY)=db_fetch_row($result,0);

						$minY=$minY=="" ? $__PERIODE : $minY;
						$maxY=$maxY=="" ? $__PERIODE : $maxY;

						for($i=$maxY; $i>=$minY; $i--)
						{
							if($i==$current_periode)
								$selected="selected='1'";
							else
								$selected="";

							print("<option value='$i' $selected>$i</option>");
						}
					}
					else
						print("<option value='$__PERIODE'>$__PERIODE</option>");

					db_close($dbr);
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' rowspan='2' valign='top'>
			<font class='Texte_menu2'><b>Options</b></font>
		</td>
		<td class='td-droite fond_menu' valign='top'>
			<font class='Texte_menu'>
				Afficher le statut de commission pédagogique&nbsp;&nbsp;<input type='radio' name='option_resultat' value='1' checked='true'>&nbsp;Oui&nbsp;&nbsp;<input type='radio' name='option_resultat' value='0'>&nbsp;Non
		<!--
				<	br><br>
				Ne montrer que les dossiers non traités&nbsp;&nbsp;<input type='radio' name='option_non_traites' value='1'>&nbsp;Oui&nbsp;&nbsp;<input type='radio' name='option_non_traites' value='0' checked='true'>&nbsp;Non
		-->
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				Afficher les "informations complémentaires" entrées par le candidat&nbsp;&nbsp;<input type='radio' name='option_infos_complementaires' value='1'>&nbsp;Oui&nbsp;&nbsp;<input type='radio' name='option_infos_complementaires' value='0' checked='true'>&nbsp;Non
			</font>
		</td>
	</tr>
	</table>


	<div class='centered_icons_box'>
		<a href='tabs_stats.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' alt='Retour au menu précédent' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/forward_32x32.png"; ?>" alt="Résultats" name="go" value="Résultats">
		</form>
	</div>

	<?php
		}
		else // résultat de la recherche
		{
			print("<center>");

			$dbr=db_connect();

			$result2=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
												FROM $_DB_propspec, $_DB_annees, $_DB_specs
											WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id='$propspec_id'
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom");
			$rows2=db_num_rows($result2);

			list($nom_annee, $spec_nom, $finalite)=db_fetch_row($result2,0);
			db_free_result($result2);

			$insc_txt=$nom_annee=="" ? "$spec_nom $tab_finalite[$finalite]" : "$nom_annee $spec_nom $tab_finalite[$finalite]";

			/*
			===========================================
						TRI PAR NOM AVEC CURSUS
			===========================================
			*/
			if($classement=="nom_cursus")
			{
				$result=db_query($dbr,"SELECT DISTINCT $_DBC_cand_candidat_id, $_DBC_cand_id, $_DBC_candidat_civilite, $_DBC_candidat_nom,
																	$_DBC_candidat_prenom, $_DBC_candidat_date_naissance, $_DBC_decisions_id,
																	$_DBC_decisions_texte, $_DBC_cand_statut
													FROM $_DB_candidat ,$_DB_cand, $_DB_decisions
												WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
												AND $_DBC_cand_propspec_id='$propspec_id'
												AND $_DBC_cand_decision = $_DBC_decisions_id
												AND $_DBC_cand_periode='$current_periode'
													ORDER BY $_DBC_candidat_nom ASC");

				$rows=db_num_rows($result);

				print("<font style='font-family: arial;' size='3'>
							Liste des candidats pour la formation : <b>$insc_txt</b>, triés par nom, avec cursus ($rows)
						</font>
						<br><br>\n");

				if($rows)
				{
					print("<table width='97%' cellpadding='2' cellspacing='0' border='0'>
							<tr>
								<td class='td-gauche fond_menu2' colspan='2'>
									<font class='Texte_menu2'><strong>Candidat</strong></font>
								</td>
								<td class='td-milieu fond_menu2'>
									<font class='Texte_menu2'><strong>Précandidature</strong></font>
								</td>\n");

					if($option_resultat)
					{
						print("<td class='td-droite fond_menu2' align='center'>
									<font class='Texte_menu2'><strong>Commission</strong></font>
								</td>\n");

						$colspan='4';
					}
					else
						$colspan='3';

					print("</tr>
							<tr>
								<td colspan='$colspan' height='15'></td>
							</tr>\n");

					for($i=0; $i<$rows; $i++)
					{
						list($candidat_id,$inid,$civilite,$nom,$prenom,$date_naiss,$decision_id, $decision_texte, $statut)=db_fetch_row($result,$i);

						switch($statut)
						{
							case $__PREC_NON_TRAITEE :	// précandidature non traitée
																$statut_txt="<font class='Texte_important_menu'>Non traitée</font>";
																break;

							case $__PREC_PLEIN_DROIT :	// entrée de plein droit
																$statut_txt="<font class='Texte_menu'>Plein droit</font>";
																break;

							case $__PREC_RECEVABLE	:	// précandidature recevable
																$statut_txt="<font class='Textevert_menu'>Recevable</font>";
																break;

							case $__PREC_EN_ATTENTE	:	// précandidature en attente
																$statut_txt="<font class='Texte_menu'>En attente</font>";
																break;

							case $__PREC_NON_RECEVABLE	:	// précandidature non recevable
																	$statut_txt="<font class='Texte_important_menu'>Non recevable</font>";
																	break;

							case $__PREC_ANNULEE	:	// précandidature annulée
															$statut_txt="<font class='Textegris'>Annulée</font>";
															break;
						}

						// Affichage du résultat d'admission ?
						if($option_resultat)
						{
							if(isset($annee_courante) && $annee_courante==0)	// années précédentes : couleurs faites pour distinguer les refus des admis
							{
								switch($decision_id)
								{
									case $__DOSSIER_ADMIS	:	$font_class="Textevert_menu"; // vert
																		break;

									case $__DOSSIER_ADMISSION_CONFIRMEE	:	$font_class="Textevert_menu"; // vert
																		break;

									case $__DOSSIER_ADMIS_RECOURS	:	$font_class="Textevert_menu"; // vert
																				break;

									case $__DOSSIER_ADMIS_ENTRETIEN:  $font_class="Textevert_menu"; // vert
																					break;

									case $__DOSSIER_ADMIS_LISTE_COMP:  $font_class="Textevert_menu"; // vert
																					break;

									case $__DOSSIER_REFUS	:	$font_class="Texte_important_menu"; // rouge
																		break;

									case $__DOSSIER_DESISTEMENT	:	$color="#CC0000"; // rouge
																				break;

									case $__DOSSIER_REFUS_RECOURS	:	$font_class="Texte_important_menu"; // rouge
																				break;

									default	:	$font_class='Texte_menu'; // orange
								}
							}
							else // année en cours : couleurs faites pour vérifier les dossiers en cours de traitement
							{
								if($decision_id<0) // pour les dossiers nécessitant encore un traitement
									$font_class="Texte_important_menu";
								elseif($decision_id>0) // dossiers traités
									$font_class="Textevert_menu";
								else
									$font_class="Texte_important_menu";
							}

							$colonne_resultat="<td class='td-milieu fond_menu' align='center'>
														<font class='$font_class'><b>$decision_texte</b></font>
													</td>";
						}
						else
							$colonne_resultat="";

						$naissance=date_fr("j/m/Y",$date_naiss);

						if($civilite=="M")
						{
							$civilite="M.";
							$naiss="né le $naissance";
						}
						else
							$naiss="née le $naissance";

						print("<tr>
									<td class='td-gauche fond_menu' colspan='2'>
										<a href='edit_candidature.php?cid=$candidat_id' target='_self' class='lien_menu_gauche'>$civilite $nom $prenom, $naiss</a>
									</td>
									<td class='td-milieu fond_menu' align='center'>
										<b>$statut_txt</b>
									</td>
									$colonne_resultat
								</tr>\n");

						// cursus
						$result2=db_query($dbr,"(SELECT $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_annee,
																	$_DBC_cursus_mention, $_DBC_cursus_ecole, $_DBC_cursus_ville, 
																	CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
																		THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
																		ELSE '' END as cursus_pays
															FROM $_DB_cursus
														WHERE $_DBC_cursus_candidat_id='$candidat_id'
														AND $_DBC_cursus_annee='0')
													UNION ALL
														(SELECT $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_annee,
																	$_DBC_cursus_mention, $_DBC_cursus_ecole, $_DBC_cursus_ville, 
																	CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
																		THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
																		ELSE '' END as cursus_pays
															FROM $_DB_cursus
														WHERE $_DBC_cursus_candidat_id='$candidat_id'
														AND $_DBC_cursus_annee!='0'
															ORDER BY $_DBC_cursus_annee DESC)");

						$rows2=db_num_rows($result2);

						$fond1='fond_blanc';
						$fond2='fond_page';

						for($j=0; $j<$rows2; $j++)
						{
							list($dipl,$intitule,$cursus_spec,$annee_obtention,$mention,$ecole,$ville,$pays)=db_fetch_row($result2,$j);

							// TODO : Compatibilité : suppression des marqueurs de champs libres (caractère '_' en début de chaîne)
							$dipl=str_replace('_','',$dipl);
							$intitule=str_replace('_','',$intitule);
							$cursus_spec=str_replace('_','',$cursus_spec);
							$ecole=str_replace('_','',$ecole);
							$ville=str_replace('_','',$ville);
							$pays=str_replace('_','',$pays);
							// =========

							if($annee_obtention=="" || $annee_obtention==0)
								$annee_obtention="En cours";

							$diplome="$dipl $intitule";

							if(!empty($cursus_spec))
								$diplome .= " ($cursus_spec)";

							if(!empty($ville))
								$ville_txt=", $ville";
							else
								$ville_txt="";

							if(!empty($ecole))
								$lieu_txt="- <i>$ecole$ville_txt</i>";
							else
								$lieu_txt="$ville_txt";

							if(!empty($mention))
								$mention_txt="- mention : $mention";
							else
								$mention_txt="";

							print("<tr>
										<td class='td-gauche $fond1'>
											<font style='font-family: arial;' size='2'>- $annee_obtention</font>
										</td>
										<td class='td-milieu $fond1'>
											<font style='font-family: arial;' size='2'>
												$diplome $mention_txt
												<br><i>$lieu_txt, $pays</i>
											</font>
										</td>
										<td  class='td-droite $fond1' colspan='".($colspan-2)."'>&nbsp;</td>
									</tr>\n");

							switch_vals($fond1, $fond2);
						}

						db_free_result($result2);

						// Informations complémentaires
						if($option_infos_complementaires)
						{
							$res_infos_comp=db_query($dbr,"SELECT $_DBC_infos_comp_texte, $_DBC_infos_comp_annee, $_DBC_infos_comp_duree
																		FROM $_DB_infos_comp
																	WHERE $_DBC_infos_comp_candidat_id='$candidat_id'
																		ORDER BY $_DBC_infos_comp_annee DESC");

							$rows_infos_comp=db_num_rows($res_infos_comp);

							if($rows_infos_comp)
							{
								print("<tr>
											<td class='fond_blanc' colspan='$colspan'>
												<font class='Texte_menu'><b><i>Informations complémentaires :</b></font>
											</td>
										</tr>\n");

								for($m=0; $m<$rows_infos_comp; $m++)
								{
									list($comp_info,$comp_annee,$comp_duree)=db_fetch_row($res_infos_comp, $m);

									$comp_info=preg_replace("/[\n]+/","<br>",$comp_info);

									$comp_duree=$comp_duree=="" ? "" : "($comp_duree)";

									print("<tr>
												<td class='td-gauche $fond1' align='left' valign='top' nowrap='true'>
													<font style='font-family: arial;' size='2'>- $comp_annee $comp_duree</font>
												</td>
												<td class='td-milieu $fond1' align='left' valign='top'>
													<font style='font-family: arial;' size='2'>$comp_info</font>
												</td>
												<td class='td-droite $fond1' colspan='".($colspan-2)."'>&nbsp;</td>
											</tr>\n");

									switch_vals($fond1, $fond2);
								}
							}

							db_free_result($res_infos_comp);
						}

						print("<tr>
									<td class='fond_blanc' colspan='$colspan'>&nbsp;</td>
								</tr>\n");
					}
					print("</table>
								<br><br>\n");
				}
				else
					print("<font class='Texte'>
								<i>Aucun candidat dans cette formation</i>
							</font>
							<br><br><br>\n");

				db_free_result($result);
			}
			/*
			===========================================
						TRI PAR NOM, SANS CURSUS
			===========================================
			*/
			elseif($classement=="nom")
			{
				$result=db_query($dbr,"SELECT DISTINCT	$_DBC_cand_candidat_id, $_DBC_cand_id, $_DBC_candidat_civilite, $_DBC_candidat_nom,
																	$_DBC_candidat_prenom, $_DBC_candidat_date_naissance, $_DBC_decisions_id,
																	$_DBC_decisions_texte, $_DBC_cand_statut
													FROM $_DB_candidat, $_DB_cand, $_DB_decisions
												WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
												AND $_DBC_cand_propspec_id='$propspec_id'
												AND $_DBC_cand_decision=$_DBC_decisions_id
												AND $_DBC_cand_periode='$current_periode'
													ORDER BY $_DBC_candidat_nom ASC");

				$rows=db_num_rows($result);

				print("<font style='font-family: arial;' size='3'>
							Liste des candidats pour la formation : <b>$insc_txt</b>, triés par nom ($rows)
							</font>
							<br><br><hr><br>");

				if($option_infos_complementaires)
					$colspan_cand=2;
				else
					$colspan_cand=1;

				if($rows)
				{
					print("<table width='97%' cellpadding='2' cellspacing='0' border='0'>
							<tr>
								<td class='td-gauche fond_menu2' colspan='$colspan_cand'>
									<font class='Texte_menu2'><strong>Candidat</strong></font>
								</td>
								<td class='td-milieu fond_menu2'>
									<font class='Texte_menu2'><strong>Précandidature</strong></font>
								</td>\n");

					if($option_resultat)
					{
						print("<td class='td-droite fond_menu2' align='center'>
									<font class='Texte_menu2'><strong>Commission</strong></font>
								</td>\n");

						$colspan=$colspan_cand+2;
					}
					else
						$colspan=$colspan_cand+1;

					print("</tr>
							<tr>
								<td colspan='$colspan' height='15'></td>
							</tr>\n");

					$fond1='fond_menu';
					$fond2='fond_blanc';

					for($i=0; $i<$rows; $i++)
					{
						list($candidat_id,$inid,$civilite,$nom,$prenom,$date_naiss,$decision_id,$decision_texte, $statut)=db_fetch_row($result,$i);

						switch($statut)
						{
							case $__PREC_NON_TRAITEE :	// précandidature non traitée
																$statut_txt="<font class='Texteorange'>Non traitée</font>";
																break;

							case $__PREC_PLEIN_DROIT :	// entrée de plein droit
																$statut_txt="<font class='Texte'>Plein droit</font>";
																break;

							case $__PREC_RECEVABLE	:	// précandidature recevable
																$statut_txt="<font class='Textevert'>Recevable</font>";
																break;

							case $__PREC_EN_ATTENTE	:	// précandidature en attente
																$statut_txt="<font class='Texteorange'>En attente</font>";
																break;

							case $__PREC_NON_RECEVABLE	:	// précandidature non recevable
																	$statut_txt="<font class='Texte_important'>Non recevable</font>";
																	break;

							case $__PREC_ANNULEE	:	// précandidature annulée
															$statut_txt="<font class='Textegris'>Annulée</font>";
															break;
						}

						// Affichage du résultat d'admission ?
						if($option_resultat)
						{
							if(isset($annee_courante) && $annee_courante==0)	// années précédentes : couleurs faites pour distinguer les refus des admis
							{
								switch($decision_id)
								{
									case $__DOSSIER_ADMIS :	$color="#00BB00"; // vert
																	break;

									case $__DOSSIER_ADMISSION_CONFIRMEE :	$color="#00BB00"; // vert
																	break;

									case $__DOSSIER_ADMIS_RECOURS :	$color="#00BB00"; // vert
																				break;

									case $__DOSSIER_ADMIS_ENTRETIEN:  $color="#00BB00"; // vert
																					break;

									case $__DOSSIER_ADMIS_LISTE_COMP:  $color="#00BB00"; // vert
																					break;

									case $__DOSSIER_REFUS	:	$color="#CC0000"; // rouge
																		break;

									case $__DOSSIER_DESISTEMENT	:	$color="#CC0000"; // rouge
																				break;

									case $__DOSSIER_REFUS_RECOURS	:	$color="#CC0000"; // rouge
																				break;

									default	:	$color='#FF8800'; // orange
								}
							}
							else // année en cours : couleurs faites pour vérifier les dossiers en cours de traitement
							{
								if($decision_id<0) // pour les dossiers nécessitant encore un traitement
									$color="#FF8800";
								elseif($decision_id>0) // dossiers traités
									$color="#00BB00";
								else
									$color="#CC0000";
							}

							$colonne_resultat="<td class='td-milieu $fond1' align='center'>
														<font class='Texte' style='color:$color'>$decision_texte</font>
													</td>";
						}
						else
							$colonne_resultat="";

						$naissance=date_fr("j/m/Y",$date_naiss);

						if($civilite=="M")
						{
							$civilite="M.";
							$naiss="né le $naissance";
						}
						else
							$naiss="née le $naissance";

						print("<td class='td-gauche $fond1' colspan='$colspan_cand'>
									<a href='edit_candidature.php?cid=$candidat_id' target='_self' class='lien2'>$civilite $nom $prenom, $naiss</a>
								</td>
								<td class='td-milieu $fond1' align='center'>
									$statut_txt
								</td>
								$colonne_resultat
							</tr>\n");

						// Informations complémentaires
						if($option_infos_complementaires)
						{
							$res_infos_comp=db_query($dbr,"SELECT $_DBC_infos_comp_texte, $_DBC_infos_comp_annee, $_DBC_infos_comp_duree
																		FROM $_DB_infos_comp
																	WHERE $_DBC_infos_comp_candidat_id='$candidat_id'
																		ORDER BY $_DBC_infos_comp_annee DESC");

							$rows_infos_comp=db_num_rows($res_infos_comp);

							if($rows_infos_comp)
							{
								print("<tr>
											<td class='fond_blanc' colspan='$colspan' align='left'>
												<font class='Texte_menu'><b><i>Informations complémentaires :</b></font>
											</td>
										</tr>\n");

								for($m=0; $m<$rows_infos_comp; $m++)
								{
									list($comp_info,$comp_annee,$comp_duree)=db_fetch_row($res_infos_comp, $m);

									$comp_info=preg_replace("/[\n]+/","<br>",$comp_info);

									$comp_duree=$comp_duree=="" ? "" : "($comp_duree)";

									print("<tr>
												<td class='td-gauche fond_blanc' valign='top'>
													<font class='Texte'>- $comp_annee $comp_duree</font>
												</td>
												<td class='td-milieu fond_blanc'>
													<font class='Texte'>$comp_info</font>
												</td>
												<td class='td-droite fond_blanc' colspan='".($colspan-2)."'>&nbsp;</td>
											</tr>\n");
								}
							}

							db_free_result($res_infos_comp);
						}

						print("<tr>
									<td colspan='$colspan'>&nbsp;</td>
								</tr>\n");
					}

					print("</table>
								<br><br>\n");
				} // fin du if($rows)
				else
					print("<font class='Texte'>
								<i>Aucun candidat dans cette formation</i>
							</font>
							<br><br><br>\n");

				db_free_result($result);
			}

			/*
			========================================================
						TRI PAR RESULTAT (STATUT) OU PAR LIEU
			========================================================
			*/
			elseif($classement=="diplome" || $classement=="lieu")
			{
				$dbr=db_connect();

				$result=db_query($dbr,"SELECT $_DBC_cand_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom,
														$_DBC_candidat_date_naissance, $_DBC_candidat_civilite, $_DBC_decisions_id,
														$_DBC_decisions_texte, $_DBC_cand_statut
													FROM $_DB_cand, $_DB_candidat, $_DB_decisions
												WHERE $_DBC_cand_candidat_id=$_DBC_candidat_id
												AND $_DBC_cand_propspec_id='$propspec_id'
												AND $_DBC_cand_decision=$_DBC_decisions_id
												AND $_DBC_cand_periode='$current_periode'");

				$rows=db_num_rows($result);

				$etudiants=array();

				if($rows)
				{
					for($i=0;$i<$rows;$i++)
					{
						list($candidat_id,$inid,$nom,$prenom,$date_naiss,$civilite, $decision_id, $decision_texte, $statut)=db_fetch_row($result,$i);

						$naissance=date_fr("j/m/Y",$date_naiss);

						switch($statut)
						{
							case $__PREC_NON_TRAITEE :	// précandidature non traitée
																$statut_txt="<font class='Texteorange'>Non traitée</font>";
																break;

							case $__PREC_PLEIN_DROIT :	// entrée de plein droit
																$statut_txt="<font class='Texte'>Plein droit</font>";
																break;

							case $__PREC_RECEVABLE	:	// précandidature recevable
																$statut_txt="<font class='Textevert'>Recevable</font>";
																break;

							case $__PREC_EN_ATTENTE	:	// précandidature en attente
																$statut_txt="<font class='Texteorange'>En attente</font>";
																break;

							case $__PREC_NON_RECEVABLE	:	// précandidature non recevable
																	$statut_txt="<font class='Texte_important'>Non recevable</font>";
																	break;

							case $__PREC_ANNULEE	:	// précandidature annulée
															$statut_txt="<font class='Textegris'>Annulée</font>";
															break;
						}

						$etudiants[$i]=array();
						$etudiants[$i]['nom']=$nom;
						$etudiants[$i]['prenom']=$prenom;
						$etudiants[$i]['civilite']=$civilite;
						$etudiants[$i]['naissance']=$naissance;
						$etudiants[$i]['id']=$candidat_id;
						$etudiants[$i]['decision_id']=$decision_id;
						$etudiants[$i]['decision_texte']=$decision_texte;
						$etudiants[$i]['statut_texte']=$statut_txt;
						$etudiants[$i]['cursus']=array();

						// recherche du dernier diplôme obtenu ou en cours
						$result2=db_query($dbr,"(SELECT $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_annee,
																	$_DBC_cursus_mention, $_DBC_cursus_ecole, $_DBC_cursus_ville, 
																	CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
																		THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
																		ELSE '' END as cursus_pays
															FROM $_DB_cursus
														WHERE $_DBC_cursus_candidat_id='$candidat_id'
														AND   $_DBC_cursus_annee='0'
															ORDER BY $_DBC_cursus_diplome, $_DBC_cursus_intitule)
													UNION ALL
														(SELECT $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_annee,
																	$_DBC_cursus_mention, $_DBC_cursus_ecole, $_DBC_cursus_ville, 
																	CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
																		THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
																		ELSE '' END as cursus_pays
															FROM $_DB_cursus
														WHERE $_DBC_cursus_candidat_id='$candidat_id'
														AND   $_DBC_cursus_annee!='0'
															ORDER BY $_DBC_cursus_annee DESC, $_DBC_cursus_diplome, $_DBC_cursus_intitule)");

						$rows2=db_num_rows($result2);

						if($rows2)
						{
							// $flag_0=0;
							for($j=0;$j<$rows2;$j++)
							{
								list($cursus_diplome,$cursus_intitule,$cursus_spec,$cursus_annee_obtention, $cursus_mention, $cursus_ecole, $cursus_ville, $cursus_pays)=db_fetch_row($result2,$j);

								// TODO : Compatibilité : suppression des marqueurs de champs libres (caractère '_' en début de chaîne)
								$cursus_diplome=str_replace('_','',$cursus_diplome);
								$cursus_intitule=str_replace('_','',$cursus_intitule);
								$cursus_spec=str_replace('_','',$cursus_spec);
								$cursus_ecole=str_replace('_','',$cursus_ecole);
								$cursus_ville=str_replace('_','',$cursus_ville);
								$cursus_pays=str_replace('_','',$cursus_pays);
								// =========

								if($cursus_annee_obtention==0)
									$etudiants[$i]['cursus'][$j]['annee']="En cours";
								else
									$etudiants[$i]['cursus'][$j]['annee']=$cursus_annee_obtention;

								$etudiants[$i]['cursus'][$j]['diplome']=$cursus_diplome;
								$etudiants[$i]['cursus'][$j]['intitule']=$cursus_intitule;
								$etudiants[$i]['cursus'][$j]['spec']=$cursus_spec;
								$etudiants[$i]['cursus'][$j]['mention']=$cursus_mention;
								$etudiants[$i]['cursus'][$j]['ecole']=$cursus_ecole;
								$etudiants[$i]['cursus'][$j]['ville']=$cursus_ville;
								$etudiants[$i]['cursus'][$j]['pays']=$cursus_pays;
							}
						}
					}

					// on a fini de completer le tableau, on le trie et on affiche

					if($classement=="diplome")
					{
						$tri="dernier diplôme obtenu";
						$bool=usort($etudiants,"cmp_diplome");
					}
					elseif($classement=="lieu")
					{
						$tri="ville du dernier diplôme obtenu";
						$bool=usort($etudiants,"cmp_lieu");
					}

					print("<font style='font-family: arial;' size='3'>
								Liste des candidats pour la formation : <b>$insc_txt</b>, triés par $tri ($rows)
							</font>
							<br><br>
							<hr>
							<br>\n");

					if($bool==FALSE)
						print("<center>
									<i><font class='Texte'>Attention : tri non effectué</font></i>
								</center>\n");

					$cnt=count($etudiants);

					if($cnt)
					{
						print("<table width='97%' cellpadding='2' cellspacing='0' border='0'>
									<tr>
										<td class='td-gauche fond_menu2' colspan='2'>
											<font class='Texte_menu2'><b>Candidat</b></font>
										</td>
										<td class='td-milieu fond_menu2'>
											<font class='Texte_menu2'><b>Précandidature</b></font>
										</td>\n");

						if($option_resultat)
						{
							print("<td class='td-droite fond_menu2' align='center'>
										<font class='Texte'><b>Commission</b></font>
									</td>\n");

							$colspan='4';
						}
						else
							$colspan='3';

						print("</tr>
								<tr>
									<td colspan='$colspan' height='15'></td>
								</tr>\n");

						// Boucle sur le tableau d'étudiants
						for($k=0;$k<$cnt;$k++)
						{
							$civ=$etudiants[$k]['civilite'];
							$date_naiss=$etudiants[$k]['naissance'];
							$decision_id=$etudiants[$k]['decision_id'];
							$decision_texte=$etudiants[$k]['decision_texte'];
							$statut_txt=$etudiants[$k]['statut_texte'];

							// Affichage du résultat d'admission ?
							if($option_resultat)
							{
								if(isset($annee_courante) && $annee_courante==0)	// années précédentes : couleurs faites pour distinguer les refus des admis
								{
									switch($decision_id)
									{
										case $__DOSSIER_ADMIS	:	$color="#00BB00"; // vert
																			break;

										case $__DOSSIER_ADMISSION_CONFIRMEE	:	$color="#00BB00"; // vert
																			break;

										case $__DOSSIER_ADMIS_RECOURS	:	$color="#00BB00"; // vert
																					break;

										case $__DOSSIER_ADMIS_ENTRETIEN: $color="#00BB00"; // vert
																					break;

										case $__DOSSIER_ADMIS_LISTE_COMP:   $color="#00BB00"; // vert
																						break;

										case $__DOSSIER_REFUS	:	$color="#CC0000"; // rouge
																			break;

										case $__DOSSIER_DESISTEMENT	:	$color="#CC0000"; // rouge
																					break;

										case $__DOSSIER_REFUS_RECOURS	:	$color="#CC0000"; // rouge
																					break;

										default	:	$color='#FF8800'; // orange
									}
								}
								else // année en cours : couleurs faites pour vérifier les dossiers en cours de traitement
								{
									if($decision_id<0) // pour les dossiers nécessitant encore un traitement
										$color="#FF8800";
									elseif($decision_id>0) // dossiers traités
										$color="#00BB00";
									else
										$color="#CC0000";
								}

								$colonne_resultat="<td class='td-milieu fond_menu' align='center'>
															<font class='Texte' style='color:$color'>$decision_texte</font>
														</td>";

								$colspan='4';
							}
							else
							{
								$colonne_resultat="";
								$colspan='3';
							}

							if($civ=="M")
							{
								$civ="M.";
								$naiss="né le $date_naiss";
							}
							else
								$naiss="née le $date_naiss";

							$nom=$etudiants[$k]['nom'];
							$prenom=$etudiants[$k]['prenom'];

							print("<tr>
										<td class='td-gauche fond_menu' colspan='2'>
											<a href='edit_candidature.php?cid=$candidat_id' target='_self' class='lien2'>$civ $nom $prenom, $naiss</a>
										</td>
										<td class='td-milieu fond_menu' align='center'>
											$statut_txt
										</td>
										$colonne_resultat
									</tr>\n");

							$cnt2=count($etudiants[$k]['cursus']);

							$fond1='fond_blanc';
							$fond2='fond_page';

							// Boucle sur le cursus
							for($l=0;$l<$cnt2;$l++)
							{
								$dip=$etudiants[$k]['cursus'][$l]['diplome'];
								$int=$etudiants[$k]['cursus'][$l]['intitule'];
								$c_spec=$etudiants[$k]['cursus'][$l]['spec'];
								$ann=$etudiants[$k]['cursus'][$l]['annee'];
								$mention=$etudiants[$k]['cursus'][$l]['mention'];
								$ecole=$etudiants[$k]['cursus'][$l]['ecole'];
								$ville=$etudiants[$k]['cursus'][$l]['ville'];
								$pays=$etudiants[$k]['cursus'][$l]['pays'];

								if(!empty($ville))
									$ville_txt=", $ville";
								else
									$ville_txt="";

								if(!empty($ecole))
									$lieu_txt="- <i>$ecole$ville_txt</i>";
								else
									$lieu_txt="$ville_txt";

								$diplome="$dip $int";

								if(!empty($c_spec))
									$diplome.=" ($c_spec)";

								if(!empty($mention))
									$mention_txt="- mention : $mention";
								else
									$mention_txt="";

								print("<tr>
											<td class='td-gauche $fond1' valign='top'>
												<font style='font-family: arial;' size='2'>- $ann</font>
											</td>
											<td class='td-milieu $fond1' valign='top'>
												<font style='font-family: arial;' size='2'>
													$diplome $mention_txt
													<br><i>$lieu_txt, $pays</i>
												</font>
											</td>
											<td class='td-milieu $fond1' colspan='".($colspan-2)."'>&nbsp;</td>
										</tr>\n");

								switch_vals($fond1, $fond2);
							}

							// Informations complémentaires
							if($option_infos_complementaires)
							{
								$res_infos_comp=db_query($dbr,"SELECT $_DBC_infos_comp_texte, $_DBC_infos_comp_annee, $_DBC_infos_comp_duree
																			FROM $_DB_infos_comp
																		WHERE $_DBC_infos_comp_candidat_id='$candidat_id'
																			ORDER BY $_DBC_infos_comp_annee DESC");

								$rows_infos_comp=db_num_rows($res_infos_comp);

								if($rows_infos_comp)
								{
									print("<tr>
												<td class='td-droite fond_blanc' colspan='$colspan'>
													<font class='Texte_menu'><b><i>Informations complémentaires :</b></font>
												</td>
											</tr>\n");

									for($m=0; $m<$rows_infos_comp; $m++)
									{
										list($comp_info,$comp_annee,$comp_duree)=db_fetch_row($res_infos_comp, $m);

										$comp_info=preg_replace("/[\n]+/","<br>",$comp_info);

										$comp_duree=$comp_duree=="" ? "" : "($comp_duree)";

										print("<tr>
													<td class='td-gauche $fond1' valign='top'>
														<font style='font-family: arial;' size='2'>- $comp_annee $comp_duree</font>
													</td>
													<td class='td-milieu $fond1' valign='top'>
														<font style='font-family: arial;' size='2'>$comp_info</font>
													</td>
													<td class='td-milieu $fond1' colspan='".($colspan-2)."'>&nbsp;</td>
												</tr>\n");

										switch_vals($fond1, $fond2);
									}
								}

								db_free_result($res_infos_comp);
							}

							print("<tr>
										<td colspan='$colspan'>&nbsp;</td>
									</tr>\n");
						}

						print("</table>
									<br><br>\n");
					}
				}
				else
					print("<font class='Texte'>
								<i>Aucun candidat dans cette formation</i>
							</font>
							<br><br><br>\n");

				db_free_result($result);
			}
			/*
			===========================================
							Classement par statut
			===========================================
			*/
			elseif($classement=="statut")
			{
				$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom,
														$_DBC_candidat_date_naissance, $_DBC_cand_id, $_DBC_decisions_id, $_DBC_decisions_texte,
														$_DBC_cand_statut
													FROM $_DB_candidat, $_DB_cand, $_DB_decisions
												WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
												AND $_DBC_cand_propspec_id='$propspec_id'
												AND $_DBC_cand_decision=$_DBC_decisions_id
												AND $_DBC_cand_periode='$current_periode'
													ORDER by $_DBC_cand_statut, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance");

				$rows=db_num_rows($result);

				print("<font style='font-family: arial;' size='3'>Statut des précandidatures pour la formation : <b>$insc_txt</b></font>
								<br><br>");

				if($rows)
				{
					if($option_infos_complementaires)
						$colspan_cand=2;
					else
						$colspan_cand=1;

					print("<table width='97%' cellpadding='2' cellspacing='0' border='0'>
							<tr>
								<td class='td-gauche fond_menu2' colspan='$colspan_cand'>
									<font class='Texte_menu2'><b>Candidat</b></font>
								</td>
								<td class='td-milieu fond_menu2' align='center'>
									<font class='Texte_menu2'><b>Précandidature</b></font>
								</td>\n");

					if($option_resultat)
					{
						print("<td class='td-milieu fond_menu2' align='center'>
									<font class='Texte_menu2'><b>Commission</b></font>
								</td>\n");

						$colspan=$colspan_cand+2;
					}
					else
						$colspan=$colspan_cand+1;

					print("</tr>
								<tr>
									<td colspan='$colspan' height='15'></td>
								</tr>\n");

					$fond1="fond_menu";
					$fond2="fond_blanc";

					for($i=0; $i<$rows;$i++)
					{
						list($candidat_id, $civilite, $nom, $prenom, $date_naiss, $inid, $decision_id,$decision_texte, $statut)=db_fetch_row($result,$i);
						$naissance=date_fr("j/m/Y",$date_naiss);

						switch($statut)
						{
							case $__PREC_NON_TRAITEE :	// précandidature non traitée
																$statut_txt="<font class='Texteorange'>Non traitée</font>";
																break;

							case $__PREC_PLEIN_DROIT :	// entrée de plein droit
																$statut_txt="<font class='Texte'>Plein droit</font>";
																break;

							case $__PREC_RECEVABLE	:	// précandidature recevable
																$statut_txt="<font class='Textevert'>Recevable</font>";
																break;

							case $__PREC_EN_ATTENTE	:	// précandidature en attente
																$statut_txt="<font class='Texteorange'>En attente</font>";
																break;

							case $__PREC_NON_RECEVABLE	:	// précandidature non recevable
																	$statut_txt="<font class='Texte_important'>Non recevable</font>";
																	break;

							case $__PREC_ANNULEE	:	// précandidature annulée
															$statut_txt="<font class='Textegris'>Annulée</font>";
															break;
						}

						// Affichage du résultat d'admission ?
						if($option_resultat)
						{
							if(isset($annee_courante) && $annee_courante==0)	// années précédentes : couleurs faites pour distinguer les refus des admis
							{
								switch($decision_id)
								{
									case $__DOSSIER_ADMIS	:	$color="#00BB00"; // vert
																		break;

									case $__DOSSIER_ADMISSION_CONFIRMEE	:	$color="#00BB00"; // vert
																		break;

									case $__DOSSIER_ADMIS_RECOURS	:	$color="#00BB00"; // vert
																				break;

									case $__DOSSIER_ADMIS_ENTRETIEN:  $color="#00BB00"; // vert
																					break;

									case $__DOSSIER_ADMIS_LISTE_COMP:  $color="#00BB00"; // vert
																					break;

									case $__DOSSIER_REFUS	:	$color="#CC0000"; // rouge
																		break;

									case $__DOSSIER_DESISTEMENT	:	$color="#CC0000"; // rouge
																				break;

									case $__DOSSIER_REFUS_RECOURS	:	$color="#CC0000"; // rouge
																				break;

									default	:	$color='#FF8800'; // orange
								}
							}
							else // année en cours : couleurs faites pour vérifier les dossiers en cours de traitement
							{
								if($decision_id<0) // pour les dossiers nécessitant encore un traitement
									$color="#FF8800";
								elseif($decision_id>0) // dossiers traités
									$color="#00BB00";
								else
									$color="#CC0000";
							}

							$colonne_resultat="<td class='td-droite $fond1' align='center'>
														<font class='Texte' style='color:$color'>$decision_texte</font>
													</td>";
						}
						else
							$colonne_resultat="";

						if($civilite=="M")
						{
							$civilite="M.";
							$naiss="né le $naissance";
						}
						else
							$naiss="née le $naissance";

						print("<tr>
									<td class='td-gauche $fond1' colspan='$colspan_cand'>
										<a href='edit_candidature.php?cid=$candidat_id' target='_self' class='lien2'>$civilite $nom $prenom, $naiss</a>
									</td>
									<td class='td-milieu $fond1' align='center'>
										$statut_txt
									</td>
									$colonne_resultat
								</tr>\n");


						// Informations complémentaires
						if($option_infos_complementaires)
						{
							$res_infos_comp=db_query($dbr,"SELECT $_DBC_infos_comp_texte, $_DBC_infos_comp_annee, $_DBC_infos_comp_duree
																		FROM $_DB_infos_comp
																	WHERE $_DBC_infos_comp_candidat_id='$candidat_id'
																		ORDER BY $_DBC_infos_comp_annee DESC");

							$rows_infos_comp=db_num_rows($res_infos_comp);

							if($rows_infos_comp)
							{
								print("<tr>
											<td class='td-milieu fond_blanc' colspan='$colspan' align='left'>
												<font class='Texte_menu'><b><i>Informations complémentaires :</b></font>
											</td>
										</tr>\n");

								for($m=0; $m<$rows_infos_comp; $m++)
								{
									list($comp_info,$comp_annee,$comp_duree)=db_fetch_row($res_infos_comp, $m);

									$comp_info=preg_replace("/[\n]+/","<br>",$comp_info);

									$comp_duree=$comp_duree=="" ? "" : "($comp_duree)";

									print("<tr>
												<td class='td-gauche fond_blanc' valign='top'>
													<font class='Texte'>- $comp_annee $comp_duree</font>
												</td>
												<td class='td-milieu fond_blanc' align='justify' valign='top'>
													<font class='Texte'>$comp_info</font>
												</td>
												<td class='td-droite fond_blanc' colspan='".($colspan-2)."'>&nbsp;</td>
											</tr>\n");
								}
							}

							db_free_result($res_infos_comp);
						}

						print("<tr>
									<td colspan='$colspan'>&nbsp;</td>
								</tr>\n");
					}

					print("</table><br><br>");
				}
				else
					print("<font class='Texte'><i>Aucun candidat dans cette formation</i></font><br><br><br>");

				db_free_result($result);
			}

			db_close($dbr);

			print("<div class='centered_icons_box'>
						<a href='tabs_stats.php' target='_self'><img src='$__ICON_DIR/rew_32x32_fond.png' alt='Retour au menu précédent' border='0'></a>
						<a href='stats_filieres_precandidatures.php' target='_self'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour au menu précédent' border='0'></a>
					</div>");
		}
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
