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
   include "$__INCLUDE_DIR_ABS/access_functions.php";

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   verif_auth();

   // Condition : la fiche doit être verrouillée
   if(!isset($_SESSION["tab_candidat"]["lock"]) || $_SESSION["tab_candidat"]["lock"]!=1)
   {
      header("Location:edit_candidature.php");
      exit;
   }

   // identifiant de l'étudiant
   $candidat_id=$_SESSION["candidat_id"];

   // identifiant de candidature
   if(isset($_GET["cand_id"]) && is_numeric($_GET["cand_id"]))
      $_SESSION["cand_id"]=$cand_id=$_GET["cand_id"];
   elseif(isset($_SESSION["cand_id"]))
      $cand_id=$_SESSION["cand_id"];
   else
   {
      header("Location:edit_candidature.php");
      exit;
   }
   
   $dbr=db_connect();

   $result=db_query($dbr,"SELECT $_DBC_candidat_nom,$_DBC_candidat_prenom FROM $_DB_candidat WHERE $_DBC_candidat_id='$candidat_id'");
   $rows=db_num_rows($result);
   
   if(!$rows)
   {
      db_free_result($result);
      db_close($dbr);
      header("Location:index.php");
      exit;
   }
   else
      list($nom,$prenom)=db_fetch_row($result,0);
      
   db_free_result($result);   
   
   // récupération de la candidature actuelle

   if($cand_array=__get_candidature($dbr, $cand_id))
   {
      // Si la date de commission n'a pas été forcée (=0), on prend la date de commission parametrée dans les sessions
      if($cand_array["date_decision_unix"]==0 || $cand_array["date_decision_unix"]=="")
         $cand_array["date_decision_unix"]=$cand_array["session_commission_unix"];

      if(ctype_digit($cand_array["date_decision_unix"]))
         $date_decision_txt=date_fr("j F Y", $cand_array["date_decision_unix"]);
      else
         $date_decision_txt="";
   }
   else
   {
      db_close($dbr);
      header("Location:index.php");
      exit;
   }
   
   // Vérification des droits d'accès au traitement de cette formation
   if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN"))
      && ($_SESSION['niveau']=="$__LVL_SAISIE" && !verif_droits_formations($_SESSION["comp_id"], $cand_array["propspec_id"])))
   {
      db_close($dbr);
      header("Location:$__MOD_DIR/gestion/noaccess.php");
      exit();
   }
   
   if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
   {
      if(isset($cand_array["date_decision_unix"]) && $cand_array["date_decision_unix"]!=0 && $cand_array["date_decision_unix"]!="")
         $new_date_decision=$cand_array["date_decision_unix"];
      else
         $new_date_decision=$_POST['date_decision'];
      
      $decision=$_POST['decision'];

/*
      $entretien_salle=$entretien_heure=$entretien_lieu="";
      $entretien_date="0";
*/
      $entretien_jour=array_key_exists("entretien_jour", $_POST) ? trim($_POST["entretien_jour"]) : "";
      $entretien_mois=array_key_exists("entretien_mois", $_POST) ? trim($_POST["entretien_mois"]) : "";

      if(array_key_exists("entretien_annee", $_POST))
      {
         if(trim($_POST["entretien_annee"])=="" || !ctype_digit(trim($_POST["entretien_annee"])))
            $entretien_annee="$__PERIODE";
         elseif(ctype_digit(trim($_POST["entretien_annee"])) && strlen(trim($_POST["entretien_annee"]))==2)
            $entretien_annee="20".trim($_POST["entretien_annee"]);
         elseif(ctype_digit(trim($_POST["entretien_annee"])) && strlen(trim($_POST["entretien_annee"]))==4)
            $entretien_annee=trim($_POST["entretien_annee"]);
      }
      else
         $entretien_annee="";

      if(array_key_exists("entretien_heure", $_POST))
         $entretien_h=trim($_POST["entretien_heure"])=="" ? "00" : trim($_POST["entretien_heure"]);
      else
         $entretien_h="";

      if(array_key_exists("entretien_minute", $_POST))
         $entretien_m=trim($_POST["entretien_minute"])=="" ? "00" : trim($_POST["entretien_minute"]);
      else
         $entretien_m="";

      if(array_key_exists("entretien_lieu", $_POST))
         $entretien_lieu=trim($_POST["entretien_lieu"]);
      else
         $entretien_lieu="";

      if(array_key_exists("entretien_salle", $_POST))
         $entretien_salle=trim($_POST["entretien_salle"]);
      else
         $entretien_salle="";

      // Convocation à un entretien ? ==> saisie de la date, de l'heure et du lieu
      if(($decision==$__DOSSIER_ENTRETIEN || $decision==$__DOSSIER_ENTRETIEN_TEL) && $cand_array["entretiens"])
      {
         if($entretien_lieu=="" || $entretien_salle=="")
         {
            $res_defaut=db_query($dbr,"SELECT $_DBC_composantes_ent_salle, $_DBC_composantes_ent_lieu
                                       FROM $_DB_composantes WHERE $_DBC_composantes_id='$_SESSION[comp_id]'");

            if(db_num_rows($res_defaut))
            {
               list($defaut_ent_salle, $defaut_ent_lieu)=db_fetch_row($res_defaut, 0);
            
               if($entretien_lieu=="")
                  $entretien_lieu=preg_replace("/[']+/","''", stripslashes($defaut_ent_lieu));

               if($entretien_salle=="")
                  $entretien_salle=preg_replace("/[']+/","''", stripslashes($defaut_ent_salle));
            }
            else
            {
               db_close($dbr);
               header("Location:login.php");
               exit();
            }

            db_free_result($res_defaut);
         }

         if($entretien_jour!="" && ctype_digit($entretien_jour) && $entretien_mois!="" && ctype_digit($entretien_mois)
            && $entretien_h!="" && ctype_digit($entretien_h) && $entretien_m!="" && ctype_digit($entretien_m))
         {
            $entretien_date=MakeTime($entretien_h,$entretien_m,0,$entretien_mois, $entretien_jour, $entretien_annee);
            $entretien_heure=$entretien_h . "h" . $entretien_m;
         }
         else
            $erreur_format_entretien_date_heure=1;
      }

      // Initialisation de certaines variables pour l'entretien au cas où ça n'aurait pas été fait dans le bloc précédent
      // Todo : à traiter plus proprement
      if($entretien_jour=="" && $entretien_mois=="" && $entretien_h=="00" && $entretien_m=="00")
         $entretien_date="0";
      elseif(ctype_digit($entretien_h) && ctype_digit($entretien_m) && ctype_digit($entretien_mois) && ctype_digit($entretien_jour) && ctype_digit($entretien_annee))
         $entretien_date=MakeTime($entretien_h,$entretien_m,0,$entretien_mois, $entretien_jour, $entretien_annee);
      else
         $entretien_date="0";

      if($entretien_h=="00" && $entretien_m=="00")
         $entretien_heure="";
      else
         $entretien_heure=$entretien_h . "h" . $entretien_m;
   
      if(!isset($erreur_format_entretien_date_heure))
      {
         // Date sur la lettre
         $force_jour=$_POST["force_jour"];
         $force_mois=$_POST["force_mois"];
         $force_annee=$_POST["force_annee"];

         if(!is_numeric($force_annee) || $force_annee<date("Y"))
            $force_annee=date("Y");

         $new_date_decision=MakeTime(0,30,0,$force_mois, $force_jour, $force_annee); // date au format unix : le jour même, le matin

         // Requête à part pour mettre cette date à jour

         if($new_date_decision!=$cand_array["session_commission_unix"])
         {
            db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_date_decision='$new_date_decision' WHERE $_DBU_cand_id='$cand_id'");
            $date_maj=1;

            $cand_array["date_decision_unix"]=$new_date_decision;

            if(ctype_digit($cand_array["date_decision_unix"]))
               $date_decision_txt=date_fr("j F Y", $cand_array["date_decision_unix"]);
            else
               $date_decision_txt="";

            // print("DBG : $date_decision_txt");
         }

         // Liste complémentaire ? si oui, saisie du rang et tests sommaires, sinon, on met une valeur vide
         if($decision==$__DOSSIER_LISTE || $decision==$__DOSSIER_LISTE_ENTRETIEN)
         {
            $rang_liste_attente=trim($_POST['rang_liste']);

            // rang vide : on prend automatiquement le max dans la base, ou 1 si pas de max
            // on n'oublie pas les années pour les recherches sur l'identifiant d'inscription (timestamp)

            // Si le rang est différent
            if($cand_array["rang_attente"]!=$rang_liste_attente || $rang_liste_attente=="")
            {
               if($rang_liste_attente=="")
               {   
                  $result=db_query($dbr,"SELECT max(CAST($_DBC_cand_liste_attente AS int)) FROM $_DB_cand
                                                         WHERE $_DBC_cand_propspec_id='$cand_array[propspec_id]'
                                                         AND $_DBC_cand_periode='$__PERIODE'
                                                         AND $_DBC_cand_liste_attente!=''");
      
                  list($max_rang)=db_fetch_row($result,0);
      
                  if($max_rang=="") // personne dans la liste complémentaire
                     $rang_liste_attente=1;
                  else
                     $rang_liste_attente=$max_rang+1;

                  db_free_result($result);
               }
               else
               {
                  if(!ctype_digit($rang_liste_attente) || $rang_liste_attente < 1)
                     $rang_vide=1;
                  else
                  {
                     // Si une candidature est déjà à ce rang là, il faudra tout décaler
                     // (jusqu'à ce qu'on tombe sur un trou : arret du décalage)
                     if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_cand
                                                      WHERE $_DBC_cand_propspec_id='$cand_array[propspec_id]'
                                                      AND $_DBC_cand_periode='$__PERIODE'
                                                      AND ($_DBC_cand_decision='$__DOSSIER_LISTE' OR $_DBC_cand_decision='$__DOSSIER_LISTE_ENTRETIEN')
                                                      AND $_DBC_cand_liste_attente='$rang_liste_attente'")))
                     {
                        $res_rangs=db_query($dbr, "SELECT $_DBC_cand_id, $_DBC_cand_liste_attente FROM $_DB_cand
                                                   WHERE $_DBU_cand_propspec_id='$cand_array[propspec_id]'
                                                   AND $_DBU_cand_periode='$__PERIODE'
                                                   AND ($_DBU_cand_decision='$__DOSSIER_LISTE' OR $_DBU_cand_decision='$__DOSSIER_LISTE_ENTRETIEN')
                                                   AND $_DBU_cand_liste_attente!=''
                                                   AND CAST($_DBU_cand_liste_attente AS int)>= '$rang_liste_attente'");

                        $rows_rangs=db_num_rows($res_rangs);

                        for($r=0; $r<$rows_rangs; $r++)
                        {
                           list($dec_cand_id, $dec_rang)=db_fetch_row($res_rangs, $r);

                           if($r!=($rows_rangs-1))
                           {
                              list($next_cand_id, $next_rang)=db_fetch_row($res_rangs, ($r+1));

                              db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$next_rang' WHERE $_DBU_cand_id='$dec_cand_id'");

                              // Le rang suivant n'est pas consécutif (=trou) : on sort de la boucle
                              if($next_rang==($dec_rang+1))
                                 $r=$rows_rangs;
                           }
                           else
                           {
                              $next_rang=$dec_rang+1;
                              db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$next_rang' WHERE $_DBU_cand_id='$dec_cand_id'");
                           }
                        }
/*

                        db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente=CAST($_DBU_cand_liste_attente AS int)+1
                                       WHERE $_DBU_cand_propspec_id='$cand_array[propspec_id]'
                                       AND $_DBU_cand_periode='$__PERIODE'
                                       AND ($_DBU_cand_decision='$__DOSSIER_LISTE' OR $_DBU_cand_decision='$__DOSSIER_LISTE_ENTRETIEN')
                                       AND $_DBU_cand_liste_attente!=''
                                       AND CAST($_DBU_cand_liste_attente AS int)>= '$rang_liste_attente'");
*/
                     }
                     // else // sinon, on met juste le rang

                     // Il ne reste plus qu'à mettre le rang de notre candidature en cours.
                     db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$rang_liste_attente' WHERE $_DBU_cand_id='$cand_id'");
                  }
               }

               write_evt($dbr, $__EVT_ID_G_PREC, "Liste complémentaire \"$cand_array[nom_complet]\" => rang $rang_liste_attente", $candidat_id, $cand_id);
            }
         }
         else // valeur vide par défaut
            $rang_liste_attente="";

         // Récupération de la décision précédente, pour tester si on doit réordonner le classement dans une liste complémentaire
         $old_decision=$_POST['old_decision'];

/*
         // CASE "recours" OBSOLETE
         // TODO : nettoyer la base pour supprimer cette colonne
         if(isset($_POST['recours']))
            $cand_array["recours"]=1;
         else
*/
         $cand_array["recours"]=0;

         // la présence du champ 'transmission de dossier' n'est pas obligatoire dans le formulaire, donc on la teste
         if($decision==$__DOSSIER_TRANSMIS && ((isset($_POST["transfert_formation"]) && !empty($_POST["transfert_formation"]))
                                             || (isset($_POST["transmission_libre"]) && !empty($_POST["transmission_libre"]))))
         {
            if(!empty($_POST['transmission_libre']))
            {
               $transmission="";
               $transmission_txt=trim($_POST['transmission_libre']); // Champ libre prioritaire
            }
            else
            {
               $transmission=$_POST['transfert_formation']; // Contient la formation vers laquelle la transmission est faite
               $result=db_query($dbr, "SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
                                          FROM $_DB_annees, $_DB_specs, $_DB_propspec
                                       WHERE $_DBC_propspec_annee=$_DBC_annees_id
                                       AND $_DBC_propspec_id_spec=$_DBC_specs_id
                                       AND $_DBC_propspec_id='$transmission'");

               if(db_num_rows($result))
               {
                  list($trans_annee, $trans_spec, $trans_finalite)=db_fetch_row($result, 0);

                  $nom_finalite=$tab_finalite[$trans_finalite];

                  if($trans_annee=="")
                     $transmission_txt=trim("$trans_spec $trans_finalite");
                  else
                     $transmission_txt=trim("$trans_annee - $trans_spec $nom_finalite");
               }
               else // annulation
               {
                  $transmission=$transmission_txt="";
                  $decision=$__DOSSIER_NON_TRAITE;
               }

               db_free_result($result);
            }
         }
         else
            $transmission=$transmission_txt="";

         $transmission_txt=str_replace("'","''", stripslashes($transmission_txt));

         $cand_array["vap"]=$_POST['vap'];

         if(array_key_exists("talon_reponse",$_POST))
            $cand_array["talon_reponse"]=$_POST['talon_reponse'];
         else
            $cand_array["talon_reponse"]=0;

/*
         if(array_key_exists("statut_frais",$_POST))
            $statut_frais=$_POST['statut_frais'];
         else
            $statut_frais=0;
*/
         // motivation de la décision
         // 1/ Méthode classique : boucle sur les motifs et vérification pour chaque élément
         // 2/ Autre méthode : un seul motif

         // TODO : Boucle très obsolète à réécrire

         if(!isset($_SESSION["gestion_motifs"]) || $_SESSION["gestion_motifs"]==0)
         {
            $motivation_decision="";
            
            $result=db_query($dbr,"SELECT $_DBC_motifs_refus_id, $_DBC_motifs_refus_exclusif FROM $_DB_motifs_refus
                                       WHERE $_DBC_motifs_refus_comp_id='$_SESSION[comp_id]'
                                    ORDER BY $_DBC_motifs_refus_motif_long");
            $rows=db_num_rows($result);
            
            for($i=0; $i<$rows; $i++)
            {
               list($motif_id, $exclusif)=db_fetch_row($result,$i);
               $key="ref_$motif_id";
               
               if(isset($_POST[$key]))
               {
                  if($exclusif)
                  {
                     if(!isset($flag_exclusif)) // (ré)initialisation de la chaine de motivations
                     {
                        $motivation_decision = $_POST[$key];
                        $flag_exclusif=1;
                     }
                     else // on complète la chaine
                     {
                        if($motivation_decision!="")
                           $motivation_decision .= "|";
         
                        $motivation_decision .= $_POST[$key];
                     }
                  }
                  elseif(!isset($flag_exclusif))
                  {
                     if($motivation_decision!="")
                        $motivation_decision .= "|";
         
                     $motivation_decision .= $_POST[$key];
                  }
               }
            }
            db_free_result($result);
         }
         else // Méthode 2 (On dirait bien que ça va plus vite ;)
            $motivation_decision=$_POST["motivation"];

         if(!isset($flag_exclusif))
         {
            $motivation_decision_libre=trim($_POST['motivation_decision_libre']);
            
            if($motivation_decision_libre!="")
            {
               if($motivation_decision!="")
                  $motivation_decision .= "|@";
               else
                  $motivation_decision="@";
               
               $motivation_decision .= str_replace("'", "''", stripslashes($motivation_decision_libre));
            }
         }

         // On interdit les motifs vides pour certaines decisions
         // TODO 15/02/06 : ajouter une colonne "propriétés" dans la table des décisions plutot que se baser sur les identifiants ?
         if(($decision==$__DOSSIER_REFUS_RECOURS || $decision==$__DOSSIER_EN_ATTENTE || $decision==$__DOSSIER_SOUS_RESERVE || $decision==$__DOSSIER_REFUS || $decision==$__DOSSIER_TRANSMIS) && empty($motivation_decision))
            $motivation_vide=1;

         // En attente : envoi d'un message
         if($decision==$__DOSSIER_EN_ATTENTE && (!isset($motivation_vide) || $motivation_vide!=1) && $_SESSION['tab_candidat']['manuelle']!="1")
         {
            $civ_mail=$_SESSION['tab_candidat']['civ_texte'];
            $nom_mail=ucwords(mb_strtolower($_SESSION['tab_candidat']['nom'], "UTF-8"));
            $prenom_mail=$_SESSION['tab_candidat']['prenom'];

            // Motivation : on doit rééxtraire les motifs :
            if(!empty($motivation_decision))
            {
               $motifs_txt="";

               $motif_array=explode("|",$motivation_decision);
               $cnt=count($motif_array);

               for($j=0; $j<$cnt; $j++)
               {
                  $motif_id=$motif_array[$j];

                  if(is_numeric($motif_id)) // motif provenant de la table motifs_refus
                  {
                     $result2=db_query($dbr,"SELECT $_DBC_motifs_refus_motif, $_DBC_motifs_refus_motif_long
                                                FROM $_DB_motifs_refus
                                             WHERE $_DBC_motifs_refus_id='$motif_id'");
                     $rows2=db_num_rows($result2);

                     if($rows2)
                        list($txt,$txt_long)=db_fetch_row($result2,0);
                     else
                        $txt=$txt_long="";

                     db_free_result($result2);
                  }
                  else // motif libre
                  {
                     // nettoyage
                     $txt_long="";
                     // $txt=str_replace("@","",$motif_array[$j]);
                     $txt=preg_replace("/^@/","", $motif_array[$j]);
                  }

                  if(!empty($txt_long))
                     $txt=$txt_long;

                  if(!$j)
                     $motifs_txt="- $txt";
                  else
                     $motifs_txt.="<br>- $txt";
               }

               $message="Bonjour $civ_mail $nom_mail,\n\n
La Commission Pédagogique a mis votre dossier en attente pour le(s) motif(s) suivant(s) : \n
$motifs_txt

Merci de faire le nécessaire pour que la Commission Pédagogique puisse statuer définitivement sur votre candidature.

Cordialement,\n\n
--
$_SESSION[adr_scol]\n
$_SESSION[composante]
$_SESSION[universite]";

               $dest_array=array("0" => array("id"    => "$candidat_id",
                                             "civ"      => "$civ_mail",
                                             "nom"    => "$nom_mail",
                                             "prenom" => "$prenom_mail",
                                             "email"   => $_SESSION['tab_candidat']['email']));

               write_msg("", array("id" => $_SESSION["auth_id"], "nom" => $_SESSION["auth_nom"], "prenom" => $_SESSION["auth_prenom"]),
                        $dest_array, "$_SESSION[composante] - $cand_array[texte_formation]", $message, "$nom_mail $prenom_mail");
            }
         }

         
         if(isset($transmission) && $transmission!="") // transmission de dossier : on force la décision
            $decision=$__DOSSIER_TRANSMIS;
         elseif($decision=="") // aucune décision choisie : dossier non traité
            $decision=$__DOSSIER_NON_TRAITE;

         if($decision==$__DOSSIER_NON_TRAITE)
            $aucune_decision=1;

         if(!isset($aucune_decision) && !isset($rang_vide) && !isset($motivation_vide))
         {
            $date_prise_decision=time();

            // on peut mettre à jour la décision de cette candidature
            $req="UPDATE $_DB_cand SET $_DBU_cand_date_decision='$cand_array[date_decision_unix]',
                                       $_DBU_cand_decision='$decision',
                                       $_DBU_cand_motivation_decision='$motivation_decision',
                                       $_DBU_cand_recours='$cand_array[recours]',
                                       $_DBU_cand_liste_attente='$rang_liste_attente',
                                       $_DBU_cand_transmission_dossier='$transmission_txt',
                                       $_DBU_cand_vap_flag='$cand_array[vap]',
                                       $_DBU_cand_talon_reponse='$cand_array[talon_reponse]',
                                       $_DBU_cand_entretien_date='$entretien_date',
                                       $_DBU_cand_entretien_heure='$entretien_heure',
                                       $_DBU_cand_entretien_lieu='$entretien_lieu',
                                       $_DBU_cand_entretien_salle='$entretien_salle',
                                       $_DBU_cand_date_prise_decision='$date_prise_decision'
                  WHERE $_DBU_cand_id='$cand_id'";

            db_query($dbr, $req);

            list($dec_txt)=db_fetch_row(db_query($dbr, "SELECT $_DBC_decisions_texte FROM $_DB_decisions WHERE $_DBC_decisions_id='$decision'"), 0);

            write_evt($dbr, $__EVT_ID_G_PREC, "Décision \"$cand_array[nom_complet]\" : $dec_txt", $candidat_id, $cand_id, stripslashes($req));

            // Si : 
            // 1 - les décisions sont publiées 
            // 2 - la notification est activée
          // 3 - aucune notification n'a encore été envoyée 
            //     - OU la décision est passée d'une décision "partielle" à une décision fixe 
            //     - OU la nouvelle décision est "admission confirmée"
            // alors on envoie un message au candidat (le message ne contient pas la décision en elle même)
            
            if((array_key_exists("affichage_decisions", $_SESSION) && (($_SESSION["affichage_decisions"]==0 
               && db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_id=(SELECT $_DBC_cand_propspec_id FROM $_DB_cand WHERE $_DBC_cand_id='$cand_id') AND $_DBC_propspec_affichage_decisions!='0'")))
               || $_SESSION["affichage_decisions"]==1 || $_SESSION["affichage_decisions"]==2))
               && $_SESSION["avertir_decision"]==1 
               && ($_SESSION["tab_candidatures"][$cand_id]["notification_envoyee"]!=1 || $decision==$__DOSSIER_ADMISSION_CONFIRMEE || ($old_decision<=$__DOSSIER_NON_TRAITE && $decision>$__DOSSIER_NON_TRAITE)))
            {
               $message="Bonjour,\n
La Commission Pédagogique a rendu une décision pour votre candidature à la formation suivante : \n
[gras]$cand_array[nom_complet][/gras]\n
Pour consulter cette décision : 
- sélectionnez si besoin l'établissement adéquat (menu \"Choisir une autre composante\")
- dans votre fiche, rendez vous dans le menu \"Précandidatures\".

Cordialement,\n\n
--
$_SESSION[adr_scol]\n
$_SESSION[composante]
$_SESSION[universite]";

               $dest_array=array("0" => array("id"    => $candidat_id,
                                             "civ"      => $_SESSION["tab_candidat"]["civilite"],
                                             "nom"    => $_SESSION["tab_candidat"]["nom"],
                                             "prenom" => $_SESSION["tab_candidat"]["prenom"],
                                             "email"   => $_SESSION["tab_candidat"]["email"]));

               write_msg("", array("id" => "0", "nom" => "Système", "prenom" => ""), $dest_array, "$_SESSION[composante] - Décision", $message, $_SESSION["tab_candidat"]["nom"]." ".$_SESSION["tab_candidat"]["prenom"]);
               write_evt($dbr, $__EVT_ID_G_PREC, "Notification de décision envoyée", $candidat_id, $cand_id);
               
               db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_notification_envoyee='1' WHERE $_DBU_cand_id='$cand_id'");
               
               $_SESSION["tab_candidatures"][$cand_id]["notification_envoyee"]=1;
            }
            // Si le candidat était sur liste complémentaire, on décale les candidats suivants dans cette liste complémentaire
            if(($old_decision==$__DOSSIER_LISTE || $old_decision==$__DOSSIER_LISTE_ENTRETIEN) && $old_decision!=$decision)
            {
               if($cand_array["rang_attente"]=="")
                  $cand_array["rang_attente"]=0;

               // Définition des limites pour la recherche des éléments à décaler
               // TODO URGENT : naze, à réécrire avec une belle requête
               $result=db_query($dbr,"SELECT $_DBC_cand_id, CAST($_DBC_cand_liste_attente AS int)
                                          FROM $_DB_cand
                                       WHERE ($_DBC_cand_decision='$__DOSSIER_LISTE'
                                                OR $_DBC_cand_decision='$__DOSSIER_LISTE_ENTRETIEN')
                                       AND $_DBC_cand_propspec_id='$cand_array[propspec_id]'
                                       AND $_DBC_cand_periode='$__PERIODE'
                                       AND $_DBC_cand_liste_attente!=''
                                       AND CAST($_DBC_cand_liste_attente AS int)> '$cand_array[rang_attente]'
                                          ORDER BY CAST($_DBC_cand_liste_attente AS int) ASC");

               $rows=db_num_rows($result);

               // décalage
               for($i=0; $i<$rows;$i++)
               {
                  list($decalage_inid,$decalage_rang)=db_fetch_row($result,$i);

                  $nouveau_rang=$decalage_rang-1;
                  db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$nouveau_rang'
                                             WHERE $_DBU_cand_id='$decalage_inid'");
               }

               db_free_result($result);
            }


            // si transmission de dossier : création d'une nouvelle candidature (si transmission vers une formation de la composante)
            if($decision==$__DOSSIER_TRANSMIS && $old_decision!=$decision)
            {
               /*
               $candidature_id=time();

               // Unicité de l'identifiant de la nouvelle candidature
               while(db_num_rows(db_query($dbr,"SELECT $_DBC_cand_id FROM $_DB_cand WHERE $_DBC_cand_id='$candidature_id'")))
                  $candidature_id++;
               */
/*
               // Identifiant de l'année trans_annee (TODO : REMPLACER PAR LES IDENTIFIANTS)
               $result=db_query($dbr,"SELECT $_DBC_annees_id FROM $_DB_annees WHERE $_DBC_annees_annee ILIKE '$trans_annee'");
               $rows=db_num_rows($result);
               if($rows)
                  list($trans_annee_id)=db_feth_row($result,0);
               else
                  die("Incohérence de la base de données (trans_annee_id), merci de contacter d'urgence l'administrateur de l'application");
               db_free_result($result);
*/
               // on détermine l'ordre max
               $result=db_query($dbr,"SELECT max($_DBC_cand_ordre)+1 FROM $_DB_cand, $_DB_propspec
                                       WHERE $_DBC_cand_candidat_id='$candidat_id'
                                       AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                       AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'");
               $rows=db_num_rows($result);
               
               if($rows)   
               {
                  list($new_ordre)=db_fetch_row($result,0);
                  if($new_ordre=="" || empty($new_ordre))
                     $new_ordre=1;
               }
               else
                  $new_ordre=1;

               /* ============================================
                  TRANSMISSION DE DOSSIER : NOUVELLE CANDIDATURE
               ============================================ */

               // Uniquement si la tramission est dans l'offre de formation de la composante
               // et si la formation n'existe pas déjà
               // TODO : Conserver "Dossier Transmis depuis : $annee - $spec_nom" dans le champ "Transmission" ?
               if(isset($transmission) && $transmission!="" && ctype_digit($transmission) 
                  && !db_num_rows(db_query($dbr, "SELECT * FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$candidat_id'
                                                                          AND $_DBC_cand_propspec_id='$transmission'")))
               {
                  $new_entretien_date=0;
                  $new_entretien_salle=$new_entretien_heure=$new_entretien_lieu=$new_motif_decision=$new_liste="";
                  $new_recours=$new_masse=$new_talon_reponse=$statut_frais="0";
                  $new_lock=1;
                  $new_ordre_spec=$new_groupe_spec="-1"; // TODO : à recalculer ?
                  $nb_rappels=0;

                  // Date du statut de recevabilité
                  $new_date_prise_decision=$new_date_statut=time();

                  $spec_nom_transfert=str_replace("'","''", $cand_array["spec_nom"]);

                  // Session pour la nouvelle candidature
                  $date_ajout_candidature_origine=id_to_date($cand_id);

                  $res_session=db_query($dbr,"SELECT $_DBC_session_id FROM $_DB_session
                                                WHERE $_DBC_session_propspec_id='$transmission'
                                                AND $_DBC_session_ouverture<='$date_ajout_candidature_origine' 
                                                AND $_DBC_session_fermeture>='$date_ajout_candidature_origine'");

                  if(db_num_rows($res_session))
                     list($new_session_id)=db_fetch_row($res_session, 0);
                  else // Session indéterminée pour la formation cible, on prend la première ouverte pour cette formation
                  {
                     db_free_result($res_session);

                     $res_session=db_query($dbr,"SELECT $_DBC_session_id FROM $_DB_session
                                                WHERE $_DBC_session_propspec_id='$transmission'
                                                ORDER BY $_DBC_session_ouverture");

                     if(db_num_rows($res_session))
                        list($new_session_id)=db_fetch_row($res_session, 0);
                     else
                        $new_session_id='-1';
                  }

                  db_free_result($res_session);

                  $candidature_id=db_locked_query($dbr, $_DB_cand, "INSERT INTO $_DB_cand VALUES ('##NEW_ID##',
                                                                                                '$candidat_id',
                                                                                                '$transmission',
                                                                                                '$new_ordre',
                                                                                                '$__PREC_RECEVABLE',
                                                                                                '$new_motif_decision',
                                                                                                '$_SESSION[auth_id]',
                                                                                                '$new_ordre_spec',
                                                                                                '$new_groupe_spec',
                                                                                                '$cand_array[date_decision_unix]',
                                                                                                '$__DOSSIER_NON_TRAITE',
                                                                                                '$new_recours',
                                                                                                '$new_liste',
                                                                                                'Dossier transmis depuis : $cand_array[annee] - $spec_nom_transfert $cand_array[nom_finalite]',
                                                                                                '$cand_array[vap]',
                                                                                                '$new_masse',
                                                                                                '$new_talon_reponse',
                                                                                                '$statut_frais',
                                                                                                '$new_entretien_date',
                                                                                                '$new_entretien_heure',
                                                                                                '$new_entretien_lieu',
                                                                                                '$new_entretien_salle',
                                                                                                '$new_date_statut',
                                                                                                '$new_date_prise_decision',
                                                                                                '$__PERIODE',
                                                                                                '$new_session_id',
                                                                                                '$new_lock',
                                                                                                '$cand_array[lockdate]',
                                                                                                '$nb_rappels',
                                                                                                '0')");
               }
            }

            db_close($dbr);
            unset($_SESSION["inid"]);
            header("Location:edit_candidature.php");
            exit;
         }
      }
   }
            
   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>

<div class='main'>
   <?php
      if($cand_array["vap"])
         $vap_txt="Oui";
      else
         $vap_txt="Non";

      // on détermine la commission associée au diplôme demandé
      // on compare avec la date actuelle

      if($cand_array["date_decision_unix"]!=0 && $cand_array["date_decision_unix"]!="") // si on a déjà une date, on n'y touche pas
      {
         $date_com=date_fr("j F Y", $cand_array["date_decision_unix"]);
         $commission_txt="<font class='Texte'>$date_com</font>";
      }
      else // pas de date, on prend celle de la commission correspondante
      {
         if($cand_array["session_commission_unix"]==0) // normalement, impossible
            $commission_txt= "<font class='Texte_important'><b>Non parametrée</b></font>";
         else
         {
            $date_com=date_fr("j F Y", $cand_array["session_commission_unix"]);
            $commission_txt="<font class='Texte'>$date_com</font>";
            $cand_array["date_decision_unix"]=$cand_array["session_commission_unix"];
         }
      }

      // print("<input type='hidden' name='date_decision' value='$cand_array[date_decision_unix]'>\n");

      print("<form action=\"$php_self\" method=\"POST\"name=\"form1\">
               <input type='hidden' name='old_decision' value='$cand_array[decision]'>
               <input type='hidden' name='date_decision' value='$cand_array[date_decision_unix]'>
               <input type='hidden' name='vap' value='$cand_array[vap]'>
               <input type='hidden' name='annee' value='$cand_array[annee_id]'>
               <input type='hidden' name='spec' value='$cand_array[spec_id]'>

               <div class='infos_candidat Texte'>
                  <div style='text-align:right; float:left;'>
                     <strong>" . $_SESSION["tab_candidat"]["etudiant"] ." : " . $_SESSION["tab_candidat"]["civ_texte"] . " " . $_SESSION["tab_candidat"]["nom"] . " " . $_SESSION["tab_candidat"]["prenom"] .", " . $_SESSION["tab_candidat"]["ne_le"] . " " . $_SESSION["tab_candidat"]["txt_naissance"] ."</strong>
                  </div>
                  <div style='text-align:right; float:right; padding-right:10px;'>
                     <strong>VAP : </strong>$vap_txt<strong> Statut :</strong> $cand_array[decision_txt]<strong> Commission :</strong> $commission_txt
                  </div>
               </div>

               <div class='centered_box'>
                  <font class='Texte_16'><strong>Traitement d'une candidature : $cand_array[nom_complet]</strong></font>
               </div>");

      if(isset($rang_vide))
         message("Erreur : le rang sur la liste complémentaire doit être un entier positif", $__ERREUR);

      if(isset($aucune_decision))
         message("Erreur : vous devez sélectionner une décision", $__ERREUR);

      if(isset($motivation_vide))
         message("Erreur : la décision sélectionnée requiert un motif non vide", $__ERREUR);

      if(isset($date_maj))
         message("Date mise à jour avec succès", $__SUCCES);

      // message("Une motivation exclusive est <b>prioritaire</b> sur toutes les autres (champ libre compris).", $__WARNING);
      if(isset($erreur_format_entretien_date_heure))
         message("ERREUR : les informations sur la date et l'heure de l'entretien sont <b>incomplètes</b>", $__ERREUR);
   ?>

   <div style="max-width:80%; margin:0px auto 0px auto;">
      <table style="width:100%; margin:0px auto 20px auto;">
      <tr>
         <td colspan='2' class='td-complet fond_menu2' style='padding:2px 6px 2px 6px;'>
            <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;Décision</b></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Sélection de la décision : </b></font>
         </td>
         <td class='td-droite fond_menu'>
            <select name='decision' size='1'>
               <option value=''></option>
                  <?php
                     if(isset($decision))
                        $cand_array["decision"]=$decision;

                     $result2=db_query($dbr,"SELECT $_DBC_decisions_id, $_DBC_decisions_texte FROM $_DB_decisions
                                                WHERE $_DBC_decisions_id IN (SELECT distinct($_DBC_decisions_comp_dec_id) FROM $_DB_decisions_comp
                                                                              WHERE $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]')
                                             ORDER BY $_DBC_decisions_texte");

                     $rows2=db_num_rows($result2);

                     for($j=0; $j<$rows2; $j++)
                     {
                        list($decision_id,$decision_txt)=db_fetch_row($result2,$j);

                        $value=htmlspecialchars($decision_txt, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);

                        if($decision_id==$cand_array["decision"])
                           $selected="selected=1";
                        else
                           $selected="";

                        print("<option value='$decision_id' $selected>$decision_txt</option>\n");
                     }

                     db_free_result($result2);
                  ?>
            </select>
         </td>
      </tr>
      <?php
         // Uniquement si le transfert de dossier est possible dans cette composante
         if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_decisions_comp WHERE $_DBC_decisions_comp_dec_id='$__DOSSIER_TRANSMIS'
                                       AND $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]'")))
         {
      ?>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Transmission => Nouvelle formation : </b></font>
         </td>
         <td class='td-droite fond_menu'>
            <?php
               $result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_propspec_id_spec,
                                             $_DBC_specs_nom_court, $_DBC_specs_nom, $_DBC_specs_mention_id, $_DBC_propspec_finalite,
                                             $_DBC_mentions_nom, $_DBC_propspec_manuelle
                                          FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
                                       WHERE $_DBC_propspec_annee=$_DBC_annees_id
                                       AND $_DBC_propspec_id_spec=$_DBC_specs_id
                                       AND $_DBC_specs_mention_id=$_DBC_mentions_id
                                       AND $_DBC_propspec_active='1'
                                       AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom_court");

               $rows=db_num_rows($result);

               if($rows)
               {
                  print("<select size='1' name='transfert_formation'>
                           <option value=''></option>\n");

                  $old_annee="-1";
                  $old_mention="-1";

                  for($i=0; $i<$rows; $i++)
                  {
                     list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom_court, $form_spec_nom,
                           $form_mention, $form_finalite, $form_mention_nom, $manuelle)=db_fetch_row($result, $i);

                     $finalite_txt=$tab_finalite[$form_finalite];

                     if($form_annee_id!=$old_annee)
                     {
                        if($i!=0)
                           print("</optgroup>
                                    <option value='' label='' disabled></option>\n");

                        if($form_annee_nom=="")
                           $form_annee_nom="Années particulières";

                        print("<optgroup label='$form_annee_nom'>\n");

                        $new_sep_annee=1;

                        $old_annee=$form_annee_id;
                        $old_mention="-1";
                     }
                     else
                        $new_sep_annee=0;

                     if($form_mention!=$old_mention)
                     {
                        if(!$new_sep_annee)
                           print("</optgroup>
                                    <option value='' label='' disabled></option>\n");

                        $val=htmlspecialchars($form_mention_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);

                        print("<optgroup label='- $val'>\n");

                        $old_mention=$form_mention;
                     }

                     if($manuelle)
                        $manuelle_txt="(M)";
                     else
                        $manuelle_txt="";

                     if(isset($transmission) && $transmission==$form_propspec_id)
                        $selected="selected='1'";
                     elseif(isset($cand_array["transmission"]) && trim($cand_array["transmission"])==trim("$form_annee_nom - $form_spec_nom $cand_array[nom_finalite]"))
                        $selected="selected='1'";
                     else
                        $selected="";

                     print("<option value='$form_propspec_id' label=\"$form_spec_nom_court $finalite_txt $manuelle_txt\" $selected>$form_spec_nom_court $finalite_txt $manuelle_txt</option>\n");
                  }

                  print("</select>\n");
               }

               db_free_result($result);
            ?>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu' style='vertical-align:middle;'><b>Ou</b> formation en toute lettres :<br>(si celle-ci n'est pas gérée par l'interface)</font>
         </td>
         <td class='td-droite fond_menu'>
            <input type='text' name='transmission_libre' value='<?php if(isset($transmission_txt) && (!isset($transmission) || $transmission=='')) echo htmlspecialchars($transmission_txt, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='50' maxlength='256'>
            &nbsp;&nbsp;<font class='Texte_menu'><i>(Champ prioritaire sur le précédent)</i></font>
         </td>
      </tr>
      <?php
         }
      ?>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Rang sur liste complémentaire</b></font>
         </td>
         <td class='td-droite fond_menu'>
            <input class="text" name='rang_liste' value='<?php if(isset($cand_array["rang_attente"])) echo $cand_array["rang_attente"]; ?>' size='25' maxlength='4'>
            <font class='Texte_menu'>
               <i>&nbsp;&nbsp;Par défaut : dernière position dans la liste</i>
            </font>
            <?php
               // TODO : afficher les positions disponibles ?
            ?>
         </td>
      </tr>
      <?php
         if($cand_array["entretiens"])
         {
            if($cand_array["entretien_date_unix"]!=0 && $cand_array["entretien_date_unix"]!="")
            {
               $cand_array_entretien_jour=date("j", $cand_array["entretien_date_unix"]);
               $cand_array_entretien_mois=date("m", $cand_array["entretien_date_unix"]);
               $cand_array_entretien_annee=date("Y", $cand_array["entretien_date_unix"]);
               $cand_array_entretien_heure=date("H", $cand_array["entretien_date_unix"]);
               $cand_array_entretien_minute=date("i", $cand_array["entretien_date_unix"]);

               if($cand_array_entretien_heure==0)
                  $cand_array_entretien_minute=$cand_array_entretien_heure="";
            }
            else
            {
               $cand_array_entretien_annee=$cand_array_entretien_jour=$cand_array_entretien_mois=$cand_array_entretien_heure=$cand_array_entretien_minute="";
            }

      ?>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Entretien : </b></font>
         </td>
         <td class='td-droite fond_menu'>
            <table cellpadding="0" cellspacing="0" border="0" align="left">
            <tr>
               <td><font class='Texte_menu'><b>Date :</b></font></td>
               <td>
                  <font class='Texte_menu'>
                     JJ : <input type="text" name='entretien_jour' value='<?php if(isset($entretien_jour)) echo htmlspecialchars($entretien_jour, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); else echo htmlspecialchars($cand_array_entretien_jour, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='3' maxlength='2'>&nbsp;
                     MM : <input type="text" name='entretien_mois' value='<?php if(isset($entretien_mois)) echo htmlspecialchars($entretien_mois, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); else echo htmlspecialchars($cand_array_entretien_mois, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='3' maxlength='2'>&nbsp;
                     AAAA : <input type="text" name='entretien_annee' value='<?php if(isset($entretien_annee)) echo htmlspecialchars($entretien_annee, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); else echo htmlspecialchars($cand_array_entretien_annee, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='5' maxlength='4'>&nbsp;&nbsp;
                  </font>
               </td>
               <td><font class='Texte_menu'><b>Heure :</b></font></td>
               <td>
                  <font class='Texte_menu'>
                     h : <input type="text" name='entretien_heure' value='<?php if(isset($entretien_h)) echo htmlspecialchars($entretien_h, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); else echo htmlspecialchars($cand_array_entretien_heure, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='3' maxlength='2'> min : <input type="text" name='entretien_minute' value='<?php if(isset($entretien_m)) echo htmlspecialchars($entretien_m, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); else echo htmlspecialchars($cand_array_entretien_minute, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='3' maxlength='2'>
                  </font>
               </td>
            </tr>
            <tr>
               <td><font class='Texte_menu'><b>Salle :</b></font></td>
               <td>
                  <input type="text" name='entretien_salle' value='<?php if(isset($entretien_salle)) echo htmlspecialchars(stripslashes($entretien_salle), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); else echo htmlspecialchars($cand_array["entretien_salle"], ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='25' maxlength='50'>
               <td><font class='Texte_menu'><b>Lieu :</b></font></td>
               <td>
                  <input type="text" name='entretien_lieu' value='<?php if(isset($entretien_lieu)) echo htmlspecialchars(stripslashes($entretien_lieu), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); else echo htmlspecialchars($cand_array["entretien_lieu"], ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='40' maxlength='128'>
               </td>
            </tr>
            <tr>
               <td colspan='4'>
                  <font class='Texte_menu'>
                  <i>Exemple : Salle = "salle 301", lieu = "à l'UFR ... située rue ..."</i>
                  <br>Si la salle et/ou le lieu sont vides, les valeurs par défaut seront utilisées (Outils => Modifier une composante)</i>
                  </font>
               </td>
            </tr>
            </table>
         </td>
      </tr>
      <?php
         }
         else
            print("<input type='hidden' name='entretien_date' value=''>
                     <input type='hidden' name='entretien_heure' value=''>
                     <input type='hidden' name='entretien_lieu' value=''>
                     <input type='hidden' name='entretien_salle' value=''>\n");
      ?>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Confirmation du candidat ?</b></font>
         </td>
         <td class='td-droite fond_menu'>
            <select name='talon_reponse' size='1'>
            <?php
                  switch($cand_array["talon_reponse"])
                  {
                     case 0   :   // talon non renvoyé (par défaut)
                                 $selected_non_renvoye="selected=1";
                                 $selected_confirme=$selected_refus="";
                                 break;

                     case 1   :   // talon renvoyé, inscription confirmée
                                 $selected_confirme="selected=1";
                                 $selected_non_renvoye=$selected_refus="";
                                 break;

                     case -1   :   // talon renvoyé, inscription refusée
                                 $selected_refus="selected=1";
                                 $selected_confirme=$selected_non_renvoye="";
                                 break;

                     default : // talon non renvoyé (par défaut)
                                 $selected_non_renvoye="selected=1";
                                 $selected_confirme=$selected_refus="";
                                 break;
               }

                  print("<option value='0'  $selected_non_renvoye>Talon Non renvoyé</option>
                           <option value='1'  $selected_confirme>Admission confirmée</option>
                           <option value='-1' $selected_refus>Admission refusée</option>\n");
               ?>
            </select>
            &nbsp;&nbsp;<font class='Texte_menu'><i>(en cas de présence d'un talon réponse sur vos modèles de lettres)</i></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Forcer la date des lettres :</b><br>(<i>A manipuler <b>avec prudence</b></i>)</font>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
               <select name='force_jour'>
                  <?php
                     if(ctype_digit($cand_array["date_decision_unix"]))
                     {
                        $force_date_jour=date("j", $cand_array["date_decision_unix"]);
                        $force_date_mois=date("n", $cand_array["date_decision_unix"]);
                        $force_date_annee=date("Y", $cand_array["date_decision_unix"]);
                     }
                     else
                     {
                        $force_date_jour=$force_date_mois=0;
                        $force_date_annee=date("Y");
                     }

                     for($j=1; $j<=31; $j++)
                     {
                        if($force_date_jour==$j)
                           $selected="selected";
                        else
                           $selected="";

                        print("<option value='$j' $selected>$j</option>\n");
                     }
                  ?>
               </select>
               <select name='force_mois'>
                  <option value='1' <?php if($force_date_mois==1) echo "selected"; ?>>Janvier</option>
                  <option value='2' <?php if($force_date_mois==2) echo "selected"; ?>>Fevrier</option>
                  <option value='3' <?php if($force_date_mois==3) echo "selected"; ?>>Mars</option>
                  <option value='4' <?php if($force_date_mois==4) echo "selected"; ?>>Avril</option>
                  <option value='5' <?php if($force_date_mois==5) echo "selected"; ?>>Mai</option>
                  <option value='6' <?php if($force_date_mois==6) echo "selected"; ?>>Juin</option>
                  <option value='7' <?php if($force_date_mois==7) echo "selected"; ?>>Juillet</option>
                  <option value='8' <?php if($force_date_mois==8) echo "selected"; ?>>Août</option>
                  <option value='9' <?php if($force_date_mois==9) echo "selected"; ?>>Septembre</option>
                  <option value='10' <?php if($force_date_mois==10) echo "selected"; ?>>Octobre</option>
                  <option value='11' <?php if($force_date_mois==11) echo "selected"; ?>>Novembre</option>
                  <option value='12' <?php if($force_date_mois==12) echo "selected"; ?>>Décembre</option>
               </select>
               <input type='text' name='force_annee' maxlength="4" size="6" value='<?php echo $force_date_annee; ?>'>
               &nbsp;&nbsp;<i>(Par défaut : date de la Commission la plus proche lors de l'ajout de la candidature)</i>
            </font>
         </td>
      </tr>
      </table>

      <table style="width:100%; margin:0px auto 0px auto;">
      <tr>
         <td colspan='2' class='td-complet fond_menu2' style='padding:2px 6px 2px 6px;'>
            <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;Motivation(s) de la décision</b></font>
         </td>
      </tr>
      <?php
         if(!isset($_SESSION["gestion_motifs"]) || $_SESSION["gestion_motifs"]==0)
         {
      ?>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Motifs prédéfinis</b></font>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte_menu'><b>Autre(s) motif(s) ou détail de la décision (mise en attente, etc)</b></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <table width='100%' cellpadding='0' cellspacing='0' border='0'>
               <?php
                  // décisions actuelles
                  $array_current_motifs=explode("|", $cand_array["motivations_id"]);
                  $cnt2=count($array_current_motifs);
   
                  $result=db_query($dbr,"SELECT $_DBC_motifs_refus_id, $_DBC_motifs_refus_motif, $_DBC_motifs_refus_exclusif
                                                            FROM $_DB_motifs_refus
                                                         WHERE $_DBC_motifs_refus_comp_id=$_SESSION[comp_id]
                                                            ORDER BY $_DBC_motifs_refus_motif");
                  $rows=db_num_rows($result);
   
                  if($rows)
                  {
                     for($i=0; $i<$rows; $i++)
                     {
                        list($motif_id,$motif,$motif_exclusif)=db_fetch_row($result,$i);
                        $value=htmlspecialchars($motif, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);
   
                        for($k=0; $k<$cnt2; $k++)
                        {
                           if($motif_id==$array_current_motifs[$k])
                           {
                              $checked="checked";
                              $k=$cnt2; // pour sortir rapidement de la boucle
                           }
                           else
                              $checked="";
                        }
   
                        if($motif_exclusif)
                        {
                           $class="Textebleu";
                           $exclusif="(exclusif)";
                        }
                        else
                        {
                           $class="Texte_menu";
                           $exclusif="";
                        }
   
                        // TODO : attention si on a un nombre impair de motifs
                        if(($i%2)==0) // affichage sur 2 colonnes
                           print("<tr>
                                    <td align='left' style='padding-top:2px; padding-bottom:2px; white-space:normal;'>
                                       <input type='checkbox' name='ref_$motif_id' value='$motif_id' $checked style='vertical-align:middle;'><font class='$class'>$value $exclusif</font>
                                    </td>");
                        else
                           print("<td align='left' style='padding-top:2px; padding-bottom:2px; white-space:normal;'>
                                    <input type='checkbox' name='ref_$motif_id' value='$motif_id' $checked style='vertical-align:middle;'><font class='$class'>$value $exclusif</font>
                                 </td>
                              </tr>");
                     }
                  }
                  else
                     print("<font class='Texte_important_menu'>Attention : aucun motif défini</font>\n");
   
                  db_free_result($result);
               ?>
            </table>
         </td>
         <td class='td-droite fond_menu'>
            <textarea class='input' cols='70' rows='5' name='motivation_decision_libre'><?php
               for($l=0; $l<$cnt2 ; $l++)
               {
                  if(!strncmp($array_current_motifs[$l], '@',1))
                  {
                     $value=$array_current_motifs[$l];
                     echo htmlspecialchars(substr(stripslashes($value),1), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);
                  }
               }
            ?></textarea>
         </td>
      </tr>
      <?php
         }
         elseif(isset($_SESSION["gestion_motifs"]) && $_SESSION["gestion_motifs"]==1)
         {
      ?>
      <tr>
         <td class='td-complet fond_menu' colspan='2'>
            <font class='Texte_menu'><b>Refus</b></font>
            <br>
            <select name='motivation'>
               <option value=''></option>
            <?php
                  // décisions actuelles
                  $array_current_motifs=explode("|", $cand_array["motivations_id"]);
                  $cnt2=count($array_current_motifs);
   
                  $current_motif=$array_current_motifs[0];
   
                  $result=db_query($dbr,"SELECT $_DBC_motifs_refus_id, $_DBC_motifs_refus_motif, $_DBC_motifs_refus_exclusif
                                                            FROM $_DB_motifs_refus
                                                         WHERE $_DBC_motifs_refus_comp_id=$_SESSION[comp_id]
                                                            ORDER BY $_DBC_motifs_refus_motif");
   
                  $rows=db_num_rows($result);
   
                  for($i=0; $i<$rows; $i++)
                  {
                     list($motif_id,$motif,$motif_exclusif)=db_fetch_row($result,$i);
                     $value=htmlspecialchars($motif, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);
   
                     if($motif_id==$current_motif)
                        $selected="selected=1";
                     else
                        $selected="";
   
                     if($motif_exclusif)
                        $exclusif="(exclusif)";
                     else
                        $exclusif="";
   
                     print("<option value='$motif_id' $selected>$motif</option>\n");
                  }
                  db_free_result($result);
               ?>
            </select>
         </td>
      </tr>
      <tr>
         <td class='td-complet fond_menu' colspan='2'>
            <font class='Texte_menu'><b>Autre(s) motif(s) ou complément du motif sélectionné ci-dessus</b></font>
            <br>
            <textarea class='input' cols='70' rows='5' name='motivation_decision_libre'><?php
               for($l=0; $l<$cnt2 ; $l++)
               {
                  if(!strncmp($array_current_motifs[$l], '@',1))
                  {
                     $value=$array_current_motifs[$l];
                     echo htmlspecialchars(substr(stripslashes($value), 1), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);
                  }
               }
            ?></textarea>
         </td>
      </tr>
   
      <?php
         }
         db_close($dbr);
      ?>
      </table>
   </div>

   <div class='centered_icons_box'>
      <a href='edit_candidature.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
      <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go_valider" value="Valider">
      </form>
   </div>
</div>

<?php
   pied_de_page();
?>

<script language="javascript">
   document.form1.annee.focus()
</script>
</body></html>
