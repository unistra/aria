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
	
	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	unset($_SESSION["position"]);
	unset($_SESSION["ordre"]);
	unset($_SESSION["ajout"]);

/*
	// récupération de l'id de la lettre
	if(isset($_SESSION["info_doc_id"]))
		$info_doc_id=$_SESSION["info_doc_id"];
	else	// pas de numéro de lettre : retour à l'index
	{
		header("Location:../index.php");
		exit;
	}
*/

	$dbr=db_connect();

	if(!isset($_SESSION["info_doc_id"]))
	{
		$result=db_query($dbr, "SELECT $_DBC_comp_infos_id FROM $_DB_comp_infos
																	WHERE $_DBC_comp_infos_comp_id='$_SESSION[comp_id]'");

		if(db_num_rows($result))
			list($_SESSION["info_doc_id"])=db_fetch_row($result, 0);
		else
		{
/*
			$_SESSION["info_doc_id"]=time();

			while(db_num_rows(db_query($dbr, "SELECT * from $_DB_comp_infos WHERE $_DBC_comp_infos_id='$_SESSION[info_doc_id]'")))
				$_SESSION["info_doc_id"]++;
*/
			$_SESSION["info_doc_id"]=db_locked_query($dbr, $_DB_comp_infos, "INSERT INTO $_DB_comp_infos VALUES ('##NEW_ID##', '$_SESSION[comp_id]')");
		}
	}

	$elements_corps=get_all_elements($dbr, $_SESSION["info_doc_id"]);
	$_SESSION["cbo"]=$nb_elem_corps=count($elements_corps); // ordre courant pour l'ajout d'un élément du corps


	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main' style='padding:0px;'>
	<div class='menu_gauche' style='width:140px;'>
		<form method='POST' action='ajout_element.php'>
		<input type='hidden' name='act' value='1'>
		<?php
			include "include/menu_editeur_v3.php";
		?>
	</div>
	<div class='corps' style='margin-left:145px;'>
		<div class='centered_box'>
			<font face='Arial' color='black' size='+1'><b>Informations Importantes</b></font>
		</div>

		<?php
		// ===========================================
		// ==== AFFICHAGE DES ELEMENTS DU CORPS ====
		// ==========================================
		// on boucle sur le tableau (array) contenant tous les éléments, $i étant l'ordre de ces éléments

		if($nb_elem_corps)
		{
			print("<table class='layout0' width='98%' align='center'>");
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
							<td align='left' style='white-space:nowrap; width:80px' nowrap>
								<input type='radio' name='position_insertion_corps' value='$i'>\n");

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
									$txt=nl2br($elements_corps["$i"]["texte"]);
									$txt_align=$elements_corps["$i"]["txt_align"];
									$txt_gras=$elements_corps["$i"]["gras"];
									$txt_italique=$elements_corps["$i"]["italique"];
									$txt_taille=$elements_corps["$i"]["taille"];

									// alignement du texte du paragraphe
									$alignement_txt=get_align($txt_align);

									// Pour afficher correctement les espaces de mise en page
									$txt=nl2br($txt);
									$txt=str_replace("<br /><br />", "<br>", $txt);

									$txt=str_replace("  ", "&nbsp;&nbsp;", $txt);

									$font_size="font-size:$txt_taille" . "px;";

									if($txt_gras)
										$weight="font-weight:bold;";
									else
										$weight="";

									if($txt_italique)
										$style="font-style:italic;";
									else
										$style="";

									print("<a href='paragraphe.php?o=$i' target='_self'><img src='$__ICON_DIR/edit_16x16.png' alt='Editer' border='0'></a>
											</td>
											<td align='$alignement_txt'>
												<font class='Texte' style='$font_size $weight $style'>$txt<br></font>
											</td>\n");

									break;

					case 6	:	// Fichier
									$txt=nl2br($elements_corps["$i"]["texte"]);
									// $align=$elements_corps["$i"]["alignement"];
									$fichier=$elements_corps["$i"]["fichier"];

									print("<a href='fichiers.php?o=$i' target='_self'><img src='$__ICON_DIR/edit_16x16.png' alt='Editer' border='0'></a>
											</td>
											<td>
												<table>
												<tr>
													<td style='text-align:center;width:20px; border:none;'>
														<img src='$__ICON_DIR/fileopen_16x16_blanc.png' border='0'>
													</td>
													<td style='text-align:left; border:none;'>
														<a href='$__CAND_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$fichier' class='lien2'>$txt</a>
													</td>
												</tr>
												</table>
											</td>\n");
									break;

					case 8	:	// séparateur
									print("</td>
												<td align='left' nowrap='true'>
													<font class='Textegris'><i>----- ligne vide -----</i></font><br>
												</td>\n");
									break;
				}

				print("<td align='right' width='20'>
							<a href='suppr_element.php?&o=$i' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick.png' alt='Supprimer' border='0'></a>
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

