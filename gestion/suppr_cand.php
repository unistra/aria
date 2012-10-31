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

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();
	
	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	// identifiant de l'étudiant
	$candidat_id=$_SESSION["candidat_id"];
	
	if(isset($_GET["cand_id"]) && ctype_digit($_GET["cand_id"]))
		$_SESSION["cand_id"]=$cand_id=$_GET["cand_id"];
	elseif(isset($_SESSION["cand_id"]))
		$cand_id=$_SESSION["cand_id"];
	else
	{
		header("Location:edit_candidature.php");
		exit;
	}

	// Condition : la formation doit être verrouillée
	if(!isset($_SESSION["tab_candidat"]["array_lock"][$cand_id]) || $_SESSION["tab_candidat"]["array_lock"][$cand_id]["lock"]!=1)
	{
		header("Location:edit_candidature.php");
		exit;
	}

	// parametre de groupe pour les candidatures à choix multiples
	if(isset($_GET["groupe"]) && $_GET["groupe"]!=-1)
		$_SESSION["groupe"]=$_GET["groupe"];

	if(isset($_GET["ordre_spec"]) && $_GET["ordre_spec"]!=-1)
		$_SESSION["ordre_spec"]=$_GET["ordre_spec"];
	
	// validation du formulaire
	if(isset($_POST["act"]) && $_POST["act"]==1)
	{
		if(isset($_POST["go"]) || isset($_POST["go_x"]))
		{
			$o=$_POST["o"];

			$dbr=db_connect();

			if(isset($_SESSION["groupe"]))
			{
				$groupe=$_SESSION["groupe"];
				$ordre_spec=$_SESSION["ordre_spec"];
				$cond_groupe="AND $_DBC_cand_groupe_spec='$groupe' AND $_DBC_cand_ordre_spec>'$ordre_spec'";
			}
			else
				$cond_groupe="AND $_DBC_cand_ordre>'$o'";

			// on décale éventuellement l'ordre des candidatures pour boucher le trou
			// TODO : pitoyable : réécrire les requêtes
			$result=db_query($dbr,"SELECT $_DBC_cand_id, $_DBC_cand_ordre, $_DBC_cand_ordre_spec FROM $_DB_cand, $_DB_propspec
											WHERE $_DBC_cand_candidat_id='$candidat_id'
											AND $_DBC_cand_propspec_id=$_DBC_propspec_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_cand_periode='$__PERIODE'
											$cond_groupe
										ORDER BY $_DBC_cand_ordre, $_DBC_cand_ordre_spec ASC");
			$rows=db_num_rows($result);

			for($i=0; $i<$rows; $i++)
			{
				list($cand_dec,$current_ordre,$current_ordre_spec)=db_fetch_row($result,$i);

				if(isset($groupe)) // suppression d'une spécialité au sein d'une candidature à choix multiples, on ne décale que l'ordre des spécialités
				{
					$new_ordre_spec=$current_ordre_spec-1;
					db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_ordre_spec='$new_ordre_spec' WHERE $_DBU_cand_id='$cand_dec'");
				}
				else
				{
					$new_ordre=$current_ordre-1;
					db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_ordre='$new_ordre' WHERE $_DBU_cand_id='$cand_dec'");
				}
			}

			db_free_result($result);
		
			// suppression de l'inscription et de la candidature extérieure correspondante
			db_query($dbr,"DELETE FROM $_DB_cand WHERE $_DBC_cand_id='$cand_id'");

			write_evt($dbr, $__EVT_ID_G_PREC, "Suppression candidature $cand_id", $candidat_id, $cand_id);

			// au cas où on aurait supprimé le dernier élément d'un groupe de spécialités, il faut décaler les candidatures suivantes

			if(isset($groupe))
			{
				$result=db_query($dbr,"SELECT count(*) FROM $_DB_cand
												WHERE $_DBC_cand_candidat_id='$candidat_id'
												AND $_DBC_cand_groupe_spec='$groupe'
												AND $_DBC_cand_periode='$__PERIODE'");
				list($count)=db_fetch_row($result,0);

				if(empty($count) || $count==0) // plus d'élément dans le groupe : il faut décaler
				{
					$result2=db_query($dbr,"SELECT $_DBC_cand_id, $_DBC_cand_ordre FROM $_DB_cand, $_DB_propspec
														WHERE $_DBC_cand_candidat_id='$candidat_id'
														AND $_DBC_cand_propspec_id=$_DBC_propspec_id
														AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
														AND $_DBC_cand_periode='$__PERIODE'
														AND $_DBC_cand_ordre>'$o'
													ORDER BY $_DBC_cand_ordre_spec ASC");
					$rows2=db_num_rows($result2);

					for($i=0; $i<$rows2; $i++)
					{
						list($cand_dec,$current_ordre)=db_fetch_row($result2,$i);

						$new_ordre=$current_ordre-1;
						db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_ordre='$new_ordre' WHERE $_DBU_cand_id='$cand_dec'");
					}

					db_free_result($result2);
				}
				db_free_result($result);
			}

			unset($_SESSION["tab_candidat"]["array_lock"][$cand_id]);

			db_close($dbr);
	
			unset($_SESSION["cand_id"]);

			header("Location:edit_candidature.php");
			exit;
		}
	}
	else // vérification de l'id passée en paramètre
	{
		$dbr=db_connect()	;
		
		$result=db_query($dbr,"SELECT $_DBC_cand_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_cand_ordre, $_DBC_propspec_finalite
											FROM $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec
										WHERE $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_cand_propspec_id=$_DBC_propspec_id
										AND $_DBC_cand_id='$cand_id'");
		$rows=db_num_rows($result);
		
		if($rows)				
		{
			list($propspec_id, $nom_annee, $nom_specialite, $ordre, $finalite)=db_fetch_row($result,0);
			db_free_result($result);
		}
		else
		{
			db_free_result($result);
			db_close($dbr);
			unset($_SESSION["cand_id"]);

			header("Location:edit_candidature.php");
			exit;
		}
		
		db_close($dbr);
	}
		
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>
<div class='main'>
	<?php
		titre_page_icone("Supprimer une candidature", "trashcan_full_32x32_slick_fond.png", 30, "L");

		print("<form action='$php_self' method='POST' name='form1'>\n
					<input type='hidden' name='o' value='$ordre'>\n
					<input type='hidden' name='act' value='1'>

					<div class='centered_box'>
						<font class='Texte3'>
							Candidature : $nom_annee $nom_specialite $tab_finalite[$finalite]\n
						</font>
					</div>\n");

		message("Souhaitez-vous réellement supprimer cette candidature ?", $__QUESTION);
	?>

	<div class='centered_icons_box'>
		<a href='edit_candidature.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Confirmer" name="go" value="Confirmer">
		</form>
	</div>

</div>
<?php
	pied_de_page();
?>
</body></html>
