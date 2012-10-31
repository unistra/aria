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
	session_name("preinsc");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		// header("Location:../index.php");
		header("Location:" . base_url($php_self) . "../index.php");
		exit();
	}

	if($_SESSION["civilite"]=="M")
		$perdu="perdu";
	else
		$perdu="perdue";

	$dbr=db_connect();

	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
	{
		if(isset($params["co"]) && ctype_digit($params["co"]))
			$selected_comp_id=$params["co"];

		if(isset($params["d"]) && ctype_digit($params["d"]))
			$expand=$params["d"];

		if(isset($selected_comp_id) && ctype_digit($selected_comp_id) && !isset($expand))
		{
			// Récupération des paramètres propres à cette composante
			$dbr=db_connect();

			$res_composante=db_query($dbr, "SELECT $_DBC_composantes_nom, $_DBC_universites_nom, $_DBC_universites_img_dir,
																$_DBC_universites_id, $_DBC_universites_css,
																$_DBC_composantes_courriel_scol, $_DBC_composantes_limite_cand_nombre,
																$_DBC_composantes_limite_cand_annee, $_DBC_composantes_limite_cand_annee_mention,
																$_DBC_composantes_affichage_decisions
															FROM $_DB_composantes, $_DB_universites
														WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
														AND $_DBC_composantes_id='$selected_comp_id'");

			if(!db_num_rows($res_composante))
			{
				db_close($dbr);
				session_write_close();
				header("Location:" . base_url($php_self) . "../index.php");
				exit();
			}

			list($_SESSION["composante"],
					$_SESSION["universite"],
					$_SESSION["img_dir"],
					$_SESSION["univ_id"],
					$_SESSION["css"],
					$_SESSION["courriel_scol"],
					$_SESSION["limite_nombre"],
					$_SESSION["limite_annee"],
					$_SESSION["limite_annee_mention"],
					$_SESSION["affichage_decisions"])=db_fetch_row($res_composante, 0);

			$_SESSION["onglet"]=0;

			db_free_result($res_composante);

			$res_comp_infos=db_query($dbr,"SELECT $_DBC_comp_infos_id FROM $_DB_comp_infos WHERE $_DBC_comp_infos_comp_id='$selected_comp_id'");

			if(db_num_rows($res_comp_infos))
			{
				list($comp_infos_id)=db_fetch_row($res_comp_infos, 0);

				if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_comp_infos_encadre WHERE $_DBC_comp_infos_encadre_info_id='$comp_infos_id'"))
					|| db_num_rows(db_query($dbr,"SELECT * FROM $_DB_comp_infos_para WHERE $_DBC_comp_infos_para_info_id='$comp_infos_id'"))
					|| db_num_rows(db_query($dbr,"SELECT * FROM $_DB_comp_infos_fichiers WHERE $_DBC_comp_infos_fichiers_info_id='$comp_infos_id'")))
					$location="info_comp.php";
				else
					$location="precandidatures.php";
			}
			else
				$location="precandidatures.php";

			db_free_result($res_comp_infos);

			$_SESSION["comp_id"]=$selected_comp_id;

			db_close($dbr);

			session_write_close();
			header("Location:" . base_url($php_self) . "$location");
			exit();
		}
	}
	
	en_tete_candidat();

	if(isset($_SESSION["comp_id"]))
		menu_sup_candidat($__MENU_COMP);
	else
		menu_sup_simple();;
?>

<div class='main'>
	<?php
		titre_page_icone("Sélection de la composante dans laquelle vous souhaitez déposer un dossier", "composante_32x32_fond.png", 15, "L");

		message("<center>
						<b>Cliquez sur le nom de la composante pour la sélectionner</b> <i>(Entre parenthèses : nombre de formations déjà choisies dans la composante)</i>
						<br>Si vous souhaitez déposer des dossiers dans plusieurs universités / composantes, <b>vous pourrez toujours revenir sur cette page par la suite</b>.
					</center>", $__INFO);

		$result=db_query($dbr, "SELECT $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_univ_id, $_DBC_universites_nom,
												 (SELECT count(*) FROM $_DB_cand, $_DB_propspec 
																WHERE $_DBC_propspec_id=$_DBC_cand_propspec_id
																AND $_DBC_propspec_comp_id=$_DBC_composantes_id
																AND $_DBC_cand_candidat_id='$_SESSION[authentifie]'
																AND $_DBC_cand_periode='$__PERIODE')
												 AS nb_candidatures
											FROM $_DB_composantes, $_DB_universites
										WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
										ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");

		$rows_fermee=db_num_rows($result);

		if($rows_fermee)
		{
			$old_univ="";

			print("<table border='0' style='padding-bottom:20px; margin-left:auto; margin-right:auto;'>\n");

			for($i=0; $i<$rows_fermee; $i++)
			{
				list($comp_id, $comp_nom, $comp_univ_id, $univ_nom, $nb)=db_fetch_row($result,$i);

				if($comp_univ_id!=$old_univ)
				{
					if($i)
						print("<tr>
									<td colspan='4' height='10'></td>
								</tr>\n");

					print("<tr>
								<td class='td-gauche fond_menu2' colspan='4' nowrap>
									<font class='Texte_menu2'><b>$univ_nom</b></font>
								</td>
							</tr>\n");

					$old_univ=$comp_univ_id;
				}

				// Nombre de voeux dans cette composante
				$nb_txt=$nb ? "(<b>$nb</b>) " : "-";

				$crypt_params=crypt_params("co=$comp_id");

				if(isset($expand) && $expand==$comp_id)
				{
					$crypt_params2=crypt_params("co=$comp_id&d=0");

					print("<tr>
								<td style='padding-right:10px' width='14'>
									<a href='$php_self?p=$crypt_params2' target='_self'><img src='$__ICON_DIR/moins_11x11.png' width='11' border='0' title='Voir les détails' desc='Détails'></a>
								</td>
								<td align='left' style='text-align:left; padding-right:10px'>
									<a href='$php_self?p=$crypt_params' class='lien_bleu_12' target='_self'><b>$comp_nom</b></a>
								</td>
								<td style='padding-right:10px; padding-left:10px; text-align:center;'>
									<font class='Texte_menu'>$nb_txt</font>
								</td>
								<td align='right'></td>
							</tr>\n");

					$old_annee="===="; // on initialise à n'importe quoi (sauf année existante et valeur vide)
					$old_propspec_id="";
					$old_mention="--";

					$current_session=1; // par défaut

					// Nombre de sessions, pour l'affichage
					$res_nb_sessions=db_query($dbr, "SELECT count(*) FROM $_DB_session, $_DB_propspec
																	WHERE $_DBC_propspec_id=$_DBC_session_propspec_id
																	AND $_DBC_propspec_comp_id='$comp_id'
																	AND $_DBC_session_periode='$__PERIODE'
																	AND $_DBC_propspec_active='1'
																GROUP BY $_DBC_session_propspec_id
																ORDER BY count DESC
																LIMIT 1");

					if(db_num_rows($res_nb_sessions))
						list($max_session)=db_fetch_row($res_nb_sessions, 0);
					else
						$max_session=0;

					// Même requête, pour l'année universitaire suivante
					$res_nb_sessions=db_query($dbr, "SELECT count(*) FROM $_DB_session, $_DB_propspec
																WHERE $_DBC_propspec_id=$_DBC_session_propspec_id
																AND $_DBC_propspec_comp_id='$comp_id'
																AND $_DBC_session_periode='".($__PERIODE+1)."'
																AND $_DBC_propspec_active='1'
																GROUP BY $_DBC_session_propspec_id
																ORDER BY count DESC
																LIMIT 1");

					if(db_num_rows($res_nb_sessions))
						list($max_session_next_periode)=db_fetch_row($res_nb_sessions, 0);
					else
						$max_session_next_periode=0;

					db_free_result($res_nb_sessions);

					$res_details=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_nom,
																	$_DBC_propspec_finalite, $_DBC_mentions_nom, $_DBC_session_id,
																	$_DBC_session_ouverture, $_DBC_session_fermeture, $_DBC_session_periode
																FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_session, $_DB_mentions
															WHERE $_DBC_propspec_annee=$_DBC_annees_id
															AND $_DBC_propspec_id_spec=$_DBC_specs_id
															AND $_DBC_propspec_id=$_DBC_session_propspec_id
															AND $_DBC_specs_mention_id=$_DBC_mentions_id
															AND $_DBC_propspec_comp_id='$comp_id'
															AND $_DBC_session_periode>='$__PERIODE'
															AND $_DBC_propspec_active='1'
																ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom, $_DBC_propspec_finalite,
																			$_DBC_propspec_id, $_DBC_session_ouverture, $_DBC_session_fermeture");

					$rows_res_details=db_num_rows($res_details);

					$array_dates_propspec=array();

					$old_propspec_id="";

					for($j=0; $j<$rows_res_details; $j++)
					{
						list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $session_id, $s_ouverture,
							  $s_fermeture, $s_periode)=db_fetch_row($res_details, $j);

						if($propspec_id!=$old_propspec_id)
						{
							$array_dates_propspec[$propspec_id]=array();
							$array_dates_propspec[$propspec_id]["dates"]=array();
							$cnt_session=1;
						}
						else
							$cnt_session++;

						$nom_finalite=$tab_finalite[$finalite];

						if($s_ouverture!=0 && $s_fermeture!=0)
						{
							$date_ouv_txt=date("Y")==date("Y", $s_ouverture) ? date_fr("j F", $s_ouverture) : date_fr("j M Y", $s_ouverture);
							$date_ferm_txt=date("Y")==date("Y", $s_fermeture) ? date_fr("j F", $s_fermeture) : date_fr("j M Y", $s_fermeture);

							if(time() >= $s_ouverture && time() <= $s_fermeture)
								$session_font_class="Textevert_10";
							else
								$session_font_class="Texte_important_10";

							$array_dates_propspec[$propspec_id]["dates"][$cnt_session]=array("dates" => "$date_ouv_txt - $date_ferm_txt",
																												  "font_class" => "$session_font_class",
																												  "periode" => "$s_periode");
						}

						$array_dates_propspec[$propspec_id]["formation"]=$annee=="" ? "" : "$annee ";
						$array_dates_propspec[$propspec_id]["formation"].="$spec_nom $nom_finalite";

						$old_propspec_id=$propspec_id;
					}

					print("<tr>
								<td style='padding-right:10px'></td>
								<td colspan='3'>
									<table border='0' cellpadding='0' cellspacing='1' align='left'>
									<tr>
										<td></td>\n");

					if($max_session)
						print("<td class='td-complet fond_menu' align='center' colspan='$max_session'>
									<font class='Texte_menu_10'><b>$__PERIODE-".($__PERIODE+1)."</b></font>
								 </td>");

					if($max_session_next_periode)				
						print("<td class='td-complet fond_menu' align='center' colspan='$max_session_next_periode'>
									<font class='Texte_menu_10'><b>".($__PERIODE+1)."-".($__PERIODE+2)."</b></font>
								 </td>");

					print("</tr>
							 <tr>
								<td>
									<font class='Texte_10'><b><u>Formations</u></b></font>
								</td>\n");

					for($n_session=1; $n_session<=($max_session+$max_session_next_periode); $n_session++)
					{
						$n_reel=($n_session>$max_session) ? $n_session-$max_session : $n_session;

						print("<td align='center' style='padding-left:15px; padding-right:15px;'>
									<font class='Texte_10'><b><u>Session $n_reel</u></b></font>
								 </td>\n");
					}

					// Affichage
					foreach($array_dates_propspec as $propspec_id => $array_propspec)
					{
						print("<tr>
									<td align='left' style='padding-right:15px;'>
										<font class='Texte_10'>$array_propspec[formation]</font>
									</td>\n");

						// Pour la séparation entre l'année universitaire courante et la suivante
						$current_session=1;
						
						foreach($array_dates_propspec[$propspec_id]["dates"] as $date_session)
						{
							// En cas de passage à la période suivante : on remplit proprement les colonnes vides
							if($current_session<=$max_session && $date_session["periode"]==($__PERIODE+1))
							{
								for($x=$current_session; $x<=$max_session; $x++)
									print("<td align='center' style='padding-left:15px; padding-right:15px;'></td>\n");
									
								$current_session=$max_session+1;
							}

							print("<td align='center' style='padding-left:15px; padding-right:15px;'>
										<font class='$date_session[font_class]'>$date_session[dates]</font>
									</td>\n");

							$current_session++;
						}

						print("</tr>\n");
					}

					print("</table>
							</td>
						</tr>\n");
				}
				else
				{
					$crypt_params2=crypt_params("co=$comp_id&d=$comp_id");

					print("<tr>
								<td style='padding-right:10px' width='14'>
									<a href='$php_self?p=$crypt_params2' target='_self'><img src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Voir les détails' desc='Détails'></a>
								</td>
								<td align='left' style='text-align:left; padding-right:10px'>
									<a href='$php_self?p=$crypt_params' class='lien_bleu_12' target='_self'>$comp_nom</a>
								</td>
								<td style='padding-right:10px; padding-left:10px; text-align:center;'>
									<font class='Texte'>$nb_txt</font>
								</td>
								<td align='right'><a href='$php_self?p=$crypt_params2' class='lien_bleu_10' target='_self'><i>Détail des dates</i></td>
							</tr>\n");

							// <td align='right'>$dates_txt</td>
				}
			}

			print("</table>
					<br>\n");
		}

		db_free_result($result);

		db_close($dbr);
	?>
<!--
	<br>
	<div class='centered_box'>
		<font class='Texte_3'>
			<p>Vous êtes <?php echo $perdu; ?> dans le choix de la composante ?</p>
			<p>Le <a href='http://www.universites-formations-alsace.fr' class='lien_bleu_14' target='_blank'><strong>Portail des formations universitaires en Alsace</strong></a> peut vous aider :
			<p><a href='http://www.universites-formations-alsace.fr' class='lien_bleu' target='_blank'><img src='<?php echo "$__IMG_DIR/logo_formations.gif"; ?>' border='0'></a></p>
		</font>
	</div>
-->
</div>
<?php
	pied_de_page_candidat();
?>
</body></html>
