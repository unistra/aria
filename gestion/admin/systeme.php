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
   include "$__INCLUDE_DIR_ABS/access_functions.php";
   include "$__INCLUDE_DIR_ABS/fonctions_ldap.php";

   // Menu
   if(isset($_GET["menu"]) && ctype_digit($_GET["menu"]) && array_key_exists($_GET["menu"], $menu_config))
      $_SESSION["current_config_menu"]=$_GET["menu"];
   elseif(!isset($_SESSION["current_config_menu"]))
      $_SESSION["current_config_menu"]=1;

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   verif_auth("$__GESTION_DIR/login.php");

   if($_SESSION['niveau']!=$__LVL_ADMIN)
   {
      header("Location:$__GESTION_DIR/noaccess.php");
      exit();
   }

   $dbr=db_connect();

   // Déverrouillage, au cas où
   if(isset($_SESSION["candidat_id"]))
      cand_unlock($dbr, $_SESSION["candidat_id"]);


   // Validation du formulaire
   if((isset($_POST["Valider"]) || isset($_POST["Valider_x"])) && array_key_exists("config", $_POST))
   {
      // En fonction de la section du menu, on récupère les variables adéquates

      switch($_SESSION["current_config_menu"])
      {
         case 1 : $new_titre_html=array_key_exists("__TITRE_HTML", $_POST["config"]) ? trim($_POST["config"]["__TITRE_HTML"]) : "";
                  $new_titre_page=array_key_exists("__TITRE_PAGE", $_POST["config"]) ? trim($_POST["config"]["__TITRE_PAGE"]) : "";
                  $new_url_candidat=array_key_exists("__URL_CANDIDAT", $_POST["config"]) ? trim($_POST["config"]["__URL_CANDIDAT"]) : "http://" . $_SERVER["SERVER_NAME"] . str_replace("gestion/admin/systeme.php", "", str_replace($_SERVER["DOCUMENT_ROOT"], "", __FILE__));;
                  $new_url_gestion=array_key_exists("__URL_GESTION", $_POST["config"]) ? trim($_POST["config"]["__URL_GESTION"]) : "http://" . $_SERVER["SERVER_NAME"] . str_replace("gestion/admin/systeme.php", "gestion/", str_replace($_SERVER["DOCUMENT_ROOT"], "", __FILE__));;
                  $new_meta=array_key_exists("__META", $_POST["config"]) ? trim($_POST["config"]["__META"]) : "";

                  $update="";

                  // Vérification sommaire du format de l'URL
                  if($new_url_gestion=="" || !preg_match("/[[:alpha:]]+:\/\/[^<>[:space:]]+[[:alnum:]]/", $new_url_gestion))
                     $format_url_gestion=1;
                  else
                     $update.="$_DBU_systeme_url_gestion='$new_url_gestion'";

                  // Vérification sommaire du format de l'URL
                  if($new_url_candidat=="" || !preg_match("/[[:alpha:]]+:\/\/[^<>[:space:]]+[[:alnum:]]/", $new_url_candidat))
                     $format_url_candidat=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_url_candidat='$new_url_candidat'";
                  }

                  if($new_titre_html=="")
                     $format_titre_html=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_titre_html='".stripslashes(preg_replace("/'/","''",$new_titre_html))."'";
                  }

                  if($new_titre_page=="")
                     $format_titre_page=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_titre_page='".stripslashes(preg_replace("/'/","''",$new_titre_page))."'";
                  }

                  $update.=$update!="" ? "," : "";
                  $update.="$_DBU_systeme_meta='".stripslashes(preg_replace("/'/","''",$new_meta))."'";

                  if($update!="")
                  {
                     db_query($dbr, "UPDATE $_DB_systeme SET $update");
   
                     $succes=1;
                  }

                  break;
                  
         case 2 : $new_ldap_actif=array_key_exists("__LDAP_ACTIF", $_POST["config"]) ? trim($_POST["config"]["__LDAP_ACTIF"]) : "f"; 
                  $new_ldap_host=array_key_exists("__LDAP_HOST", $_POST["config"]) ? trim($_POST["config"]["__LDAP_HOST"]) : "";
                  $new_ldap_port=array_key_exists("__LDAP_PORT", $_POST["config"]) ? trim($_POST["config"]["__LDAP_PORT"]) : "389";
                  $new_ldap_proto=array_key_exists("__LDAP_PROTO", $_POST["config"]) ? trim($_POST["config"]["__LDAP_PROTO"]) : "3";
                  $new_ldap_id=array_key_exists("__LDAP_ID", $_POST["config"]) ? trim($_POST["config"]["__LDAP_ID"]) : "";
                  $new_ldap_pass=array_key_exists("__LDAP_PASS", $_POST["config"]) ? trim($_POST["config"]["__LDAP_PASS"]) : "";
                  $new_ldap_basedn=array_key_exists("__LDAP_BASEDN", $_POST["config"]) ? trim($_POST["config"]["__LDAP_BASEDN"]) : "";

                  $new_ldap_attr_login=array_key_exists("__LDAP_ATTR_LOGIN", $_POST["config"]) ? strtolower(trim($_POST["config"]["__LDAP_ATTR_LOGIN"])) : "";
                  $new_ldap_attr_prenom=array_key_exists("__LDAP_ATTR_PRENOM", $_POST["config"]) ? strtolower(trim($_POST["config"]["__LDAP_ATTR_PRENOM"])) : "";
                  $new_ldap_attr_nom=array_key_exists("__LDAP_ATTR_NOM", $_POST["config"]) ? strtolower(trim($_POST["config"]["__LDAP_ATTR_NOM"])) : "";
                  $new_ldap_attr_mail=array_key_exists("__LDAP_ATTR_MAIL", $_POST["config"]) ? strtolower(trim($_POST["config"]["__LDAP_ATTR_MAIL"])) : "";

                  $update="";

                  if($new_ldap_actif!='t')
                     $new_ldap_actif='f';
                     
                  $update.="$_DBU_systeme_ldap_actif='$new_ldap_actif'";

                  // Vérification sommaire du format de l'URL
                  if($new_ldap_host=="") // || !preg_match("/[[:alpha:]]+:\/\/[^<>[:space:]]+[[:alnum:]]/", $new_ldap_host))
                     $format_ldap_host=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_host='$new_ldap_host'";
                  }

                  // Vérification sommaire du format du port
                  if($new_ldap_port=="" || !ctype_digit($new_ldap_port))
                     $format_ldap_port=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_port='$new_ldap_port'";
                  }

                  if($new_ldap_proto=="" || !ctype_digit($new_ldap_proto))
                     $format_ldap_proto=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_proto='$new_ldap_proto'";
                  }

                  if($new_ldap_id=="")
                     $format_ldap_id=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_id='$new_ldap_id'";
                  }
                  
                  if($new_ldap_pass=="")
                     $format_ldap_pass=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_pass='".stripslashes(preg_replace("/'/","''",$new_ldap_pass))."'";
                  }                  
                  
                  if($new_ldap_basedn=="")
                     $format_ldap_basedn=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_basedn='$new_ldap_basedn'";
                  }
                  
                  if($new_ldap_attr_login=="")
                     $format_ldap_attr_login=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_attr_login='$new_ldap_attr_login'";
                  }
                  
                  if($new_ldap_attr_prenom=="")
                     $format_ldap_attr_prenom=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_attr_prenom='$new_ldap_attr_prenom'";
                  }
                  
                  if($new_ldap_attr_nom=="")
                     $format_ldap_attr_nom=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_attr_nom='$new_ldap_attr_nom'";
                  }
                  
                  if($new_ldap_attr_mail=="")
                     $format_ldap_attr_mail=1;
                  else
                  {
                     // ce paramètre peut être multivalué : champs séparés par des virgules
                     
                     $attr_mail=preg_replace("/[\ ]+/", ",", $new_ldap_attr_mail);
                     
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_attr_mail='$attr_mail'";
                  }
                  /*
                  if($new_ldap_attr_pass=="")
                     $format_ldap_attr_pass=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_ldap_attr_pass='$new_ldap_attr_pass'";
                  }
                  */

                  if($update!="")
                  {
                     db_query($dbr, "UPDATE $_DB_systeme SET $update");
                     $succes=1;
                  }

                  break;

         case 3 : $new_nom_admin=array_key_exists("__NOM_ADMIN", $_POST["config"]) ? trim($_POST["config"]["__NOM_ADMIN"]) : "";
                  $new_email_admin=array_key_exists("__EMAIL_ADMIN", $_POST["config"]) ? trim($_POST["config"]["__EMAIL_ADMIN"]) : "";
                  $new_email_support=array_key_exists("__EMAIL_SUPPORT", $_POST["config"]) ? trim($_POST["config"]["__EMAIL_SUPPORT"]) : "";
                  $new_email_noreply=array_key_exists("__EMAIL_NOREPLY", $_POST["config"]) ? trim($_POST["config"]["__EMAIL_NOREPLY"]) : "";
                  $new_signature_admin=array_key_exists("__SIGNATURE_ADMIN", $_POST["config"]) ? trim($_POST["config"]["__SIGNATURE_ADMIN"]) : "";
                  $new_signature_courriels=array_key_exists("__SIGNATURE_COURRIELS", $_POST["config"]) ? trim($_POST["config"]["__SIGNATURE_COURRIELS"]) : "";

                  $update="";

                  // Vérification sommaire
                  if($new_email_admin=="" || !preg_match("/[[:alnum:]]+@[^<>[:space:]]+[[:alnum:]]/", $new_email_admin))
                     $format_email_admin=1;
                  else
                     $update.="$_DBU_systeme_courriel_admin='".stripslashes(preg_replace("/'/","''", $new_email_admin))."'";
                     
                  if($new_email_support=="" || !preg_match("/[[:alnum:]]+@[^<>[:space:]]+[[:alnum:]]/", $new_email_support))
                     $format_email_support=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_courriel_support='".stripslashes(preg_replace("/'/","''", $new_email_support))."'";
                  }
                  
                  if($new_email_noreply=="" || !preg_match("/[[:alnum:]]+@[^<>[:space:]]+[[:alnum:]]/", $new_email_noreply))
                     $format_email_noreply=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_courriel_noreply='".stripslashes(preg_replace("/'/","''", $new_email_noreply))."'";
                  }

                  if($new_nom_admin=="")
                     $format_nom_admin=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_admin='".stripslashes(preg_replace("/'/","''",$new_nom_admin))."'";
                  }

                  if($new_signature_admin=="")
                     $format_signature_admin=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_signature_admin='".stripslashes(preg_replace("/'/","''",$new_signature_admin))."'";
                  }

                  if($new_signature_courriels=="")
                     $format_signature_courriels=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_signature_courriels='".stripslashes(preg_replace("/'/","''",$new_signature_courriels))."'";
                  }

                  if($update!="")
                  {
                     db_query($dbr, "UPDATE $_DB_systeme SET $update");
   
                     $succes=1;
                  }

                  break;

         case 4 : $new_ville=array_key_exists("__VILLE", $_POST["config"]) ? trim($_POST["config"]["__VILLE"]) : "";
                  $new_informatique_liberte=array_key_exists("__INFORMATIQUE_ET_LIBERTES", $_POST["config"]) ? trim($_POST["config"]["__INFORMATIQUE_ET_LIBERTES"]) : "";
                  $new_mois_limite_candidatures=array_key_exists("__MOIS_LIMITE_CANDIDATURE", $_POST["config"]) ? trim($_POST["config"]["__MOIS_LIMITE_CANDIDATURE"]) : "03";
                  $new_max_cand_masse=array_key_exists("__MAX_CAND_MASSE", $_POST["config"]) ? trim($_POST["config"]["__MAX_CAND_MASSE"]) : "40";
                  $new_defaut_decisions=array_key_exists("__DEFAUT_DECISIONS", $_POST["config"]) ? trim($_POST["config"]["__DEFAUT_DECISIONS"]) : "t";
                  $new_defaut_motifs=array_key_exists("__DEFAUT_MOTIFS", $_POST["config"]) ? trim($_POST["config"]["__DEFAUT_MOTIFS"]) : "t";
                  $new_max_rappels=array_key_exists("__MAX_RAPPELS", $_POST["config"]) ? trim($_POST["config"]["__MAX_RAPPELS"]) : "3";
                  $new_ajout_verrouillage_jours=array_key_exists("__AJOUT_VERROUILLAGE_JOURS", $_POST["config"]) ? trim($_POST["config"]["__AJOUT_VERROUILLAGE_JOURS"]) : "2";
                  $new_assistance=array_key_exists("__ASSISTANCE", $_POST["config"]) ? trim($_POST["config"]["__ASSISTANCE"]) : "f";
                  
                  $update="";

                  if($new_ville=="")
                     $format_ville=1;
                  else
                     $update.="$_DBU_systeme_ville='".stripslashes(preg_replace("/'/","''",$new_ville))."'";

                  if($new_informatique_liberte=="")
                     $format_info_liberte=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_info_liberte='".preg_replace("/'/","''", stripslashes($new_informatique_liberte))."'";
                  }

                  if(!ctype_digit($new_mois_limite_candidatures) || $new_mois_limite_candidatures<1 || $new_mois_limite_candidatures>12)
                     $format_mois_limite_candidatures=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_limite_periode='$new_mois_limite_candidatures'";
                  }

                  if(!ctype_digit($new_max_cand_masse) || $new_max_cand_masse<1)
                     $format_max_cand_masse=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_limite_masse='$new_max_cand_masse'";
                  }

                  if($new_defaut_decisions!="t" && $new_defaut_decisions!="f")
                     $new_defaut_decisions="t";

                  $update.=$update!="" ? "," : "";
                  $update.="$_DBU_systeme_defaut_decision='$new_defaut_decisions'";

                  if($new_assistance!="t" && $new_assistance!="f")
                     $new_assistance="f";
                                       
                  $update.=$update!="" ? "," : "";
                  $update.="$_DBU_systeme_assistance='$new_assistance'";

                  if($new_defaut_motifs!="t" && $new_defaut_motifs!="f")
                     $new_defaut_motifs="t";

                  $update.=$update!="" ? "," : "";
                  $update.="$_DBU_systeme_defaut_motifs='$new_defaut_motifs'";

                  if(!ctype_digit($new_max_rappels) || $new_max_rappels<0)
                     $format_max_rappels=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_max_rappels='$new_max_rappels'";
                  }

                  if(!ctype_digit($new_ajout_verrouillage_jours) || $new_ajout_verrouillage_jours<1)
                     $format_ajout_verrouillage_jours=1;
                  else
                  {
                     $update.=$update!="" ? "," : "";
                     $update.="$_DBU_systeme_rappel_delai_sup='$new_ajout_verrouillage_jours'";
                  }

                  if($update!="")
                  {
                     db_query($dbr, "UPDATE $_DB_systeme SET $update");
   
                     $succes=1;
                  }

                  break;

         case 5 : $new_erreur_sujet=array_key_exists("__ERREUR_SUJET", $_POST["config"]) ? trim($_POST["config"]["__ERREUR_SUJET"]) : "";
                  $new_debug_sujet=array_key_exists("__DEBUG_SUJET", $_POST["config"]) ? trim($_POST["config"]["__DEBUG_SUJET"]) : "";
                  $new_debug=array_key_exists("__DEBUG", $_POST["config"]) ? trim($_POST["config"]["__DEBUG"]) : "t";
                  $new_debug_id=array_key_exists("__DEBUG_RAPPEL_IDENTIFIANTS", $_POST["config"]) ? trim($_POST["config"]["__DEBUG_RAPPEL_IDENTIFIANTS"]) : "f";
                  $new_debug_cursus=array_key_exists("__DEBUG_CURSUS", $_POST["config"]) ? trim($_POST["config"]["__DEBUG_CURSUS"]) : "f";
                  $new_debug_prec=array_key_exists("__DEBUG_STATUT_PREC", $_POST["config"]) ? trim($_POST["config"]["__DEBUG_STATUT_PREC"]) : "f";
                  $new_debug_reg=array_key_exists("__DEBUG_ENREGISTREMENT", $_POST["config"]) ? trim($_POST["config"]["__DEBUG_ENREGISTREMENT"]) : "t";
                  $new_debug_lock=array_key_exists("__DEBUG_LOCK", $_POST["config"]) ? trim($_POST["config"]["__DEBUG_LOCK"]) : "f";

                  // Vérification et valeurs par défaut. Les champs "sujet" peuvent rester vides.
                  if($new_debug!="t" && $new_debug!="f")
                     $new_debug="t";

                  if($new_debug_id!="t" && $new_debug_id!="f")
                     $new_debug_id="f";

                  if($new_debug_cursus!="t" && $new_debug_cursus!="f")
                     $new_debug_cursus="f";

                  if($new_debug_prec!="t" && $new_debug_prec!="f")
                     $new_debug_prec="f";

                  if($new_debug_reg!="t" && $new_debug_reg!="f")
                     $new_debug_reg="t";

                  if($new_debug_lock!="t" && $new_debug_lock!="f")
                     $new_debug_lock="f";

                  db_query($dbr, "UPDATE $_DB_systeme SET $_DBU_systeme_erreur_sujet='$new_erreur_sujet',
                                                          $_DBU_systeme_debug_sujet='$new_debug_sujet',
                                                          $_DBU_systeme_debug='$new_debug',
                                                          $_DBU_systeme_debug_rappel_id='$new_debug_id',
                                                          $_DBU_systeme_debug_cursus='$new_debug_cursus',
                                                          $_DBU_systeme_debug_statut_prec='$new_debug_prec',
                                                          $_DBU_systeme_debug_enregistrement='$new_debug_reg',
                                                          $_DBU_systeme_debug_lock='$new_debug_lock'");

                  $succes=1;

                  break;
      }

      // Rechargement de la configuration
      __get_config($dbr);
   }


   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>

<div class='main'>
   <div class='menu_gauche'>
      <ul class='menu_gauche'>
         <?php
            foreach($menu_config as $menu => $nom_menu)
            {
               if($_SESSION["current_config_menu"]==$menu)
                  print("<li class='menu_gauche_select'><strong>$nom_menu</strong></li>\n");
               else
                  print("<li class='menu_gauche'><a href='$php_self?menu=$menu' class='lien_menu_gauche' target='_self'>$nom_menu</a></li>\n");
            }
         ?>
      </ul>
   </div>
   <div class='corps_gestion'>
      <?php
         titre_page_icone("Paramètres de l'application", "preferences_32x32_fond.png", 15, "L");

         if(isset($succes))
            message("Paramètres enregistrés", $__SUCCES);

         $message="";

         if(isset($format_titre_html))
            $message.="<br>- le champ \"Titre de la fenêtre du navigateur\" ne doit pas être vide";

         if(isset($format_titre_page))
            $message.="<br>- le champ \"Titre des pages par défaut\" ne doit pas être vide";

         if(isset($format_url_candidat))
            $message.="<br>- l'adresse de l'interface pour les candidats est manquante ou incorrecte (format : http://... ou https://)";

         if(isset($format_url_gestion))
            $message.="<br>- l'adresse de l'interface pour les gestionnaire est manquante ou incorrecte (format : http://... ou https://)";

         // 2 - LDAP
         if(isset($format_ldap_host))
            $message.="<br>- l'adresse du serveur LDAP est manquante ou incorrecte (format : ldap://... ou ldaps://)";
            
         if(isset($format_ldap_port))
            $message.="<br>- le port du serveur LDAP est manquant ou incorrect (entier positif)";

         if(isset($format_ldap_proto))
            $message.="<br>- le protocol LDAP est manquant ou incorrect (entier positif)";

         if(isset($format_ldap_id))
            $message.="<br>- l'identifiant du compte LDAP est vide";
            
         if(isset($format_ldap_pass))
            $message.="<br>- le mot de passe du compte LDAP est vide";

         if(isset($format_ldap_basedn))
            $message.="<br>- le BaseDN du serveur LDAP est manquant ou incorrect";
            
         if(isset($format_ldap_attr_login))
            $message.="<br>- l'attribut \"identifiant\" LDAP est vide";
            
         if(isset($format_ldap_attr_nom))
            $message.="<br>- l'attribut \"nom\" LDAP est vide";
          
         if(isset($format_ldap_attr_prenom))
            $message.="<br>- l'attribut \"prénom\" LDAP est vide";
            
         if(isset($format_ldap_attr_mail))
            $message.="<br>- l'attribut \"mail\" LDAP est vide";
         /*
         if(isset($format_ldap_attr_pass))
            $message.="<br>- l'attribut \"Mot de passe\" LDAP est vide";
         */   
         // 3 - Admin

         if(isset($format_nom_admin))
            $message.="<br>- le nom de l'administrateur ne doit pas être vide";

         if(isset($format_signature_admin))
            $message.="<br>- le champ \"Signature des messages administrateur\" ne doit pas être vide";

         if(isset($format_signature_courriels))
            $message.="<br>- le champ \"Signature des messages de l'application\" ne doit pas être vide";

         if(isset($format_email_admin))
            $message.="<br>- l'adresse électronique de l'administrateur est manquante ou incorrecte (format : adresse@domaine)";

         if(isset($format_email_support))
            $message.="<br>- l'adresse électronique du \"Support informatique\" est manquante ou incorrecte (format : adresse@domaine)";
            
         if(isset($format_email_noreply))
            $message.="<br>- l'adresse électronique \"no reply\" est manquante ou incorrecte (format : adresse@domaine)";

         if(isset($format_ville))
            $message.="<br>- le champ \"Ville\" ne peut être vide";

         if(isset($format_info_liberte))
            $message.="<br>- le champ \"Informatique et Libertés\" ne peut être vide";

         if(isset($format_mois_limite_candidatures))
            $message.="<br>- le mois délimitant deux années universitaires est manquant ou incorrect";

         if(isset($format_max_cand_masse))
            $message.="<br>- le nombre de page maximal par PDF est manquant ou incorrect (nombre attendu : entier positif)";

         if(isset($format_max_rappels))
            $message.="<br>- le nombre de rappels automatiques est manquant ou incorrect (nombre attendu : entier positif)";

         if(isset($format_ajout_verrouillage_jours))
            $message.="<br>- le nombre de jours entre deux rappels est manquant ou incorrect (nombre attendu : entier positif)";

         if(isset($message) && $message!="")
            message("<strong>Attention</strong> : $message<br><br>Le ou les paramètres concernés par cet avertissement <strong>n'ont pas été enregistrés</strong>.", $__ERREUR);

         if(!isset($succes) && $message=="")
            message("N'oubliez pas de valider le formulaire pour prendre en compte les modifications !", $__INFO);

         print("<form action='$php_self' method='POST' name='form1'>\n");

      ?>
      <table style='margin-left:auto; margin-right:auto; padding:10px;'>

      <?php
         switch($_SESSION["current_config_menu"])
         {
            case 1 : if(isset($new_titre_html))
                        $titre_html=$new_titre_html;
                     elseif(isset($GLOBALS["__TITRE_HTML"]))
                        $titre_html=$GLOBALS["__TITRE_HTML"];
                     else
                        $titre_html="ARIA - Précandidatures en ligne";

                     if(isset($new_titre_page))
                        $titre_page=$new_titre_page;
                     elseif(isset($GLOBALS["__TITRE_PAGE"]))
                        $titre_page=$GLOBALS["__TITRE_PAGE"];
                     else
                        $titre_page="Université de ...";

                     if(isset($new_url_candidat))
                        $url_candidat=$new_url_candidat;
                     elseif(isset($GLOBALS["__URL_CANDIDAT"]))
                        $url_candidat=$GLOBALS["__URL_CANDIDAT"];
                     else
                        $url_candidat="http://" . $_SERVER["SERVER_NAME"] . str_replace("gestion/admin/systeme.php", "", str_replace($_SERVER["DOCUMENT_ROOT"], "", __FILE__));

                     if(isset($new_url_gestion))
                        $url_gestion=$new_url_gestion;
                     elseif(isset($GLOBALS["__URL_GESTION"]))
                        $url_gestion=$GLOBALS["__URL_GESTION"];
                     else
                        $url_gestion="http://" . $_SERVER["SERVER_NAME"] . str_replace("gestion/admin/systeme.php", "gestion/", str_replace($_SERVER["DOCUMENT_ROOT"], "", __FILE__));

                     if(isset($new_meta))
                        $meta=$new_meta;
                     elseif(isset($GLOBALS["__META"]))
                        $meta=$GLOBALS["__META"];
                     else
                        $meta="";
      ?>

      <tr>
         <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
            <font class='Texte_menu2'><strong>Paramètres HTTP</strong></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='width:50%; padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Titre de la fenêtre du navigateur</strong>
               <br>Texte apparaissant dans la barre de titre de la fenêtre ou de l'onglet lorsque vous vous connectez à l'interface.
            </font>
         </td>
         <td class='td-droite fond_menu' style='width:50%; padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__TITRE_HTML]' value='<?php echo htmlspecialchars(stripslashes($titre_html), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Titre des pages par défaut</strong>
               <br>Texte apparaissant dans le bandeau supérieur de l'interface.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__TITRE_PAGE]' value='<?php echo htmlspecialchars(stripslashes($titre_page), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Adresse complète de l'interface pour les candidats</strong>
               <br>Adresse rappelée dans de nombreuses pages et dans certains messages envoyés aux candidats.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__URL_CANDIDAT]' value='<?php echo htmlspecialchars(stripslashes($url_candidat), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='256'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Adresse complète de l'interface pour les gestionnaires</strong>
               <br>Adresse apparaissant dans certains messages envoyés aux gestionnaires (création d'un accès, notification de messages non lus, etc).
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__URL_GESTION]' value='<?php echo htmlspecialchars(stripslashes($url_gestion), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='256'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Mots clés pour l'indexation de l'interface par certains moteurs de recherche</strong>
               <br>Ces mots clés (séparés par une virgule) doivent être en rapport avec l'interface et votre université.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__META]' value='<?php echo htmlspecialchars(stripslashes($meta), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='2048'>
            </font>
         </td>
      </tr>

      <?php
               break;
               
         case 2 : if(isset($new_ldap_actif))
                     $ldap_actif=$new_ldap_actif;
                  elseif(isset($GLOBALS["__LDAP_ACTIF"]))
                     $ldap_actif=$GLOBALS["__LDAP_ACTIF"];
                  else
                     $ldap_actif="f";
                     
                  if($ldap_actif=="t")
                  {
                     $ldap_actif_yes="checked";
                     $ldap_actif_no="";
                  }
                  else
                  {
                     $ldap_actif_no="checked";
                     $ldap_actif_yes="";
                  }

                  if(isset($new_ldap_host))
                     $ldap_host=$new_ldap_host;
                  elseif(isset($GLOBALS["__LDAP_HOST"]))
                     $ldap_host=$GLOBALS["__LDAP_HOST"];
                  else
                     $ldap_host="ldap://";
                     
                  if(isset($new_ldap_port))
                     $ldap_port=$new_ldap_port;
                  elseif(isset($GLOBALS["__LDAP_PORT"]))
                     $ldap_port=$GLOBALS["__LDAP_PORT"];
                  else
                     $ldap_port="389";
                     
                  if(isset($new_ldap_proto))
                     $ldap_proto=$new_ldap_proto;
                  elseif(isset($GLOBALS["__LDAP_PROTO"]))
                     $ldap_proto=$GLOBALS["__LDAP_PROTO"];
                  else
                     $ldap_proto="3";
                     
                  if(isset($new_ldap_id))
                     $ldap_id=$new_ldap_id;
                  elseif(isset($GLOBALS["__LDAP_ID"]))
                     $ldap_id=$GLOBALS["__LDAP_ID"];
                  else
                     $ldap_id="";
                     
                  if(isset($new_ldap_pass))
                     $ldap_pass=$new_ldap_pass;
                  elseif(isset($GLOBALS["__LDAP_PASS"]))
                     $ldap_pass=$GLOBALS["__LDAP_PASS"];
                  else
                     $ldap_pass="";
                                         
                  if(isset($new_ldap_basedn))
                     $ldap_basedn=$new_ldap_basedn;
                  elseif(isset($GLOBALS["__LDAP_BASEDN"]))
                     $ldap_basedn=$GLOBALS["__LDAP_BASEDN"];
                  else
                     $ldap_basedn="";
                    
                  // Attributs
                     
                  if(isset($new_ldap_attr_login))
                     $ldap_attr_login=$new_ldap_attr_login;
                  elseif(isset($GLOBALS["__LDAP_ATTR_LOGIN"]))
                     $ldap_attr_login=$GLOBALS["__LDAP_ATTR_LOGIN"];
                  else
                     $ldap_attr_login="uid";
                     
                  if(isset($new_ldap_attr_nom))
                     $ldap_attr_nom=$new_ldap_attr_nom;
                  elseif(isset($GLOBALS["__LDAP_ATTR_NOM"]))
                     $ldap_attr_nom=$GLOBALS["__LDAP_ATTR_NOM"];
                  else
                     $ldap_attr_nom="sn";
                     
                  if(isset($new_ldap_attr_prenom))
                     $ldap_attr_prenom=$new_ldap_attr_prenom;
                  elseif(isset($GLOBALS["__LDAP_ATTR_PRENOM"]))
                     $ldap_attr_prenom=$GLOBALS["__LDAP_ATTR_PRENOM"];
                  else
                     $ldap_attr_prenom="givenname";
                     
                  if(isset($new_ldap_attr_mail))
                     $ldap_attr_mail=$new_ldap_attr_mail;
                  elseif(isset($GLOBALS["__LDAP_ATTR_MAIL"]))
                     $ldap_attr_mail=$GLOBALS["__LDAP_ATTR_MAIL"];
                  else
                     $ldap_attr_mail="mail";
                  /*
                  if(isset($new_ldap_attr_pass))
                     $ldap_attr_pass=$new_ldap_attr_pass;
                  elseif(isset($GLOBALS["__LDAP_ATTR_PASS"]))
                     $ldap_attr_pass=$GLOBALS["__LDAP_ATTR_PASS"];
                  else
                     $ldap_attr_pass="userpassword";
                  */
      ?>

      <tr>
         <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
            <font class='Texte_menu2'><strong>Paramètres LDAP</strong></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Activer LDAP ?</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <input type='radio' name='config[__LDAP_ACTIF]' value='t' <?php echo $ldap_actif_yes; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Oui</font>
            <input type='radio' name='config[__LDAP_ACTIF]' value='f' <?php echo $ldap_actif_no; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Non</font>
         </td>
      </tr>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Test de connexion</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <?php            
               if($ldap_actif=="t")
               {
                  if(-1==($conn_ldap=aria_ldap_connect()))
                     print("<font class='Texte_important'>Echec</font>\n");
                  else
                  {
                     print("<font class='Textevert'>Succès</font>\n");
                     aria_ldap_close($conn_ldap);  
                  }
               }
               else
                  print("<font class='Texte_menu'><i>LDAP désactivé</font>\n");
            ?>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Adresse du serveur LDAP</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__LDAP_HOST]' value='<?php echo htmlspecialchars(stripslashes($ldap_host), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Port du serveur LDAP</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__LDAP_PORT]' value='<?php echo htmlspecialchars(stripslashes($ldap_port), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='6' maxlength='5'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Version du protocole LDAP</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <select name='config[__LDAP_PROTO]'>
                  <option value='3' <?php echo $ldap_proto=="3" ? "selected='1'" : ""; ?>>3</option>
                  <option value='2' <?php echo $ldap_proto=="2" ? "selected='1'" : ""; ?>>2</option>
               </select>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Identifiant LDAP</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__LDAP_ID]' value='<?php echo htmlspecialchars(stripslashes($ldap_id), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Mot de passe LDAP</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='password' name='config[__LDAP_PASS]' value='<?php echo htmlspecialchars(stripslashes($ldap_pass), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>BaseDN pour les utilisateurs</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__LDAP_BASEDN]' value='<?php echo htmlspecialchars(stripslashes($ldap_basedn), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='fond_page' colspan='2' style='padding:4px;'></td>
      </tr>
      <tr>
         <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
            <font class='Texte_menu2'><strong>Attributs LDAP des comptes utilisateurs</strong></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Nom</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__LDAP_ATTR_NOM]' value='<?php echo htmlspecialchars(stripslashes($ldap_attr_nom), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='25' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Prénom</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__LDAP_ATTR_PRENOM]' value='<?php echo htmlspecialchars(stripslashes($ldap_attr_prenom), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='25' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Identifiant</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__LDAP_ATTR_LOGIN]' value='<?php echo htmlspecialchars(stripslashes($ldap_attr_login), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='25' maxlength='128'>
            </font>
         </td>
      </tr>
      <!--
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Mot de passe</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__LDAP_ATTR_PASS]' value='<?php echo htmlspecialchars(stripslashes($ldap_attr_pass), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='25' maxlength='128'>
            </font>
         </td>
      </tr>
      -->
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Courriel</strong>
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__LDAP_ATTR_MAIL]' value='<?php echo htmlspecialchars(stripslashes($ldap_attr_mail), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='25' maxlength='128'>
               <br /><i>Plusieurs attributs possibles, séparés par des virgules</i>
            </font>
         </td>
      </tr>
      

      <?php
               break;

         case 3 : if(isset($new_nom_admin))
                     $nom_admin=$new_nom_admin;
                  elseif(isset($GLOBALS["__NOM_ADMIN"]))
                     $nom_admin=$GLOBALS["__NOM_ADMIN"];
                  else
                     $nom_admin="M/Mme/Mlle ...";

                  if(isset($new_email_admin))
                     $email_admin=$new_email_admin;
                  elseif(isset($GLOBALS["__EMAIL_ADMIN"]))
                     $email_admin=$GLOBALS["__EMAIL_ADMIN"];
                  else
                     $email_admin="adresse@domaine";
                     
                  if(isset($new_email_noreply))
                     $email_noreply=$new_email_noreply;
                  elseif(isset($GLOBALS["__EMAIL_NOREPLY"]))
                     $email_noreply=$GLOBALS["__EMAIL_NOREPLY"];
                  else
                     $email_noreply="no-reply@domaine";
                  
                  if(isset($new_email_support))
                     $email_support=$new_email_support;
                  elseif(isset($GLOBALS["__EMAIL_SUPPORT"]))
                     $email_support=$GLOBALS["__EMAIL_SUPPORT"];
                  else
                     $email_support="adresse@domaine";

                  if(isset($new_signature_admin))
                     $signature_admin=$new_signature_admin;
                  elseif(isset($GLOBALS["__SIGNATURE_ADMIN"]))
                     $signature_admin=$GLOBALS["__SIGNATURE_ADMIN"];
                  else
                     $signature_admin="M/Mme/Mlle ... \nAdministrateur Système\nUniversité ...";

                  if(isset($new_signature_courriels))
                     $signature_courriels=$new_signature_courriels;
                  elseif(isset($GLOBALS["__SIGNATURE_COURRIELS"]))
                     $signature_courriels=$GLOBALS["__SIGNATURE_COURRIELS"];
                  else
                     $signature_courriels="Application Aria\nUniversité ...";
      ?>

      <tr>
         <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
            <font class='Texte_menu2'><strong>Paramètres d'administration de l'interface</strong></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='width:50%; padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Civilité, nom et prénom de l'admistrateur principal de l'application</strong>
               <br>Le nom de l'administrateur peut apparaître dans certains textes informatifs.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__NOM_ADMIN]' value='<?php echo htmlspecialchars(stripslashes($nom_admin), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='256'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Adresse électronique de l'administrateur</strong>
               <br>Adresse utilisée pour les rapports d'erreur et les messages de <i>debug</i>.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__EMAIL_ADMIN]' value='<?php echo htmlspecialchars(stripslashes($email_admin), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Adresse électronique 'no reply'</strong>
               <br>Adresse d'expédition pour les messages automatiques envoyés aux candidats.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__EMAIL_NOREPLY]' value='<?php echo htmlspecialchars(stripslashes($email_admin), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Adresse électronique du Support informatique</strong>
               <br>Adresse utilisée par les candidats et les gestionnaires pour les demandes de support.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__EMAIL_SUPPORT]' value='<?php echo htmlspecialchars(stripslashes($email_support), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Signature des messages "administrateur"</strong>
               <br>Signature utilisée automatiquement dans certains messages envoyés par l'application (ex: création d'un compte utilisateur).
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <textarea name='config[__SIGNATURE_ADMIN]' cols='50' rows='5'><?php echo htmlspecialchars(stripslashes($signature_admin), ENT_QUOTES, $default_htmlspecialchars_encoding); ?></textarea>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Signature des messages de l'application</strong>
               <br>Signature utilisée par les messages envoyés automatiquement aux candidats.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <textarea name='config[__SIGNATURE_COURRIELS]' cols='50' rows='5'><?php echo htmlspecialchars(stripslashes($signature_courriels), ENT_QUOTES, $default_htmlspecialchars_encoding); ?></textarea>
            </font>
         </td>
      </tr>

      <?php
               break;

         case 4 : if(isset($new_ville))
                     $ville=$new_ville;
                  elseif(isset($GLOBALS["__VILLE"]))
                     $ville=$GLOBALS["__VILLE"];
                  else
                     $ville="";

                  if(isset($new_informatique_liberte))
                     $informatique_liberte=$new_informatique_liberte;
                  elseif(isset($GLOBALS["__INFORMATIQUE_ET_LIBERTES"]))
                     $informatique_liberte=$GLOBALS["__INFORMATIQUE_ET_LIBERTES"];
                  else
                     $informatique_liberte="Les informations vous concernant font l'objet d'un traitement informatique destiné à gérer les précandidatures en ligne. L'unique destinataire des données est l'Université de <VOTRE UNIVERSITE>. Conformément à la loi 'Informatique et Libertés' du 6 janvier 1978, vous bénéficiez d'un droit d'accès et de rectification à ces informations. Si vous souhaitez exercer ce droit et obtenir communication de ces dernières, veuillez vous adresser au Support Informatique (par courriel <a href='mailto:$__EMAIL_SUPPORT?subject=Informatique et Libertés' class='lien_bleu_10'>à cette adresse</a>). Vous pouvez également, pour des motifs légitimes, vous opposer au traitement des données vous concernant.";

                  if(isset($new_mois_limite_candidatures))
                     $mois_limite_candidatures=$new_mois_limite_candidatures;
                  elseif(isset($GLOBALS["__MOIS_LIMITE_CANDIDATURE"]))
                     $mois_limite_candidatures=$GLOBALS["__MOIS_LIMITE_CANDIDATURE"];
                  else
                     $mois_limite_candidatures="03"; // Mars est un bon compromis

                  if(isset($new_max_cand_masse))
                     $max_cand_masse=$new_max_cand_masse;
                  elseif(isset($GLOBALS["__MAX_CAND_MASSE"]))
                     $max_cand_masse=$GLOBALS["__MAX_CAND_MASSE"];
                  else
                     $max_cand_masse="40";

                  if(isset($new_defaut_decisions))
                     $defaut_decisions=$new_defaut_decisions;
                  elseif(isset($GLOBALS["__DEFAUT_DECISIONS"]))
                     $defaut_decisions=$GLOBALS["__DEFAUT_DECISIONS"];
                  else
                     $defaut_decisions="t";

                  if($defaut_decisions=="t")
                  {
                     $dd_yes="checked";
                     $dd_no="";
                  }
                  else
                  {
                     $dd_yes="";
                     $dd_no="checked";
                  }

                  if(isset($new_assistance))
                     $assistance=$new_assistance;
                  elseif(isset($GLOBALS["__ASSISTANCE"]))
                     $assistance=$GLOBALS["__ASSISTANCE"];
                  else
                     $assistance="t";

                  if($assistance=="t")
                  {
                     $assistance_yes="checked";
                     $assistance_no="";
                  }
                  else
                  {
                     $assistance_yes="";
                     $assistance_no="checked";
                  }

                  if(isset($new_defaut_motifs))
                     $defaut_motifs=$new_defaut_motifs;
                  elseif(isset($GLOBALS["__DEFAUT_MOTIFS"]))
                     $defaut_motifs=$GLOBALS["__DEFAUT_MOTIFS"];
                  else
                     $defaut_motifs="t";

                  if($defaut_motifs=="t")
                  {
                     $dm_yes="checked";
                     $dm_no="";
                  }
                  else
                  {
                     $dm_yes="";
                     $dm_no="checked";
                  }

                  if(isset($new_max_rappels))
                     $max_rappels=$new_max_rappels;
                  elseif(isset($GLOBALS["__MAX_RAPPELS"]))
                     $max_rappels=$GLOBALS["__MAX_RAPPELS"];
                  else
                     $max_rappels="3";

                  if(isset($new_ajout_verrouillage_jours))
                     $ajout_verrouillage_jours=$new_ajout_verrouillage_jours;
                  elseif(isset($GLOBALS["__AJOUT_VERROUILLAGE_JOURS"]))
                     $ajout_verrouillage_jours=$GLOBALS["__AJOUT_VERROUILLAGE_JOURS"];
                  else
                     $ajout_verrouillage_jours="2";
      ?>

      <tr>
         <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
            <font class='Texte_menu2'><strong>Informations administratives</strong></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; width:60%; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Ville accueillant l'Université</strong>
               <br>Le nom de la ville apparait dans les courriers générés par l'interface (ex: "Strasbourg, le <?php echo date_fr("j F Y") ?>"
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__VILLE]' value='<?php echo htmlspecialchars(stripslashes($ville), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Texte "Informatique et Libertés"</strong>
               <br>Texte informant les candidats sur leurs droits concernant les données informatiques.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <textarea name='config[__INFORMATIQUE_ET_LIBERTES]' cols='60' rows='7'><?php echo htmlspecialchars(stripslashes($informatique_liberte), ENT_QUOTES, $default_htmlspecialchars_encoding); ?></textarea>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
            <font class='Texte_menu2'><strong>Création d'une composante : paramètres par défaut</strong></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Décisions par défaut</strong>
               <br>Détermine si les décisions (admis, refus, ...) par défaut doivent être rattachées lors de la création d'une composante. Si elles ne sont pas rattachées,
               elles devront être sélectionnées individuellement via le menu "Décisions Utilisées".
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <input type='radio' name='config[__DEFAUT_DECISIONS]' value='t' <?php echo $dd_yes; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Rattacher les décisions</font>
            <br><input type='radio' name='config[__DEFAUT_DECISIONS]' value='f' <?php echo $dd_no; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Ne pas rattacher les décisions</font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Gestion des motifs de refus prédéfinis</strong>
               <br>Détermine si les motifs par défaut doivent être rattachés lors de la création d'une composante. Si les motifs ne sont pas
               rattachés, ils devront être créés via le menu "Motifs de refus ou de mises en attente".
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <input type='radio' name='config[__DEFAUT_MOTIFS]' value='t' <?php echo $dm_yes; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Rattacher les motifs prédéfinis</font>
            <br><input type='radio' name='config[__DEFAUT_MOTIFS]' value='f' <?php echo $dm_no; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Ne pas rattacher les motifs prédéfinis</font>
         </td>
      </tr>
      <tr>
         <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
            <font class='Texte_menu2'><strong>Divers</strong></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Formulaire d'assistance aux candidats</strong>
               <br>Sur la partie candidat, le pied de page peut donner accès à un formulaire d'aide incluant divers conseils et procédures automatisées.
               <br>Si le formulaire est désactivé, le pied de page redirigera vers l'adresse électronique de l'administrateur.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <input type='radio' name='config[__ASSISTANCE]' value='t' <?php echo $assistance_yes; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Activer l'assistance</font>
            <br><input type='radio' name='config[__ASSISTANCE]' value='f' <?php echo $assistance_no; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Désactiver l'assistance</font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Mois délimitant deux années universitaires</strong>
               <br>Une fois la limite atteinte (1er jour du mois choisi), l'interface sélectionne automatiquement l'année universitaire suivante.
               <br>Si des sessions sont encore ouvertes pour l'année en cours, elles seront toujours disponibles jusqu'à leur fermeture
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <select name='config[__MOIS_LIMITE_CANDIDATURE]'>
                  <option value='01' <?php if($mois_limite_candidatures=="01") echo "selected"; ?>>Janvier</option>
                  <option value='02' <?php if($mois_limite_candidatures=="02") echo "selected"; ?>>Février</option>
                  <option value='03' <?php if($mois_limite_candidatures=="03") echo "selected"; ?>>Mars</option>
                  <option value='04' <?php if($mois_limite_candidatures=="04") echo "selected"; ?>>Avril</option>
                  <option value='05' <?php if($mois_limite_candidatures=="05") echo "selected"; ?>>Mai</option>
                  <option value='06' <?php if($mois_limite_candidatures=="06") echo "selected"; ?>>Juin</option>
                  <option value='07' <?php if($mois_limite_candidatures=="07") echo "selected"; ?>>Juillet</option>
                  <option value='08' <?php if($mois_limite_candidatures=="08") echo "selected"; ?>>Août</option>
                  <option value='09' <?php if($mois_limite_candidatures=="09") echo "selected"; ?>>Septembre</option>
                  <option value='10' <?php if($mois_limite_candidatures=="10") echo "selected"; ?>>Octobre</option>
                  <option value='11' <?php if($mois_limite_candidatures=="11") echo "selected"; ?>>Novembre</option>
                  <option value='12' <?php if($mois_limite_candidatures=="12") echo "selected"; ?>>Décembre</option>
               </select>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Gestion de masse : nombre de pages maximum dans les PDF</strong>
               <br>Si le nombre de pages dépasse cette limite, le document PDF sera scindé en plusieurs parties.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__MAX_CAND_MASSE]' value='<?php echo $max_cand_masse; ?>' size='60' maxlength='4'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Nombre de rappels automatiques pour une fiche incomplète</strong>
               <br>Lorsque des "Renseignements complémentaires" (ajoutés via le constructeur de dossiers) obligatoires ne sont pas complétés par un candidat, la formation
               n'est pas verrouillée sur sa fiche à la date prévue et un rappel lui est envoyé via la messagerie. Ce paramètre détermine le nombre de rappels maximum à
               envoyer.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__MAX_RAPPELS]' value='<?php echo $max_rappels; ?>' size='60' maxlength='3'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Nombre de jours entre deux rappels</strong>
               <br>Lorsqu'un rappel est envoyé, la date de verrouillage de la formation est repoussée sur la fiche du candidat. Ce paramètre détermine le nombre de jours
               à ajouter à la date de verrouillage prévue entre chaque rappel.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__AJOUT_VERROUILLAGE_JOURS]' value='<?php echo $ajout_verrouillage_jours; ?>' size='' maxlength=''>
            </font>
         </td>
      </tr>
      <?php
               break;

         case 5 : if(isset($new_erreur_sujet))
                     $erreur_sujet=$new_erreur_sujet;
                  elseif(isset($GLOBALS["__ERREUR_SUJET"]))
                     $erreur_sujet=$GLOBALS["__ERREUR_SUJET"];
                  else
                     $erreur_sujet="[Erreur Aria]";

                  if(isset($new_debug_sujet))
                     $debug_sujet=$new_debug_sujet;
                  elseif(isset($GLOBALS["__DEBUG_SUJET"]))
                     $debug_sujet=$GLOBALS["__DEBUG_SUJET"];
                  else
                     $debug_sujet="[Debug Aria]";

                  if(isset($new_debug))
                     $debug=$new_debug;
                  elseif(isset($GLOBALS["__DEBUG"]))
                     $debug=$GLOBALS["__DEBUG"];
                  else
                     $debug="t";

                  if($debug=="t")
                  {
                     $debug_yes="checked";
                     $debug_no="";
                  }
                  else
                  {
                     $debug_yes="";
                     $debug_no="checked";
                  }

                  if(isset($new_debug_id))
                     $debug_id=$new_debug_id;
                  elseif(isset($GLOBALS["__DEBUG_RAPPEL_IDENTIFIANTS"]))
                     $debug_id=$GLOBALS["__DEBUG_RAPPEL_IDENTIFIANTS"];
                  else
                     $debug_id="f";

                  if($debug_id=="t")
                  {
                     $debug_id_yes="checked";
                     $debug_id_no="";
                  }
                  else
                  {
                     $debug_id_yes="";
                     $debug_id_no="checked";
                  }

                  if(isset($new_debug_cursus))
                     $debug_cursus=$new_debug_cursus;
                  elseif(isset($GLOBALS["__DEBUG_CURSUS"]))
                     $debug_cursus=$GLOBALS["__DEBUG_CURSUS"];
                  else
                     $debug_cursus="f";

                  if($debug_cursus=="t")
                  {
                     $debug_cursus_yes="checked";
                     $debug_cursus_no="";
                  }
                  else
                  {
                     $debug_cursus_yes="";
                     $debug_cursus_no="checked";
                  }

                  if(isset($new_debug_prec))
                     $debug_prec=$new_debug_prec;
                  elseif(isset($GLOBALS["__DEBUG_STATUT_PREC"]))
                     $debug_prec=$GLOBALS["__DEBUG_STATUT_PREC"];
                  else
                     $debug_prec="f";

                  if($debug_prec=="t")
                  {
                     $debug_prec_yes="checked";
                     $debug_prec_no="";
                  }
                  else
                  {
                     $debug_prec_yes="";
                     $debug_prec_no="checked";
                  }

                  if(isset($new_debug_reg))
                     $debug_reg=$new_debug_reg;
                  elseif(isset($GLOBALS["__DEBUG_ENREGISTREMENT"]))
                     $debug_reg=$GLOBALS["__DEBUG_ENREGISTREMENT"];
                  else
                     $debug_reg="t";

                  if($debug_reg=="t")
                  {
                     $debug_reg_yes="checked";
                     $debug_reg_no="";
                  }
                  else
                  {
                     $debug_reg_yes="";
                     $debug_reg_no="checked";
                  }

                  if(isset($new_debug_lock))
                     $debug_lock=$new_debug_lock;
                  elseif(isset($GLOBALS["__DEBUG_LOCK"]))
                     $debug_lock=$GLOBALS["__DEBUG_LOCK"];
                  else
                     $debug_lock="f";

                  if($debug_lock=="t")
                  {
                     $debug_lock_yes="checked";
                     $debug_lock_no="";
                  }
                  else
                  {
                     $debug_lock_yes="";
                     $debug_lock_no="checked";
                  }
      ?>

      <tr>
         <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
            <font class='Texte_menu2'><strong>Courriels d'erreur envoyés à l'administrateur</strong></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; width:50%; white-space:normal;'>
            <font class='Texte_menu'><strong>Préfixe des courriels d'erreurs</strong></font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__ERREUR_SUJET]' value='<?php echo htmlspecialchars(stripslashes($erreur_sujet), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='256'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
            <font class='Texte_menu2'>
               <strong>Courriels de contrôle envoyés à l'administrateur (<i>debug</i>)</strong>
               <br><strong>Attention :</strong> l'activation de ces paramètres peut générer de nombreux messages !
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'><strong>Préfixe des courriels de contrôle</strong></font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <font class='Texte_menu'>
               <input type='text' name='config[__DEBUG_SUJET]' value='<?php echo htmlspecialchars(stripslashes($debug_sujet), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='256'>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Activation globale des courriels de contrôle</strong>
               <br>Ce paramètre détermine si des courriels sont envoyés à l'administrateur (erreurs, avertissements, informations diverses, ...).
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <input type='radio' name='config[__DEBUG]' value='t' <?php echo $debug_yes; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Activer</font>
            <br><input type='radio' name='config[__DEBUG]' value='f' <?php echo $debug_no; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Désactiver</font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Demande de renvoi des identifiants</strong>
               <br>Si ce paramètre est activé, un courriel est envoyé à l'administrateur lorsqu'un candidat demande le rappel de ses identifiants.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <input type='radio' name='config[__DEBUG_RAPPEL_IDENTIFIANTS]' value='t' <?php echo $debug_id_yes; ?>  style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Activer</font>
            <br><input type='radio' name='config[__DEBUG_RAPPEL_IDENTIFIANTS]' value='f' <?php echo $debug_id_no; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Désactiver</font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Statut du cursus</strong>
               <br>Si ce paramètre est activé, un courriel est envoyé à l'administrateur lorsqu'un gestionnaire modifie le statut du cursus d'un candidat.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <input type='radio' name='config[__DEBUG_CURSUS]' value='t' <?php echo $debug_cursus_yes; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Activer</font>
            <br><input type='radio' name='config[__DEBUG_CURSUS]' value='f' <?php echo $debug_cursus_no; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Désactiver</font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Statut des candidatures</strong>
               <br>Si ce paramètre est activé, un courriel est envoyé à l'administrateur lorsqu'un gestionnaire modifie le statut d'une précandidature.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <input type='radio' name='config[__DEBUG_STATUT_PREC]' value='t' <?php echo $debug_prec_yes; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Activer</font>
            <br><input type='radio' name='config[__DEBUG_STATUT_PREC]' value='f' <?php echo $debug_prec_no; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Désactiver</font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Enregistrement</strong>
               <br>Si ce paramètre est activé, un courriel est envoyé à l'administrateur lorsqu'un candidat s'enregistre sur l'interface.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <input type='radio' name='config[__DEBUG_ENREGISTREMENT]' value='t' <?php echo $debug_reg_yes; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Activer</font>
            <br><input type='radio' name='config[__DEBUG_ENREGISTREMENT]' value='f' <?php echo $debug_reg_no; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Désactiver</font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' style='padding:4px; white-space:normal;'>
            <font class='Texte_menu'>
               <strong>Verrouillage des formations</strong>
               <br>Si ce paramètre est activé, un message interne est envoyé à l'administrateur lorsque le voeu d'un candidat est verrouillé.
            </font>
         </td>
         <td class='td-droite fond_menu' style='padding:4px;'>
            <input type='radio' name='config[__DEBUG_LOCK]' value='t' <?php echo $debug_lock_yes; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Activer</font>
            <br><input type='radio' name='config[__DEBUG_LOCK]' value='f' <?php echo $debug_lock_no; ?> style='vertical-align:middle; margin:0px 8px 0px 0px;'><font class='Texte_menu'>Désactiver</font>
         </td>
      </tr>
      <?php
         break;
      }

      db_close($dbr);
      ?>
      </table>

      <div class='centered_box' style='padding-top:20px;'>
         <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="Valider" value="Valider">
      </div>

      </form>
   </div>
</div>
<?php
   pied_de_page();
?>
