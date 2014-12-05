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

	$periode_annee_courante=substr($__PERIODE, 2, 2) . "010100020000000";

	if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
		$current_periode=$_POST["periode"];
	else // par défaut : année en cours
 		$current_periode=$__PERIODE;

	$periode_txt="$current_periode - " . ($current_periode+1);

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>
<div class='main'>
	<?php
		titre_page_icone("Précandidatures en chiffres", "kpercentage_32x32_fond.png", 15, "L");

		// Selection de la période : on prend les bornes min et max des identifiants d'inscriptions (timestamps)

		$dbr=db_connect();

		$result=db_query($dbr,"SELECT min($_DBC_cand_periode), max($_DBC_cand_periode) FROM $_DB_cand
										WHERE $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																					WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')");
		$rows=db_num_rows($result);

		if($rows)
		{
			list($minY,$maxY)=db_fetch_row($result,0);

			print("<form action='$php_self' method='POST' name='form1'>

						<table cellspacing='0' cellpadding='4' border='0' align='center'>
						<tr>
							<td class='td-gauche fond_menu'>
								<font class='Texte_menu'><b>Statistiques de précandidatures sur la période :</b></font>
							</td>
							<td class='td-milieu fond_menu' valign='middle'>
								<select name='periode' size='1'>\n");

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

			print("</select>
					</td>
					<td class='td-droite fond_menu' valign='middle' width='40'>
						<input type='image' src='$__ICON_DIR/forward_32x32_menu.png' alt='Valider' name='go' value='Valider'>
					</td>
				</tr>
				</table>
				</form>
				<br>\n");
		}

		db_free_result($result);
	?>

	<center>
		<font class='Texte_important'>
			Toutes les précandidatures sont prises en compte dans ces listes.
		</font>
		<br><br>
		<a href='tabs_stats.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32.png" ; ?>' alt='Retour au menu précédent' border='0'></a>
	</center>
	<br>

	<table width='70%' cellpadding='4' cellspacing='0' border='0' align='center'>
	<tr>
		<td align='left' valign='top' nowrap='true'>
			<font class='Texte'><b>Statistiques globales</b></font>
		</td>
		<td align='left' valign='top' nowrap='true'>
			<font class='Texte'><b>Statistiques pour la période <?php echo $periode_txt; ?></b></font>
		</td>
	</tr>
	<tr>
		<td align='left' valign='top' nowrap='true'>
			<font class='Texte'>
			Nombre total de fiches candidats :
				<?php
					// nombre total
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_candidat
													WHERE $_DBC_candidat_id IN (SELECT distinct($_DBC_cand_candidat_id)
																							FROM $_DB_cand, $_DB_specs, $_DB_propspec
																						WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
																						AND $_DBC_cand_propspec_id=$_DBC_propspec_id
																						AND $_DBC_propspec_comp_id=$_SESSION[comp_id])");

					list($nombre_total)=db_fetch_row($result,0);
					db_free_result($result);

/*
					// étudiants ajoutés depuis le 1er janvier de l'année courante
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_candidat
													WHERE $_DBC_candidat_id>'$periode_annee_courante'
													AND $_DBC_candidat_id IN (SELECT distinct($_DBC_cand_candidat_id)
																							FROM $_DB_cand, $_DB_specs, $_DB_propspec
																						WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
																						AND $_DBC_cand_propspec_id=$_DBC_propspec_id
																						AND $_DBC_propspec_comp_id=$_SESSION[comp_id])");

					list($nb_nouveaux)=db_fetch_row($result,0);
					db_free_result($result);
*/
					print("$nombre_total\n");
/*
								<br>&nbsp;&nbsp;- dont $nb_nouveaux ajoutées depuis le 1er janvier $annee_courante

*/

					// nombre total
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_cand");
					list($nombre_total)=db_fetch_row($result,0);

					db_free_result($result);
/*
					// dossiers ajoutés depuis le 1er janvier de l'année courante
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_cand, $_DB_specs, $_DB_propspec
																		WHERE $_DBC_cand_id>'$periode_annee_courante'
																		AND $_DBC_propspec_id_spec=$_DBC_specs_id
																		AND $_DBC_cand_propspec_id=$_DBC_propspec_id
																		AND $_DBC_propspec_comp_id=$_SESSION[comp_id]");

					list($nb_nouveaux)=db_fetch_row($result,0);
					db_free_result($result);
*/
					print("<br>Nombre total de voeux de candidature : $nombre_total\n");
//							<br>&nbsp;&nbsp;- dont $nb_nouveaux ajoutés depuis le 1er janvier $annee_courante");
				?>
			</font>
		</td>
		<td align='left' valign='top' nowrap='true'>
			<font class='Texte'>
				<?php
					// étudiants ajoutés pendant l'année sélectionnée
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_candidat
													WHERE $_DBC_candidat_id IN (SELECT distinct($_DBC_cand_candidat_id)
																							FROM $_DB_cand, $_DB_specs, $_DB_propspec
																						WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
																						AND $_DBC_cand_propspec_id=$_DBC_propspec_id
																						AND $_DBC_cand_periode='$current_periode'
																						AND $_DBC_propspec_comp_id=$_SESSION[comp_id])");

					list($nb_etudiants_Y)=db_fetch_row($result,0);
					db_free_result($result);

					// dossiers ajoutés pendant l'année sélectionnée
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_cand, $_DB_specs, $_DB_propspec
													WHERE $_DBC_cand_periode='$current_periode'
													AND $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_cand_propspec_id=$_DBC_propspec_id
													AND $_DBC_propspec_comp_id=$_SESSION[comp_id]");

					list($nb_dossiers_Y)=db_fetch_row($result,0);
					db_free_result($result);

					print("Fiches candidats $nb_etudiants_Y
								<br>Voeux de candidature : $nb_dossiers_Y");
				?>
		</font>
		</td>
	</tr>
	</table>

			<br><br>

			<table width='70%' cellpadding='4' cellspacing='0' border='0' align='center'>
			<tr>
				<td align='left' valign='top' nowrap='true'>

			<?php
				$statuts_array=array();
				$statuts_array[$__PREC_ANNULEE]['texte']="Annulée";
				$statuts_array[$__PREC_ANNULEE]['couleur']="#888888";

				$statuts_array[$__PREC_NON_RECEVABLE]['texte']="Non recevable";
				$statuts_array[$__PREC_NON_RECEVABLE]['couleur']="#00BB00";

				$statuts_array[$__PREC_NON_TRAITEE]['texte']="Non traitée";
				$statuts_array[$__PREC_NON_TRAITEE]['couleur']="#CC0000";

				$statuts_array[$__PREC_RECEVABLE]['texte']="Recevable";
				$statuts_array[$__PREC_RECEVABLE]['couleur']="#00BB00";

				$statuts_array[$__PREC_EN_ATTENTE]['texte']="En attente";
				$statuts_array[$__PREC_EN_ATTENTE]['couleur']="#FF8800";

				$statuts_array[$__PREC_PLEIN_DROIT]['texte']="Entrée de plein droit";
				$statuts_array[$__PREC_PLEIN_DROIT]['couleur']="#00BB00";

				// tri du tableau
				ksort($statuts_array,SORT_NUMERIC);

				$nb_statuts=count($statuts_array);
				$span=$nb_statuts+1;

				// boucle sur les années
				$result=db_query($dbr,"SELECT $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_annees_ordre, count(*)
													FROM $_DB_cand, $_DB_annees,$_DB_specs, $_DB_propspec
												WHERE $_DBC_propspec_annee=$_DBC_annees_id
												AND $_DBC_cand_propspec_id=$_DBC_propspec_id
												AND $_DBC_propspec_id_spec=$_DBC_specs_id
												AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
												AND $_DBC_cand_periode='$current_periode'
													GROUP BY $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_annees_ordre
													ORDER BY $_DBC_annees_ordre ASC");
				$rows=db_num_rows($result);

				for($i=0; $i<$rows; $i++)
				{
					list($id_annee, $annee, $annee_ordre, $nombre_inscr_annee)=db_fetch_row($result,$i);

					// REQUETE POUR LES DECISIONS : select annee,specialite,decision,count(*) from inscriptions group by annee,specialite,decision order by annee,specialite,decision;

					$nom_annee=$annee=="" ? "Années particulières" : $annee;

					print("<table width='100%' cellpadding='2' cellspacing='0' align='center' border='0'>
							<tr>
								<td class='td-gauche fond_menu2' style='text-align:center' colspan='$span'>
									<font class='Texte_menu2'><b>$nom_annee ($nombre_inscr_annee voeux)</b></font>
								</td>
							</tr>
							<tr>
								<td class='td-gauche fond_menu'>
									<font class='Texte_menu'><b>Spécialité (traités / total)</b></font>
								</td>\n");

					// ligne donnant l'intitulé des décisions
					foreach($statuts_array as $sub_statut_array)
					{
						$statut_intitule=$sub_statut_array["texte"];
						print("<td class='td-milieu fond_menu' style='text-align:center'>
									<font class='Texte_menu'><b>$statut_intitule</b></font>
								 </td>\n");
					}

					print("</tr>");

					// plutot que de sélectionner toutes les specs d'un coup avec les différentes décisions (qui nécessiterait ensuite
					// un traitement php assez lourd), on boucle sur les différentes spécialités de cette année :
					$result2=db_query($dbr,"SELECT $_DBC_propspec_id_spec, $_DBC_specs_nom_court, count(*)
														FROM $_DB_cand, $_DB_specs, $_DB_propspec
													WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_cand_propspec_id=$_DBC_propspec_id
													AND $_DBC_propspec_comp_id=$_SESSION[comp_id]
													AND $_DBC_propspec_annee='$id_annee'
													AND $_DBC_cand_periode='$current_periode'
														GROUP BY $_DBC_propspec_id_spec, $_DBC_specs_nom_court
														ORDER BY $_DBC_specs_nom_court");
					$rows2=db_num_rows($result2);

					$fond1="fond_blanc";
					$fond2="fond_gris_E";

					for($j=0; $j<$rows2; $j++)
					{
						list($spec_id, $spec_nom,$nombre_inscr)=db_fetch_row($result2,$j);

						// Nombre de dossiers traités dans cette spécialité
						$result3=db_query($dbr,"SELECT count(*) FROM $_DB_cand, $_DB_propspec
															WHERE $_DBC_propspec_annee='$id_annee'
														AND $_DBC_propspec_id_spec='$spec_id'
														AND $_DBC_cand_propspec_id=$_DBC_propspec_id
														AND $_DBC_cand_periode='$current_periode'
														AND $_DBC_cand_statut!='$__PREC_NON_TRAITEE'");
						list($nb_traites)=db_fetch_row($result3, 0);

						if($nb_traites=="")
							$nb_traites=0;

						print("<tr>
									<td class='td-gauche $fond1'>
										<font class='Texte'>$spec_nom ($nb_traites / $nombre_inscr)</font></td>\n");

						// sélection des décisions pour ce couple année/spécialité
						$result3=db_query($dbr,"SELECT $_DBC_cand_statut, count(*) FROM $_DB_cand, $_DB_propspec
															WHERE $_DBC_propspec_annee='$id_annee'
															AND $_DBC_cand_propspec_id=$_DBC_propspec_id
															AND $_DBC_propspec_id_spec=$spec_id
															AND $_DBC_cand_periode='$current_periode'
														GROUP BY $_DBC_cand_statut
														ORDER BY $_DBC_cand_statut");

						$rows3=db_num_rows($result3);

						// on met le compteur des décisions à 0
						foreach($statuts_array as $key => $sub_statut_array)
							$statuts_array[$key]["nombre"]="";

						for($k=0; $k<$rows3;$k++)
						{
							list($statut_id,$nombre)=db_fetch_row($result3,$k);
							$statuts_array[$statut_id]["nombre"]=$nombre;
						}
						db_free_result($result3);

						// les données sont prêtes, on affiche le tableau
						foreach($statuts_array as $sub_statut_array)
						{
							$nombre=$sub_statut_array["nombre"];
							$couleur=$sub_statut_array["couleur"];

							print("<td class='td-milieu $fond1' style='text-align:center'>
										<font class='Texte' style='color:$couleur'>$nombre</font>
									 </td>\n");
						}

						switch_vals($fond1, $fond2);

						print("</tr>\n");
					}
					print("</table><br><br>\n");

					db_free_result($result2);
				}
				db_free_result($result);
			?>
			</font>
		</td>
	</tr>
	</table>
	</form>
</div>
<?php
	db_close($dbr);
	pied_de_page();
?>

</body>
</html>
