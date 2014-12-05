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

   // Blocage si le fichier gestion/admin/config.php existe encore
   if(is_file("../gestion/admin/config.php") && is_readable("../gestion/admin/config.php"))
      die("Configuration de l'interface incomplète - Accès impossible.\n");

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/access_functions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	unset($_SESSION["code_conf"]);

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;
/*
	if(!isset($_SESSION["date_ouverture_globale"]) || !isset($_SESSION["date_fermeture_globale"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}
*/
	$dbr=db_connect();

	$load_config=__get_config($dbr);

	if(isset($_POST["valider1"]) || isset($_POST["valider1_x"])) // validation du formulaire - 1er bouton
	{
		// vérification identifiant/code d'accès
		$identifiant=strtolower(trim($_POST["identifiant"]));
		$code_personnel=trim($_POST["code_personnel"]);

		$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_nom_naissance, $_DBC_candidat_prenom,
												$_DBC_candidat_prenom2, $_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance,
												$_DBC_candidat_dpt_naissance, $_DBC_candidat_nationalite as nat_code,
												CASE WHEN $_DBC_candidat_nationalite IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite) 
													  THEN (SELECT $_DBC_pays_nat_ii_nat FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite)
													  ELSE '' END as nationalite,
												$_DBC_candidat_telephone, $_DBC_candidat_telephone_portable, $_DBC_candidat_adresse_1, $_DBC_candidat_adresse_2, 
												$_DBC_candidat_adresse_3, $_DBC_candidat_numero_ine, $_DBC_candidat_email, $_DBC_candidat_connexion, 
												$_DBC_candidat_pays_naissance as pays_code,
												CASE WHEN $_DBC_candidat_pays_naissance IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance) 
													  THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance)
													  ELSE '' END as pays_naissance,
												$_DBC_candidat_adresse_cp, $_DBC_candidat_adresse_ville, $_DBC_candidat_adresse_pays as adresse_pays_code,
												CASE WHEN $_DBC_candidat_adresse_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_adresse_pays) 
													  THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_adresse_pays)
													  ELSE '' END as adresse_pays,
												$_DBC_candidat_cursus_en_cours, $_DBC_candidat_deja_inscrit, $_DBC_candidat_annee_premiere_inscr,
											$_DBC_candidat_annee_bac, $_DBC_candidat_serie_bac
											FROM $_DB_candidat
										WHERE $_DBC_candidat_identifiant LIKE '$identifiant'
										AND $_DBC_candidat_code_acces LIKE '$code_personnel'");
		if(db_num_rows($result)) // on a une réponse positive : le code existe
		{
			list($candidat_id,$civilite,$nom,$nom_naissance, $prenom,$prenom2,$date_naissance,$lieu_naissance,$dpt_naissance,$nationalite_code,$nationalite,$telephone, $telephone_portable, 
			     $adresse_1,$adresse_2,$adresse_3,$numero_ine,$email, $connexion, $pays_naissance_code, $pays_naissance, $adr_cp, $adr_ville, $adr_pays_code, $adr_pays, 
				  $cursus_en_cours, $deja_inscrit, $annee_premiere_inscr, $annee_bac, $serie_bac)=db_fetch_row($result,0);
			db_free_result($result);
			
			// Département de naissance
			$res_departements=db_query($dbr, "SELECT $_DBC_departements_fr_nom FROM $_DB_departements_fr
                                           WHERE $_DBC_departements_fr_numero='$dpt_naissance'");

         if(db_num_rows($res_departements))
            list($nom_departement)=db_fetch_row($res_departements, 0);
         else
            $nom_departement="";

			db_free_result($res_departements);

			// Série du bac
			$res_series_bac=db_query($dbr, "SELECT $_DBC_diplomes_bac_intitule FROM $_DB_diplomes_bac
													  WHERE $_DBC_diplomes_bac_code='$serie_bac'");

         if(db_num_rows($res_series_bac))
            list($nom_serie_bac)=db_fetch_row($res_series_bac, 0);
         else
            $nom_serie_bac="";

			db_free_result($res_series_bac);
			
			$_SESSION["authentifie"]=$candidat_id;
			$_SESSION["nom"]=htmlspecialchars($nom, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["nom_naissance"]=htmlspecialchars($nom_naissance, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["civilite"]=htmlspecialchars($civilite, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["prenom"]=htmlspecialchars($prenom, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["prenom2"]=htmlspecialchars($prenom2, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["naissance"]=$date_naissance;
			$_SESSION["lieu_naissance"]=htmlspecialchars($lieu_naissance, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["dpt_naissance"]=$dpt_naissance;
			$_SESSION["nom_departement"]=$nom_departement;
			$_SESSION["pays_naissance_code"]=$pays_naissance_code;
			$_SESSION["pays_naissance"]=htmlspecialchars($pays_naissance, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["nationalite_code"]=$nationalite_code;
			$_SESSION["nationalite"]=htmlspecialchars($nationalite, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["telephone"]=htmlspecialchars($telephone, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["telephone_portable"]=htmlspecialchars($telephone_portable, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["adresse_1"]=htmlspecialchars($adresse_1, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["adresse_2"]=htmlspecialchars($adresse_2, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["adresse_3"]=htmlspecialchars($adresse_3, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["adresse_cp"]=$adr_cp;
			$_SESSION["adresse_ville"]=$adr_ville;
			$_SESSION["adresse_pays_code"]=$adr_pays_code;
			$_SESSION["adresse_pays"]=htmlspecialchars($adr_pays, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["numero_ine"]=htmlspecialchars($numero_ine, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["email"]=htmlspecialchars($email, ENT_QUOTES, $default_htmlspecialchars_encoding);
			$_SESSION["derniere_connexion"]=$connexion;
			$_SESSION["cursus_en_cours"]=$cursus_en_cours;
			$_SESSION["deja_inscrit"]=$deja_inscrit;
			$_SESSION["annee_premiere_inscr"]=$annee_premiere_inscr;
			$_SESSION["annee_bac"]=$annee_bac;
			$_SESSION["serie_bac"]=$serie_bac;
			$_SESSION["nom_serie_bac"]=$nom_serie_bac;

			$_SESSION["onglet"]=0; // onglet par défaut : doc

			// Mise à jour de la date de dernière connexion et du couple IP / HOST
			$date_cnx=time();

			$last_ip=$_SERVER["REMOTE_ADDR"];
			$last_host=&gethostbyaddr($_SERVER['REMOTE_ADDR']);
			$last_user_agent=$_SERVER["HTTP_USER_AGENT"];

         // Niveau supplémentaire dans l'arborescence des messages
         $_SESSION["MSG_SOUS_REP"]=sous_rep_msg($_SESSION["authentifie"]);

			if($browser=&get_browser(null, true))
			{
				if(isset($browser["parent"]) && isset($browser["platform"]) && isset($browser["browser"]) && isset($browser["version"]) && isset($browser["css"]))
					$last_user_agent="$browser[parent] - $browser[platform] - $browser[browser] $browser[version] / CSS : $browser[css]";
			}

			db_query($dbr,"UPDATE $_DB_candidat SET $_DBU_candidat_connexion='$date_cnx',
																 $_DBU_candidat_derniere_ip='$last_ip',
																 $_DBU_candidat_dernier_host='$last_host',
																 $_DBU_candidat_dernier_user_agent='$last_user_agent'
								WHERE $_DBU_candidat_id='$candidat_id'");

			// création du vecteur d'encryption (utilisé pour crypter les paramètres)
/*
			$td=mcrypt_module_open("tripledes", "", "cbc", "");
			$_SESSION["iv"]=generate_pass();
			mcrypt_module_close($td);
*/

         $td=mcrypt_module_open("tripledes", "", "cbc", "");
         $_SESSION["iv"]=mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
         mcrypt_module_close($td);

			// Historique : log de la connexion dans la BDD
			write_evt($dbr, $__EVT_ID_LOGIN, "Connexion réussie", $candidat_id, $candidat_id);

			// Chargement des plugins
			if(function_exists("add_modules"))
				add_modules();

			if(isset($_SESSION["comp_id"]) && ctype_digit($_SESSION["comp_id"])
				&& db_num_rows($result=db_query($dbr, "SELECT  $_DBC_composantes_nom, $_DBC_universites_nom, 
																			  $_DBC_universites_img_dir, $_DBC_universites_id,
																			  $_DBC_universites_css, $_DBC_composantes_courriel_scol, $_DBC_composantes_limite_cand_nombre,
																			  $_DBC_composantes_limite_cand_annee, $_DBC_composantes_limite_cand_annee_mention,
																			  $_DBC_composantes_affichage_decisions
																		FROM $_DB_composantes, $_DB_universites
																	WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
																	AND $_DBC_composantes_id='$_SESSION[comp_id]'")))
			{
				list($_SESSION["composante"],
						$_SESSION["universite"],
						$_SESSION["img_dir"],
						$_SESSION["univ_id"],
						$_SESSION["css"],
						$_SESSION["courriel_scol"],
						$_SESSION["limite_nombre"],
						$_SESSION["limite_annee"],
						$_SESSION["limite_annee_mention"],
						$_SESSION["affichage_decisions"])=db_fetch_row($result, 0);

				db_free_result($result);

				// Présence d'une lettre d'information (non vide) ?
				if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_comp_infos_encadre WHERE $_DBC_comp_infos_encadre_info_id
															IN (SELECT $_DBC_comp_infos_id FROM $_DB_comp_infos 
																 WHERE $_DBC_comp_infos_comp_id='$_SESSION[comp_id]')")))
					$location="info_comp.php";
				elseif(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_comp_infos_para WHERE $_DBC_comp_infos_para_info_id
																IN (SELECT $_DBC_comp_infos_id FROM $_DB_comp_infos
																	 WHERE $_DBC_comp_infos_comp_id='$_SESSION[comp_id]')")))
					$location="info_comp.php";
				elseif(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_comp_infos_fichiers WHERE $_DBC_comp_infos_fichiers_info_id
                                                IN (SELECT $_DBC_comp_infos_id FROM $_DB_comp_infos
																	 WHERE $_DBC_comp_infos_comp_id='$_SESSION[comp_id]')")))
					$location="info_comp.php";
				else
					$location="precandidatures.php";

				db_close($dbr);

				session_write_close();
				// header("Location:$location");
				header("Location:" . base_url($php_self) . "$location");
				exit();
			}
			else
			{
				db_close($dbr);

				session_write_close();
				// header("Location:composantes.php");
				header("Location:" . base_url($php_self) . "composantes.php");
				exit();
			}
		}
		else
		{
			db_free_result($result);

			$time_erreur=date_fr("j F Y, H:i");

			// On écrit dans le champ "last_error" le code entré par le candidat et la date de cette erreur (à des fins de vérification coté admin)
			// Si l'identifiant n'existe pas, ça ne changera rien
			db_query($dbr, "UPDATE $_DB_candidat SET $_DBU_candidat_derniere_erreur_code='$time_erreur - [$code_personnel]'
								 WHERE $_DBU_candidat_identifiant LIKE '$identifiant'");

			$identification_incorrecte=1;
			unset($code_personnel);

			// Historique : log de l'échec dans la BDD
			$identifiant=stripslashes(preg_replace("/'/","''",preg_replace("/[\\\]*/","",$identifiant)));
			write_evt($dbr, $__EVT_ID_LOGIN, "Connexion échouée - $identifiant");
			$identifiant=preg_replace("/[']+/","'",$identifiant);
		}
	}
	if(isset($_SESSION["conditions_ok"]) || ((isset($_POST["Continuer"]) || isset($_POST["Continuer_x"])) && (isset($_POST['conditions']) && $_POST['conditions']==1)))
		$_SESSION["conditions_ok"]=1;
	else
	{
		// on nettoie la session et on retourne à l'index
		session_unset();
		session_destroy();
		session_write_close();

		// header("Location:../index.php?conditions=0");
		header("Location:" . base_url($php_self) . "../index.php?conditions=0");
		exit();
	}

	en_tete_candidat();
	menu_sup_simple();
?>

<div class='main' style='padding-top:20px; padding-bottom:20px;'>
	<?php
		titre_page_icone("Identification", "password_32x32_fond.png", 15, "C");

		$prev_periode=$__PERIODE-1 . "-$__PERIODE";

		message("Si vous avez déjà rempli une fiche sur cette interface l'année passée, vous pouvez réutiliser vos anciens identifiants", $__INFO);
/*
		if(!isset($_SESSION["interface_ouverte"]) || $_SESSION["interface_ouverte"]==0)
			message("<center>
							L'enregistrement n'est pas encore possible : aucune session de candidatures n'est actuellement en cours.
							<br>Merci de consulter les dates <a href='../doc/limites.php' class='lien_bleu_12' style='vertical-align:top;'><b>sur cette page</b></a>.
						</center>", $__WARNING);
*/
	?>

	<div style='text-align:center; padding-bottom:10px;'>
		<font class='Texte3'>Veuillez saisir votre identifiant et votre code personnel :</font>
	</div>

	<form name='form1' action="<?php print("$php_self"); ?>" method="POST">

	<table border="0" align='center' valign="top">
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>Identifiant : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input name="identifiant" value="<?php if(isset($identifiant)) echo htmlspecialchars($identifiant,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>" autocomplete="off" type="text" size=20 maxlength='30'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>Code personnel :</font>
		</td>
		<td class='td-droite fond_menu'>
			<input name="code_personnel" value="" autocomplete="off" type="password" size=20 maxlength='15'>
		</td>
	</tr>
	</table>
	<br>

	<?php
		if(isset($identification_incorrecte))
			message("Couple Identifiant / Code personnel incorrect !", $__ERREUR);
	?>

	<div class='centered_box'>
		<input type="image" src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Valider" name="valider1" value="Valider">
	</div>
	
	<table cellpadding='0' border='0' align='center'>
   <tr>
      <td align='center' nowrap='true' valign='middle' style='padding-right:20px'>
         <a href='enregistrement.php' class='lien2'>Vous n'avez pas encore d'identifiant,<br />cliquez ici pour vous enregistrer</a>
      </td>
      <td align='center' nowrap='true' valign='middle' style='padding-left:20px'>
         <a href='recuperation_identifiants.php' class="lien2">Vous avez perdu vos identifiants, <br />cliquez ici pour une demande de renvoi</a>
      </td>
   </tr>
   </table>

	</form>

	<?php
/*
		// TODO : Maintenance : à scripter autrement

		$heures=date("H", time());
		$minutes=date("i", time());

		if($minutes)
			$heures_restantes=12-$heures-1;
		else
			$heures_restantes=12-$heures;

		$minutes_restantes=60-$minutes;

		if($heures_restantes)
			$temps_restant="$heures_restantes heures $minutes_restantes minutes";
		else
			$temps_restant="$minutes_restantes minutes";

		print("<center>
					<font class='Texte_important'>
						<b>Maintenance programmée aujourd'hui de 12h à 13h30 (temps restant : $temps_restant)</b>
						<br>Ce service sera indisponible pendant la durée de la maintenance mais vos données ne seront pas perdues.
						<br>Merci pour votre compréhension</b>
					</font>
				</center>\n");
*/
		db_close($dbr);
	?>
</div>
<?php
	pied_de_page_candidat();
?>

<script language="javascript">
	document.form1.identifiant.focus()
</script>

</body>
</html>

