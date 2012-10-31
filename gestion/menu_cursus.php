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
	verif_auth();

	if(!isset($_SESSION["candidat_id"]))
	{
		header("Location:index.php");
		exit;
	}

	print("<div class='centered_box'>
				<font class='Texte_16'><strong>$_SESSION[onglet] - Son cursus scolaire</strong></font>
			</div>\n");

	if(isset($modifs) && $modifs>0)
		message("Succès de la mise à jour - message envoyé au candidat", $__SUCCES);
	elseif(isset($modifs) && $modifs==0)
		message("Aucune modification effectuée, aucun message n'a été envoyé.", $__SUCCES);

	if(isset($precision_vide))
		message("Attention : le champ 'Précisions' de certaines étapes non justifiées n'a pas été renseigné !", $__WARNING);

	if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
		print("<div class='centered_box'>
					<a href='cursus.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter'></a>
					<a href='cursus.php' target='_self' class='lien2'>Ajouter manuellement une étape du cursus</a>
				</div>\n");
?>
	<table style='margin-left:auto; margin-right:auto; padding-bottom:20px;'>
	<tr>
		<td colspan='2' class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><strong>Diplôme / Niveau d'études</strong></font>
		</td>
		<td class='td-milieu fond_menu2'>
			<font class='Texte_menu2'><strong>Justification</strong></font>
		</td>
	<?php
		if($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1)
			print("<td colspan='2' class='td-droite fond_menu2'>
						<font class='Texte_menu2'><strong>Précisions</strong></font>
					 </td>\n");
		else
			print("<td class='td-droite fond_menu2'>
						<font class='Texte_menu2'><strong>Précisions</strong></font>
					 </td>\n");
	?>
	</tr>
	<?php
		$result=db_query($dbr,"(SELECT 	$_DBC_cursus_id, $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_annee,
													$_DBC_cursus_ecole, $_DBC_cursus_ville,
													CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
														THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
														ELSE '' END as cursus_pays,
													$_DBC_cursus_mention, $_DBC_cursus_moyenne
											FROM $_DB_cursus
										WHERE $_DBC_cursus_candidat_id='$candidat_id'
										AND   $_DBC_cursus_annee='0')
									UNION ALL
										(SELECT 	$_DBC_cursus_id, $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_annee,
													$_DBC_cursus_ecole, $_DBC_cursus_ville,
													CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
														THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
														ELSE '' END as cursus_pays,
														$_DBC_cursus_mention, $_DBC_cursus_moyenne
											FROM $_DB_cursus
										WHERE $_DBC_cursus_candidat_id='$candidat_id'
										AND $_DBC_cursus_annee!='0'
											ORDER BY $_DBC_cursus_annee DESC)");
		$rows=db_num_rows($result);

		// compteur de cursus non justifiés (sert à déterminer si une fiche peut être transférée vers la compeda)
		$cursus_non_justifies=0;

		// on a des informations sur le cursus, on initialise le tableau
		$_SESSION["tab_cursus"]=array();

		for($i=0; $i<$rows; $i++)
		{
			list($cu_id, $dip, $int, $annee_obt, $ecole, $ville, $pays, $mention, $moyenne)=db_fetch_row($result,$i);

			$dip=preg_replace("/_/","",$dip);
			$int=preg_replace("/_/","",$int);
			$ecole=preg_replace("/_/","",$ecole);
			$ville=preg_replace("/_/","",$ville);

			if($annee_obt==0)
				$annee_obt="En cours";

			if(!empty($pays))
				$pays="- ". preg_replace("/_/","",$pays);
			else
				$pays="";

			// si le candidat a été ajourné, on le précise (ça évite de demander un justificatif)
			if(!empty($mention) && $mention=="Ajourné")
				$mention="- <strong>Ajourné</strong>";
			else
				$mention="";

			if(!empty($moyenne))
				$moyenne="- <strong>Moyenne : $moyenne</strong>";
			else
				$moyenne="";

			// Satut de l'étape pour la composante concernée
			$result2=db_query($dbr, "SELECT $_DBC_cursus_justif_statut, $_DBC_cursus_justif_precision
												FROM $_DB_cursus_justif
											 WHERE $_DBC_cursus_justif_cursus_id='$cu_id'
											 AND $_DBC_cursus_justif_comp_id='$_SESSION[comp_id]'
											 AND $_DBC_cursus_justif_periode='$__PERIODE'");

			$rows2=db_num_rows($result2);

			if(!$rows2) // insertion dans la base
			{
				db_query($dbr, "INSERT INTO $_DB_cursus_justif VALUES('$cu_id','$_SESSION[comp_id]','$__CURSUS_EN_ATTENTE','', '$__PERIODE')");
				$justifie=$__CURSUS_EN_ATTENTE;
				$precision="";
			}
			else
				list($justifie, $precision)=db_fetch_row($result2, 0);

			db_free_result($result2);

			// Justification des éléments du cursus (cf macros dans vars.php)
			// 0 = En attente des pièces (valeur par défaut)
			// -2 = Pièces à fournir dès l'obtention du diplôme (pour les étapes en cours)
			// -1 = Pièce(s) requise(s) manquante(s)
			// 1 = Etape du cursus validée
			// 2 = Etape ne nécessitant aucune justification

			switch($justifie)
			{
				case	$__CURSUS_NON_JUSTIFIE:			$en_attente_selected=$a_fournir_des_obtention=$non_necessaire_selected=$oui_selected=$pieces_manquantes_selected="";
																$non_justifie='selected=1';
																$statut_cursus="Information non confirmée";
																break;


				case	$__CURSUS_EN_ATTENTE	:			$non_justifie=$a_fournir_des_obtention=$non_necessaire_selected=$oui_selected=$pieces_manquantes_selected="";
																$en_attente_selected='selected=1';
																$cursus_non_justifies++;
																$statut_cursus="En attente des pièces";
																break;

				case	$__CURSUS_VALIDE	:				$oui_selected='selected=1';
																$non_justifie=$a_fournir_des_obtention=$non_necessaire_selected=$en_attente_selected=$pieces_manquantes_selected="";
																$statut_cursus="Information validée";
																break;

				case	$__CURSUS_PIECES	:				$non_justifie=$a_fournir_des_obtention=$non_necessaire_selected=$oui_selected=$en_attente_selected="";
																$pieces_manquantes_selected='selected=1';
																$cursus_non_justifies++;
																$statut_cursus="Pièces manquantes";
																break;

				case	$__CURSUS_NON_NECESSAIRE	:	$non_justifie=$oui_selected=$en_attente_selected=$a_fournir_des_obtention=$pieces_manquantes_selected="";
																$non_necessaire_selected='selected=1';
																$statut_cursus="Aucun justificatif nécessaire";
																break;

				case	$__CURSUS_DES_OBTENTION	:		$non_justifie=$oui_selected=$non_necessaire_selected=$en_attente_selected=$pieces_manquantes_selected="";
																$a_fournir_des_obtention='selected=1';
																$statut_cursus="Justificatif à fournir dès l'obtention";
																break;
			}

			// stockage des infos du cursus dans le tableau
			$_SESSION["tab_cursus"][$cu_id]=array();
			$_SESSION["tab_cursus"][$cu_id]["texte"]="$annee_obt : $dip $int";
			$_SESSION["tab_cursus"][$cu_id]["justifie"]=$justifie;
			$_SESSION["tab_cursus"][$cu_id]["precision"]=$precision;

			// Si la fiche est vérrouillée, on autorise la modification des étapes entrées
			if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
			{
				print("<tr>
							<td class='td-gauche fond_menu'>
								<a href='cursus.php?cu_id=$cu_id' class='lien_menu_gauche'>$annee_obt : </a>
							</td>
							<td class='td-milieu fond_menu'>
								<a href='cursus.php?cu_id=$cu_id' class='lien_menu_gauche'>$dip $int $mention $moyenne
									<br><i>$ecole, $ville $pays</i>
								</a>
							</td>\n");
			}
			else
				print("<tr>
							<td class='td-gauche fond_menu'>
								<font class='Texte_menu'>$annee_obt : </font>
							</td>
							<td class='td-milieu fond_menu'>
								<font class='Texte_menu'>$dip $int $mention $moyenne
									<br><i>$ecole, $ville $pays</i>
								</font>
							</td>\n");

			print("<td class='td-milieu fond_menu'>\n");

			if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
				print("<select name='justification_$cu_id' size='1'>
							<option value='$__CURSUS_EN_ATTENTE' $en_attente_selected>En attente des pièces</option>
							<option value='$__CURSUS_VALIDE' $oui_selected>Information validée</option>
							<option value='$__CURSUS_PIECES' $pieces_manquantes_selected>Pièces manquantes</option>
							<option value='$__CURSUS_DES_OBTENTION' $a_fournir_des_obtention>Justificatif à fournir dès l'obtention</option>
							<option value='$__CURSUS_NON_NECESSAIRE' $non_necessaire_selected>Aucun justificatif nécessaire</option>
							<option value='$__CURSUS_NON_JUSTIFIE' $non_justifie>Information jamais confirmée</option>
						</select>
					</td>
					<td class='td-milieu fond_menu'>
						<input type='text' name='precision_$cu_id' value=\"$precision\" size='30' maxlength='256'>
					</td>
					<td class='td-droite fond_menu' style='text-align:center; width:24px;'>
						<a href='suppr_cursus.php?cu_id=$cu_id' target='_self' class='lien2'><img src='$__ICON_DIR/trashcan_full_22x22_slick_menu.png' alt='Supprimer' width='22' height='22' border='0'></a>
					</td>
				</tr>\n");
			else
				print("	<font class='Texte_menu'>$statut_cursus</font></td>
						<td class='td-droite fond_menu'>
							<font class='Texte_menu'>$precision</font>
						</td>
					</tr>\n");
		}
		db_free_result($result);

		print("</table>\n");

		if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
		{
			message("Toute modification validée entraîne automatiquement l'envoi d'un message au candidat<br><center>(message global pour l'ensemble des étapes du cursus)</center>", $__WARNING);

			print("<div class='centered_box'>
						<input type='image' src='$__ICON_DIR/bouton_valider_128x32_fond.png' alt='Valider les modifications' name='go_cursus' value='Valider les modifications'>
					</div>\n");
		}
?>
