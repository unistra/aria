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
<div class='menu_haut_2'>
	<form method='POST' action='ajout_element.php'>
	<input type='hidden' name='act' value='1'>
	<?php
		if(array_key_exists("filtre_justif", $_SESSION) && $_SESSION["filtre_justif"]!=-1)
		{
			print("<a href='apercu.php' target='_blank'><img class='icone_menu_haut_2' src='$__ICON_DIR/view_text_16x16_menu2.png' border='0' alt='+'></a>
					 <a href='apercu.php' target='_blank' class='lien_menu_haut_2'>Aper�u</a>\n");
		}
	?>

	<a href='justificatif.php?a=1' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' alt='+'></a>
	<a href='justificatif.php?a=1' target='_self' class='lien_menu_haut_2'>Cr�er un justificatif</a>

	<a href='justificatif.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/edit_16x16_menu2.png"; ?>' alt='+'></a>
	<a href='justificatif.php' target='_self' class='lien_menu_haut_2'>Modifier un justificatif</a>

	<a href='suppr_justif_base.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/trashcan_full_16x16_slick_menu2.png"; ?>' alt='+'></a>
	<a href='suppr_justif_base.php' target='_self' class='lien_menu_haut_2'>Supprimer un justificatif de la Base de Donn�es</a>

	<input style='vertical-align:middle; padding-right:5px;' type='image' src='<?php echo "$__ICON_DIR/randr_16x16_menu2.png"; ?>' border='0' name='ajout_justif' alt='+' value=''><input type='submit' class='menu_haut_2' style='width:150px;' name='ajout_justif' alt='Rattacher un justificatif' value='Rattacher un justificatif'>

	<a href='fichiers.php' target='_self'><img class='icone_menu_haut_2' src='<?php echo "$__ICON_DIR/fileopen_16x16_menu2.png"; ?>' border='0' alt='+'></a>
	<a href='fichiers.php' target='_self' class='lien_menu_haut_2'>Fichiers attach�s</a>
</div>
