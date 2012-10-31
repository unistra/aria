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
	include "$__INCLUDE_DIR_ABS/db.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	$dbr=db_connect();

	if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
	{
		if(ctype_digit($_POST["comp_id"]) && db_num_rows(db_query($dbr, "SELECT * FROM $_DB_composantes WHERE $_DBC_composantes_id='$_POST[comp_id]'")))
			$composante=$_POST["comp_id"];
		else
			$erreur=1;
	}
	elseif(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
	{
		if(isset($params["comp_id"]) && ctype_digit($params["comp_id"]))
		{
			if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_composantes WHERE $_DBC_composantes_id='$params[comp_id]'")))
				$composante=$params["comp_id"];
			else
				$erreur=1;
		}
	}

	en_tete_candidat_simple();
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Dates des différentes sessions de candidatures", "clock_32x32_fond.png", 15, "L");

		message("Vous ne pouvez choisir une formation que lorsqu'une session est ouverte.", $__INFO);

		$dbr=db_connect();

		if(!isset($composante)) // choix de la composante
		{
			$result=db_query($dbr, "SELECT $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_univ_id, $_DBC_universites_nom
												FROM $_DB_composantes, $_DB_universites
											WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
												ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");

			$rows=db_num_rows($result);

			print("<form action='$php_self' method='POST' name='form1'>

					<table align='center'>
					<tr>
						<td class='td-gauche fond_menu2'>
							<font class='Texte_menu2'><b>Sélection de la composante : </b></font>
						</td>
					</tr>
					<tr>
						<td class='td-gauche fond_menu'>
							<select name='comp_id' size='1' style='vertical-align:middle;'>\n");

			$old_univ="";

			for($i=0; $i<$rows; $i++)
			{
				list($comp_id, $comp_nom, $comp_univ_id, $univ_nom)=db_fetch_row($result,$i);

				if($comp_univ_id!=$old_univ)
				{
					if($i!=0)
						print("</optgroup>
									<option value='' label='' disabled></option>\n");

					print("<optgroup label='$univ_nom'>\n");
				}

				$value=htmlspecialchars($comp_nom, ENT_QUOTES);

				if(isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==$comp_id)
					$selected="selected='1'";
				else
					$selected="";

				print("<option value='$comp_id' label=\"$value\" $selected>$value</option>\n");

				$old_univ=$comp_univ_id;
			}

			db_free_result($result);

			print("</select>
						</td>
					</tr>
					</table>

					<div class='centered_icons_box'>
						<a href='../index.php' target='_self'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
						<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant' value='Suivant'>
						</form>
					</div>\n");


			if(isset($erreur) && $erreur==1)
				message("Erreur : Vous devez sélectionner une composante dans la liste ci-dessus.", $__ERREUR);
		}
		else // composante choisie, on récupère les infos actuelles
		{
			// Nombre de sessions, pour l'affichage
			// Pour la période précédente, seulement si des sessions sont encore ouvertes
			$date=time();

			$result=db_query($dbr, "SELECT count(*) FROM $_DB_session, $_DB_propspec
											WHERE $_DBC_propspec_id=$_DBC_session_propspec_id
											AND $_DBC_propspec_comp_id='$composante'
											AND $_DBC_session_periode='".($__PERIODE-1)."'
											AND $_DBC_session_fermeture>'$date'
											AND $_DBC_propspec_active='1'
											GROUP BY $_DBC_session_propspec_id
											ORDER BY count DESC
											LIMIT 1");

			if(db_num_rows($result))
				list($max_session_prev_periode)=db_fetch_row($result, 0);
			else
				$max_session_prev_periode=0;

			$result=db_query($dbr, "SELECT count(*) FROM $_DB_session, $_DB_propspec
											WHERE $_DBC_propspec_id=$_DBC_session_propspec_id
											AND $_DBC_propspec_comp_id='$composante'
											AND $_DBC_session_periode='$__PERIODE'
											AND $_DBC_propspec_active='1'
											GROUP BY $_DBC_session_propspec_id
											ORDER BY count DESC
											LIMIT 1");

			if(db_num_rows($result))
				list($max_session)=db_fetch_row($result, 0);
			else
				$max_session=0;

			$result=db_query($dbr, "SELECT count(*) FROM $_DB_session, $_DB_propspec
											WHERE $_DBC_propspec_id=$_DBC_session_propspec_id
											AND $_DBC_propspec_comp_id='$composante'
											AND $_DBC_session_periode='".($__PERIODE+1)."'
											AND $_DBC_propspec_active='1'
											GROUP BY $_DBC_session_propspec_id
											ORDER BY count DESC
											LIMIT 1");

			if(db_num_rows($result))
				list($max_session_next_periode)=db_fetch_row($result, 0);
			else
				$max_session_next_periode=0;

			$colspan_annee_prev_periode=$max_session_prev_periode;
			$colspan_annee=$max_session+1;
			$colspan_annee_next_periode=$max_session_next_periode;

			db_free_result($result);

			$result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_annees_annee_longue, $_DBC_specs_nom, $_DBC_propspec_finalite,
														$_DBC_mentions_nom, $_DBC_session_id, $_DBC_session_ouverture, $_DBC_session_fermeture,
														$_DBC_session_reception, $_DBC_session_periode
												FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_session, $_DB_mentions
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_propspec_id=$_DBC_session_propspec_id
											AND $_DBC_specs_mention_id=$_DBC_mentions_id
											AND $_DBC_propspec_comp_id='$composante'
											AND (($_DBC_session_periode='".($__PERIODE-1)."' AND $_DBC_session_fermeture>$date)
												  OR $_DBC_session_periode='$__PERIODE' OR $_DBC_session_periode='".($__PERIODE+1)."')
											AND $_DBC_propspec_active='1'
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom, $_DBC_propspec_finalite,
															$_DBC_session_ouverture, $_DBC_session_fermeture, $_DBC_session_reception, $_DBC_session_periode");

			$rows=db_num_rows($result);

			if(!$rows)
				message("Il n'y a actuellement aucune session de candidatures prévue pour cette composante.", $__ERREUR);
			else
			{
				print("<table align='center' style='padding-bottom:20px;'>
						 <tr>
							<td>\n");

				$old_annee="===="; // on initialise à n'importe quoi (sauf année existante et valeur vide)
				$old_propspec_id="";
				$old_mention="--";

				$current_session=1; // par défaut

				for($i=0; $i<$rows; $i++)
				{
					list($propspec_id, $annee, $spec_nom, $finalite, $mention, $session_id, $s_ouverture, $s_fermeture,
						  $s_reception, $s_periode)=db_fetch_row($result, $i);

					$nom_finalite=$tab_finalite[$finalite];

					if($s_ouverture!=0 && $s_fermeture!=0 && $s_reception!=0)
					{
						$date_ouv_txt=date("Y")==date("Y", $s_ouverture) ? date_fr("j F", $s_ouverture) : date_fr("j M Y", $s_ouverture);
						$date_ferm_txt=date("Y")==date("Y", $s_fermeture) ? date_fr("j F", $s_fermeture) : date_fr("j M Y", $s_fermeture);
						$date_rec_txt=date("Y")==date("Y", $s_reception) ? date_fr("j F", $s_reception) : date_fr("j M Y", $s_reception);

						if(time()<$s_fermeture && time()>$s_ouverture)
							$fonte="Textevert_menu";
						elseif(time()>$s_fermeture)
							$fonte="Textegris";
						elseif(time()<$s_ouverture)
							$fonte="Texte";

						$dates_txt="<font class='$fonte'>$date_ouv_txt - $date_ferm_txt<br>Réception des justificatifs : $date_rec_txt</font>";
					}
					else
						$dates_txt="";

					if($annee=="")
						$annee="Années particulières";

					if($propspec_id!=$old_propspec_id && $old_propspec_id!="" && $current_session<=($max_session_prev_periode+$max_session+$max_session_next_periode))
					{
						$diff_colspan=$max_session+$max_session_prev_periode+$max_session_next_periode-$current_session+1;
						print("<td class='td-milieu fond_menu' colspan='$diff_colspan'></td>
							</tr>\n");
					}

					if($annee!=$old_annee)
					{
						if($i!=0)
							print("</tr>
									 <tr>
										<td class='fond_page' colspan='".($colspan_annee+$colspan_annee_next_periode+$colspan_annee_prev_periode)."' height='15px'></td>
									</tr>\n");

						print("<tr>
									<td class='td-complet fond_menu2' align='center' colspan='".($colspan_annee+$colspan_annee_next_periode+$colspan_annee_prev_periode)."' style='padding:4px 20px 4px 20px;'>
										<font class='Texte_menu2'><strong>$annee</strong></font>
									</td>
								</tr>
								<tr>
									<td class='fond_menu2'></td>\n");

						if($max_session_prev_periode)
							print("<td class='fond_menu2' colspan='$colspan_annee_prev_periode' align='center'>
										<font class='Texte_menu2'><strong>".($__PERIODE-1)."-".($__PERIODE)."</strong></font>
									</td>\n");

						if($max_session)
							print("<td class='fond_menu2' colspan='$max_session' align='center'>
										<font class='Texte_menu2'><strong>$__PERIODE-".($__PERIODE+1)."</strong></font>
									</td>\n");

						if($max_session_next_periode)
							print("<td class='fond_menu2' colspan='$colspan_annee_next_periode' align='center'>
										<font class='Texte_menu2'><strong>".($__PERIODE+1)."-".($__PERIODE+2)."</strong></font>
									</td>\n");

						print("</tr>
						 		 <tr>
									<td class='fond_menu2' style='padding:4px 20px 4px 20px; white-space:nowrap' width='20%'>
										<font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
									</td>\n");
									
						for($s=1; $s<=($max_session_prev_periode+$max_session+$max_session_next_periode); $s++)
						{
							if($max_session_prev_periode && $s>$max_session_prev_periode)
								$nb=$s-$max_session_prev_periode;
							elseif($max_session && $s>$max_session)
								$nb=$s-$max_session-$max_session_prev_periode;
							else
								$nb=$s;
						
							print("<td class='fond_menu2' align='center' style='padding:4px 20px 4px 20px; white-space:nowrap;'>
										<font class='Texte_menu2'><b>Session n°$nb</b></font>
									</td>\n");
						}

						print("</tr>");

						$old_annee=$annee;
						$current_session=1;
						$first_spec=1;
						$old_mention="--";
					}
					else
						$first_spec=0;

					if($mention!=$old_mention)
					{
						$span=$colspan_annee_prev_periode+$max_session+1+$colspan_annee_next_periode;

						if(!$first_spec)
							print("<tr>
										<td class='fond_menu2' colspan='$span' style='padding:4px 20px 4px 20px; white-space:nowrap;' width='20%'>
											<font class='Texte_menu'><b>&#8226;&nbsp;&nbsp;Mention : $mention</b></font>
										</td>
									</tr>\n");

						$old_mention=$mention;
					}

					if($propspec_id!=$old_propspec_id)
					{

						print("<tr>
									<td class='td-gauche fond_menu' style='white-space:normal;' width='35%'>
										<font class='Texte_menu'>$spec_nom $nom_finalite</font>
									</td>\n");

						$current_session=1;
					}

					// On passe à la période suivante : on remplit proprement les colonnes vides
					if($current_session<=$max_session_prev_periode && $s_periode==($__PERIODE))
					{
						for($x=$current_session; $x<=$max_session_prev_periode; $x++)
							print("<td class='td-milieu fond_menu'></td>\n");
							
						$current_session=$max_session_prev_periode+1;
					}
					elseif($current_session<=$max_session && $s_periode==($__PERIODE+1))
					{
						for($x=$current_session; $x<=$max_session; $x++)
							print("<td class='td-milieu fond_menu'></td>\n");
							
						$current_session=$max_session+1;
					}

					print("<td class='td-milieu fond_menu' style='text-align:center;'>$dates_txt</td>\n");

					$current_session++;
					$old_propspec_id=$propspec_id;
				}

				// fermeture propre de la fin du tableau
				if($current_session<($max_session+$max_session_prev_periode+$max_session_next_periode+1))
				{
					$diff_colspan=$max_session_prev_periode+$max_session+$max_session_next_periode-$current_session+1;
					print("<td class='td-milieu fond_menu' colspan='$diff_colspan'></td>\n");
				}
				elseif($current_session<($max_session+$max_session_next_periode+1))
				{
					$diff_colspan=$max_session+$max_session_next_periode-$current_session+1;
					print("<td class='td-milieu fond_menu' colspan='$diff_colspan'></td>\n");
				}

				print("</tr>
						 </table>\n");

				db_free_result($result);
				db_close($dbr);
			?>
		</td>
		</table>
	<?php
		}
		// Le retour n'est possible que lorsque l'utilisateur vient de la documentation, inutile sinon
		if(!isset($params))
		{
			print("<div class='centered_icons_box'>
						<a href='../index.php' target='_self'><img src='$__ICON_DIR/rew_32x32_fond.png' alt='Retour' border='0'></a>
						<a href='$php_self' target='_self'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
					</div>\n");
		}
	}
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
