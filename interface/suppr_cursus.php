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

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}
	else
		$candidat_id=$_SESSION["authentifie"];

/*
	if(!isset($_SESSION["lock"]) || $_SESSION["lock"]==1)
	{
		session_write_close();
		header("Location:precandidatures.php");
		exit();
	}
*/
	$dbr=db_connect();

	if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$candidat_id'
											AND $_DBC_cand_periode='$__PERIODE'
											AND $_DBC_cand_lock='1'")))
	{
		db_close($dbr);
		
		session_write_close();
		header("Location:precandidatures.php");
		exit();
	}

	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
	{
		if(isset($params["cid"]) && ctype_digit($params["cid"]))
			$_SESSION["cid"]=$cid=$params["cid"];
		else	// Paramètre manquant : retour forcé
		{
			db_close($dbr);
			
			session_write_close();
			header("Location:precandidatures.php");
			exit;
		}	
	}
	elseif(isset($_SESSION["cid"]))
		$cid=$_SESSION["cid"];
	else	// Paramètre manquant : retour forcé
	{
		db_close($dbr);
		
		session_write_close();
		header("Location:precandidatures.php");
		exit;
	}

	if(isset($_POST["go"]) || isset($_POST["go_x"]))
	{
		db_query($dbr,"DELETE FROM $_DB_cursus WHERE $_DBC_cursus_candidat_id='$candidat_id' AND $_DBC_cursus_id='$cid'");
		db_close($dbr);

		session_write_close();
		header("Location:precandidatures.php");
		exit;
	}
	else // récupération des données actuelles
	{
		$result=db_query($dbr,"SELECT $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_annee, 
										CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
												THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
												ELSE '' END as cursus_pays
											FROM $_DB_cursus
										WHERE $_DBC_cursus_candidat_id='$candidat_id'
										AND $_DBC_cursus_id='$cid'");
		$rows=db_num_rows($result);
		
		if($rows)				
		{
			list($diplome,$intitule,$annee_obtention,$pays)=db_fetch_row($result,0);
			db_free_result($result);		
		}
		else
		{
			db_free_result($result);
			db_close($dbr);
			
			session_write_close();
			header("Location:../index.php");
			exit;
		}
		
		db_close($dbr);
	}
	
	en_tete_candidat();
	menu_sup_candidat($__MENU_FICHE);
?>

<div class='main'>
	<?php
		titre_page_icone("Supprimer une étape de votre cursus", "trashcan_full_32x32_slick_fond.png", 30, "L");

		if($annee_obtention==0)
			$annee_obtention="En cours";

		print("<form action='$php_self' method='POST' name='form1'>

				<div class='centered_box'>
					<font class='Texte'><b>Diplôme :</b> \"$annee_obtention : $diplome $intitule ($pays)\"</font>
				</div>\n");

		message("Souhaitez-vous réellement supprimer cette étape ?", $__QUESTION);
	?>

	<div class="centered_icons_box">
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Confirmer" name="go" value="Confirmer">
		<a href='precandidatures.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
	</div>

	</form>
</div>
<?php
	pied_de_page_candidat();
?>
</body></html>
