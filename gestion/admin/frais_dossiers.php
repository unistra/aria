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

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";


	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");
	
	if(!in_array($_SESSION['niveau'], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	if((isset($_POST["select"]) || isset($_POST["select_x"])) && array_key_exists("annee_id", $_POST) && ctype_digit($_POST["annee_id"]))
	{
		$id_annee=$_POST["annee_id"];
		$resultat=1;

		$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBU_propspec_frais,
												$_DBC_propspec_finalite, $_DBC_propspec_manuelle
										FROM $_DB_propspec, $_DB_annees, $_DB_specs
										WHERE $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_propspec_annee='$id_annee'
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom_court, $_DBC_propspec_finalite");

		if($rows=db_num_rows($result))
		{
			$_SESSION["cur_frais_array"]=array();	// contient le nom de l'année et un tableau de spécialités

			for($i=0; $i<$rows;$i++)
			{
				list($propspec_id, $annee_nom, $nom_specialite,$frais_dossiers,$finalite,$manuelle)=db_fetch_row($result,$i);

				$_SESSION["nom_annee"]=$annee_nom;

				$nom_finalite=$tab_finalite[$finalite];

				if(!array_key_exists($propspec_id,$_SESSION["cur_frais_array"]))
					$_SESSION["cur_frais_array"]["$propspec_id"]=array(); // contient le nom de la spécialité et les frais de dossiers associés

				if($manuelle)
					$manuelle_txt="(M)";
				else
					$manuelle_txt="";

				$_SESSION["cur_frais_array"]["$propspec_id"]["spec_nom"]="$nom_specialite $nom_finalite $manuelle_txt";
				$_SESSION["cur_frais_array"]["$propspec_id"]["frais_dossiers"]=$frais_dossiers;
			}
		}
		else
		{
			db_free_result($result);
			db_close($dbr);
			header("Location:$__GESTION_DIR/login.php");
			exit;
		}

		db_free_result($result);
	}
	elseif((isset($_POST["valider"]) || isset($_POST["valider_x"])) && isset($_SESSION["cur_frais_array"]) && array_key_exists("annee_id", $_POST) && ctype_digit($_POST["annee_id"]))
	{
		$id_annee=$_POST["annee_id"];
		$maj=0;

		foreach($_SESSION["cur_frais_array"] as $propspec_id => $specialite_array)
		{
			if(isset($_POST["frais_$propspec_id"]))
			{
				$nouveaux_frais=$_POST["frais_$propspec_id"];

				if(empty($nouveaux_frais))
					$nouveaux_frais=0;

				if($nouveaux_frais!=$specialite_array["frais_dossiers"] && (is_numeric($nouveaux_frais) && $nouveaux_frais>=0)) // on met à jour
				{
					// $cur_frais=$specialite_array["frais_dossiers"];
					// $cur_nom_spec=$specialite_array["nom"];

					$maj++;
					db_query($dbr, "UPDATE $_DB_propspec SET $_DBU_propspec_frais='$nouveaux_frais'
														WHERE $_DBU_propspec_id='$propspec_id'");
				}
			}
		}
		if($maj)
			$success=1;

		unset($_SESSION["cur_frais_array"]);
	}
	else
		unset($_SESSION["cur_frais_array"]);
			
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Modifier les frais de dossiers", "xcalc_32x32_fond.png", 15, "L");

		print("<form action='$php_self' method='POST' name='form1'>\n");

		if(!isset($resultat))
		{
			print("<table align='center'>
						<tr>
						<td class='td-gauche fond_menu2'>
							<font class='Texte_menu2'><b>Sélection de l'année : </b></font>
						</td>
						<td class='td-droite fond_menu'>
							<select name='annee_id'>\n");

			$result=db_query($dbr,"SELECT $_DBC_annees_id, $_DBC_annees_annee FROM $_DB_annees
																WHERE $_DBC_annees_id IN (SELECT distinct($_DBC_propspec_annee) FROM $_DB_propspec
																														WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
																ORDER BY $_DBC_annees_ordre");
			$rows=db_num_rows($result);

			for($i=0; $i<$rows; $i++)
			{
				list($annee_id,$annee_nom)=db_fetch_row($result,$i);

				if($annee_nom=="")
					$annee_nom="Années particulières (concours, ...)";

				print("<option value='$annee_id'>$annee_nom</option>\n");
			}

			print("</select>
					</td>
				</tr>
				</table>

				<div class='centered_icons_box'>
					<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
					<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Sélectionner' name='select' value='Sélectionner'>
					</form>
				</div>\n");

			if(isset($success))
				message("$maj Information(s) mise(s) à jour avec succès.</font>", $__SUCCES);

			db_free_result($result);
		}
		elseif(isset($resultat) && $resultat==1)
		{
			message("Les sommes sont à indiquer en euros. Une valeur vide correspond à des frais nuls.", $__INFO);

			$nom_annee=$_SESSION["nom_annee"];

			if(empty($nom_annee))
				$nom_annee="Années particulières (Concours, ...)";
			else
				$nom_annee="Année : $nom_annee";

			print("<table align='center'>
					 <tr>
						<td class='fond_menu2' colspan='2' nowrap='true' style='padding:4px 20px 4px 20px;'>
							<font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$nom_annee</b></font>
						</td>
					 </tr>\n");

			foreach($_SESSION["cur_frais_array"] as $propspec_id => $specialite_array)
			{
				$nom_specialite=$specialite_array["spec_nom"];

				if(isset($new_frais[$propspec_id]))
					$frais_specialite=$new_frais[$propspec_id];
				else
					$frais_specialite=$specialite_array["frais_dossiers"];

				print("<tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><b>$nom_specialite</b></font>
							</td>
							<td class='td-droite fond_menu'>
								<input type='text' name='frais_$propspec_id' value='$frais_specialite' maxlength='8' size='9'>
							</td>
						</tr>\n");
			}

			print("</table>

					<div class='centered_icons_box'>
						<input type='hidden' name='annee_id' value='$id_annee'>
						<a href='$php_self' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
						<input type='image' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider' value='Valider'>
						</form>
					</div>\n");
		}

		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
