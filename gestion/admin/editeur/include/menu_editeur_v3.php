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
<ul class='menu_gauche'>
	<li class='menu_gauche'><strong>Lettre :</strong></li>
	<li class='menu_gauche'>
		<a href='edit_proprietes.php' target='_self'><img class='icone_menu_gauche' style='vertical-align:middle; padding-right:10px;' src='<?php echo "$__ICON_DIR/edit_16x16_menu2.png"; ?>' border='0' alt='+'></a>
		<a href='edit_proprietes.php' target='_self' class='lien_menu_gauche'>Propriétés</a>
	</li>
	<li class='menu_gauche'>
		<a href='edit_liens.php' target='_self'><img class='icone_menu_gauche' style='vertical-align:middle; padding-right:10px;' src='<?php echo "$__ICON_DIR/randr_16x16_menu2.png"; ?>' border='0' alt='+'></a>
		<a href='edit_liens.php' target='_self' class='lien_menu_gauche'>Décisions/Formations</a>
	</li>
	<li class='menu_gauche'>
		<a href='apercu.php?lettre_id=<?php echo $_SESSION["lettre_id"]; ?>' target='_blank'><img class='icone_menu_gauche' style='vertical-align:middle; padding-right:10px;' src='<?php echo "$__ICON_DIR/view_text_16x16_menu2.png"; ?>' border='0' alt='+'></a>
		<a href='apercu.php?lettre_id=<?php echo $_SESSION["lettre_id"]; ?>' target='_blank' class='lien_menu_gauche'>Aperçu</a>
	</li>
	<li class='menu_gauche' style='padding-bottom:20px;'>
		<a href='suppr_lettre.php' target='_self'><img class='icone_menu_gauche' style='vertical-align:middle; padding-right:10px;' src='<?php echo "$__ICON_DIR/trashcan_full_16x16_slick_menu2.png"; ?>' border='0' alt='-'></a>
		<a href='suppr_lettre.php' target='_self' class='lien_menu_gauche'>Supprimer la lettre</a>
	</li>
	<li class='menu_gauche'><strong>Rédaction :</strong></li>
	<li class='menu_gauche'>
		<input class='menu_gauche' type='image' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' border='0' name='ajout_paragraphe' alt='+' value=''>
		<input class='menu_gauche' type='submit' name='ajout_paragraphe' alt='Paragraphe' value='Paragraphe'>
	</li>
	<li class='menu_gauche'>
		<input class='menu_gauche' type='image' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' border='0' name='ajout_encadre' alt='+' value=''>
		<input class='menu_gauche' type='submit' name='ajout_encadre' alt='Encadré' value='Encadré'>
	</li>
	<li class='menu_gauche'>
		<input class='menu_gauche' type='image' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' border='0' name='ajout_separateur' alt='+' value=''>
		<input class='menu_gauche' type='submit' name='ajout_separateur' alt='Ligne vide' value='Ligne vide'>
	</li>
</ul>
