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

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		session_write_close();
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	if((isset($_POST["valider"]) || isset ($_POST["valider_x"])) && isset($_POST["codes"]))
	{
		// print_r($_POST);

		// Récupération des codes et versions d'étapes
		// Puisqu'il s'agit d'un module indépendant, la table n'est pas automatiquement complétée lorsqu'une formation est
		// créée : il faut donc soit mettre à jour, soit créer les enregistrements

		$requete="DELETE FROM $_module_apogee_DB_formations
						WHERE $_module_apogee_DBC_formations_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																								WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
																								AND $_DBC_propspec_active='1'); ";

		// Compteur de centres vides pour avertir l'utilisateur
		$centres_vides=0;

		foreach($_POST["codes"] as $propspec_id => $codes)
		{
			if(isset($codes["centre"]) && $codes["centre"]!='')
				$centre=$codes["centre"];
			else
			{
				$centre="0";
				$centres_vides++;
			}

			$requete.="INSERT INTO $_module_apogee_DB_formations VALUES ('$propspec_id','$codes[cet]','$codes[vet]','$centre');";
		}

		db_query($dbr, "$requete");

		$succes=1;
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Module Apogée : Codes et Versions d'étapes - Centres de gestion", "randr_32x32_fond.png", 15, "L");

		if(isset($succes))
			message("Codes enregistrés avec succès.", $__SUCCES);

		if(isset($centres_vides) && $centres_vides>0)
			message("<center>
							<strong>Attention :</strong> le centre de gestion n'a pas été renseigné pour toutes les formations ($centres_vides).
							<br>Ce renseignement est <strong>obligatoire</strong> pour les transferts des candidats admis vers APOGEE.
						</center>", $__WARNING);
						

		// Présence indispensable des centres de gestion)
		if(!db_num_rows(db_query($dbr, "SELECT * FROM $_module_apogee_DB_centres_gestion
													WHERE $_module_apogee_DBU_centres_gestion_comp_id='$_SESSION[comp_id]'")))
			message("Vous devez ajouter au moins un <strong>Centre de gestion</strong> avant de pouvoir configurer
						les codes et versions d'étape.", $__ERREUR);
		else
		{
	?>

		<form method='POST' action='<?php echo $php_self; ?>'>

		<?php
			// Les CASE sont nécessaires car la table module_apogee_formations ne contient pas nécessairement les
			// identifiants des formations (plugin)

			$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_nom_court,
													$_DBC_propspec_finalite, $_DBC_mentions_id, $_DBC_mentions_nom,
													CASE WHEN $_DBC_propspec_id IN (SELECT $_module_apogee_DBC_formations_propspec_id
																								FROM  $_module_apogee_DB_formations
																								WHERE $_module_apogee_DBC_formations_propspec_id=$_DBC_propspec_id)
													THEN (SELECT $_module_apogee_DBC_formations_cet
																FROM $_module_apogee_DB_formations
																WHERE $_module_apogee_DBC_formations_propspec_id=$_DBC_propspec_id)
													END as cet,
													CASE WHEN $_DBC_propspec_id IN (SELECT $_module_apogee_DBC_formations_propspec_id
																								FROM  $_module_apogee_DB_formations
																								WHERE $_module_apogee_DBC_formations_propspec_id=$_DBC_propspec_id)
													THEN (SELECT $_module_apogee_DBC_formations_vet
																FROM $_module_apogee_DB_formations
																WHERE $_module_apogee_DBC_formations_propspec_id=$_DBC_propspec_id)
													END as vet,
													CASE WHEN $_DBC_propspec_id IN (SELECT $_module_apogee_DBC_formations_propspec_id
																								FROM  $_module_apogee_DB_formations
																								WHERE $_module_apogee_DBC_formations_propspec_id=$_DBC_propspec_id)
													THEN (SELECT $_module_apogee_DBC_formations_centre_gestion
																FROM $_module_apogee_DB_formations
																WHERE $_module_apogee_DBC_formations_propspec_id=$_DBC_propspec_id)
													END as centre_gestion
												FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_specs_mention_id=$_DBC_mentions_id
											AND $_DBC_propspec_active='1'
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_specs_nom_court");

			$rows=db_num_rows($result);
			$old_annee="===="; // idem

			if($rows)
			{
				// Liste des centres de gestion pour attribution
				$res_centres_gestion=db_query($dbr,"SELECT $_module_apogee_DBC_centres_gestion_id, $_module_apogee_DBC_centres_gestion_nom,
																		$_module_apogee_DBC_centres_gestion_code
																	FROM $_module_apogee_DB_centres_gestion
																WHERE $_module_apogee_DBC_centres_gestion_comp_id='$_SESSION[comp_id]'
																ORDER BY $_module_apogee_DBC_centres_gestion_nom");

				$rows_centres_gestion=db_num_rows($res_centres_gestion);

				$liste_centres_gestion=array();

				if($rows_centres_gestion)
				{
					for($c=0; $c<$rows_centres_gestion; $c++)
					{
						list($centre_id, $centre_nom, $centre_code)=db_fetch_row($res_centres_gestion, $c);
						$liste_centres_gestion["$centre_id"]="$centre_nom ($centre_code)";
					}

					// S'il n'y a qu'un centre (très courant dans les UFR), on le conserve en "valeur par défaut"
					$default_centre_gestion=($rows_centres_gestion==1) ? $centre_id : "";
				}

				db_free_result($res_centres_gestion);

				// on initialise les anciennes valeurs
				$old_propspec_id=$old_annee_id=$old_mention="--"; 

				$j=0;

				print("<table align='center' style='padding-bottom:20px;'>\n");

				for($i=0; $i<$rows; $i++)
				{
					list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $mention_nom, $cet, $vet,
						$centre_gestion_id)=db_fetch_row($result, $i);

					$nom_finalite=$tab_finalite[$finalite];

					if($annee_id!=$old_annee_id)
					{
						$annee=$annee=="" ? "Années particulières" : $annee;

						if($i) // Le premier résultat du tableau est particulier (i=0)
							print("<tr>
										<td class='fond_page' height='10' colspan='3'></td>
									</tr>\n");

						print("<tr>
									<td class='td-complet fond_menu2' colspan='4'>
										<font class='Texte_menu2'><strong>$annee</strong></font>
									</td>
								</tr>
								<tr>
									<td class='td-gauche fond_menu2'><font class='Texte_menu2'><strong>Mention / Formation</strong></font></td>
									<td class='td-milieu fond_menu2'><font class='Texte_menu2'><strong>Code étape</strong></font></td>
									<td class='td-milieu fond_menu2'><font class='Texte_menu2'><strong>Version d'étape</strong></font></td>
									<td class='td-droite fond_menu2'><font class='Texte_menu2'><strong>Centre de gestion</strong></font></td>
								</tr>
								<tr>
									<td class='td-gauche fond_menu2' colspan='4' valign='top'>
										<font class='Texte_menu2'><strong>$mention_nom</strong></font>
									</td>
								</tr>\n");

						$old_mention="$mention";
						$old_annee_id=$annee_id;
					}

					if($old_mention!=$mention)
					{
						print("<tr>
									<td class='td-gauche fond_menu2' colspan='4' valign='top'>
										<font class='Texte_menu2'><b>$mention_nom</b></font>
									</td>
								</tr>\n");

						$old_mention=$mention;
					}

					print("<tr>
								<td class='td-gauche fond_menu'>
									<font class='Texte_menu'>$spec_nom $nom_finalite</font>
								</td>
								<td class='td-milieu fond_menu'>
									<input type='text' name='codes[$propspec_id][cet]' value='$cet' size='8' maxlength='8'>
								</td>
								<td class='td-milieu fond_menu'>
									<input type='text' name='codes[$propspec_id][vet]' value='$vet' size='8' maxlength='8'>
								</td>
								<td class='td-droite fond_menu'>\n");

					if(count($liste_centres_gestion))
					{
						print("<select name='codes[$propspec_id][centre]'>
									<option value=''></option>\n");
						foreach($liste_centres_gestion as $liste_centre_id => $liste_centre_nom)
						{
							if($centre_gestion_id!="0")
								$selected=$liste_centre_id==$centre_gestion_id ? "selected='1'" : "";
							elseif(isset($default_centre_gestion))
								$selected=$liste_centre_id==$default_centre_gestion ? "selected='1'" : "";
							else
								$selected="";

							print("<option value='$liste_centre_id' $selected>$liste_centre_nom</option>\n");
						}
					}

					print("</td>
							</tr>\n");
				}

				db_free_result($result);

				print("</table>

						<div class='centered_icons_box'>
							<a href='$__GESTION_DIR/admin/index.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Annuler' border='0'></a>
							<input type='image' class='icone' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider' value='Valider'>
							</form>
						</div>\n");
			}
			else
				message("Vous devez d'abord créer des formations avant de pouvoir utiliser ce module.", $__WARNING);
		}

		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
