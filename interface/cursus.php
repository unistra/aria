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

	$dbr=db_connect();

	// Condition particulière : si une composante a verrouillé la fiche, le candidat ne peut plus le modifier (sauf en envoyant des pièces par courrier)

	if(isset($_SESSION["lock"]) && $_SESSION["lock"]==1)
	{
		session_write_close();
		header("Location:precandidatures.php");
		exit();
	}

	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
	{
		if(isset($params["cid"]) && ctype_digit($params["cid"]))
			$_SESSION["cid"]=$cid=$params["cid"];
	}
	elseif(isset($_SESSION["cid"]))
		$cid=$_SESSION["cid"];
	else // pas de paramètre : ajout d'une étape au cursus
		$cid=0;

	if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
	{
		$diplome=$_POST["filiere"];
/*
		if(empty($diplome)) // filière venant du champ libre ?
		{
			$diplome=html_entity_decode(ucfirst(trim($_POST["filiere_libre"])));
			if(!empty($diplome))
				$diplome_libre="_" . $diplome;	// ajout du marqueur "champ libre" (pour la validation de la scol lors du transfert vers la compeda)
		}
*/

		// même traitement avec l'intitulé
/*
		$intitule=html_entity_decode($_POST["intitule"]);
		if(empty($intitule)) // pays venant du champ libre ?
		{
*/
			$intitule=html_entity_decode(ucfirst(trim($_POST["intitule_libre"])));
/*
			if(!empty($intitule))
				$intitule_libre="_" . $intitule;

		}
*/
		// presque pareil avec la spécialité, la ville et l'école
		$specialite=html_entity_decode(ucfirst(strtolower(trim($_POST["specialite"]))));
		$ville=html_entity_decode(ucfirst(trim($_POST["ville"])));
		$ecole=html_entity_decode(trim($_POST["ecole"]));

		// format strict
		$annee_obtention=trim($_POST["annee"]);

      if($annee_obtention=="")
         $annee_obtention=date("Y");
		elseif(strlen($annee_obtention)!=4 || !ctype_digit($annee_obtention) || (ctype_digit($annee_obtention) && $annee_obtention>(date("Y")+1)))
			$annee_format=1;

		$pays=$_POST["pays"];

		$mention=html_entity_decode(trim($_POST["mention"]));
		
		$note_moyenne=preg_replace("/,/",".",html_entity_decode(str_replace(" ", "", $_POST["note"])));

		if(empty($diplome) || empty($intitule) || empty($pays) || $pays=="00" || empty($ville) || empty($ecole))
			$champ_vide=1;

		// Note moyenne et mention : traitements particulièrs : pas obligatoires pour l'année en cours ("année" et "année+1")
		if($annee_obtention!=date("Y") && $annee_obtention!=(date("Y")+1) && ($note_moyenne=="" || empty($mention)))
			$champ_vide=1;
			
		// champ facultatifs
		$rang=html_entity_decode(trim($_POST["rang"]));

		if(!isset($champ_vide) && !isset($annee_format))
		{
			// ajout ?
			
			if($cid==0)
			{
				$cursus_id=db_locked_query($dbr, $_DB_cursus, "INSERT INTO $_DB_cursus VALUES('##NEW_ID##','$candidat_id','$diplome','$intitule','$specialite','$annee_obtention','$ecole','$ville','$pays','$note_moyenne','$mention','$rang')");

				// Si l'année d'obtention est celle en cours (ou +1), on modifie le champ adéquat dans la fiche du candidat
				if($annee_obtention==$__PERIODE || $annee_obtention==($__PERIODE+1))
				{
					db_query($dbr,"UPDATE $_DB_candidat SET $_DBU_candidat_cursus_en_cours='$annee_obtention'
										WHERE $_DBU_candidat_id='$candidat_id'");

					$_SESSION["cursus_en_cours"]="$annee_obtention";
					$_SESSION["force_annee_courante"]=0;
				}

				write_evt("", $__EVT_ID_C_CURSUS, "Ajout cursus : $annee_obtention : " . str_replace("'", "''", stripslashes($diplome)), $candidat_id, $cursus_id);
			}
			else
			{
				db_query($dbr,"UPDATE $_DB_cursus SET	$_DBU_cursus_diplome='$diplome',
																	$_DBU_cursus_intitule='$intitule',
																	$_DBU_cursus_spec='$specialite',
																	$_DBU_cursus_annee='$annee_obtention',
																	$_DBU_cursus_ecole='$ecole',
																	$_DBU_cursus_ville='$ville',
																	$_DBU_cursus_pays='$pays',
																	$_DBU_cursus_moyenne='$note_moyenne',
																	$_DBU_cursus_mention='$mention',
																	$_DBU_cursus_rang='$rang'
									WHERE $_DBU_cursus_id='$cid'
									AND $_DBU_cursus_candidat_id='$candidat_id'");

				if($annee_obtention==$__PERIODE || $annee_obtention==($__PERIODE+1))
				{
					$_SESSION["cursus_en_cours"]="$annee_obtention";
					$_SESSION["force_annee_courante"]=0;
				}

				write_evt("", $__EVT_ID_C_CURSUS, "MAJ cursus : $annee_obtention : " . str_replace("'", "''", stripslashes($diplome)), $candidat_id, $cid);
			}
			db_close($dbr);

			session_write_close();
			header("Location:precandidatures.php");
			exit();
		}
	}
	elseif(isset($_POST["Suivant"]) || isset($_POST["Suivant_x"])) // validation du formulaire pour l'année courante
	{
		if(array_key_exists("dip_en_cours", $_POST))
			$reponse_force=$_POST["dip_en_cours"];
		else
			$reponse_force=1;

      // Confirmation de l'année d'obtention
      if(array_key_exists("dip_en_cours_annee", $_POST))
         $_SESSION["force_annee"]=$_POST["dip_en_cours_annee"];
      
		// Si la réponse est non, on supprime la demande directe de l'année en cours et on enregistre cette décision
		if(!$reponse_force)
		{
			$_SESSION["cursus_en_cours"]="$__PERIODE";
			$_SESSION["force_annee_courante"]=0;

			db_query($dbr,"UPDATE $_DB_candidat SET $_DBU_candidat_cursus_en_cours='$__PERIODE' WHERE $_DBU_candidat_id='$candidat_id'");
		}
	}
	else
	{
		// avant de continuer, on regarde si le candidat a complété l'année en cours
		// Si ce n'est pas le cas : on redirige vers un formulaire à part

		if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_cursus WHERE $_DBC_cursus_candidat_id='$candidat_id'
																					 AND $_DBC_cursus_annee='$__PERIODE'"))
			 && $_SESSION["cursus_en_cours"]!=$__PERIODE
          && $_SESSION["cursus_en_cours"]!=($__PERIODE+1))
			$_SESSION["force_annee_courante"]=1;
		else
			$_SESSION["force_annee_courante"]=0;
	}

	if(isset($cid) && $cid!=0)
	{
		// récupération des valeurs courantes
		$result=db_query($dbr,"SELECT $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_annee,
												$_DBC_cursus_ecole, $_DBC_cursus_ville, $_DBC_cursus_pays, $_DBC_cursus_moyenne,
												$_DBC_cursus_mention, $_DBC_cursus_rang
									  FROM $_DB_cursus WHERE $_DBC_cursus_id='$cid'");
		$rows=db_num_rows($result);

		if(!$rows) // erreur
		{
			db_free_result($result);
			db_close($dbr);
			
			session_write_close();
			header("Location:../index.php");
			exit();
		}
		else
		{
			list($cur_diplome,$cur_intitule,$cur_specialite,$cur_annee_obtention,$cur_ecole,$cur_ville,$cur_pays,$cur_note_moyenne,$cur_mention,$cur_rang)=db_fetch_row($result,0);
			db_free_result($result);
		}
	}
	else // nouvelle étape : initialisation des valeurs
		$cur_diplome=$cur_intitule=$cur_specialite=$cur_annee_obtention=$cur_ecole=$cur_ville=$cur_pays=$cur_note_moyenne=$cur_mention=$cur_rang="";

	en_tete_candidat();
	menu_sup_candidat($__MENU_FICHE);
?>

<div class='main'>
	<?php
		titre_page_icone("Ajouter/Modifier une étape de votre cursus scolaire", "edit_32x32_fond.png", 15, "L");

		print("<form action='$php_self' method='POST' name='form1'>\n");

		if(isset($champ_vide))
			message("Formulaire incomplet : les champs en gras sont <u>obligatoires</u>", $__ERREUR);
		elseif(isset($annee_format))
			message("Erreur : la valeur du champ 'Année' est incorrecte (valeur numérique à 4 chiffres, années futures interdites)", $__ERREUR);

		if(isset($_SESSION["force_annee_courante"]) && $_SESSION["force_annee_courante"]==1 && !isset($reponse_force))
		{
			message("Vous devez tout d'abord compléter les informations relatives à l'année en cours : ", $__INFO);
	?>

	<table style="margin-left:auto; margin-right:auto;">
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'><b>Préparez-vous actuellement un diplôme</b> ?</font>
		</td>
		<td class='td-droite fond_menu' style="text-align:left;">
			<font class='Texte_menu'>
				<?php
					if(!isset($en_cours_flag))
						$en_cours_flag=1;

					if($en_cours_flag=="" || $en_cours_flag==0)
					{
						$yes_checked="";
						$no_checked="checked";
					}
					else
					{
						$yes_checked="checked";
						$no_checked="";
					}

					print("<input type='radio' name='dip_en_cours' value='1' $yes_checked>&nbsp;Oui
							&nbsp;&nbsp;<input type='radio' name='dip_en_cours' value='0' $no_checked>&nbsp;Non\n");
				?>
			</font>
		</td>
	</tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_menu2'><b>Si oui, indiquez l'année d'obtention prévue</b> :</font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <select name='dip_en_cours_annee'>
            <?php
               /* L'année peut être assez complexe à déterminer, en fonction de la date à laquelle le candidat remplit sa fiche.
               - Si c'est en fin d'année, l'année universitaire en cours est encore "l'ancienne" (ex : en décembre 2009, la "période" est encore "2009").
               Dans ce cas là, on ne peut pas forcer l'année à "2009" ("période" ou année en cours) car le candidat obtiendra vraisemblablement son année
               en 2010.
               - En début d'année (avant le mois séparant deux "périodes"), par exemple janvier 2010, la période est toujours "2009" : il faut prendre l'année en cours.
               - Passé le mois limite, on peut prendre la "période" ou l'année en cours.

               Idée :
               - de janvier à octobre (inclus) de l'année A, on propose A et A+1, en préselectionnant A
               - à partir d'octobre, on préselectionne A+1 (plus vraisemblable)
               */

               $Y=date("Y");

               if(date("n")<10)
                  $annee_defaut=date("Y");
               else
                  $annee_defaut=date("Y")+1;
   
               if(!isset($en_cours_annee))
                  $en_cours_annee=$annee_defaut;

               if($en_cours_annee==$Y)
               {
                  $Y_selected="selected";
                  $NY_selected="";
               }
               else
               {
                  $Y_selected="";
                  $NY_selected="selected";
               }

               print("<option value='$Y' $Y_selected>$Y</option>
                      <option value='".($Y+1)."' $NY_selected>".($Y+1)."</option>\n");
            ?>
         </select>
         <font class='Texte' style='padding-left:10px;'>
            <i>Si le diplôme se prépare en plusieurs années, n'indiquez que l'année en cours.
            <br>Exemple : si vous êtes en seconde année de licence (L2), indiquez l'année d'obtention prévue du L2.</i>
         </font>
      </td>
   </tr>
	</table>

	<div class='centered_icons_box'>
		<a href='precandidatures.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Suivant" name="Suivant" value="Suivant">
		</form>
	</div>
	
	<?php
		}
		else
		{
			if(!isset($_SESSION["force_annee_courante"]) || $_SESSION["force_annee_courante"]==0 || $reponse_force==0)
				message("<center>
				            <strong><u>Important</u></strong> 
				            <br><br>Le cursus doit être renseigné à partir du <strong>Baccalauréat</strong> (ou équivalent) inclus.
							   <br>Si vous n'avez pas le baccalauréat, sélectionnez <strong>\"Autre\"</strong> dans le champ \"Diplôme\" et indiquez le dernier diplôme obtenu dans le champ \"Intitulé\".
						   </center>", $__WARNING);
			else
			{
				$txt_periode=isset($_SESSION["force_annee"]) && $_SESSION["force_annee"]!="" ? $_SESSION["force_annee"]-1 . " - $_SESSION[force_annee]" : $__PERIODE-1 . " - $__PERIODE";
				message("Veuillez indiquer le diplôme préparé au titre de l'année <strong>$txt_periode</strong> :", $__INFO);
			}
	?>

	<table style="margin-left:auto; margin-right:auto;">
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Diplôme / Niveau d'études</b></font>
		</td>
		<td class='td-droite fond_menu' style="text-align:left;">
			<select name='filiere' size='1'>
			<?php

				$result=db_query($dbr,"SELECT $_DBC_cursus_diplomes_intitule, $_DBC_cursus_diplomes_niveau
													FROM $_DB_cursus_diplomes
												ORDER BY $_DBC_cursus_diplomes_niveau,lower($_DBC_cursus_diplomes_intitule)");

/*
				$result=db_query($dbr,"SELECT $_DBC_cursus_apogee_code, $_DBC_cursus_apogee_libelle_long
													FROM $_DB_cursus_apogee
												ORDER BY $_DBC_cursus_apogee_code");
*/
				$rows=db_num_rows($result);

				$current_niveau=-10; // initialisé à n'importe quelle valeur inférieure à -1

				if(isset($diplome))
					$cur_diplome=$diplome;

				if($cur_diplome=="")
					print("<option value='' selected></option>");
				else
					print("<option value=''></option>");

				if($cur_diplome=="Autre")
					$selected="selected";
				else
					$selected="";

				print("<option value='Autre' $selected>Autre (préciser dans le champ \"Mention - Intitulé\"</option>
							<option value=''></option>\n");

				$value2=preg_replace("/_/","",htmlspecialchars(stripslashes($cur_diplome), ENT_QUOTES, $default_htmlspecialchars_encoding));

				for($i=0; $i<$rows; $i++)
				{
					// list($diplome_code,$diplome_intitule)=db_fetch_row($result,$i);
					list($diplome_intitule,$diplome_niveau)=db_fetch_row($result,$i);
					$value=htmlspecialchars($diplome_intitule, ENT_QUOTES, $default_htmlspecialchars_encoding);

					if($diplome_niveau!=$current_niveau)
					{
						switch($diplome_niveau)
						{
							case -1	:	$type_niveau="------ Filières particulières ------";
													break;

							case 0	:	$type_niveau="------ Niveau Baccalauréat ------";
													break;

							default	:	$type_niveau="------ Niveau Bac + $diplome_niveau ------";
													break;
						}

						print("<option value='' disabled>$type_niveau</option>");

						$current_niveau=$diplome_niveau;
					}

					if(isset($diplome))
						$cur_diplome=$diplome;

					if(isset($cur_diplome) && $value2==$value)
					{
						$selected="selected=1";
					}
					else
						$selected="";

					print("<option value='$value' $selected>$value</option>\n");
				}
				db_free_result($result);
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Mention / Intitulé</b></font>
		</td>
		<td class='td-droite fond_menu' style="text-align:left;">
			<input type='text' name='intitule_libre' value='<?php if(isset($intitule)) echo html_entity_decode($intitule); elseif(isset($cur_intitule)) echo htmlspecialchars(stripslashes($cur_intitule),ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="80" maxlength="256">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'>Spécialité / Parcours (si applicable)</font>
		</td>
		<td class='td-droite fond_menu' style="text-align:left;">
			<input type='text' name='specialite' value='<?php if(isset($specialite)) echo htmlspecialchars(stripslashes(str_replace("_","",$specialite)), ENT_QUOTES, $default_htmlspecialchars_encoding);  else echo htmlspecialchars(preg_replace("/_/","",$cur_specialite),ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='50' size='30'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Année</b></font>
		</td>
		<td class='td-droite fond_menu' style="text-align:left;">
			<?php
				if(isset($reponse_force) && $reponse_force==1)
            {
               $annee_forcee=isset($_SESSION["force_annee"]) && $_SESSION["force_annee"]!="" ? $_SESSION["force_annee"] : $__PERIODE;

					print("<font class='Texte_menu'><b>$annee_forcee</b></font>
								<br><input type='hidden' name='annee' value='$annee_forcee'>\n");
            }
				else
				{
			?>
			<input type='text' name='annee' value='<?php if(isset($annee_obtention)) echo $annee_obtention; else echo $cur_annee_obtention;?>' maxlength='4' size='15'>&nbsp;&nbsp;
			<font class='Texte_menu'>
				<i><u>Format</u> : AAAA
				<br>Si le champ est vide, l'année <strong><?php echo date("Y"); ?></strong> sera automatiquement prise en compte.
				</i>
			</font>
			<?php
				}
			?>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Etablissement</b></font>
		</td>
		<td class='td-droite fond_menu' style="text-align:left;">
			<input type='text' name='ecole' value='<?php if(isset($ecole)) $cur_ecole=$ecole; echo htmlspecialchars(str_replace("_","",stripslashes($cur_ecole)),ENT_QUOTES, $default_htmlspecialchars_encoding);?>' maxlength='128' size='30'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Ville</b></font>
		</td>
		<td class='td-droite fond_menu' style="text-align:left;">
			<input type='text' name='ville' value='<?php if(isset($ville)) $cur_ville=$ville; echo htmlspecialchars(str_replace("_","",stripslashes($cur_ville)),ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='128' size='30'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Pays</b></font>
		</td>
		<td class='td-droite fond_menu' style="text-align:left;">
			<select name='pays' size='1'>
				<?php
					$res_pays_nat=db_query($dbr, "SELECT $_DBC_pays_nat_ii_iso, $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii
															ORDER BY to_ascii($_DBC_pays_nat_ii_pays)");
											
					$rows_pays_nat=db_num_rows($res_pays_nat);
	
					for($p=0; $p<$rows_pays_nat; $p++)
					{
						list($code_iso, $table_pays)=db_fetch_row($res_pays_nat, $p);
		
						$selected=(isset($pays) && $pays==$code_iso) || (isset($cur_pays) && $cur_pays==$code_iso) ? "selected=1" : "";

						print("<option value='$code_iso' $selected>$table_pays</option>\n");
					}
				?>
			</select>
		</td>
	</tr>
	<?php
		// Les champs suivants ne sont pas demandés pour le diplôme en cours
		if(isset($reponse_force) && $reponse_force==1)
			$font_class="class='Texte_menu2'";
		else
			$font_class="class='Texte_important_menu2' style='font-weight:bold;'";
	?>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font <?php echo $font_class; ?>>Mention / Résultat obtenu</font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='mention' size='1'>
				<?php
					$result=db_query($dbr,"SELECT $_DBC_cursus_mentions_intitule FROM $_DB_cursus_mentions ORDER BY $_DBC_cursus_mentions_id");
					$rows=db_num_rows($result);

					if(isset($mention))
						$cur_mention=$mention;

					$value2=htmlspecialchars($cur_mention,ENT_QUOTES, $default_htmlspecialchars_encoding);

					for($i=0; $i<$rows; $i++)
					{
						list($mention)=db_fetch_row($result,$i);
						$value=htmlspecialchars($mention,ENT_QUOTES, $default_htmlspecialchars_encoding);

						if(isset($value2) && !strcmp($value,$value2))
							$selected="selected=1";
						else
							$selected="";

						print("<option value='$value' $selected>$value</option>\n");
					}
				?>
			</select>&nbsp;&nbsp;<font class='Texte_menu'><i><b>Champ facultatif pour votre année en cours</b></i></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font <?php echo $font_class; ?>>Note moyenne rapportée à 20</font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='note' value='<?php if(isset($note_moyenne)) echo str_replace("_","",$note_moyenne); else echo preg_replace("/_/","",$cur_note_moyenne);?>' maxlength='10' size='10'>&nbsp;&nbsp;<font class='Texte_menu'><i>Exemple : 14,54/20&nbsp;&nbsp;<b>Champ facultatif pour votre année en cours</b></i></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'>Rang (si connu)</font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='rang' value='<?php if(isset($rang)) echo preg_replace("/_/","",$rang); else echo str_replace("_","",$cur_rang);?>' maxlength='15' size='10'>&nbsp;&nbsp;<font class='Texte_menu'><i>Exemple : 22/80</i></font>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='precandidatures.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
		</form>
	</div>

	<?php
		db_close($dbr);
	} // fin du else(force_annee_courante)
	?>
</div>
<?php
	pied_de_page_candidat();
?>

<script language="javascript">
	document.form1.diplome.focus()
</script>
</body></html>

