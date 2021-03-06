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

	include "../../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	unset($_SESSION["lettre_id"]);
	unset($_SESSION["cbo"]);

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<div class='menu_haut_2'>
		<a href='index.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/abiword_16x16_menu2.png"; ?>'></a>
		<a href='index.php' target='_self' class='lien_menu_haut_2'>Liste des lettres</a>
		<?php
			if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
			{
		?>
			<a href='parametres.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/preferences_16x16_menu2.png"; ?>' alt='parametres'></a>
			<a href='parametres.php' target='_self' class='lien_menu_haut_2'>Param�tres par d�faut</a>
		<?php
			}
		?>
			<a href='editeur.php?lettre_id=-1'  target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' alt='+'></a>
			<a href='editeur.php?lettre_id=-1'  target='_self' class='lien_menu_haut_2'>Cr�er une nouvelle lettre</a>
		<?php
			if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
			{
		?>
			<a href='copie_lettre.php'  target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/editcopy_16x16_menu2.png"; ?>' alt='+'></a>
			<a href='copie_lettre.php'  target='_self' class='lien_menu_haut_2'>Dupliquer une lettre</a>
		<?php
			}
		?>
	</div>

	<?php
		titre_page_icone("Tableau r�capitulatif : lettres, formations et d�cisions", "kdeprint_report_22x22_fond.png", 15, "L");

		if(isset($_GET["succes"]) && $_GET["succes"]==1)
			message("Informations mises � jour avec succ�s", $__SUCCES);
	?>
<!--
"-5" => "LC-AE",
"-4" => "E",
"-3" => "LC", 
"-2" => "EA", 
"-1" => "SR", 
"0" => "NT", 
"1" => "A", 
"2" => "R", 
"3" => "DT", 
"4" => "R-AE", 
"5" => "A-AE",
"6" => "A-LC", 
"7" => "A-REC", 
"8" => "R-REC",
"9" => "D"
-->
	<table align='center' border='0'>
	<tr>
		<td style='white-space:nowrap; padding-left:5px;'>		
			<font class='Texte'><b>LC-AE</b> : Liste Compl�mentaire Apr�s Entretien</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>E</b> : Convocable � l'Entretien</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>LC</b> : Entretien T�l�phonique</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>LC</b> : Liste Compl�mentaire</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>EA</b> : En Attente</font>
		</td>
	</tr>
	<tr>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>SR</b> : Admis Sous R�serve</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>A</b> : Admis</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>R</b> : Refus</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>DT</b> : Dossier Transmis</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>R-AE</b> : Refus Apr�s Entretien</font>
		</td>
	</tr>
	<tr>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>A-AE</b> : Admis Apr�s Entretien</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>A-LC</b> : Admis depuis la Liste Compl�mentaire</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>A-REC</b> : Admis sur Recours</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>R-REC</b> : Refus apr�s Recours</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>D</b> : D�sistement</font>
		</td>
	</tr>
	<tr>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>A-AC</b> : Admis, attente de confirmation</font>
		</td>
		<td style='white-space:nowrap; padding-left:5px;'>
			<font class='Texte'><b>AD-C</b> : Admission confirm�e</font>
		</td>
		<td colspan='3'></td>
	</tr>
	</table>
	<br clear='all'>

	<?php
		$dbr=db_connect();

		$result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_propspec_id_spec,
													$_DBC_specs_nom_court, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_mentions_nom,
													$_DBC_propspec_manuelle
											FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
										WHERE $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_specs_mention_id=$_DBC_mentions_id
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										AND $_DBC_propspec_active='1'
											ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_propspec_active DESC, $_DBC_specs_nom_court");

		$rows=db_num_rows($result);

		if($rows)
		{
			$res_decision=db_query($dbr, "SELECT $_DBC_decisions_id, $_DBC_decisions_texte
														FROM $_DB_decisions, $_DB_decisions_comp
													WHERE $_DBC_decisions_id=$_DBC_decisions_comp_dec_id
													AND $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]'
													AND $_DBC_decisions_id!='0'
														ORDER BY $_DBC_decisions_id");

			$nb_decisions=db_num_rows($res_decision);

			$array_decisions=array();

			for($d=0; $d<$nb_decisions; $d++)
			{
				list($dec_id, $dec_txt)=db_fetch_row($res_decision, $d);

				$array_decisions[$dec_id]=$dec_txt;
			}

			db_free_result($res_decision);

			// Construction du tableau formations => lettres / d�cisions pour limiter les requ�tes � la base
			$res_lettres_decs=db_query($dbr,"SELECT $_DBC_lettres_propspec_propspec_id, $_DBC_lettres_dec_dec_id,
																	$_DBC_lettres_titre
															FROM $_DB_lettres, $_DB_lettres_dec, $_DB_lettres_propspec
														WHERE $_DBC_lettres_id=$_DBC_lettres_dec_lettre_id
														AND $_DBC_lettres_id=$_DBC_lettres_propspec_lettre_id
														AND $_DBC_lettres_comp_id='$_SESSION[comp_id]'
															ORDER BY $_DBC_lettres_propspec_propspec_id, $_DBC_lettres_dec_dec_id");

			$rows_lettres_decs=db_num_rows($res_lettres_decs);

			$array_lettres_decs=array();

			for($rld=0; $rld<$rows_lettres_decs; $rld++)
			{
				list($formation_id, $dec_id, $lettre_titre)=db_fetch_row($res_lettres_decs, $rld);

				if(!array_key_exists($formation_id, $array_lettres_decs))
					$array_lettres_decs[$formation_id]=array($dec_id => array());
				elseif(!array_key_exists($dec_id, $array_lettres_decs[$formation_id]))
					$array_lettres_decs[$formation_id][$dec_id]=array();

				$index=count($array_lettres_decs[$formation_id][$dec_id]);

				$array_lettres_decs[$formation_id][$dec_id][$index]=$lettre_titre;
			}

			db_free_result($res_lettres_decs);

			print("<table align='center' style='padding-bottom:20px;'>\n");

			$old_annee="-1";
			$old_mention="-1";

			for($i=0; $i<$rows; $i++)
			{
				list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom, $form_mention, $form_finalite,
						$form_mention_nom, $form_manuelle)=db_fetch_row($result, $i);

				if($form_annee_id!=$old_annee)
				{
					if($i)
						print("<tr>
									<td class='fond_page' height='15' colspan='" . ($nb_decisions+1) . "' nowrap='true'></td>
								</tr>\n");

					$annee_nom=$form_annee_nom=="" ? "Ann�es particuli�res" : $form_annee_nom;

					print("<tr>
								<td class='fond_menu2' nowrap='true'>
									<font class='Texte_menu2'><b>$annee_nom</b></font>
								</td>\n");

					foreach($array_decisions as $dec_id => $dec_txt)
					{
						print("<td class='fond_menu2' align='center' style='padding-left:10px; padding-right:10px;'>
									<font class='Texte_menu2'>$__DOSSIER_DECISIONS_COURTES[$dec_id]</font>
								</td>\n");
					}

					print("</tr>\n");

					$new_sep_annee=1;

					$old_annee=$form_annee_id;
					$old_mention="-1";
				}
				else
					$new_sep_annee=0;

				if($form_mention!=$old_mention)
				{
					$val=htmlspecialchars($form_mention_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);

					print("<tr>
								<td class='fond_menu'  nowrap='true'>
									<font class='Texte_menu'><b>$val</b></font>
								</td>
								<td class='fond_menu' colspan='$nb_decisions'></td>
							</tr>\n");

					$old_mention=$form_mention;
				}

				$manuelle_txt=$form_manuelle ? "(M) " : "";
				$nom_formation=$form_annee_nom=="" ? "$manuelle_txt$form_spec_nom $tab_finalite[$form_finalite]"
																: "$manuelle_txt$form_annee_nom - $form_spec_nom $tab_finalite[$form_finalite]";


				print("<tr>
							<td class='fond_page' nowrap='true'>
								<font class='Texte'>$nom_formation</font>
							</td>\n");

				foreach($array_decisions as $dec_id => $dec_txt)
				{
					if(array_key_exists($form_propspec_id, $array_lettres_decs)
					&& array_key_exists($dec_id, $array_lettres_decs[$form_propspec_id]))
					{
						$link_title="";

						$nb_lettres=count($array_lettres_decs[$form_propspec_id][$dec_id]);

						foreach($array_lettres_decs[$form_propspec_id][$dec_id] as $lettre_titre)
							$link_title.="&#8226; " . htmlspecialchars(stripslashes($lettre_titre), ENT_QUOTES, $default_htmlspecialchars_encoding) . " ";

						print("<td class='fond_page' align='center' valign='middle'>
									<font class='Texte'>
										<a href='#' title='$link_title' class='nolink_12'>$nb_lettres</a>
									</font>
								</td>\n");
					}
					else
						print("<td class='fond_page' align='center'>
									<img src='$__ICON_DIR/cancel_16x16_fond.png' border='0' desc='0'>
								</td>\n");
				}

				print("</tr>\n");
			}

			print("</table>\n");
		}
		else
			message("Aucune mention / sp�cialit� n'a encore �t� d�finie pour cet �tablissement.", $__INFO);

		db_free_result($result);
		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>

</body></html>
