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

	// Condition : la fiche doit être verrouillée
	// TODO : désactivé, à vérifier ou redéfinir
/*
	if((!isset($_SESSION["tab_candidat"]["lock"]) || $_SESSION["tab_candidat"]["lock"]!=1) && $_SESSION["tab_candidat"]["manuelle"]!=1)
	{
		header("Location:edit_candidature.php");
		exit;
	}
*/

	// identifiant de l'étudiant
	$candidat_id=$_SESSION["candidat_id"];

	$dbr=db_connect();

	// Seconde condition : on doit avoir le verrouillage exclusif
	$res=cand_lock($dbr, $candidat_id);

	if($res>0)
	{
		db_close($dbr);
		header("Location:fiche_verrouillee.php");
		exit;
	}
	elseif($res==-1)
	{
		db_close($dbr);
		header("Location:edit_candidature.php");
		exit;
	}

	if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
	{
		// vérification des valeurs entrées dans le formulaire
		// TODO : vérifications poussées

		$civilite=$_POST["civilite"];

		$nom=stripslashes(str_replace("'","''", mb_strtoupper(trim($_POST["nom"]))));
		$nom_naissance=stripslashes(str_replace("'","''", mb_strtoupper(trim($_POST["nom_naissance"]))));
		
		if($nom_naissance=="")
		   $nom_naissance=$nom; 
		
		$prenom=stripslashes(str_replace("'","''", ucwords(strtolower(trim($_POST["prenom"])))));
		$prenom2=stripslashes(str_replace("'","''", ucwords(strtolower(trim($_POST["prenom2"])))));

		$jour=trim($_POST["jour"]);
		$mois=trim($_POST["mois"]);
		$annee=trim($_POST["annee"]);

		$adresse_1=stripslashes(str_replace("'","''", trim($_POST["adresse_1"])));
		$adresse_2=stripslashes(str_replace("'","''", trim($_POST["adresse_2"])));
		$adresse_3=stripslashes(str_replace("'","''", trim($_POST["adresse_3"])));
		$adr_cp=stripslashes(str_replace("'","''", $_POST["adr_cp"]));
		$adr_ville=stripslashes(str_replace("'","''", $_POST["adr_ville"]));
		$adr_pays_code=$_POST["adr_pays"];

		$lieu_naissance=stripslashes(str_replace("'","''", ucwords(strtolower(trim($_POST["lieu_naissance"])))));
		$dpt_naissance=$_POST["dpt_naissance"];
		$pays_naissance_code=$_POST["pays_naissance"];

		$email=trim($_POST["email"]);
		$telephone=trim($_POST["telephone"]);
		$telephone_portable=trim($_POST["telephone_portable"]);

		$nationalite_code=$_POST["nationalite"];

		$deja_inscrit=trim($_POST["deja_inscrit"]);

		if($deja_inscrit!="0" && $deja_inscrit!="1")
			$err_deja_inscrit="1";

		$annee_premiere_inscr=$_POST["annee_premiere_inscr"];

		if($deja_inscrit==0)
			$annee_premiere_inscr="";
		elseif(!ctype_digit($annee_premiere_inscr) || strlen($annee_premiere_inscr)!=4 || $annee_premiere_inscr<1900 || $annee_premiere_inscr>"$__PERIODE")
			$err_annee_premiere_inscr=1;

		$serie_bac=$_POST["serie_bac"];

		// Ajouter le cas "sans bac"
		$annee_bac=$_POST["annee_bac"];

		// L'année et la série du bac ne sont pas obligatoires pour la partie gestion, mais on avertit l'utilisateur
		if($serie_bac=="")
			$warn_serie_bac="wsb=1";

		if($annee_bac=="")
			$warn_annee_bac="wab=1";
		elseif(!ctype_digit($annee_bac) || strlen($annee_bac)!=4 || $annee_bac<1900 || $annee_bac>"$__PERIODE")
			$err_annee_bac=1;

		$num_ine=trim($_POST["num_ine"]);

		if($num_ine!="" && check_ine_bea($num_ine))
			$erreur_ine_bea=1;
			
		if($deja_inscrit==1 && $num_ine=="")
         $erreur_ine_obligatoire=1;

		$champs_obligatoires=array($nom,$prenom,$jour,$mois,$annee,$lieu_naissance,$pays_naissance_code,$adresse,$nationalite_code, $adr_cp, $adr_ville, $adr_pays_code);

		if($_SESSION["tab_candidat"]["manuelle"]!=1)
			array_push($champs_obligatoires, $email);

		$cnt_obl=count($champs_obligatoires);

		for($i=0; $i<$cnt_obl; $i++) // vérification des champs obligatoires
		{
			if($champs_obligatoires[$i]=="")
			{
				$champ_vide=1;
				$i=$cnt_obl;
			}
		}

		// Le département de naissance est obligatoire pour ceux nés en France
		if($pays_naissance_code=="FR" && $dpt_naissance!="2A" && $dpt_naissance!="2B" && (!ctype_digit($dpt_naissance) || $dpt_naissance<1 || ($dpt_naissance>95 && ($dpt_naissance<971 || $dpt_naissance>987))))
			$bad_dpt_naissance=1;

		if(!ctype_digit($mois) || $mois<=0 || $mois >12 || !ctype_digit($jour) || $jour<=0 || $jour > 31 || !ctype_digit($annee) || $annee<=0 || $annee>=date('Y'))
			$erreur_date_naissance=1;
		else
		{
			$date_naissance=MakeTime(12,0,0,$mois,$jour,$annee);

			// Vérification d'unicité si (nom/prenom/date de naissance) a changé
			// TODO : vérifier si ces critères sont suffisants

			if(strtolower($_SESSION['tab_candidat']["nom"])!=strtolower($nom) || strtolower($_SESSION['tab_candidat']["prenom"])!=strtolower($prenom) || strtolower($_SESSION['tab_candidat']["naissance"])!=strtolower($date_naissance))
			{
				$result=db_query($dbr,"SELECT $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance FROM $_DB_candidat
												WHERE $_DBC_candidat_nom ILIKE '$nom'
												AND 	$_DBC_candidat_prenom ILIKE '$prenom'
												AND 	$_DBC_candidat_date_naissance='$date_naissance'
												AND	$_DBC_candidat_id!='$candidat_id'");

				$rows=db_num_rows($result);
		
				if($rows)
					$id_existe=1;

				db_free_result($result);
			}
		}

		if(!isset($champ_vide) && !isset($id_existe) && !isset($erreur_date_naissance) && !isset($bad_dpt_naissance) && !isset($erreur_ine_bea)
			&& !isset($err_deja_inscrit) && !isset($err_annee_premiere_inscr) && !isset($err_serie_bac) && !isset($err_annee_bac) && !isset($erreur_ine_obligatoire))
		{
			// Les données du nouvel utilisateur sont complètes (pas forcément bonnes, mais ça le pénalisera)
			// On peut créer l'identifiant et le code, l'insérer dans la base et envoyer le mail

			// Requete stockée dans une variable pour pouvoir l'enregistrer dans l'historique
			$req="UPDATE $_DB_candidat SET $_DBU_candidat_civilite='$civilite',
													 $_DBU_candidat_nom='$nom',
													 $_DBU_candidat_nom_naissance='$nom_naissance',
													 $_DBU_candidat_prenom='$prenom',
													 $_DBU_candidat_prenom2='$prenom2',
													 $_DBU_candidat_date_naissance='$date_naissance',
													 $_DBU_candidat_lieu_naissance='$lieu_naissance',
													 $_DBU_candidat_dpt_naissance='$dpt_naissance',
													 $_DBU_candidat_pays_naissance='$pays_naissance_code',
													 $_DBU_candidat_nationalite='$nationalite_code',
													 $_DBU_candidat_telephone='$telephone',
													 $_DBU_candidat_telephone_portable='$telephone_portable',
													 $_DBU_candidat_adresse_1='$adresse_1',
                                        $_DBU_candidat_adresse_2='$adresse_2',
                                        $_DBU_candidat_adresse_3='$adresse_3',
                                        $_DBU_candidat_adresse_cp='$adr_cp',
													 $_DBU_candidat_adresse_ville='$adr_ville',
													 $_DBU_candidat_adresse_pays='$adr_pays_code',
													 $_DBU_candidat_numero_ine='$num_ine',
													 $_DBU_candidat_email='$email',
													 $_DBU_candidat_deja_inscrit='$deja_inscrit',
													 $_DBU_candidat_annee_premiere_inscr='$annee_premiere_inscr',
													 $_DBU_candidat_annee_bac='$annee_bac',
													 $_DBU_candidat_serie_bac='$serie_bac'
											 WHERE $_DBU_candidat_id='$candidat_id';";

			// Requête : on n'utilise pas la variable "$req" car on ne peut pas "l'échapper" correctement (les ' sont doublés
			// partout, c'est ok pour les valeurs, mais pas pour leur délimitation ...
			db_query($dbr, "UPDATE $_DB_candidat SET $_DBU_candidat_civilite='$civilite',
													 $_DBU_candidat_nom='$nom',
													 $_DBU_candidat_nom_naissance='$nom_naissance',
													 $_DBU_candidat_prenom='$prenom',
													 $_DBU_candidat_prenom2='$prenom2',
													 $_DBU_candidat_date_naissance='$date_naissance',
                                        $_DBU_candidat_lieu_naissance='$lieu_naissance',
													 $_DBU_candidat_dpt_naissance='$dpt_naissance',
													 $_DBU_candidat_pays_naissance='$pays_naissance_code',
													 $_DBU_candidat_nationalite='$nationalite_code',
													 $_DBU_candidat_telephone='$telephone',
													 $_DBU_candidat_telephone_portable='$telephone_portable',
													 $_DBU_candidat_adresse_1='$adresse_1',
                                        $_DBU_candidat_adresse_2='$adresse_2',
                                        $_DBU_candidat_adresse_3='$adresse_3',
													 $_DBU_candidat_adresse_cp='$adr_cp',
													 $_DBU_candidat_adresse_ville='$adr_ville',
													 $_DBU_candidat_adresse_pays='$adr_pays_code',
													 $_DBU_candidat_numero_ine='$num_ine',
													 $_DBU_candidat_email='$email',
													 $_DBU_candidat_deja_inscrit='$deja_inscrit',
													 $_DBU_candidat_annee_premiere_inscr='$annee_premiere_inscr',
													 $_DBU_candidat_annee_bac='$annee_bac',
													 $_DBU_candidat_serie_bac='$serie_bac'
											 WHERE $_DBU_candidat_id='$candidat_id';");

			write_evt($dbr, $__EVT_ID_G_ID, "Identité modifiée", $candidat_id, $candidat_id, stripslashes($req));

			db_close($dbr);

			if(array_key_exists($pays_naissance_code, $_SESSION["liste_pays_nat_iso"]))
				$_SESSION['tab_candidat']["pays_naissance"]=htmlspecialchars(stripslashes($_SESSION["liste_pays_nat_iso"]["$pays_naissance_code"]["pays"]), ENT_QUOTES);
			else
				$_SESSION['tab_candidat']["pays_naissance"]="";

			if(array_key_exists($adr_pays_code, $_SESSION["liste_pays_nat_iso"]))
				$_SESSION['tab_candidat']["adresse_pays"]=htmlspecialchars(stripslashes($_SESSION["liste_pays_nat_iso"]["$adr_pays_code"]["pays"]), ENT_QUOTES);
			else
				$_SESSION['tab_candidat']["adresse_pays"]="";

			if(array_key_exists($nationalite_code, $_SESSION["liste_pays_nat_iso"]))
				$_SESSION['tab_candidat']["nationalite"]=htmlspecialchars(stripslashes($_SESSION["liste_pays_nat_iso"]["$nationalite_code"]["nationalite"]), ENT_QUOTES);
			else
				$_SESSION['tab_candidat']["nationalite"]="";

			$_SESSION['tab_candidat']["nom"]=htmlspecialchars($nom, ENT_QUOTES);
			$_SESSION['tab_candidat']["nom_naissance"]=htmlspecialchars($nom_naissance, ENT_QUOTES);
			$_SESSION['tab_candidat']["civilite"]=htmlspecialchars($civilite, ENT_QUOTES);
			$_SESSION['tab_candidat']["prenom"]=htmlspecialchars($prenom, ENT_QUOTES);
			$_SESSION['tab_candidat']["prenom2"]=htmlspecialchars($prenom2, ENT_QUOTES);
			$_SESSION['tab_candidat']["naissance"]=htmlspecialchars($date_naissance, ENT_QUOTES);
			$_SESSION['tab_candidat']["txt_naissance"]=date_fr("j F Y",$date_naissance);
			$_SESSION['tab_candidat']["lieu_naissance"]=htmlspecialchars($lieu_naissance, ENT_QUOTES);
			$_SESSION['tab_candidat']["dpt_naissance"]=$dpt_naissance;
			$_SESSION['tab_candidat']["pays_naissance_code"]=$pays_naissance_code;
			$_SESSION['tab_candidat']["nationalite_code"]=$nationalite_code;
			$_SESSION['tab_candidat']["telephone"]=htmlspecialchars($telephone, ENT_QUOTES);
			$_SESSION['tab_candidat']["telephone_portable"]=htmlspecialchars($telephone_portable, ENT_QUOTES);
			$_SESSION['tab_candidat']["adresse_1"]=htmlspecialchars($adresse_1, ENT_QUOTES);
			$_SESSION['tab_candidat']["adresse_2"]=htmlspecialchars($adresse_2, ENT_QUOTES);
			$_SESSION['tab_candidat']["adresse_3"]=htmlspecialchars($adresse_3, ENT_QUOTES);
			$_SESSION['tab_candidat']["adresse_pays_code"]=$adr_pays_code;
			$_SESSION['tab_candidat']["numero_ine"]=htmlspecialchars($num_ine, ENT_QUOTES);
			$_SESSION['tab_candidat']["email"]=htmlspecialchars($email, ENT_QUOTES);
			$_SESSION['tab_candidat']["deja_inscrit"]=$deja_inscrit;
			$_SESSION['tab_candidat']["annee_premiere_inscr"]=$annee_premiere_inscr;
			$_SESSION['tab_candidat']["annee_bac"]=$annee_bac;
			$_SESSION['tab_candidat']["serie_bac"]=$serie_bac;

         $_SESSION['tab_candidat']["adresse"]=$_SESSION['tab_candidat']["adresse_1"];
         $_SESSION['tab_candidat']["adresse"].=$_SESSION['tab_candidat']["adresse_2"]!="" ? "\n".$_SESSION['tab_candidat']["adresse_2"] : "";
         $_SESSION['tab_candidat']["adresse"].=$_SESSION['tab_candidat']["adresse_3"]!="" ? "\n".$_SESSION['tab_candidat']["adresse_3"] : "";
         

			if($dpt_naissance!="" && isset($_SESSION["liste_departements"]["$dpt_naissance"]))
				$_SESSION['tab_candidat']["nom_departement"]=$_SESSION["liste_departements"]["$dpt_naissance"];
			else
				$_SESSION['tab_candidat']["nom_departement"]="";

			// Série du bac
			if($serie_bac!="" && isset($_SESSION["intitules_series_bac"]["$serie_bac"]))
				$_SESSION['tab_candidat']["nom_serie_bac"]=$_SESSION["intitules_series_bac"]["$serie_bac"];
			else
				$_SESSION['tab_candidat']["nom_serie_bac"]="";

			$args="succes=1";

			if(isset($warn_annee_bac))
				$args.="&$warn_annee_bac";

			if(isset($warn_serie_bac))
				$args.="&$warn_serie_bac";

			header("Location:edit_candidature.php?$args");
			exit();
		}
	}
	else
	{
		$cur_annee=date_fr("Y", $_SESSION['tab_candidat']['naissance']);
		$cur_mois=date_fr("m", $_SESSION['tab_candidat']['naissance']);
		$cur_jour=date_fr("d", $_SESSION['tab_candidat']['naissance']);
	}
	
	// Construction de la liste des pays et nationalités (codes ISO) pour son utilisation dans le formulaire
	$_SESSION["liste_pays_nat_iso"]=array();
	
	$res_pays_nat=db_query($dbr, "SELECT $_DBC_pays_nat_ii_iso, $_DBC_pays_nat_ii_insee, $_DBC_pays_nat_ii_pays, $_DBC_pays_nat_ii_nat
											FROM $_DB_pays_nat_ii
											ORDER BY to_ascii($_DBC_pays_nat_ii_pays)");
											
	$rows_pays_nat=db_num_rows($res_pays_nat);
	
	for($p=0; $p<$rows_pays_nat; $p++)
	{
		list($code_iso, $code_insee, $table_pays, $table_nationalite)=db_fetch_row($res_pays_nat, $p);
		
		// Construction uniquement si le code insee est présent (pour les exports APOGEE ou autres)
		if($code_insee!="")
			$_SESSION["liste_pays_nat_iso"]["$code_iso"]=array("pays" => "$table_pays", "nationalite" => $table_nationalite);
/*		
		if($code_insee!="")
			$_SESSION["liste_pays_nat_insee"]["$code_insee"]=array("pays" => "$table_pays", "nationalite" => $table_nationalite);
*/
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		print("<div class='infos_candidat Texte'>
					<strong>" . $_SESSION["tab_candidat"]["etudiant"] ." : " . $_SESSION["tab_candidat"]["civ_texte"] . " " . $_SESSION["tab_candidat"]["nom"] . " " . $_SESSION["tab_candidat"]["prenom"] .", " . $_SESSION["tab_candidat"]["ne_le"] . " " . $_SESSION["tab_candidat"]["txt_naissance"] ."</strong>
				 </div>

				 <form action='$php_self' method='POST' name='form1'>");

		titre_page_icone("Identité du candidat", "contacts_32x32_fond.png", 15, "L");

		if(isset($success))
			message("Informations mises à jour avec succès", $__SUCCES);

		$message_erreur="";

		if(isset($bad_dpt_naissance))
			$message_erreur.="- si le candidat / la candidate est né(e) en France, le département de naissance est obligatoire";

		if(isset($erreur_date_naissance))
		{
			$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
			$message_erreur.="- le format de la date de naissance est incorrect (JJ / MM / AAAA)";
		}

		if(isset($erreur_ine_bea))
		{
			$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
			$message_erreur.="- le numero INE ou BEA est incorrect";
		}

      if(isset($erreur_ine_obligatoire))
      {
         $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
         $message_erreur.="- Si l'étudiant(e) déjà été inscrit(e) dans cette Université : le numero INE ou BEA est <strong>obligatoire</strong>";
      }

		if(isset($err_deja_inscrit))
		{
			$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
			$message_erreur.="- vous devez indiquer si le candidat / la candidate a déjà été inscrit(e) ou non dans cette Université";
		}

		if(isset($err_annee_premiere_inscr))
		{
			$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
			$message_erreur.="- le format de l'année de première inscription dans cette Université est incorrect (AAAA)";
		}

		if(isset($err_annee_bac))
		{
			$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
			$message_erreur.="- le format de l'année d'obtention du baccalauréat est incorrect (AAAA)";
		}
/*
		if(isset($err_serie_bac))
		{
			$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
			$message_erreur.="- vous devez sélectionner la série du baccalauréat (ou équivalence). Si le candidat / la candidate n'a pas obtenu le baccalauréat, sélectionnez \"Sans bac\" dans le menu déroulant.";
		}
*/
		if($message_erreur!="")
		{
 			$message_erreur="<strong>Erreur(s)</strong> :\n<br>$message_erreur";
			message("$message_erreur", $__ERREUR);
		}

		if(isset($champ_vide))
			message("Formulaire incomplet: les champs en gras sont <u>obligatoires</u>", $__ERREUR);
		elseif(isset($id_existe))
			message("<center>Erreur : les nouvelles données correspondent à une entrée déjà existante dans la base</center>
						<br>Si vous pensez qu'il s'agit d'une autre personne ayant les mêmes nom, prénom et date de naissance, merci <a href='mailto:$__EMAIL_SUPPORT' class='lien2a'>d'envoyer un mail à cette adresse</a> avec toutes les données du formulaire.", $__ERREUR);
		else
			message("Rappel : les champs en gras sont <u>obligatoires</u>", $__WARNING);
	?>

	<table align='center'>
	<tr>
		<td class='td-complet fond_menu2' colspan='2'>
			<font class='Texte_menu2' style="font-size:14px"><strong>Identité</strong></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Civilité : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
			if(isset($civilite))
				$civ=$civilite;
			else
				$civ=$_SESSION['tab_candidat']["civilite"];

			if($civ=="M")
			{
				$selected_M="selected='1'";
				$selected_Mlle="";
				$selected_Mme="";
			}
			else
			{
				if($civ=="Mme")
				{
					$selected_Mme="selected='1'";
					$selected_M="";
					$selected_Mlle="";
				}
				else
				{
					$selected_Mlle="selected='1'";
					$selected_M="";
					$selected_Mme="";
				}
			}

			print("
			<select name='civilite' size='1'>
				<option value='Mme' $selected_Mme>Madame</option>
				<option value='Mlle' $selected_Mlle>Mademoiselle</option>
				<option value='M' $selected_M>Monsieur</option>
			</select>");
			?>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Nom usuel : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='nom' value='<?php if(isset($nom)) echo htmlspecialchars(stripslashes($nom), ENT_QUOTES); else echo htmlspecialchars(stripslashes($_SESSION['tab_candidat']["nom"]),ENT_QUOTES) ?>' size="25" maxlength="30">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Nom de naissance (si différent) : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='nom_naissance' value='<?php if(isset($nom_naissance)) echo htmlspecialchars(stripslashes($nom_naissance), ENT_QUOTES); else echo htmlspecialchars(stripslashes($_SESSION['tab_candidat']["nom_naissance"]),ENT_QUOTES) ?>' size="25" maxlength="30">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Prénom : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='prenom' value='<?php if(isset($prenom)) echo htmlspecialchars(stripslashes($prenom), ENT_QUOTES); else echo htmlspecialchars(stripslashes($_SESSION['tab_candidat']["prenom"]),ENT_QUOTES); ?>' size="25" maxlength="30">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Deuxième prénom (facultatif) : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='prenom2' value='<?php if(isset($prenom2)) echo htmlspecialchars(stripslashes($prenom2), ENT_QUOTES); else echo htmlspecialchars(stripslashes($_SESSION['tab_candidat']["prenom2"]),ENT_QUOTES); ?>' size="25" maxlength="30">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Date de naissance (jour/mois/annee) : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='jour' value='<?php if(isset($jour)) echo htmlspecialchars($jour,ENT_QUOTES); else echo htmlspecialchars($cur_jour,ENT_QUOTES); ?>' size="2" maxlength="2">/
			<input type='text' name='mois' value='<?php if(isset($mois)) echo htmlspecialchars($mois,ENT_QUOTES); else echo htmlspecialchars($cur_mois,ENT_QUOTES); ?>' size="2" maxlength="2">/
			<input type='text' name='annee' value='<?php if(isset($annee)) echo htmlspecialchars($annee,ENT_QUOTES); else echo htmlspecialchars($cur_annee,ENT_QUOTES); ?>' size="4" maxlength="4">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Ville de naissance : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='lieu_naissance' value='<?php if(isset($lieu_naissance)) echo htmlspecialchars(stripslashes($lieu_naissance),ENT_QUOTES); else echo htmlspecialchars(stripslashes($_SESSION['tab_candidat']["lieu_naissance"]),ENT_QUOTES); ?>' size="25" maxlength="60">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Si né(e) en France<br>N° du département de naissance: </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='dpt_naissance'>
				<option value=''></option>
				<?php
					$_SESSION["liste_departements"]=array();

					$res_departements=db_query($dbr, "SELECT $_DBC_departements_fr_numero, $_DBC_departements_fr_nom
																 FROM $_DB_departements_fr
																 ORDER BY $_DBC_departements_fr_numero");

					$nb_dpts_fr=db_num_rows($res_departements);

					for($dpt=0; $dpt<$nb_dpts_fr; $dpt++)
					{
						list($dpt_num, $dpt_nom)=db_fetch_row($res_departements, $dpt);

						$_SESSION["liste_departements"]["$dpt_num"]=$dpt_nom;

						$selected=(isset($dpt_naissance) && $dpt_naissance==$dpt_num) || (isset($_SESSION["tab_candidat"]["dpt_naissance"]) && $_SESSION["tab_candidat"]["dpt_naissance"]==$dpt_num) ? "selected='1'" : "";
						
						print("<option value='$dpt_num' $selected>$dpt_num - $dpt_nom</option>\n");
					}

					db_free_result($res_departements);
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Pays de naissance : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='pays_naissance' size='1'>
			 	<option value=''></option>
				<?php
					foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
					{
						if($array_pays_nat["pays"]!="")
						{
							$selected=(isset($pays_naissance_code) && $pays_naissance_code==$code_iso) || (isset($_SESSION["tab_candidat"]["pays_naissance_code"]) &&	$_SESSION["tab_candidat"]["pays_naissance_code"]==$code_iso) ? "selected='1'" : "";
							
							print("<option value='$code_iso' $selected>$array_pays_nat[pays]</option>\n");
						}
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Nationalité : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='nationalite' size='1'>
			 	<option value=''></option>
				<?php
					foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
					{
						if($array_pays_nat["nationalite"]!="")
						{
							$selected=(isset($nationalite_code) && $nationalite_code==$code_iso) || (isset($_SESSION["tab_candidat"]["nationalite_code"]) && $_SESSION["tab_candidat"]["nationalite_code"]==$code_iso) ? "selected='1'" : "";
							
							print("<option value='$code_iso' $selected>$array_pays_nat[nationalite]</option>\n");
						}
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Adresse électronique valide (<i>email</i>) : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='email' value='<?php if(isset($email)) echo htmlspecialchars(stripslashes($email),ENT_QUOTES); else echo htmlspecialchars($_SESSION['tab_candidat']["email"],ENT_QUOTES); ?>' size="25" maxlength="255"> <font class='Texte_menu'><i>(Facultative dans le cas d'une fiche manuelle)</i></font>
		</td>
	</tr>
	<tr>
		<td colspan='2' style='height:10px;'></td>
	</tr>
	<tr>
		<td class='td-complet fond_menu2' colspan='2'>
			<font class='Texte_menu2' style="font-size:14px"><strong>Adresse postale pour la réception des courriers</strong></font>
		</td>
	</tr>
	<tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_1' value="<?php if(isset($adresse_1)) echo htmlspecialchars(stripslashes($adresse_1), ENT_QUOTES); else echo htmlspecialchars(stripslashes($_SESSION['tab_candidat']["adresse_1"]), ENT_QUOTES); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse (suite) : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_2' value="<?php if(isset($adresse_2)) echo htmlspecialchars(stripslashes($adresse_2), ENT_QUOTES); else echo htmlspecialchars(stripslashes($_SESSION['tab_candidat']["adresse_2"]), ENT_QUOTES); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse (suite) : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_3' value="<?php if(isset($adresse_3)) echo htmlspecialchars(stripslashes($adresse_3), ENT_QUOTES); else echo htmlspecialchars(stripslashes($_SESSION['tab_candidat']["adresse_3"]), ENT_QUOTES); ?>" size='40' maxlength="30">
      </td>
   </tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Code Postal :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='adr_cp' value='<?php if(isset($adr_cp)) echo htmlspecialchars(stripslashes($adr_cp),ENT_QUOTES); else echo htmlspecialchars(stripslashes($_SESSION['tab_candidat']["adresse_cp"]), ENT_QUOTES); ?>' size="25" maxlength="15">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Ville :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='adr_ville' value='<?php if(isset($adr_ville)) echo htmlspecialchars(stripslashes($adr_ville),ENT_QUOTES); else echo htmlspecialchars(stripslashes($_SESSION['tab_candidat']["adresse_ville"]), ENT_QUOTES); ?>' size="25" maxlength="60">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Pays :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='adr_pays' size='1'>
			 	<option value=''></option>
				<?php
					foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
					{
						if($array_pays_nat["pays"]!="")
						{
							$selected=(isset($adr_pays_code) && $adr_pays_code==$code_iso) || (isset($_SESSION["tab_candidat"]["adresse_pays_code"]) && $_SESSION["tab_candidat"]["adresse_pays_code"]==$code_iso) ? "selected='1'" : "";
							
							print("<option value='$code_iso' $selected>$array_pays_nat[pays]</option>\n");
						}
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan='2' style='height:10px;'></td>
	</tr>
	<tr>
		<td class='td-complet fond_menu2' colspan='2'>
			<font class='Texte_menu2' style="font-size:14px"><strong>Baccalauréat (ou équivalent) : précisions</strong></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'>
				<b>Année d'obtention du baccalauréat<br>(ou équivalent) ?</b>
			</font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='annee_bac' value='<?php if(isset($annee_bac)) echo "$annee_bac"; elseif(isset($_SESSION["tab_candidat"]["annee_bac"])) echo $_SESSION["tab_candidat"]["annee_bac"]; ?>' size="25" maxlength="4"><font class='Texte'><i>(Format : YYYY)</i></font>
			<br><font class='Texte_menu_10'><i>Si le candidat n'a pas le baccalauréat (et qu'il n'est pas en cours de préparation), sélectionnez "Sans bac" dans<br>la liste et indiquez l'année du dernier diplôme obtenu</i></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Série du baccalauréat (ou équivalent) :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='serie_bac' size='1'>
				<option value=''></option>
				<?php
					$_SESSION["intitules_series_bac"]=array();

					$result=db_query($dbr,"SELECT $_DBC_diplomes_bac_code, $_DBC_diplomes_bac_intitule
												FROM $_DB_diplomes_bac ORDER BY $_DBC_diplomes_bac_intitule");
					$rows=db_num_rows($result);

					if(isset($serie_bac))
						$cur_serie_bac=$serie_bac;

					for($i=0; $i<$rows; $i++)
					{
						list($serie_bac, $intitule_bac)=db_fetch_row($result,$i);

						$_SESSION["intitules_series_bac"]["$serie_bac"]=$intitule_bac;

						$selected=(isset($cur_serie_bac) && $cur_serie_bac==$serie_bac) || (isset($_SESSION["tab_candidat"]["serie_bac"]) && $_SESSION["tab_candidat"]["serie_bac"]==$serie_bac) ? "selected=1" : "";

						print("<option value='$serie_bac' $selected>$intitule_bac</option>\n");
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan='2' style='height:10px;'></td>
	</tr>
	<tr>
		<td class='td-complet fond_menu2' colspan='2'>
			<font class='Texte_menu2' style="font-size:14px"><strong>Inscriptions antérieures</strong></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Le candidat/la candidate a-t'il/elle déjà été inscrit(e) dans cette Université ?</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
				if(!isset($deja_inscrit) && isset($_SESSION["tab_candidat"]["deja_inscrit"]))
					$deja_inscrit=$_SESSION["tab_candidat"]["deja_inscrit"];

				if(isset($deja_inscrit) && $deja_inscrit==1)
				{
					$oui_checked="checked";
					$non_checked="";
				}
				else
				{
					$oui_checked="";
					$non_checked="checked";
				}

				print("<input type='radio' name='deja_inscrit' value='1' $oui_checked><font class='Texte_menu'>&nbsp;Oui&nbsp;&nbsp;</font><input type='radio' name='deja_inscrit' value='0' $non_checked><font class='Texte'>&nbsp;Non</font>\n");
			?>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b><u>Si oui</u>, indiquez l'année de première inscription :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='annee_premiere_inscr' value='<?php if(isset($annee_premiere_inscr)) echo "$annee_premiere_inscr"; elseif(isset($_SESSION["tab_candidat"]["annee_premiere_inscr"])) echo $_SESSION["tab_candidat"]["annee_premiere_inscr"]; ?>' size="25" maxlength="4"><font class='Texte'><i>(Format : YYYY)</i></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu'>Numéro INE <b>ou</b> BEA : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='num_ine' value='<?php if(isset($num_ine)) echo htmlspecialchars($num_ine,ENT_QUOTES); else echo htmlspecialchars($_SESSION['tab_candidat']["numero_ine"],ENT_QUOTES); ?>' size="25" maxlength="11"> <font class='Texte_menu'>(<b>obligatoire</b> si vous avez déjà été inscrit(e) dans cette Université)</font>
		</td>
	</tr>
	<tr>
		<td colspan='2' style='height:10px;'></td>
	</tr>
	<tr>
		<td class='td-complet fond_menu2' colspan='2'>
			<font class='Texte_menu2' style="font-size:14px"><strong>Autres informations</strong></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'>Téléphone fixe : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='telephone' value='<?php if(isset($telephone)) echo htmlspecialchars($telephone,ENT_QUOTES); else echo htmlspecialchars($_SESSION['tab_candidat']["telephone"],ENT_QUOTES); ?>' size="25" maxlength="15">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'>Téléphone portable : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='telephone_portable' value='<?php if(isset($telephone_portable)) echo htmlspecialchars($telephone_portable,ENT_QUOTES); else echo htmlspecialchars($_SESSION['tab_candidat']["telephone_portable"],ENT_QUOTES); ?>' size="25" maxlength="15">
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='edit_candidature.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
		</form>
	</div>

</div>
<?php
	db_close($dbr);
	pied_de_page();
?>
</body>
</html>

