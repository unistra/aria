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
   include "$__INCLUDE_DIR_ABS/access_functions.php";

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   // param�tre sp�cial : consultation directe d'un candidat (identifiant "fiche") � partir d'un mail "message"
   if(isset($_GET["fiche"]) && ctype_digit($_GET["fiche"]) && isset($_GET["dco"]) && ctype_digit($_GET["dco"]))
   {
      $_GET["fiche"]=str_replace(" ", "", $_GET["fiche"]);
      $_GET["dco"]=str_replace(" ", "", $_GET["dco"]);

      $candidat_id=$_SESSION["candidat_id"]=$_SESSION["fiche_id"]=$_GET["fiche"];

      // Si l'utilisateur �tait d�j� connect�, on bascule la composante (�ventuellement)
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
         else // pas acc�s
         {
            db_close($dbr);
            header("Location:index.php");
            exit;
         }
      }
      else // Pas encore authentifi� : on conserve la composante pour l'acc�s direct et on redirige vers la page d'authentification
         $_SESSION["dco"]=$_GET["dco"];
   }

   verif_auth();

   // param�tre : identifiant du candidat
   if(isset($_GET["cid"]) && ctype_digit($_GET["cid"]))
   {
      $_SESSION["candidat_id"]=$candidat_id=$_GET["cid"];

      // Au cas o� on viendrait de la page Recherche, on regarde si on peut basculer vers la composante dans laquelle le candidat a d�pos� un voeu
      if(isset($_GET["rech"]) && $_GET["rech"]==1)
      {
         $dbr=db_connect();

         if(isset($_SESSION["niveau"]) && in_array($_SESSION["niveau"], array("$__LVL_ADMIN", "$__LVL_SUPPORT")))
         {
            // Administrateur et support : on ignore les droits d'acc�s sur les composantes
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

         // Si on a un r�sultat, c'est que l'utilisateur a acc�s � la composante dans laquelle le candidat a demand� une candidature
         if($rows=db_num_rows($result))
         {
            $all=db_fetch_all($result);

            // print_r($all);

            foreach($all as $i => $array_result)
            {
               if($array_result["composante_id"] == $_SESSION["comp_id"]) // composante courante trouv�e : on s'arrete
               {
                  $found=1;
                  $_SESSION["comp_id"]=$new_comp_id=$array_result["composante_id"];
               }
            }

            if(!isset($found)) // composante courante non trouv�e, on change en prenant la premi�re
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

   // Onglet par d�faut : identit� (1)
   if(isset($_GET["onglet"]) && is_numeric($_GET["onglet"]) && $_GET["onglet"]>0 && $_GET["onglet"]<=10)
      $_SESSION["onglet"]=$_GET["onglet"];
   elseif(!isset($_SESSION["onglet"]))
      $_SESSION["onglet"]=1;

   if(!in_array($_SESSION['niveau'], array("$__LVL_SUPPORT", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["onglet"]==7))
      $_SESSION["onglet"]=1;

   $dbr=db_connect();

   // Changement �ventuel de composante
   if(isset($_GET["co"]) && ctype_digit($_GET["co"]))
   {
      $new_comp_id=$_GET["co"];

      // V�rification de l'acc�s
      if(in_array($_SESSION["niveau"], array("$__LVL_ADMIN", "$__LVL_SUPPORT")) || db_num_rows(db_query($dbr, "SELECT $_DBC_acces_comp_composante_id FROM $_DB_acces_comp
                                                                              WHERE $_DBC_acces_comp_acces_id='$_SESSION[auth_id]'
                                                                           AND $_DBC_acces_comp_composante_id='$new_comp_id'")))
      {
         // R�cup�ration des param�tres propres � cette composante, si elle existe
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

   // Lib�ration de la fiche
   cand_unlock($dbr, $candidat_id);

   // r�cup�ration des infos du candidat
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
         $_SESSION['tab_candidat']['derniere_connexion']="Jamais connect�";

      switch($_SESSION['tab_candidat']['civilite'])
      {
         case "M" :        $_SESSION['tab_candidat']['civ_texte']="M.";
                           $_SESSION['tab_candidat']['etudiant']="Candidat";
                           $_SESSION['tab_candidat']['etudiant_particule']="le Candidat";
                           $_SESSION['tab_candidat']['etudiant_coi']="au Candidat";
                           $_SESSION['tab_candidat']['ne_le']="N� le";
                           break;

         case   "Mlle" :   $_SESSION['tab_candidat']['civ_texte']="Mlle";
                           $_SESSION['tab_candidat']['etudiant']="Candidate";
                           $_SESSION['tab_candidat']['etudiant_particule']="la Candidate";
                           $_SESSION['tab_candidat']['etudiant_coi']="� la Candidate";
                           $_SESSION['tab_candidat']['ne_le']="N�e le";
                           break;

         case   "Mme"   :  $_SESSION['tab_candidat']['civ_texte']="Mme";
                           $_SESSION['tab_candidat']['etudiant']="Candidate";
                           $_SESSION['tab_candidat']['etudiant_particule']="la Candidate";
                           $_SESSION['tab_candidat']['etudiant_coi']="� la Candidate";
                           $_SESSION['tab_candidat']['ne_le']="N�e le";
                           break;

         default      :    $_SESSION['tab_candidat']['civ_texte']="M.";
                           $_SESSION['tab_candidat']['etudiant']="Candidat";
                           $_SESSION['tab_candidat']['etudiant_particule']="le Candidat";
                           $_SESSION['tab_candidat']['etudiant_coi']="au Candidat";
                           $_SESSION['tab_candidat']['ne_le']="N� le";
      }

      db_free_result($result);

      // Autres informations
      // D�partement de naissance
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

      // S�rie du bac
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

   // Modification du statut des candidatures ou retour � la page pr�c�dente

   if(in_array($_SESSION["niveau"], array("$__LVL_SUPPORT", "$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
   {
      // Restauration d'une candidature annul�e
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

               // En fonction de la d�cision de commission, on ne restaure pas le m�me statut
               // => si une d�cision a d�j� �t� prise, on met la pr�candidature recevable
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
         // en fonction de l'onglet actif et du tableau pr�sent en $_SESSION, ce ne sont pas les m�mes infos qui sont valid�es

         // ============================================
         //    TRAITEMENT DES JUSTIFICATIFS DU CURSUS
         // ============================================

         if(isset($_SESSION['tab_cursus']))
         {
             $contenu_mail="Composante : $_SESSION[composante]\n\nConcernant votre cursus, les pi�ces justificatives que vous nous avez transmises ont �t� r�ceptionn�es.\n\nStatut de votre cursus :\n\n";
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

               if($nouveau_statut==$__CURSUS_VALIDE) // plus besoin du champ "pr�cision" pour un cursus valid�
               {
                  $nouvelle_precision="";
                  $contenu_mail.="- $texte : Justificatifs re�us\n\n";
               }
               elseif($nouveau_statut==$__CURSUS_NON_NECESSAIRE) // idem pour un justificatif non n�cessaire
               {
                  $nouvelle_precision="";
                  $contenu_mail.="- $texte : Justificatifs non n�cessaires pour cette �tape\n\n";
               }
               elseif($nouveau_statut==$__CURSUS_NON_JUSTIFIE) // aucun justificatif jamais re�u
               {
                  $nouvelle_precision="";
                  $contenu_mail.="- $texte : Information non confirm�e car aucun justificatif n'a �t� re�u\n\n";
               }
               elseif($nouveau_statut==$__CURSUS_EN_ATTENTE)
               {
                  $nouvelle_precision="";
                  $contenu_mail.="- $texte : Pi�ces non re�ues ou en cours de traitement\n\n";
               }
               elseif($nouveau_statut==$__CURSUS_DES_OBTENTION) // Pour les cursus en cours : pr�cision facultative
               {
                  $contenu_mail.="- $texte : Pi�ce(s) � fournir d�s l'obtention du dipl�me";
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

                  $contenu_mail.="- $texte : Pi�ces manquantes $texte_precision\n\n";
               }

               if($nouveau_statut!=$statut_actuel || $nouvelle_precision!=$precision_actuelle) // changement d�tect�
               {
                  $modifs++;

                  $req="UPDATE $_DB_cursus_justif SET $_DBU_cursus_justif_statut='$nouveau_statut',
                                                      $_DBU_cursus_justif_precision='$nouvelle_precision'
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
                  $contenu_mail.="\nEn l'�tat, vos pr�candidatures ne sont pas recevables. Merci d'envoyer la ou les pi�ces manquantes le plus rapidement possible.\n\nNous rappelons qu'un dossier incomplet ne sera PAS TRAITE.";

               $contenu_mail.="\n\n<b>Nous rappelons que ce statut est uniquement valable pour la composante mentionn�e. Si vous avez d�pos� un dossier dans une autre composante, ce statut pourra �tre diff�rent en fonction des pi�ces que vous avez envoy�es.</b>";

               $civ_mail=$_SESSION['tab_candidat']['civ_texte'];
               $cand_prenom=$_SESSION['tab_candidat']['prenom'];
               $nom_mail=ucwords(mb_strtolower($_SESSION['tab_candidat']['nom']));

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
                  $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_ADMIN]\r\nReply-To: $_SESSION[auth_email]\r\nContent-Type: text/plain; charset=ISO-8859-15\r\nContent-transfer-encoding: 8bit\r\n\r\n";
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
               if(verif_droits_formations($_SESSION["comp_id"], $cand_array["propspec_id"]))
               {
               // On d�termine le nom complet de la candidature pour l'insertion dans l'historique (pour que le texte soit lisible)
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

               // On teste si le nouveau statut est diff�rent de l'ancien, ou si la motivation est diff�rente
               if((isset($_POST["statut"]) && array_key_exists($cand_id, $_POST["statut"]) && $_POST["statut"]["$cand_id"]!=$statut)
                  || ($_POST["statut"]["$cand_id"]==$statut && stripslashes(trim($_POST["motivation"]["$cand_id"]))!=stripslashes($cur_motivation)))
               {
                  // $new_statut=$_POST["$key_statut"];
                  $new_statut=$_POST["statut"]["$cand_id"];

                  if(!isset($new_motivation))
                     $new_motivation=array();

                  $new_motivation[$cand_id]=trim($_POST["motivation"]["$cand_id"]);

                  // Texte en fonction des frais de dossiers
                  // TODO : configurer l'ordre des ch�ques dans la base ?

                  switch($new_statut)
                  {
                     case $__PREC_NON_TRAITEE :    // Retour au statut 'non trait�e' : pas de mail
                                 $motiv=stripslashes($new_motivation[$cand_id]);
                                 $statut_txt="Non trait�e";
                                 break;

                     case $__PREC_RECEVABLE   :   // pr�candidature accept�e, on efface la motivation
                                 $new_motivation[$cand_id]="";
                                 $statut_txt="Recevable";
                                 break;

                     case $__PREC_PLEIN_DROIT   :   // entr�e de plein droit : notification seulement
                                 $new_motivation[$cand_id]="";
                                 $statut_txt="Plein droit";
                                 $decision="Le d�p�t d'un dossier en ligne n'est pas n�cessaire pour votre pr�candidature en \"$filiere\" car vous entrez de plein droit dans cette fili�re (sous r�serve de l'obtention de l'ann�e en cours).\n\nPour vous inscrire dans cette fili�re, vous pourrez vous connecter sur le site de l'Universit� � partir du 19 juillet.";
                                 break;

                     case $__PREC_EN_ATTENTE   :   // pr�candidature en attente (en g�n�ral parce qu'il manque une pi�ce au dossier)
                                 $motiv=stripslashes($new_motivation[$cand_id]);
                                 $statut_txt="Mettre en attente";
                                 $decision="Votre pr�candidature en \"$filiere\" a �t� mise en attente pour le motif suivant : $motiv.\n\nMerci de compl�ter votre dossier dans les plus brefs d�lais. Tout dossier incomplet ne sera pas trait�.";
                                 break;

                     case $__PREC_NON_RECEVABLE   :   // pr�candidature non recevable : motif pr�d�fini si non rempli
                                 if(trim($new_motivation[$cand_id])!="")
                                 {                              
                                    $motiv=stripslashes($new_motivation[$cand_id]);
                                    $decision="Nous avons le regret de vous informer que votre pr�candidature en \"$filiere\" a �t� jug�e non recevable pour le motif suivant : $motiv.";
                                 }
                                 else
                                    $decision="Nous avons le regret de vous informer que les conditions de recevabilit� ne sont pas satisfaites pour votre pr�candidature en \"$filiere\".";

                                 $statut_txt="Non recevable";
                                 break;

                     case $__PREC_ANNULEE   :               
                                 $statut_txt="Annul�e";
                                 break;
                  }

                  // V�rification : une mise en attente doit �tre motiv�e
                  if($new_statut==$__PREC_EN_ATTENTE && empty($new_motivation[$cand_id]))
                     $motivation_vide=1;
                  else
                  {
                     // mise � jour
                     $id_annuaire=$_SESSION["auth_id"];

                     $new_date_statut=time();

                     $req="UPDATE $_DB_cand SET $_DBU_cand_statut='$new_statut',
                                                $_DBU_cand_traitee_par='$id_annuaire',
                                                $_DBU_cand_motivation_decision='$new_motivation[$cand_id]',
                                                $_DBU_cand_date_statut='$new_date_statut'
                           WHERE $_DBU_cand_id='$cand_id'";

                     db_query($dbr, $req);

                     write_evt("", $__EVT_ID_G_PREC, "Statut precandidature $prec_txt : $statut_txt", $candidat_id, $cand_id, $req);

                     // envoi du mail si n�cessaire
                     if($new_statut==$__PREC_EN_ATTENTE || $new_statut==$__PREC_NON_RECEVABLE || $new_statut==$__PREC_PLEIN_DROIT)
                     {
                        $civ_mail=$_SESSION['tab_candidat']['civ_texte'];
                        $nom_mail=ucwords(mb_strtolower($_SESSION['tab_candidat']['nom']));
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
                           $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_ADMIN]\r\nReply-To: $_SESSION[auth_email]\r\nContent-Type: text/plain; charset=ISO-8859-15\r\nContent-transfer-encoding: 8bit\r\n\r\n";
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
         db_query($dbr,"UPDATE $_DB_candidat SET $_DBU_candidat_email='$new_email' WHERE $_DBU_candidat_id='$candidat_id'");

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
         // Reg�n�ration d'un code personnel
         srand((double)microtime()*1000000);
         $code_conf=mb_strtoupper(md5(rand(0,9999)));
         $new_code=substr($code_conf, 17, 8);
         // on supprime le chiffre 1, les lettres I, L, O et le z�ro : portent � confusion - on les remplace par d'autres caract�res
         $new_code=str_replace("0","A", $new_code);
         $new_code=str_replace("O","H", $new_code);
         $new_code=str_replace("1","P", $new_code);
         $new_code=str_replace("I","F", $new_code);
         $new_code=str_replace("L","K", $new_code);         
         
         $result=db_query($dbr,"SELECT $_DBC_candidat_identifiant FROM $_DB_candidat WHERE $_DBC_candidat_id='$candidat_id'");

         $rows=db_num_rows($result);

         // TODO : traiter le cas o� $rows=0

         if($rows==1)
         {
            list($cand_identifiant)=db_fetch_row($result,0);

            // Mise � jour du code
            db_query($dbr, "UPDATE $_DB_candidat SET $_DBU_candidat_code_acces='$new_code' WHERE $_DBC_candidat_id='$candidat_id'");

            $headers = "MIME-Version: 1.0\r\nFrom: $__EMAIL_ADMIN\r\nReply-To: $_SESSION[auth_email]\r\nContent-Type: text/plain; charset=ISO-8859-15\r\nContent-transfer-encoding: 8bit\r\n\r\n";
            $additional= "-r$_SESSION[auth_email]";

            $corps_message="============================================================\nCeci est un message automatique, merci de ne pas y r�pondre.\n============================================================\n
Bonjour " . $_SESSION['tab_candidat']['civ_texte'] . " " . $_SESSION['tab_candidat']['nom'] . ",\n\n
Les informations vous permettant d'acc�der � l'interface de pr�candidatures sont les suivantes:
- Adresse : $__URL_CANDIDAT
- Identifiant : ". stripslashes($cand_identifiant) . "
- Code Personnel : $new_code\n
Ne perdez surtout pas votre identifiant car vous devrez le joindre aux �ventuels justificatifs de dipl�mes � envoyer � la scolarit�.\n\n
Cordialement,\n\n
--
Interface de pr�candidatures ARIA
$_SESSION[universite]";

            $ret=mail($_SESSION['tab_candidat']['email'],"[$_SESSION[universite] - Pr�candidatures en ligne Aria] - Vos identifiants", $corps_message, $headers, $additional);

            if($ret!=true)
            {
               mail($__EMAIL_ADMIN,"[$_SESSION[composante] - ERREUR Pr�candidatures] - Erreur d'envoi de courriel", "Erreur lors du renvoi manuel des identifiants\n\nFiche : " . $_SESSION['tab_candidat']['nom'] . " " . $_SESSION['tab_candidat']['prenom'] . "\nID :  $candidat_id\nEmail : " . $_SESSION['tab_candidat']['email']);
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
      elseif($_SESSION["niveau"]!=$__LVL_SUPPORT && isset($_SESSION["tab_candidatures"])) // changement de la date de verrouillage ou verrouillage/d�verrouillage manuel
      {
         // on doit parcourir toute la liste � chaque validation (pour voir ce qui a �t� valid�.
         // Pas terrible (� optimiser), mais comme la liste est normalement assez courte, on ne perd pas trop en perfs.

         // TODO : r��crire cette boucle proprement, surtout les champs des formulaires (utilisation des [])

         foreach($_SESSION["tab_candidatures"] as $cand_id => $cand_array)
         {
            $key_lock="lock_".$cand_id;
            $key_unlock="unlock_".$cand_id;

            $key_newlockdate="newlockdate_".$cand_id;
            $key_jour="jour_verr_".$cand_id;
            $key_mois="mois_verr_".$cand_id;
            $key_annee="annee_verr_".$cand_id;

            if(isset($_POST[$key_unlock])) // d�verrouillage forc�
            {
               // Si la candidature fait partie d'un groupe, on doit d�verrouiller tout le groupe
               if($cand_array["groupe_spec"]!=-1)
               {
                  db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_lock='0'
                                  WHERE $_DBC_cand_candidat_id='$candidat_id'
                                  AND $_DBC_cand_groupe_spec='$cand_array[groupe_spec]'
                                  AND $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
                                                                 WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')");

                  write_evt($dbr, $__EVT_ID_G_UNLOCK, "D�verrouillage manuel (cand. choix multiples)", $candidat_id, $cand_array["groupe_spec"], "");
               }
               else
               {
                  db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_lock='0'
                                    WHERE $_DBC_cand_id='$cand_id'");

                  write_evt($dbr, $__EVT_ID_G_UNLOCK, "D�verrouillage manuel", $candidat_id, $cand_id, "");
               }
            }
            elseif(isset($_POST[$key_lock])) // d�verrouillage forc�
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
               if(strlen($_POST["annee_verr_$cand_id"])==4) // format de l'ann�e
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

                     write_evt($dbr, $__EVT_ID_G_LOCKDATE, "Date de verrouillage modifi�e : $jour/$mois/$annee", $candidat_id, $cand_id, "");
                  }
                  else
                  {
                     db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_lockdate='$new_date'
                                       WHERE $_DBC_cand_id='$cand_id'");

                     $succes_date=1;

                     write_evt($dbr, $__EVT_ID_G_LOCKDATE, "Date de verrouillage modifi�e : $jour/$mois/$annee", $candidat_id, $cand_id, "");
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
   // Ces infos sont utilis�es dans plusieurs menus, c'est pourquoi on met cette requ�te
   // dans edit_candidature.php et non dans menu_precandidatures.php
   //TODO : � v�rifier pour optimiser les requ�tes et les boucles
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

      // Au moins une formation est verrouill�e : fiche modifiable
      if($lock)
         $_SESSION["tab_candidat"]["lock"]=1;
   }

   if(!array_key_exists("lock", $_SESSION['tab_candidat']))
      $_SESSION['tab_candidat']['lock']=0;

   db_free_result($result2);
   

   // Quelques param�tres
   $Y=date('Y');
   $Z=$Y+1;

   // Nettoyage g�n�ral
   // TODO : � g�n�raliser et am�liorer
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
         // Encadr� contenant les infos essentielles du candidat
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
               print("<font class='Texte_important_10'>Aucune adresse �lectronique</font>\n");
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
         // Navigation entre les fiches, par ordre alphab�tique (candidat suivant / pr�c�dent)
         // d�termin�s en fonction du filtre appliqu� par l'utilisateur

         if(isset($_SESSION["filtre_propspec"]) && $_SESSION["filtre_propspec"]!="-1")
         {
            $filtre_formation="AND $_DBC_candidat_id IN (SELECT distinct($_DBC_cand_candidat_id) FROM $_DB_cand
                                                         WHERE $_DBC_cand_propspec_id='$_SESSION[filtre_propspec]'
                                                         AND $_DBC_cand_periode='$__PERIODE')";

            $filtre_formation_statut="<font class='Texte_important_10'><strong>(filtre activ�)</strong></font>";
         }
         else
         {
            // Pas de filtre sur une formation particuli�re = toutes les formations de la composante
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
            // 0 : le candidat est seul dans la base ou dans sa cat�gorie (fonction du filtre)
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
                           $prev_icone="<a class='lien_navigation_10' href='$php_self?cid=$nav_candidat_id'><img style='vertical-align:middle;' src='$__ICON_DIR/back_16x16_blanc.png' desc='Candidat pr�c�dent' title='[Candidat pr�c�dent]' border='0'></a>";
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
                        $prev_icone="<a class='lien_navigation_10' href='$php_self?cid=$nav_candidat_id'><img style='vertical-align:middle;' src='$__ICON_DIR/back_16x16_blanc.png' desc='Candidat pr�c�dent' title='[Candidat pr�c�dent]' border='0'></a>";

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

            // TODO : r�organiser la d�finition des menus dans un tableau, avec les param�tres de niveaux ad�quats
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
         // Inclusion du fichier en fonction du menu s�lectionn�

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


