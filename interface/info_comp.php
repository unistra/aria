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
	session_name("preinsc");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

/*
	// récupération de l'id de la lettre
	if(isset($_SESSION["info_doc_id"]))
		$info_doc_id=$_SESSION["info_doc_id"];
	else	// pas de numéro de lettre : retour à l'index
	{
		session_write_close();
		header("Location:../index.php");
		exit;
	}
*/

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		// header("Location:../index.php");
		header("Location:" . base_url($php_self) . "../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]))
	{
		session_write_close();
		// header("Location:composantes.php");
		header("Location:" . base_url($php_self) . "composantes.php");
		exit();
	}

	$dbr=db_connect();
	$result=db_query($dbr, "SELECT $_DBC_comp_infos_id FROM $_DB_comp_infos WHERE $_DBC_comp_infos_comp_id='$_SESSION[comp_id]'");

	if(db_num_rows($result))
		list($doc_id)=db_fetch_row($result, 0);
	else
	{
		db_close($dbr);
		
		session_write_close();
		// header("Location:precandidatures.php");
		header("Location:" . base_url($php_self) . "precandidatures.php");
		exit();
	}

	$elements_corps=get_infos_elements($dbr, $doc_id);
	$nb_elem_corps=count($elements_corps);
	
	en_tete_candidat();
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Informations importantes", "messagebox_warning_32x32_fond.png", 15, "L");
	
	// ===========================================
	// ==== AFFICHAGE DES ELEMENTS DU CORPS ====
	// ==========================================
	// on boucle sur le tableau (array) contenant tous les éléments, $i étant l'ordre de ces éléments

	if($nb_elem_corps)
	{
		print("<table width='90%' align='center' border='0' cellpadding='4'>");
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
			print("<tr>\n");

			switch($element_type)
			{
				case 2	:	// encadré
									$txt=nl2br($elements_corps["$i"]["texte"]);
									// $align=$elements_corps["$i"]["alignement"];
									$txt_align=$elements_corps["$i"]["txt_align"];
									
									// alignement du tableau dans le corps
									// $alignement_tableau=get_info_align($align);
									
									// alignement du texte dans le tableau
									$alignement_txt=get_info_align($txt_align);

									// Transformation des URL en liens
									// $txt=ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a class='lien2' target='_blank' href=\"\\0\">\\0</a>", $txt);

									print("<td align='center'>
												<table cellpadding='4' align='center' style='border:1px black solid;'>
												<tr>
													<td class='td-complet fond_menu' style='text-align:$alignement_txt; border-size:1px; border-color:black; padding:4px;'>
														<font class='Texte_menu'>".parse_macros($txt)."</font>
													</td>
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
									$alignement_txt=get_info_align($txt_align);

									// Pour afficher correctement les espaces de mise en page
									$txt=nl2br($txt);
									$txt=str_replace("<br /><br />", "<br>", $txt);
									$txt=str_replace("  ", "&nbsp;&nbsp;", $txt);

/*
									$new_txt="";

									foreach(preg_split("/(\[lien=[[:alpha:]]+:\/\/[^<>[:space:]]+[[:alnum:]]\].*?\[\/lien\])/", $txt, -1, PREG_SPLIT_DELIM_CAPTURE) as $texte)
									{
										// Transformation des url au format [lien=...]description[/lien]
										if(preg_match("/\[lien=[[:alpha:]]+:\/\/[^<>[:space:]]+[[:alnum:]]\].*?\[\/lien\]/", $texte))
											$new_txt.=preg_replace("/\[lien=([[:alpha:]]+:\/\/[^<>[:space:]]+[[:alnum:]])\](.*)?\[\/lien\]/", "<a class='lien2' target='_blank' href=\"\\1\">\\2</a>", $texte);
										else // Transformation des url brutes en liens
											$new_txt.=ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a class='lien2' target='_blank' href=\"\\0\">\\0</a>", $texte);
									}

									$txt=$new_txt;
*/
									$font_size="font-size:$txt_taille" . "px;";

									if($txt_gras)
										$weight="font-weight:bold;";
									else
										$weight="";
										
									if($txt_italique)
										$style="font-style:italic;";
									else
										$style="";

									print("<td align='$alignement_txt'>
													<font class='Texte' style='$font_size $weight $style'>".parse_macros($txt)."<br></font>
												</td>\n");

									break;

				case 6	:	// Fichier
									$txt=nl2br($elements_corps["$i"]["texte"]);
									$fichier=$elements_corps["$i"]["fichier"];

									print("<td align='center'>
													<table cellpadding='4' align='left'>
													<tr>
														<td align='center' width='20'>
															<img src='$__ICON_DIR/fileopen_16x16_fond.png' border='0'>
														</td>
														<td align='center'>
															<a href='$__CAND_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$fichier' class='lien_bleu_12'>$txt</a>
														</td>
													</tr>
													</table>
												</td>\n");

									break;

				case 8	:	// séparateur
									print("<td align='left' nowrap='true' height='10'></td>\n");
									break;
			}

			print("</tr>\n");
		}
		print("</table>\n");
	}
	?>

	<div class='centered_box'>
		<a href='precandidatures.php' target='_self'><img src='<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>' alt='Continuer' border='0'></a>
	</div>
</div>
<?php
	pied_de_page_candidat();
	db_close($dbr);
?>
</body></html>

