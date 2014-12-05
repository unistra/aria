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
	header ("Content-type: image/png");
/*
	$im = @imagecreatetruecolor (100, 50) or die ("Impossible de crée un flux d'image GD");
	
	$red = 255;
	$green = 255;
	$blue = 255;
	$color = ImageColorAllocate( $im, $red, $green, $blue );

	imagefill($im,0,0,$color);

	$text_color = imagecolorallocate ($im, 0, 0, 0);
//	imagestring ($im, 1, 5, 5,  "Une simple chaîne de texte", $text_color);

	// Chargement de la fonte
	putenv('GDFONTPATH=' . realpath('.'));
	$font="vera_sans";

	$texte="Texte simple";
	imagettftext($im, 12, 0, 10, 20, $text_color, $font, $texte);
*/
	$im=imagecreatefrompng("../images/fond_image_confirmation2.png");

	// Chargement de la fonte
	putenv('GDFONTPATH=' . realpath('.'));
	$font="vera_sans.ttf";

	// affichage du texte
	$text_color = imagecolorallocate ($im, 100, 100, 100);
	imagettftext($im, 16, 0, 30, 25, $text_color, $font, $_SESSION["code_conf"]);

	imagepng ($im);
	imagedestroy ($im);
?>