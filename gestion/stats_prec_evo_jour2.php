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

			if(ctype_digit($propspec_id))
				$resultat=1;
			else
				$erreur_selection=1;

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
		titre_page_icone("Evolution journalière du nombre de précandidatures", "kpercentage_32x32_fond.png", 15, "L");

		// message("Seules les précandidatures <b>recevables</b> sont prises en compte dans ces listes.", $__INFO);

		if(isset($erreur_selection))
			message("Erreur : vous devez sélectionner une formation valide dans la liste.", $__ERREUR);

		if(!isset($resultat))
		{
         message("- La présentation sous forme d'histogramme est uniquement valable lorsqu'une seule formation est sélectionnée
                 <br>- Attention à la cohérence entre le choix de l'année universitaire et les dates limites !", $__INFO); 		   
		   
			print("<form action='$php_self' method='POST'>\n
						<input type='hidden' name='act' value='1'>\n");
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2' colspan='2' style='padding:4px;'>
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
					
					$result=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_id, $_DBC_specs_mention_id,
															$_DBC_mentions_nom, $_DBC_propspec_finalite
														FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
													WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_mentions_id=$_DBC_specs_mention_id
													AND $_DBC_propspec_annee=$_DBC_annees_id
													AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
													$requete_droits_formations
														ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom, $_DBC_propspec_finalite");
					$rows=db_num_rows($result);

					// variables initialisées à n'importe quoi
					$prev_annee="--";
					$prev_mention="";

					// TODO : dans la base compeda, revoir l'utilisation de la table annee (intégration de annees.id dans
					// proprietes_specialites, par exemple) et répercuter les changements ici
					for($i=0; $i<$rows; $i++)
					{
						list($annee, $nom,$propspec_id, $mention, $mention_nom, $finalite)=db_fetch_row($result,$i);

						$nom_finalite=$tab_finalite[$finalite];

						if($annee!=$prev_annee)
						{
							if($i!=0)
								print("</optgroup>\n");

							if(empty($annee))
								print("<optgroup label='Années particulières'>\n");
							else
								print("<optgroup label='$annee'>\n");

							$prev_annee=$annee;
							$prev_mention="";
						}

						if($prev_mention!=$mention)
							print("<option value='' label='' disabled>-- $mention_nom --</option>\n");

						if(isset($formation) && $formation==$propspec_id)
							$selected="selected=1";
						else
							$selected="";

						print("<option value='$propspec_id' label=\"$nom $nom_finalite\" $selected>$nom $nom_finalite</option>\n");

						$prev_mention=$mention;
					}

					db_free_result($result);
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Début</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'> 
			   <?php
			      $cur_jour_debut=isset($jour_debut) ? $jour_debut : "";
               $cur_mois_debut=isset($mois_debut) ? $mois_debut : "";
               $cur_annee_debut=isset($annee_debut) ? $annee_debut : "";
               
			      print("JJ :&nbsp;<input type='text' name='jour_debut' maxlength='2' size='4' value='$cur_jour_debut'>&nbsp;
				  			 MM :&nbsp;<input type='text' name='mois_debut' maxlength='2' size='4' value='$cur_mois_debut'>&nbsp;
				  			 AAAA :&nbsp;<input type='text' name='annee_debut' maxlength='4' size='6' value='$cur_annee_debut'>");
			   ?>
			</font>
		</td>
	</tr>
	<tr>
	   <td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Fin</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'> 
            <?php
               $cur_jour_fin=isset($jour_fin) ? $jour_fin : date("d", time());
               $cur_mois_fin=isset($mois_fin) ? $mois_fin : date("m", time());
               $cur_annee_fin=isset($annee_fin) ? $annee_fin : date("Y", time());
               
			      print("JJ :&nbsp;<input type='text' name='jour_fin' maxlength='2' size='4' value='$cur_jour_fin'>&nbsp;
				  			 MM :&nbsp;<input type='text' name='mois_fin' maxlength='2' size='4' value='$cur_mois_fin'>&nbsp;
				  			 AAAA :&nbsp;<input type='text' name='annee_fin' maxlength='4' size='6' value='$cur_annee_fin'>");
			   ?>						
			</font>			
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Année universitaire</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='periode' size='1'>
				<?php
				   $selected=($cur_periode==0) ? "selected='1'" : "";
				   
				   print("<option value='0' $selected>Indifférente</option>");
				   			  
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
							$selected=($i==$current_periode) ? "selected='1'" : "";
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
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Choix de l'échelle</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='echelle' size='1'>
				<?php
				   if(isset($cur_echelle))
				     $echelle=$cur_echelle;
               else
                 $echelle="w";
                 
               $d_selected=$w_selected=$y_selected="";                 
                 
               switch($echelle)
               {
                  case "d" : // jour
                             $d_selected="selected='1'";
                             break;   
                             
                  case "w" : // semaine
                             $w_selected="selected='1'";
                             break;
                             
                  case "y" : // année
                             $y_selected="selected='1'";
                             break;
               }
               
               print("<option value='d' $d_selected>Jours</option>
                      <option value='w' $w_selected>Semaines</option>
                      <option value='y' $y_selected>Années</option>\n");
              
				?>
			</select>
		</td>
	</tr>
	</table>
	
	<div class='centered_icons_box'>
		<a href='tabs_stats.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour au menu précédent' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Résultats" name="go" value="Résultats">
		</form>
	</div>

	<?php
		}
		else // résultat de la recherche
		{
			print("<center>");

			if(isset($resultat) && $resultat==1)
			{
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
				if($nom_annee=="")
					$insc_txt="$spec_nom";
				else
					$insc_txt="$nom_annee - $spec_nom";
*/
				/*
				===========================================
								TRI PAR NOM AVEC CURSUS
				===========================================
				*/
				if($classement=="nom_cursus")
				{
					$result=db_query($dbr,"SELECT DISTINCT $_DBC_cand_candidat_id, $_DBC_cand_id, $_DBC_candidat_civilite, $_DBC_candidat_nom,
																		$_DBC_candidat_prenom, $_DBC_candidat_date_naissance, $_DBC_decisions_id,
																		$_DBC_decisions_texte
														FROM $_DB_candidat ,$_DB_cand, $_DB_decisions
													WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
													AND $_DBC_cand_propspec_id='$propspec_id'
													AND $_DBC_cand_decision = $_DBC_decisions_id
													AND $_DBC_cand_statut='$__PREC_RECEVABLE'
													AND $_DBC_cand_periode='$current_periode'
														ORDER BY $_DBC_candidat_nom ASC");

					$rows=db_num_rows($result);

					print("<font style='font-family: arial;' size='3'>Liste des candidats pour la formation : <b>$insc_txt $tab_finalite[$finalite]</b>, triés par nom, avec cursus ($rows)</font>
					<br><br><hr><br>");

					if($rows)
					{
						print("<table width='97%' cellpadding='2' cellspacing='0' border='0'>\n");

						for($i=0; $i<$rows; $i++)
						{
							list($candidat_id,$inid,$civilite,$nom,$prenom,$date_naiss,$decision_id, $decision_texte)=db_fetch_row($result,$i);

							// Affichage du résultat d'admission ?
							if($option_resultat)
							{
								if(isset($annee_courante) && $annee_courante==0)	// années précédentes : couleurs faites pour distinguer les refus des admis
								{
									switch($decision_id)
									{
										case $__DOSSIER_ADMIS_ENTRETIEN:	$color="#00BB00"; // vert
																					break;

										case $__DOSSIER_ADMIS_LISTE_COMP:	$color="#00BB00"; // vert
																						break;

										case $__DOSSIER_ADMIS	:	$color="#00BB00"; // vert
																			break;
																			
										case $__DOSSIER_ADMISSION_CONFIRMEE	:	$color="#00BB00"; // vert
																			break;

										case $__DOSSIER_ADMIS_RECOURS	:	$color="#00BB00"; // vert
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

								$colonne_resultat="<td class='td-droite fond_menu' align='center'>
															<font class='Texte_menu' style='color:$color'>$decision_texte</font>
														</td>";

								$colspan='3';
							}
							else
							{
								$colonne_resultat="";
								$colspan='2';
							}

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
											<a href='edit_candidature.php?cid=$candidat_id' target='_self' class='lien2'>$civilite $nom $prenom, $naiss</a>
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
											<td class='td-gauche $fond1' valign='top'>
												<font style='font-family: arial;' size='2'>- $annee_obtention</font>
											</td>
											<td class='td-milieu $fond1' valign='top'>
												<font class='Texte'>
													$diplome $mention_txt
													<br><i>$lieu_txt, $pays</i>
												</font>
											</td>\n");

								if($option_resultat) // ajout d'une colonne vide
									print("<td class='td-droite $fond1'>&nbsp;</td>\n");

								print("</tr>\n");

								switch_vals($fond1, $fond2);
							}
							db_free_result($result2);

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
					$result=db_query($dbr,"SELECT DISTINCT $_DBC_cand_candidat_id, $_DBC_cand_id, $_DBC_candidat_civilite, $_DBC_candidat_nom,
																		$_DBC_candidat_prenom, $_DBC_candidat_date_naissance, $_DBC_decisions_id, $_DBC_decisions_texte
													FROM $_DB_candidat, $_DB_cand, $_DB_decisions
												WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
												AND $_DBC_cand_decision=$_DBC_decisions_id
												AND $_DBC_cand_propspec_id='$propspec_id'
												AND $_DBC_cand_statut='$__PREC_RECEVABLE'
												AND $_DBC_cand_periode='$current_periode'
													ORDER BY $_DBC_candidat_nom ASC");

					$rows=db_num_rows($result);

					print("<font style='font-family: arial;' size='3'>Liste des candidats pour la formation : <b>$insc_txt $tab_finalite[$finalite]</b>, triés par nom ($rows)</font>
								<br><br><hr><br>");

					if($rows)
					{
						print("<table width='97%' cellpadding='6' cellspacing='0' border='0'>
									<tr>\n");

						$fond1="fond_menu";
						$fond1='fond_blanc';

						for($i=0; $i<$rows; $i++)
						{
							list($candidat_id,$inid,$civilite,$nom,$prenom,$date_naiss,$decision_id,$decision_texte)=db_fetch_row($result,$i);

							// Affichage du résultat d'admission ?
							if($option_resultat)
							{
								if(isset($annee_courante) && $annee_courante==0)	// années précédentes : couleurs faites pour distinguer les refus des admis
								{
									switch($decision_id)
									{
										case $__DOSSIER_ADMIS_ENTRETIEN: $color="#00BB00"; // vert
																					break;

										case $__DOSSIER_ADMIS_LISTE_COMP:	$color="#00BB00"; // vert
																						break;

										case $__DOSSIER_ADMIS	:	$color="#00BB00"; // vert
																			break;

										case $__DOSSIER_ADMISSION_CONFIRMEE	:	$color="#00BB00"; // vert
																			break;

										case $__DOSSIER_ADMIS_RECOURS	:	$color="#00BB00"; // vert
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

							$naissance=date_fr("j/m/Y",$date_naiss);

							if($civilite=="M")
							{
								$civilite="M.";
								$naiss="né le $naissance";
							}
							else
								$naiss="née le $naissance";

							print("<td class='td-gauche $fond1'>
										<a href='edit_candidature.php?cid=$candidat_id' target='_self' class='lien2'>$civilite $nom $prenom, $naiss</a>
									</td>
									$colonne_resultat
								</tr>\n");

							switch_vals($fond1, $fond2);
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
				========================================================
							TRI PAR RESULTAT (STATUT) OU PAR LIEU
				========================================================
				*/
				elseif($classement=="diplome" || $classement=="lieu")
				{
					$dbr=db_connect();
					$result=db_query($dbr,"SELECT $_DBC_cand_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom,
															$_DBC_candidat_date_naissance, $_DBC_candidat_civilite, $_DBC_decisions_id,
															$_DBC_decisions_texte
														FROM $_DB_cand, $_DB_candidat, $_DB_decisions
													WHERE $_DBC_cand_candidat_id=$_DBC_candidat_id
													AND $_DBC_cand_propspec_id='$propspec_id'
													AND $_DBC_cand_decision=$_DBC_decisions_id
													AND $_DBC_cand_statut='$__PREC_RECEVABLE'
													AND $_DBC_cand_periode='$current_periode'");

					$rows=db_num_rows($result);

					$etudiants=array();

					if($rows)
					{
						for($i=0;$i<$rows;$i++)
						{
							list($candidat_id,$inid,$nom,$prenom,$date_naiss,$civilite, $decision_id, $decision_texte)=db_fetch_row($result,$i);

							$naissance=date_fr("j/m/Y",$date_naiss);

							$etudiants[$i]=array();
							$etudiants[$i]['nom']=$nom;
							$etudiants[$i]['prenom']=$prenom;
							$etudiants[$i]['civilite']=$civilite;
							$etudiants[$i]['naissance']=$naissance;
							$etudiants[$i]['id']=$candidat_id;
							$etudiants[$i]['decision_id']=$decision_id;
							$etudiants[$i]['decision_texte']=$decision_texte;
							$etudiants[$i]['cursus']=array();

							$result2=db_query($dbr,"(SELECT $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_annee,
																		$_DBC_cursus_mention, $_DBC_cursus_ecole, $_DBC_cursus_ville, 
																		CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
																			THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
																			ELSE '' END as cursus_pays
																FROM $_DB_cursus
															WHERE $_DBC_cursus_candidat_id='$candidat_id'
															AND $_DBC_cursus_annee='0'
																ORDER BY $_DBC_cursus_diplome, $_DBC_cursus_intitule)
													UNION ALL
															(SELECT $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_annee,
																		$_DBC_cursus_mention, $_DBC_cursus_ecole, $_DBC_cursus_ville, 
																		CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
																			THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
																			ELSE '' END as cursus_pays
																FROM $_DB_cursus
															WHERE $_DBC_cursus_candidat_id='$candidat_id'
															AND $_DBC_cursus_annee!='0'
																ORDER BY $_DBC_cursus_annee DESC, $_DBC_cursus_diplome, $_DBC_cursus_intitule)");

							$rows2=db_num_rows($result2);

							if($rows2)
							{
								// $flag_0=0;
								for($j=0;$j<$rows2;$j++)
								{
									list($cursus_diplome,$cursus_intitule,$cursus_spec,$cursus_annee_obtention, $cursus_mention, $cursus_ecole, $cursus_ville, $cursus_pays)=db_fetch_row($result2,$j);

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

						print("<font style='font-family: arial;' size='3'>Liste des candidats pour la formation : <b>$insc_txt $tab_finalite[$finalite]</b>, triés par $tri ($rows)</font>
								<br><br>
								<hr>
								<br>\n");

						if($bool==FALSE)
							print("<center>
										<i><font class='Texte'>Attention : tri non effectué</font></i>
									</center>\n");

						print("<table width='97%' cellpadding='2' cellspacing='0' border='0'>\n");

						$cnt=count($etudiants);

						// Boucle sur le tableau d'étudiants
						for($k=0;$k<$cnt;$k++)
						{
							$civ=$etudiants[$k]['civilite'];
							$date_naiss=$etudiants[$k]['naissance'];
							$decision_id=$etudiants[$k]['decision_id'];
							$decision_texte=$etudiants[$k]['decision_texte'];

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

										case $__DOSSIER_ADMIS_LISTE_COMP:	$color="#00BB00"; // vert
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

								$colonne_resultat="<td class='td-droite fond_menu' align='center'>
															<font class='Texte_menu' style='color:$color'>$decision_texte</font>
														</td>";

								$colspan='3';
							}
							else
							{
								$colonne_resultat="";
								$colspan='2';
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
										$colonne_resultat
									</tr>\n");

							$cnt2=count($etudiants[$k]['cursus']);

							$fond1='fond_blanc';
							$fond2="fond_page";

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
												<font class='Texte'>- $ann</font>
											</td>
											<td class='td-milieu $fond1' valign='top'>
												<font class='Texte'>
													$diplome $mention_txt
													<br><i>$lieu_txt, $pays</i>
												</font>
											</td>\n");

								if($option_resultat) // Ajout d'une ligne vide si nécessaire
									print("<td class='$fond1'>&nbsp;</td>");

								print("</tr>\n");

								switch_vals($fond1, $fond2);
							}

							print("<tr>
										<td colspan='$colspan'>&nbsp;</td>
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
						Tri par retour du talon réponse
				===========================================
				*/
				elseif($classement=="talon_reponse") // pour les 'admis' et 'admis sous réserve'
				{
					$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom,
															$_DBC_cand_id, $_DBC_decisions_id, $_DBC_decisions_texte, $_DBC_cand_talon_reponse
														FROM $_DB_candidat, $_DB_cand, $_DB_decisions
													WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
													AND $_DBC_cand_propspec_id='$propspec_id'
													AND $_DBC_cand_decision=$_DBC_decisions_id
													AND $_DBC_cand_statut='$__PREC_RECEVABLE'
													AND ($_DBC_cand_decision='$__DOSSIER_ADMIS' OR $_DBC_cand_decision='$__DOSSIER_ADMIS_AVANT_CONFIRMATION' OR $_DBC_cand_decision='$__DOSSIER_ADMISSION_CONFIRMEE' 
														  OR $_DBC_cand_decision='$__DOSSIER_ADMIS_RECOURS' OR $_DBC_cand_decision='$__DOSSIER_SOUS_RESERVE')
													AND $_DBC_cand_periode='$current_periode'
														ORDER BY	abs($_DBC_cand_talon_reponse) DESC,
																				$_DBC_candidat_nom ASC,
																				$_DBC_candidat_prenom ASC");

					$rows=db_num_rows($result);

					print("<font style='font-family: arial;' size='3'>Formation : <b>$insc_txt $tab_finalite[$finalite]</b>, décision des candidats</font>
							<br><br>
							<hr>
							<br>");

					if($rows)
					{
						print("<table width='97%' cellpadding='4' cellspacing='0' border='0'>
								<tr>
									<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Candidat</b></td>
									<td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Statut</b></td>
									<td class='td-droite fond_menu2'><font class='Texte_menu2'><b>Décision du candidat</b></td>
								</tr>
								<tr>
									<td colspan='3' height='5'></td>
								</tr>\n");

						for($i=0; $i<$rows;$i++)
						{
							list($candidat_id, $civilite, $nom, $prenom, $inid, $decision_id,$decision_texte,$talon_reponse)=db_fetch_row($result,$i);

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

									case $__DOSSIER_ADMIS_LISTE_COMP:  $color="#00BB00"; // vert
																					break;

									case $__DOSSIER_REFUS	:	$color="#CC0000"; // rouge
																		break;

									case $__DOSSIER_DESISTEMENT	:	$color="#CC0000"; // rouge
																				break;

									case $__DOSSIER_REFUS_RECOURS	:	$color="#CC0000"; // rouge
																				break;

									default	:	$color='#FF8800';
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

							if($civilite=="M")
								$civilite="M.";

							switch($talon_reponse)
							{
								case 0	:	// talon non renvoyé (par défaut)
												$talon_txt="Réponse non renvoyée";
												break;

								case 1	:	// talon renvoyé, inscription confirmée
												$talon_txt="Admission confirmée";
												break;

								case -1	:	// talon renvoyé, inscription refusée
												$talon_txt="Admission refusée";
												break;

								default : // talon non renvoyé (par défaut)
												$talon_txt="Réponse non renvoyée";
												break;
							}

							if(isset($old_talon_reponse)) // séparation si statut du talon réponse différent
							{
								if($talon_reponse!=$old_talon_reponse)
								{
									$old_talon_reponse=$talon_reponse;
									print("<tr>
												<td colspan='2' height='5'>&nbsp;</td>
											</tr>");
								}
							}
							else
								$old_talon_reponse=$talon_reponse;

							if($i%2)
								$fond="fond_blanc";
							else
								$fond="fond_menu";

							print("<tr>
										<td class='td-gauche $fond'>
											<a href='edit_candidature.php?cid=$candidat_id' target='_self' class='lien2'>$civilite $nom $prenom</a>
										</td>
										<td class='td-milieu $fond'><font class='Texte' style='color:$color'>$decision_texte</font></td>
										<td class='td-droite $fond'><font class='Texte'>$talon_txt</font></td>
									</tr>\n");
						}

						print("</table>
								 <br><br>\n");
					}
					else
						print("<font class='Texte'><i>Aucun candidat dans cette formation</i></font>
								 <br><br><br>");

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
															$_DBC_candidat_date_naissance, $_DBC_cand_id, $_DBC_decisions_id, $_DBC_decisions_texte
														FROM $_DB_candidat, $_DB_cand, $_DB_decisions
													WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
													AND $_DBC_cand_propspec_id='$propspec_id'
													AND $_DBC_cand_decision=$_DBC_decisions_id
													AND $_DBC_cand_statut='$__PREC_RECEVABLE'
													AND $_DBC_cand_periode='$current_periode'
														ORDER by $_DBC_cand_decision, $_DBC_candidat_nom");

					$rows=db_num_rows($result);

					print("<font style='font-family: arial;' size='3'>Statut des dossiers pour la formation : <b>$insc_txt $tab_finalite[$finalite]</b></font>
							<br><br>
							<hr>
							<br>
							<table width='97%' cellpadding='4' cellspacing='0' border='0'>
							<tr>
								<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Candidat</b></td>
								<td class='td-droite fond_menu2'><font class='Texte_menu2'><b>Statut</b></td>
							</tr>
							<tr>
								<td colspan='2' height='5'></td>
							</tr>\n");

					if($rows)
					{
						for($i=0; $i<$rows;$i++)
						{
							list($candidat_id, $civilite, $nom, $prenom, $date_naiss, $inid, $decision_id,$decision_texte)=db_fetch_row($result,$i);
							$naissance=date_fr("j/m/Y",$date_naiss);

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

									case $__DOSSIER_ADMIS_LISTE_COMP:	$color="#00BB00"; // vert
																					break;

									case $__DOSSIER_REFUS	:	$color="#CC0000"; // rouge
																		break;

									case $__DOSSIER_DESISTEMENT	:	$color="#CC0000"; // rouge
																				break;

									case $__DOSSIER_REFUS_RECOURS	:	$color="#CC0000"; // rouge
																				break;

									default	:	$color='#FF8800';
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

							$decision="<font class='Texte' style='color:$color'>$decision_texte</font>";

							if($civilite=="M")
							{
								$civilite="M.";
								$naiss="né le $naissance";
							}
							else
								$naiss="née le $naissance";

							print("<tr>
										<td class='td-gauche'>
											<a href='edit_candidature.php?cid=$candidat_id' target='_self' class='lien2'>$civilite $nom $prenom, $naiss</a>
										</td>
										<td class='td-droite'>$decision</td>
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
							<a href='tabs_stats.php' target='_self' class='lien2'><img src='$__ICON_DIR/rew_32x32_fond.png' alt='Retour au menu précédent' border='0'></a>
							<a href='stats_filieres_compeda.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour au menu précédent' border='0'></a>
						 </div>\n");
			}
		}
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
