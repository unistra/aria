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

	$dbr=db_connect();
	
	if(isset($_GET["succes"]))
		$succes=$_GET["succes"];

	if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
	{
		$justif_id=$_POST["justif_id"];
		$resultat=1;
	}
	elseif((isset($_POST["valider"]) || isset($_POST["valider_x"]))
		  && isset($_POST['intitule']) && isset($_POST['titre']) && isset($_POST['texte']))
	{
		$dbr=db_connect();

		$justif_intitule=trim($_POST['intitule']);
		$justif_titre=trim($_POST['titre']);
		$justif_texte=$_POST['texte'];

		if(!isset($_SESSION["ajout"]) && isset($_POST["justif_id"]) && $_POST["justif_id"]!="") // Modification
		{
			$new_id=$_POST["justif_id"];

			// unicité
			if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_justifs
													WHERE ($_DBC_justifs_intitule ILIKE '$justif_intitule'
														OR ($_DBC_justifs_texte!='' AND $_DBC_justifs_texte ILIKE '$justif_texte'))
													AND $_DBC_justifs_id!='$new_id'
													AND $_DBC_justifs_comp_id='$_SESSION[comp_id]'")))
				$justif_existe="1";
		}
		elseif(isset($_SESSION["ajout"])
				 && db_num_rows(db_query($dbr,"SELECT * FROM $_DB_justifs
															WHERE ($_DBC_justifs_intitule ILIKE '$justif_intitule'
																OR ($_DBC_justifs_texte!='' AND $_DBC_justifs_texte ILIKE '$justif_texte'))
															AND $_DBC_justifs_comp_id='$_SESSION[comp_id]'")))
			$justif_existe="1";

		// vérification des champs
		if($justif_intitule=="")
			$intitule_vide=1;

		if($justif_titre=="" && $justif_texte=="")
			$infos_vides=1;

		if((isset($new_id) && $new_id!="") || isset($_SESSION["ajout"]))
		{
			if(!isset($justif_existe) && !isset($intitule_vide) && !isset($infos_vides)) // on peut poursuivre
			{
				// Modification
				if(!isset($_SESSION["ajout"]) && isset($new_id))
					db_query($dbr,"UPDATE $_DB_justifs SET	$_DBU_justifs_intitule='$justif_intitule',
																		$_DBU_justifs_titre='$justif_titre',
																		$_DBU_justifs_texte='$justif_texte'
										WHERE $_DBU_justifs_id='$new_id'");
				else
					$new_id=db_locked_query($dbr, $_DB_justifs, "INSERT INTO $_DB_justifs VALUES ('##NEW_ID##', '$_SESSION[comp_id]', '$justif_intitule', '$justif_titre', '$justif_texte')");

				db_close($dbr);

				header("Location:$php_self?succes=1");
				exit;
			}
			else
				db_close($dbr);
		}
		else
			$erreur_selection=1;
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		if(isset($_GET["a"]) || isset($_SESSION["ajout"]))
		{
			$_SESSION["ajout"]=1;
			titre_page_icone("Créer un justificatif", "add_32x32_fond.png", 15, "L");
			$message="Après avoir créé cet élément, n'oubliez pas de le rattacher aux formations désirées.";
		}
		else
		{
			titre_page_icone("Modifier un justificatif existant", "edit_32x32_fond.png", 15, "L");
			$message="Attention : la modification sera valable pour toutes les formations rattachées à cet élément.";
		}

		if(isset($intitule_vide))
			message("Erreur : le champ 'Intitulé' ne doit pas être vide", $__ERREUR);

		if(isset($infos_vides))
			message("Erreur : les champs 'Paragraphe' et 'Titre du paragraphe' ne doivent pas être tous les deux vides (un accepté)", $__ERREUR);

		if(isset($justif_existe))
			message("Erreur : cet intitulé de justificatif existe déjà.", $__ERREUR);

		if(isset($succes))
		{
			if(!isset($_SESSION["ajout"]))
				message("Le justificatif a été modifié avec succès.", $__SUCCES);
			else
				message("Le justificatif a été créé avec succès.", $__SUCCES);
		}

		message("$message", $__INFO);

		// message("Si le paragraphe est vide, seul le titre apparaîtra sur le document (texte non gras).", $__INFO);

		$dbr=db_connect();

		if(!isset($resultat) && !isset($_GET["a"]) && !isset($_SESSION["ajout"])) // choix de l'élément à modifier
		{
			$result=db_query($dbr, "SELECT $_DBC_justifs_id, $_DBC_justifs_intitule
												FROM $_DB_justifs
											WHERE $_DBC_justifs_comp_id=$_SESSION[comp_id]
												ORDER BY $_DBC_justifs_intitule ASC");

			$rows=db_num_rows($result);

			print("<form action='$php_self' method='POST' name='form1'>
					 <div class='centered_box'>
						<font class='Texte'>Justificatif à modifier : </font>
						<select name='justif_id' size='1'>\n");

			$old_univ="";

			for($i=0; $i<$rows; $i++)
			{
				list($justif_id, $intitule)=db_fetch_row($result,$i);

				$value=htmlspecialchars($intitule, ENT_QUOTES, $default_htmlspecialchars_encoding);

				print("<option value='$justif_id' label=\"$value\">$value</option>\n");
			}

			db_free_result($result);

			print("</select>
					</div>
					<div class='centered_icons_box'>\n");

			if(isset($succes))
				print("<a href='index.php' target='_self'><img src='$__ICON_DIR/rew_32x32_fond.png' alt='Retour' border='0'></a>\n");
			else
				print("<a href='index.php' target='_self'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>\n");

			print("<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant' value='Suivant'>
					</form>
					</div>\n");

			if(isset($erreur_selection))
				message("Erreur de sélection du justificatif", $__ERREUR);
		}
		else // élément choisi, on récupère les infos actuelles
		{
			if(isset($_GET["a"]) || isset($_SESSION["ajout"]))
			{
				if(!isset($intitule)) // un seul test devrait suffire
					$intitule=$titre=$texte="";
			}
			else
			{
				$result=db_query($dbr,"SELECT $_DBC_justifs_intitule, $_DBC_justifs_titre, $_DBC_justifs_texte
													FROM $_DB_justifs
												WHERE $_DBC_justifs_id='$justif_id'");

				list($intitule, $titre, $texte)=db_fetch_row($result,0);

				db_free_result($result);
			}

			print("<form name='form1' enctype='multipart/form-data' method='POST' action='$php_self'>\n");

			if(isset($justif_id))
				print("<input type='hidden' name='justif_id' value='$justif_id'>\n");
	?>
		<table align='center'>
		<tr>
			<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
				<font class='Texte_menu2'>
					<b>&#8226;&nbsp;&nbsp;Informations</b>
				</font>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Intitulé (court)</b></font>
			</td>
			<td class='td-droite fond_menu'>
				<input type='text' name='intitule' value='<?php if(isset($intitule)) echo htmlspecialchars(stripslashes($intitule), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='196' size='70'>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Titre du paragraphe<br>(affiché en gras)</b></font>
			</td>
			<td class='td-droite fond_menu'>
				<input type='text' name='titre' value='<?php if(isset($titre)) echo htmlspecialchars(stripslashes($titre), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='256' size='70'>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Paragraphe</b></font>
			</td>
			<td class='td-droite fond_menu'>
				<textarea name='texte' rows='20' cols='80'><?php
					if(isset($texte)) echo htmlspecialchars(stripslashes($texte), ENT_QUOTES, $default_htmlspecialchars_encoding);
				?></textarea>
			</td>
		</tr>
		</table>

		<div class='centered_icons_box'>
			<?php
				if(!isset($_SESSION["ajout"]))
					print("<a href='justificatif.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");
				elseif(isset($_GET["succes"]))
					print("<a href='index.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");

				if(!isset($succes))
					print("<a href='index.php' target='_self'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>\n");
			?>

			<input type='image' class='icone' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='valider' value='Valider'>
			</form>
		</div>

		<?php
		}

		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>
<script language="javascript">
	document.form1.justif_id.focus()
</script>

</body></html>
