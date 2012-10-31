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
	// Vérifications complémentaires au cas où ce fichier serait appelé directement
	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:composantes.php");
		exit();
	}

	print("<div class='centered_box'>
				<font class='TitrePage_16'>$_SESSION[onglet] - Informations complémentaires et expériences professionnelles</font>
			 </div>");

	message("Cette section sert à ajouter d'éventuelles informations sur votre parcours professionnel (stages, emplois, formations, service national, ...).
				<br>Si vous avez arrêté, puis repris vos études, vous pouvez également le mentionner ici.", $__INFO);


	$result=db_query($dbr,"SELECT $_DBC_infos_comp_id, $_DBC_infos_comp_texte, $_DBC_infos_comp_annee, $_DBC_infos_comp_duree
										FROM $_DB_infos_comp
									WHERE $_DBC_infos_comp_candidat_id='$candidat_id'
										ORDER BY $_DBC_infos_comp_annee DESC");
	$rows=db_num_rows($result);

	if($rows)
	{
		print("<table style='margin:0px auto 0px auto;'>\n");

		for($i=0; $i<$rows; $i++)
		{
			list($iid, $info,$annee,$duree)=db_fetch_row($result,$i);
			$info=str_replace("\n"," - ",$info);

			if($duree=="")
				$dur="";
			else
				$dur="($duree)";

			if($_SESSION["lock"]!=1)
			{
				$crypt_params=crypt_params("iid=$iid");
				print("<tr>
							<td class='td-gauche fond_menu' style='white-space: normal;'>
								<a href='info.php?p=$crypt_params' class='lien_menu_gauche'><b>$annee</b> : $info $dur</a>
							</td>
							<td class='td-droite fond_menu' style='text-align:right;'>
								<a href='suppr_info.php?p=$crypt_params' target='_self' class='lien_menu_gauche'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' border='0'></a>
							</td>
						</tr>
						<tr>
							<td colspan='2' height='20' class='td-separation fond_page'></td>
						</tr>\n");
			}
			else
				print("<tr>
							<td class='td-gauche fond_menu' style='white-space: normal;'>
								<font class='Texte_menu'><b>$annee</b> : $info $dur</font>
							</td>
						</tr>
						<tr>
							<td height='20' class='td-separation fond_page'></td>
						</tr>\n");
		}

		print("</table>");
	}

	db_free_result($result);

	if($_SESSION["lock"]!=1)
		print("<div class='centered_box'>
					<a href='info.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter'></a>
					<a href='info.php' target='_self' class='lien2'>Ajouter une information</a>
				</div>");
	else
		message("<center>Une composante a déjà verrouillé l'un de vos voeux : vous ne pouvez plus modifier ces informations en ligne.
					<br><strong>Toute information complémentaire doit être envoyée par courrier aux composantes concernées</strong></center>", $__ERREUR);
?>
