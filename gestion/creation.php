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

  $dbr=db_connect();

  // Déverrouillage, au cas où
  if(isset($_SESSION["candidat_id"]))
    cand_unlock($dbr, $_SESSION["candidat_id"]);

  if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
  {
    // vérification des valeurs entrées dans le formulaire
    // TODO : vérifications poussées ?
    $civilite=$_POST["civilite"];

    // $nom=mb_strtoupper(trim($_POST["nom"]));
    $nom=str_replace("'","''", str_replace("\\","", (mb_strtoupper(trim($_POST["nom"]), "UTF-8"))));
    $nom_naissance=str_replace("'","''", str_replace("\\","", (mb_strtoupper(trim($_POST["nom_naissance"]), "UTF-8"))));
    
    if($nom_naissance=="")
       $nom_naissance=$nom;
    
    // $prenom=ucwords(strtolower(trim($_POST["prenom"])));
    // $deuxieme_prenom=ucwords(strtolower(trim($_POST["prenom2"])));
    $prenom=str_replace("'","''", str_replace("\\","", (ucwords(mb_strtolower(trim($_POST["prenom"]), "UTF-8")))));
    // $deuxieme_prenom=ucwords(strtolower(trim($_POST["prenom2"])));
    $deuxieme_prenom=str_replace("'","''", str_replace("\\","", (ucwords(mb_strtolower(trim($_POST["prenom2"]), "UTF-8")))));

    $jour=trim($_POST["jour"]);
    $mois=trim($_POST["mois"]);
    $annee=trim($_POST["annee"]);

    $adresse_1=str_replace("'","''", str_replace("\\","", (mb_strtolower(trim($_POST["adresse_1"]), "UTF-8"))));
    $adresse_2=str_replace("'","''", str_replace("\\","", (mb_strtolower(trim($_POST["adresse_2"]), "UTF-8"))));
    $adresse_3=str_replace("'","''", str_replace("\\","", (mb_strtolower(trim($_POST["adresse_3"]), "UTF-8"))));
    $adr_cp=str_replace("'","''", str_replace("\\","", ($_POST["adr_cp"])));
    $adr_ville=str_replace("'","''", str_replace("\\","", ($_POST["adr_ville"])));
    $adr_pays_code=$_POST["adr_pays"];      

    $lieu_naissance=str_replace("'","''", str_replace("\\","", (ucwords(mb_strtolower(trim($_POST["lieu_naissance"]), "UTF-8")))));
    $dpt_naissance=$_POST["dpt_naissance"];
    $pays_naissance_code=$_POST["pays_naissance"];

    $email=trim($_POST["email"]);
    $telephone=trim($_POST["telephone"]);
    $telephone_portable=trim($_POST["telephone_portable"]);

    $nationalite_code=$_POST["nationalite"];

    $deja_inscrit=$_POST["deja_inscrit"];

    if($deja_inscrit!="0" && $deja_inscrit!="1")
      $err_deja_inscrit="1";

      $num_ine=str_replace(" ", "", $_POST["num_ine"]);

    if($num_ine!="" && check_ine_bea($num_ine))
      $erreur_ine_bea=1;

      if($deja_inscrit==1 && $num_ine=="")
         $erreur_ine_obligatoire=1;

    $annee_premiere_inscr=$_POST["annee_premiere_inscr"];

    if($deja_inscrit==0)
      $annee_premiere_inscr="";
    elseif(!ctype_digit($annee_premiere_inscr) || strlen($annee_premiere_inscr)!=4 || $annee_premiere_inscr<1900 || $annee_premiere_inscr>"$__PERIODE")
      $err_annee_premiere_inscr=1;

    $serie_bac=$_POST["serie_bac"];

    if($serie_bac=="")
      $err_serie_bac=1;

    // Ajouter le cas "sans bac"
    $annee_bac=$_POST["annee_bac"];

    if(!ctype_digit($annee_bac) || strlen($annee_bac)!=4 || $annee_bac<1900 || $annee_bac>"$__PERIODE")
      $err_annee_bac=1;
  
    $champs_obligatoires=array($nom,$prenom,$jour,$mois,$annee,$lieu_naissance,$pays_naissance_code,$adresse_1,$nationalite_code, $adr_cp, $adr_ville, $adr_pays_code,$annee_bac,$serie_bac,$deja_inscrit);
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
      $date_naissance=MakeTime(12,0,0,$mois,$jour,$annee); // heure : midi (pour éviter les problèmes de décallages horaires)

      // Vérification d'unicité - On se base sur le nom, le prénom et la date de naissance
      // TODO : vérifier si ces critères sont suffisants

      $result=db_query($dbr,"SELECT $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance
                                FROM $_DB_candidat
                             WHERE $_DBC_candidat_nom ILIKE '".preg_replace("/[']+/", "''", stripslashes($nom))."'
                             AND $_DBC_candidat_prenom ILIKE '".preg_replace("/[']+/", "''", stripslashes($prenom))."'
                             AND $_DBC_candidat_date_naissance='$date_naissance'");
      $rows=db_num_rows($result);

      if($rows)
        $id_existe=1;

      db_free_result($result);
    }

    if(!isset($champ_vide) && !isset($id_existe) && !isset($erreur_date_naissance) && !isset($erreur_ine_bea) && !isset($err_deja_inscrit)
      && !isset($err_annee_premiere_inscr) && !isset($err_serie_bac) && !isset($err_annee_bac) && !isset($erreur_ine_obligatoire))
    {
      // Les données du nouvel utilisateur sont complètes (pas forcément bonnes, mais ça le pénalisera)
      // On peut créer l'identifiant et le code, l'insérer dans la base et envoyer le mail

      // Création de l'identifiant
      $new_identifiant=str_replace(" ","",mb_strtolower($nom, "UTF-8")); // base de l'identifiant
      $new_identifiant=str_replace("-","",$new_identifiant);

      // initialisation de la boucle
      $prenom2=mb_strtolower(str_replace(" ","",$prenom));
      $nb_lettres_prenom=1;
      $iteration=0;
      $len_prenom=strlen($prenom);

      while(db_num_rows(db_query($dbr,"SELECT $_DBC_candidat_id FROM $_DB_candidat WHERE $_DBC_candidat_identifiant='$new_identifiant'")))
      {
        if($nb_lettres_prenom<=$len_prenom) // si on peut encore utiliser le prénom
        {
          $new_identifiant= substr($prenom2,0,$nb_lettres_prenom) . "." . mb_strtolower($nom, "UTF-8");
          $nb_lettres_prenom++;
        }
        else
        {
          $iteration++;
          $new_identifiant=$prenom2 . "." . mb_strtolower($nom, "UTF-8") . $iteration;
        }
      }

      // génération du Code Personnel
      srand((double)microtime()*1000000);
      $code_conf=mb_strtoupper(md5(rand(0,9999)), "UTF-8");
      $new_code=substr($code_conf, 17, 8);

      $fiche_manuelle=1;
      $candidat_lock=$candidat_lockdate=$derniere_connexion=$cursus_en_cours=0;
         $derniere_ip=$dernier_host=$dernier_user_agent=$derniere_erreur_code="";
         
      $new_id=db_locked_query($dbr, $_DB_candidat, "INSERT INTO $_DB_candidat (
          $_DBU_candidat_id,
          $_DBU_candidat_civilite, 
          $_DBU_candidat_nom, 
          $_DBU_candidat_nom_naissance, 
          $_DBU_candidat_prenom, 
          $_DBU_candidat_prenom2, 
          $_DBU_candidat_date_naissance, 
          $_DBU_candidat_lieu_naissance,
          $_DBU_candidat_dpt_naissance, 
          $_DBU_candidat_pays_naissance, 
          $_DBU_candidat_nationalite, 
          $_DBU_candidat_telephone, 
          $_DBU_candidat_telephone_portable, 
          $_DBU_candidat_adresse_1, 
          $_DBU_candidat_adresse_2, 
          $_DBU_candidat_adresse_3, 
          $_DBU_candidat_adresse_cp, 
          $_DBU_candidat_adresse_ville, 
          $_DBU_candidat_adresse_pays, 
          $_DBU_candidat_numero_ine, 
          $_DBU_candidat_email, 
          $_DBU_candidat_identifiant, 
          $_DBU_candidat_code_acces, 
          $_DBU_candidat_connexion, 
          $_DBU_candidat_derniere_ip, 
          $_DBU_candidat_dernier_host, 
          $_DBU_candidat_dernier_user_agent, 
          $_DBU_candidat_derniere_erreur_code, 
          $_DBU_candidat_manuelle, 
          $_DBU_candidat_cursus_en_cours, 
          $_DBU_candidat_lock, 
          $_DBU_candidat_lockdate, 
          $_DBU_candidat_deja_inscrit, 
          $_DBU_candidat_annee_premiere_inscr, 
          $_DBU_candidat_annee_bac,
          $_DBU_candidat_serie_bac)
          
      VALUES(
          '##NEW_ID##',
         '$civilite',
         '".preg_replace("/[']+/", "''", stripslashes($nom))."',
         '".preg_replace("/[']+/", "''", stripslashes($nom_naissance))."',
         '".preg_replace("/[']+/", "''", stripslashes($prenom))."',
         '".preg_replace("/[']+/", "''", stripslashes($deuxieme_prenom))."',
         '$date_naissance',
         '".preg_replace("/[']+/", "''", stripslashes($lieu_naissance))."',
         '".preg_replace("/[']+/", "''", stripslashes($dpt_naissance))."',
         '$pays_naissance_code',
         '$nationalite_code',
         '".preg_replace("/[']+/", "''", stripslashes($telephone))."',
         '".preg_replace("/[']+/", "''", stripslashes($telephone_portable))."',
         '".preg_replace("/[']+/", "''", stripslashes($adresse_1))."',
         '".preg_replace("/[']+/", "''", stripslashes($adresse_2))."',
         '".preg_replace("/[']+/", "''", stripslashes($adresse_3))."',
         '".preg_replace("/[']+/", "''", stripslashes($adr_cp))."',
         '".preg_replace("/[']+/", "''", stripslashes($adr_ville))."',
         '$adr_pays_code',
         '$num_ine',
         '".preg_replace("/[']+/", "''", stripslashes($email))."',
         '$new_identifiant',
         '$new_code',
         '$derniere_connexion',
         '$derniere_ip',
         '".preg_replace("/[']+/", "''", stripslashes($dernier_host))."',
         '".preg_replace("/[']+/", "''", stripslashes($dernier_user_agent))."',
         '".preg_replace("/[']+/", "''", stripslashes($derniere_erreur_code))."',
         '$fiche_manuelle',
         '$cursus_en_cours',
         '$candidat_lock',
         '$candidat_lockdate',
         '$deja_inscrit',
         '$annee_premiere_inscr',
         '$annee_bac',
         '".preg_replace("/[']+/", "''", stripslashes($serie_bac))."')");

      // renseignements minimum pour l'historique     
      $_SESSION['tab_candidat']=array("nom" => $nom, "prenom" => $prenom, "email" => $email);

      write_evt($dbr, $__EVT_ID_G_ID, 
          "Création fiche manuelle $nom $prenom", 
          $new_id, 
          $new_id, 
          "INSERT INTO $_DB_candidat VALUES('$new_id','$civilite','$nom','$prenom','$date_naissance','$nationalite_code','$telephone','".$adresse_1." ".$adresse_2." ".$adresse_3."','$num_ine','$email','$new_identifiant','$new_code','0','$lieu_naissance', '$deuxieme_prenom','','','$pays_naissance_code','$adr_cp','$adr_ville','$adr_pays_code','','','$fiche_manuelle', '$candidat_lock','$candidat_lockdate','$dpt_naissance','$nom_naissance','$telephone_portable')");

      // Message au candidat
         $corps_message="Bonjour $civilite ". preg_replace("/[']+/", "'", $nom) .",

Bienvenue sur l'Interface de Candidatures !

Cette interface vous permet de déposer un ou plusieurs dossiers de candidatures dans les composantes enregistrées.

<strong><u>Quelques conseils pour débuter :</u></strong>

&#8226;&nbsp;&nbsp;<a href='$__DOC_DIR/documentation.php' target='_blank' class='lien_bleu_12'><b>la documentation</b> : elle résume toute la procédure</a>

&#8226;&nbsp;&nbsp;<b>le menu supérieur</b> : il vous permet d'accéder aux fonctionnalités de l'interface :
&nbsp;&nbsp;- \"Choisir une autre composante\" pour déposer un dossier dans un autre établissement,
&nbsp;&nbsp;- \"Votre fiche\" pour compléter vos informations (menu par défaut),
&nbsp;&nbsp;- \"Rechercher une formation\" pour trouver la composante proposant la formation que vous cherchez,
&nbsp;&nbsp;- \"Messagerie\" : l'application vous enverra automatiquement des messages (avec notification de réception sur votre adresse électronique), et vous pourrez également l'utiliser pour contacter la scolarité,
&nbsp;&nbsp;- \"Mode d'emploi\" : un lien permanent vers la documentation.

N'hésitez pas à explorer cette interface !


Vous pouvez maintenant cliquer sur <strong>\"Votre fiche\"</strong> et commencer à compléter les informations demandées.


Bien cordialement,


$__SIGNATURE_COURRIELS";

         $dest_array=array("0" => array("id"       => "$new_id",
                                        "civ"      => "$civilite",
                                        "nom"       => preg_replace("/[']+/", "'", $nom),
                                        "prenom"    => preg_replace("/[']+/", "'", $prenom),
                                        "email"      => "$email"));

         write_msg("", array("id" => "0", "nom" => "Système", "prenom" => "", "composante" => "", "universite" => "$__SIGNATURE_COURRIELS"),
                   $dest_array, "Bienvenue !", $corps_message, "$nom $prenom", $__FLAG_MSG_NO_NOTIFICATION);


         // Enregistrement : identifiants
         
         if(FALSE!==strpos($email, "@"))
         {
            $headers = "MIME-Version: 1.0\r\nFrom: $__EMAIL_NOREPLY\r\nReply-To: $__EMAIL_NOREPLY\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";
            
            $corps_message="============================================================\nCeci est un message automatique, merci de ne pas y répondre.\n============================================================\n\n
Bonjour $civilite ". preg_replace("/[']+/", "'", $nom) . ",\n
Les informations vous permettant d'accéder à l'interface de précandidatures sont les suivantes:
- Adresse : $__URL_CANDIDAT
- Identifiant : ". stripslashes($new_identifiant) . "
- Code Personnel : $new_code\n
Attention : respectez bien les minuscules et majuscules lorsque vous entrez ces codes !\n
Ne perdez surtout pas ces informations : elles vous serviront à consulter certains documents et résultats par la suite.\n\n
Cordialement,\n\n
--
$__SIGNATURE_COURRIELS";

            $ret=mail($email,"[Précandidatures] - Enregistrement", $corps_message, $headers);
         }
         
       db_close($dbr);

      header("Location:" . base_url($php_self) . "edit_candidature.php?cid=$new_id");
      exit();
    }
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

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    titre_page_icone("Création manuelle d'une fiche candidat", "add_32x32_fond.png", 12, "L");

    if(isset($id_existe))
      message("<strong>Erreur</strong> : ces données existent déjà dans la base (mêmes nom, prénom(s) et date de naissance)", $__ERREUR);
    else
    {
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

      if(isset($err_serie_bac))
      {
        $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
        $message_erreur.="- vous devez sélectionner la série du baccalauréat (ou équivalence). Si le candidat / la candidate n'a pas obtenu le baccalauréat, sélectionnez \"Sans bac\" dans le menu déroulant.";
      }

      if($message_erreur!="")
      {
        $message_erreur="<strong>Erreur(s)</strong> :\n<br>$message_erreur";
        message("$message_erreur", $__ERREUR);
      }
    }

    if(isset($champ_vide))
      message("<strong>Formulaire incomplet</strong> : les champs en gras sont <u>obligatoires</u>", $__ERREUR);
    else
      message("<strong>Les champs en gras sont <u>obligatoires</u></strong>. Aucun courriel n'est envoyé lors de la création manuelle d'une Fiche Candidat.", $__WARNING);
  ?>

  <form name="form1" action="<?php print("$php_self"); ?>" method="POST">

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
      {
        if($civilite=="M")
        {
          $selected_M="selected='1'";
          $selected_Mlle="";
          $selected_Mme="";
        }
        else
        {
          if($civilite=="Mme")
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
      }
      else
        $selected_M=$selected_Mlle=$selected_Mme="";

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
      <input type='text' name='nom' value='<?php if(isset($nom)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($nom)), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="30">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Nom de naissance (si différent) : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='nom_naissance' value='<?php if(isset($nom_naissance)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($nom_naissance)), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="30">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Prénom : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='prenom' value='<?php if(isset($prenom)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($prenom)),ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="30">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'>Deuxième prénom (facultatif) : </font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='prenom2' value='<?php if(isset($deuxieme_prenom)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($deuxieme_prenom)),ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="30">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Date de naissance (JJ/MM/AAAA) : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='jour' value='<?php if(isset($jour)) echo htmlspecialchars($jour,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="2" maxlength="2">/
      <input type='text' name='mois' value='<?php if(isset($mois)) echo htmlspecialchars($mois,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="2" maxlength="2">/
      <input type='text' name='annee' value='<?php if(isset($annee)) echo htmlspecialchars($annee,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="4" maxlength="4">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Ville de naissance : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='lieu_naissance' value='<?php if(isset($lieu_naissance)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($lieu_naissance)),ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="60">
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
          $res_departements=db_query($dbr, "SELECT $_DBC_departements_fr_numero, $_DBC_departements_fr_nom
                                 FROM $_DB_departements_fr
                                 ORDER BY $_DBC_departements_fr_numero");

          $nb_dpts_fr=db_num_rows($res_departements);

          for($dpt=0; $dpt<$nb_dpts_fr; $dpt++)
          {
            list($dpt_num, $dpt_nom)=db_fetch_row($res_departements, $dpt);

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
              $selected=(isset($pays_naissance_code) && $pays_naissance_code==$code_iso) ? "selected='1'" : "";
              
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
              $selected=(isset($nationalite_code) && $nationalite_code==$code_iso) ? "selected='1'" : "";
              
              print("<option value='$code_iso' $selected>$array_pays_nat[nationalite]</option>\n");
            }
          }
        ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'>Adresse électronique valide : </font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='email' value='<?php if(isset($email)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($email)), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="255">
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
         <input name='adresse_1' value="<?php if(isset($adresse_1)) echo htmlspecialchars(stripslashes($adresse_1), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse (suite) : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_2' value="<?php if(isset($adresse_2)) echo htmlspecialchars(stripslashes($adresse_2), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse (suite) : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_3' value="<?php if(isset($adresse_3)) echo htmlspecialchars(stripslashes($adresse_3), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>" size='40' maxlength="30">
      </td>
   </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Code Postal :</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='adr_cp' value='<?php if(isset($adr_cp)) echo htmlspecialchars($adr_cp,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="15">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_important_menu2'><b>Ville :</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='adr_ville' value='<?php if(isset($adr_ville)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($adr_ville)),ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="60">
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
              $selected=(isset($adr_pays_code) && $adr_pays_code==$code_iso) ? "selected='1'" : "";
              
              print("<option value='$code_iso' $selected>$array_pays_nat[pays]</option>\n");
            }
          }
        ?>
      </select>
      <!-- <input type='text' name='adr_pays' value='<?php if(isset($adr_pays)) echo htmlspecialchars($adr_pays,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="60"> -->
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
      <input type='text' name='annee_bac' value='<?php if(isset($annee_bac)) echo "$annee_bac"; ?>' size="25" maxlength="4"><font class='Texte'><i>(Format : YYYY)</i></font>
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
          $result=db_query($dbr,"SELECT $_DBC_diplomes_bac_code, $_DBC_diplomes_bac_intitule
                        FROM $_DB_diplomes_bac ORDER BY $_DBC_diplomes_bac_intitule");
          $rows=db_num_rows($result);

          if(isset($serie_bac))
            $cur_serie_bac=$serie_bac;

          for($i=0; $i<$rows; $i++)
          {
            list($serie_bac, $intitule_bac)=db_fetch_row($result,$i);

            $selected=isset($cur_serie_bac) && $cur_serie_bac==$serie_bac ? "selected=1" : "";

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
      <input type='text' name='annee_premiere_inscr' value='<?php if(isset($annee_premiere_inscr)) echo "$annee_premiere_inscr"; ?>' size="25" maxlength="4"><font class='Texte'><i>(Format : YYYY)</i></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'>Numéro INE <b>ou</b> BEA : </font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='num_ine' value='<?php if(isset($num_ine)) echo htmlspecialchars($num_ine,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="11"> <font class='Texte_menu'>(<b>obligatoire</b> en cas d'inscription antérieure dans cette Université)</font>
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
      <input type='text' name='telephone' value='<?php if(isset($telephone)) echo htmlspecialchars($telephone,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="15">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'>Téléphone portable : </font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='telephone_portable' value='<?php if(isset($telephone_portable)) echo htmlspecialchars($telephone_portable,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size="25" maxlength="15">
    </td>
  </tr>
  </table>

  <div class='centered_icons_box'>
    <a href='index.php' target='_self'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
    </form>
  </div>

</div>
<?php
  db_close($dbr);
  pied_de_page();
?>

<script language="javascript">
  document.form1.civilite.focus()
</script>

</body>
</html>

