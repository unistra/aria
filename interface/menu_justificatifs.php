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
	else
		$candidat_id=$_SESSION['authentifie'];

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:composantes.php");
		exit();
	}

	print("<div class='centered_box'>
				<font class='Texte_16'><strong>Récapitulatifs et Justificatifs</strong></font>
			</div>");

	$result=db_query($dbr,"SELECT $_DBC_cand_id, $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite,
											$_DBC_cand_statut
											FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_cand
										WHERE $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_cand_propspec_id=$_DBC_propspec_id
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										AND $_DBC_cand_periode='$__PERIODE'
										AND $_DBC_cand_candidat_id='$candidat_id'
										AND $_DBC_cand_lock='1'");

	$rows=db_num_rows($result);
	
	if($rows)
	{
		message("Les documents <strong>PDF</strong> suivants sont générés lorsque vous cliquez sur les liens proposés. Cette opération peut prendre quelques secondes.
					<br>Le programme <a href='http://www.adobe.com/fr/' class='lien_bleu' target='_blank' style='vertical-align:top;'><strong>Adobe Acrobat Reader</strong></a> 
					peut être utilisé pour ouvrir les fichiers PDF", $__INFO);

		print("<table cellpadding='4' cellspacing='0' align='center' border='0'>
				 <tr>
					<td align='left' nowrap='true' width='40' valign='top' style='padding-bottom:20px;'>
						<a href='gen_recapitulatif.php' class='lien_bleu_10' target='_blank'><img src='$__ICON_DIR/pdf_32x32_fond.png' alt='PDF' desc='PDF' border='0'></a>
					</td>
					<td align='left' nowrap='true' valign='middle' style='padding-bottom:20px;'>
						<a href='gen_recapitulatif.php' class='lien_bleu_10' target='_blank'>Récapitulatif des informations que vous avez saisies</a>
					</td>
				</tr>
				<tr>
					<td align='left' nowrap='true' width='40' valign='top' style='padding-bottom:20px;'>
						<img src='$__ICON_DIR/pdf_32x32_fond.png' alt='PDF' desc='PDF' border='0'>
					</td>
					<td align='left' nowrap='true' valign='middle' style='padding-bottom:20px;'>
						<font class='Texte'>
							<strong>Justificatifs à nous fournir pour vos voeux dans l'établissement \"$_SESSION[composante]\"</strong>\n");

		for($i=0; $i<$rows; $i++)
		{
			list($cand_id, $propspec_id, $annee, $spec, $finalite, $statut)=db_fetch_row($result, $i);

			if($annee=="")
				print("<br>- <a href='gen_justificatifs.php?cand_id=$cand_id' class='lien_bleu_10' target='_blank'>$spec $tab_finalite[$finalite]</a>\n");
			else
				print("<br>- <a href='gen_justificatifs.php?cand_id=$cand_id' class='lien_bleu_10' target='_blank'>$annee - $spec $tab_finalite[$finalite]</a>\n");
		}
		
		print("</font>
			   </td>
		    </tr>
		    </table>
		    <br>\n");
		    
		message("<center>
                  Attention : cette fonctionnalité peut poser problème avec certaines version du navigateur Internet Explorer.
                  <br>Le navigateur <a href='http://www.mozilla-europe.org/fr/products/firefox/' target='_blank' class='lien_bleu' style='vertical-align:top;'>Mozilla Firefox</a> (gratuit) est en revanche totalement compatible.
				   </center>", $__WARNING);
	}
	else
		message("Ces documents ne sont pas disponibles car :
		         <br>- soit vous n'avez sélectionné aucune formation dans cette composante (menu <strong>5 - Précandidatures</strong>),
		         <br>- soit aucun de vos voeux n'est encore verrouillé (date également visible dans le menu 5).", $__ERREUR);

	db_free_result($result);
?>
