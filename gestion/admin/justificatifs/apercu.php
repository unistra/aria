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
	include "$__INCLUDE_DIR_ABS/access_functions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	$Y=date("Y");
	$Z=$Y+1;

	// Largeur max du corps, en mm
	$__LARGEUR_MAX_CORPS="135";

	$dbr=db_connect();

	$result=db_query($dbr, "SELECT $_DBC_justifs_id, $_DBC_justifs_titre, $_DBC_justifs_texte, $_DBC_justifs_jf_nationalite
										FROM $_DB_justifs, $_DB_justifs_jf
									WHERE $_DBC_justifs_jf_propspec_id='$_SESSION[filtre_justif]'
									AND $_DBC_justifs_jf_justif_id=$_DBC_justifs_id
										ORDER BY $_DBC_justifs_jf_ordre");

	$rows=db_num_rows($result);

	if($rows)
	{
		// Utilisation de la librairie fpdf (libre)
		require("$__FPDF_DIR_ABS/fpdf.php");

		// Création du PDF
		$justificatifs=new FPDF("P","mm","A4");

		$justificatifs->SetCreator("Application de Gestion des Candidatures de l'Université de Strasbourg");
		$justificatifs->SetAuthor("Christophe BOCCHECIAMPE - UFR de Mathématique et d'Informatique - Université de Strasbourg");
		$justificatifs->SetSubject("Justificatifs");
		$justificatifs->SetTitle("Justificatifs");

		// saut de page automatique, à 15mm du bas
		$justificatifs->SetAutoPageBreak(1,11);
		// $justificatifs->SetMargins(11,11,11);

		$justificatifs->AddPage();

		$justificatifs->SetXY(13, 24);
		// TODO : ATTENTION : NE PAS OUBLIER DE GENERER LA FONTE ARIBLK.TTF LORS D'UN CHANGEMENT DE MACHINE
		// $justificatifs->AddFont("arial_black");
		// $justificatifs->SetFont('arial_black','',12);
		// $justificatifs->SetTextColor(0, 91, 209);
		// $justificatifs->Cell(42,5,"UFR",0, 2, "R");
		// $justificatifs->Cell(42,5,"de mathématique",0, 2, "R");
		//$justificatifs->Cell(42,5,"et d'informatique",0, 2, "R");

		// $justificatifs->Line(11, 41, 53, 41);
		// $justificatifs->image('logo_ulp_300px.jpg', 21, 44, 32, 18, 'JPG');

		$justificatifs->SetFont('arial','',10);
	/*
		$justificatifs->SetXY(104, 49);
	*/
		$justificatifs->SetTextColor(0, 0, 0);
	/*
		$candidat_adresse="$civ_texte " .  $candidat_array["nom"] . " " . $candidat_array["prenom"] . "\n" . $candidat_array["adresse"];

		$justificatifs->MultiCell(0,5,$candidat_adresse, 0, "L");
	*/

		// Premier élément : position fixe (à affiner manuellement, sans doute)
		// $justificatifs->SetXY(60, 78);

		$justificatifs->SetXY(20, 15);
		$justificatifs->SetFont('arial',"B",14);
		$justificatifs->MultiCell(0, 5, "JUSTIFICATIFS A FOURNIR", 0, "C");

		$justificatifs->SetXY(20, 30);

		for($j=0; $j<$rows; $j++)
		{
			list($elem_id, $elem_int, $elem_para, $elem_nat)=db_fetch_row($result, $j);

			$justificatifs->SetFont('arial',"B",10);
			$justificatifs->SetX(20);
			$justificatifs->MultiCell(0, 5, $elem_int, 0, "J");

			$justificatifs->SetFont('arial',"",10);
			$justificatifs->SetX(20);
			$justificatifs->MultiCell(0, 5, $elem_para, 0, "J");

			$justificatifs->Ln(5);
		}

		// Génération du fichier et copie dans le répertoire

		$nom_fichier=clean_str($_SESSION["auth_user"] . "_apercu_Justificatifs.pdf");
		// $justificatifs->Output("$nom_fichier", "I");

		$justificatifs->Output("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$nom_fichier");

		// Attention : ce chemin doit être relatif à www-root (document_root du serveur Apache)
		echo "<HTML><SCRIPT>document.location='$__GESTION_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$nom_fichier';</SCRIPT></HTML>";
	}

	db_free_result($result);
	db_close($dbr);
?>
