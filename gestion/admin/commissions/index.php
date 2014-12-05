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


	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	// Période définie par l'utilisateur
	if(isset($_GET["np"]) && $_GET["np"]==1 && isset($_SESSION["user_periode"]))
		$_SESSION["user_periode"]++;
	elseif(isset($_GET["pp"]) && $_GET["pp"]==1 && isset($_SESSION["user_periode"]))
		$_SESSION["user_periode"]--;
	elseif(!isset($_SESSION["user_periode"]))	// Par défaut, on considère la période actuelle
		$_SESSION["user_periode"]=$__PERIODE;

	// Nombre de commissions, pour l'affichage

	$result=db_query($dbr, "SELECT count(*) FROM $_DB_commissions, $_DB_propspec
										WHERE $_DBC_propspec_id=$_DBC_commissions_propspec_id
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										AND $_DBC_commissions_periode='$_SESSION[user_periode]'
										AND $_DBC_propspec_active='1'
										GROUP BY $_DBC_commissions_propspec_id
									ORDER BY count DESC
									LIMIT 1");

	if(db_num_rows($result))
		list($max_commissions)=db_fetch_row($result, 0);
	else
		$max_commissions=0;

	$colspan_annee=$max_commissions+1;

	db_free_result($result);								

	$result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
											 $_DBC_mentions_nom, $_DBC_commissions_id, $_DBC_commissions_date, $_DBC_commissions_periode
										FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_commissions, $_DB_mentions
									WHERE $_DBC_propspec_annee=$_DBC_annees_id
									AND $_DBC_propspec_id_spec=$_DBC_specs_id
									AND $_DBC_propspec_id=$_DBC_commissions_propspec_id
									AND $_DBC_specs_mention_id=$_DBC_mentions_id
									AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
									AND $_DBC_commissions_periode='$_SESSION[user_periode]'
									AND $_DBC_propspec_active='1'
										ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
													$_DBC_propspec_id, $_DBC_commissions_date");

	$rows=db_num_rows($result);

	if(!$rows)
		$aucune_specialite=1;

	// Nettoyage
	unset($_SESSION["new_date_periode"]);
	unset($_SESSION["suppr_date_periode"]);

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<div class='menu_haut_2'>
		<a href='commission.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' alt='+'></a>
		<a href='commission.php' target='_self' class='lien_menu_haut_2'>Ajouter une date</a>
		<a href='suppr_commission.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/trashcan_full_16x16_slick_menu2.png"; ?>' alt='+'></a>
		<a href='suppr_commission.php' target='_self' class='lien_menu_haut_2'>Supprimer une date</a>
	<?php
		// Navigation entre les périodes
		// Sessions existantes dans les périodes précédentes / suivantes ?

		$res_periodes=db_query($dbr, "SELECT count(*) FROM $_DB_commissions, $_DB_propspec
												WHERE $_DBC_propspec_id=$_DBC_commissions_propspec_id
												AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
												AND $_DBC_commissions_periode='".($_SESSION["user_periode"]-1)."'
												AND $_DBC_propspec_active='1'
												GROUP BY $_DBC_commissions_propspec_id
												ORDER BY count DESC
												LIMIT 1");
		if(db_num_rows($res_periodes))
			list($nb_dates_periode_precedente)=db_fetch_row($res_periodes, 0);
		else
			$nb_dates_periode_precedente=0;

		$res_periodes=db_query($dbr, "SELECT count(*) FROM $_DB_commissions, $_DB_propspec
										WHERE $_DBC_propspec_id=$_DBC_commissions_propspec_id
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										AND $_DBC_commissions_periode='".($_SESSION["user_periode"]+1)."'
										AND $_DBC_propspec_active='1'
										GROUP BY $_DBC_commissions_propspec_id
										ORDER BY count DESC
										LIMIT 1");
		if(db_num_rows($res_periodes))
			list($nb_dates_periode_suivante)=db_fetch_row($res_periodes, 0);
		else
			$nb_dates_periode_suivante=0;

		db_free_result($res_periodes);

		if($nb_dates_periode_precedente || $nb_dates_periode_suivante)
		{
			print("<span style='margin-top:2px; position:absolute; right:4px;'>\n");

			if($nb_dates_periode_precedente)
			{
				print("<span>
							<a href='$php_self?pp=1' target='_self' class='lien_navigation_10'><img style='vertical-align:middle;' border='0' src='$__ICON_DIR/back_16x16_menu2.png'></a>
							<a href='$php_self?pp=1' target='_self' class='lien_navigation_10'><strong>Année ". ($_SESSION["user_periode"]-1) ."-$_SESSION[user_periode]</strong></a>
						</span>\n");
			}

			if($nb_dates_periode_suivante)
			{
				print("<span>
							<a href='$php_self?np=1' target='_self' class='lien_navigation_10'><strong>Année ".($_SESSION["user_periode"]+1)."-".($_SESSION["user_periode"]+2)."</strong></a>
							<a href='$php_self?np=1' target='_self' class='lien_navigation_10'><img style='vertical-align:middle;' border='0' src='$__ICON_DIR/forward_16x16_menu2.png'></a>
						</span>\n");
			}

			print("</span>\n");
		}
	?>
	</div>

	<?php
		titre_page_icone("Gestion des dates de Commissions Pédagogiques pour l'année $_SESSION[user_periode]-".($_SESSION["user_periode"]+1), "clock_32x32_fond.png", 10, "L");
	?>

	<?php
		if(isset($_GET["succes"]) && $_GET["succes"]==1)
			message("Informations mises à jour avec succès.", $__SUCCES);

		if(isset($aucune_specialite) && $aucune_specialite==1)
			message("<center>Il n'y a actuellement aucune date de Commission Pédagogique.
						<br>Cliquez sur \"Ajouter une date\" pour en créer une.</center>", $__INFO);
	?>

	<table cellpadding='0' cellspacing='0' border='0' align='center' style='padding-bottom:20px;'>
	<tr>
		<td>
			<?php
				$old_annee="===="; // on initialise à n'importe quoi (sauf année existante et valeur vide)
				$old_propspec_id="";
				$old_mention="--";

				$current_commission=1; // par défaut

				$_SESSION["all_commissions"]=array();

				for($i=0; $i<$rows; $i++)
				{
					list($propspec_id, $annee, $spec_nom, $finalite, $mention, $commission_id, $com_date, $com_periode)=db_fetch_row($result, $i);

					$nom_finalite=$tab_finalite[$finalite];

					if($com_date!=0)
						$date_com_txt=date("Y")==date("Y", $com_date) ? date_fr("j F", $com_date) : date_fr("j M Y", $com_date);
					else
						$date_com_txt="";

					$annee=$annee=="" ? "Années particulières" : $annee;

					if($propspec_id!=$old_propspec_id && $old_propspec_id!="" && $current_commission<($max_commissions+1))
					{
						$diff_colspan=$max_commissions-$current_commission+1;
						print("<td class='td-milieu fond_menu' colspan='$diff_colspan' style='text-align:center; width:10%;'></td>\n");
					}

					if($annee!=$old_annee)
					{
						if($i!=0)
							print("</tr>
									 </table>\n");

						print("<table align='center' style='width:100%; padding-bottom:20px;'>
								 <tr>
									<td class='fond_menu2' align='center' colspan='$colspan_annee' style='padding:4px 20px 4px 20px;'>
										<font class='Texte_menu2'><b>$annee</b></font>
									</td>
								 </tr>
								 <tr>
									<td class='fond_menu2' style='padding:4px 20px 4px 20px;'>
										<font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
									</td>\n");

						for($s=1; $s<=$max_commissions; $s++)
							print("<td class='fond_menu2' align='center' style='padding:4px 20px 4px 20px; white-space:nowrap;'>
										<a href='edit_commission.php?n=$s' class='lien_rouge12'><b>Commission n°$s</b></font>
									</td>\n");

						$old_annee=$annee;
						$current_commission=1;
						$first_spec=1;
						$old_mention="--";
					}
					else
						$first_spec=0;

					if($mention!=$old_mention)
					{
						$span=$max_commissions+1;

						if(!$first_spec)
							print("<tr>
										<td class='fond_menu2' colspan='$span' style='padding:4px 20px 4px 20px;'>
											<font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
										</td>
									</tr>\n");

						$old_mention=$mention;
					}

					if($propspec_id!=$old_propspec_id)
					{
						print("</tr>
								 <tr>
									<td class='td-gauche fond_menu'>
										<font class='Texte_menu'>$spec_nom $nom_finalite</font>
									</td>\n");

						$current_commission=1;
						$_SESSION["all_commissions"][$propspec_id]=array();
					}

					print("<td class='td-milieu fond_menu' style='text-align:center; width:10%;'>
								<font class='Texte_menu'>$date_com_txt</font>
							 </td>\n");

					// Enregistrement dans une variable de session : évite les requêtes lourdes dans la BDD
					// et permet de retrouver facilement les intervalles pour modification
					$_SESSION["all_commissions"][$propspec_id][$current_commission]=array("com_id" => "$commission_id",
																												 "com" => "$com_date",
																												 "peiode" => "$com_periode");

					$current_commission++;
					$old_propspec_id=$propspec_id;
				}

				// fermeture propre de la fin du tableau
				if($current_commission<($max_commissions+1))
				{
					$diff_colspan=$max_commissions-$current_commission+1;
					print("<td class='td-milieu fond_menu' colspan='$diff_colspan' style='text-align:center; width:10%;'></td>\n");
				}

				print("</tr>
						 </table>\n");

				db_free_result($result);
				db_close($dbr);
			?>
		</td>
	</tr>
	</table>
</div>
<?php
	pied_de_page();
?>
</form>

</body></html>
