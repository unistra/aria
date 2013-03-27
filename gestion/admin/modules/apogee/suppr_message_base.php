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
   include "include/vars.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	$dbr=db_connect();

	// Suppression d'un élément
	if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
	{
		$_SESSION["suppr_msg_id"]=$_POST["msg_id"];
		$resultat=1;
	}

	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		db_query($dbr,"DELETE FROM $_module_apogee_DB_messages WHERE $_module_apogee_DBC_messages_msg_id='$_SESSION[suppr_msg_id]'");

		header("Location:$php_self?succes=1");
		exit;
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Supprimer un message de la base de données", "trashcan_full_32x32_slick_fond.png", 15, "L");

		print("<form method='post' action='$php_self'>\n");

		if(isset($_GET["succes"]))
			message("Message supprimé avec succès de la base de données", $__SUCCES);

		if(!isset($resultat))
		{
			message("<center>Sécurité : seuls les messages NON RATTACHES peuvent être supprimés.
						<br>Les autres n'apparaissent pas dans la liste.</center>", $__INFO);
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Message à supprimer de la base de données :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
				$result=db_query($dbr,"SELECT $_module_apogee_DBC_messages_msg_id, $_module_apogee_DBC_messages_nom, $_module_apogee_DBC_messages_type
													FROM $_module_apogee_DB_messages
												WHERE $_module_apogee_DBC_messages_msg_id NOT IN (SELECT distinct($_module_apogee_DBC_messages_formations_msg) FROM $_module_apogee_DB_messages_formations)
												AND $_module_apogee_DBC_messages_comp_id='$_SESSION[comp_id]'
													ORDER BY $_module_apogee_DBC_messages_type, $_module_apogee_DBC_messages_nom");
				$rows=db_num_rows($result);

				if($rows)
				{
					print("<select name='msg_id'>\n");

               $old_type="";

					for($i=0; $i<$rows; $i++)
					{
						list($msg_id, $msg_nom, $msg_type)=db_fetch_row($result, $i);

                  if($old_type!=$msg_type)
                  {
                     if($i)
                        print("</optgroup>\n");
                        
                     print("<optgroup label='".htmlspecialchars(stripslashes($_MOD_APOGEE_MSG_TYPES["$msg_type"], ENT_QUOTES, $default_htmlspecialchars_encoding))."'>\n");
                     
                     $old_type=$msg_type;
                  }

						$val=htmlspecialchars($msg_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);

						print("<option value='$msg_id'>$val</option>\n");
					}

					print("</optgroup>
					    </select>\n");
				}
				else
				{
					$no_element=1;
					print("<font class='Texte_menu'>Aucun message ne peut être supprimé<br></font>\n");
				}

				db_free_result($result);
			?>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png" ?>' alt='Retour' border='0'></a>
		<?php
			if(!isset($no_element))
				print("<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant' value='Suivant'>\n");
		?>
		</form>
	</div>

	<?php
		}
		else
		{
			message("Attention : la suppression de ce message est <strong>définitive</strong>.", $__WARNING);

			message("Souhaitez-vous vraiment supprimer ce message ?", $__QUESTION);

			print("<div class='centered_icons_box'>
						<a href='messages_formations.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
						<input type='image' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Confirmer' name='valider' value='Confirmer'>
						</form>
					 </div>");
		}

		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
