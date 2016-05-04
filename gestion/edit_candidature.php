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

   // paramètre spécial : consultation directe d'un candidat (identifiant "fiche") à partir d'un mail "message"
   if(isset($_GET["fiche"]) && ctype_digit($_GET["fiche"]) && isset($_GET["dco"]) && ctype_digit($_GET["dco"]))
   {
      $_GET["fiche"]=str_replace(" ", "", $_GET["fiche"]);
      $_GET["dco"]=str_replace(" ", "", $_GET["dco"]);

      $candidat_id=$_SESSION["candidat_id"]=$_SESSION["fiche_id"]=$_GET["fiche"];

      // Si l'utilisateur était déjà connecté, on bascule la composante (éventuellement)
      if(isset($_SESSION["comp_id"]) && isset($_SESSION["auth_id"]) && $_GET["dco"] != $_SESSION["comp_id"])
      {
         $dbr=db_connect();

         if((isset($_SESSION["niveau"]) && in_array($_SESSION["niveau"], array("$__LVL_ADMIN", "$__LVL_SUPPORT")))
            || db_num_rows(db_query($dbr, "SELECT * FROM $_DB_acces_comp WHERE $_DBC_acces_comp_acces_id='$_SESSION[auth_id]'
                                                    AND $_DBC_acces_comp_composante_id='$_GET[dco]'")))
         {
            $result=db_query($dbr, "SELECT $_DBC_composantes_nom, $_DBC_universites_nom, $_DBC_universites_img_dir,
                                           $_DBC_universites_id, $_DBC_universites_css,
                                           $_DBC_composantes_gestion_motifs, $_DBC_composantes_scolarite,
                                           $_DBC_composantes_affichage_decisions
                                       FROM $_DB_composantes, $_DB_universites
                                    WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
                                    AND $_DBC_composantes_id='$_GET[dco]'");

            list($_SESSION["composante"],
                  $_SESSION["universite"],
                  $_SESSION["img_dir"],
                  $_SESSION["univ_id"],
                  $_SESSION["css"],
                  $_SESSION["gestion_motifs"],
                  $_SESSION["adr_scol"],
                  $_SESSION["affichage_decisions"])=db_fetch_row($result, 0);

            db_free_result($result);
            db_close($dbr);

            $_SESSION["dco"]=$_GET["dco"];
            $_SESSION["comp_id"]=$_GET["dco"];

            header("Location:$php_self?cid=$candidat_id");
            exit;
         }
         else // pas accès
         {
            db_close($dbr);
            header("Location:index.php");
            exit;
         }
      }
      else // Pas encore authentifié : on conserve la composante pour l'accès direct et on redirige vers la page d'authentification
         $_SESSION["dco"]=$_GET["dco"];
   }

   verif_auth();

   // paramètre : identifiant du candidat
   if(isset($_GET["cid"]) && ctype_digit($_GET["cid"]))
   {
      $_SESSION["candidat_id"]=$candidat_id=$_GET["cid"];

      // Au cas où on viendrait de la page Recherche, on regarde si on peut basculer vers la composante dans laquelle le candidat a déposé un voeu
      if(isset($_GET["rech"]) && $_GET["rech"]==1)
      {
         $dbr=db_connect();

         if(isset($_SESSION["niveau"]) && in_array($_SESSION["niveau"], array("$__LVL_ADMIN", "$__LVL_SUPPORT")))
         {
            // Administrateur et support : on ignore les droits d'accès sur les composantes
            $result=db_query($dbr, "SELECT distinct($_DBC_composantes_id) as composante_id,$_DBC_composantes_nom, $_DBC_universites_nom,
                                          $_DBC_universites_img_dir, $_DBC_universites_id as univ_id,
                                          $_DBC_universites_css, $_DBC_composantes_gestion_motifs,
                                          $_DBC_composantes_scolarite, $_DBC_composantes_affichage_decisions, $_DBC_cand_id
                                    FROM $_DB_propspec, $_DB_cand, $_DB_composantes, $_DB_universites
                                       WHERE $_DBC_cand_candidat_id='$candidat_id'
                                       AND $_DBC_composantes_univ_id=$_DBC_universites_id
                                       AND $_DBC_composantes_id=$_DBC_propspec_comp_id
                                       AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                       AND $_DBC_cand_periode='$__PERIODE'
                                    ORDER BY $_DBC_cand_id");
         }
         else
            $result=db_query($dbr, "SELECT distinct($_DBC_composantes_id) as composante_id,$_DBC_composantes_nom, $_DBC_universites_nom,
                                          $_DBC_universites_img_dir, $_DBC_universites_id as univ_id,
                                          $_DBC_universites_css, $_DBC_composantes_gestion_motifs,
                                          $_DBC_composantes_scolarite, $_DBC_composantes_affichage_decisions, $_DBC_cand_id
                                    FROM $_DB_propspec, $_DB_cand, $_DB_acces_comp, $_DB_composantes, $_DB_universites
                                       WHERE $_DBC_cand_candidat_id='$candidat_id'
                                       AND $_DBC_composantes_univ_id=$_DBC_universites_id
                                       AND $_DBC_composantes_id=$_DBC_propspec_comp_id
                                       AND $_DBC_acces_comp_composante_id=$_DBC_composantes_id
                                       AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                       AND $_DBC_cand_periode='$__PERIODE'
                                       AND $_DBC_acces_comp_acces_id='$_SESSION[auth_id]'
                                    ORDER BY $_DBC_cand_id");

         // Si on a un résultat, c'est que l'utilisateur a accès à la composante dans laquelle le candidat a demandé une candidature
         if($rows=db_num_rows($result))
         {
            $all=db_fetch_all($result);

            // print_r($all);

            foreach($all as $i => $array_result)
            {
               if($array_result["composante_id"] == $_SESSION["comp_id"]) // composante courante trouvée : on s'arrete
               {
                  $found=1;
                  $_SESSION["comp_id"]=$new_comp_id=$array_result["composante_id"];
               }
            }

            if(!isset($found)) // composante courante non trouvée, on change en prenant la première
               list($new_comp_id,
                     $_SESSION["composante"],
                     $_SESSION["universite"],
                     $_SESSION["img_dir"],
                     $_SESSION["univ_id"],
                     $_SESSION["css"],
                     $_SESSION["gestion_motifs"],
                     $_SESSION["adr_scol"],
                     $_SESSION["affichage_decisions"],
                     $cand_id_trash)=db_fetch_row($result, 0);

            // Si on change de composante, on recharge la page
            if($_SESSION["comp_id"]!=$new_comp_id)
            {
               $_SESSION["comp_id"]=$new_comp_id;

               db_free_result($result);
               db_close($dbr);
   
               header("Location:$php_self");
               exit;
            }
         }
         // Sinon, on reste dans la composante courante
         db_free_result($result);
         db_close($dbr);
      }
   }
   elseif(isset($_SESSION["candidat_id"]))
      $candidat_id=$_SESSION["candidat_id"];
   else
   {
      header("Location:index.php");
      exit;
   }

   // Onglet par défaut : identité (1)
   if(isset($_GET["onglet"]) && is_numeric($_GET["onglet"]) && $_GET["onglet"]>0 && $_GET["onglet"]<=10)
      $_SESSION["onglet"]=$_GET["onglet"];
   elseif(!isset($_SESSION["onglet"]))
      $_SESSION["onglet"]=1;

   if(!in_array($_SESSION['niveau'], array("$__LVL_SUPPORT", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["onglet"]==7))
      $_SESSION["onglet"]=1;

   $dbr=db_connect();

   // Changement éventuel de composante
   if(isset($_GET["co"]) && ctype_digit($_GET["co"]))
   {
      $new_comp_id=$_GET["co"];

      // Vérification de l'accès
      if(in_array($_SESSION["niveau"], array("$__LVL_ADMIN", "$__LVL_SUPPORT")) || db_num_rows(db_query($dbr, "SELECT $_DBC_acces_comp_composante_id FROM $_DB_acces_comp
                                                                              WHERE $_DBC_acces_comp_acces_id='$_SESSION[auth_id]'
                                                                           AND $_DBC_acces_comp_composante_id='$new_comp_id'")))
      {
         // Récupération des paramètres propres à cette composante, si elle existe
         $result=db_query($dbr, "SELECT $_DBC_composantes_nom, $_DBC_universites_nom, $_DBC_universites_img_dir, $_DBC_universites_id, 
                                        $_DBC_universites_css, $_DBC_composantes_gestion_motifs, $_DBC_composantes_scolarite,
                                        $_DBC_composantes_affichage_decisions
                                    FROM $_DB_composantes, $_DB_universites
                                 WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
                                 AND $_DBC_composantes_id='$new_comp_id'");

         $rows=db_num_rows($result);

         if($rows)
         {

            list($_SESSION["composante"],
                  $_SESSION["universite"],
                  $_SESSION["img_dir"],
                  $_SESSION["univ_id"],
                  $_SESSION["css"],
                  $_SESSION["gestion_motifs"],
                  $_SESSION["adr_scol"],
                  $_SESSION["affichage_decisions"])=db_fetch_row($result, 0);

            $_SESSION['comp_id']=$new_comp_id;

            db_free_result($result);
            db_close($dbr);

            header("Location:$php_self?onglet=1");
            exit();
         }
      }
   }

   // Libération de la fiche
   cand_unlock($dbr, $candidat_id);

   // récupération des infos du candidat
   $result=db_query($dbr,"SELECT $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_nom_naissance, $_DBC_candidat_prenom, $_DBC_candidat_prenom2,
                                 $_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance, $_DBC_candidat_dpt_naissance, 
                                 $_DBC_candidat_nationalite,
                                 (SELECT $_DBC_pays_nat_ii_nat FROM $_DB_pays_nat_ii
                                    WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite) as nationalite,
                                 $_DBC_candidat_telephone, $_DBC_candidat_telephone_portable, $_DBC_candidat_adresse_1, $_DBC_candidat_adresse_2, 
                                 $_DBC_candidat_adresse_3, $_DBC_candidat_numero_ine, $_DBC_candidat_email, $_DBC_candidat_connexion, 
                                 $_DBC_candidat_pays_naissance,
                                 (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii
                                    WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance) as pays_naissance,
                                 $_DBC_candidat_adresse_cp, $_DBC_candidat_adresse_ville, $_DBC_candidat_adresse_pays,
                                 (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii
                                    WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_adresse_pays) as adresse_pays,
                                 $_DBC_candidat_manuelle, $_DBC_candidat_deja_inscrit, $_DBC_candidat_annee_premiere_inscr,
                                 $_DBC_candidat_annee_bac, $_DBC_candidat_serie_bac, $_DBC_candidat_identifiant
                             FROM $_DB_candidat
                          WHERE $_DBC_candidat_id='$candidat_id'");
   $rows=db_num_rows($result);

   if($rows)
   {
      $_SESSION['tab_candidat']=array();

      list($_SESSION['tab_candidat']['civilite'],
            $_SESSION['tab_candidat']['nom'],
            $_SESSION['tab_candidat']['nom_naissance'],
            $_SESSION['tab_candidat']['prenom'],
            $_SESSION['tab_candidat']['prenom2'],
            $date_naiss,
            $_SESSION['tab_candidat']['lieu_naissance'],
            $_SESSION['tab_candidat']['dpt_naissance'],
            $_SESSION['tab_candidat']['nationalite_code'],
            $_SESSION['tab_candidat']['nationalite'],
            $_SESSION['tab_candidat']['telephone'],
            $_SESSION['tab_candidat']['telephone_portable'],
            $_SESSION['tab_candidat']['adresse_1'],
            $_SESSION['tab_candidat']['adresse_2'],
            $_SESSION['tab_candidat']['adresse_3'],
            $_SESSION['tab_candidat']['numero_ine'],
            $_SESSION['tab_candidat']['email'],
            $connexion,
            $_SESSION['tab_candidat']['pays_naissance_code'],
            $_SESSION['tab_candidat']['pays_naissance'],
            $_SESSION['tab_candidat']['adresse_cp'],
            $_SESSION['tab_candidat']['adresse_ville'],
            $_SESSION['tab_candidat']['adresse_pays_code'],
            $_SESSION['tab_candidat']['adresse_pays'],
            $_SESSION['tab_candidat']['manuelle'],
            $_SESSION['tab_candidat']['deja_inscrit'],
            $_SESSION['tab_candidat']['annee_premiere_inscr'],
            $_SESSION['tab_candidat']['annee_bac'],
            $_SESSION['tab_candidat']['serie_bac'],
            $_SESSION['tab_candidat']['identifiant'])=db_fetch_row($result,0);

      $_SESSION['tab_candidat']["adresse"]=$_SESSION['tab_candidat']["adresse_1"];
      $_SESSION['tab_candidat']["adresse"].=$_SESSION['tab_candidat']["adresse_2"]!="" ? "\n".$_SESSION['tab_candidat']["adresse_2"] : "";
      $_SESSION['tab_candidat']["adresse"].=$_SESSION['tab_candidat']["adresse_3"]!="" ? "\n".$_SESSION['tab_candidat']["adresse_3"] : "";

      $_SESSION['tab_candidat']['naissance']=$date_naiss;
      $_SESSION['tab_candidat']['txt_naissance']=date_fr("j F Y",$date_naiss);

      if($connexion!=0)
         $_SESSION['tab_candidat']['derniere_connexion']=date_fr("j F Y - H:i", $connexion);
      else
         $_SESSION['tab_candidat']['derniere_connexion']="Jamais connecté";

      switch($_SESSION['tab_candidat']['civilite'])
      {
         case "M" :        $_SESSION['tab_candidat']['civ_texte']="M.";
                           $_SESSION['tab_candidat']['etudiant']="Candidat";
                           $_SESSION['tab_candidat']['etudiant_particule']="le Candidat";
                           $_SESSION['tab_candidat']['etudiant_coi']="au Candidat";
                           $_SESSION['tab_candidat']['ne_le']="Né le";
                           break;

         case   "Mlle" :   $_SESSION['tab_candidat']['civ_texte']="Mlle";
                           $_SESSION['tab_candidat']['etudiant']="Candidate";
                           $_SESSION['tab_candidat']['etudiant_particule']="la Candidate";
                           $_SESSION['tab_candidat']['etudiant_coi']="à la Candidate";
                           $_SESSION['tab_candidat']['ne_le']="Née le";
                           break;

         case   "Mme"   :  $_SESSION['tab_candidat']['civ_texte']="Mme";
                           $_SESSION['tab_candidat']['etudiant']="Candidate";
                           $_SESSION['tab_candidat']['etudiant_particule']="la Candidate";
                           $_SESSION['tab_candidat']['etudiant_coi']="à la Candidate";
                           $_SESSION['tab_candidat']['ne_le']="Née le";
                           break;

         default      :    $_SESSION['tab_candidat']['civ_texte']="M.";
                           $_SESSION['tab_candidat']['etudiant']="Candidat";
                           $_SESSION['tab_candidat']['etudiant_particule']="le Candidat";
                           $_SESSION['tab_candidat']['etudiant_coi']="au Candidat";
                           $_SESSION['tab_candidat']['ne_le']="Né le";
      }

      db_free_result($result);

      // Autres informations
      // Département de naissance
      if($_SESSION['tab_candidat']['dpt_naissance']!="")
      {
         $res_departements=db_query($dbr, "SELECT $_DBC_departements_fr_nom FROM $_DB_departements_fr
                                             WHERE $_DBC_departements_fr_numero='".$_SESSION["tab_candidat"]["dpt_naissance"]."'");

         if(db_num_rows($res_departements))
            list($_SESSION['tab_candidat']["nom_departement"])=db_fetch_row($res_departements, 0);
         else
            $_SESSION['tab_candidat']["nom_departement"]="";

         db_free_result($res_departements);
      }
      else
         $_SESSION['tab_candidat']["nom_departement"]="";

      // Série du bac
      if($_SESSION['tab_candidat']['serie_bac']!="")
      {
         $res_series_bac=db_query($dbr, "SELECT $_DBC_diplomes_bac_intitule FROM $_DB_diplomes_bac
                                          WHERE $_DBC_diplomes_bac_code='".$_SESSION["tab_candidat"]["serie_bac"]."'");

         if(db_num_rows($res_series_bac))
            list($_SESSION['tab_candidat']['nom_serie_bac'])=db_fetch_row($res_series_bac, 0);
         else
            $_SESSION['tab_candidat']['nom_serie_bac']="";

         db_free_result($res_series_bac);
      }
      else
         $_SESSION['tab_candidat']["nom_serie_bac"]="";

      // Vu par la personne qui consulte ?
      if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_acces_candidats_lus
                                       WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
                                       AND $_DBC_acces_candidats_lus_candidat_id='$candidat_id'
                                       AND $_DBC_acces_candidats_lus_periode='$__PERIODE'")))
         $candidat_vu=1;
      else
         $candidat_vu=-1;
   }
   else
   {
      db_close($dbr);
      header("Location:index.php");
      exit();
   }

   // Modification du statut des candidatures ou retour à la page précédente

   if(in_array($_SESSION["niveau"], array("$__LVL_SUPPORT", "$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
   {
      // Restauration d'une candidature annulée
      if($_SESSION["niveau"]!=$__LVL_SUPPORT && isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
      {
         if(isset($params["cand_id"]) && ctype_digit($params["cand_id"]) && isset($params["r"]) && $params["r"]==1)
         {
            $restore_cand_id=$params["cand_id"];

            if(db_num_rows($res_decision=db_query($dbr, "SELECT $_DBC_cand_decision FROM $_DB_cand
                                                            WHERE $_DBC_cand_id='$restore_cand_id'
                                                            AND $_DBC_cand_candidat_id='$candidat_id'
                                                            AND $_DBC_cand_statut='$__PREC_ANNULEE'")))
            {
               list($cur_decision)=db_fetch_row($res_decision, 0);

               // En fonction de la décision de commission, on ne restaure pas le même statut
               // => si une décision a déjà été prise, on met la précandidature recevable
               $new_statut=$cur_decision==$__DOSSIER_NON_TRAITE ? $__PREC_NON_TRAITEE : $__PREC_RECEVABLE;

               db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_statut='$new_statut'
                                 WHERE $_DBU_cand_id='$restore_cand_id'
                                 AND $_DBU_cand_candidat_id='$candidat_id'
                                 AND $_DBU_cand_statut='$__PREC_ANNULEE'");

               $rest_succes=1;
            }
         }
      }

      if($_SESSION["niveau"]!=$__LVL_SUPPORT && (isset($_POST["go_cursus"]) || isset($_POST["go_cursus_x"])))
      {
         // en fonction de l'onglet actif et du tableau présent en $_SESSION, ce ne sont pas les mêmes infos qui sont validées

         // ============================================
         //    TRAITEMENT DES JUSTIFICATIFS DU CURSUS
         // ============================================

         if(isset($_SESSION['tab_cursus']))
         {
             $contenu_mail="Composante : $_SESSION[composante]\n\nConcernant votre cursus, les pièces justificatives que vous nous avez transmises ont été réceptionnées.\n\nStatut de votre cursus :\n\n";
            $modifs=0;
            $pieces_manquantes=0;

            foreach($_SESSION["tab_cursus"] as $cu_id => $cursus_array)
            {
               $key_justifie="justification_".$cu_id;
               $key_precision="precision_".$cu_id;

               $texte=$cursus_array["texte"];
               $statut_actuel=$cursus_array["justifie"];
               $precision_actuelle=$cursus_array["precision"];

               $nouveau_statut=$_POST["$key_justifie"];
               $nouvelle_precision=$_POST["$key_precision"];

               if($nouveau_statut==$__CURSUS_VALIDE) // plus besoin du champ "précision" pour un cursus validé
               {
                  $nouvelle_precision="";
                  $contenu_mail.="- $texte : Justificatifs reçus\n\n";
               }
               elseif($nouveau_statut==$__CURSUS_NON_NECESSAIRE) // idem pour un justificatif non nécessaire
               {
                  $nouvelle_precision="";
                  $contenu_mail.="- $texte : Justificatifs non nécessaires pour cette étape\n\n";
               }
               elseif($nouveau_statut==$__CURSUS_NON_JUSTIFIE) // aucun justificatif jamais reçu
               {
                  $nouvelle_precision="";
                  $contenu_mail.="- $texte : Information non confirmée car aucun justificatif n'a été reçu\n\n";
               }
               elseif($nouveau_statut==$__CURSUS_EN_ATTENTE)
               {
                  $nouvelle_precision="";
                  $contenu_mail.="- $texte : Pièces non reçues ou en cours de traitement\n\n";
               }
               elseif($nouveau_statut==$__CURSUS_DES_OBTENTION) // Pour les cursus en cours : précision facultative
               {
                  $contenu_mail.="- $texte : Pièce(s) à fournir dès l'obtention du diplôme";
                  $contenu_mail.=($nouvelle_precision!="") ? " : $nouvelle_precision\n\n" : "\n\n";
               }
               else
               {
                  $pieces_manquantes++;

                  if($nouvelle_precision=="")
                  {
                     $precision_vide=1;
                     $texte_precision="";
                  }
                  else
                     $texte_precision=stripslashes("($nouvelle_precision)");

                  $contenu_mail.="- $texte : Pièces manquantes $texte_precision\n\n";
               }

               if($nouveau_statut!=$statut_actuel || $nouvelle_precision!=$precision_actuelle) // changement détecté
               {
                  $modifs++;

                  $req="UPDATE $_DB_cursus_justif SET 
                        $_DBU_cursus_justif_statut='".preg_replace("/[']+/", "''", stripslashes($nouveau_statut))."',
                        $_DBU_cursus_justif_precision='".preg_replace("/[']+/", "''", stripslashes($nouvelle_precision))."'
                        WHERE $_DBU_cursus_justif_cursus_id='$cu_id'
                        AND $_DBU_cursus_justif_comp_id='$_SESSION[comp_id]'
                        AND $_DBU_cursus_justif_periode='$__PERIODE'";

                  db_query($dbr, $req);

                  write_evt($dbr, $__EVT_ID_G_CURSUS, "Statut cursus $cu_id : $nouveau_statut", $candidat_id, $cu_id, $req);
               }

               unset($nouveau_statut);
               unset($nouvelle_precision);
            }

            // envoi du mail de notification en cas de changement de statuts
            if($modifs)
            {
               if($pieces_manquantes)
                  $contenu_mail.="\nEn l'état, vos précandidatures ne sont pas recevables. Merci d'envoyer la ou les pièces manquantes le plus rapidement possible.\n\nNous rappelons qu'un dossier incomplet ne sera PAS TRAITE.";

               $contenu_mail.="\n\n<b>Nous rappelons que ce statut est uniquement valable pour la composante mentionnée. Si vous avez déposé un dossier dans une autre composante, ce statut pourra être différent en fonction des pièces que vous avez envoyées.</b>";

               $civ_mail=$_SESSION['tab_candidat']['civ_texte'];
               $cand_prenom=$_SESSION['tab_candidat']['prenom'];
               $nom_mail=ucwords(mb_strtolower($_SESSION['tab_candidat']['nom'], "UTF-8"));

               $message="Bonjour $civ_mail $nom_mail,\n
$contenu_mail\n\n
Cordialement,\n\n
--
$_SESSION[adr_scol]\n
$_SESSION[composante]
$_SESSION[universite]";

               $dest_array=array("0" => array("id"       => "$candidat_id",
                                              "civ"      => "$civ_mail",
                                              "nom"       => "$nom_mail",
                                              "prenom"    => "$cand_prenom",
                                              "email"      => $_SESSION['tab_candidat']['email']));

               write_msg("", array("id" => $_SESSION["auth_id"], "nom" => $_SESSION["auth_nom"], "prenom" => $_SESSION["auth_prenom"]),
                         $dest_array, "$_SESSION[composante] - Statut des justificatifs de votre cursus", $message, "$nom_mail $cand_prenom");

               if($GLOBALS["__DEBUG"]=="t" && $GLOBALS["__DEBUG_CURSUS"]=="t" && $GLOBALS["__EMAIL_ADMIN"]!="")
               {
                  $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_NOREPLY]\r\nReply-To: $GLOBALS[__EMAIL_NOREPLY]\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";
                  $prenom_mail=$_SESSION['tab_candidat']['prenom'];
                  mail($GLOBALS["__EMAIL_ADMIN"], "$GLOBALS[__DEBUG_SUJET] - $_SESSION[composante] - CURSUS - $civ_mail $prenom_mail $nom_mail", "\nBonjour $civ_mail $nom_mail, \n\n\n$contenu_mail\n\n\nCordialement,\n\n\n--\n$_SESSION[adr_scol]\n\n$_SESSION[composante]\n$_SESSION[universite]", $headers);
               }
            }
         }
      }
      elseif($_SESSION["niveau"]!=$__LVL_SUPPORT && (isset($_POST["go_prec"]) || isset($_POST["go_prec_x"])))
      {
         // ============================================
         //         TRAITEMENT DES PRE-CANDIDATURES
         // ============================================
         if(isset($_SESSION["tab_candidatures"]))
         {
            $cand_success=0;
            $cand_messages=0;

            foreach($_SESSION["tab_candidatures"] as $cand_id => $cand_array)
            {
               if(verif_droits_formations($_SESSION["comp_id"], $cand_array["propspec_id"]) && $cand_array["lock"]==1)
               {
               // On détermine le nom complet de la candidature pour l'insertion dans l'historique (pour que le texte soit lisible)
               $res_prec=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom_court FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_cand
                                          WHERE $_DBC_propspec_annee=$_DBC_annees_id
                                          AND $_DBC_propspec_id_spec=$_DBC_specs_id 
                                          AND $_DBC_propspec_id=$_DBC_cand_propspec_id
                                          AND $_DBC_cand_id='$cand_id'");
                                             
               if(db_num_rows($res_prec))
                  list($hist_annee, $hist_spec)=db_fetch_row($res_prec, 0);
               else
                  $hist_annee=$hist_spec="";
                     
               db_free_result($res_prec);
                  
               if(empty($hist_annee))
                  $prec_txt=str_replace("'","''", stripslashes($hist_spec));
               else
                  $prec_txt="$hist_annee " . str_replace("'","''", stripslashes($hist_spec));

               $statut=$cand_array["statut"];
               $cur_motivation=$cand_array["motivation"];

               $filiere=$cand_array["filiere"];

               // Update the vap_flag if different
               
               if(array_key_exists("vap", $_POST) && array_key_exists($cand_array["propspec_id"], $_POST["vap"])) {
                  $vap_flag=$_POST["vap"][$cand_array["propspec_id"]];
               
                  if($cand_array["vap"]!=$vap_flag) {
                     db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_vap_flag='$vap_flag' 
                                     WHERE $_DBC_cand_id='$cand_id'");
                  }
               }               

               // On teste si le nouveau statut est différent de l'ancien, ou si la motivation est différente
               if((isset($_POST["statut"]) && array_key_exists($cand_id, $_POST["statut"]) && $_POST["statut"]["$cand_id"]!=$statut)
                  || (array_key_exists($cand_id, $_POST["statut"]) && $_POST["statut"]["$cand_id"]==$statut
                      && stripslashes(trim($_POST["motivation"]["$cand_id"]))!=stripslashes($cur_motivation)))
               {
                  // $new_statut=$_POST["$key_statut"];
                  $new_statut=$_POST["statut"]["$cand_id"];

                  if(!isset($new_motivation))
                     $new_motivation=array();

                  $new_motivation[$cand_id]=trim($_POST["motivation"]["$cand_id"]);

                  // Texte en fonction des frais de dossiers
                  // TODO : configurer l'ordre des chèques dans la base ?

                  switch($new_statut)
                  {
                     case $__PREC_NON_TRAITEE :    // Retour au statut 'non traitée' : pas de mail
                                 $motiv=stripslashes($new_motivation[$cand_id]);
                                 $statut_txt="Non traitée";
                                 break;

                     case $__PREC_RECEVABLE   :   // précandidature acceptée, on efface la motivation
                                 $new_motivation[$cand_id]="";
                                 $statut_txt="Recevable";
                                 break;

                     case $__PREC_PLEIN_DROIT   :   // entrée de plein droit : notification seulement
                                 $new_motivation[$cand_id]="";
                                 $statut_txt="Plein droit";
                                 $decision="Le dépôt d'un dossier en ligne n'est pas nécessaire pour votre précandidature en \"$filiere\" car vous entrez de plein droit dans cette filière (sous réserve de l'obtention de l'année en cours).\n\nPour vous inscrire dans cette filière, vous pourrez vous connecter sur le site de l'Université à partir du 19 juillet.";
                                 break;

                     case $__PREC_EN_ATTENTE   :   // précandidature en attente (en général parce qu'il manque une pièce au dossier)
                                 $motiv=stripslashes($new_motivation[$cand_id]);
                                 $statut_txt="Mettre en attente";
                                 $decision="Votre précandidature en \"$filiere\" a été mise en attente pour le motif suivant : $motiv.\n\nMerci de compléter votre dossier dans les plus brefs délais. Tout dossier incomplet ne sera pas traité.";
                                 break;

                     case $__PREC_NON_RECEVABLE   :   // précandidature non recevable : motif prédéfini si non rempli
                                 if(trim($new_motivation[$cand_id])!="")
                                 {                              
                                    $motiv=stripslashes($new_motivation[$cand_id]);
                                    $decision="Nous avons le regret de vous informer que votre précandidature en \"$filiere\" a été jugée non recevable pour le motif suivant : $motiv.";
                                 }
                                 else
                                    $decision="Nous avons le regret de vous informer que les conditions de recevabilité ne sont pas satisfaites pour votre précandidature en \"$filiere\".";

                                 $statut_txt="Non recevable";
                                 break;

                     case $__PREC_ANNULEE   :               
                                 $statut_txt="Annulée";
                                 break;
                  }

                  // Vérification : une mise en attente doit être motivée
                  if($new_statut==$__PREC_EN_ATTENTE && empty($new_motivation[$cand_id]))
                     $motivation_vide=1;
                  else
                  {
                     // mise à jour
                     $id_annuaire=$_SESSION["auth_id"];

                     $new_date_statut=time();

                     $req="UPDATE $_DB_cand SET 
                              $_DBU_cand_statut='$new_statut',
                              $_DBU_cand_traitee_par='$id_annuaire',
                              $_DBU_cand_motivation_decision='".preg_replace("/[']+/", "''", stripslashes($new_motivation[$cand_id]))."',
                              $_DBU_cand_date_statut='$new_date_statut'
                           WHERE $_DBU_cand_id='$cand_id'";

                     db_query($dbr, $req);

                     write_evt("", $__EVT_ID_G_PREC, "Statut precandidature $prec_txt : $statut_txt", $candidat_id, $cand_id, $req);

                     // envoi du mail si nécessaire
                     if($new_statut==$__PREC_EN_ATTENTE || $new_statut==$__PREC_NON_RECEVABLE || $new_statut==$__PREC_PLEIN_DROIT)
                     {
                        $civ_mail=$_SESSION['tab_candidat']['civ_texte'];
                        $nom_mail=ucwords(mb_strtolower($_SESSION['tab_candidat']['nom'], "UTF-8"));
                        $prenom_mail=$_SESSION['tab_candidat']['prenom'];

                        $message="Bonjour $civ_mail $nom_mail,\n\n
$decision\n\n
Cordialement,\n\n
--
$_SESSION[adr_scol]\n
$_SESSION[composante]
$_SESSION[universite]";

                        $dest_array=array("0" => array("id"       => "$candidat_id",
                                                       "civ"      => "$civ_mail",
                                                       "nom"       => "$nom_mail",
                                                       "prenom"    => "$prenom_mail",
                                                       "email"      => $_SESSION['tab_candidat']['email']));

                        write_msg("", array("id" => $_SESSION["auth_id"], "nom" => $_SESSION["auth_nom"], "prenom" => $_SESSION["auth_prenom"]),
                                  $dest_array, "$_SESSION[composante] - $prec_txt", $message, "$nom_mail $prenom_mail");

                        $cand_messages++;

                        if($GLOBALS["__DEBUG"]=="t" && $GLOBALS["__DEBUG_STATUT_PREC"]=="t" && $GLOBALS["__EMAIL_ADMIN"]!="")
                        {
                           $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_NOREPLY]\r\nReply-To: $_GLOBALS[__EMAIL_NOREPLY]\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";
                           mail($GLOBALS["__EMAIL_ADMIN"],"$GLOBALS[__DEBUG_SUJET] - $_SESSION[composante] - STATUT PREC - $civ_mail $prenom_mail $nom_mail", "\nBonjour $civ_mail $nom_mail, \n\n\n$decision\n\n\nCordialement,\n\n\n--\n$_SESSION[adr_scol]\n\n$_SESSION[composante]\n$_SESSION[universite]", $headers);
                        }
                     }

                     $cand_success++;
                  }
               }

               // Frais de dossiers, s'il y en a
               // if(array_key_exists("$key_statut_frais", $_POST))
               if(isset($_POST["statut_frais"]) && array_key_exists($cand_id, $_POST["statut_frais"]))
               {
                  // $new_statut_frais=$_POST["$key_statut_frais"];
                  $new_statut_frais=$_POST["statut_frais"]["$cand_id"];

                  db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_statut_frais='$new_statut_frais' WHERE $_DBU_cand_id='$cand_id'");

                  write_evt($dbr, $__EVT_ID_G_PREC, "Statut prec $prec_txt : Frais : $new_statut_frais", $candidat_id, $cand_id);
               }
            } // fin du if(verif_droits_formations)
            }
         }
      }
      // ===========================================================
      //         MODE MANUEL : adresse et renvoi de messages
      // ===========================================================
      elseif(isset($_POST["go_email"]) || isset($_POST["go_email_x"])) // Modification de l'adresse email
      {
         $new_email=trim(strtolower($_POST["email"]));
         db_query($dbr,"UPDATE $_DB_candidat SET 
            $_DBU_candidat_email='".preg_replace("/[']+/", "''", stripslashes($new_email))."' 
            WHERE $_DBU_candidat_id='$candidat_id'");

         $_SESSION['tab_candidat']['email']=$new_email;
         $email_ok="1";

         write_evt($dbr, $__EVT_ID_G_MAN, "Nouveau courriel : $new_email", $candidat_id, $candidat_id);
      }
      elseif(isset($_POST["go_identifiant"]) || isset($_POST["go_identifiant_x"])) // Modification de l'identifiant
      {
         $new_identifiant=preg_replace("/[^[:alnum:].]/", "", trim(strtolower($_POST["identifiant"])));
         
         if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_candidat WHERE lower($_DBC_candidat_identifiant)='$new_identifiant' AND $_DBC_candidat_id!='$candidat_id'")))
         {
            db_query($dbr,"UPDATE $_DB_candidat SET $_DBU_candidat_identifiant='$new_identifiant' WHERE $_DBU_candidat_id='$candidat_id'");

            $_SESSION['tab_candidat']['identifiant']=$new_identifiant;
            $identifiant_ok="1";
            
            write_evt($dbr, $__EVT_ID_G_MAN, "Nouvel identifiant : $new_identifiant", $candidat_id, $candidat_id);
         }
         else
            $identifiant_nok="1";
      }
      elseif($_SESSION["niveau"]!=$__LVL_SUPPORT && (isset($_POST["go_mode"]) || isset($_POST["go_mode_x"]))) // Bascule "mode manuel / mode normal"
      {
         $new_mode=array_key_exists("mode_manuel", $_POST) && ($_POST["mode_manuel"]==0 || $_POST["mode_manuel"]==1) ? $_POST["mode_manuel"] : 0;

         db_query($dbr,"UPDATE $_DB_candidat SET $_DBU_candidat_manuelle='$new_mode'
                        WHERE $_DBU_candidat_id='$candidat_id'");

         $_SESSION['tab_candidat']['manuelle']=$new_mode;
         $mode_ok="1";

         $mode_txt=$new_mode ? "manuelle" : "normale";

         write_evt($dbr, $__EVT_ID_G_MAN, "Bascule de la fiche en mode \"$mode_txt\"", $candidat_id, $candidat_id);
      }
      elseif(isset($_POST["go_send_id"]) || isset($_POST["go_send_id_x"]))   // Renvoi des identifiants, par mail
      {
         // Regénération d'un code personnel
         srand((double)microtime()*1000000);
         $code_conf=mb_strtoupper(md5(rand(0,9999)), "UTF-8");
         $new_code=mb_substr($code_conf, 17, 8, "UTF-8");
         // on supprime le chiffre 1, les lettres I, L, O et le zéro : portent à confusion - on les remplace par d'autres caractères
         $new_code=str_replace("0","A", $new_code);
         $new_code=str_replace("O","H", $new_code);
         $new_code=str_replace("1","P", $new_code);
         $new_code=str_replace("I","F", $new_code);
         $new_code=str_replace("L","K", $new_code);         
         
         $result=db_query($dbr,"SELECT $_DBC_candidat_identifiant FROM $_DB_candidat WHERE $_DBC_candidat_id='$candidat_id'");

         $rows=db_num_rows($result);

         // TODO : traiter le cas où $rows=0

         if($rows==1)
         {
            list($cand_identifiant)=db_fetch_row($result,0);

            // Mise à jour du code
            db_query($dbr, "UPDATE $_DB_candidat SET $_DBU_candidat_code_acces='$new_code' WHERE $_DBC_candidat_id='$candidat_id'");

            $headers = "MIME-Version: 1.0\r\nFrom: $__EMAIL_NOREPLY\r\nReply-To: $__EMAIL_NOREPLY\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";
            $additional= "-r$__EMAIL_NOREPLY";

            $corps_message="============================================================\nCeci est un message automatique, merci de ne pas y répondre.\n============================================================\n
Bonjour " . $_SESSION['tab_candidat']['civ_texte'] . " " . $_SESSION['tab_candidat']['nom'] . ",\n\n
Les informations vous permettant d'accéder à l'interface de précandidatures sont les suivantes:
- Adresse : $__URL_CANDIDAT
- Identifiant : ". stripslashes($cand_identifiant) . "
- Code Personnel : $new_code\n
Ne perdez surtout pas votre identifiant car vous devrez le joindre aux éventuels justificatifs de diplômes à envoyer à la scolarité.\n\n
Cordialement,\n\n
--
Interface de précandidatures ARIA
$_SESSION[universite]";

            $ret=mail($_SESSION['tab_candidat']['email'],"[$_SESSION[universite] - Précandidatures en ligne Aria] - Vos identifiants", $corps_message, $headers, $additional);

            if($ret!=true)
            {
               mail($__EMAIL_ADMIN,"[$_SESSION[composante] - ERREUR Précandidatures] - Erreur d'envoi de courriel", "Erreur lors du renvoi manuel des identifiants\n\nFiche : " . $_SESSION['tab_candidat']['nom'] . " " . $_SESSION['tab_candidat']['prenom'] . "\nID :  $candidat_id\nEmail : " . $_SESSION['tab_candidat']['email']);
               $send_mail=-1;
            }
            else
               $send_mail=1;
         }

         db_free_result($result);

         write_evt($dbr, $__EVT_ID_G_MAN, "Envoi manuel des identifiants", $candidat_id, $candidat_id);
      }
      elseif(isset($_POST["go_send_recap"]) || isset($_POST["go_send_recap_x"]))
      {
         // L'envoi des justificatifs ne verrouille pas la candidature
         if(!send_recap_justifs($candidat_id, $_SESSION['comp_id']))
            $envoi_ok=1;

         write_evt($dbr, $__EVT_ID_G_MAN, "Envoi manuel des justificatifs", $candidat_id, $candidat_id);
      }
      elseif($_SESSION["niveau"]!=$__LVL_SUPPORT && (isset($_POST["go_suppr_fiche"]) || isset($_POST["go_suppr_fiche_x"])))
      {
         db_close($dbr);
         header("Location:suppr_fiche.php");

         exit();
      }
      elseif(isset($_POST["go_retour"]) || isset($_POST["go_retour_x"]))
      {
         db_close($dbr);
         header("Location:index.php");
         exit();
      }
      elseif($_SESSION["niveau"]!=$__LVL_SUPPORT && isset($_SESSION["tab_candidatures"])) // changement de la date de verrouillage ou verrouillage/déverrouillage manuel
      {
         // on doit parcourir toute la liste à chaque validation (pour voir ce qui a été validé.
         // Pas terrible (à optimiser), mais comme la liste est normalement assez courte, on ne perd pas trop en perfs.

         // TODO : réécrire cette boucle proprement, surtout les champs des formulaires (utilisation des [])

         foreach($_SESSION["tab_candidatures"] as $cand_id => $cand_array)
         {
            $key_lock="lock_".$cand_id;
            $key_unlock="unlock_".$cand_id;

            $key_newlockdate="newlockdate_".$cand_id;
            $key_jour="jour_verr_".$cand_id;
            $key_mois="mois_verr_".$cand_id;
            $key_annee="annee_verr_".$cand_id;

            if(isset($_POST[$key_unlock])) // déverrouillage forcé
            {
               // Si la candidature fait partie d'un groupe, on doit déverrouiller tout le groupe
               if($cand_array["groupe_spec"]!=-1)
               {
                  db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_lock='0'
                                  WHERE $_DBC_cand_candidat_id='$candidat_id'
                                  AND $_DBC_cand_groupe_spec='$cand_array[groupe_spec]'
                                  AND $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
                                                                 WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')");

                  write_evt($dbr, $__EVT_ID_G_UNLOCK, "Déverrouillage manuel (cand. choix multiples)", $candidat_id, $cand_array["groupe_spec"], "");
               }
               else
               {
                  db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_lock='0'
                                  WHERE $_DBC_cand_id='$cand_id'");

                  write_evt($dbr, $__EVT_ID_G_UNLOCK, "Déverrouillage manuel", $candidat_id, $cand_id, "");
               }
            }
            elseif(isset($_POST[$key_lock])) // déverrouillage forcé
            {
               if($cand_array["groupe_spec"]!=-1)
               {
                  db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_lock='1'
                                  WHERE $_DBC_cand_candidat_id='$candidat_id'
                                  AND $_DBC_cand_groupe_spec='$cand_array[groupe_spec]'
                                  AND $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
                                                                 WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')");

                  write_evt($dbr, $__EVT_ID_G_LOCK, "Verrouillage manuel (cand. choix multiples)", $candidat_id, $cand_array["groupe_spec"], "");
               }
               else
               {
                  db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_lock='1'
                                  WHERE $_DBC_cand_id='$cand_id'");

                  write_evt($dbr, $__EVT_ID_G_LOCK, "Verrouillage manuel", $candidat_id, $cand_id, "");
               }
            }
            elseif(isset($_POST[$key_newlockdate]) && isset($_POST[$key_jour]) && ctype_digit($_POST[$key_jour]) && isset($_POST[$key_mois])
                  && ctype_digit($_POST[$key_mois]) && isset($_POST[$key_annee]) && ctype_digit($_POST[$key_annee]))
            {
               if(strlen($_POST["annee_verr_$cand_id"])==4) // format de l'année
               {
                  $mois=$_POST[$key_mois];
                  $jour=$_POST[$key_jour];
                  $annee=$_POST[$key_annee];

                  $new_date=maketime(5, 0, 0, $mois, $jour, $annee);

                  if($cand_array["groupe_spec"]!=-1)
                  {
                     db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_lockdate='$new_date'
                                     WHERE $_DBC_cand_id='$cand_id'
                                     AND $_DBC_cand_groupe_spec='$cand_array[groupe_spec]'");

                     $succes_date=1;

                     write_evt($dbr, $__EVT_ID_G_LOCKDATE, "Date de verrouillage modifiée : $jour/$mois/$annee", $candidat_id, $cand_id, "");
                  }
                  else
                  {
                     db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_lockdate='$new_date'
                                     WHERE $_DBC_cand_id='$cand_id'");

                     $succes_date=1;

                     write_evt($dbr, $__EVT_ID_G_LOCKDATE, "Date de verrouillage modifiée : $jour/$mois/$annee", $candidat_id, $cand_id, "");
                  }
               }
            }
         }
      }
   }
   elseif($_SESSION["niveau"]!=$__LVL_SUPPORT && (isset($_POST["marquage"]) || isset($_POST["marquage_x"])))
   {
      $marque=$_POST["marque"];

      if($marque==1)
      {
         db_query($dbr, "DELETE FROM $_DB_acces_candidats_lus WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
                                                              AND $_DBC_acces_candidats_lus_candidat_id='$candidat_id'
                                                              AND $_DBC_acces_candidats_lus_periode='$__PERIODE';
                         INSERT INTO $_DB_acces_candidats_lus VALUES('$_SESSION[auth_id]', '$candidat_id', '$__PERIODE')");

         $candidat_vu=1;
      }
      elseif($marque==-1)
      {
         db_query($dbr, "DELETE FROM $_DB_acces_candidats_lus WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
                                                              AND $_DBC_acces_candidats_lus_candidat_id='$candidat_id'
                                                              AND $_DBC_acces_candidats_lus_periode='$__PERIODE'");
         $candidat_vu=-1;
      }
   }

   // Verrouillage et quelques infos sur les formations du candidat
   // Ces infos sont utilisées dans plusieurs menus, c'est pourquoi on met cette requête
   // dans edit_candidature.php et non dans menu_precandidatures.php
   //TODO : à vérifier pour optimiser les requêtes et les boucles
   $result2=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_cand_lock, $_DBC_cand_lockdate, $_DBC_cand_id
                                 FROM $_DB_cand, $_DB_propspec
                              WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                              AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                              AND $_DBC_cand_candidat_id='$candidat_id'
                              AND $_DBC_cand_periode='$__PERIODE'
                              ORDER BY $_DBC_cand_lockdate, $_DBC_cand_propspec_id");

   $rows=db_num_rows($result2);

   $_SESSION["tab_candidat"]["array_lock"]=array();

   for($i=0; $i<$rows; $i++)
   {
      list($propspec_id, $lock, $lockdate, $candidature_id)=db_fetch_row($result2, $i);

      $lockdate_txt=date_fr("j F Y", $lockdate);

      $_SESSION["tab_candidat"]["array_lock"][$candidature_id]=array("propspec_id" => $propspec_id,
                                                                     "lock" => $lock,
                                                                     "lockdate" => $lockdate,
                                                                     "lockdate_txt" => $lockdate_txt);

      // Au moins une formation est verrouillée : fiche modifiable
      if($lock)
         $_SESSION["tab_candidat"]["lock"]=1;
   }

   if(!array_key_exists("lock", $_SESSION['tab_candidat']))
      $_SESSION['tab_candidat']['lock']=0;

   db_free_result($result2);
   

   // Quelques paramètres
   $Y=date('Y');
   $Z=$Y+1;

   // Nettoyage général
   // TODO : à généraliser et améliorer
   unset($_SESSION["tab_cursus"]);
   unset($_SESSION["tab_candidatures"]);
   unset($_SESSION["cu_id"]);
   unset($_SESSION["ce_id"]);
   unset($_SESSION["la_id"]);
   unset($_SESSION["ct_id"]);
   unset($_SESSION["cand_id"]);
   unset($_SESSION["iid"]);

   unset($_SESSION["requete"]);
   unset($_SESSION["mail_masse"]);
   unset($_SESSION["checked_message"]);

   unset($_SESSION["to"]);
   unset($_SESSION["msg_dest_civilite"]);
   unset($_SESSION["msg_dest_nom"]);
   unset($_SESSION["msg_dest_prenom"]);
   unset($_SESSION["msg_dest_email"]);

   unset($_SESSION["all_sessions"]);

   unset($_SESSION["candidatures_array"]);
   unset($_SESSION["generer_justifs_propspec_id"]);

   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>

<div class='main'>
   <form action='<?php echo $php_self; ?>' method='POST'>
      <?php
         // Encadré contenant les infos essentielles du candidat
         $to=crypt_params("to=$candidat_id");

         print("<div class='infos_candidat'>
                  <span style='margin-top:2px; position:absolute; left:4px;'>
                  <font class='Texte'>
                     <strong>" . $_SESSION["tab_candidat"]["civ_texte"] . " " . $_SESSION["tab_candidat"]["nom"] . " " . $_SESSION["tab_candidat"]["prenom"] .", " . $_SESSION["tab_candidat"]["ne_le"] . " " . $_SESSION["tab_candidat"]["txt_naissance"] ."</strong>\n");

         if($_SESSION["niveau"]!=$__LVL_SUPPORT)
         {
            if($_SESSION["tab_candidat"]["manuelle"]==0 || ($_SESSION["tab_candidat"]["manuelle"]==1 && FALSE!=strstr($_SESSION["tab_candidat"]["email"], "@")))
               print("<a href='messagerie/compose.php?p=$to' class='lien_navigation_10'><img class='icone' src='$__ICON_DIR/mail_send_16x16_blanc.png' border='0'></a>\n");
            else
               print("<font class='Texte_important_10'>Aucune adresse électronique</font>\n");
         }

         if($_SESSION["niveau"]==$__LVL_CONSULT)
         {
            if(isset($candidat_vu))
            {
               if($candidat_vu==1)
               {
                  $selected_0=$selected_non="";
                  $selected_oui="selected='1'";
               }
               else
               {
                  $selected_0=$selected_oui="";
                  $selected_non="selected='1'";
               }
            }
            else
            {
               $selected_0="selected='1'";
               $selected_oui=$selected_non="";
            }

            print("<select name='marque'>
                     <option value='' $selected_0></option>
                     <option value='1' $selected_oui>Marquer comme Vu</option>
                     <option value='-1' $selected_non>Marquer comme Non Vu</option>
                  </select>
                  <input type='submit' name='marquage' value='Valider'>\n");
         }
      ?>
      </font>
      </span>
      <?php
         // Navigation entre les fiches, par ordre alphabétique (candidat suivant / précédent)
         // déterminés en fonction du filtre appliqué par l'utilisateur

         if(isset($_SESSION["filtre_propspec"]) && $_SESSION["filtre_propspec"]!="-1")
         {
            $filtre_formation="AND $_DBC_candidat_id IN (SELECT distinct($_DBC_cand_candidat_id) FROM $_DB_cand
                                                         WHERE $_DBC_cand_propspec_id='$_SESSION[filtre_propspec]'
                                                         AND $_DBC_cand_periode='$__PERIODE')";

            $filtre_formation_statut="<font class='Texte_important_10'><strong>(filtre activé)</strong></font>";
         }
         else
         {
            // Pas de filtre sur une formation particulière = toutes les formations de la composante
            $filtre_formation="AND $_DBC_candidat_id IN (SELECT distinct($_DBC_cand_candidat_id)
                                                            FROM $_DB_cand, $_DB_propspec
                                                         WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                                                         AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                                         AND $_DBC_cand_periode='$__PERIODE')";

            $filtre_formation_statut="<font class='Texte_10'>(aucun filtre)</font>";
         }

         $result_candidats=db_query($dbr,"(SELECT $_DBC_candidat_id, $_DBC_candidat_nom, $_DBC_candidat_prenom,
                                                  $_DBC_candidat_date_naissance
                                                FROM $_DB_candidat
                                             WHERE (($_DBC_candidat_nom<'" . preg_replace("/[']+/", "''", $_SESSION["tab_candidat"]["nom"]) . "')
                                                   OR ($_DBC_candidat_nom='" . preg_replace("/[']+/", "''", $_SESSION["tab_candidat"]["nom"]) . "'
                                                       AND $_DBC_candidat_prenom<'" . preg_replace("/[']+/", "''", $_SESSION["tab_candidat"]["prenom"]) . "')
                                                   OR ($_DBC_candidat_nom='" . preg_replace("/[']+/", "''", $_SESSION["tab_candidat"]["nom"]) . "'
                                                       AND $_DBC_candidat_prenom='" . preg_replace("/[']+/", "''", $_SESSION["tab_candidat"]["prenom"]) . "'
                                                       AND $_DBC_candidat_date_naissance<='" . $_SESSION["tab_candidat"]["naissance"] . "'))                                                   
                                             AND $_DBC_candidat_id!='$_SESSION[candidat_id]'
                                             $filtre_formation
                                                ORDER BY $_DBC_candidat_nom DESC, $_DBC_candidat_prenom DESC, $_DBC_candidat_date_naissance DESC
                                                LIMIT 1)
                                       UNION ALL
                                          (SELECT $_DBC_candidat_id, $_DBC_candidat_nom, $_DBC_candidat_prenom,
                                                  $_DBC_candidat_date_naissance
                                                FROM $_DB_candidat
                                             WHERE (($_DBC_candidat_nom>'" . preg_replace("/[']+/", "''", $_SESSION["tab_candidat"]["nom"]) . "')
                                                   OR ($_DBC_candidat_nom='" . preg_replace("/[']+/", "''", $_SESSION["tab_candidat"]["nom"]) . "'
                                                       AND $_DBC_candidat_prenom>'" . preg_replace("/[']+/", "''", $_SESSION["tab_candidat"]["prenom"]) . "')
                                                   OR ($_DBC_candidat_nom='" . preg_replace("/[']+/", "''", $_SESSION["tab_candidat"]["nom"]) . "'
                                                       AND $_DBC_candidat_prenom='" . preg_replace("/[']+/", "''", $_SESSION["tab_candidat"]["prenom"]) . "'
                                                       AND $_DBC_candidat_date_naissance>='" . $_SESSION["tab_candidat"]["naissance"] . "'))
                                             AND $_DBC_candidat_id!='$_SESSION[candidat_id]'
                                             $filtre_formation
                                                ORDER BY $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance
                                                LIMIT 1)");

         $rows_candidats=db_num_rows($result_candidats);

         switch($rows_candidats)
         {
            // On a 3 valeurs possibles
            // 0 : le candidat est seul dans la base ou dans sa catégorie (fonction du filtre)
            // 1 : il s'agit du premier ou du dernier candidat de la liste
            //     : dans ce cas, il faut tester s'il s'agit du premier (< candidat actuel) ou du dernier (> candidat actuel)
            // 2 : tous les autres cas (candidat parmi les autres de la liste)

            case   0   :   $no_next=$no_prev=1;
                        break;

            case   1   :   list($nav_candidat_id, $nav_candidat_nom, $nav_candidat_prenom, $nav_candidat_naissance)=db_fetch_row($result_candidats,0);
                        
                        if(strcasecmp($nav_candidat_nom,$_SESSION["tab_candidat"]["nom"])<0
                           || (!strcasecmp($nav_candidat_nom,$_SESSION["tab_candidat"]["nom"]) && strcasecmp($nav_candidat_prenom,$_SESSION["tab_candidat"]["prenom"])<0)
                           || (!strcasecmp($nav_candidat_nom,$_SESSION["tab_candidat"]["nom"]) && !strcasecmp($nav_candidat_prenom,$_SESSION["tab_candidat"]["prenom"])
                              && $nav_candidat_naissance<$_SESSION["tab_candidat"]["naissance"]))
                        {
                           $nav_naissance_txt=date_fr("j F Y",$nav_candidat_naissance);
                           $prev_candidat="<a class='lien_navigation_10' href='$php_self?cid=$nav_candidat_id'>$nav_candidat_nom $nav_candidat_prenom ($nav_naissance_txt)</a>";
                           $prev_icone="<a class='lien_navigation_10' href='$php_self?cid=$nav_candidat_id'><img style='vertical-align:middle;' src='$__ICON_DIR/back_16x16_blanc.png' desc='Candidat précédent' title='[Candidat précédent]' border='0'></a>";
                           $no_next=1;
                        }
                        else
                        {
                           $nav_naissance_txt=date_fr("j F Y",$nav_candidat_naissance);
                           $next_candidat="<a class='lien_navigation_10' href='$php_self?cid=$nav_candidat_id'>$nav_candidat_nom $nav_candidat_prenom ($nav_naissance_txt)</a>";
                           $next_icone="<a class='lien_navigation_10' href='$php_self?cid=$nav_candidat_id'><img style='vertical-align:middle;' src='$__ICON_DIR/forward_16x16_blanc.png' desc='Candidat suivant' title='[Candidat suivant]' border='0'></a>";
                           $no_prev=1;
                        }
                        break;

            default   :   list($nav_candidat_id, $nav_candidat_nom, $nav_candidat_prenom, $nav_candidat_naissance)=db_fetch_row($result_candidats,0);
                        $nav_naissance_txt=date_fr("j F Y",$nav_candidat_naissance);
                        $prev_candidat="<a class='lien_navigation_10' href='$php_self?cid=$nav_candidat_id'>$nav_candidat_nom $nav_candidat_prenom ($nav_naissance_txt)</a>";
                        $prev_icone="<a class='lien_navigation_10' href='$php_self?cid=$nav_candidat_id'><img style='vertical-align:middle;' src='$__ICON_DIR/back_16x16_blanc.png' desc='Candidat précédent' title='[Candidat précédent]' border='0'></a>";

                        list($nav_candidat_id, $nav_candidat_nom, $nav_candidat_prenom, $nav_candidat_naissance)=db_fetch_row($result_candidats,1);
                        $nav_naissance_txt=date_fr("j F Y",$nav_candidat_naissance);
                        $next_candidat="<a class='lien_navigation_10' href='$php_self?cid=$nav_candidat_id'>$nav_candidat_nom $nav_candidat_prenom ($nav_naissance_txt)</a>";
                        $next_icone="<a class='lien_navigation_10' href='$php_self?cid=$nav_candidat_id'><img style='vertical-align:middle;' src='$__ICON_DIR/forward_16x16_blanc.png' desc='Candidat suivant' title='[Candidat suivant]' border='0'></a>";
                        break;
         }

         if(!isset($no_prev) || !isset($no_next))
         {
            print("<span style='margin-top:2px; position:absolute; right:4px;'>\n");

            if(!isset($no_prev) && isset($prev_candidat))
            {
               print("<span>
                        $prev_candidat
                         $prev_icone
                      </span>\n");               
            }

            print("<span style='padding-left:5px; padding-right:5px;'>$filtre_formation_statut</span>");

            if(!isset($no_next) && isset($next_candidat))
            {
               print("<span>
                        $next_icone
                         $next_candidat
                      </span>\n");
            }

            print("</span>\n");
         }
         db_free_result($result_candidats);
      ?>
   </div>
   <div class='menu_gauche'>
      <ul class='menu_gauche'>
         <?php
            // class='menu0'
            // Menu de la colonne gauche
            $cnt_menu=count($menu_gestion);

            // TODO : réorganiser la définition des menus dans un tableau, avec les paramètres de niveaux adéquats
            for($i=0; $i<$cnt_menu; $i++)
            {
               $onglet=$i+1;
               $nom_onglet=$menu_gestion[$onglet];

               if($_SESSION["onglet"]!=$onglet)
               {
                  if(($onglet==7) && $_SESSION["niveau"]==$__LVL_CONSULT)
                     print("<li class='menu_gauche_select'><strong>$nom_onglet</strong></li>\n");
                  elseif($onglet!=10 || $_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT)
                     print("<li class='menu_gauche'><a href='$php_self?onglet=$onglet' class='lien_menu_gauche' target='_self'>$nom_onglet</a></li>\n");
               }
               else
               {
                  $nom_page=$nom_onglet;

                  if($onglet==10 && ($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT))
                     print("<li class='menu_gauche_select'><strong>$nom_onglet</strong></li>
                            <li class='menu_gauche'>&nbsp&nbsp;<a href='$php_self?onglet=$onglet&dossier=$__MSG_INBOX' class='lien_menu_gauche' target='_self'>- $__MSG_DOSSIERS[$__MSG_INBOX]</a></li>
                            <li class='menu_gauche'>&nbsp&nbsp;<a href='$php_self?onglet=$onglet&dossier=$__MSG_SENT' class='lien_menu_gauche' target='_self'>- $__MSG_DOSSIERS[$__MSG_SENT]</a></li>
                            <li class='menu_gauche'>&nbsp&nbsp;<a href='$php_self?onglet=$onglet&dossier=$__MSG_TRAITES' class='lien_menu_gauche' target='_self'>- $__MSG_DOSSIERS[$__MSG_TRAITES]</a></li>
                            <li class='menu_gauche'>&nbsp&nbsp;<a href='$php_self?onglet=$onglet&dossier=$__MSG_TRASH' class='lien_menu_gauche' target='_self'>- $__MSG_DOSSIERS[$__MSG_TRASH]</a></li>\n");
                  else
                     print("<li class='menu_gauche_select'><strong>$nom_onglet</strong></li>\n");
               }
            }
         ?>
      </ul>
   </div>
   <div class='corps_gestion'>
      <?php
         // Inclusion du fichier en fonction du menu sélectionné

         switch($_SESSION["onglet"])
         {
            case 1   :  include "menu_identite.php";
                        break;

            case 2  :   include "menu_cursus.php";
                        break;

            case 3  :   include "menu_langues.php";
                        break;

            case 4  :   include "menu_infos_complementaires.php";
                        break;

            case 5  :   include "menu_autres_renseignements.php";
                        break;

            case 6  :   include "menu_precandidatures.php";
                        break;

            case 7  :   if(in_array($_SESSION["niveau"], array("$__LVL_SUPPORT","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
                           include "menu_mode_manuel.php";

                        break;

            case 8  :   include "menu_documents_pdf.php";
                        break;

            case 9 :    include "menu_historique.php";
                        break;

            case 10 :   if($_SESSION["niveau"]==$__LVL_ADMIN)
                           include "menu_messagerie.php";
                        else
                           include "menu_identite.php";
                        break;

            default :   include "menu_identite.php";
                        break;
         }

         db_close($dbr);
      ?>
      </form>
   </div>
</div>
<?php
   pied_de_page();
?>
</body>
</html>


