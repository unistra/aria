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

	// identifiant de l'�tudiant
	if(isset($_SESSION["candidat_id"]))
		$candidat_id=$_SESSION["candidat_id"];

	$dbr=db_connect();

	// D�verrouillage, au cas o�
	if(isset($_SESSION["candidat_id"]))
		cand_unlock($dbr, $_SESSION["candidat_id"]);

	if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
	{
		unset($_SESSION["candidatures_array"]);
		$propspec_id=$_POST["formation"];

		if($propspec_id!="")
		{
			$resultat=1;
			$_SESSION["propspec_id"]=$propspec_id;
		}
		else
			$selection_invalide=1;
	}
	elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]) && isset($_SESSION["propspec_id"]))
	{
		$propspec_id=$_SESSION["propspec_id"];
		$jour_inf=$_POST["date_inf"];
		$jour_sup=$_POST["date_sup"];

		$jour_inf_min=$_POST["jour_inf_min"];
		$jour_sup_max=$_POST["jour_sup_max"];

		$filtre_dec=isset($_POST["filtre_decision"]) && array_key_exists($_POST["filtre_decision"], $__DOSSIER_DECISIONS_COURTES) ? $_POST["filtre_decision"] : "";
		$param_dec=($filtre_dec!="") ? "&decid=$_POST[filtre_decision]" : "";

		$tri=isset($_POST["tri"]) && ($_POST["tri"]=="dec" || $_POST["tri"]=="nom") ? "&tri=$_POST[tri]" : "&tri=nom";

      if($jour_inf=="" || $jour_inf==0)
         $jour_inf=$jour_inf_min;
         
		if($jour_sup=="" || $jour_sup==0)
         $jour_sup=$jour_sup_max;
/*
		if($jour_inf=="" || $jour_sup=="")
			$date_invalide=1;
		else
		{
*/		
			if(array_key_exists("force", $_POST) && $_POST["force"]==1)
			{
				// Date sur la lettre
				$force_jour=$_POST["force_jour"];
				$force_mois=$_POST["force_mois"];
				$force_annee=$_POST["force_annee"];

				if(!is_numeric($force_annee) || $force_annee<date("Y"))
					$force_annee=date("Y");

				$new_date_decision="&date=" . MakeTime(23,59,50,$force_mois, $force_jour, $force_annee); // date au format unix
			}
			else
				$new_date_decision="";

			// Limite sup�rieure : on se cale sur la fin de la journ�e (on ajoute 82799 secondes (86400s - 3601s))
			$jour_sup+=82799;

			// Inversion, au cas o�
			if($jour_inf>$jour_sup)
			{
				$temp=$jour_inf;
				$jour_inf=$jour_sup;
				$jour_sup=$temp;
			}

			$lien_lettres="<a href='lettres/generateur_lettres.php?jour_inf=$jour_inf&jour_sup=$jour_sup&id_form=$propspec_id$new_date_decision$param_dec&$tri' class='lien_bleu_10' target='_blank'>Lettres pr�tes - cliquez ici pour les ouvrir (ouverture dans une nouvelle page)</a>";
			$resultat=2;
//		}
	}
			
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("G�n�rer des lettres de d�cisions en masse (Commissions P�dagogiques)", "kpersonalizer_32x32_fond.png", 15, "L");

		message("Dans cette section, vous pouvez �galement g�n�rer des lettres pour des d�cisions non trait�es en masse
					<br>- <b>Les formations pour lesquelles aucune d�cision n'a �t� rendue n'apparaissent pas</b>.
					<br>- Si aucune date n'est s�lectionn�e, toutes les lettres seront g�n�r�es.", $__INFO);

		if(isset($selection_invalide))
			message("Erreur : veuillez s�lectionner une formation valide dans le menu d�roulant.", $__ERREUR);

		if(isset($date_invalide))
			message("Erreur : veuillez s�lectionner une date valide dans le menu d�roulant.", $__ERREUR);

		if(isset($_GET["erreur"]) && $_GET["erreur"]==1)
			message("Erreur lors de la g�n�ration des lettres. Un message a �t� envoy� � l'administrateur.", $__ERREUR);

		if(isset($success) && $nb_success>0)
		{
			if($nb_success==1)
				message("$nb_success d�cision valid�e avec succ�s", $__SUCCES);
			else
				message("$nb_success d�cisions valid�es avec succ�s", $__SUCCES);
		}
		elseif(isset($deja_traitee) && $deja_traitee!=0)
		{
			if($deja_traitee==1)
				message("ATTENTION : une d�cision n'a pas �t� valid�e car elle semblait d�j� trait�e. $nb_success ont �t� valid�es avec succ�s.", $__ERREUR);
			else
				message("ATTENTION : $deja_traitee d�cisions n'ont pas �t� valid�es car elles semblaient d�j� trait�es. $nb_success ont �t� valid�es avec succ�s.", $__ERREUR);
		}

		print("<form action='$php_self' method='POST' name='form1'>\n");

		if(!isset($resultat))
		{
			$dbr=db_connect();

			$result=db_query($dbr,"SELECT count(*), $_DBC_annees_ordre, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_id, $_DBC_propspec_finalite
												FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_cand
											WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_specs_comp_id='$_SESSION[comp_id]'
											AND $_DBC_cand_propspec_id=$_DBC_propspec_id
											AND $_DBC_cand_decision!='$__DOSSIER_NON_TRAITE'
											AND $_DBC_cand_periode='$__PERIODE'
												GROUP BY $_DBC_annees_ordre, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_id, $_DBC_propspec_finalite
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom_court, $_DBC_propspec_finalite");
			$rows=db_num_rows($result);

			// variables initialis�es � n'importe quoi
			$prev_annee="--";
			$prev_mention="";

			if($rows)
			{
				print("<table align='center'>
						<tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><b>Formation : </b></font>
							</td>
							<td class='td-milieu fond_menu'>
								<select name='formation' size='1' style='vertical-align:middle;'>
									<option value='' disabled></option>\n");

			for($i=0; $i<$rows; $i++)
			{
				list($count, $annee_ordre, $annee, $nom, $cur_propspec_id, $finalite)=db_fetch_row($result,$i);

				if($annee!=$prev_annee)
				{
					if($i)
						print("</optgroup>\n");

					if(empty($annee))
						print("<optgroup label='Ann�es particuli�res'>\n");
					else
						print("<optgroup label='$annee'>\n");

					$prev_annee=$annee;
				}

				if($annee=="")
					$annee_spec_txt="$nom";
				else
					$annee_spec_txt="$annee - $nom $tab_finalite[$finalite]";

				if(isset($propspec_id) && $propspec_id==$cur_propspec_id)
					$selected="selected=1";
				else
					$selected="";

				print("<option value='$cur_propspec_id' label=\"$annee_spec_txt ($count d�cisions)\" $selected>$annee_spec_txt ($count d�cision(s))</option>\n");
			}

			print("</optgroup>
					</select>
				</td>
				<td class='td-droite fond_menu'>
					<input type='image' border='0' src='$__ICON_DIR/forward_22x22_menu.png' alt='Suivant' name='suivant' value='Suivant'>
				</td>
			</tr>
			</table>\n");
		}
		else
			message("Aucune d�cision n'a encore �t� rendue dans votre composante  : il n'y a rien � imprimer.", $__WARNING);
	?>

	<script language="javascript">
		document.form1.formation.focus()
	</script>

	<br>

	<?php
		}
		elseif(isset($resultat) && $resultat==1) // r�sultat de la recherche : Choix de la date
		{
			// Nom de la formation choisie
			$result=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
												FROM $_DB_propspec, $_DB_annees, $_DB_specs
											WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id='$propspec_id'
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom");

			list($nom_annee, $spec_nom, $finalite)=db_fetch_row($result,0);

			if($nom_annee=="")
				$_SESSION["formation_txt"]=$formation_txt="$spec_nom $tab_finalite[$finalite]";
			else
				$_SESSION["formation_txt"]=$formation_txt="$nom_annee $spec_nom $tab_finalite[$finalite]";

			db_free_result($result);

			// D�cisions disponibles
			$result=db_query($dbr,"SELECT $_DBC_cand_decision,$_DBC_decisions_texte, count(*) FROM $_DB_cand, $_DB_decisions
			                       WHERE $_DBC_cand_decision=$_DBC_decisions_id
			                       AND $_DBC_cand_propspec_id='$propspec_id'
			                       AND $_DBC_cand_decision!='$__DOSSIER_NON_TRAITE'
			                       AND $_DBC_cand_periode='$__PERIODE'			               
											GROUP BY $_DBC_cand_decision, $_DBC_decisions_texte         
			                       ORDER BY $_DBC_decisions_texte");
			                        
			$rows=db_num_rows($result);

         $liste_decisions="<select size='1' name='filtre_decision'>
                              <option value=''>Toutes</option>\n";

         if($rows)
         {
            for($i=0; $i<$rows; $i++)
            {
               list($dec_id, $dec_texte, $dec_count)=db_fetch_row($result, $i);

               $selected=(isset($filtre_dec) && $filtre_dec==$dec_id) ? "selected='1'" : "";

               $liste_decisions.="<option value='$dec_id' $selected>$dec_texte ($dec_count)</option>\n";
            }
         }                        
			    
         $liste_decisions.="</select>\n";
                    
			db_free_result($result);

			// Dates disponibles
			// TODO : v�rifier l'ensemble des dates possibles et ajouter des tests
			// Une date de d�cision peut-elle �tre nulle ?
			$result=db_query($dbr,"SELECT DISTINCT(CASE WHEN $_DBC_cand_date_prise_decision='0'
																THEN '0'
																ELSE TO_CHAR(TIMESTAMP WITH TIME ZONE 'epoch' + $_DBC_cand_date_prise_decision * INTERVAL '1 second', 'YYYY-MM-DD')
																END) as date_case
												FROM $_DB_cand
											WHERE $_DBC_cand_propspec_id='$propspec_id'
											AND $_DBC_cand_decision!='$__DOSSIER_NON_TRAITE'
											AND $_DBC_cand_periode='$__PERIODE'
										  ORDER BY date_case");


			$rows=db_num_rows($result);

			if($rows)
			{
				$liste_options="";

				for($i=0; $i<$rows; $i++)
				{
					list($date_base)=db_fetch_row($result, $i);

					if($date_base==0)
					{
						$date_txt="Date ind�termin�e";
						$jour_debut="0";
					}
					else
					{
						$date_array=explode("-", $date_base);

						$jour_debut=maketime("1","0","0", $date_array[1], $date_array[2], $date_array[0]);

						// $jour_debut=strtotime("$date_array[0]$date_array[1]$date_array[2],0100");
						$date_txt=date_fr("l j F Y", $jour_debut);
						
						if($i==0)
							$jour_inf_min=$jour_debut;
					}

					// Conservation des deux extr�mes (valeurs par d�faut)
					if($i==0)
						$jour_inf_min=$jour_debut;
					elseif($i==($rows-1))
					   $jour_sup_max=$jour_debut;

					$liste_options.="<option value='$jour_debut'>$date_txt</option>\n";
				}				

				print("<input type='hidden' name='jour_inf_min' value='$jour_inf_min'>
						 <input type='hidden' name='jour_sup_max' value='$jour_sup_max'>
						 
						 <table align='center'>
						 <tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><b>Formation : </b></font>
							</td>
							<td class='td-milieu fond_menu2' colspan='2'>
								<font class='Texte_menu2'><b>$formation_txt</b></font>
							</td>
							<td class='td-droite fond_menu2' rowspan='6'>
								<input type='image' border='0' src='$__ICON_DIR/button_ok_22x22_menu2.png' alt='Valider' name='valider' value='Valider'>
							</td>
						</tr>
 					   <tr>
                     <td class='td-gauche fond_menu2'>
                        <font class='Texte_menu2'><b>Filtrer par d�cision ?</b></font>
                     </td>
                     <td class='td-milieu fond_menu2' colspan='2'>
								$liste_decisions
                     </td>                     
						</tr>
						<tr>
                     <td class='td-gauche fond_menu2'>
                        <font class='Texte_menu2'><b>Tri des lettres</b></font>
                     </td>
                     <td class='td-milieu fond_menu2' colspan='2'>
								<select size='1' name='tri'>
								   <option value='nom'>Par nom/pr�nom</option>
									<option value='dec'>Par d�cision</option>
								</select>
                     </td>                     
						</tr>
						<tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><b>Dates limites (jours inclus) : </b></font>
							</td>
							<td class='td-milieu fond_menu'>
								<font class='Texte_menu'><b>Inf�rieure : </b></font>
								<select name='date_inf' size='1' style='vertical-align:middle;'>
									<option value='' disabled></option>
									$liste_options
								</select>
							</td>
							<td class='td-milieu fond_menu'>
								<font class='Texte_menu'><b>Sup�rieure : </b></font>
								<select name='date_sup' size='1' style='vertical-align:middle;'>
									<option value='' disabled></option>
									$liste_options
								</select>
							</td>
						</tr>
						<tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><b>Forcer la date des lettres ?</b><br>(<i>A manipuler <b>avec prudence</b></i>)</font>
							</td>
							<td class='td-milieu fond_menu' colspan='2'>
								<font class='Texte_menu'>
									<input style='padding-right:10px;' type='radio' name='force' value='1'>Oui<input style='padding-left:15px; padding-right:10px;' type='radio' name='force' value='0' checked='1'>Non
								</font>
							</td>
						</tr>
						<tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><b>Si oui, Nouvelle date :</b></font>
							</td>
							<td class='td-milieu fond_menu' colspan='2'>
								<font class='Texte_menu'>
									<select name='force_jour' style='vertical-align:middle;'>\n");

				$force_date_jour=date("j", time());
				$force_date_mois=date("n", time());
				$force_date_annee=date("Y", time());

				for($j=1; $j<=31; $j++)
				{
					$selected=($force_date_jour==$j) ? "selected" : "";

					print("<option value='$j' $selected>$j</option>\n");
				}
			?>
						</select>
						&nbsp;
						<select name='force_mois' style='vertical-align:middle;'>
							<option value='1' <?php if($force_date_mois==1) echo "selected"; ?>>Janvier</option>
							<option value='2' <?php if($force_date_mois==2) echo "selected"; ?>>Fevrier</option>
							<option value='3' <?php if($force_date_mois==3) echo "selected"; ?>>Mars</option>
							<option value='4' <?php if($force_date_mois==4) echo "selected"; ?>>Avril</option>
							<option value='5' <?php if($force_date_mois==5) echo "selected"; ?>>Mai</option>
							<option value='6' <?php if($force_date_mois==6) echo "selected"; ?>>Juin</option>
							<option value='7' <?php if($force_date_mois==7) echo "selected"; ?>>Juillet</option>
							<option value='8' <?php if($force_date_mois==8) echo "selected"; ?>>Ao�t</option>
							<option value='9' <?php if($force_date_mois==9) echo "selected"; ?>>Septembre</option>
							<option value='10' <?php if($force_date_mois==10) echo "selected"; ?>>Octobre</option>
							<option value='11' <?php if($force_date_mois==11) echo "selected"; ?>>Novembre</option>
							<option value='12' <?php if($force_date_mois==12) echo "selected"; ?>>D�cembre</option>
						</select>
						&nbsp;
						<input type='text' name='force_annee' maxlength="4" size="6" value='<?php echo $force_date_annee; ?>' style='vertical-align:middle;'>
						<br><i>(Cette date ne sera pas enregistr�e dans la base de donn�es)</i>
					</font>
				</td>
			</tr>
			</table>
			<?php
			}

			db_free_result($result);

		}
		elseif(isset($resultat) && $resultat==2)
		{
			$date_inf=date_fr("l j F Y", $jour_inf);

			$date_sup=date_fr("l j F Y", $jour_sup);
			
			if($date_inf==$date_sup)
				$date_txt="$date_inf";
			else
				$date_txt="$date_inf - $date_sup";

			if(isset($filtre_dec) && $filtre_dec!="")
			{
            $res_decision=db_query($dbr, "SELECT $_DBC_decisions_texte FROM $_DB_decisions WHERE $_DBC_decisions_id='$filtre_dec'");

			   if(db_num_rows($res_decision))
			     list($filtre_decision_texte)=db_fetch_row($res_decision, 0);
				else // possible ? si oui, il serait sans doute pr�f�rable de ne pas lancer la g�n�ration des lettres ... ?
				  $filtre_decision_texte="Filtre incomplet";
			}
			else
				$filtre_decision_texte="Toutes";

			print("<table align='center'>
					<tr>
						<td class='td-gauche fond_menu2'>
							<font class='Texte_menu2'><b>Formation : </b></font>
						</td>
						<td class='td-droite fond_menu2'>
							<font class='Texte_menu2'><b>$_SESSION[formation_txt]</b></font>
						</td>
					</tr>
					<tr>
						<td class='td-gauche fond_menu2'>
							<font class='Texte_menu2'><b>D�cision(s) : </b></font>
						</td>
						<td class='td-droite fond_menu2'>
							<font class='Texte_menu2'><b>$filtre_decision_texte</b></font>
						</td>
					</tr>
					<tr>
						<td class='td-gauche fond_menu2'>
							<font class='Texte_menu2'><b>Intervalle : </b></font>
						</td>
						<td class='td-droite fond_menu2'>
							<font class='Texte_menu2'><b>$date_txt</b></font>
						</td>
					</tr>
					</table>\n");
		}

		db_close($dbr);

		if(isset($lien_lettres) && $lien_lettres!="")
			print("<br>
					<center>$lien_lettres</center>\n");
	?>

	<div class='centered_icons_box'>
		<a href='masse.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/rew_32x32_fond.png"; ?>' alt='Retour au menu pr�c�dent' border='0'></a>
		<?php
			if(isset($resultat))
				print("<a href='$php_self' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour au menu pr�c�dent' border='0'></a>");
		?>
		</form>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
