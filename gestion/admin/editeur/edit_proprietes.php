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

	verif_auth("../../login.php");

	// Modification des propriétés
	if(isset($_SESSION["lettre_id"]))
		$lettre_id=$_SESSION["lettre_id"];
	else
	{
		header("Location:index.php");
		exit;
	}

	// section exécutée lorsque le formulaire est validé
	if(isset($_POST["go"]) || isset($_POST["go_x"]))
	{
		$titre=trim($_POST["titre"]);

		if($titre=="")
			$titre_vide=1;
		else
		{
			$dbr=db_connect();

			// Lettres : infos par défaut
			$new_flag_logo=!array_key_exists("flag_logo", $_POST) || $_POST["flag_logo"]=="" ? "f" : $_POST["flag_logo"];
			$new_flag_txt_logo=!array_key_exists("flag_txt_logo", $_POST) || $_POST["flag_txt_logo"]=="" ? "f" : $_POST["flag_txt_logo"];
			$new_flag_txt_scol=!array_key_exists("flag_txt_scol", $_POST) || $_POST["flag_txt_scol"]=="" ? "f" : $_POST["flag_txt_scol"];
			$new_flag_txt_sign=!array_key_exists("flag_txt_sign", $_POST) || $_POST["flag_txt_sign"]=="" ? "f" : $_POST["flag_txt_sign"];
			$new_flag_adr_cand=!array_key_exists("flag_adr_cand", $_POST) || $_POST["flag_adr_cand"]=="" ? "t" : $_POST["flag_adr_cand"];
			$new_flag_adr_pos=!array_key_exists("flag_adr_pos", $_POST) || $_POST["flag_adr_pos"]=="" ? "t" : $_POST["flag_adr_pos"];
			$new_flag_corps_pos=!array_key_exists("flag_corps_pos", $_POST) || $_POST["flag_corps_pos"]=="" ? "t" : $_POST["flag_corps_pos"];
			$new_flag_date=!array_key_exists("flag_date", $_POST) || $_POST["flag_date"]=="" ? "t" : $_POST["flag_date"];

			$new_txt_sign=trim($_POST['texte_signature']);
			$new_txt_logo=trim($_POST['texte_logo']);
			$new_txt_scol=trim($_POST['texte_scol']);
			$new_largeur_logo=trim($_POST['largeur_logo']);

			$new_adr_pos_x=trim($_POST['adr_pos_x']);
			$new_adr_pos_y=trim($_POST['adr_pos_y']);

			$new_corps_pos_x=trim($_POST['corps_pos_x']);
			$new_corps_pos_y=trim($_POST['corps_pos_y']);

			$new_lang=$_POST['langue'];

			$new_logo=$_FILES["fichier"]["name"];
			$new_logo_size=$_FILES["fichier"]["size"];
			$new_logo_tmp_name=$_FILES["fichier"]["tmp_name"];

			// récupération de certains paramètres par défaut de la composante
			$res_comp=db_query($dbr,"SELECT $_DBC_composantes_adr_pos_x, $_DBC_composantes_adr_pos_y, $_DBC_composantes_corps_pos_x,
													$_DBC_composantes_corps_pos_y, $_DBC_composantes_largeur_logo
												FROM $_DB_composantes
											WHERE $_DBC_composantes_id='$_SESSION[comp_id]'");

			if(db_num_rows($res_comp)) // toujours vrai à cet endroit (sauf si la composante a été effacée entretemps ...)
				list($comp_adr_pos_x, $comp_adr_pos_y, $comp_corps_pos_x, $comp_corps_pos_y, $comp_largeur_logo)=db_fetch_row($res_comp,0);
			else
			{
				$comp_adr_pos_x=109;
				$comp_adr_pos_y=42;
				$comp_corps_pos_x=60;
				$comp_corps_pos_y=78;
				$comp_largeur_logo=33;
			}

			db_free_result($res_comp);

			if(!is_numeric($new_largeur_logo) || $new_largeur_logo<=0)
				$new_largeur_logo=$comp_largeur_logo;

			if(!is_numeric($new_adr_pos_x) || $new_adr_pos_x<=0)
				$new_adr_pos_x=$comp_adr_pos_x;

			if(!is_numeric($new_adr_pos_y) || $new_adr_pos_y<=0)
				$new_adr_pos_y=$comp_adr_pos_y;

			if(!is_numeric($new_corps_pos_x) || $new_corps_pos_x<=0)
				$new_corps_pos_x=$comp_corps_pos_x;

			if(!is_numeric($new_corps_pos_y) || $new_corps_pos_y<=0)
				$new_corps_pos_y=$comp_corps_pos_y;

			if(!empty($new_logo))
				$new_logo=htmlspecialchars(validate_filename($new_logo),ENT_QUOTES);

			// Création du répertoire dédié à la composante, si besoin
			if(!is_dir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]"))
				mkdir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]", 0770);

			// Gestion du logo pour cette lettre
			if($new_logo_tmp_name!="" && $new_flag_logo=="f")
			{
				$path="$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]";

				$new_name=$new_logo;
				$destination_file="$path/$new_name";

				$x=0;

				while(is_file($destination_file))
				{
					$x++;
					$new_name=$x . "_" . $new_logo;

					$destination_file="$path/$new_name";
				}

				list($image_width, $image_height, $image_type)=getimagesize($new_logo_tmp_name);

				if($new_logo_size>200000)
					$file_wrong_size=1;
				elseif($image_type!=IMAGETYPE_JPEG)
					$image_wrong_type=1;
				else
				{
					if(!move_uploaded_file($new_logo_tmp_name, $destination_file))
						$move_error=1;
				}
			}

			if(!isset($move_error) && !isset($image_wrong_type) && !isset($file_wrong_size))
			{
				if($new_logo_tmp_name!="" && $new_flag_logo=="f")
					$update_logo="$_DBU_lettres_logo='$new_name',";
				else
					$update_logo="";

				db_query($dbr,"UPDATE $_DB_lettres SET $_DBU_lettres_titre='$titre',
																	$update_logo
																	$_DBU_lettres_txt_logo='$new_txt_logo',
																	$_DBU_lettres_txt_scol='$new_txt_scol',
																	$_DBU_lettres_txt_sign='$new_txt_sign',
																	$_DBU_lettres_largeur_logo='$new_largeur_logo',
																	$_DBU_lettres_flag_logo='$new_flag_logo',
																	$_DBU_lettres_flag_txt_logo='$new_flag_txt_logo',
																	$_DBU_lettres_flag_txt_scol='$new_flag_txt_scol',
																	$_DBU_lettres_flag_txt_sign='$new_flag_txt_sign',
																	$_DBU_lettres_flag_adr_cand='$new_flag_adr_cand',
																	$_DBU_lettres_flag_date='$new_flag_date',
																	$_DBU_lettres_flag_adr_pos='$new_flag_adr_pos',
																	$_DBU_lettres_adr_pos_x='$new_adr_pos_x',
																	$_DBU_lettres_adr_pos_y='$new_adr_pos_y',
																	$_DBU_lettres_flag_corps_pos='$new_flag_corps_pos',
																	$_DBU_lettres_corps_pos_x='$new_corps_pos_x',
																	$_DBU_lettres_corps_pos_y='$new_corps_pos_y',
																	$_DBU_lettres_langue='$new_lang'
												WHERE $_DBU_lettres_id='$lettre_id'");

				db_close($dbr);

				header("Location:editeur.php");
				exit;
			}
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Modifier les propriétés de la lettre", "preferences_32x32_fond.png", 15, "L");

		if(isset($titre_vide))
			message("Le titre ne peut pas être vide", $__ERREUR);

		if(isset($file_wrong_size))
			message("Erreur : la taille du logo est limitée à 200ko", $__ERREUR);

		if(isset($image_type))
			message("Erreur : le logo doit être au format JPEG", $__ERREUR);

		if(isset($move_error))
			message("Erreur lors de la copie du logo : merci de contacter rapidement l'administrateur.", $__ERREUR);

		$dbr=db_connect();
		$result=db_query($dbr,"SELECT $_DBC_lettres_titre, $_DBC_lettres_logo, $_DBC_lettres_txt_scol, $_DBC_lettres_txt_sign,
												$_DBC_lettres_txt_logo, $_DBC_lettres_largeur_logo, $_DBC_lettres_flag_logo, $_DBC_lettres_flag_txt_logo,
												$_DBC_lettres_flag_txt_scol, $_DBC_lettres_flag_txt_sign, $_DBC_lettres_flag_adr_cand,
												$_DBC_lettres_flag_date, $_DBC_lettres_flag_adr_pos, $_DBC_lettres_adr_pos_x,
												$_DBC_lettres_adr_pos_y, $_DBC_lettres_flag_corps_pos, $_DBC_lettres_corps_pos_x,
												$_DBC_lettres_corps_pos_y, $_DBC_lettres_langue
										FROM $_DB_lettres WHERE $_DBC_lettres_id='$lettre_id'");

		$rows=db_num_rows($result);

		if($rows) // si != 1 : probleme...
		{
			list($current_titre, $current_logo, $current_txt_scol, $current_txt_sign, $current_txt_logo, $current_largeur_logo,
					$current_flag_logo, $current_flag_txt_logo,	$current_flag_txt_scol, $current_flag_txt_sign,
					$current_flag_adr_cand, $current_flag_date, $current_flag_adr_pos, $adr_pos_x, $adr_pos_y,
					$current_flag_corps_pos, $corps_pos_x, $corps_pos_y, $cur_lang)=db_fetch_row($result,0);

			db_free_result($result);

			print("<div class='centered_box'>
						<font class='TitrePage2' style='font-size:16px'><b>'$current_titre'</b></font>
					</div>
							
					<form name='form1' enctype='multipart/form-data' method='POST' action='$php_self'>
					<input type='hidden' name='MAX_FILE_SIZE' value='200000'>\n");
		?>

		<table style='margin-left:auto; margin-right:auto;'>
		<tr>
			<td class='fond_menu2' colspan='4' style='padding:4px 20px 4px 20px;'>
				<font class='Texte_menu2'>
					<b>&#8226;&nbsp;&nbsp;Données de la lettre</b>
				</font>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2' style='padding-bottom:30px'>
				<font class='Texte_menu2'><b>Titre de la lettre</b><br>(n'apparaît pas sur la lettre elle-même) :</font>
			</td>
			<td class='td-droite fond_menu' colspan='3' style='padding-bottom:30px'>
				<input type='text' name='titre' value='<?php if(isset($titre)) echo htmlspecialchars($titre, ENT_QUOTES); else echo htmlspecialchars($current_titre, ENT_QUOTES); ?>' size='40' maxlenght='96'>
			</td>
		</tr>
		<tr>
			<td class='fond_menu2' colspan='4' style='padding:4px 20px 4px 20px;'>
				<font class='Texte_menu2'>
					<b>&#8226;&nbsp;&nbsp;Champs optionnels et/ou spécifiques à cette lettre</b>
				</font>
			</td>
		</tr>
		<tr>
			<td class='fond_menu2' style='padding:4px 20px 4px 20px;'>
				<font class='Texte_menu2'><b>Elément</b></font>
			</td>
			<td class='fond_menu2' style='padding:4px 20px 4px 20px;'>
				<font class='Texte_menu2'><b>Utiliser la<br>valeur par défaut ?</b></font>
			</td>
			<td class='fond_menu2' style='padding:4px 20px 4px 20px;' colspan='2'>
				<font class='Texte_menu2'><b>Valeur spécifique à cette lettre</b></font>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Logo</b></font>
			</td>
			<td class='td-milieu fond_menu'>
				<font class='Texte_menu'>
					<?php
						if(isset($new_flag_logo))
							$flag_logo=$new_flag_logo;
						elseif(isset($current_flag_logo))
							$flag_logo=$current_flag_logo;
						else
							$flag_logo="t";

						if($flag_logo=="f")
						{
							$yes_checked="";
							$no_checked="checked";
						}
						else
						{
							$yes_checked="checked";
							$no_checked="";
						}

						print("<input type='radio' name='flag_logo' value='t' $yes_checked>&nbsp;Oui
									&nbsp;&nbsp;<input type='radio' name='flag_logo' value='f' $no_checked>&nbsp;Non\n");
					?>
				</font>
			</td>
			<td class='td-droite fond_menu' colspan='2'>
				<font class='Texte_menu'>
					<input type='file' name='fichier'>&nbsp;&nbsp;<i>Format imposé : <b>jpeg</b>. Taille maximale : 200ko.</i>
					<?php if(isset($current_logo) && !empty($current_logo)) print("<br>Fichier actuel : <b>$current_logo</b>"); ?>
				</font>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Largeur du logo (en mm) :</b></font>
			</td>
			<td class='td-milieu fond_menu'></td>
			<td class='td-droite fond_menu' colspan='2'>
				<input type='text' name='largeur_logo' value='<?php if(isset($current_largeur_logo)) echo htmlspecialchars(stripslashes($current_largeur_logo), ENT_QUOTES); else print("32"); ?>' maxlength='3' size='4'>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Texte affiché<br>au dessus du logo</b></font>
			</td>
			<td class='td-milieu fond_menu'>
				<font class='Texte_menu'>
					<?php
						if(isset($new_flag_txt_logo))
							$flag_txt_logo=$new_flag_txt_logo;
						elseif(isset($current_flag_txt_logo))
							$flag_txt_logo=$current_flag_txt_logo;
						else
							$flag_txt_logo="t";

						if($flag_txt_logo=="f")
						{
							$yes_checked="";
							$no_checked="checked";
						}
						else
						{
							$yes_checked="checked";
							$no_checked="";
						}

						print("<input type='radio' name='flag_txt_logo' value='t' $yes_checked>&nbsp;Oui
									&nbsp;&nbsp;<input type='radio' name='flag_txt_logo' value='f' $no_checked>&nbsp;Non\n");
					?>
				</font>
			</td>
			<td class='td-droite fond_menu' colspan='2'>
				<textarea name='texte_logo' rows='5' cols='60'><?php
					if(isset($current_txt_logo)) echo htmlspecialchars(stripslashes($current_txt_logo), ENT_QUOTES);
				?></textarea>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Texte affiché en signature</b></font>
			</td>
			<td class='td-milieu fond_menu'>
				<font class='Texte_menu'>
					<?php
						if(isset($new_flag_txt_sign))
							$flag_txt_sign=$new_flag_txt_sign;
						elseif(isset($current_flag_txt_sign))
							$flag_txt_sign=$current_flag_txt_sign;
						else
							$flag_txt_sign="t";

						if($flag_txt_sign=="f")
						{
							$yes_checked="";
							$no_checked="checked";
						}
						else
						{
							$yes_checked="checked";
							$no_checked="";
						}

						print("<input type='radio' name='flag_txt_sign' value='t' $yes_checked>&nbsp;Oui
									&nbsp;&nbsp;<input type='radio' name='flag_txt_sign' value='f' $no_checked>&nbsp;Non\n");
					?>
				</font>
			</td>
			<td class='td-droite fond_menu' colspan='2'>
				<textarea name='texte_signature' rows='5' cols='60'><?php
					if(isset($current_txt_sign)) echo htmlspecialchars(stripslashes($current_txt_sign), ENT_QUOTES);
				?></textarea>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Information Scolarité<br>(colonne gauche, bas)</b></font>
			</td>
			<td class='td-milieu fond_menu'>
				<font class='Texte_menu'>
					<?php
						if(isset($new_flag_txt_scol))
							$flag_txt_scol=$new_flag_txt_scol;
						elseif(isset($current_flag_txt_scol))
							$flag_txt_scol=$current_flag_txt_scol;
						else
							$flag_txt_scol="t";

						if($flag_txt_scol=="f")
						{
							$yes_checked="";
							$no_checked="checked";
						}
						else
						{
							$yes_checked="checked";
							$no_checked="";
						}

						print("<input type='radio' name='flag_txt_scol' value='t' $yes_checked>&nbsp;Oui
									&nbsp;&nbsp;<input type='radio' name='flag_txt_scol' value='f' $no_checked>&nbsp;Non\n");
					?>
				</font>
			</td>
			<td class='td-droite fond_menu' colspan='2'>
				<textarea name='texte_scol' rows='7' cols='60'><?php
					if(isset($current_txt_scol)) echo htmlspecialchars(stripslashes($current_txt_scol), ENT_QUOTES);
				?></textarea>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Afficher l'adresse<br>postale du candidat ?</b></font>
			</td>
			<td class='td-milieu fond_menu'></td>
			<td class='td-droite fond_menu' colspan='2'>
				<font class='Texte_menu'>
					<?php
						if(isset($new_flag_adr_cand))
							$flag_adr_cand=$new_flag_adr_cand;
						elseif(isset($current_flag_adr_cand))
							$flag_adr_cand=$current_flag_adr_cand;
						else
							$flag_adr_cand="t";

						if($flag_adr_cand=="f")
						{
							$yes_checked="";
							$no_checked="checked";
						}
						else
						{
							$yes_checked="checked";
							$no_checked="";
						}

						print("<input type='radio' name='flag_adr_cand' value='t' $yes_checked>&nbsp;Oui
									&nbsp;&nbsp;<input type='radio' name='flag_adr_cand' value='f' $no_checked>&nbsp;Non\n");
					?>
				</font>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Position de l'adresse</b></font>
			</td>
			<td class='td-milieu fond_menu'>
				<font class='Texte_menu'>
					<?php
						if(isset($new_flag_adr_pos))
							$flag_adr_pos=$new_flag_adr_pos;
						elseif(isset($current_flag_adr_pos))
							$flag_adr_pos=$current_flag_adr_pos;
						else
							$flag_adr_pos="t";

						if($flag_adr_pos=="f")
						{
							$yes_checked="";
							$no_checked="checked";
						}
						else
						{
							$yes_checked="checked";
							$no_checked="";
						}

						print("<input type='radio' name='flag_adr_pos' value='t' $yes_checked>&nbsp;Oui&nbsp;&nbsp;
									<input type='radio' name='flag_adr_pos' value='f' $no_checked>&nbsp;Non\n");
					?>
				</font>
			</td>
			<td class='td-milieu fond_menu' width='100'>
				<img src='<?php echo "$__IMG_DIR/legende_lettre.png"; ?>' border='0' Title='Légende' desc='Légende'>
			</td>
			<td class='td-droite fond_menu'>
				<font class='Texte_menu'>
				<b>Position :</b>
				<br>Valeur de X (en mm) : <input type='text' name='adr_pos_x' value='<?php if(isset($new_adr_pos_x)) echo htmlspecialchars(stripslashes($new_adr_pos_x), ENT_QUOTES); else echo htmlspecialchars(stripslashes($adr_pos_x), ENT_QUOTES); ?>' maxlength='3' size='4'>
				<br>Valeur de Y (en mm) : <input type='text' name='adr_pos_y' value='<?php if(isset($new_adr_pos_y)) echo htmlspecialchars(stripslashes($new_adr_pos_y), ENT_QUOTES); else echo htmlspecialchars(stripslashes($adr_pos_y), ENT_QUOTES); ?>' maxlength='3' size='4'>
				<br>(relativement au coin supérieur gauche de la feuille)
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Limites du corps de lettre</b></font>
			</td>
			<td class='td-milieu fond_menu'>
				<font class='Texte_menu'>
					<?php
						if(isset($new_flag_corps_pos))
							$flag_corps_pos=$new_flag_corps_pos;
						elseif(isset($current_flag_corps_pos))
							$flag_corps_pos=$current_flag_corps_pos;
						else
							$flag_corps_pos="t";

						if($flag_corps_pos=="f")
						{
							$yes_checked="";
							$no_checked="checked";
						}
						else
						{
							$yes_checked="checked";
							$no_checked="";
						}

						print("<input type='radio' name='flag_corps_pos' value='t' $yes_checked>&nbsp;Oui&nbsp;&nbsp;
									<input type='radio' name='flag_corps_pos' value='f' $no_checked>&nbsp;Non\n");
					?>
				</font>
			</td>
			<td class='td-milieu fond_menu' width='100'>
				<img src='<?php echo "$__IMG_DIR/legende_corps_lettre.png"; ?>' border='0' Title='Légende' desc='Légende'>
			</td>
			<td class='td-droite fond_menu'>
				<font class='Texte_menu'>
				<b>Position :</b>
				<br>Valeur de X (en mm) : <input type='text' name='corps_pos_x' value='<?php if(isset($new_corps_pos_x)) echo htmlspecialchars(stripslashes($new_corps_pos_x), ENT_QUOTES); else echo htmlspecialchars(stripslashes($corps_pos_x), ENT_QUOTES); ?>' maxlength='3' size='4'>
				<br>Valeur de Y (en mm) : <input type='text' name='corps_pos_y' value='<?php if(isset($new_corps_pos_y)) echo htmlspecialchars(stripslashes($new_corps_pos_y), ENT_QUOTES); else echo htmlspecialchars(stripslashes($corps_pos_y), ENT_QUOTES); ?>' maxlength='3' size='4'>
				<br>(relativement au coin supérieur gauche de la feuille)
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Affichage de la date de la commission :</b></font>
			</td>
			<td class='td-milieu fond_menu'></td>
			<td class='td-droite fond_menu' colspan='2'>
				<font class='Texte_menu'>
					<?php
						if(isset($new_flag_date))
							$flag_date=$new_flag_date;
						elseif(isset($current_flag_date))
							$flag_date=$current_flag_date;
						else
							$flag_date="1";

						if($flag_date=="1")
						{
							$aucune_date_selected=$date_jour_selected="";
							$date_com_selected="checked";
						}
						elseif($flag_date=="-1")
						{
							$date_jour_selected="checked";
							$aucune_date_selected=$date_com_selected="";
						}
						else
						{
							$aucune_date_selected="checked";
							$date_jour_selected=$date_com_selected="";
						}

						print("<input type='radio' name='flag_date' value='0' $aucune_date_selected>&nbsp;Aucun affichage&nbsp;&nbsp;
									<input type='radio' name='flag_date' value='1' $date_com_selected>&nbsp;Date de la commission&nbsp;&nbsp;
									<input type='radio' name='flag_date' value='-1' $date_jour_selected>&nbsp;Date de génération de la lettre\n");
					?>
				</font>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Langue pour les champs fixes :</b></font>
			</td>
			<td class='td-milieu fond_menu'></td>
			<td class='td-droite fond_menu' colspan='2'>
				<?php
					// Pour le moment, deux langues sont proposées : FR et EN
					$selected_FR=$selected_EN="";

					if($cur_lang=="FR")
						$selected_FR="selected='1'";
					elseif($cur_lang=="EN")
						$selected_EN="selected='1'";

					print("<select name='langue' style='padding-right:10px;'>
								<option value='EN' $selected_EN>Anglais</option>
								<option value='FR' $selected_FR>Français</option>
							</select>\n");
				?>
				<font class='Texte_menu'>
					<i>Les "champs fixes" affectés sont les dates et les "civilités"</i>
				<font>
			<td>
		</tr>
		</table>

		<div class='centered_icons_box'>
			<a href='editeur.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
			<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
			</form>
		</div>

	<?php
		}
	?>
</div>
<?php
	pied_de_page();
	db_close($dbr);
?>
</body></html>

