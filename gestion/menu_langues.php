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
	// V�rifications compl�mentaires au cas o� ce fichier serait appel� directement
	verif_auth();

	if(!isset($_SESSION["candidat_id"]))
	{
		header("Location:index.php");
		exit;
	}

	print("<div class='centered_box'>
				<font class='Texte_16'><strong>$_SESSION[onglet] - Langues</strong></font>
			 </div>\n");

	if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
		print("<div class='centered_box'>
					<a href='langues.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter'></a>
					<a href='langues.php' target='_self' class='lien2'>Ajouter manuellement une langue �trang�re</a>
				</div>\n");

	$result=db_query($dbr,"SELECT $_DBC_langues_id, $_DBC_langues_langue, $_DBC_langues_niveau, $_DBC_langues_annees
										FROM $_DB_langues
									WHERE $_DBC_langues_candidat_id='$candidat_id'
										ORDER BY $_DBC_langues_langue ASC");

	$rows=db_num_rows($result);

	if($rows)
	{
		print("<table style='margin-left:auto; margin-right:auto; padding-bottom:20px;'>\n");

		for($i=0; $i<$rows; $i++)
		{
			list($la_id, $langue,$niveau, $nb_annees)=db_fetch_row($result,$i);

			// Dipl�mes obtenus dans cette langue
			$result2=db_query($dbr,"SELECT $_DBC_langues_dip_id, $_DBC_langues_dip_nom, $_DBC_langues_dip_annee, $_DBC_langues_dip_resultat
												FROM $_DB_langues_dip WHERE $_DBC_langues_dip_langue_id='$la_id'
											ORDER BY $_DBC_langues_dip_annee");

			$rows2=db_num_rows($result2);

			$niveau_langue=explode("|",$niveau);
			$niveau_txt="";

			if($niveau_langue[0])
				$niveau_txt="Lu";

			if($niveau_langue[1])
			{
				if(!empty($niveau_txt))
					$niveau_txt.=", ";

				$niveau_txt.="Ecrit";
			}
			if($niveau_langue[2])
			{
				if(!empty($niveau_txt))
					$niveau_txt.=", ";

				$niveau_txt.="Parl�";
			}

			if(isset($niveau_langue[3]) && $niveau_langue[3])
			{
				if(!empty($niveau_txt))
					$niveau_txt.=", ";

				$niveau_txt.="Langue Maternelle";
			}

			if(!empty($nb_annees))
				$nb_annees="Nombre d'ann�es : $nb_annees";

			if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))	// Si la fiche est v�rrouill�e, on autorise la modification et la suppression
			{
				print("<tr>
							<td class='td-gauche fond_menu2' style='width:24px;'>
								<a href='langues.php?suppr=$la_id' target='_self' class='lien2'><img src='$__ICON_DIR/trashcan_full_22x22_slick_menu2.png' alt='Supprimer' width='22' height='22' border='0'></a>
							</td>
							<td class='td-milieu fond_menu2'>
								<a href='langues.php?la_id=$la_id' class='lien_menu_gauche'><b>$langue</b></a>
							</td>
							<td class='td-milieu fond_menu2'>
								<a href='langues.php?la_id=$la_id' class='lien_menu_gauche'>$niveau_txt</a>
							</td>
							<td class='td-droite fond_menu2'>
								<a href='langues.php?la_id=$la_id' class='lien_menu_gauche'>$nb_annees</a>
							</td>
						</tr>");

				if($rows2)
				{
					$langue_txt="";

					for($j=0; $j<$rows2; $j++)
					{
						list($langue_dip_id, $langue_dip, $langue_dip_annee, $langue_dip_resultat)=db_fetch_row($result2, $j);

						if(!empty($langue_txt))
							$langue_txt.="<br>";

						if(!empty($langue_dip_annee) && $langue_dip_annee!=0)
							$langue_txt="$langue_dip_annee : $langue_dip";
						else
							$langue_txt="$langue_dip";

						if(!empty($langue_dip_resultat))
							$langue_txt.="&nbsp;&nbsp;&nbsp;<b>R�sultat / Mention :</b> $langue_dip_resultat";

						$crypt_params=crypt_params("la_id=$la_id&suppr=$langue_dip_id");

						print("<tr>
									<td colspan='3' class='td-gauche fond_menu'>
										<font class='Texte_menu'>$langue_txt</font>
									</td>
									<td class='td-droite fond_menu' style='text-align:right;'>
										<a href='langues_diplomes.php?p=$crypt_params' class='lien_menu_gauche'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' border='0'></a>
									</td>
								</tr>\n");
					}
				}

				$crypt_params=crypt_params("la_id=$la_id");

				print("<tr>
							<td colspan='4' align='center' class='fond_menu'>
								<a href='langues_diplomes.php?p=$crypt_params' class='lien_menu_gauche'>Cliquer ici pour ajouter un dipl�me dans cette langue</a>
							</td>
						</tr>
						<tr>
							<td colspan='4' height='20' class='fond_page'></td>
						</tr>\n");
			}
			else
			{
				print("<tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><b>$langue</b></font>
							</td>
							<td class='td-milieu fond_menu2'>
								<font class='Texte_menu2'>$niveau_txt</font>
							</td>
							<td class='td-droite fond_menu2'>
								<font class='Texte_menu2'>$nb_annees</font>
							</td>
						</tr>\n");

				if($rows2)
				{
					$langue_txt="";

					for($j=0; $j<$rows2; $j++)
					{
						list($langue_dip_id, $langue_dip, $langue_dip_annee, $langue_dip_resultat)=db_fetch_row($result2, $j);

						if(!empty($langue_txt))
							$langue_txt.="<br>";

						if(!empty($langue_dip_annee) && $langue_dip_annee!=0)
							$langue_txt.="$langue_dip_annee : $langue_dip";
						else
							$langue_txt.="$langue_dip";

						if(!empty($langue_dip_resultat))
							$langue_txt.="&nbsp;&nbsp;&nbsp;<b>R�sultat / Mention :</b> $langue_dip_resultat";
					}

					print("<tr>
								<td colspan='3' class='td-complet fond_menu'>
									<font class='Texte_menu'>$langue_txt</font>
								</td>
							</tr>\n");
				}

				print("<tr>
							<td colspan='3' height='20' class='fond_page'></td>
						</tr>\n");
			}

			db_free_result($result2);
		}

		print("</table>\n");
	}

	db_free_result($result);
?>
