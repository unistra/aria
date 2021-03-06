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

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	if(isset($_POST["select_periode"]) || isset($_POST["select_periode_x"]))
		$_SESSION["suppr_session_periode"]=$_POST["periode"];

	if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
	{
		$session_num=$_POST["session"];
		$resultat=1;
	}
	elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$session_num=$_POST["session_num"];

		foreach($_SESSION["all_sessions"] as $propspec_id => $propspec_sessions)
		{
			foreach($propspec_sessions as $current_session_num => $current_session_infos)
			{
				if($current_session_num==$session_num)
				{
					db_query($dbr, "DELETE FROM $_DB_session
										WHERE $_DBC_session_id='$current_session_infos[s_id]'
										AND $_DBC_session_propspec_id='$propspec_id'
										AND $_DBC_session_periode='$_SESSION[suppr_session_periode]'");

					write_evt($dbr, $__EVT_ID_G_SESSION, "Suppression session $current_session_infos[s_id] ($propspec_id), p�riode $_SESSION[suppr_session_periode]");
				}
			}
		}

		db_close($dbr);

		header("Location:index.php?");
		exit();
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<div class='menu_haut_2'>
		<a href='index.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/kdeprint_report_16x16_menu2.png"; ?>' alt='+'></a>
		<a href='index.php' target='_self' class='lien_menu_haut_2'>Liste des sessions</a>
	</div>
	<?php
		print("<form action='$php_self' method='POST' name='form1'>\n");

		if(!isset($_SESSION["suppr_session_periode"]))
		{
			titre_page_icone("Supprimer une session de candidatures : s�lection de l'ann�e", "trashcan_full_32x32_slick_fond.png", 15, "L");

			message("<center>
							S�lectionnez l'ann�e universitaire pour laquelle la session sera valide.
       					<br>Attention : les sessions ne doivent pas se recouvrir, m�me si les ann�es universitaires sont distinctes.
						</center>", $__WARNING);
	?>
		<table align='center'>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Ann�e universitaire concern�e par la session � supprimer : </b></font>
			</td>
			<td class='td-droite fond_menu'>
				<select name='periode'>
					<?php
						$result=db_query($dbr, "SELECT distinct($_DBC_cand_periode) FROM $_DB_cand
										ORDER BY $_DBC_cand_periode DESC");

						$rows=db_num_rows($result);

						for($i=0; $i<$rows; $i++)
						{
							list($liste_periode)=db_fetch_row($result,$i);
							
							if(isset($_SESSION["suppr_session_periode"]))
								$selected=($_SESSION["suppr_session_periode"]==$liste_periode) ? "selected" : "";
							else
								$selected=($liste_periode==$__PERIODE) ? "selected" : "";
							
							print("<option value='$liste_periode' $selected>$liste_periode-".($liste_periode+1)."</option>\n");
						}

					   print("<option value='".($__PERIODE+1)."' $selected>Ann�e suivante (".($__PERIODE+1). "-" . ($__PERIODE+2) . ")</option>\n");
					?>
				</select>
			</td>
		</tr>
		</table>

		<div class='centered_icons_box'>
			<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
			<input type="image" src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Suivant" name="select_periode" value="Suivant">
			</form>
		</div>
	<?php
		}
		// Choix de la session � supprimer
		elseif(!isset($resultat))
		{
			titre_page_icone("Supprimer une session de candidatures pour l'ann�e $_SESSION[suppr_session_periode]-".($_SESSION["suppr_session_periode"]+1), "trashcan_full_32x32_slick_fond.png", 15, "L");

			// Nombre de sessions existantes (!= identifiants de sessions)
			$res1=db_query($dbr, "SELECT count($_DBC_session_propspec_id) FROM $_DB_session
											WHERE $_DBC_session_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																							WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
											AND $_DBC_session_periode='$_SESSION[suppr_session_periode]'
											GROUP BY $_DBC_session_propspec_id
											ORDER BY count DESC");

			$nb_rows=db_num_rows($res1);

			if($nb_rows)
				list($nb_sessions)=db_fetch_row($res1, 0);
			else
				$nb_sessions=0;

			if($nb_sessions=="")
				$nb_sessions=0;

			db_free_result($res1);

			if($nb_sessions)
			{
				// Couples Formation/identifiant de session utilis�s (donc non supprimables)
				$res_used_sessions=db_query($dbr, "SELECT $_DBC_cand_propspec_id, $_DBC_cand_session_id
																	FROM $_DB_cand,$_DB_propspec
																WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
																AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
																AND $_DBC_cand_periode='$_SESSION[suppr_session_periode]'
																AND $_DBC_cand_session_id IS NOT NULL
																	GROUP BY $_DBC_cand_propspec_id, $_DBC_cand_session_id
																	ORDER BY $_DBC_cand_propspec_id, $_DBC_cand_session_id");

				$nb_used_sessions=db_num_rows($res_used_sessions);

				$used_sessions=array();

				for($i=0; $i<$nb_used_sessions; $i++)
				{
					list($propspec_id, $session_id)=db_fetch_row($res_used_sessions, $i);

					if(!array_key_exists($propspec_id, $used_sessions))
						$used_sessions[$propspec_id]=array("$session_id" => 1);
					else
						$used_sessions[$propspec_id][$session_id]=1;
				}

				db_free_result($res_used_sessions);

				// On a maintenant tous les couples formation/session_id utilis�s

				// $sessions_non_supprimables="";
				$sessions_non_supprimables=array();

				// on regarde la liste des sessions existantes et on d�termine celles que l'on peut supprimer
				foreach($_SESSION["all_sessions"] as $propspec_id => $array_sessions)
				{
					foreach($array_sessions as $s_num => $infos_session)
					{
						// attention : on ne se base pas sur le num�ro d'ordre de la session, mais sur son identifiant
						$s_id=$infos_session["s_id"];

						// le couple formation/session est pas dans la liste des �l�ments utilis�s : on ne peut pas supprimer la session
						if(array_key_exists($propspec_id, $used_sessions) && array_key_exists($s_id, $used_sessions[$propspec_id]))
							$sessions_non_supprimables[$s_num]=1;
					}
				}

				$count=count($sessions_non_supprimables);

				if($count<$nb_sessions) // on a des sessions supprimables
				{
					print("<table style='margin-left:auto; margin-right:auto'>
							<tr>
								<td class='td-gauche fond_menu2'>
									<font class='Texte_menu2'><b>Choix de la session � supprimer : </b></font>
								</td>
								<td class='td-droite fond_menu'>
									<select size='1' name='session'>
										<option value=''></option>\n");

					for($i=1; $i<=$nb_sessions; $i++)
					{
						// list($session_id)=db_fetch_row($result, $i);
						if(!isset($sessions_non_supprimables[$i]))
							print("<option value='$i'>Session n�$i</option>\n");
					}

					print("</select>
							</td>
						</tr>
						</table>

						<script language='javascript'>
							document.form1.session.focus()
						</script>

						<div class='centered_icons_box'>
							<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>
							<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant' value='Suivant'>
							</form>
						</div>\n");
				}
				else
				{
					message("<center>Suppression impossible : toutes les sessions sont actuellement utilis�es
								<br>(des candidatures ont d�j� �t� ajout�es)</center>", $__ERREUR);

					print("<div class='centered_box' style='padding-top:20px;'>
								<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Annuler' border='0'></a>
							 </div>\n");

				}

				// db_free_result($result);
			}
			else
			{
				message("Il n'existe aucune session de candidatures � supprimer", $__INFO);

				print("<div class='centered_box' style='padding-top:20px;'><center>
							<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>
						</div>\n");
			}
		}
		elseif(isset($resultat) && $resultat==1)
		{
			titre_page_icone("Supprimer une session de candidatures pour l'ann�e $_SESSION[suppr_session_periode]-".($_SESSION["suppr_session_periode"]+1), "trashcan_full_32x32_slick_fond.png", 15, "L");

			message("Souhaitez vous r�ellement supprimer la session n� $session_num ?", $__QUESTION);

			print("<input type='hidden' name='session_num' value='$session_num'>\n");
	?>

	<div class='centered_icons_box'>
		<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
		</form>
	</div>

	<script language="javascript">
		document.form1.session.focus()
	</script>

	<?php
		}
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
