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
<div class='menu_haut_2'>
	<form method='POST' action='ajout_element.php'>
	<input type='hidden' name='act' value='1'>
	<?php
		if(array_key_exists("filtre_justif", $_SESSION) && $_SESSION["filtre_justif"]!=-1)
		{
			print("<a href='apercu.php' target='_blank'><img class='icone_menu_haut_2' src='$__ICON_DIR/view_text_16x16_menu2.png' border='0' alt='+'></a>
					 <a href='apercu.php' target='_blank' class='lien_menu_haut_2'>Aperçu</a>\n");
		}
	?>

	<a href='justificatif.php?a=1' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' alt='+'></a>
	<a href='justificatif.php?a=1' target='_self' class='lien_menu_haut_2'>Créer un justificatif</a>

	<a href='justificatif.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/edit_16x16_menu2.png"; ?>' alt='+'></a>
	<a href='justificatif.php' target='_self' class='lien_menu_haut_2'>Modifier un justificatif</a>

	<a href='suppr_justif_base.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/trashcan_full_16x16_slick_menu2.png"; ?>' alt='+'></a>
	<a href='suppr_justif_base.php' target='_self' class='lien_menu_haut_2'>Supprimer un justificatif de la Base de Données</a>

	<input style='vertical-align:middle; padding-right:5px;' type='image' src='<?php echo "$__ICON_DIR/randr_16x16_menu2.png"; ?>' border='0' name='ajout_justif' alt='+' value=''><input type='submit' class='menu_haut_2' style='width:150px;' name='ajout_justif' alt='Rattacher un justificatif' value='Rattacher un justificatif'>

	<a href='fichiers.php' target='_self'><img class='icone_menu_haut_2' src='<?php echo "$__ICON_DIR/fileopen_16x16_menu2.png"; ?>' border='0' alt='+'></a>
	<a href='fichiers.php' target='_self' class='lien_menu_haut_2'>Fichiers attachés</a>
</div>
