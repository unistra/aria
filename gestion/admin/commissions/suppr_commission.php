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

	if(isset($_POST["select_periode"]) || isset($_POST["select_periode_x"]))
		$_SESSION["suppr_date_periode"]=$_POST["periode"];

	if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
	{
		$commission_num=$_POST["commission"];
		$resultat=1;
	}
	elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$commission_num=$_POST["commission_num"];

		foreach($_SESSION["all_commissions"] as $propspec_id => $propspec_commissions)
		{
			foreach($propspec_commissions as $current_commission_num => $current_commission_infos)
			{
				if($current_commission_num==$commission_num)
				{
					db_query($dbr, "DELETE FROM $_DB_commissions
										WHERE $_DBC_commissions_id='$current_commission_infos[com_id]'
										AND $_DBC_commissions_propspec_id='$propspec_id'
										AND $_DBC_commissions_periode='$_SESSION[suppr_date_periode]'");

					write_evt($dbr, $__EVT_ID_G_SESSION, "Suppression commission $current_commission_infos[com_id] ($propspec_id), période $_SESSION[suppr_date_periode]");
				}
			}
		}

		db_close($dbr);

		header("Location:index.php?");
		exit();
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		print("<form action='$php_self' method='POST' name='form1'>\n");

		if(!isset($_SESSION["suppr_date_periode"]))
		{
			titre_page_icone("Supprimer une date de commission pédagogique : sélection de l'année", "trashcan_full_32x32_slick_fond.png", 15, "L");

			message("<center>
							Sélectionnez l'année universitaire pour laquelle la session sera valide.
       					<br>Attention : les sessions ne doivent pas se recouvrir, même si les années universitaires sont distinctes.
						</center>", $__WARNING);
	?>
		<table align='center'>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Année universitaire concernée par la session à supprimer : </b></font>
			</td>
			<td class='td-droite fond_menu'>
				<select name='periode'>
					<?php
						if(isset($_SESSION["user_periode"]) && $_SESSION["user_periode"]==$__PERIODE)
						{
							$selected="selected";
							$selected_suivante="";
						}
						else
						{
							$selected_suivante="selected";
							$selected="";
						}

						print("<option value='$__PERIODE' $selected>Année actuelle ($__PERIODE-" . ($__PERIODE+1) . ")</option>
								 <option value='".($__PERIODE+1)."' $selected_suivante>Année suivante (".($__PERIODE+1). "-" . ($__PERIODE+2) . ")</option>\n");
					?>
				</select>
			</td>
		</tr>
		</table>

		<div class='centered_icons_box'>
			<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
			<input type="image" src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Suivant" name="select_periode" value="Suivant">
			</form>
		</div>
	<?php
		}
		// Choix de la commission à supprimer
		elseif(!isset($resultat))
		{
			titre_page_icone("Supprimer une date de commission pédagogique pour l'année $_SESSION[suppr_date_periode]-".($_SESSION["suppr_date_periode"]+1), "trashcan_full_32x32_slick_fond.png", 15, "L");

			// Nombre de commissions existantes (!= identifiants de commissions)
			$res1=db_query($dbr, "SELECT count($_DBC_commissions_propspec_id) FROM $_DB_commissions
											WHERE $_DBC_commissions_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																								WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
											AND $_DBC_commissions_periode='$_SESSION[suppr_date_periode]'
											GROUP BY $_DBC_commissions_propspec_id
											ORDER BY count DESC");

			$nb_rows=db_num_rows($res1);

			if($nb_rows)
				list($nb_commissions)=db_fetch_row($res1, 0);
			else
				$nb_commissions=0;

			if($nb_commissions=="")
				$nb_commissions=0;

			db_free_result($res1);

			if($nb_commissions)
			{
				print("<table style='margin-left:auto; margin-right:auto'>
						<tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><b>Choix de la commission à supprimer : </b></font>
							</td>
							<td class='td-droite fond_menu'>
								<select size='1' name='commission'>
									<option value=''></option>\n");

				for($i=1; $i<=$nb_commissions; $i++)
					print("<option value='$i'>Commission n°$i</option>\n");

				print("</select>
						</td>
					</tr>
					</table>

					<script language='javascript'>
						document.form1.commission.focus()
					</script>

					<div class='centered_icons_box'>
						<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>
						<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant' value='Suivant'>
						</form>
					</div>\n");
				}
				else
				{
					message("Il n'existe aucune commission à supprimer", $__INFO);

					print("<div class='centered_box'>
								<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>
							 </div>\n");
				}
			}
			elseif(isset($resultat) && $resultat==1)
			{
				titre_page_icone("Supprimer une date de commission pédagogique : confirmation", "trashcan_full_32x32_slick_fond.png", 15, "L");

				message("Souhaitez vous réellement supprimer la commission $commission_num (année $_SESSION[suppr_date_periode]-".($_SESSION["suppr_date_periode"]+1).") ?", $__QUESTION);

				print("<input type='hidden' name='commission_num' value='$commission_num'>\n");
		?>

		<div class='centered_icons_box'>
			<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
			<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
			</form>
		</div>

		<script language="javascript">
			document.form1.commission.focus()
		</script>

	<?php
		}
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
