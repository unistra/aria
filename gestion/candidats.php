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

	$dbr=db_connect();

	// Déverrouillage, au cas où
	if(isset($_SESSION["candidat_id"]))
		cand_unlock($dbr, $_SESSION["candidat_id"]);

	// tri & filtre

	if(isset($_GET["t"]) && is_numeric($_GET["t"]) && $_GET["t"]>=0 & $_GET["t"]<5) // tri
		$_SESSION['tri']=$_GET["t"];

	// Afficher les fiches orphelines ?
	$orphelines=(isset($_GET["orph"]) && $_GET["orph"]==1) ? "1" : "0";

	// Tri par défaut : nom
	if(!isset($_SESSION["tri"]))
		$_SESSION["tri"]=1;

	// filtre sur une formation
	if(isset($_POST["valider"]) || isset($_POST["valider_x"]) || isset($_POST["defaut"]) || isset($_POST["defaut_x"]))
	{
		if(isset($_POST["filiere"]) && $_POST["filiere"]!="")
		{
			$_SESSION["filtre_propspec"]=$_POST["filiere"];

			if(isset($_POST["defaut"]) || isset($_POST["defaut_x"])) // on conserve la valeur par défaut dans la base annuaire
			{
				$_SESSION["spec_filtre_defaut"]=$_SESSION["filtre_propspec"];

				db_query($dbr,"UPDATE $_DB_acces SET $_DBU_acces_filtre='$_SESSION[spec_filtre_defaut]' WHERE $_DBU_acces_id='$_SESSION[auth_id]'");
			}
		}
	}

	// Filtre par défaut, si aucun filtre n'a encore été sélectionné
	if(!isset($_SESSION["filtre_propspec"]) && isset($_SESSION['spec_filtre_defaut']))
		$_SESSION["filtre_propspec"]=$_SESSION['spec_filtre_defaut'];

/*
	// si vraiment rien ...
	if(!isset($_SESSION["filtre_annee"]) || !isset($_SESSION["filtre_spec"]))
		$_SESSION["filtre_annee"]=$_SESSION["filtre_spec"]=-1;
*/

	// Nettoyage de variables utilisées ailleurs
	unset($_SESSION["cursus_a_valider"]);
	unset($_SESSION["cursus_transfert"]);
	unset($_SESSION["candidatures_transfert"]);
	// unset($_SESSION["candidat_id"]);
	unset($_SESSION["tab_candidatures"]);
	// unset($_SESSION["tab_candidat"]);

	$_SESSION["onglet"]=1; // onglet par défaut : identité du candidat

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main' style='padding-left:4px; padding-right:4px;'>
	<?php
		titre_page_icone("Intégralité des fiches candidats ($__PERIODE - ".($__PERIODE+1).")", "system-file-manager_32x32_fond.png", 10, "L");

		message("Cette liste contient également les fiches non verrouillées (fiches en <b>lecture seule</b> pour la scolarité).
					<br><b>Rappel</b> : vous pouvez consulter une fiche non verrouillée, mais vous ne pourrez pas la modifier.",$__INFO);

	?>

	<font class='Texte'>
	<b>Trier les fiches : </b></font>
	<?php
		// Méthode de tri
		// Attention : le tri par date n'est valide que si le filtre est activé, car ça n'a pas de sens sinon
		// => on force alors le tri par nom si besoin

		if($_SESSION["tri"]==0 && ($_SESSION["filtre_propspec"]==-1 || $orphelines==1))
			$_SESSION["tri"]=1;

		switch($_SESSION["tri"])
		{
			case 0:	print("<font class='Texte'><b>par date</b></font>&nbsp;&nbsp;<a href='$php_self?t=1' class='lien_bleu'><b>par nom</b></a>");
						$ordre_tri="date";
						$order_by="$_DBC_cand_id"; // l'identifiant d'une candidature est un timestamp = date à laquelle la candidature a été entrée
						break;

			case 1:	if($_SESSION["filtre_propspec"]!=-1)
							print("<a href='$php_self?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<font class='Texte'><b>par nom</b></font>");
						else
							print("<font class='Textegris'>par date</font>&nbsp;&nbsp;<font class='Texte'><b>par nom</b></font>");

						$ordre_tri="nom";
						$order_by="$_DBU_candidat_nom, $_DBU_candidat_prenom, $_DBU_candidat_date_naissance";
						break;
/*
			case 2:	print("<a href='$php_self?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<a href='$php_self?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<font class='Texte'><b>par formation</b></font>&nbsp;&nbsp;<a href='$php_self?t=3' class='lien_bleu'><b>par moyenne du dernier diplôme</b></a>");
						$ordre_tri="formation";
						$order_by="$_DBC_annees_ordre, $_DBC_propspec_id_spec, $_DBC_cand_id";
						break;

			case 3:	print("<a href='$php_self?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<a href='$php_self?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<a href='$php_self?t=2' class='lien_bleu'><b>par formation</b></a>&nbsp;&nbsp;<font class='Texte'><b>par moyenne du dernier diplôme</b></font>");
						$ordre_tri="note moyenne";
						// cas particulier : le tri ne se fait pas dans la table Candidats (mais on doit quand même mettre l'ordre ici)
						$order_by="$_DBC_cand_id";
						break;
*/
			default:	if($_SESSION["filtre_propspec"]!=-1)
							print("<a href='$php_self?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<font class='Texte'><b>par nom</b></font>");
						else
							print("<font class='Textegris'>par date</font>&nbsp;&nbsp;<font class='Texte'><b>par nom</b></font>");

						$ordre_tri="nom";
						$order_by="$_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBU_candidat_date_naissance";
						break;
		}

		print("<br><font class='Texte_10'><i>Le tri par date de dépôt n'est possible que lorsqu'un filtre sur une formation est activé.</i>");

		// Filtre
		if($_SESSION["filtre_propspec"]!=-1)
		{
			// $filtre="AND $_DBC_propspec_id='$_SESSION[filtre_propspec]'";
			$filtre="AND $_DBC_cand_propspec_id='$_SESSION[filtre_propspec]'";
			$filtre_statut="<font class='Texte_important'><b>(filtre activé)</b></font>";
		}
		else
		{
			// $filtre="AND $_DBC_cand_propspec_id LIKE '$_SESSION[comp_id]%'";
			$filtre="";
			$filtre_statut="<font class='Textevert'><b>(filtre désactivé)</b></font>";
		}
	?>
	<form action='<?php echo $php_self; ?>' method='POST' name='form1'>
	
	<font class='Texte'><b>Filtrer par Formation : </b></font>
	<select size="1" name="filiere">
		<option value="-1">Montrer toutes les formations</option>
		<option value="-1" disabled='1'></option>
		<?php
			$requete_droits_formations=requete_auth_droits($_SESSION["comp_id"]);
		
			$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
													$_DBC_propspec_manuelle
											FROM $_DB_propspec, $_DB_annees, $_DB_specs
										WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										AND $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id IN (SELECT distinct($_DBC_cand_propspec_id) FROM $_DB_cand, $_DB_propspec
																			WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
																			AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
																			AND $_DBC_cand_periode='$__PERIODE')
										$requete_droits_formations
											ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom");
			$rows=db_num_rows($result);

			$prev_annee="--"; // variable initialisée à n'importe quoi

			// TODO : revoir l'utilisation de la table annee (intégration de annees.id dans proprietes_specialites_v2, par exemple) et répercuter les changements ici ?
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

				$nom_finalite=$tab_finalite[$finalite];

				$selected=$_SESSION["filtre_propspec"]==$propspec_id ? "selected=1" : "";

				$manuelle_txt=$manuelle ? "(M)" : "";

				print("<option value='$propspec_id' label=\"$annee - $nom $nom_finalite $manuelle_txt\" $selected>$annee - $nom $nom_finalite $manuelle_txt</option>\n");
			}
			db_free_result($result);
		?>
	</select>
	&nbsp;&nbsp;<input type='submit' name='valider' value='Valider'>&nbsp;&nbsp;<input type='submit' name='defaut' value='Configurer ce filtre par défaut'>&nbsp;&nbsp;&nbsp;<?php print("$filtre_statut"); ?>
	<br><font class='Texte_10'><i>Seules les formations pour lesquelles des candidatures ont été déposées sont proposées.</i>
	</form>

	<br>
	<?php
		// jeux de couleurs obsolètes : à nettoyer
		$fond1="fond_menu";
		$Texte_important_1='Texte_important_menu';
		$Textevert1='Textevert_menu';
		$lien1="lien_menu_gauche";
		$icone_manuelle1="contact-new_16x16_menu.png";

		$fond2="fond_blanc";
		$Texte_important_2='Texte_important';
		$Textevert2='Textevert';
		$lien2="lien2a";
		$icone_manuelle2="contact-new_16x16_blanc.png";

		$annee_orph=date("y");

		if(substr($annee_orph, 0,1)=="0")
			$annee_orph=substr($annee_orph, 1,1);

		// Nombre de fiches sans précandidatures
/*		
		$result=db_query($dbr,"SELECT count(*) FROM $_DB_candidat
										WHERE $_DBC_candidat_id LIKE '$annee_orph%'
										AND $_DBC_candidat_id NOT IN (SELECT distinct($_DBC_cand_candidat_id) FROM $_DB_cand)");

		// même si count(*) vaut 0, la requête va renvoyer un résultat (vide)
		list($nb_fiches_vides)=db_fetch_row($result,0);
		
		
		if($orphelines==1 && $_SESSION["filtre_propspec"]==-1)
		{
			$condition_orph="UNION ALL (SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom,
															$_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance, 
															CASE WHEN $_DBC_candidat_pays_naissance IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance) 
																  THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance)
																  ELSE '' END as pays_naissance,
															$_DBC_candidat_manuelle, '0' as locked, '0' as unlocked,
															CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																									FROM $_DB_acces_candidats_lus
																									WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																									AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
																THEN '1' ELSE '0' END AS vu
													FROM $_DB_candidat
													WHERE $_DBC_candidat_id NOT IN (SELECT distinct($_DBC_cand_candidat_id) FROM $_DB_cand)) ";

			$lien="MASQUER ces fiches orphelines";
			$orph=0;
		}
		else
		{
			$condition_orph="";
			$lien="MONTRER également ces fiches orphelines";
			$orph=1;
		}

		if($nb_fiches_vides != "" && $nb_fiches_vides!=0 && $_SESSION["filtre_propspec"]==-1)
			print("<center>
							<font class='Texte'>Remarque : il y a $nb_fiches_vides fiches sans aucune précandidature.</font>
							<a href='$php_self?orph=$orph' class='lien_bleu_12'><b>$lien</b></a>
							<br><br>
						</center>");

		db_free_result($result);
*/
		// TEST : condition vide forcée pour éviter de modifier la requête
		$condition_orph="";

		// Récupération de tous les candidats en fonction du filtre sélectionné + les fiches sans voeu si le paramètre "orph" est là
		if($_SESSION["filtre_propspec"]==-1)
		{
			$result=db_query($dbr,"(SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom,
													$_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance, 
													CASE WHEN $_DBC_candidat_pays_naissance IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance) 
													  THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance)
													  ELSE '' END as pays_naissance,
													$_DBC_candidat_manuelle, sum(CASE WHEN $_DBC_cand_lock='1' THEN 1 END) as locked,
													sum(CASE WHEN $_DBC_cand_lock='0' THEN 1 END) as unlocked,
													CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																									FROM $_DB_acces_candidats_lus
																									WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																									AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
																THEN '1' ELSE '0' END AS vu
												FROM $_DB_candidat, $_DB_cand
											WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
											$filtre
											AND $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																						WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
																						$requete_droits_formations)
											AND $_DBC_cand_periode='$__PERIODE'
												GROUP BY $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom,
															$_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance, pays_naissance,
															$_DBC_candidat_manuelle, vu)
											$condition_orph
											ORDER BY $order_by");
		}
		else
		{
			$result=db_query($dbr,"SELECT $_DBC_cand_id, $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom,
													$_DBC_candidat_prenom, $_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance,
													CASE WHEN $_DBC_candidat_pays_naissance IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance) 
													  THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance)
													  ELSE '' END as pays_naissance,
													$_DBC_candidat_manuelle, $_DBC_cand_lock,
													CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																									FROM $_DB_acces_candidats_lus
																									WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																									AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
																THEN '1' ELSE '0' END AS vu
												FROM $_DB_candidat, $_DB_cand
											WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
											$filtre
											AND $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																						WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
																						$requete_droits_formations)
											AND $_DBC_cand_periode='$__PERIODE'
											ORDER BY $order_by");
		}

		$rows=db_num_rows($result);

		if($rows)
		{
			$s=$rows>1 ? "s" : "";

			$filtre_txt=$_SESSION["filtre_propspec"]!=-1 ? "Avec filtrage sur cette formation, il y a" : $filtre_txt="Au total, il y a";

			if($_SESSION["filtre_propspec"]!=-1)
				$td_titre_date="<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Date</b></font></td>";
			else
				$td_titre_date="";

			print("<font class='Texte3'><b>$filtre_txt $rows fiches (tri par nom, prénom et date de naissance) : </b></font><br><br>
						<table width='100%' border='0' cellspacing='0' cellpadding='4'>
						<tr>
							$td_titre_date
							<td class='td-gauche fond_menu2'></td>
							<td class='td-milieu fond_menu2'>
								<font class='Texte_menu2'><b>Candidat(e)s</b></font>
							</td>
							<td class='td-milieu fond_menu2'>
								<font class='Texte_menu2'><b>Naissance</b></font>
							</td>
							<td class='td-droite fond_menu2'>
								<font class='Texte_menu2'><b>Etat de la fiche</b></font>
							</td>
						</tr>\n");

			// Affichage des candidats
			for($i=0; $i<$rows; $i++)
			{
				if($_SESSION["filtre_propspec"]==-1)
				{
					list($candidat_id, $candidat_civ, $nom,$prenom,$date_naissance,$lieu_naissance, $pays_naissance, $fiche_manuelle,
						  $nb_lock, $nb_unlock, $vu)=db_fetch_row($result,$i);

					$td_date="";
					$nb_lock=$nb_lock=="" ? 0 : $nb_lock;
					$nb_unlock=$nb_unlock=="" ? 0 : $nb_unlock;
				}
				else
				{
					list($cand_id, $candidat_id, $candidat_civ, $nom,$prenom,$date_naissance,$lieu_naissance, $pays_naissance, $fiche_manuelle,
						  $flag_lock, $vu)=db_fetch_row($result,$i);

					$date_creation=date_fr("j F y", id_to_date($cand_id));
					$td_date="<td class='$fond1' nowrap='true'>
									<font class='Texte_menu'>- $date_creation</font>
								</td>\n";

					$nb_lock=$flag_lock=="1" ? "1" : "0";
					$nb_unlock=$flag_lock=="0" ? "1" : "0";
				}

				$naissance=date_fr("j F Y",$date_naissance);

				$lock_txt="";

				if($nb_lock)
				{
					$s2=$nb_lock>1 ? "s" : "";
					$x2=$nb_lock>1 ? "x" : "";

					$lock_txt="<font class='$Textevert1'>$nb_lock voeu$x2 traitable$s2 par la scolarité</font>";
				}

				if($nb_unlock)
				{
					if($lock_txt!="")
						$lock_txt.="<br>";

					$s2=$nb_unlock>1 ? "s" : "";
					$x2=$nb_unlock>1 ? "x" : "";

					$lock_txt.="<font class='$Texte_important_1'>$nb_unlock voeu$x2 non traitable$s2 par la scolarité</font>";
				}


				if($fiche_manuelle)
					$td_manuelle="<td class='$fond1' align='center' width='22'>
											<img src='$__ICON_DIR/$icone_manuelle1' alt='Fiche manuelle' desc='Fiche créée manuellement' border='0'>
										</td>\n";
				else
					$td_manuelle="<td class='$fond1'></td>\n";

				
				$link_class=isset($vu) && $vu ? "lien_vu_12" : "$lien1";

				print("<tr>
							$td_date
							$td_manuelle
							<td class='$fond1' align='left' nowrap='true'>
								<a href='edit_candidature.php?cid=$candidat_id' class='$link_class'><b>$nom $prenom</b></a>
							</td>
							<td class='$fond1' align='left' nowrap='true'>
								<a href='edit_candidature.php?cid=$candidat_id' class='$link_class'>$naissance à $lieu_naissance ($pays_naissance)</a>
							</td>
							<td class='$fond1' align='left' nowrap='true'>
								$lock_txt
							</td>
						</tr>\n");
			}
			print("</table>\n");
		}
		else
			print("<font class='Texte3'><b>Aucune fiche dans la base.</b></font><br>");

		db_free_result($result);
		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>
<br>

</body>
</html>

