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
   include "$__INCLUDE_DIR_ABS/fonctions_ldap.php";


   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   verif_auth("$__GESTION_DIR/login.php");

   if($_SESSION['niveau']!=$__LVL_ADMIN)
   {
      session_write_close();
      header("Location:$__MOD_DIR/gestion/noaccess.php");
      exit();
   }

   // Ajout, Modification ou suppression
   if(array_key_exists("a", $_GET) && ctype_digit($_GET["a"]))
      $_SESSION["ajout_user"]=$_GET["a"]==1 ? 1 : 0;
   elseif(!isset($_SESSION["ajout_user"]))
      $_SESSION["ajout_user"]=0;

   if(array_key_exists("s", $_GET) && ctype_digit($_GET["s"]))
      $_SESSION["suppression"]=$_GET["s"]==1 ? 1 : 0;
   elseif(!isset($_SESSION["suppression"]))
      $_SESSION["suppression"]=0;

   if(array_key_exists("m", $_GET) && ctype_digit($_GET["m"]))
      $_SESSION["modification"]=$_GET["m"]==1 ? 1 : 0;
   elseif(!isset($_SESSION["modification"]))
      $_SESSION["modification"]=0;

   $dbr=db_connect();

   // Sélection de la source
   if((isset($_POST["valider_source"]) || isset($_POST["valider_source_x"])) && isset($_POST["source"]))
   {
      $_SESSION["source"]=trim($_POST["source"]);
   }
   // Recherche LDAP
   elseif((isset($_POST["rechercher"]) || isset($_POST["rechercher_x"])) && isset($_POST["recherche_ldap"]))
   {
      if(!empty($_POST["recherche_ldap"]))
      {
         $_SESSION["current_recherche_ldap"]=trim($_POST["recherche_ldap"]);
         
         $ldap_conn=aria_ldap_connect();
         
         if($ldap_conn!=-1)
         {
            $_SESSION["resultat_recherche_ldap"]=recherche_individu_ldap($ldap_conn, $_SESSION["current_recherche_ldap"]);
            
            aria_ldap_close($ldap_conn);
         }
         else
            $erreur_ldap=1;
      }
   }
   elseif((isset($_POST["selectionner"]) || isset($_POST["selectionner_x"])) && isset($_POST["selection_ldap"]) && isset($_SESSION["resultat_recherche_ldap"]))
   {
      if(array_key_exists($_POST["selection_ldap"], $_SESSION["resultat_recherche_ldap"]))
      {
         if(array_key_exists("nom", $_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]))
            $new_nom=$_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]["nom"];
            
         if(array_key_exists("prenom", $_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]))
            $new_prenom=$_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]["prenom"];
            
         if(array_key_exists("login", $_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]))
            $new_login=$_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]["login"];
         
         if(array_key_exists("mail", $_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]))
            $new_mail=$_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]["mail"];
         /*
         if(array_key_exists("pass", $_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]))
            $_SESSION["new_pass_ldap"]=$_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]["pass"];
         */   
         // print_r($_SESSION["resultat_recherche_ldap"][$_POST["selection_ldap"]]["pass"]);
         
         // $_SESSION["source"]=$GLOBALS["__COMPTE_LDAP"];
      }
   }
   elseif((isset($_POST["modifier"]) || isset($_POST["modifier_x"])) && array_key_exists("user_id", $_POST) && ctype_digit($_POST["user_id"]))
   {
      $user_id=$_POST["user_id"];
      $_SESSION["modification"]=1;
   }
   elseif((isset($_POST["supprimer"]) || isset($_POST["supprimer_x"])) && array_key_exists("user_id", $_POST) && ctype_digit($_POST["user_id"]))
   {
      $user_id=$_POST["user_id"];
      $_SESSION["suppression"]=1;
   }
   elseif((isset($_POST["modifier_recherche"]) || isset($_POST["modifier_recherche_x"])) && array_key_exists("user_id_recherche", $_POST) && ctype_digit($_POST["user_id_recherche"]))
   {
      $user_id=$_POST["user_id_recherche"];
      $_SESSION["modification"]=1;
   }
   elseif((isset($_POST["supprimer_recherche"]) || isset($_POST["supprimer_recherche_x"])) && array_key_exists("user_id_recherche", $_POST) && ctype_digit($_POST["user_id_recherche"]))
   {
      $user_id=$_POST["user_id_recherche"];
      $_SESSION["suppression"]=1;
   }
   elseif(isset($_POST["conf_supprimer"]) || isset($_POST["conf_supprimer_x"]))
   {
      $user_id=$_POST["user_id"];

      if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_acces WHERE $_DBC_acces_id='$user_id'"))==1)
      {
         db_query($dbr,"DELETE FROM $_DB_acces WHERE $_DBC_acces_id='$user_id'");

         // Sous répertoire de la messagerie
         $sous_rep=sous_rep_msg($user_id);

         if(is_dir("$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$user_id") && is_writable("$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$user_id"))
            deltree("$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$user_id");

         $succes=1;
      }
      else
      {
         header("Location:$php_self?erreur_suppr=1");
         db_close($dbr);
         exit();
      }
   }
   elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
   {
      if(isset($_POST["user_id"]))
         $user_id=$_POST["user_id"];

      $new_nom=strtoupper(trim($_POST["nom"]));
      $new_prenom=trim($_POST["prenom"]);
      $new_login=strtolower(trim($_POST["login"]));
      $new_mail=trim($_POST["email"]);
      $new_composante=$_POST["composante"];
      $new_niveau=$_POST["niveau"];
      $new_reception_msg=$_POST["reception_msg"];
      $new_reception_msg_sys=$_POST["reception_msg_sys"];

      if($_SESSION["source"]==$GLOBALS["__COMPTE_MANUEL"])
      {
         $new_pass=array_key_exists("pass", $_POST) ? $_POST["pass"] : "";
         $new_pass_conf=array_key_exists("conf_pass", $_POST) ? $_POST["conf_pass"] : "";
      }
      
      if($new_nom=="" || $new_login=="" || $new_mail=="")
      {
         $_SESSION["modification"]=1;
         $champs_vides=1;
      }

      if($_SESSION["source"]==$GLOBALS["__COMPTE_MANUEL"])
      {
         // Ajout d'utilisateur et passe vide : on génère un nouveau pass
         if($_SESSION["ajout_user"]==1 && $new_pass=="" && $new_pass_conf=="")
         {
            if($new_pass=="" && $new_pass_conf=="")
            {
               $warn_pass_vide=1;
               srand((double)microtime()*1000000);
               $code_conf=mb_strtoupper(md5(rand(0,9999)), "UTF-8");
               $new_pass=substr($code_conf, 17, 8);
               // on supprime le chiffre 1, le zéro et la lettre O : portent à confusion - on les remplace par d'autres caractères
               $new_pass=str_replace("0","A", $new_pass);
               $new_pass=str_replace("O","H", $new_pass);
               $new_pass=str_replace("1","P", $new_pass);

               $new_pass_conf=$new_pass;
            }
         }

         // Tests : les deux pass sont identiques, et pas trop courts
         if($new_pass!="" && $new_pass==$new_pass_conf && strlen($new_pass)<7)
            $erreur_pass_court=1;   
         elseif($new_pass!=$new_pass_conf)   
            $erreur_pass_match=1;

         if(!isset($erreur_pass_court) && !isset($erreur_pass_match) && $new_pass!="")
         {
            $md5_pass=md5($new_pass);
            $update_pass=",$_DBU_acces_pass='$md5_pass'";
         }
         else
            $update_pass="";
      }

      // récupération des valeurs courantes, en cas de modification
      if($_SESSION["ajout_user"]==0 && isset($user_id))
      {
         $result=db_query($dbr,"SELECT $_DBC_acces_login, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel, 
                                       $_DBC_acces_niveau, $_DBC_acces_reception_msg_scol, $_DBC_acces_reception_msg_systeme, 
                                       $_DBC_acces_composante_id, $_DBC_acces_source
                                    FROM $_DB_acces
                                 WHERE $_DBC_acces_id='$user_id'");
         $rows=db_num_rows($result);

         if(!$rows)
         {
            $_SESSION["modification"]=1;
            $user_id_existe_pas=1;
         }
         else
         {
            list($current_login,$current_nom,$current_prenom,$current_mail, $current_niveau, $current_reception_msg_scol, 
                 $current_reception_msg_systeme, $current_comp_id, $current_source)=db_fetch_row($result,0);

            db_free_result($result);

            if(strcasecmp($current_nom,$new_nom) || strcasecmp($current_prenom, $new_prenom))
            {
               if(db_num_rows(db_query($dbr,"SELECT $_DBC_acces_id FROM $_DB_acces
                                             WHERE $_DBC_acces_nom ILIKE '".preg_replace("/[']+/", "''", stripslashes($new_nom))."'
                                             AND $_DBC_acces_prenom ILIKE '".preg_replace("/[']+/", "''", stripslashes($new_prenom))."'
                                             AND $_DBC_acces_id!='$user_id'")))
                  $nom_existe=1;
               else
                  $nom_existe=0;
            }
         }

         // On vérifie si le login n'existe pas déjà (s'il a été modifié)
         if(db_num_rows(db_query($dbr,"SELECT $_DBC_acces_id FROM $_DB_acces
                                       WHERE $_DBC_acces_login ILIKE '".preg_replace("/[']+/", "''", stripslashes($new_login))."'
                                       AND $_DBC_acces_id!='$user_id'")))
            $login_existe=1;
      }
      else // Ajout d'un nouvel utilisateur
      {
         if(db_num_rows(db_query($dbr,"SELECT $_DBC_acces_id FROM $_DB_acces
                                       WHERE $_DBC_acces_nom ILIKE '".preg_replace("/[']+/", "''", stripslashes($new_nom))."'
                                       AND $_DBC_acces_prenom ILIKE '".preg_replace("/[']+/", "''", stripslashes($new_prenom))."'")))
            $nom_existe=1;
         else
            $nom_existe=0;

         if(db_num_rows(db_query($dbr,"SELECT $_DBC_acces_id FROM $_DB_acces WHERE $_DBC_acces_login ILIKE '".preg_replace("/[']+/", "''", stripslashes($new_login))."'")))
            $login_existe=1;
      }

      if(!isset($champs_vides) && !isset($pass_dont_match) && !isset($login_existe) && !isset($pass_vide) && !isset($erreur_pass_court) && !isset($erreur_pass_match))
      {
         if($_SESSION["ajout_user"]==0 && isset($user_id))
            db_query($dbr,"UPDATE $_DB_acces SET   
                $_DBU_acces_login='$new_login',
                $_DBU_acces_nom='".preg_replace("/[']+/", "''", stripslashes($new_nom))."',
                $_DBU_acces_prenom='".preg_replace("/[']+/", "''", stripslashes($new_prenom))."',
                $_DBU_acces_niveau='$new_niveau',
                $_DBU_acces_reception_msg_scol='$new_reception_msg',
                $_DBU_acces_reception_msg_systeme='$new_reception_msg_sys',
                $_DBU_acces_composante_id='$new_composante',
                $_DBU_acces_courriel='$new_mail'$update_pass
                WHERE $_DBU_acces_id='$user_id'");
         else
         {
            // Valeurs par défaut :
            $default_filtre=$default_absence_message=$default_signature_texte="";
            $default_absence_active='f';
            $default_signature_active='t';
            $default_absence_debut=$default_absence_fin="0";

            $new_source=isset($_SESSION["source"]) ? $_SESSION["source"] : $GLOBALS["__COMPTE_MANUEL"];

            $user_id=db_locked_query($dbr, $_DB_acces, "INSERT INTO $_DB_acces VALUES(
                '##NEW_ID##', 
                '$new_composante', 
                '".preg_replace("/[']+/", "''", stripslashes($new_nom))."', 
                '".preg_replace("/[']+/", "''", stripslashes($new_prenom))."', 
                '$new_login', 
                '$md5_pass', 
                '$new_mail', 
                '$new_niveau',
                '$default_filtre', 
                '$new_reception_msg',
                '$default_absence_debut',
                '$default_absence_fin',
                '".preg_replace("/[']+/", "''", stripslashes($default_absence_message))."',
                '$default_absence_active',
                '".preg_replace("/[']+/", "''", stripslashes($default_signature_texte))."',
                '$default_signature_active',
                '$new_reception_msg_sys',
                '$new_source')");

            db_query($dbr,"INSERT INTO $_DB_acces_comp VALUES('$user_id', '$new_composante');");

            // mail
            $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_NOREPLY]\r\nReply-To: $GLOBALS[__EMAIL_NOREPLY]\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";
            
            if($new_source==$GLOBALS["__COMPTE_LDAP"])
               $corps_message="\nBonjour, \n\n\nUn compte vous a été créé sur l'interface de Gestion des Candidatures en ligne.\n\nLes identifiants vous permettant d'y accéder sont identiques à ceux de l'ENT. \n\n- Adresse de l'application : $__URL_GESTION\n\n\nCordialement,\n\n\n--\n$__SIGNATURE_ADMIN";
            elseif($new_source==$GLOBALS["__COMPTE_MANUEL"])
               $corps_message="\nBonjour, \n\n\nUn compte vous a été créé sur l'interface de Gestion des Candidatures en ligne.\n\nLes informations vous permettant d'y accéder sont les suivantes : \n\n- Adresse : $__URL_GESTION\n\n- Identifiant : ". stripslashes($new_login) . "\n- Code Personnel : $new_pass\n\n\nCordialement,\n\n\n--\n$__SIGNATURE_ADMIN";
               
            $ret=mail($new_mail,"[Aria - Gestion des Candidatures] - Enregistrement", $corps_message, $headers);
         }

         $succes=1;
      
      
         // Renvoi des informations à l'utilisateur
         // Attention : le pass est automatiquement changé (non conservé en clair)

         if(isset($_POST["renvoyer"]) && $_POST["renvoyer"]==1 && $_SESSION["ajout_user"]==0 && isset($user_id) && isset($new_mail) && isset($new_login))
         {
            if(!isset($md5_pass) && $new_pass=="" && $new_pass_conf=="")
            {
               srand((double)microtime()*1000000);
               $code_conf=mb_strtoupper(md5(rand(0,9999)), "UTF-8");
               $new_pass=substr($code_conf, 17, 8);
               // on supprime le chiffre 1, le zéro et la lettre O : portent à confusion - on les remplace par d'autres caractères
               $new_pass=str_replace("0","A", $new_pass);
               $new_pass=str_replace("O","H", $new_pass);
               $new_pass=str_replace("1","P", $new_pass);

               $md5_pass=md5($new_pass);
            }

            // mail
            $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_NOREPLY]\r\nReply-To: $GLOBALS[__EMAIL_NOREPLY]\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";
            $corps_message="\nBonjour, \n\n\nLes informations pour vous connecter à l'interface ARIA de Gestion des Candidatures en ligne sont les suivantes : \n\n- Adresse : $__URL_GESTION\n\n-Identifiant : ". stripslashes($new_login) . "\n- Code Personnel : $new_pass\n\n\nCordialement,\n\n\n--\n$__SIGNATURE_ADMIN";
            $ret=mail($new_mail,"[Aria - Gestion des Candidatures] - Vos identifiants", $corps_message, $headers);

            db_query($dbr,"UPDATE $_DB_acces SET $_DBU_acces_pass='$md5_pass' WHERE $_DBU_acces_id='$user_id'");

            $succes_renvoi=1;      
         }

      if((isset($_POST["copie_messages"]) && $_POST["copie_messages"]=="1" && isset($_POST["copie_messages_user_id"]) && ctype_digit($_POST["copie_messages_user_id"]))
         || (isset($_POST["marquer_lus"]) && $_POST["marquer_lus"]=="1"))
      {
        // Il faut modifier l'entête de chaque message non lu et le copier dans le répertoire cible avec les bonnes informations
        
        // TODO : écrire des fonctions pour l'accès aux messages et à leurs paramètres

        $sous_rep_user=sous_rep_msg($_SESSION[auth_id]);
        
        if(isset($_POST["copie_messages_user_id"]))
        {
          $destinataire_id=$_POST["copie_messages_user_id"];
          $sous_rep_destinataire=sous_rep_msg($destinataire_id);
        }
        
        if(is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$sous_rep_user/$_SESSION[auth_id]/"))
        {
           if(FALSE!==($contenu_repertoire=scandir("$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$sous_rep_user/$_SESSION[auth_id]/$__MSG_INBOX", 1)))
          {
            if(FALSE!==($key=array_search(".", $contenu_repertoire)))
                 unset($contenu_repertoire[$key]);

              if(FALSE!==($key=array_search("..", $contenu_repertoire)))
                unset($contenu_repertoire[$key]);

                if(FALSE!==($key=array_search("index.php", $contenu_repertoire)))
                 unset($contenu_repertoire[$key]);

            foreach($contenu_repertoire as $nom_fichier)
            {
              $path="$__GESTION_MSG_STOCKAGE_DIR_ABS/$sous_rep_user/$_SESSION[auth_id]/$__MSG_INBOX/$nom_fichier";
              $file_or_dir_name=$nom_fichier;
              
              if(is_dir($path)) // Répertoire : message avec pièce(s) jointe(s)
                 {
                   // On regarde le contenu du répertoire. Normalement, le message a le même nom que ce dernier, terminé par .0 ou .1
                   if(is_file("$path/$nom_fichier.0"))
                {
                  $nom_complet="$path/$nom_fichier.0";
                  $nom_fichier="$file_or_dir_name.0";
                }
                elseif(is_file("$path/$nom_fichier.1"))
                {
                  $nom_complet="$path/$nom_fichier.1";
                  $nom_fichier="$file_or_dir_name.1";
                }
              }
              else
                 $nom_complet=$path;

              // Identifiant du message = date
              // Format : AA(1 ou 2) MM JJ HH Mn SS µS(5)

              if(strlen($nom_fichier)==18) // Année sur un caractère (16 pour l'identifiant + ".0" ou ".1" pour le flag "read")
              {
                $date_offset=0;
                $annee_len=1;
                $leading_zero="0";
                $msg_id=substr($nom_fichier, 0, 16);
                $msg_read=substr($nom_fichier, 17, 1);
              }
              else // Année sur 2 caractères (chaine : 19 caractères)
              {
                $date_offset=1;
                $annee_len=2;
                $leading_zero="";
                $msg_id=substr($nom_fichier, 0, 17);
                $msg_read=substr($nom_fichier, 18, 1);
              }
              
              if(!$msg_read)
              {
                 if(($array_file=file("$nom_complet"))==FALSE)
                {
                  mail($__EMAIL_ADMIN, "[Précandidatures] - Erreur d'ouverture de mail", "Fichier : $nom_complet\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");

                  die("Erreur d'ouverture du fichier. Un message a été envoyé à l'administrateur.");
                }

                $msg_exp_id=$array_file["0"];
                $msg_exp=$array_file["1"];
                $msg_to_id=$array_file["2"];
                $msg_to=$array_file["3"];
                $msg_sujet=stripslashes($array_file["4"]);
        
                // On ne transfère pas les messages système non lus
                if($msg_exp_id!="0")
                {
/*        
                  $res_from=db_query($dbr,"SELECT $_DBC_candidat_nom, $_DBC_candidat_prenom
                                                      FROM $_DB_candidat
                                                   WHERE $_DBC_candidat_id='$msg_exp_id'");

                    if(db_num_rows($res_from))
                    {
                        $array_from=array("id"    => $msg_exp_id);

                        list($array_from["nom"], $array_from["prenom"])=db_fetch_row($res_from, 0);

                        $array_dest=array("0" => array("id"    => $destinataire_id));

                    echo "copie : $nom_fichier => $destinataire_id/\n<br>";
                      $retour_copie=copy_msg("", $__MSG_INBOX, $nom_fichier, $destinataire_id);
                   }

                     db_free_result($res_from);
*/                  

                    $retour_copie=copy_msg("", $__MSG_INBOX, $file_or_dir_name, $destinataire_id);
                }
                
                if(isset($_POST["marquer_lus"]) && $_POST["marquer_lus"]==1)
                {
                   if(is_dir($path))
                  {
                    // Attention : il faut bien tenir compte du répertoire
                        rename("$nom_complet", "$__GESTION_MSG_STOCKAGE_DIR_ABS/$sous_rep_user/$_SESSION[auth_id]/$__MSG_INBOX/$msg_id/$msg_id".".1");
                  }
                   else
                  {
                    rename("$nom_complet", "$__GESTION_MSG_STOCKAGE_DIR_ABS/$sous_rep_user/$_SESSION[auth_id]/$__MSG_INBOX/$msg_id".".1");
                  }
                }
              }
            }
          }
         }
      }
      
      if(isset($_POST["marquer_lus"]) && $_POST["marquer_lus"]=="1")
      {
      }
      }
   }
  elseif((isset($_POST["recherche"]) || isset($_POST["recherche_x"])) && trim($_POST["recherche_nom"])!="")
  {
     $recherche=1;
    $nom_recherche=trim($_POST["recherche_nom"]);
  }
  elseif(isset($_POST["clear_form"]) || isset($_POST["clear_form_x"]))
  {
  }
   else
   {
      unset($_SESSION["resultat_recherche_ldap"]);
      unset($_SESSION["current_recherche_ldap"]);
      unset($_SESSION["source"]);
   }
   
   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>

<div class='main'>
   <?php
      if($_SESSION["ajout_user"]==1)
         titre_page_icone("Ajouter un utilisateur", "contacts_32x32_fond.png", 30, "L");
      elseif(isset($_SESSION["action"]) && $_SESSION["action"]=="modification")
         titre_page_icone("Modifier un utilisateur existant", "edit_32x32_fond.png", 30, "L");
      elseif(isset($_SESSION["action"]) && $_SESSION["action"]=="suppression")
         titre_page_icone("Supprimer un utilisateur", "trashcan_full_34x34_slick_fond.png", 30, "L");
      else
         titre_page_icone("Gestion des utilisateurs", "contacts_32x32_fond.png", 30, "L");

      // Messages d'erreur et de succès

      if(isset($user_id_existe_pas) || isset($_GET["erreur_suppr"]))
         message("Erreur : l'identifiant demandé est incorrect (problème de cohérence de la base ?)", $__ERREUR);

      if(isset($champs_vides))
         message("Erreur : les champs en <b>gras</b> sont <b>obligatoires</b>.", $__ERREUR);

      if(isset($pass_vide))
         message("Erreur : le mot de passe ne peut pas être vide.", $__ERREUR);

      if(isset($login_existe))
         message("Erreur : cet identifiant est déjà utilisé.", $__ERREUR);

      if(isset($pass_dont_match))
         message("Erreur : les mots de passe sont différents.", $__ERREUR);

      if(isset($nom_existe) && $nom_existe==1)
         message("Attention : couple nom/prénom déjà présent dans la base.", $__WARNING);

      if(isset($erreur_ldap) && $erreur_ldap==1)
         message("Erreur lors de la recherche LDAP", $__ERREUR);

      if(isset($erreur_pass_court) && $erreur_pass_court==1)
         message("Erreur : le mot de passe entré est trop court (7 caractères minimum)", $__ERREUR);

      if(isset($erreur_pass_match) && $erreur_pass_match==1)
         message("Erreur : les mots de passe sont différents", $__ERREUR);       

      if(isset($succes) && $succes==1)
      {
         if($_SESSION["modification"]==1)
         {
            message("L'utilisateur a été modifié avec succès.", $__SUCCES);
            $_SESSION["modification"]=0;
            unset($_SESSION["source"]);
         }
         elseif($_SESSION["ajout_user"]==1)
         {
            if(isset($warn_pass_vide) && $warn_pass_vide==1)
               $warn_txt="<br><strong>Un mot de passe aléatoire a été généré pour l'utilisateur.</strong>";
            else
               $warn_txt="<br><strong>Le mot de passe entré lui a été attribué.</strong>";

            message("L'utilisateur a été créé avec succès.$warn_txt", $__SUCCES);

            $_SESSION["ajout_user"]=0;
            unset($_SESSION["source"]);
         }
         elseif($_SESSION["suppression"]==1)
         {
            message("L'utilisateur a été supprimé avec succès.", $__SUCCES);
            $_SESSION["suppression"]=0;
            unset($_SESSION["source"]);
         }
      }
    
      if(isset($succes_renvoi) && $succes_renvoi==1)
      {
         message("Informations envoyées à l'utilisateur avec succès.", $__SUCCES);
         $_SESSION["modification"]=0;
         unset($_SESSION["source"]);
      }

      print("<form action='$php_self' method='POST' name='form1'>\n");

      if($_SESSION["ajout_user"]==0 && $_SESSION["modification"]==0 && $_SESSION["suppression"]==0)  // Choix de l'utilisateur à modifier
      {
      if(!isset($recherche))
         $nom_recherche="";
      
         print("<table cellpadding='4' align='center'>
                <tr>
                  <td class='fond_menu2' align='right'>
                     <font class='Texte_menu2' style='font-weight:bold;'>Recherche par nom / identifiant : </font>
                  </td>
                  <td class='fond_menu'>
               <input type='text' name='recherche_nom' value=\"".stripslashes($nom_recherche)."\" maxlength='30' size='30'>
            </td>
                  <td class='fond_menu' style='text-align:center;'>");
            
      if(isset($nom_recherche) && trim($nom_recherche!=""))
         print("<input type='image' src='$__ICON_DIR/cancel_16x16_menu.png' alt='Effacer le formulaire' name='clear_form' value='Effacer le formulaire'>");
            
       print("     <input type='image' src='$__ICON_DIR/forward_16x16_menu.png' alt='Rechercher' name='recherche' value='Rechercher'>
            </td>
           </tr>");

      $critere_recherche=isset($nom_recherche) && trim($nom_recherche!="") ? "AND ($_DBC_acces_nom ILIKE '".preg_replace("/[']+/", "''", stripslashes($nom_recherche))."%"."' 
            OR $_DBC_acces_login ILIKE '".preg_replace("/[']+/", "''", stripslashes($nom_recherche))."%"."')" : "";
      
      if(isset($nom_recherche) && trim($nom_recherche!=""))
      {   
         $result_recherche=db_query($dbr, "(SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                                      $_DBC_acces_prenom as aprenom, $_DBC_acces_login, '0' as cnom
                                                  FROM $_DB_acces
                                               WHERE $_DBC_acces_niveau IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]')
                                               AND ($_DBC_acces_nom ILIKE '".preg_replace("/[']+/", "''", stripslashes($nom_recherche))."%"."' 
                                                    OR $_DBC_acces_login ILIKE '".preg_replace("/[']+/", "''", stripslashes($nom_recherche))."%"."')
                                              )
                                             UNION   
                                                (SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                                        $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
                                                   FROM $_DB_acces, $_DB_composantes
                                                WHERE $_DBC_acces_composante_id=$_DBC_composantes_id
                                                AND $_DBC_acces_niveau NOT IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]')
                                                AND ($_DBC_acces_nom ILIKE '".preg_replace("/[']+/", "''", stripslashes($nom_recherche))."%"."' 
                                                    OR $_DBC_acces_login ILIKE '".preg_replace("/[']+/", "''", stripslashes($nom_recherche))."%"."'))
                                             UNION
                                                (SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                                        $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
                                                   FROM $_DB_acces, $_DB_acces_comp, $_DB_composantes
                                                WHERE $_DBC_acces_comp_composante_id=$_DBC_composantes_id
                                                AND $_DBC_acces_comp_acces_id=$_DBC_acces_id
                                                AND $_DBC_acces_niveau NOT IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]')
                                                AND ($_DBC_acces_nom ILIKE '".preg_replace("/[']+/", "''", stripslashes($nom_recherche))."%"."' 
                                                    OR $_DBC_acces_login ILIKE '".preg_replace("/[']+/", "''", stripslashes($nom_recherche))."%"."'))
                                             ORDER BY cnom, aniveau DESC, anom, aprenom");
                              
            $rows_recherche=db_num_rows($result_recherche);
         }

         $result=db_query($dbr, "(SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                         $_DBC_acces_prenom as aprenom, $_DBC_acces_login, '0' as cnom
                                    FROM $_DB_acces
                                    WHERE $_DBC_acces_niveau IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]')
                                 )
                              UNION   
                                 (SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                         $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
                                    FROM $_DB_acces, $_DB_composantes
                                 WHERE $_DBC_acces_composante_id=$_DBC_composantes_id
                                 AND $_DBC_acces_niveau NOT IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]'))
                              UNION
                                 (SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                         $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
                                    FROM $_DB_acces, $_DB_acces_comp, $_DB_composantes
                                 WHERE $_DBC_acces_comp_composante_id=$_DBC_composantes_id
                                 AND $_DBC_acces_comp_acces_id=$_DBC_acces_id
                                 AND $_DBC_acces_niveau NOT IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]'))
                              ORDER BY cnom, aniveau DESC, anom, aprenom");
         
         $rows=db_num_rows($result);
      
      if(isset($recherche))
         {
         print("<tr>
                      <td class='fond_menu2' align='right'>
                         <font class='Texte_menu2' style='font-weight:bold;'>Résultat de la recherche : </font>
                      </td>
                      <td class='fond_menu' colspan='2'>");

        if(!$rows_recherche)
           print("<font class='Texte_menu'>Aucun utilisateur ne correspond à votre recherche</font>");
        else
        {
               print("<select name='user_id_recherche' size='1'>
                        <option value=''></option>\n");
   
               $old_comp="--";
               $old_niveau="";
   
               for($i=0; $i<$rows_recherche; $i++)
               {
                  list($user_id, $login_niveau, $login_nom,$login_prenom,$login,$comp_nom)=db_fetch_row($result_recherche,$i);
   
                  if($comp_nom!=$old_comp)
                  {
                     if($i!=0)
                        print("</optgroup>
                                 <option value='' label='' disabled></option>\n");
                     if($comp_nom=="0")
                        print("<optgroup label='==== Administrateurs, support et accès étendus ===='>\n");
                     else
                        print("<optgroup label='==== ".htmlspecialchars($comp_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE)." ===='>\n");
   
                     $old_comp=$comp_nom;
                     $old_niveau="";
                  }
               
                  if($login_niveau!=$old_niveau)
                  {
                    if($i!=0)
                       print("</optgroup>
                              <option value='' label='' disabled></option>\n");
               
                    print("<optgroup label='".htmlspecialchars(stripslashes($GLOBALS["tab_niveau"]["$login_niveau"]), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE)."'></optgroup>\n");
               
                    $old_niveau=$login_niveau;
                  }
   
                  print("<option value='$user_id'>" . htmlspecialchars("$login_nom $login_prenom", ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE) . "</option>\n");
               }
   
               print("</optgroup>
                     </select>
                     
                     <input type='image' class='icone' src='$__ICON_DIR/edit_16x16_menu.png' alt='Modifier' name='modifier_recherche' value='Modifier' title='[Modifier un utilisateur]'>
                     <input type='image' class='icone' src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' name='supprimer_recherche' value='Supprimer' title='[Supprimer un utilisateur]'>");
         }
       
          print("</td>
              </tr>\n");
       
         }
         
         print("<tr>
                   <td colspan='3' class='fond_page' height='20'></td>
                </tr>
                <tr>
                   <td class='fond_menu2' align='right'>
                      <font class='Texte_menu2' style='font-weight:bold;'>Liste complète des utilisateurs : </font>
                   </td>
                   <td class='fond_menu' colspan='2'>

                   <select name='user_id' size='1'>
                     <option value=''></option>\n");

         $old_comp="--";
         $old_niveau="";

         for($i=0; $i<$rows; $i++)
         {
            list($user_id, $login_niveau, $login_nom,$login_prenom,$login,$comp_nom)=db_fetch_row($result,$i);

            if($comp_nom!=$old_comp)
            {
               if($i!=0)
                  print("</optgroup>
                              <option value='' label='' disabled></option>\n");
               if($comp_nom=="0")
                  print("<optgroup label='==== Administrateurs, support et accès étendus ===='>\n");
               else
                  print("<optgroup label='==== ".htmlspecialchars($comp_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE)." ===='>\n");

               $old_comp=$comp_nom;
               $old_niveau="";
            }
            
            if($login_niveau!=$old_niveau)
            {
               if($i!=0)
                  print("</optgroup>
                         <option value='' label='' disabled></option>\n");
            
               print("<optgroup label='".htmlspecialchars(stripslashes($GLOBALS["tab_niveau"]["$login_niveau"]), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE)."'></optgroup>\n");
            
               $old_niveau=$login_niveau;
            }

            print("<option value='$user_id'>" . htmlspecialchars("$login_nom $login_prenom", ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE) . "</option>\n");
         }

         print("</optgroup>
            </select>
            
            <input type='image' class='icone' src='$__ICON_DIR/edit_16x16_menu.png' alt='Modifier' name='modifier' value='Modifier' title='[Modifier un utilisateur]'>
            <input type='image' class='icone' src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' name='supprimer' value='Supprimer' title='[Supprimer un utilisateur]'>
            
            </td>
         </tr>         
       
         </table>

         <div class='centered_icons_box'>
            <a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
            <a href='$php_self?a=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/add_32x32_fond.png' alt='Ajouter' title='[Ajouter un utilisateur]' border='0'></a>\n");

         print("</form>
               </div>
               <script language='javascript'>
                  document.form1.id.focus()
               </script>\n");
      }
      elseif($_SESSION["suppression"]==1)
      {
         print("<form action='$php_self' method='POST' name='form1'>
                  <input type='hidden' name='user_id' value='$user_id'>");

         $result=db_query($dbr,"SELECT $_DBC_acces_nom, $_DBC_acces_prenom FROM $_DB_acces WHERE $_DBC_acces_id='$user_id'");

         list($current_nom, $current_prenom)=db_fetch_row($result,0);

         db_free_result($result);

         message("<center>
                     Souhaitez vous vraiment supprimer \"$current_prenom $current_nom\" de la liste des utilisateurs ?
                     <br>(Tous ses messages seront supprimés)
                  </center>", $__QUESTION);

         print("<div class='centered_icons_box'>
                  <a href='$php_self?s=0' target='_self' class='lien_bleu_12'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' title='[Annuler la suppression]' border='0'></a>
                  <input type='image' src='$__ICON_DIR/trashcan_full_34x34_slick_fond.png' alt='Supprimer' title='[Confirmer la suppression]' name='conf_supprimer' value='Supprimer'>
                  </form>
                </div>\n");
      }
      elseif($_SESSION["ajout_user"]==1)
      {
         if(!isset($_SESSION["source"]))
         {
            // On regarde les sources disponibles (le compte manuel est toujours possible : count=1)
            // S'il y a en plus qu'une, on donne le choix à l'utilisateur
            $count=1;
            
            // Code à compléter en cas d'ajout d'autres sources
            if(isset($GLOBALS["__LDAP_ACTIF"]) && $GLOBALS["__LDAP_ACTIF"]=="t")
               $count++;
            
            if($count>1)
            {
               print("<form action='$php_self' method='POST' name='form1'>
               
                      <table align='center'>
                      <tr>
                        <td class='td-complet fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
                           <font class='Texte_menu2' style='vertical-align:middle;'>
                              <b>Création d'un compte</b>
                           </font>
                        </td>
                      </tr>
                      <tr>
                        <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Source du compte : </b></font></td>
                        <td class='td-droite fond_menu'>\n");
   
               if(isset($new_source))
                  $source=$new_source;
               elseif(isset($current_source))
                  $source=$current_source;
               else
                  $source=$GLOBALS["__COMPTE_MANUEL"];
   
               print("<select name='source'>\n");
               
               foreach($__SOURCE_COMPTE as $source_nb => $source_nom)
               {
                  // Condition pour proposer la source LDAP : LDAP actif
                  if($source_nb==$GLOBALS["__COMPTE_LDAP"])
                  {
                     if($GLOBALS["__LDAP_ACTIF"]=="t")
                     {
                        $selected=$source==$source_nb ? "selected='1'" : "";
                        print("<option value='$source_nb' $selected>".htmlspecialchars(stripslashes($source_nom), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE)."</option>\n");
                     }
                  }
                  else // Ajouter les conditions en fonction des autres sources
                  {
                     $selected=$source==$source_nb ? "selected='1'" : "";
                     print("<option value='$source_nb' $selected>".htmlspecialchars(stripslashes($source_nom), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE)."</option>\n");
                  }  
               }
                
               print("</select>
                      <input type='submit' style='margin-left:10px;' name='valider_source' value='Sélectionner'>
                        </td>
                     </tr>
                     </table>\n");
            }
            else // Par défaut
               $_SESSION["source"]=$GLOBALS["__COMPTE_MANUEL"];
         }
         
         if(!isset($new_nom)) // un seul test devrait suffire ...
         {
            $new_login=$new_nom=$new_prenom=$new_mail=$new_composante="";
            $new_niveau=$__LVL_CONSULT;
         }
         
      }
      elseif((isset($user_id) && $_SESSION["modification"]==1)) // modification (on récupère les infos actuelles)
      {
         $result=db_query($dbr,"SELECT $_DBC_acces_login, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
                                       $_DBC_acces_composante_id, $_DBC_acces_niveau, $_DBC_acces_reception_msg_scol, 
                                       $_DBC_acces_reception_msg_systeme, $_DBC_acces_source
                                    FROM $_DB_acces
                                 WHERE $_DBC_acces_id='$user_id'");

         list($current_login, $current_nom, $current_prenom, $current_mail, $current_comp_id, $current_niveau, $current_reception_msg_scol, 
              $current_reception_msg_sys, $_SESSION["source"])=db_fetch_row($result,0);

         db_free_result($result);

         print("<form action='$php_self' method='POST' name='form1'>\n");

         if(isset($user_id))
         {
            print("<input type='hidden' name='user_id' value='$user_id'>
                   <input type='hidden' name='current_login' value='$current_login'>
                   <input type='hidden' name='current_mail' value='$current_mail'>\n");
         }
      }
      
      if(isset($_SESSION["source"]))
      {
   ?>
   <table align='center'>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px 20px 4px 20px;' colspan='2'>
         <font class='Texte_menu2' style='vertical-align:middle;'>
            <b>&#8226;&nbsp;&nbsp;Données du compte utilisateur</b>
         </font>
      </td>      
   </tr>
   <?php
      // Ajout d'un utilisateur : possibilité de recherche dans LDAP si le module est activé
      if($_SESSION["ajout_user"]==1)
      {
         if($GLOBALS["__LDAP_ACTIF"]=="t" && $_SESSION["source"]==$GLOBALS["__COMPTE_LDAP"])
         {
   ?>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Recherche dans l'annuaire LDAP (nom ou identifiant) : </b></font></td>
      <td class='td-droite fond_menu'>
         <input type='text' name='recherche_ldap' value="<?php if(isset($_SESSION["current_recherche_ldap"])) echo htmlspecialchars(stripslashes($_SESSION["current_recherche_ldap"]), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>" size='40'> 
         <input type='submit' style='margin-left:10px;' name='rechercher' value='Rechercher'>
   <?php
            if(isset($_SESSION["resultat_recherche_ldap"]))
            {
               if(!count($_SESSION["resultat_recherche_ldap"]))
                  print("<br /><font class='Texte'>Aucun résultat</font>\n");                  
               else
               {
                  print("<br /><select name='selection_ldap'>\n");
                  
                  foreach($_SESSION["resultat_recherche_ldap"] as $key => $current_personne)
                  {
                     $ldap_nom=utf8_decode(htmlspecialchars(stripslashes($current_personne["nom"]), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE));
                     $ldap_prenom=utf8_decode(htmlspecialchars(stripslashes($current_personne["prenom"]), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE));
                     $ldap_login=utf8_decode(htmlspecialchars(stripslashes($current_personne["login"]), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE));
                     
                     print("<option value='$key'>$ldap_nom $ldap_prenom ($ldap_login)</option>\n");
                  }
                  
                  print("</select>
                         <input type='submit' style='margin-left:10px;' name='selectionner' value='Sélectionner'>\n");
               }
            }
   ?>
      </td>
   </tr>
   <?php
         }
      }
   ?>     
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Source du compte : </b></font></td>
      <td class='td-droite fond_menu'>
         <?php
         /*
            if(isset($_SESSION["source"]))
               $source=$new_source;
            elseif(isset($current_source))
               $source=$current_source;
            else
               $source=$GLOBALS["__COMPTE_MANUEL"];
         */
            /*
            print("<select name='source'>\n");
            
            foreach($__SOURCE_COMPTE as $source_nb => $source_nom)
            {
               $selected=$source==$source_nb ? "selected='1'" : "";
               print("<option value='$source_nb' $selected>".htmlspecialchars(stripslashes($source_nom), ENT_QUOTES, $GLOBALS[default_htmlspecialchars_encoding])."</option>\n");  
            }
             
            print("</select>\n");
            */

            print("<font class='Texte_menu'>".$__SOURCE_COMPTE[$_SESSION["source"]]."</font>\n");
         ?>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Identifiant (en minuscules) : </b></font></td>
      <td class='td-droite fond_menu'>
      <?php 
         if(isset($new_login))
            $login=$new_login;
         elseif(isset($current_login))
            $login=$current_login;
         else
            $login="";
            
         if($_SESSION["source"]!=$GLOBALS["__COMPTE_MANUEL"])
         {
            if(isset($login))
               print("<font class='Texte_menu'>$login</font>
                      <input type='hidden' name='login' value='$login'>\n");
         }
         else
            print("<input type='text' name='login' value='$login' size='20'>\n");
      ?>
      </td>         
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Nom : </b></font></td>
      <td class='td-droite fond_menu'>
      <?php 
         if(isset($new_nom))
            $nom=$new_nom;
         elseif(isset($current_nom))
            $nom=$current_nom;
         else
            $nom="";
            
         if($_SESSION["source"]!=$GLOBALS["__COMPTE_MANUEL"])
         {
            if(isset($nom))
               print("<font class='Texte_menu'>$nom</font>
                      <input type='hidden' name='nom' value='$nom'>\n");
         }
         else
            print("<input type='text' name='nom' value='$nom' size='40'>\n");
      ?>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Prénom</b></font></td>
      <td class='td-droite fond_menu'>
      <?php 
         if(isset($new_prenom))
            $prenom=$new_prenom;
         elseif(isset($current_prenom))
            $prenom=$current_prenom;
         else
            $prenom="";
            
         if($_SESSION["source"]!=$GLOBALS["__COMPTE_MANUEL"])
         {
            if(isset($prenom))
               print("<font class='Texte_menu'>$prenom</font>
                      <input type='hidden' name='prenom' value='$prenom'>\n");
         }
         else
            print("<input type='text' name='prenom' value='$prenom' size='40'>\n");
      ?>      
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Courrier électronique : </b></font></td>
      <td class='td-droite fond_menu'>
      <?php 
         if(isset($new_mail))
            $mail=$new_mail;
         elseif(isset($current_mail))
            $mail=$current_mail;
         else
            $mail="";           
            
         // LDAP : le mail peut être multivalué
         if($_SESSION["source"]==$GLOBALS["__COMPTE_LDAP"])
         {
            if(is_array($mail) && count($mail)>1)
            {
               print("<select name='email'>");
               
               foreach($mail as $cur_mail)
                  echo "<option value='$cur_mail'>$cur_mail</option>\n";
               
               print("</select>\n");
            }
            elseif((is_array($mail) && count($mail)==1) || !is_array($mail))  // 1 seul mail, pas de choix
            {
               $cur_mail=!is_array($mail) ? $mail : $mail[0];
                              
               print("<font class='Texte_menu'>$cur_mail</font>
                      <input type='hidden' name='email' value='$cur_mail'>\n");
            }
            else
               print("<font class='Texte_menu'>Erreur : aucun courriel n'a été trouvé dans l'annuaire</font>
                      <input type='hidden' name='email' value=''>\n");
         }
         elseif($_SESSION["source"]!=$GLOBALS["__COMPTE_MANUEL"])
         {
            if(isset($mail))
               print("<font class='Texte_menu'>$mail</font>
                      <input type='hidden' name='email' value='$mail'>\n");
         }
         else
            print("<input type='text' name='email' value='$mail' size='60'>\n");
      ?>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Composante</b></font></td>
      <td class='td-droite fond_menu'>
         <select name='composante'>
            <?php
               if(isset($new_comp_id))
                  $form_comp_id=$new_comp_id;
               elseif(isset($current_comp_id))
                  $form_comp_id=$current_comp_id;
               elseif($_SESSION["ajout_user"]==1 && isset($_SESSION['comp_id']))
                  $form_comp_id=$_SESSION['comp_id'];
               else
                  $form_comp_id="";
         
               $result2=db_query($dbr, "SELECT $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_univ_id,
                                               $_DBC_universites_nom
                                          FROM $_DB_composantes, $_DB_universites
                                       WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
                                          ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");

               $old_univ="";

               $rows2=db_num_rows($result2);

               for($i=0; $i<$rows2; $i++)
               {
                  list($comp_id, $comp_nom, $univ_id, $univ_nom)=db_fetch_row($result2,$i);

                  if($univ_id!=$old_univ)
                  {
                     if($i!=0)
                        print("</optgroup>
                                 <option value='' label='' disabled></option>\n");

                     print("<optgroup label='".htmlspecialchars(stripslashes($univ_nom), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE)."'>\n");
                  }

                  $selected=($form_comp_id==$comp_id) ? "selected='1'" : "";

                  print("<option value='$comp_id' $selected>$comp_nom</option>\n");

                  $old_univ=$univ_id;
               }

               db_free_result($result2);
            ?>
            </optgroup>
         </select>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Droits d'accès</b></font></td>
      <td class='td-droite fond_menu'>
         <select name='niveau'>
            <?php
               if(isset($new_niveau))
                  $form_niveau=$new_niveau;
               elseif(isset($current_niveau))
                  $form_niveau=$current_niveau;
               else
                  $form_niveau=$__LVL_CONSULT;

               foreach($tab_niveau as $lvl => $nom_lvl)
                  echo "<option value='$lvl' " . ($form_niveau==$lvl ? "selected" : "") . ">$nom_lvl</option>";
            ?>
         </select>
      </td>
   </tr>
   <?php
      if($_SESSION["source"]==$GLOBALS["__COMPTE_MANUEL"])
      {
   ?>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'>Nouveau mot de passe (<strong>7 caractères minimum</strong>) :</font></td>
      <td class='td-droite fond_menu'><input type='password' name='pass' value='<?php if(isset($new_pass)) print($new_pass); ?>' size='40'>
         <?php
            if(isset($_SESSION["ajout_user"]) && $_SESSION["ajout_user"]==1)
               print("<font class='Texte_menu'><i>(Facultatif : généré automatiquement si vide)</i><font>\n");
         ?>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'>Confirmation du nouveau mot de passe :</font></td>
      <td class='td-droite fond_menu'><input type='password' name='conf_pass' value='<?php if(isset($new_pass_conf)) print($new_pass_conf); ?>' size='40'></td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'>Envoyer le nouveau mot de passe<br>ou générer un mot de passe aléatoire</font></td>
      <td class='td-droite fond_menu'><input type='checkbox' name='renvoyer' value='1'></td>
   </tr>
   <?php
      }
   ?>
  <tr>
     <td colspan='2' height='10' class='fond_page'></td>
   </tr>
  <tr>
      <td class='td-complet fond_menu2' colspan='2'>
      <font class='Texte_menu2' style='vertical-align:middle;'><b>&#8226;&nbsp;&nbsp;Messagerie interne</b></font>
    </td>
  </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Destinataire potentiel des messages<br>envoyés par les candidats ?</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <font class='Texte_menu'>
            <?php
               if(isset($new_reception_msg))
                  $reception=$new_reception_msg;
               elseif(isset($current_reception_msg_scol))
                  $reception=$current_reception_msg_scol;
               else
                  $reception='t';

               if($reception=="" || $reception=='f')
               {
                  $yes_checked="";
                  $no_checked="checked";
               }
               else
               {
                  $yes_checked="checked";
                  $no_checked="";
               }

               print("<input type='radio' name='reception_msg' value='t' $yes_checked>&nbsp;Oui
                        &nbsp;&nbsp;<input type='radio' name='reception_msg' value='f' $no_checked>&nbsp;Non\n");
            ?>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Destinataire des messages système ?</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <font class='Texte_menu'>
            <?php
               if(isset($new_reception_sys))
                  $reception_sys=$new_reception_sys;
               elseif(isset($current_reception_msg_sys))
                  $reception_sys=$current_reception_msg_sys;
               else
                  $reception_sys='f';

               if($reception_sys=="" || $reception_sys=='f')
               {
                  $yes_checked="";
                  $no_checked="checked";
               }
               else
               {
                  $yes_checked="checked";
                  $no_checked="";
               }

               print("<input type='radio' name='reception_msg_sys' value='t' $yes_checked>&nbsp;Oui
                        &nbsp;&nbsp;<input type='radio' name='reception_msg_sys' value='f' $no_checked>&nbsp;Non\n");
            ?>
         </font>
      </td>
   </tr>
   <?php
      if($_SESSION["ajout_user"]==0)
      {
   ?>
  <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'>Transférer les messages 'non lus' à un autre utilisateur</font></td>
      <td class='td-droite fond_menu'>
      <input type='checkbox' name='copie_messages' value='1' style='vertical-align:middle;'>
      
      <?php
        $result=db_query($dbr, "(SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                         $_DBC_acces_prenom as aprenom, $_DBC_acces_login, '0' as cnom
                                    FROM $_DB_acces
                                    WHERE $_DBC_acces_niveau IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]'))
                              UNION   
                                 (SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                         $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
                                    FROM $_DB_acces, $_DB_composantes
                                 WHERE $_DBC_acces_composante_id=$_DBC_composantes_id
                                 AND $_DBC_acces_niveau NOT IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]'))
                              UNION
                                 (SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                         $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
                                    FROM $_DB_acces, $_DB_acces_comp, $_DB_composantes
                                 WHERE $_DBC_acces_comp_composante_id=$_DBC_composantes_id
                                 AND $_DBC_acces_comp_acces_id=$_DBC_acces_id
                                 AND $_DBC_acces_niveau NOT IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]'))
                              ORDER BY cnom, aniveau DESC, anom, aprenom");
                    
         print("<font class='Texte_menu'>Destinataire : </font>
               <select name='copie_messages_user_id' size='1'>
                     <option value=''></option>\n");

            $old_comp="--";
            $old_niveau="";

            $rows=db_num_rows($result);

            for($i=0; $i<$rows; $i++)
            {
               list($user_id, $login_niveau, $login_nom,$login_prenom,$login,$comp_nom)=db_fetch_row($result,$i);

               if($comp_nom!=$old_comp)
               {
                  if($i!=0)
                     print("</optgroup>
                              <option value='' label='' disabled></option>\n");
                    
                  if($comp_nom=="0")
                     print("<optgroup label='==== Administrateurs, support et accès étendus ===='>\n");
                  else
                     print("<optgroup label='==== ".htmlspecialchars($comp_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE)." ===='>\n");

                  $old_comp=$comp_nom;
                  $old_niveau="";
               }
            
               if($login_niveau!=$old_niveau)
               {
                 if($i!=0)
                    print("</optgroup>
                           <option value='' label='' disabled></option>\n");
            
                 print("<optgroup label='".htmlspecialchars(stripslashes($GLOBALS["tab_niveau"]["$login_niveau"]), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE)."'></optgroup>\n");
            
                 $old_niveau=$login_niveau;
               }

               print("<option value='$user_id'>" . htmlspecialchars("$login_nom $login_prenom", ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE) . "</option>\n");
            }

            db_free_result($result);

            print("</optgroup>
                  </select>"); 
      ?>
      
    </td>
  </tr>
  <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'>Marquer tous les messages comme 'lus' (irréversible)</font></td>
      <td class='td-droite fond_menu'><input type='checkbox' name='marquer_lus' value='1'></td>
  </tr>
  <?php
  }
  ?>
   </table>

   <script language='javascript'>
      document.form1.login.focus()
   </script>

   <div class='centered_icons_box'>
      <a href='<?php echo "$php_self?m=0&a=0"; ?>' target='_self' class='lien_bleu_12'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
      <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
      </form>
   </div>

   <?php
      }
      db_close($dbr);
   ?>

</div>
<?php
   pied_de_page();
?>
</body></html>

