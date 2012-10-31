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
	include "include/editeur_fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	unset($_SESSION["fichier_id"]);

	$dbr=db_connect();

	if(isset($_POST["go_supprimer"]) || isset($_POST["go_supprimer_x"])) // Validation d'une partie du formulaire
	{
		$fichier_existe_pas=$erreur_suppression="";

		if(array_key_exists("selection_fichier", $_POST))
		{
			foreach($_POST["selection_fichier"] as $fichier_id)
			{			
				// On cherche le fichier dans la base
				$result=db_query($dbr, "SELECT $_DBC_justifs_fichiers_nom FROM $_DB_justifs_fichiers
												WHERE $_DBC_justifs_fichiers_id='$fichier_id'");

				if(db_num_rows($result))
				{
					list($nom_fichier)=db_fetch_row($result, 0);

					if(is_file("$__PUBLIC_DIR_ABS/$_SESSION[comp_id]/justificatifs/$nom_fichier"))
					{
						if(unlink("$__PUBLIC_DIR_ABS/$_SESSION[comp_id]/justificatifs/$nom_fichier") == FALSE)
						{
							if(empty($erreur_suppression))
								$erreur_suppression="$nom_fichier";
							else
								$erreur_suppression.=", $nom_fichier";
						}
						else
							db_query($dbr, "DELETE FROM $_DB_justifs_fichiers WHERE $_DBC_justifs_fichiers_id='$fichier_id'");
					}
					else
					{
						if(empty($fichier_existe_pas))
							$fichier_existe_pas=$nom_fichier;
						else
							$fichier_existe_pas.=", $nom_fichier";
					}
				}

				db_free_result($result);
			}
		}
	}
	elseif(isset($_POST["go_envoyer"]) || isset($_POST["go_envoyer_x"]))
	{
		// informations liées au fichier envoyé
		$file_name=$_FILES["fichier"]["name"];
		$file_size=$_FILES["fichier"]["size"];
		$file_tmp_name=$_FILES["fichier"]["tmp_name"];
		$file_error=$_FILES["fichier"]["error"]; // PHP > 4.2.0 uniquement

		$file_name=html_entity_decode(validate_filename($file_name),ENT_QUOTES);

		if($file_size>16777216)
			$trop_gros=1;
		elseif($file_name=="none" || $file_name=="" || !is_uploaded_file($_FILES["fichier"]["tmp_name"]))
			$fichier_vide=1;
		else
		{
			if(!is_dir("$__PUBLIC_DIR_ABS/$_SESSION[comp_id]/justificatifs"))
				mkdir("$__PUBLIC_DIR_ABS/$_SESSION[comp_id]/justificatifs", 0770, TRUE);

			$destination_path="$__PUBLIC_DIR_ABS/$_SESSION[comp_id]/justificatifs/$file_name";

			$x=0;
			while(is_file("$destination_path")) // le fichier existe deja : on change le nom en ajoutant un numéro
			{
				$test_file_name=$x. "-$file_name";
				$destination_path="$__PUBLIC_DIR_ABS/$_SESSION[comp_id]/justificatifs/$test_file_name";

				$x++;
			}

			// DEBUG Uniquement
			// print("$file_tmp_name / $destination_path");

			if(!move_uploaded_file($file_tmp_name, $destination_path))
				$erreur_copie_fichier=1;
			else
			{
				$copie_ok=1;

				if(isset($test_file_name) && $test_file_name!="")
					$file_name=$test_file_name;

				$id_fichier=db_locked_query($dbr, $_DB_justifs_fichiers, "INSERT INTO $_DB_justifs_fichiers VALUES ('##NEW_ID##', '$_SESSION[comp_id]', '$file_name')");
			}
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Justificatifs : gestion des pièces jointes", "document-export_32x32_fond.png", 30, "L");

		if(isset($erreur_copie_fichier))
			message("Erreur : impossible de copier le fichier reçu. Merci de contacter l'administrateur.", $__ERREUR);

		if(isset($trop_gros))
			message("Erreur : le fichier envoyé est trop gros (max : 16Mo)", $__ERREUR);

		if(isset($fichier_vide))
			message("Erreur : aucun fichier sélectionné.", $__ERREUR);

		if(isset($copie_ok))
			message("<center>
							Fichier transféré avec succès.
							<br><b>N'oubliez pas de le rattacher aux formations voulues !</b>
						</center>", $__SUCCES);

		if(isset($fichier_existe_pas) && !empty($fichier_existe_pas))
		{
			if(strstr($fichier_existe_pas,",")) // plusieurs fichiers
				message("Erreur de suppression : les fichiers suivants n'existent pas : $fichier_existe_pas.", $__ERREUR);
			else
				message("Erreur de suppression : le fichier '$fichier_existe_pas' n'existe pas.", $__ERREUR);
		}

		if(isset($erreur_suppression) && !empty($erreur_suppression))
		{
			if(strstr($erreur_suppression,",")) // plusieurs fichiers
				message("Erreur : impossible de supprimer les fichiers suivants : $erreur_suppression.", $__ERREUR);
			else
				message("Erreur : impossible de supprimer le fichier '$erreur_suppression'.", $__ERREUR);
		}
	?>

	<table width='100%' cellpadding='4' cellspacing='0' border='0' align='center'>
	<tr>
		<td align='center' valign='top' width='50%'>
			<font class='Titre' face="Arial" size="4" style="font-weight: bold;">
				Fichiers actuellement stockés sur le serveur :
			</font>
			<br><br>

			<?php
				print("<form enctype='multipart/form-data' action='$php_self' method='POST'>\n");

				if(!is_dir("$__PUBLIC_DIR_ABS/$_SESSION[comp_id]/justificatifs"))
					mkdir("$__PUBLIC_DIR_ABS/$_SESSION[comp_id]/justificatifs", 0770, TRUE);

				$_SESSION["contenu_repertoire"] = scandir("$__PUBLIC_DIR_ABS/$_SESSION[comp_id]/justificatifs");

				$nb_fichiers=count($_SESSION["contenu_repertoire"]);

				if($nb_fichiers==2) // uniquement les répertoire . et ..
					print("<font class='Texte3'><b>Il n'y a aucun fichier dans l'espace de stockage.</b></font>
								<br><br><br>\n");
				else
				{
					$nb_reel_fichiers=$nb_fichiers-2; // on supprime les répertoires . et ..

					if($nb_reel_fichiers>1)
						$txt="$nb_reel_fichiers fichiers : ";
					else
						$txt="Un fichier : ";

					print("<table style='padding-bottom:20px;'>
								<tr>
									<td class='fond_menu2' colspan='4' style='padding:4px 10px 4px 10px;'>
										<font class='Texte_menu2'><b>$txt</b>
									</td>
								</tr>
								<tr>
									<td class='fond_menu2' nowrap='true' valign='top' style='padding:4px 4px 4px 10px;'>
										<font class='Texte_menu2'><b>Dernière modification</b></font>
									</td>
									<td class='fond_menu2' align='center' nowrap='true' valign='top' style='padding:4px;'>
										<font class='Texte_menu2'><b>Nom</b></font>
									</td>
									<td class='fond_menu2' align='center' nowrap='true' valign='top' style='padding:4px;'>
										<font class='Texte_menu2'><b>Taille (en octets)</b></font>
									</td>
									<td class='fond_menu2' align='center' nowrap='true' valign='top' style='padding:4px 10px 4px 4px;'>
										<font class='Texte_menu2'><b>Sélection</b></font>
									</td>
								</tr>\n");

					for($i=2; $i<$nb_fichiers; $i++)
					{
						// TODO : ajouter tests de retour des fonctions
						// $fichier=$_SESSION["repertoire"] . "/" . $_SESSION["contenu_repertoire"][$i];

						$fichier="$__PUBLIC_DIR_ABS/$_SESSION[comp_id]/justificatifs/" . $_SESSION["contenu_repertoire"][$i];
						$nom_fichier=$_SESSION["contenu_repertoire"][$i];

						// On cherche le fichier dans la base
						$result=db_query($dbr, "SELECT $_DBC_justifs_fichiers_id FROM $_DB_justifs_fichiers
														WHERE $_DBC_justifs_fichiers_nom='$nom_fichier'
														AND $_DBC_justifs_fichiers_comp_id='$_SESSION[comp_id]'");

						if(db_num_rows($result)) // normalement, un seul résultat
						{
							list($fichier_id)=db_fetch_row($result,0);

							$infos_fichier=stat($fichier);

							$taille=$infos_fichier["size"];
							$creation=date_fr("j F Y, H:i", $infos_fichier["mtime"]);

							print("<tr>
										<td class='fond_menu' nowrap='true' valign='top' style='padding:4px 4px 4px 10px;'>
											<font class='Texte_menu'>$creation</font>
										</td>
										<td class='fond_menu' align='center' nowrap='true' valign='top' style='padding:4px;'>
											<font class='Texte_menu'><b><a href='fichiers_formations.php?f=$fichier_id' class='lien2'>$nom_fichier</a></b></font>
										</td>
										<td class='fond_menu' align='center' nowrap='true' valign='top' style='padding:4px;'>
											<font class='Texte_menu'>$taille</font>
										</td>
										<td class='fond_menu' align='center' nowrap='true' valign='top' style='padding:4px 10px 4px 4px;'>
											<input type='checkbox' name='selection_fichier[]' value='$fichier_id'>
										</td>
									</tr>\n");
						}

						db_free_result($result);
					}

					print("<tr>
								<td colspan='4' align='center' valign='top' nowrap='true' style='padding:4px 10px 4px 10px;'>
									<input type='submit' name='go_supprimer' value='Supprimer les fichiers sélectionnés'>
								</td>
							</tr>
							</table>
							
							<font class='Texte3'>
								Pour modifier les liens entre un fichier et une formation, cliquez sur le nom du fichier voulu.
							</font>\n");
				}
			?>
		</td>
		<td class='fond_page' align='center' valign='top' width='50%'>
			<font class='Titre' face="Arial" size="4" style="font-weight: bold;">
				Envoyer un nouveau fichier
			</font>
			
			<table>
			<tr>
				<td class='fond_menu2' colspan='2' align='left' style='padding:4px 10px 4px 10px;'>
					<font class='Texte_menu2'><b>Sélection du fichier (Taille : 16Mo maximum) :</b></font>
				</td>
			</tr>
			<tr>
				<td class='fond_menu' style='padding:4px 10px 4px 10px;'>
					<input type="hidden" name="MAX_FILE_SIZE" value="16777216">
					<input type='file' name='fichier'>
				</td>
				<td class='fond_menu' align='center' vertical-align='middle' width='40' style='padding:4px 10px 4px 10px;'>
					<input type="image" src="<?php echo "$__ICON_DIR/mail_forward_32x32_menu.png"; ?>" alt="Envoyer ce fichier" name="go_envoyer" value="Envoyer ce fichier">
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>

	<div class='centered_box' style='padding-bottom:15px;'>
		<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		</form>
	</div>
</div>
<?php
	db_close($dbr);
	pied_de_page();
?>			
</body></html>
