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

	unset($_SESSION["position"]);
	unset($_SESSION["ordre"]);
	unset($_SESSION["ajout"]);

	// récupération de l'id de la lettre
	if(isset($_GET["lettre_id"]))
		$_SESSION["lettre_id"]=$lettre_id=$_GET["lettre_id"];
	elseif(isset($_SESSION["lettre_id"]))
		$lettre_id=$_SESSION["lettre_id"];
	else	// pas de numéro de lettre : retour à l'index
	{
		header("Location:index.php");
		exit;
	}
	
	// type de lettre
	
	if($lettre_id==-1) // nouvelle lettre
	{
		$dbr=db_connect();

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
			$comp_largeur_logo=32;
		}

		db_free_result($res_comp);

		// Par défaut, une nouvelle lettre utilise les paramètres de la composante (logo, textes, etc), d'où les TRUE pour les 4 derniers champs et le "1" (flag_date)

		// Paramètre à déplacer ?
		$default_lang='FR';

		$lettre_id=db_locked_query($dbr, $_DB_lettres, "INSERT INTO $_DB_lettres VALUES ('##NEW_ID##', '$_SESSION[comp_id]', 'Nouvelle lettre', '', '', '', '', '$comp_largeur_logo','TRUE','TRUE','TRUE','TRUE','TRUE','0','1','TRUE', '$comp_adr_pos_x','$comp_adr_pos_y', 'TRUE', '$comp_corps_pos_x','$comp_corps_pos_y','$default_lang')");

		// on redirige vers l'url contenant l'id de la nouvelle lettre (pour eviter les 'reload' avec id=-1)

		header("Location:editeur.php?lettre_id=$lettre_id");
		exit;
	}
	else // affichage de la lettre en cours de rédaction
	{
		$dbr=db_connect();

		 // si l'id passée en paramètre n'est pas correcte...
		if(!db_num_rows(db_query($dbr,"SELECT $_DBC_lettres_id FROM $_DB_lettres WHERE $_DBC_lettres_id='$lettre_id'")))
		{
			db_close($dbr);
			header("Location:index.php");
			exit;
		}

		// on détermine tous les éléments du corps de la lettre (position=0)
		$elements_corps=get_all_elements($dbr, $lettre_id);
		$_SESSION["cbo"]=$nb_elem_corps=count($elements_corps); // ordre courant pour l'ajout d'un élément du corps
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	// menu_sup_simple();

	// =======================================================
	// 							 TITRE DE LA LETTRE
	// ======================================================

	$result=db_query($dbr,"SELECT $_DBC_lettres_titre FROM $_DB_lettres WHERE $_DBC_lettres_id='$lettre_id'");
	$rows=db_num_rows($result);

	if($rows) // si différent de 1, on a un sacré problème...
	{
		list($lettre_titre)=db_fetch_row($result,0);
		$date_creation=date_fr("j F Y - H:i:s", id_to_date($lettre_id));

		$chemin=array("Editeur : Menu Principal" => "index.php", "$lettre_titre" => "");
	}
	else
	{
		$err_file=realpath(__FILE__);
		$line=__LINE__;
		
		if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
		{
			mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Lettre non trouvée'\nUtilisateur : $_SESSION[auth_user]\nLettre en cause : $lettre_id");
			die("Erreur dans la base de données. Un courriel a été envoyé à l'administrateur.");
		}
		else
			die("Erreur dans la base de données. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
	}

	db_free_result($result);

	menu_sup_gestion();
?>

<div class='main' style='padding:0px;'>
	<div class='menu_haut_2'>
		<a href='index.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/abiword_16x16_menu2.png"; ?>'></a>
		<a href='index.php' target='_self' class='lien_menu_haut_2'>Liste des lettres</a>
		<a href='tableau.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/kdeprint_report_16x16_menu2.png"; ?>'></a>
		<a href='tableau.php' target='_self' class='lien_menu_haut_2'>Tableau récapitulatif</a>
		<?php
			if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
			{
		?>

			<a href='parametres.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/preferences_16x16_menu2.png"; ?>' alt='parametres'></a>
			<a href='parametres.php' target='_self' class='lien_menu_haut_2'>Paramètres par défaut</a>
		<?php
			}
		?>
			<a href='editeur.php?lettre_id=-1'  target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' alt='+'></a>
			<a href='editeur.php?lettre_id=-1'  target='_self' class='lien_menu_haut_2'>Créer une nouvelle lettre</a>
		<?php
			if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
			{
				print("<a href='copie_lettre.php?lettre_id=$lettre_id' target='_self'><img class='icone_menu_haut_2' border='0' src='$__ICON_DIR/editcopy_16x16_menu2.png' alt='+'></a>
						 <a href='copie_lettre.php?lettre_id=$lettre_id' target='_self' class='lien_menu_haut_2'>Dupliquer cette lettre</a>\n");
			}
		?>
	</div>
	<div class='menu_gauche' style='width:160px;'>
		<form method='POST' action='ajout_element.php'>
		<input type='hidden' name='act' value='1'>
		<?php
			// Menu colonne gauche
			include "include/menu_editeur_v3.php";
		?>
	</div>
	<div class='corps' style='margin-left:170px;'>
		<?php
				

				print("<p>
							<font class='Texte_16'><strong>Edition de la lettre \"$lettre_titre\"</strong> </font><font class='Texte'>(Créée le $date_creation)</font>
						 </p>\n");

		// ===========================================
		// ==== AFFICHAGE DES ELEMENTS DU CORPS ====
		// ==========================================
		// on boucle sur le tableau (array) contenant tous les éléments, $i étant l'ordre de ces éléments

		if($nb_elem_corps)
		{
			print("<table class='layout0' width='99%' align='center'>");
			// print("<table width='100%' border='1' cellpadding='1' cellspacing='0' align='center'>");

			for($i=0; $i<$nb_elem_corps; $i++)
			{
				// variable pour les liens (move_element.php, etc)
				if($i!=0)
				{
					$j=$i-1; // élément précédent
					$tt=$elements_corps["$j"]["type"]; // target type (tt)
				}
				else
					$tt=-1;

				if($i!=($nb_elem_corps-1))
				{
					$k=$i+1; // élément suivant
					$tt2=$elements_corps["$k"]["type"]; // target type (tt)
				}
				else
					$tt2=-1;

				// variables communes à tous les types d'éléments
				
				$element_id=$elements_corps["$i"]["id"];
				$element_type=$elements_corps["$i"]["type"];

				// nouvelle ligne dans le tableau pour l'élément en cours
				print("<tr>
							<td width='50' align='left' style='white-space:nowrap'>
								<input type='radio' name='position_insertion_corps' value='$i' style='vertical-align:middle;'>\n");

				show_up_down2($i,$nb_elem_corps,$element_type,$tt,$tt2);

				switch($element_type)
				{
					case 2	:	// encadré
									$txt=nl2br($elements_corps["$i"]["texte"]);
									// $align=$elements_corps["$i"]["alignement"];
									$txt_align=$elements_corps["$i"]["txt_align"];

									// alignement du tableau dans le corps
									// $alignement_tableau=get_align($align);

									// alignement du texte dans le tableau
									$alignement_txt=get_align($txt_align);

									print("<a href='encadre.php?o=$i' target='_self'><img src='$__ICON_DIR/edit_16x16.png' alt='Editer' border='0'></a>
											</td>
											<td>
												<table class='cadre'>
												<tr>
													<td class='cadre Texte' style='text-align:$alignement_txt'>$txt</td>
												</tr>
												</table>
											</td>\n");
									break;

					case 5	:	// paragraphe
									$txt_align=$elements_corps["$i"]["txt_align"];
									$txt_gras=$elements_corps["$i"]["gras"];
									$txt_italique=$elements_corps["$i"]["italique"];
									$txt_taille=$elements_corps["$i"]["taille"];
									$txt_marge_gauche=$elements_corps["$i"]["marge_gauche"];

									// alignement du texte du paragraphe
									$alignement_txt=get_align($txt_align);

									// Pour afficher correctement les espaces de mise en page
									$txt=nl2br(str_replace("<br /><br />", "<br>", str_replace(" ", "&nbsp; ", $elements_corps["$i"]["texte"])));
									// $txt=nl2br(str_replace("<br /><br />", "<br>", preg_replace("/\s{2}/", "&nbsp; ", $elements_corps["$i"]["texte"])));

									$font_size="font-size:$txt_taille" . "px;";

									$weight=$txt_gras ? "font-weight:bold;" : "";
									$style=$txt_italique ? "font-style:italic;" : "";

									// calcul du décalage (marge gauche)
									// La valeur entrée est en millimetres. Pour l'affichage sous forme de tableau, il faut la
									// convertir en pourcentage de la largeur du tableau

									if($txt_marge_gauche!=0)
									{
										$txt_marge="<div style='float:left'>
															<font class='Textegris'><i>[Marge $txt_marge_gauche mm]</font>
														</div>";
										$width="padding-left:". floor(($txt_marge_gauche/210)*100) . "%";
									}
									else
										$txt_marge=$width="";

									print("<a href='paragraphe.php?o=$i' target='_self'><img src='$__ICON_DIR/edit_16x16.png' alt='Editer' border='0'></a>
											</td>
											<td align='$alignement_txt'>
												$txt_marge
												<div style='$width'>
													<font class='Texte' style='$font_size $weight $style'>$txt<br></font>
												</div>
											</td>\n");

									break;
									
					case 8	:	// séparateur
									$hauteur=$elements_corps["$i"]["nb_lignes"]*16; // 16px est la hauteur d'une ligne (icône)

									$s=$elements_corps["$i"]["nb_lignes"]>1 ? "s" : "";

									print("</td>
												<td align='left' nowrap='true' height='$hauteur'>
													<font class='Textegris'><i>----- ".$elements_corps["$i"]["nb_lignes"]." ligne$s vide$s -----</i></font><br>
												</td>\n");
									break;
				}

				print("<td align='right' width='20'>
							<a href='suppr_element.php?o=$i' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick.png' alt='Supprimer' border='0'></a>
						</td>
					</tr>\n");
			}
			print("<tr>
						<td align='left' nowrap='true' colspan='3'>
							<input type='radio' name='position_insertion_corps' value='$i'>
						</td>
					</tr>
					</table>");
		}
		?>
		</form>
	</div>
</div>
<?php
	pied_de_page();
	db_close($dbr);
?>
</body></html>

