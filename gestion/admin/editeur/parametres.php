<?php
/*
=======================================================================================================
APPLICATION ARIA - UNIVERSITE DE STRASBOURG

LICENCE : CECILL-B
Copyright Universit� de Strasbourg
Contributeur : Christophe Boccheciampe - Janvier 2006
Adresse : cb@dpt-info.u-strasbg.fr

L'application utilise des �l�ments �crits par des tiers, plac�s sous les licences suivantes :

Ic�nes :
- CrystalSVG (http://www.everaldo.com), sous licence LGPL (http://www.gnu.org/licenses/lgpl.html).
- Oxygen (http://oxygen-icons.org) sous licence LGPL-V3
- KDE (http://www.kde.org) sous licence LGPL-V2

Librairie FPDF : http://fpdf.org (licence permissive sans restriction d'usage)

=======================================================================================================
[CECILL-B]

Ce logiciel est un programme informatique permettant � des candidats de d�poser un ou plusieurs
dossiers de candidatures dans une universit�, et aux gestionnaires de cette derni�re de traiter ces
demandes.

Ce logiciel est r�gi par la licence CeCILL-B soumise au droit fran�ais et respectant les principes de
diffusion des logiciels libres. Vous pouvez utiliser, modifier et/ou redistribuer ce programme sous les
conditions de la licence CeCILL-B telle que diffus�e par le CEA, le CNRS et l'INRIA sur le site
"http://www.cecill.info".

En contrepartie de l'accessibilit� au code source et des droits de copie, de modification et de
redistribution accord�s par cette licence, il n'est offert aux utilisateurs qu'une garantie limit�e.
Pour les m�mes raisons, seule une responsabilit� restreinte p�se sur l'auteur du programme, le titulaire
des droits patrimoniaux et les conc�dants successifs.

A cet �gard l'attention de l'utilisateur est attir�e sur les risques associ�s au chargement, �
l'utilisation, � la modification et/ou au d�veloppement et � la reproduction du logiciel par l'utilisateur
�tant donn� sa sp�cificit� de logiciel libre, qui peut le rendre complexe � manipuler et qui le r�serve
donc � des d�veloppeurs et des professionnels avertis poss�dant  des  connaissances informatiques
approfondies. Les utilisateurs sont donc invit�s � charger et tester l'ad�quation du logiciel � leurs
besoins dans des conditions permettant d'assurer la s�curit� de leurs syst�mes et ou de leurs donn�es et,
plus g�n�ralement, � l'utiliser et l'exploiter dans les m�mes conditions de s�curit�.

Le fait que vous puissiez acc�der � cet en-t�te signifie que vous avez pris connaissance de la licence
CeCILL-B, et que vous en avez accept� les termes.

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

	if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
	{
		$comp_txt_sign=trim($_POST['texte_signature']);
		$comp_txt_logo=trim($_POST['texte_logo']);
		$comp_txt_scol=trim($_POST['texte_scol']);
		$comp_largeur_logo=trim($_POST['largeur_logo']);

		$comp_adr_pos_x=trim($_POST['adr_pos_x']);
		$comp_adr_pos_y=trim($_POST['adr_pos_y']);

		$comp_corps_pos_x=trim($_POST['corps_pos_x']);
		$comp_corps_pos_y=trim($_POST['corps_pos_y']);

		$comp_logo=$_FILES["fichier"]["name"];
		$comp_logo_size=$_FILES["fichier"]["size"];
		$comp_logo_tmp_name=$_FILES["fichier"]["tmp_name"];

		if(!is_numeric($comp_largeur_logo) || $comp_largeur_logo<=0)
			$comp_largeur_logo=32;

		// Valeurs par d�faut de la position de l'adresse
		if(!is_numeric($comp_adr_pos_x) || $comp_adr_pos_x<=0)
			$comp_adr_pos_x=109;

		if(!is_numeric($comp_adr_pos_y) || $comp_adr_pos_y<=0)
			$comp_adr_pos_y=42;

		// Valeurs par d�faut de la position du corps de la lettre
		if(!is_numeric($comp_corps_pos_x) || $comp_corps_pos_x<=0)
			$comp_corps_pos_x=60;

		if(!is_numeric($comp_corps_pos_y) || $comp_corps_pos_y<=0)
			$comp_corps_pos_y=78;

		if(!empty($comp_logo))
			$comp_logo=htmlspecialchars(validate_filename($comp_logo),ENT_QUOTES, $default_htmlspecialchars_encoding);

		// Cr�ation du r�pertoire d�di� � la composante
		if(!is_dir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]"))
			mkdir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]", 0770);

		// Gestion du logo
		if($comp_logo_tmp_name!="")
		{
			$path="$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]";
			$destination_file="$path/$comp_logo";

			list($image_width, $image_height, $image_type)=getimagesize($comp_logo_tmp_name);

			if($comp_logo_size>200000)
				$file_wrong_size=1;
			elseif($image_type!=IMAGETYPE_JPEG)
				$image_wrong_type=1;
			else
			{
				if(!move_uploaded_file($comp_logo_tmp_name, $destination_file))
					$move_error=1;
			}
		}

		// Validation
		if(!isset($move_error) && !isset($image_wrong_type) && !isset($file_wrong_size))
		{
			if($comp_logo_tmp_name!="")
				$update_logo="$_DBU_composantes_logo='$comp_logo',";
			else
				$update_logo="";

			db_query($dbr,"UPDATE $_DB_composantes SET	$update_logo
																		$_DBU_composantes_txt_logo='$comp_txt_logo',
																		$_DBU_composantes_txt_sign='$comp_txt_sign',
																		$_DBU_composantes_txt_scol='$comp_txt_scol',
																		$_DBU_composantes_largeur_logo='$comp_largeur_logo',
																		$_DBU_composantes_adr_pos_x='$comp_adr_pos_x',
																		$_DBU_composantes_adr_pos_y='$comp_adr_pos_y',
																		$_DBU_composantes_corps_pos_x='$comp_corps_pos_x',
																		$_DBU_composantes_corps_pos_y='$comp_corps_pos_y'
								WHERE $_DBU_composantes_id='$_SESSION[comp_id]'");
		}

		db_close($dbr);

		header("Location:index.php?succes=1");
		exit;
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Lettres : modifier les param�tres par d�faut", "preferences_32x32_fond.png", 15, "L");

		if(isset($file_wrong_size))
			message("Erreur : la taille du logo est limit�e � 200ko", $__ERREUR);

		if(isset($image_type))
			message("Erreur : le logo doit �tre au format JPEG", $__ERREUR);

		if(isset($move_error))
			message("Erreur lors de la copie du logo : merci de contacter rapidement l'administrateur.", $__ERREUR);

		$result=db_query($dbr,"SELECT $_DBC_composantes_logo, $_DBC_composantes_txt_scol, $_DBC_composantes_txt_sign,
												$_DBC_composantes_txt_logo, $_DBC_composantes_largeur_logo, $_DBC_composantes_adr_pos_x,
												$_DBC_composantes_adr_pos_y, $_DBC_composantes_corps_pos_x, $_DBC_composantes_corps_pos_y
											FROM $_DB_composantes
										WHERE $_DBC_composantes_id='$_SESSION[comp_id]'");

		list($comp_logo, $comp_txt_scol, $comp_txt_sign, $comp_txt_logo, $comp_largeur_logo, $comp_adr_pos_x,
				$comp_adr_pos_y, $comp_corps_pos_x, $comp_corps_pos_y)=db_fetch_row($result,0);

		db_free_result($result);

		print("<form name='form1' enctype='multipart/form-data' method='POST' action='$php_self'>
					<input type='hidden' name='MAX_FILE_SIZE' value='200000'>\n");

	?>
	<table align='center'>
	<tr>
		<td class='td-complet fond_menu2' colspan='3' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Lettres : donn�es par d�faut</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Adresse du candidat</b></font>
		</td>
		<td class='td-milieu fond_menu' width='100'>
			<img src='<?php echo "$__IMG_DIR/legende_lettre.png"; ?>' border='0' Title='L�gende' desc='L�gende'>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
			<b>Position :</b>
			<br>Valeur de X (en mm) : <input type='text' name='adr_pos_x' value='<?php if(isset($comp_adr_pos_x)) echo htmlspecialchars(stripslashes($comp_adr_pos_x), ENT_QUOTES, $default_htmlspecialchars_encoding); else print("109"); ?>' maxlength='3' size='4'>
			<br>Valeur de Y (en mm) : <input type='text' name='adr_pos_y' value='<?php if(isset($comp_adr_pos_y)) echo htmlspecialchars(stripslashes($comp_adr_pos_y), ENT_QUOTES, $default_htmlspecialchars_encoding); else print("42"); ?>' maxlength='3' size='4'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Limites du corps de lettre<br>contenant les paragraphes</b></font>
		</td>
		<td class='td-milieu fond_menu' width='100'>
			<img src='<?php echo "$__IMG_DIR/legende_corps_lettre.png"; ?>' border='0' Title='L�gende' desc='L�gende'>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
			<b>Position :</b>
			<br>Valeur de X (en mm) : <input type='text' name='corps_pos_x' value='<?php if(isset($comp_corps_pos_x)) echo htmlspecialchars(stripslashes($comp_corps_pos_x), ENT_QUOTES, $default_htmlspecialchars_encoding); else print("60"); ?>' maxlength='3' size='4'>
			<br>Valeur de Y (en mm) : <input type='text' name='corps_pos_y' value='<?php if(isset($comp_corps_pos_y)) echo htmlspecialchars(stripslashes($comp_corps_pos_y), ENT_QUOTES, $default_htmlspecialchars_encoding); else print("78"); ?>' maxlength='3' size='4'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Texte affich� en signature</b></font>
		</td>
		<td class='td-droite fond_menu' colspan='2'>
			<textarea name='texte_signature' rows='5' cols='60'><?php
				if(isset($comp_txt_sign)) echo htmlspecialchars(stripslashes($comp_txt_sign), ENT_QUOTES, $default_htmlspecialchars_encoding);
			?></textarea>
		</td>
	</tr>
	<tr>
		<td class='td-complet fond_menu2' colspan='3' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Param�tres <u>uniquement</u> si le papier � ent�te n'est pas utilis�</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Logo de la composante</b></font>
		</td>
		<td class='td-droite fond_menu' colspan='2'>
			<font class='Texte_menu'>
				<input type='file' name='fichier'>&nbsp;&nbsp;<i>Format impos� : <b>jpeg</b>. Taille maximale : 200ko.</i>
				<?php if(isset($comp_logo) && !empty($comp_logo)) print("<br>Fichier actuel : <b>$comp_logo</b>"); ?>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Largeur du logo (en mm) :</b></font>
		</td>
		<td class='td-droite fond_menu' colspan='2'>
			<input type='text' name='largeur_logo' value='<?php if(isset($comp_largeur_logo)) echo htmlspecialchars(stripslashes($comp_largeur_logo), ENT_QUOTES, $default_htmlspecialchars_encoding); else print("32"); ?>' maxlength='3' size='4'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Texte affich�<br>au dessus du logo</b></font>
		</td>
		<td class='td-droite fond_menu' colspan='2'>
			<textarea name='texte_logo' rows='5' cols='60'><?php
				if(isset($comp_txt_logo)) echo htmlspecialchars(stripslashes($comp_txt_logo), ENT_QUOTES, $default_htmlspecialchars_encoding);
			?></textarea>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Information Scolarit�<br>(colonne gauche, bas)</b></font>
		</td>
		<td class='td-droite fond_menu' colspan='2'>
			<textarea name='texte_scol' rows='7' cols='60'><?php
				if(isset($comp_txt_scol)) echo htmlspecialchars(stripslashes($comp_txt_scol), ENT_QUOTES, $default_htmlspecialchars_encoding);
			?></textarea>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='index.php' target='_self'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='go_valider' value='Valider'>
		</form>
	</div>

	<?php
		db_close($dbr);
	?>
</div>

<?php
	pied_de_page();
?>

</body></html>
