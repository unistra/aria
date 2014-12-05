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
	// Gestion des filtres entre les formations
	// Exemple : si un candidat a sélectionné la formation X, alors il ne peut pas sélectionner la formation Y

	session_name("preinsc_gestion");
	session_start();

	include "../../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";


	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION['niveau'], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	// paramètre chiffré : identifiant du filtre en cas de modification (activation / désactivation ici)
	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])) && (isset($_GET["a"]) || isset($_GET["d"])))
	{
		if(isset($params["fid"]) && ctype_digit($params["fid"]))
		{
			$fid=$params["fid"];

			if(isset($_GET["a"]) && $_GET["a"]==1)	// on active
				db_query($dbr, "UPDATE $_DB_filtres SET $_DBU_filtres_actif='1' WHERE $_DBU_filtres_id='$fid'");
			elseif(isset($_GET["d"]) && $_GET["d"]==1)	// on désactive
				db_query($dbr, "UPDATE $_DB_filtres SET $_DBU_filtres_actif='0' WHERE $_DBU_filtres_id='$fid'");
		}
	}

	unset($_SESSION["modification"]);
	unset($_SESSION["ajout"]);
	unset($_SESSION["fid"]);
	unset($_SESSION["etape"]);
	unset($_SESSION["filtre_formations_condition_propspec"]);
	unset($_SESSION["filtre_formations_condition_annee"]);
	unset($_SESSION["filtre_formations_condition_mention"]);
	unset($_SESSION["filtre_formations_condition_specialite"]);
	unset($_SESSION["filtre_formations_condition_finalite"]);
	unset($_SESSION["filtre_formations_cible_propspec"]);
	unset($_SESSION["filtre_formations_cible_annee"]);
	unset($_SESSION["filtre_formations_cible_mention"]);
	unset($_SESSION["filtre_formations_cible_specialite"]);
	unset($_SESSION["filtre_formations_cible_finalite"]);
	unset($_SESSION["filtre_formations_nom"]);

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Filtres : exclusions entre formations", "applications-science_32x32_fond.png", 5, "L");

		if(isset($_GET["succes_a"]) && $_GET["succes_a"]==1)
			message("Filtre créé avec succès.", $__SUCCES);
		elseif(isset($_GET["succes_m"]) && $_GET["succes_m"]==1)
			message("Filtre modifié avec succès.", $__SUCCES);

		// L'offre de formation doit être renseignée avant de pouvoir créer des filtres
		if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_annees"))
		|| !db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'"))
		|| !db_num_rows(db_query($dbr, "SELECT * FROM $_DB_mentions WHERE $_DBC_mentions_comp_id='$_SESSION[comp_id]'"))
		|| !db_num_rows(db_query($dbr, "SELECT * FROM $_DB_specs WHERE $_DBC_specs_comp_id='$_SESSION[comp_id]'")))
			message("L'offre de formation est incomplète : vous ne pouvez pas encore ajouter de filtres", $__WARNING);
		else
			print("<div class='centered_box'>
						<a href='filtre.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter' title='[Ajouter un filtre]></a>
						<a href='filtre.php' target='_self' class='lien2'>Ajouter un filtre</a>
					</div>\n");

		// trouver un moyen pour simplifier cette requête (utilisation d'une vue ?)
		
		$result_filtres=db_query($dbr, "SELECT $_DBC_filtres_id, $_DBC_filtres_nom, $_DBC_filtres_cond_propspec_id, $_DBC_filtres_cond_annee_id,
															CASE WHEN $_DBC_filtres_cond_annee_id IN (SELECT $_DBC_annees_id FROM $_DB_annees)
																  THEN (SELECT $_DBC_annees_annee FROM $_DB_annees
																			WHERE $_DBC_annees_id=$_DBC_filtres_cond_annee_id)
																  ELSE '-1'
															END AS cond_annee_nom, $_DBC_filtres_cond_mention_id, 
															CASE WHEN $_DBC_filtres_cond_mention_id IN (SELECT $_DBC_mentions_id FROM $_DB_mentions)
																  THEN (SELECT $_DBC_mentions_nom_court FROM $_DB_mentions
																			WHERE $_DBC_mentions_id=$_DBC_filtres_cond_mention_id)
																  ELSE '-1'
															END AS cond_mention_nom, $_DBC_filtres_cond_spec_id, 
															CASE WHEN $_DBC_filtres_cond_spec_id IN (SELECT $_DBC_specs_id FROM $_DB_specs)
																  THEN (SELECT $_DBC_specs_nom_court FROM $_DB_specs
																			WHERE $_DBC_specs_id=$_DBC_filtres_cond_spec_id)
																  ELSE '-1'
															END AS cond_spec_nom,
															$_DBC_filtres_cond_finalite,
															$_DBC_filtres_cible_propspec_id, $_DBC_filtres_cible_annee_id, 
															CASE WHEN $_DBC_filtres_cible_annee_id IN (SELECT $_DBC_annees_id FROM $_DB_annees)
																  THEN (SELECT $_DBC_annees_annee FROM $_DB_annees
																			WHERE $_DBC_annees_id=$_DBC_filtres_cible_annee_id)
																  ELSE '-1'
															END AS cible_annee_nom, $_DBC_filtres_cible_mention_id, 
															CASE WHEN $_DBC_filtres_cible_mention_id IN (SELECT $_DBC_mentions_id FROM $_DB_mentions)
																  THEN (SELECT $_DBC_mentions_nom_court FROM $_DB_mentions
																			WHERE $_DBC_mentions_id=$_DBC_filtres_cible_mention_id)
																  ELSE '-1'
															END AS cible_mention_nom, $_DBC_filtres_cible_spec_id,
															CASE WHEN $_DBC_filtres_cible_spec_id IN (SELECT $_DBC_specs_id FROM $_DB_specs)
																  THEN (SELECT $_DBC_specs_nom_court FROM $_DB_specs
																			WHERE $_DBC_specs_id=$_DBC_filtres_cible_spec_id)
																  ELSE '-1'
															END AS cible_spec_nom,
															$_DBC_filtres_cible_finalite,
															$_DBC_filtres_actif
												  	FROM $_DB_filtres
													WHERE $_DBC_filtres_comp_id='$_SESSION[comp_id]'
													ORDER BY id DESC");

		$rows_filtres=db_num_rows($result_filtres);

		if(!$rows_filtres)
			message("Aucun filtre n'a encore été défini.", $__INFO);
		else
		{
			message("<center>
							Attention : des filtres mal configurés peuvent bloquer toutes les candidatures.
							<br><strong>Vérifiez bien leur cohérence !</strong>
						</center>", $__WARNING);

			message("- Le caractère * signifie : \"Tous/Toutes ...\".
						<br>- Cliquez sur l'icône de la première colonne pour activer/désactiver un filtre.", $__INFO);

			print("<table cellpadding='2' style='margin-left:auto; margin-right:auto; padding-bottom:30px;'>
					 <tr>
						<td class='fond_menu2' style='text-align:center;'>
							<font class='Texte_menu2'><strong>Actif ?</strong></font>
						</td>
						<td class='fond_menu2' style='text-align:center;'>
							<font class='Texte_menu2'><strong>Nom du filtre</strong></font>
						</td>
						<td rowspan='" . ($rows_filtres+2) . "'></td>
						<td class='fond_menu2' colspan='4'  style='text-align:center;'>
							<font class='Texte_menu2'><strong>Si un candidat a déposé<br>un voeu en ...</strong></font>
						</td>
						<td rowspan='" . ($rows_filtres+2) . "'></td>
						<td class='fond_menu2' colspan='4'  style='text-align:center;'>
							<font class='Texte_menu2'><strong>Alors il ne peut plus<br>déposer de voeux en ...</strong></font>
						</td>
					</tr>
					<tr>
						<td class='fond_menu2'></td>
						<td class='fond_menu2'></td>
						<td class='fond_menu2' style='text-align:center;'>
							<font class='Texte_menu2'><strong>Année</strong></font>
						</td>
						<td class='fond_menu2' style='text-align:center;'>
							<font class='Texte_menu2'><strong>Mention</strong></font>
						</td>
						<td class='fond_menu2' style='text-align:center;'>
							<font class='Texte_menu2'><strong>Spécialité</strong></font>
						</td>
						<td class='fond_menu2' style='text-align:center;'>
							<font class='Texte_menu2'><strong>Finalité</strong></font>
						</td>
						<td class='fond_menu2' style='text-align:center;'>
							<font class='Texte_menu2'><strong>Année</strong></font>
						</td>
						<td class='fond_menu2' style='text-align:center;'>
							<font class='Texte_menu2'><strong>Mention</strong></font>
						</td>
						<td class='fond_menu2' style='text-align:center;'>
							<font class='Texte_menu2'><strong>Spécialité</strong></font>
						</td>
						<td class='fond_menu2' style='text-align:center;'>
							<font class='Texte_menu2'><strong>Finalité</strong></font>
						</td>
						<td class='fond_menu2' style='text-align:center;' colspan='2'></td>
					</tr>\n");

			// on conserve les filtres dans un tableau afin d'éviter
			// la même requête dans les autres pages (suppression notamment)
			$_SESSION["tab_filtres"]=array();

			for($i=0; $i<$rows_filtres; $i++)
			{
				list($filtre_id, $filtre_nom, $filtre_cond_propspec_id, $filtre_cond_annee_id, $filtre_cond_annee, $filtre_cond_mention_id, $filtre_cond_mention,
                 $filtre_cond_spec, $filtre_cond_spec_id, $filtre_cond_finalite, $filtre_cible_propspec_id, $filtre_cible_annee_id, $filtre_cible_annee,
                 $filtre_cible_mention, $filtre_cible_mention_id, $filtre_cible_spec, $filtre_cible_spec_id, $filtre_cible_finalite,
                 $filtre_actif)=db_fetch_row($result_filtres, $i);

				if($filtre_cond_propspec_id!="-1")
				{
					$result=db_query($dbr, "SELECT $_DBC_annees_annee, $_DBC_mentions_nom, $_DBC_specs_nom,
															 $_DBC_propspec_finalite
												FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_specs_mention_id=$_DBC_mentions_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_id='$filtre_cond_propspec_id'
											AND $_DBC_propspec_active='1'");

					$rows=db_num_rows($result);

					list($cond_annee_txt, $cond_mention_txt, $cond_spec_txt, $filtre_cond_finalite)=db_fetch_row($result, 0);

					$cond_finalite_txt=$filtre_cond_finalite=="0" ? "" : $tab_finalite_abbregee[$filtre_cond_finalite];

					db_free_result($result);
				}
				else
				{
					$cond_annee_txt=$filtre_cond_annee=="-1" ? "*" : $filtre_cond_annee;
					$cond_mention_txt=$filtre_cond_mention=="-1" ? "*" : $filtre_cond_mention;
					$cond_spec_txt=$filtre_cond_spec=="-1" ? "*" : $filtre_cond_spec;
					$cond_finalite_txt=$filtre_cond_finalite=="-1" ? "*" : $tab_finalite_abbregee[$filtre_cond_finalite];
				}

				if($filtre_cible_propspec_id!="-1")
				{
					$result=db_query($dbr, "SELECT $_DBC_annees_annee, $_DBC_mentions_nom, $_DBC_specs_nom,
															 $_DBC_propspec_finalite
												FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_specs_mention_id=$_DBC_mentions_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_id='$filtre_cible_propspec_id'
											AND $_DBC_propspec_active='1'");

					$rows=db_num_rows($result);

					list($cible_annee_txt, $cible_mention_txt, $cible_spec_txt, $filtre_cible_finalite)=db_fetch_row($result, 0);

					$cible_finalite_txt=$filtre_cible_finalite=="0" ? "" : $tab_finalite_abbregee[$filtre_cible_finalite];

					db_free_result($result);
				}
				else
				{
					$cible_annee_txt=$filtre_cible_annee=="-1" ? "*" : $filtre_cible_annee;
					$cible_mention_txt=$filtre_cible_mention=="-1" ? "*" : $filtre_cible_mention;
					$cible_spec_txt=$filtre_cible_spec=="-1" ? "*" : $filtre_cible_spec;
					$cible_finalite_txt=$filtre_cible_finalite=="-1" ? "*" : $tab_finalite_abbregee[$filtre_cible_finalite];
				}

				$checked=$filtre_actif ? "checked" : "";

				$crypt_params=crypt_params("fid=$filtre_id");

				
				if($filtre_actif)
					$lien_filtre_actif="<a href='$php_self?p=$crypt_params&d=1' target='_self'><img src='$__ICON_DIR/button_ok_16x16_menu.png' width='16' height='16' border='0' title='Cliquez pour désactiver'></a>";
				else
					$lien_filtre_actif="<a href='$php_self?p=$crypt_params&a=1' target='_self'><img src='$__ICON_DIR/cancel_16x16_menu.png' width='16' height='16' border='0' title='Cliquez pour activer'></a>";

				$_SESSION["tab_filtres"][$filtre_id]=array("nom" => "$filtre_nom",
																		 "cond_propspec_id" => "$filtre_cond_propspec_id", 
																		 "cond_annee" => "$cond_annee_txt",
																		 "cond_annee_id" => "$filtre_cond_annee_id",
																		 "cond_mention" => "$cond_mention_txt",
																		 "cond_mention_id" => "$filtre_cond_mention_id",
																		 "cond_spec" => "$cond_spec_txt",
																		 "cond_spec_id" => "$filtre_cond_spec_id",
																		 "cond_finalite" => "$cond_finalite_txt",
																		 "cond_finalite_id" => "$filtre_cond_finalite",
																		 "cible_propspec_id" => "$filtre_cible_propspec_id",
																		 "cible_annee" => "$cible_annee_txt",
																		 "cible_annee_id" => "$filtre_cible_annee_id",
																		 "cible_mention" => "$cible_mention_txt",
																		 "cible_mention_id" => "$filtre_cible_mention_id",
																		 "cible_spec" => "$cible_spec_txt",
																		 "cible_spec_id" => "$filtre_cible_spec_id",
																		 "cible_finalite" => "$cible_finalite_txt",
																		 "cible_finalite_id" => "$filtre_cible_finalite");

				print("<tr>
							<td class='fond_menu' style='text-align:center;'>$lien_filtre_actif</td>
							<td class='fond_menu' style='text-align:center;'><font class='Texte_menu'>$filtre_nom</font></td>
							<td class='fond_menu' style='text-align:center;'><font class='Texte_menu'>$cond_annee_txt</font></td>
							<td class='fond_menu' style='text-align:center;'><font class='Texte_menu'>$cond_mention_txt</font></td>
							<td class='fond_menu' style='text-align:center;'><font class='Texte_menu'>$cond_spec_txt</font></td>
							<td class='fond_menu' style='text-align:center;'><font class='Texte_menu'>$cond_finalite_txt</font></td>
							<td class='fond_menu' style='text-align:center;'><font class='Texte_menu'>$cible_annee_txt</font></td>
							<td class='fond_menu' style='text-align:center;'><font class='Texte_menu'>$cible_mention_txt</font></td>
							<td class='fond_menu' style='text-align:center;'><font class='Texte_menu'>$cible_spec_txt</font></td>
							<td class='fond_menu' style='text-align:center;'><font class='Texte_menu'>$cible_finalite_txt</font></td>
							<td class='fond_menu' style='text-align:center;'>
								<a href='filtre.php?p=$crypt_params' target='_self'><img src='$__ICON_DIR/edit_16x16_menu.png' border='0' title='[Modifier le filtre]' desc='Modifier' alt='Modifier'></a>
							</td>
							<td class='fond_menu' style='text-align:center;'>
								<a href='suppr_filtre.php?p=$crypt_params' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' border='0' title='[Supprimer le filtre]' desc='Supprimer' alt='Supprimer'></a>
							</td>
						</tr>\n");
			}

			print("</table>\n");
		}
	
		db_free_result($result_filtres);
		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
