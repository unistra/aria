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
<?php
session_name("preinsc_gestion");
session_start();

include "../../configuration/aria_config.php";
include "$__INCLUDE_DIR_ABS/vars.php";
include "$__INCLUDE_DIR_ABS/fonctions.php";
include "$__INCLUDE_DIR_ABS/db.php";
include "$__INCLUDE_DIR_ABS/access_functions.php";
// TODO : rassembler les includes pour �viter ce genre de chemins ...
include "../admin/editeur/include/editeur_fonctions.php";

$php_self=$_SERVER['PHP_SELF'];
$_SESSION['CURRENT_FILE']=$php_self;

verif_auth("../login.php");

$dbr=db_connect();

$Y=date("Y");
$Z=$Y+1;

// Largeur max du corps, en mm
// $__LARGEUR_MAX_CORPS="135";

if(array_key_exists("jour", $_GET) && $_GET["jour"]!="" && ctype_digit($_GET["jour"]) && isset($_GET["id_form"]) && ctype_digit($_GET["id_form"]))
{
	$formation=$_GET["id_form"];

	$jour=$_GET["jour"];

	if(isset($_SESSION["cur_entretien_salle"]))
	{
		$ent_salle=$_SESSION["cur_entretien_salle"];
		$condition_salle="AND $_DBC_cand_entretien_salle ilike '$ent_salle'";
	}
	else
	{
		$ent_salle="";
		$condition_salle="";
	}

	// Pour r�cup�rer les bonnes dates, on doit prendre date jour-1:23h59 -> jour:23h59

	$j=date("j", $jour);
	$m=date("m", $jour);
	$y=date("Y", $jour);

	$limite_inf=MakeTime(23,59,0, $m, ($j-1), $y); // PHP calcule automatiquement le passage d'un mois � l'autre
	$limite_sup=MakeTime(23,59,0, $m, $j, $y);

	// V�rification des param�tres
	$result=db_query($dbr, "SELECT $_DBC_cand_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom,
											 $_DBC_cand_entretien_date
										FROM $_DB_cand, $_DB_propspec, $_DB_candidat
									WHERE $_DBC_propspec_id=$_DBC_cand_propspec_id
									AND $_DBC_cand_candidat_id=$_DBC_candidat_id
									AND $_DBC_cand_propspec_id='$formation'
									AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
									AND $_DBC_cand_decision='$__DOSSIER_ENTRETIEN_TEL'
									AND $_DBC_cand_statut='$__PREC_RECEVABLE'
									AND $_DBC_cand_periode='$__PERIODE'
									AND $_DBC_cand_entretien_date BETWEEN '$limite_inf' AND '$limite_sup'
									$condition_salle
										ORDER BY $_DBC_cand_entretien_date, $_DBC_candidat_nom, $_DBC_candidat_prenom");

	$rows=db_num_rows($result);

	if(!$rows) // personne sur la liste
	{
		db_free_result($result);
		db_close($dbr);
	
		mail($_SESSION["mail_admin"], "[Pr�candidatures] - Entretiens t�l�phoniques : g�n�ration de listes", "=> Aucun candidat trouv� par la requ�te.\n\nJour pass� en param�tre : $jour (" . date_fr("j F Y", $jour) . ")\nLimites : " . date_fr("j F Y h:i", $limite_inf) . " => " . date_fr("j F Y h:i", $limite_sup) . "\n\nFormation : $formation");

		header("Location:../masse_listes_entretiens_tel.php?erreur=1");
		exit();
	}
	else
	{
		// Nom de la formation

		$res_formation=db_query($dbr, "SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
													FROM $_DB_annees, $_DB_specs, $_DB_propspec
												 WHERE $_DBC_annees_id=$_DBC_propspec_annee
												 AND $_DBC_specs_id=$_DBC_propspec_id_spec
												 AND $_DBC_propspec_id='$formation'");

		if(!db_num_rows($res_formation))
		{
			header("Location:../masse_listes_entretiens_tel.php?erreur=1");
			exit();
		}
		else
		{
			list($nom_annee, $nom_spec, $finalite)=db_fetch_row($res_formation, 0);

			$formation_txt=$nom_annee=="" ? "" : $nom_annee;

			$formation_txt.=$tab_finalite[$finalite]=="" ? " $nom_spec" : " $nom_spec $tab_finalite[$finalite]";
		}

		// Utilisation de la librairie fpdf (libre)
		require("$__FPDF_DIR_ABS/fpdf.php");

		// Cr�ation du PDF
		$doc_liste=new FPDF("P","mm","A4");

		$doc_liste->SetCreator("Application de Gestion des Candidatures de l'Universit� de Strasbourg");
		$doc_liste->SetAuthor("Christophe BOCCHECIAMPE - UFR de Math�matique et d'Informatique - Universit� de Strasbourg");
		$doc_liste->SetSubject("Liste des candidats convoqu�s � l'entretien t�l�phonique");
		$doc_liste->SetTitle("Liste des candidats convoqu�s � l'entretien t�l�phonique");

		$doc_liste->SetAutoPageBreak(1,11);

		// TODO : ATTENTION : NE PAS OUBLIER DE GENERER LA FONTE ARIBLK.TTF LORS D'UN CHANGEMENT DE MACHINE
		$doc_liste->AddFont("arial_black");
		$doc_liste->SetFont('arial','',10);
		$doc_liste->SetTextColor(0, 0, 0);

		// Premier �l�ment : position fixe (� affiner manuellement, sans doute)
		// $doc_liste->SetXY(60, 78);

		$doc_liste->AddPage();

		$doc_liste->SetXY(11, 11);
		$doc_liste->SetFont('arial',"IB",14);

		$date_jour=date_fr("l jS F Y", $jour);

		$titre_txt="$formation_txt\nEntretiens t�l�phoniques du $date_jour";
		$titre_txt.=$ent_salle!="" ? " - $ent_salle" : "";

		$doc_liste->MultiCell(0, 8, "$titre_txt", 0, "C");

		$doc_liste->Ln(10);

		$doc_liste->SetFont('arial',"",10);

		for($i=0; $i<$rows; $i++)
		{
			list($candidat_id, $candidature_id, $cand_nom, $cand_prenom, $ent_date)=db_fetch_row($result, $i);

			$doc_liste->SetX(11);
			$doc_liste->Cell(140, 5, "$cand_nom $cand_prenom", 1, 0, "L");

			$heure=date_fr("G", $ent_date);

			if($heure)
			{
				$date_txt=$heure . "h" . date_fr("i", $ent_date);

				$doc_liste->Cell(48, 5, $date_txt, 1, 1, "C");
			}
		}

		// write_evt($dbr,$__EVT_ID_G_PREC, "Liste entretiens", $candidat_id, $cand_id);

		$date_fr=date_fr("j_F_Y_H_i", time());
		$nom_fichier=clean_str("Liste_entretiens_tel_" . $_SESSION["auth_user"] . "_" . $formation . "_$date_fr.pdf");

		// TODO : centraliser ces fonctions de cr�ation automatique de chemins
		if(!is_dir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]"))
			mkdir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]", 0770);

		// $doc_liste->Output("$__COMP_FILES_DIR/$_SESSION[comp_id]/$nom_fichier");
		$doc_liste->Output("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$nom_fichier");

		// Attention : chemin relatif � www-root (document_root du serveur Apache)
		echo "<HTML><SCRIPT>document.location='$__GESTION_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$nom_fichier';</SCRIPT></HTML>";
	}
}

db_close($dbr);

?>
