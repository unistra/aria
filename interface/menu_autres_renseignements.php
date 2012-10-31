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
	// Vérifications complémentaires au cas où ce fichier serait appelé directement
	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:composantes.php");
		exit();
	}

	// Nettoyage
	unset($_SESSION["current_choix"]);

	print("<div class='centered_box'>
				<font class='Texte_16'><strong>$onglet - Autres Renseignements Obligatoires</strong></font>
			</div>");

	if(isset($_GET["succes"]) && $_GET["succes"]==1)
		message("Modifications prises en compte.", $__SUCCES);
	
	print("<table border='0' cellspacing='0' cellpadding='0' align='center' width='90%'>
			 <tr>
			 	<td valign='top'>\n");

	$_SESSION["elements_dossier"]=array();

	// La requête est effectuée dans precandidatures.php
	for($i=0; $i<$rows_elems; $i++)
	{
		list($elem_id, $elem_type, $elem_para, $elem_propspec_id, $elem_vap, $elem_annee, $elem_spec, $elem_finalite, $demande_unique)=db_fetch_row($result_elems, $i);

		// Si l'élément est une demande unique et s'il a déjà été demandé, on ne le demande pas une seconde fois
		if($demande_unique=="f" || !in_array($elem_id, $_SESSION['elements_dossier']))
		{
			$nom_finalite=$tab_finalite[$elem_finalite];

			if($demande_unique=="f")
				$elem_formation=$elem_annee=="" ? "<i><u>Cette question concerne la formation suivante :</u></i> $elem_spec $nom_finalite<br>" : "<i><u>Cette question concerne la formation suivante :</u> $elem_annee $elem_spec $nom_finalite</i><br>";
			else
			{
				// Demande globale pour plusieurs formations :
				// On liste les formations concernées, pour que le candidat organise sa réponse

				$res_formations=db_query($dbr, "SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
															FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_dossiers_ef, $_DB_cand
														  WHERE $_DBC_annees_id=$_DBC_propspec_annee
														  AND $_DBC_specs_id=$_DBC_propspec_id_spec
														  AND $_DBC_dossiers_ef_propspec_id=$_DBC_propspec_id
														  AND $_DBC_dossiers_ef_elem_id='$elem_id'
														  AND $_DBC_cand_propspec_id=$_DBC_propspec_id
														  AND $_DBC_cand_candidat_id='$_SESSION[authentifie]'
														  AND $_DBC_cand_periode>='$__PERIODE'
														  AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
														  	ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom, $_DBC_propspec_finalite");

				$num_formations=db_num_rows($res_formations);

				if($num_formations)
				{
					$elem_formation=$num_formations==1 ? "<i><u>Cette question concerne la formation suivante :</u>" : "<i><u>Cette question concerne les $num_formations formations suivantes :</u>";

					for($j=0; $j<$num_formations; $j++)
					{
						list($f_annee, $f_spec, $f_finalite)=db_fetch_row($res_formations, $j);

						$nom_formation=$f_annee=="" ? "$f_spec" : "$f_annee $f_spec";
						$nom_formation.=$tab_finalite[$f_finalite]=="" ? "" : " $tab_finalite[$f_finalite]";

						$elem_formation.="<br>- $nom_formation";
					}
					$elem_formation.="<br></i>";
				}
				else
					$elem_formation="";

				db_free_result($res_formations);
			}

			$elem_para=nl2br($elem_para);

			// On regarde si le candidat a déjà rempli quelque chose :

			if($demande_unique=="t")
				$elem_propspec_id=0;

			$_SESSION["elements_dossier"]["$i"]=$elem_id;

         if($elem_propspec_id==0)
			   $condition="(SELECT max($_DBC_cand_periode) FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$candidat_id'
  				   		    AND $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')) ";
   		else
            $condition="(SELECT max($_DBC_cand_periode) FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$candidat_id'
                                                                       AND ($_DBC_cand_propspec_id='$elem_propspec_id')) ";

			$result=db_query($dbr, "SELECT $_DBC_dossiers_elems_contenu_para FROM $_DB_dossiers_elems_contenu
											WHERE $_DBC_dossiers_elems_contenu_candidat_id='$candidat_id'
											AND $_DBC_dossiers_elems_contenu_elem_id='$elem_id'
											AND $_DBC_dossiers_elems_contenu_comp_id='$_SESSION[comp_id]'
											AND $_DBC_dossiers_elems_contenu_periode=$condition
											AND $_DBC_dossiers_elems_contenu_propspec_id='$elem_propspec_id'");
											
											// AND $_DBC_dossiers_elems_contenu_periode=(SELECT max($_DBC_cand_periode) FROM $_DB_cand WHERE $_DBC_candidat_id='$candidat_id' AND $_DBC_cand_propspec_id='$elem_propspec_id')
											// AND $_DBC_dossiers_elems_contenu_periode='$__PERIODE'

			if(db_num_rows($result))
			{
				list($contenu)=db_fetch_row($result, 0);
				if(!empty($contenu))
				{
					$contenu=nl2br($contenu);
					$elem_rempli=1;
					$icone_statut="button_ok_32x32_menu2.png";
					$font_statut="Textevert";
					$texte_statut="Formulaire<br>complété";
				}
				else
				{
					$elem_rempli=0;
					$icone_statut="messagebox_critical_32x32_menu2.png";
					$font_statut="Texte_important";
					$texte_statut="<strong>Formulaire<br>incomplet</strong>";
				}
			}
			else
			{
				$contenu="";
				$elem_rempli=0;
				$icone_statut="messagebox_critical_32x32_menu2.png";
				$font_statut="Texte_important";
				$texte_statut="<strong>Formulaire<br>incomplet</strong>";
			}

			db_free_result($result);

			$crypt_params=$demande_unique=="f" ? crypt_params("elem_id=$elem_id&elem_propspec=$elem_propspec_id") : crypt_params("elem_id=$elem_id&elem_propspec=0");

			/* CONTENU, EN FONCTION DU TYPE D'ELEMENT */

			$contenu_txt="";

			switch($elem_type)
			{
				case $__ELEM_TYPE_FORM : 	if($contenu!="")
														$contenu_txt=nl2br($contenu);
													break;

				case $__ELEM_TYPE_UN_CHOIX :
						// Traitement du contenu : normalement une seule réponse : id du choix
						$res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_id,$_DBC_dossiers_elems_choix_texte
																FROM $_DB_dossiers_elems_choix
															WHERE $_DBC_dossiers_elems_choix_elem_id='$elem_id'
															ORDER BY $_DBC_dossiers_elems_choix_ordre");
						$nb_choix=db_num_rows($res_choix);

						if($nb_choix)
						{
							for($c=0; $c<$nb_choix; $c++)
							{
								list($choix_id, $choix_texte)=db_fetch_row($res_choix, $c);

								$elem_para.="<br>- $choix_texte\n";

								if($contenu!="" && ctype_digit($contenu) && $choix_id==$contenu)
									$contenu_txt=$choix_texte;
							}
						}

						db_free_result($res_choix);

						break;

				case $__ELEM_TYPE_MULTI_CHOIX :
						// Traitement du contenu : plusieurs réponses possibles séparées par "|" (id du ou des choix)
						$choix_array=explode("|",$contenu);

						// Affichage des choix
						$res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_id, $_DBC_dossiers_elems_choix_texte
																FROM $_DB_dossiers_elems_choix
															WHERE $_DBC_dossiers_elems_choix_elem_id='$elem_id'
															ORDER BY $_DBC_dossiers_elems_choix_ordre");

						$nb_choix=db_num_rows($res_choix);

						if($nb_choix)
						{
							for($c=0; $c<$nb_choix; $c++)
							{
								list($choix_id, $choix_texte)=db_fetch_row($res_choix, $c);

								$elem_para.="<br>- $choix_texte\n";

								if(is_array($choix_array) && in_array($choix_id, $choix_array))
									$contenu_txt.="- $choix_texte<br>\n";
							}
						}

						db_free_result($res_choix);

						break;
			}

			print("<table style='width:100%; padding-bottom:15px;'>\n");

			// if($_SESSION["lock"]==0)

			// Demande individuelle (unique=f) et formation non verrouillée
			// OU
			// Demande globale (unique=t) et au moins une des formations n'est pas verrouillée
			if(($demande_unique=="f" && $_SESSION["array_lock"][$elem_propspec_id]["lock"]==0)
				|| ($demande_unique=="t" && db_num_rows(db_query($dbr,"SELECT * FROM $_DB_cand
																							WHERE $_DBC_cand_candidat_id='$_SESSION[authentifie]'
																							AND $_DBC_cand_periode>='$__PERIODE'
																							AND $_DBC_cand_lock='0'
																							AND $_DBC_cand_propspec_id IN (SELECT $_DBC_dossiers_ef_propspec_id
																																	 FROM $_DB_dossiers_ef
																																	 WHERE $_DBC_dossiers_ef_elem_id='$elem_id')"))))
			{
				print("<tr>
							<td class='td-gauche fond_menu2' style='text-align:center; width:40px; padding:4px 10px 4px 20px;'>
								<a href='renseignements.php?p=$crypt_params' target='_self'><img src='$__ICON_DIR/edit_32x32_menu.png' border='0' alt=''></a>
								<br><a href='renseignements.php?p=$crypt_params' target='_self' class='lien_menu_gauche'>Compléter<br>cette<br>demande</a>
							</td>
							<td class='td-milieu fond_menu2' valign='top' style='text-align:justify; padding:4px 20px 4px 20px; white-space: normal;'>
								<font class='Texte_menu2'>
									$elem_formation
									<strong>$elem_para</strong>
								</font>
							</td>
							<td class='td-droite fond_menu2' style='text-align:center; width:40px; padding:4px 10px 4px 10px;'>
								<font class='$font_statut'><i>$texte_statut</i></font>
								<br><img src='$__ICON_DIR/$icone_statut' border='0' alt=''></a>
							</td>
						</tr>\n");

				if(trim($contenu)!="")
					print("<tr>
								<td class='td-gauche fond_menu' style='width:40px; padding:4px 10px 4px 20px;'></td>
								<td class='td-milieu fond_menu' valign='top' style='text-align:justify; padding:4px 20px 4px 20px; white-space: normal;'>
									<font class='Texte_menu'>$contenu_txt</font>
								</td>
								<td class='td-droite fond_menu' style='width:40px; padding:4px 10px 4px 10px;'></td>
							</tr>\n");
			}
			else
			{
				print("<tr>
							<td class='td-milieu fond_menu2' valign='top' style='text-align:justify; padding:4px 20px 4px 20px; white-space: normal;'>
								<font class='Texte_menu2'>
									$elem_formation
									<strong>$elem_para</strong>
								</font>
							</td>
							<td class='td-droite fond_menu2' style='text-align:center; width:40px; padding:4px 10px 4px 10px;'>
								<font class='$font_statut'><i>$texte_statut</i></font>
								<br><img src='$__ICON_DIR/$icone_statut' border='0' alt=''></a>
							</td>
						</tr>\n");

				if(trim($contenu)!="")
					print("<tr>
								<td class='td-milieu fond_menu' valign='top' style='text-align:justify; padding:4px 20px 4px 20px; white-space: normal;'>
									<font class='Texte_menu'>$contenu_txt</font>
								</td>
								<td class='td-droite fond_menu' style='width:40px; padding:4px 10px 4px 10px;'></td>
							</tr>\n");
			}

			print("</table>\n");
		} // fin du if(demande_unique || ... )
	} // fin de la boucle for()
?>
		</td>
	</tr>
	</table>

	<br>
	</form>
