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

  if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
  {
    // vérification des valeurs entrées dans le formulaire
    // TODO : vérifications poussées

    $civilite=$_POST["civilite"];

    $nom=mb_strtoupper(trim($_POST["nom"]));
    $nom_naissance=mb_strtoupper(trim($_POST["nom_naissance"]));
    
      if($nom_naissance=="")
         $nom_naissance=$nom;
      
      $prenom=ucwords(strtolower((trim($_POST["prenom"]))));
    $prenom2=ucwords(strtolower((trim($_POST["prenom2"]))));

    $jour=trim($_POST["jour"]);
    $mois=trim($_POST["mois"]);
    $annee=trim($_POST["annee"]);

    $adresse_1=trim($_POST["adresse_1"]);
    $adresse_2=trim($_POST["adresse_2"]);
    $adresse_3=trim($_POST["adresse_3"]);
    $adr_cp=$_POST["adr_cp"];
    $adr_ville=$_POST["adr_ville"];
    $adr_pays_code=$_POST["adr_pays"];

    $lieu_naissance=ucwords(strtolower(trim($_POST["lieu_naissance"])));
    $dpt_naissance=trim($_POST["dpt_naissance"]);
    $pays_naissance_code=$_POST["pays_naissance"];

    $email=mb_strtolower(trim($_POST["email"]));
    $emailconf=mb_strtolower(trim($_POST["emailconf"]));

    if(strcmp($email, $emailconf)) // si les 2 adresses sont différentes ...
      $email_inegaux=1;

    $telephone=trim($_POST["telephone"]);
    $telephone_portable=trim($_POST["telephone_portable"]);

    $nationalite_code=$_POST["nationalite"];

    $deja_inscrit=trim($_POST["deja_inscrit"]);

    if($deja_inscrit!="0" && $deja_inscrit!="1")
      $err_deja_inscrit="1";

    $premiere_inscr=$_POST["premiere_inscr"];

    if($deja_inscrit==0)
      $premiere_inscr="";
    elseif(!ctype_digit($premiere_inscr) || strlen($premiere_inscr)!=4 || $premiere_inscr<1900) // || $premiere_inscr>"$__PERIODE")
      $err_premiere_inscr=1;

    $serie_bac=$_POST["serie_bac"];

    if($serie_bac=="")
      $err_serie_bac=1;

    // Ajouter le cas "sans bac"
    $baccalaureat=$_POST["baccalaureat"];

    if(!ctype_digit($baccalaureat) || strlen($baccalaureat)!=4 || $baccalaureat<1900) // || $baccalaureat>"$__PERIODE")
      $err_annee_bac=1;

    $num_ine=str_replace(" ", "", $_POST["num_ine"]);

    if($num_ine!="" && check_ine_bea($num_ine))
      $erreur_ine_bea=1;
      
      if($deja_inscrit==1 && $num_ine=="") 
         $erreur_ine_obligatoire=1;

    $champs_obligatoires=array($nom,$prenom,$jour,$mois,$annee,$lieu_naissance,$pays_naissance_code,$adresse_1,$email,$emailconf,$nationalite_code,$adr_cp,$adr_ville,$adr_pays_code,$baccalaureat,$serie_bac,$deja_inscrit);
    $cnt_obl=count($champs_obligatoires);

    for($i=0; $i<$cnt_obl; $i++) // vérification des champs obligatoires
    {
      if($champs_obligatoires[$i]=="")
      {
        $champ_vide=1;
        $i=$cnt_obl;
      }
    }

    if($pays_naissance_code=="FR" && $dpt_naissance!="2A" && $dpt_naissance!="2B" && (!ctype_digit($dpt_naissance) || $dpt_naissance<1 || ($dpt_naissance>95 && ($dpt_naissance<971 || $dpt_naissance>987))))
      $bad_dpt_naissance=1;

    if(!ctype_digit($jour) || !ctype_digit($mois) || !ctype_digit($annee) || $annee>=date('Y'))
      $erreur_date_naissance=1;
    else
      $date_naissance=MakeTime(12,0,0,$mois,$jour,$annee); // heure : midi (pour éviter les problèmes de décallages horaires)

    // Vérification d'unicité si (nom/prenom/date de naissance) a changé
    // TODO : vérifier si ces critères sont suffisants

    if(!isset($erreur_date_naissance) && (strtolower($_SESSION["nom"])!=strtolower($nom) || strtolower($_SESSION["prenom"])!=strtolower($prenom) || strtolower($_SESSION["naissance"])!=strtolower($date_naissance)))
    {
      $result=db_query($dbr,"SELECT $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance
                        FROM $_DB_candidat
                      WHERE $_DBC_candidat_nom ILIKE '$nom'
                      AND $_DBC_candidat_prenom ILIKE '$prenom'
                      AND $_DBC_candidat_date_naissance='$date_naissance'
                      AND $_DBC_candidat_id!='$candidat_id'");
      $rows=db_num_rows($result);
  
      if($rows)
        $id_existe=1;

      db_free_result($result);
    }

    if(!isset($champ_vide) && !isset($id_existe) && !isset($email_inegaux) && !isset($bad_dpt_naissance) && !isset($erreur_date_naissance)
         && !isset($erreur_ine_bea) && !isset($err_deja_inscrit) && !isset($err_premiere_inscr) && !isset($err_serie_bac) && !isset($err_annee_bac)
         && !isset($erreur_ine_obligatoire))
    {
      // Les données du nouvel utilisateur sont complètes (pas forcément bonnes, mais ça le pénalisera)
      // On peut créer l'identifiant et le code, l'insérer dans la base et envoyer le mail

      $requete="UPDATE $_DB_candidat SET $_DBU_candidat_civilite='$civilite',
                              $_DBU_candidat_nom='$nom',
                              $_DBU_candidat_nom_naissance='$nom_naissance',
                              $_DBU_candidat_prenom='$prenom',
                              $_DBU_candidat_prenom2='$prenom',
                              $_DBU_candidat_date_naissance='$date_naissance',
                              $_DBU_candidat_nationalite='$nationalite_code',
                              $_DBU_candidat_telephone='$telephone',
                              $_DBU_candidat_telephone_portable='$telephone_portable',
                              $_DBU_candidat_adresse_1='$adresse_1',
                              $_DBU_candidat_adresse_2='$adresse_2',
                              $_DBU_candidat_adresse_3='$adresse_3',
                              $_DBU_candidat_numero_ine='$num_ine',
                              $_DBU_candidat_email='$email',
                              $_DBU_candidat_lieu_naissance='$lieu_naissance',
                              $_DBU_candidat_pays_naissance='$pays_naissance_code',
                              $_DBU_candidat_adresse_cp='$adr_cp',
                              $_DBU_candidat_adresse_ville='$adr_ville',
                              $_DBU_candidat_adresse_pays='$adr_pays_code',
                              $_DBU_candidat_dpt_naissance='$dpt_naissance',
                              $_DBU_candidat_deja_inscrit='$deja_inscrit',
                              $_DBU_candidat_annee_premiere_inscr='$premiere_inscr',
                              $_DBU_candidat_annee_bac='$baccalaureat',
                              $_DBU_candidat_serie_bac='$serie_bac'
                          WHERE $_DBU_candidat_id='$candidat_id';";

      // Requête : ici, on n'utilise pas la variable "$requete" précédente car on ne peut pas "l'échapper" correctement
      // (les ' sont doublés partout, c'est ok pour les valeurs, mais pas pour leur délimitation ...)
      db_query($dbr, "UPDATE $_DB_candidat SET $_DBU_candidat_civilite='$civilite',
                              $_DBU_candidat_nom='$nom',
                              $_DBU_candidat_nom_naissance='$nom_naissance',
                              $_DBU_candidat_prenom='$prenom',
                              $_DBU_candidat_prenom2='$prenom2',
                              $_DBU_candidat_date_naissance='$date_naissance',
                              $_DBU_candidat_nationalite='$nationalite_code',
                              $_DBU_candidat_telephone='$telephone',
                              $_DBU_candidat_telephone_portable='$telephone_portable',
                              $_DBU_candidat_adresse_1='$adresse_1',
                              $_DBU_candidat_adresse_2='$adresse_2',
                              $_DBU_candidat_adresse_3='$adresse_3',
                              $_DBU_candidat_numero_ine='$num_ine',
                              $_DBU_candidat_email='$email',
                              $_DBU_candidat_lieu_naissance='$lieu_naissance',
                              $_DBU_candidat_pays_naissance='$pays_naissance_code',
                              $_DBU_candidat_adresse_cp='$adr_cp',
                              $_DBU_candidat_adresse_ville='$adr_ville',
                              $_DBU_candidat_adresse_pays='$adr_pays_code',
                              $_DBU_candidat_dpt_naissance='$dpt_naissance',
                              $_DBU_candidat_deja_inscrit='$deja_inscrit',
                              $_DBU_candidat_annee_premiere_inscr='$premiere_inscr',
                              $_DBU_candidat_annee_bac='$baccalaureat',
                              $_DBU_candidat_serie_bac='$serie_bac'                             
                          WHERE $_DBU_candidat_id='$candidat_id';");

      write_evt("", $__EVT_ID_C_ID, "MAJ Identité", $candidat_id, $candidat_id, preg_replace("/[']+/","''", stripslashes($requete)));

      db_close($dbr);

      if(array_key_exists($pays_naissance_code, $_SESSION["liste_pays_nat_iso"]))
        $_SESSION["pays_naissance"]=htmlspecialchars(stripslashes($_SESSION["liste_pays_nat_iso"]["$pays_naissance_code"]["pays"]), ENT_QUOTES, $default_htmlspecialchars_encoding);
      else
        $_SESSION["pays_naissance"]="";

      if(array_key_exists($adr_pays_code, $_SESSION["liste_pays_nat_iso"]))
        $_SESSION["adresse_pays"]=htmlspecialchars(stripslashes($_SESSION["liste_pays_nat_iso"]["$adr_pays_code"]["pays"]), ENT_QUOTES, $default_htmlspecialchars_encoding);
      else
        $_SESSION["adresse_pays"]="";

      if(array_key_exists($nationalite_code, $_SESSION["liste_pays_nat_iso"]))
        $_SESSION["nationalite"]=htmlspecialchars(stripslashes($_SESSION["liste_pays_nat_iso"]["$nationalite_code"]["nationalite"]), ENT_QUOTES, $default_htmlspecialchars_encoding);
      else
        $_SESSION["nationalite"]="";


      $_SESSION["nom"]=htmlspecialchars(stripslashes($nom), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["nom_naissance"]=htmlspecialchars(stripslashes($nom_naissance), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["civilite"]=htmlspecialchars(stripslashes($civilite), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["prenom"]=htmlspecialchars(stripslashes($prenom), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["prenom2"]=htmlspecialchars(stripslashes($prenom2), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["naissance"]=htmlspecialchars(stripslashes($date_naissance), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["lieu_naissance"]=htmlspecialchars(stripslashes($lieu_naissance), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["dpt_naissance"]=$dpt_naissance;
      $_SESSION["nom_departement"]=isset($_SESSION["liste_departements"]) && array_key_exists("$dpt_naissance", $_SESSION["liste_departements"]) ? $_SESSION["liste_departements"]["$dpt_naissance"]: "";
      $_SESSION["pays_naissance_code"]=$pays_naissance_code;
      $_SESSION["nationalite_code"]=$nationalite_code;
      $_SESSION["telephone"]=htmlspecialchars(stripslashes($telephone), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["telephone_portable"]=htmlspecialchars(stripslashes($telephone_portable), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["adresse_1"]=htmlspecialchars(stripslashes($adresse_1), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["adresse_2"]=htmlspecialchars(stripslashes($adresse_2), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["adresse_3"]=htmlspecialchars(stripslashes($adresse_3), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["adresse_cp"]=$adr_cp;
      $_SESSION["adresse_ville"]=htmlspecialchars(stripslashes($adr_ville), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["adresse_pays_code"]=$adr_pays_code;      
      $_SESSION["numero_ine"]=htmlspecialchars(stripslashes($num_ine), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["email"]=htmlspecialchars(stripslashes($email), ENT_QUOTES, $default_htmlspecialchars_encoding);
      $_SESSION["deja_inscrit"]=$deja_inscrit;
      $_SESSION["annee_premiere_inscr"]=$premiere_inscr;
      $_SESSION["annee_bac"]=$baccalaureat;
      $_SESSION["serie_bac"]=$serie_bac;

      if(isset($serie_bac) && isset($_SESSION["intitules_series_bac"]["$serie_bac"]))
        $_SESSION["nom_serie_bac"]=$_SESSION["intitules_series_bac"]["$serie_bac"];
      else
        $_SESSION["nom_serie_bac"]="";

      if(isset($dpt_naissance) && isset($_SESSION["liste_departements"]["$dpt_naissance"]))
        $_SESSION["nom_departement"]=$_SESSION["liste_departements"]["$dpt_naissance"];
      else
        $_SESSION["nom_departement"]="";

      session_write_close();
      header("Location:precandidatures.php?sed=1"); // sed = "Succès Edition Candidat"
      exit();
    }
  }
  else
  {
    $cur_annee=date_fr("Y", $_SESSION["naissance"]);
    $cur_mois=date_fr("m", $_SESSION["naissance"]);
    $cur_jour=date_fr("d", $_SESSION["naissance"]);
  }
  
  // Construction de la liste des pays et nationalités (codes ISO) pour son utilisation dans le formulaire
  $_SESSION["liste_pays_nat_iso"]=array();
  
  $res_pays_nat=db_query($dbr, "SELECT $_DBC_pays_nat_ii_iso, $_DBC_pays_nat_ii_insee, $_DBC_pays_nat_ii_pays, $_DBC_pays_nat_ii_nat
                      FROM $_DB_pays_nat_ii
                      ORDER BY unaccent($_DBC_pays_nat_ii_pays)");
                      
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
  
  en_tete_candidat();
  menu_sup_candidat($__MENU_FICHE);
?>

<div class='main'>
  <?php
    titre_page_icone("Modification de vos données personnelles", "contacts_32x32_fond.png", 30, "L");

    $message_erreur="";

    if(isset($email_inegaux))
      $message_erreur.="- les deux adresses électroniques ne correspondent pas";

    if(isset($bad_dpt_naissance))
    {
      $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
      $message_erreur.="- si vous êtes né(e) en France, le département de naissance est obligatoire";
    }

    if(isset($erreur_date_naissance))
    {
      $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
      $message_erreur.="- le format de votre date de naissance est incorrect (JJ / MM / AAAA)";
    }

    if(isset($erreur_ine_bea))
    {
      $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
      $message_erreur.="- le numero INE ou BEA est incorrect";
    }
    
    if(isset($erreur_ine_obligatoire))
      {
         $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
         $message_erreur.="- vous avez indiqué avoir déjà été inscrit(e) dans cette Université : le numero INE ou BEA est <strong>obligatoire</strong>";
      }

    if(isset($err_deja_inscrit))
    {
      $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
      $message_erreur.="- vous devez indiquer si vous avez déjà été inscrit(e) ou non dans cette Université";
    }

    if(isset($err_premiere_inscr) && $err_premiere_inscr=="1")
    {
      $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
      $message_erreur.="- le format de l'année de première inscription dans cette Université est incorrect";
    }

    if(isset($err_annee_bac) && $err_annee_bac=="1")
    {
      $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
      $message_erreur.="- le format de l'année d'obtention du baccalauréat est incorrect";
    }

    if(isset($err_serie_bac))
    {
      $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
      $message_erreur.="- vous devez sélectionner la série de votre baccalauréat (ou équivalence). Si vous n'avez pas obtenu le baccalauréat, sélectionnez \"Sans bac\" dans le menu déroulant.";
    }

    if($message_erreur!="")
    {
      $message_erreur="<strong>Erreur(s)</strong> :\n<br>$message_erreur";
      message("$message_erreur", $__ERREUR);
    }

    if(isset($success))
      message("Informations mises à jour avec succès", $__SUCCES);

    if(isset($champ_vide))
      message("Formulaire incomplet: les champs en gras sont <u>obligatoires</u>", $__ERREUR);
    elseif(isset($id_existe))
      message("<center>Erreur : les nouvelles données correspondent à une entrée déjà existante dans la base</center>
            <br>Si vous pensez qu'il s'agit d'une autre personne ayant les mêmes nom, prénom et date de naissance, merci <a href='mailto:$__EMAIL_SUPPORT' class='lien2a'>d'envoyer un mail à cette adresse</a> avec toutes les données du formulaire.", $__ERREUR);
  ?>

  <form action="<?php print("$php_self"); ?>" method="POST">

  <?php
    message("<font class='Texte_menu'>Les champs <strong>en gras</strong> sont <strong><u>obligatoires</u></strong>", $__WARNING);
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
        $civ=$_SESSION["civilite"];

      if($civ=="M")
      {
        $selected_M="selected='1'";
        $etudiant="étudiant";
        $selected_Mlle="";
        $selected_Mme="";
      }
      else
      {
        $etudiant="étudiante";

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
      <input type='text' name='nom' value='<?php if(isset($nom)) echo htmlspecialchars(stripslashes($nom), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars(stripslashes($_SESSION["nom"]), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="30">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'>Nom de naissance (si différent) :</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='nom_naissance' value='<?php if(isset($nom_naissance)) echo htmlspecialchars(stripslashes($nom_naissance), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars(stripslashes($_SESSION["nom_naissance"]), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="30">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Prénom : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='prenom' value='<?php if(isset($prenom)) echo htmlspecialchars(stripslashes($prenom), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars(stripslashes($_SESSION["prenom"]), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="30">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'>Deuxième prénom : </font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='prenom2' value='<?php if(isset($prenom2)) echo htmlspecialchars(stripslashes($prenom2), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars(stripslashes($_SESSION["prenom2"]), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="30">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Date de naissance (JJ/MM/AAAA) : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='jour' value='<?php if(isset($jour)) echo htmlspecialchars($jour,ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars($cur_jour,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="2" maxlength="2">/
      <input type='text' name='mois' value='<?php if(isset($mois)) echo htmlspecialchars($mois,ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars($cur_mois,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="2" maxlength="2">/
      <input type='text' name='annee' value='<?php if(isset($annee)) echo htmlspecialchars($annee,ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars($cur_annee,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="4" maxlength="4">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Ville de naissance : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='lieu_naissance' value='<?php if(isset($lieu_naissance)) echo htmlspecialchars(stripslashes($lieu_naissance), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars(stripslashes($_SESSION["lieu_naissance"]), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="60">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Si vous êtes né(e) en France, merci d'indiquer<br>votre N° de département de naissance: </b></font>
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

            $_SESSION["liste_departements"]["$dpt_num"]="$dpt_nom";

            $selected=(isset($dpt_naissance) && $dpt_naissance==$dpt_num) || (isset($_SESSION["dpt_naissance"]) && $_SESSION["dpt_naissance"]==$dpt_num) ? "selected='1'" : "";
            
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
              $selected=(isset($pays_naissance_code) && $pays_naissance_code==$code_iso) || (isset($_SESSION["pays_naissance_code"]) && $_SESSION["pays_naissance_code"]==$code_iso) ? "selected='1'" : "";
              
              print("<option value='$code_iso' $selected>$array_pays_nat[pays]</option>\n");
            }
          }
        ?>
      </select>
      <!-- <input type='text' name='pays_naissance' value='<?php if(isset($pays_naissance)) echo htmlspecialchars(stripslashes($pays_naissance), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo stripslashes($_SESSION["pays_naissance"]); ?>' size="25" maxlength="60"> -->
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
              $selected=(isset($nationalite_code) && $nationalite_code==$code_iso) || (isset($_SESSION["nationalite_code"]) && $_SESSION["nationalite_code"]==$code_iso) ? "selected='1'" : "";
              
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
    <td class='td-droite fond_menu2'>
      <input type='text' name='email' value='<?php if(isset($email)) echo htmlspecialchars(stripslashes($email), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars($_SESSION["email"],ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="40" maxlength="255">
      &nbsp;&nbsp;<font class='Texte_menu'><b><u>Une seule adresse</u> dans ce champ.</b></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Veuillez confirmer l'adresse électronique : </b></font>
    </td>
    <td class='td-droite fond_menu2'>
      <input type='text' name='emailconf' value='<?php if(isset($emailconf)) echo htmlspecialchars(stripslashes($emailconf), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars($_SESSION["email"],ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="40" maxlength="255">
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
         <input name='adresse_1' value="<?php if(isset($adresse_1)) echo htmlspecialchars(stripslashes($adresse_1), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars(stripslashes($_SESSION["adresse_1"]), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse (suite) : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_2' value="<?php if(isset($adresse_2)) echo htmlspecialchars(stripslashes($adresse_2), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars(stripslashes($_SESSION["adresse_2"]), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse (suite) : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_3' value="<?php if(isset($adresse_3)) echo htmlspecialchars(stripslashes($adresse_3), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars(stripslashes($_SESSION["adresse_3"]), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>" size='40' maxlength="30">
      </td>
   </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Code Postal :</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='adr_cp' value='<?php if(isset($adr_cp)) echo htmlspecialchars(stripslashes($adr_cp), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars(stripslashes($_SESSION["adresse_cp"]), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="15">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Ville :</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='adr_ville' value='<?php if(isset($adr_ville)) echo htmlspecialchars(stripslashes($adr_ville), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars(stripslashes($_SESSION["adresse_ville"]), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="60">
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
              $selected=(isset($adr_pays_code) && $adr_pays_code==$code_iso) || (isset($_SESSION["adresse_pays_code"]) && $_SESSION["adresse_pays_code"]==$code_iso) ? "selected='1'" : "";
              
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
      <font class='Texte_menu2' style="font-size:14px">
        <strong>Votre baccalauréat : précisions (indépendantes du menu "2-Cursus")</strong>
      </font>
      <font class='Texte_menu2'>
        <br><i>Si vous n'avez pas le baccalauréat (et que vous ne le préparez pas cette année), sélectionnez "Sans bac" dans<br>la liste et indiquez l'année du dernier diplôme obtenu</i>
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'>
        <b>Année d'obtention du baccalauréat (ou équivalent) ?</b>      
      </font>
    </td>
    <td class='td-droite fond_menu'>
      <?php
        if(!isset($baccalaureat))
          $baccalaureat=$_SESSION["annee_bac"];
      ?>
      <input type='text' name='baccalaureat' value='<?php if(isset($baccalaureat)) echo trim("$baccalaureat"); ?>' size="25" maxlength="4"><font class='Texte'><i>(Format : YYYY)</i></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Série de votre baccalauréat :</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <select name='serie_bac' size='1'>
        <option value=''></option>
        <?php
          $_SESSION["intitules_series_bac"]=array();

          if(!isset($serie_bac))
            $serie_bac=$_SESSION["serie_bac"];

          $result=db_query($dbr,"SELECT $_DBC_diplomes_bac_code, $_DBC_diplomes_bac_intitule
                        FROM $_DB_diplomes_bac ORDER BY $_DBC_diplomes_bac_intitule");
          $rows=db_num_rows($result);

          if(isset($serie_bac))
            $cur_serie_bac=$serie_bac;

          for($i=0; $i<$rows; $i++)
          {
            list($serie_bac, $intitule_bac)=db_fetch_row($result,$i);

            $_SESSION["intitules_series_bac"]["$serie_bac"]="$intitule_bac";

            $selected=isset($cur_serie_bac) && $cur_serie_bac==$serie_bac ? "selected=1" : "";

            print("<option value='$serie_bac' $selected>$intitule_bac</option>\n");
          }
        ?>
      </select>
    </td>
  </tr>
  <tr>
    <td colspan='' class='fond_page' style='height:10px;'></td>
  </tr>
  <tr>
    <td class='td-complet fond_menu2' colspan='2'>
      <font class='Texte_menu2' style="font-size:14px"><strong>Inscriptions antérieures</strong></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Avez vous déjà été inscrit(e) dans cette Université ?</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <?php
        if(!isset($deja_inscrit))
          $deja_inscrit=$_SESSION["deja_inscrit"];

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
        
        if(isset($__REMARQUE_FUSION_TEMPORAIRE))
          print("$__REMARQUE_FUSION_TEMPORAIRE");
      ?>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Si oui, indiquez l'année de première inscription :</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <?php
        if(!isset($premiere_inscr))
          $premiere_inscr=$_SESSION["annee_premiere_inscr"];
      ?>
      <input type='text' name='premiere_inscr' value='<?php if(isset($premiere_inscr)) echo trim("$premiere_inscr"); ?>' size="25" maxlength="4"><font class='Texte'><i>(Format : YYYY)</i></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'>Numéro INE <b>ou</b> BEA : </font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='num_ine' value='<?php if(isset($num_ine)) echo htmlspecialchars(stripslashes($num_ine), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo htmlspecialchars($_SESSION["numero_ine"],ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="11"> <font class='Texte_menu'>(<b>obligatoire</b> en cas d'inscription antérieure dans cette Université)</font>
    </td>
  </tr>
  <tr>
      <td colspan='' class='fond_page' style='height:10px;'></td>
   </tr>
  <tr>
      <td class='td-complet fond_menu2' colspan='2'>
         <font class='Texte_menu2' style="font-size:14px"><strong>Autres informations</strong></font>
      </td>
   </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'>Numéro de téléphone fixe : </font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='telephone' value='<?php if(isset($telephone)) echo htmlspecialchars(stripslashes($telephone), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo stripslashes($_SESSION["telephone"]); ?>' size="25" maxlength="15">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'>Numéro de téléphone portable : </font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='telephone_portable' value='<?php if(isset($telephone_portable)) echo htmlspecialchars(stripslashes($telephone_portable), ENT_QUOTES, $default_htmlspecialchars_encoding); else echo stripslashes($_SESSION["telephone_portable"]); ?>' size="25" maxlength="15">
    </td>
  </tr>
  </table>

  <div class='centered_icons_box'>
    <a href='precandidatures.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
    </form>
  </div>
  
</div>
<?php
  db_close($dbr);

  pied_de_page_candidat();
?>
</body>
</html>

