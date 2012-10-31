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
				<font class='Texte_16' style='weight:bold'>
					<p><strong>A propos de la Composante...</p>
					<p>$_SESSION[composante]</o>
				</font>
			</div>");

	$result=db_query($dbr, "SELECT $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_univ_id, $_DBC_universites_nom,
											$_DBC_composantes_scolarite, $_DBC_composantes_www
										FROM $_DB_composantes, $_DB_universites
									WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
									AND $_DBC_composantes_id='$_SESSION[comp_id]'");

	$rows=db_num_rows($result);

	if($rows)
	{
		list($comp_id, $comp_nom, $comp_univ_id, $univ_nom, $scolarite, $comp_www)=db_fetch_row($result,0);

		print("<table cellspacing='0' cellpadding='4' border='0' align='center'>\n");

		if(!empty($scolarite))
		{
			$scolarite=nl2br($scolarite);

			print("<tr>
						<td align='justify'>
							<font class='Texte'>
								<strong>Sauf indication contraire dans la liste des justificatifs, vous devez envoyer les pièces demandées PAR COURRIER à l'adresse suivante :</strong>
								<br><br>
								$scolarite
								<br><br>
							</font>
						</td>
					</tr>\n");
		}

		if(!empty($comp_www) && (!strncmp("http://", $comp_www, 7) || !strncmp("https://", $comp_www, 8)))
		{
			print("<tr>
						<td align='justify'>
							<font class='Texte'>
								<strong>Adresse du site Internet de la composante :</strong>
								<br><br>
								<a href='$comp_www' target='_blank' class='lien_bleu_12'><strong>$comp_www</strong></a>
								<br><br>
							</font>
						</td>
					</tr>\n");
		}

		print("<tr>
					<td align='justify'>
						<font class='Texte'>
							<strong>Envoyer un message : </strong>
							<br><br>
							<a href='$__CAND_MSG_DIR/compose.php' class='lien2'>- Cliquer ici pour contacter la Scolarité</a>
						</font>
					</td>
				</tr>
				</table>\n");
	}

	db_free_result($result);
?>
