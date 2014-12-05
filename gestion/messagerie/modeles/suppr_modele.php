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

	$dbr=db_connect();

	// Suppression d'un élément
	// Arguments :
	// o : ordre de l'objet (dans le tableau) à supprimer (il faudra décaler tous les objets suivants).
	// récupération des variables cryptées
	
	if(isset($_POST["go_suivant"]) || isset($_POST["go_suivant_x"]))
	{
		$_SESSION["suppr_modele_id"]=$_POST["modele_id"];
		$resultat=1;
	}

	if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
	{
		db_query($dbr,"DELETE FROM $_DB_msg_modeles WHERE $_DBC_msg_modeles_id='$_SESSION[suppr_modele_id]'");

		unset($_SESSION["suppr_modele_id"]);

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
		titre_page_icone("Supprimer un modèle de courriel", "trashcan_full_32x32_slick_fond.png", 15, "L");

		print("<form method='post' action='$php_self'>\n");

		if(isset($_GET["succes"]))
				message("Modèle supprimé avec succès.", $__SUCCES);

		if(!isset($resultat))
		{
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Modèle à supprimer :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
				$result=db_query($dbr,"SELECT $_DBC_msg_modeles_id, $_DBC_msg_modeles_intitule
													FROM $_DB_msg_modeles
												WHERE $_DBC_msg_modeles_acces_id='$_SESSION[auth_id]'
												ORDER BY $_DBC_msg_modeles_intitule");
				$rows=db_num_rows($result);

				if($rows)
				{
					print("<select name='modele_id'>\n");

					for($i=0; $i<$rows; $i++)
					{
						list($modele_id, $modele_intitule)=db_fetch_row($result, $i);

						$val=htmlspecialchars($modele_intitule, ENT_QUOTES, $default_htmlspecialchars_encoding);

						print("<option value='$modele_id'>$modele_intitule</option>\n");
					}

					print("</select>\n");
				}
				else
				{
					$no_element=1;
					print("<font class='Texte_menu'>Aucun modèle à supprimer<br></font>\n");
				}

				db_free_result($result);
			?>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='../index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png" ?>' alt='Retour' border='0'></a>
		<?php
			if(!isset($no_element))
				print("<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='go_suivant' value='Suivant'>\n");
		?>
		</form>
	</div>

	<?php
		}
		else
		{
			message("Attention : la suppression de cet élément est <b>définitive</b> !", $__WARNING);

			message("Souhaitez-vous vraiment supprimer ce modèle ?", $__QUESTION);

			print("<div class='centered_icons_box'>
						<a href='../index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
						<input type='image' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Confirmer' name='go_valider' value='Confirmer'>
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
