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
  // Gestion des filtres entre les formations
  // Exemple : si un candidat a sélectionné la formation X, alors il ne peut pas sélectionner la formation Y

  session_name("preinsc_gestion");
  session_start();

  include "../../../configuration/aria_config.php";
  include "$__INCLUDE_DIR_ABS/vars.php";
  include "$__INCLUDE_DIR_ABS/fonctions.php";
  include "$__INCLUDE_DIR_ABS/db.php";

  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  verif_auth("$__GESTION_DIR/login.php");

  if(!in_array($_SESSION['niveau'], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
  {
    header("Location:$__GESTION_DIR/noaccess.php");
    exit();
  }

  // paramètre chiffré : identifiant du filtre en cas de modification
  if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
  {
    if(isset($params["fid"]) && ctype_digit($params["fid"]))
    {
      $_SESSION["fid"]=$params["fid"];
      $_SESSION["modification"]=1;
      $_SESSION["etape"]=1;
    }
  }
  elseif(isset($_SESSION["fid"]) && ctype_digit($_SESSION["fid"]))
    $_SESSION["modification"]=1;
  else // pas de paramètre : ajout d'une étape au cursus
    $_SESSION["ajout"]=1;

  // TRAITEMENT DES FORMULAIRES
  // En fonction de l'étape, on ne stocke pas les valeurs dans les mêmes variables
  if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
  {
    if($_SESSION["etape"]==1)
    {
      $_SESSION["filtre_formations_condition_propspec"]=isset($_POST["propspec_id"]) ? $_POST["propspec_id"] : "-1";
      $_SESSION["filtre_formations_condition_annee"]=isset($_POST["annee_id"]) ? $_POST["annee_id"] : "-1";
      $_SESSION["filtre_formations_condition_mention"]=isset($_POST["mention_id"]) ? $_POST["mention_id"] : "-1";
      $_SESSION["filtre_formations_condition_specialite"]=isset($_POST["spec_id"]) ? $_POST["spec_id"] : "-1";
      $_SESSION["filtre_formations_condition_finalite"]=isset($_POST["finalite"]) ? $_POST["finalite"] : "-1";

      $_SESSION["etape"]=2;
    }
    elseif($_SESSION["etape"]==2 || $_SESSION["etape"]==3)
    {
      $_SESSION["filtre_formations_cible_propspec"]=isset($_POST["propspec_id"]) ? $_POST["propspec_id"] : "-1";
      $_SESSION["filtre_formations_cible_annee"]=isset($_POST["annee_id"]) ? $_POST["annee_id"] : "-1";
      $_SESSION["filtre_formations_cible_mention"]=isset($_POST["mention_id"]) ? $_POST["mention_id"] : "-1";
      $_SESSION["filtre_formations_cible_specialite"]=isset($_POST["spec_id"]) ? $_POST["spec_id"] : "-1";
      $_SESSION["filtre_formations_cible_finalite"]=isset($_POST["finalite"]) ? $_POST["finalite"] : "-1";

      $_SESSION["etape"]=3;

      // Récapitulatif
      // Formation complète : champ prioritaire
      if($_SESSION["filtre_formations_condition_propspec"]!="-1")
      {
        $_SESSION["filtre_condition"]=$_SESSION["tab_formations"][$_SESSION["filtre_formations_condition_propspec"]];
        $_SESSION["filtre_condition_txt"]="";
      }
      else // construction année / mention / spécialité / finalite
      {
        $_SESSION["filtre_condition"]="";
        $_SESSION["filtre_condition_txt"]="";
        $cnt=0;

        if($_SESSION["filtre_formations_condition_annee"]!="-1")
          $_SESSION["filtre_condition"].="<strong>Année</strong> : " . $_SESSION["tab_annees"][$_SESSION["filtre_formations_condition_annee"]];
        else
        {
          $_SESSION["filtre_condition_txt"].="l'année";
          $cnt++;
        }

        if($_SESSION["filtre_formations_condition_mention"]!="-1")
        {
          $_SESSION["filtre_condition"]=$_SESSION["filtre_condition"]=="" ? "" : ", ";
          $_SESSION["filtre_condition"].="<strong>Mention</strong> : " . $_SESSION["tab_mentions"][$_SESSION["filtre_formations_condition_mention"]];
        }
        else
        {
          $_SESSION["filtre_condition_txt"].=$_SESSION["filtre_condition_txt"]=="" ? "la mention" : ", la mention";
          $cnt++;
        }

        if($_SESSION["filtre_formations_condition_specialite"]!="-1")
        {
          $_SESSION["filtre_condition"]=$_SESSION["filtre_condition"]=="" ? "" : ", ";
          $_SESSION["filtre_condition"].="<strong>Spécialité</strong> : " . $_SESSION["tab_specs"][$_SESSION["filtre_formations_condition_specialite"]];
        }
        else
        {
          $_SESSION["filtre_condition_txt"].=$_SESSION["filtre_condition_txt"]=="" ? "la spécialité" : ", la spécialité";
          $cnt++;
        }

        if($_SESSION["filtre_formations_condition_finalite"]!="-1")
        {
          $_SESSION["filtre_condition"]=$_SESSION["filtre_condition"]=="" ? "" : ", ";
          $_SESSION["filtre_condition"].=$tab_finalite[$_SESSION["filtre_formations_condition_finalite"]]=="" ? "" : "</strong>Finalité</strong> : " . $tab_finalite_complete[$_SESSION["filtre_formations_condition_finalite"]];
        }
        else
        {
          $_SESSION["filtre_condition_txt"].=$_SESSION["filtre_condition_txt"]=="" ? "la finalité" : ", la finalité";
          $cnt++;
        }

        if($cnt)
        {
          if($cnt>1)
            $_SESSION["filtre_condition_txt"]="<br>(Quelles que soient $_SESSION[filtre_condition_txt])";
          else
            $_SESSION["filtre_condition_txt"]="<br>(Quelle que soit $_SESSION[filtre_condition_txt])";
        }
      }

      // Même chose pour la cible

      // Formation complète : champ prioritaire
      if($_SESSION["filtre_formations_cible_propspec"]!="-1")
      {
        $filtre_cible=$_SESSION["tab_formations"][$_SESSION["filtre_formations_cible_propspec"]];
        $filtre_cible_txt="";
      }
      else // construction année / mention / spécialité / finalite
      {
        $filtre_cible="";
        $filtre_cible_txt="";
        $cnt=0;

        if($_SESSION["filtre_formations_cible_annee"]!="-1")
          $filtre_cible.="<strong>Année</strong> : " . $_SESSION["tab_annees"][$_SESSION["filtre_formations_cible_annee"]];
        else
        {
          $filtre_cible_txt.="l'année";
          $cnt++;
        }

        if($_SESSION["filtre_formations_cible_mention"]!="-1")
        {
          $filtre_cible=$filtre_cible=="" ? "" : ", ";
          $filtre_cible.="<strong>Mention</strong> : " . $_SESSION["tab_mentions"][$_SESSION["filtre_formations_cible_mention"]];
        }
        else
        {
          $filtre_cible_txt.=$filtre_cible_txt=="" ? "la mention" : ", la mention";
          $cnt++;
        }

        if($_SESSION["filtre_formations_cible_specialite"]!="-1")
        {
          $filtre_cible=$filtre_cible=="" ? "" : ", ";
          $filtre_cible.="<strong>Spécialité</strong> : " . $_SESSION["tab_specs"][$_SESSION["filtre_formations_cible_specialite"]];
        }
        else
        {
          $filtre_cible_txt.=$filtre_cible_txt=="" ? "la spécialité" : ", la spécialité";
          $cnt++;
        }

        if($_SESSION["filtre_formations_cible_finalite"]!="-1")
        {
          $filtre_cible=$filtre_cible=="" ? "" : ", ";
          $filtre_cible.=$tab_finalite[$_SESSION["filtre_formations_cible_finalite"]]=="" ? "" : "</strong>Finalité</strong> : " . $tab_finalite_complete[$_SESSION["filtre_formations_cible_finalite"]];
        }
        else
        {
          $filtre_cible_txt.=$filtre_cible_txt=="" ? "la finalité" : ", la finalité";
          $cnt++;
        }

        if($cnt)
        {
          if($cnt>1)
            $filtre_cible_txt="<br>(Quelles que soient $filtre_cible_txt)";
          else
            $filtre_cible_txt="<br>(Quelle que soit $filtre_cible_txt)";
        }
      }
    }
  }

  $dbr=db_connect();

  // Validation : création / modification du filtre
  if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
  {
    $_SESSION["filtre_formations_nom"]=$filtre_nom=$_POST["nom_filtre"];

    if(isset($_SESSION["filtre_formations_condition_propspec"]) && isset($_SESSION["filtre_formations_condition_annee"])
      && isset($_SESSION["filtre_formations_condition_mention"]) && isset($_SESSION["filtre_formations_condition_specialite"])
      && isset($_SESSION["filtre_formations_condition_finalite"]) && isset($_SESSION["filtre_formations_condition_propspec"])
      && isset($_SESSION["filtre_formations_condition_annee"]) && isset($_SESSION["filtre_formations_condition_mention"])
      && isset($_SESSION["filtre_formations_condition_specialite"]) && isset($_SESSION["filtre_formations_condition_finalite"]))
    {
      if(isset($_SESSION["modification"]) && $_SESSION["modification"]==1)
      {
        // Unicité
        if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_filtres
                              WHERE $_DBC_filtres_cond_propspec_id='$_SESSION[filtre_formations_condition_propspec]'
                              AND $_DBC_filtres_cond_annee_id='$_SESSION[filtre_formations_condition_annee]'
                              AND $_DBC_filtres_cond_mention_id='$_SESSION[filtre_formations_condition_mention]'
                              AND $_DBC_filtres_cond_spec_id='$_SESSION[filtre_formations_condition_specialite]'
                              AND $_DBC_filtres_cond_finalite='$_SESSION[filtre_formations_condition_finalite]'
                              AND $_DBC_filtres_cible_propspec_id='$_SESSION[filtre_formations_cible_propspec]'
                              AND $_DBC_filtres_cible_annee_id='$_SESSION[filtre_formations_cible_annee]'
                              AND $_DBC_filtres_cible_mention_id='$_SESSION[filtre_formations_cible_mention]'
                              AND $_DBC_filtres_cible_spec_id='$_SESSION[filtre_formations_cible_specialite]'
                              AND $_DBC_filtres_cible_finalite='$_SESSION[filtre_formations_cible_finalite]'
                              AND $_DBC_filtres_comp_id='$_SESSION[comp_id]'
                              AND $_DBC_filtres_id!='$_SESSION[fid]'")))
          $filtre_existe=1;
        else
        {
          db_query($dbr, "UPDATE $_DB_filtres SET $_DBU_filtres_nom='$filtre_nom',
                                      $_DBU_filtres_cond_propspec_id='$_SESSION[filtre_formations_condition_propspec]',
                                      $_DBU_filtres_cond_annee_id='$_SESSION[filtre_formations_condition_annee]',
                                      $_DBU_filtres_cond_mention_id='$_SESSION[filtre_formations_condition_mention]',
                                      $_DBU_filtres_cond_spec_id='$_SESSION[filtre_formations_condition_specialite]',
                                      $_DBU_filtres_cond_finalite='$_SESSION[filtre_formations_condition_finalite]',
                                      $_DBU_filtres_cible_propspec_id='$_SESSION[filtre_formations_cible_propspec]',
                                      $_DBU_filtres_cible_annee_id='$_SESSION[filtre_formations_cible_annee]',
                                      $_DBU_filtres_cible_mention_id='$_SESSION[filtre_formations_cible_mention]',
                                      $_DBU_filtres_cible_spec_id='$_SESSION[filtre_formations_cible_specialite]',
                                      $_DBU_filtres_cible_finalite='$_SESSION[filtre_formations_cible_finalite]'
                      WHERE $_DBU_filtres_comp_id='$_SESSION[comp_id]'
                      AND $_DBU_filtres_id='$_SESSION[fid]'");

          write_evt($dbr, $__EVT_ID_G_FILTRES, "Modification filtre $_SESSION[fid]", "", $_SESSION["fid"]);

          $succes="succes_m=1";
        }
      }
      elseif(isset($_SESSION["ajout"]) && $_SESSION["ajout"]==1)
      {
        // Unicité
        if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_filtres
                              WHERE $_DBC_filtres_cond_propspec_id='$_SESSION[filtre_formations_condition_propspec]'
                              AND $_DBC_filtres_cond_annee_id='$_SESSION[filtre_formations_condition_annee]'
                              AND $_DBC_filtres_cond_mention_id='$_SESSION[filtre_formations_condition_mention]'
                              AND $_DBC_filtres_cond_spec_id='$_SESSION[filtre_formations_condition_specialite]'
                              AND $_DBC_filtres_cond_finalite='$_SESSION[filtre_formations_condition_finalite]'
                              AND $_DBC_filtres_cible_propspec_id='$_SESSION[filtre_formations_cible_propspec]'
                              AND $_DBC_filtres_cible_annee_id='$_SESSION[filtre_formations_cible_annee]'
                              AND $_DBC_filtres_cible_mention_id='$_SESSION[filtre_formations_cible_mention]'
                              AND $_DBC_filtres_cible_spec_id='$_SESSION[filtre_formations_cible_specialite]'
                              AND $_DBC_filtres_cible_finalite='$_SESSION[filtre_formations_cible_finalite]'
                              AND $_DBC_filtres_comp_id='$_SESSION[comp_id]'")))
          $filtre_existe=1;
        else
        {
          // Création du filtre défini
          $new_fid=db_locked_query($dbr, $_DB_filtres, "INSERT INTO $_DB_filtres VALUES (
                                          '##NEW_ID##',
                                          '$filtre_nom',
                                          '$_SESSION[comp_id]',
                                          '$_SESSION[filtre_formations_condition_propspec]',
                                          '$_SESSION[filtre_formations_condition_annee]',
                                          '$_SESSION[filtre_formations_condition_mention]',
                                          '$_SESSION[filtre_formations_condition_specialite]',
                                          '$_SESSION[filtre_formations_condition_finalite]',
                                          '$_SESSION[filtre_formations_cible_propspec]',
                                          '$_SESSION[filtre_formations_cible_annee]',
                                          '$_SESSION[filtre_formations_cible_mention]',
                                          '$_SESSION[filtre_formations_cible_specialite]',
                                          '$_SESSION[filtre_formations_cible_finalite]')");

          write_evt($dbr, $__EVT_ID_G_FILTRES, "Ajout filtre $new_fid", "", $new_fid);

          // Création du filtre réciproque ? (s'il n'existe pas déjà)
          if(isset($_POST["reciproque"]) && $_POST["reciproque"]==1)
          {
            if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_filtres
                              WHERE $_DBC_filtres_cond_propspec_id='$_SESSION[filtre_formations_cible_propspec]'
                              AND $_DBC_filtres_cond_annee_id='$_SESSION[filtre_formations_cible_annee]'
                              AND $_DBC_filtres_cond_mention_id='$_SESSION[filtre_formations_cible_mention]'
                              AND $_DBC_filtres_cond_spec_id='$_SESSION[filtre_formations_cible_specialite]'
                              AND $_DBC_filtres_cond_finalite='$_SESSION[filtre_formations_cible_finalite]'
                              AND $_DBC_filtres_cible_propspec_id='$_SESSION[filtre_formations_condition_propspec]'
                              AND $_DBC_filtres_cible_annee_id='$_SESSION[filtre_formations_condition_annee]'
                              AND $_DBC_filtres_cible_mention_id='$_SESSION[filtre_formations_condition_mention]'
                              AND $_DBC_filtres_cible_spec_id='$_SESSION[filtre_formations_condition_specialite]'
                              AND $_DBC_filtres_cible_finalite='$_SESSION[filtre_formations_condition_finalite]'
                              AND $_DBC_filtres_comp_id='$_SESSION[comp_id]'")))
            {
              $new_fid=db_locked_query($dbr, $_DB_filtres, "INSERT INTO $_DB_filtres VALUES (
                                            '##NEW_ID##',
                                            '$filtre_nom (réciproque)',
                                            '$_SESSION[comp_id]',
                                            '$_SESSION[filtre_formations_cible_propspec]',
                                            '$_SESSION[filtre_formations_cible_annee]',
                                            '$_SESSION[filtre_formations_cible_mention]',
                                            '$_SESSION[filtre_formations_cible_specialite]',
                                            '$_SESSION[filtre_formations_cible_finalite]',
                                            '$_SESSION[filtre_formations_condition_propspec]',
                                            '$_SESSION[filtre_formations_condition_annee]',
                                            '$_SESSION[filtre_formations_condition_mention]',
                                            '$_SESSION[filtre_formations_condition_specialite]',
                                            '$_SESSION[filtre_formations_condition_finalite]')");

              write_evt($dbr, $__EVT_ID_G_FILTRES, "Ajout filtre réciproque $new_fid", "", $new_fid);
            }
          }

          $succes="succes_a=1";
        }
      }

      if(!isset($filtre_existe))
      {
        header("Location:index.php?$succes");
        db_close($dbr);
        exit();
      }
    }
  }

  // Changement d'étape
  if(isset($_GET["e"]) && ctype_digit($_GET["e"]) && ($_GET["e"]==1 || $_GET["e"]==2 || $_GET["e"]==3))
    $_SESSION["etape"]=$_GET["e"];
  elseif(!isset($_SESSION["etape"]))  // Etape par défaut
    $_SESSION["etape"]=1;

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    if(isset($_SESSION["ajout"]))
      titre_page_icone("Ajouter un filtre : étape $_SESSION[etape]", "applications-science_32x32_fond.png", 15, "L");
    elseif(isset($_SESSION["modification"]))
      titre_page_icone("Modifier un filtre : étape $_SESSION[etape]", "applications-science_32x32_fond.png", 15, "L");

    print("<form action='$php_self' method='POST' name='form1'>\n");

    if(isset($filtre_existe))
      message("Erreur : un filtre avec des paramètres identiques existe déjà.", $__ERREUR);

    switch($_SESSION["etape"])
    {
      case 1  : message("<center>
                      <strong>Etape 1</strong> : sélection de la condition (<strong>voeu choisi par le candidat</strong>)
                      <br>(le caractère * signifie \"n'importe quel élémént\")
                    </center>", $__INFO);

              // conservation en mémoire des champs select en cas de retour ou de modification
              if(isset($_SESSION["filtre_formations_condition_propspec"]) && $_SESSION["filtre_formations_condition_propspec"]!="-1")
                $cur_val_propspec=$_SESSION["filtre_formations_condition_propspec"];
              elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]) && $_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_propspec_id"]!="-1")
                $cur_val_propspec=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_propspec_id"];
              else
              {
                if(isset($_SESSION["filtre_formations_condition_annee"]))
                  $cur_val_annee=$_SESSION["filtre_formations_condition_annee"];
                elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
                  $cur_val_annee=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_annee_id"];

                if(isset($_SESSION["filtre_formations_condition_mention"]))
                  $cur_val_mention=$_SESSION["filtre_formations_condition_mention"];
                elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
                  $cur_val_mention=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_mention_id"];

                if(isset($_SESSION["filtre_formations_condition_specialite"]))
                  $cur_val_spec=$_SESSION["filtre_formations_condition_specialite"];
                elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
                  $cur_val_spec=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_spec_id"];

                if(isset($_SESSION["filtre_formations_condition_finalite"]))
                  $cur_val_finalite=$_SESSION["filtre_formations_condition_finalite"];
                elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
                  $cur_val_finalite=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_finalite_id"];
              }

              break;

      case 2  : message("<center>
                      <strong>Etape 2</strong> : sélection de la conséquence (<strong>ce que ne peut plus choisir le candidat</strong>)
                      <br>(le caractère * signifie \"n'importe quel élémént\")
                    </center>", $__INFO);

              // mémoire des champs select en cas de retour ou de modification
              if(isset($_SESSION["filtre_formations_cible_propspec"]) && $_SESSION["filtre_formations_cible_propspec"]!="-1")
                $cur_val_propspec=$_SESSION["filtre_formations_cible_propspec"];
              elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]) && $_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_propspec_id"]!="-1")
                $cur_val_propspec=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_propspec_id"];
              else
              {
                if(isset($_SESSION["filtre_formations_cible_annee"]))
                  $cur_val_annee=$_SESSION["filtre_formations_cible_annee"];
                elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
                  $cur_val_annee=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_annee_id"];

                if(isset($_SESSION["filtre_formations_cible_mention"]))
                  $cur_val_mention=$_SESSION["filtre_formations_cible_mention"];
                elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
                  $cur_val_mention=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_mention_id"];

                if(isset($_SESSION["filtre_formations_cible_specialite"]))
                  $cur_val_spec=$_SESSION["filtre_formations_cible_specialite"];
                elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
                  $cur_val_spec=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_spec_id"];

                if(isset($_SESSION["filtre_formations_cible_finalite"]))
                  $cur_val_finalite=$_SESSION["filtre_formations_cible_finalite"];
                elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
                  $cur_val_finalite=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_finalite_id"];
              }

              break;

      case 3  :   message("<strong>Dernière étape</strong> : récapitulatif, options et confirmation", $__INFO);

              if(isset($_SESSION["filtre_formations_nom"]))
                $cur_val_nom=$_SESSION["filtre_formations_nom"];
              elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
                $cur_val_nom=$_SESSION["tab_filtres"][$_SESSION["fid"]]["nom"];
              else
                $cur_val_nom="";

              break;
    }

    if($_SESSION["etape"]!="3")
    {
      // Sélection d'une formation complète
      $result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_propspec_id_spec,
                           $_DBC_specs_nom, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_mentions_nom,
                           $_DBC_propspec_manuelle, $_DBC_propspec_active
                        FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
                      WHERE $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_specs_mention_id=$_DBC_mentions_id
                      AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                      AND $_DBC_propspec_active='1'
                        ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom, $_DBC_propspec_finalite");

      $rows=db_num_rows($result);

      print("<table cellpadding='4' align='center'>
          <tr>
            <td class='fond_menu2' colspan='2'>
              <font class='Texte_menu2'><strong>Sélectionnez une formation ... </strong></font>
            </td>
          </tr>
          <tr>
            <td class='fond_menu2' align='right'>
              <font class='Texte_menu2' style='font-weight:bold;'>Formation : </font>
            </td>
            <td class='fond_menu'>\n");

      if($rows)
      {
        print("<select name='propspec_id' size='1'>
              <option value='-1'></option>\n");

        $old_annee=$old_mention="-1";

        $_SESSION["tab_formations"]=array();

        for($i=0; $i<$rows; $i++)
        {
          list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom, $form_mention_id,
              $form_finalite, $form_mention_nom, $form_manuelle, $form_active)=db_fetch_row($result, $i);

          if($form_annee_id!=$old_annee)
          {
            if($i!=0)
              print("</optgroup>
                    <option value='-1' label='' disabled></option>\n");

            $annee_nom=$form_annee_nom=="" ? "Années particulières" : $form_annee_nom;

            print("<optgroup label='$annee_nom'>\n");

            $new_sep_annee=1;

            $old_annee=$form_annee_id;
            $old_mention="-1";
          }
          else
            $new_sep_annee=0;

          if($form_mention_id!=$old_mention)
          {
            if(!$new_sep_annee)
              print("</optgroup>
                   <option value='-1' label='' disabled></option>\n");

            $val=htmlspecialchars($form_mention_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE);

            print("<optgroup label='- $val'>\n");

            $old_mention=$form_mention_id;
          }

          $manuelle_txt=$form_manuelle ? "(M) " : "";

          $selected=isset($cur_val_propspec) && $cur_val_propspec==$form_propspec_id ? "selected" : "";
          
          if($form_annee_nom=="")
          {
            print("<option value='$form_propspec_id' label=\"$manuelle_txt$form_spec_nom $tab_finalite[$form_finalite]\" $selected>$manuelle_txt$form_spec_nom  $tab_finalite[$form_finalite]</option>\n");
            $_SESSION["tab_formations"][$form_propspec_id]=$form_spec_nom . " " . $tab_finalite[$form_finalite] . " (mention : $form_mention_nom)";
          }
          else
          {
            print("<option value='$form_propspec_id' label=\"$manuelle_txt$form_annee_nom - $form_spec_nom  $tab_finalite[$form_finalite]\" $selected>$manuelle_txt$form_annee_nom - $form_spec_nom  $tab_finalite[$form_finalite]</option>\n");
            $_SESSION["tab_formations"][$form_propspec_id]="$form_annee_nom - $form_spec_nom " . $tab_finalite[$form_finalite] . " (mention : $form_mention_nom)";
          }
        }

        print("</optgroup>
            </select>
            <br>
            <font class='Texte_important_menu'><strong>S'il est utilisé, ce champ sera prioritaire sur les suivants.</strong></font>\n");
      }
      else
        print("<font class='Texte_menu'><i>Aucune formation enregistrée</i></font>\n");

      print("</td>
          </tr>\n");

      // Sélection d'une année
      $result=db_query($dbr, "SELECT $_DBC_annees_id, $_DBC_annees_annee FROM $_DB_annees ORDER BY $_DBC_annees_ordre");
      $rows=db_num_rows($result);

      print("<tr>
            <td class='fond_page' colspan='2' height='15px'></td>
           </tr>
           <tr>
            <td class='fond_menu2' colspan='2'>
              <font class='Texte_menu2'>... <strong>ou</strong> une combinaison des élements suivants :</font>
            </td>
          </tr>
          <tr>
            <td class='fond_menu2' align='right'>
              <font class='Texte_menu2' style='font-weight:bold;'>Année : </font>
            </td>
            <td class='fond_menu'>\n");

      if($rows)
      {
        $_SESSION["tab_annees"]=array();

        print("<select name='annee_id' size='1'>
              <option value='-1'>*</option>\n");

        for($i=0; $i<$rows; $i++)
        {
          list($form_annee_id, $form_annee_nom)=db_fetch_row($result, $i);

          $form_annee_nom=$form_annee_nom=="" ? "Années particulières" : $form_annee_nom;

          $_SESSION["tab_annees"][$form_annee_id]=$form_annee_nom;

          $selected=isset($cur_val_annee) && $cur_val_annee==$form_annee_id ? "selected" : "";

          print("<option value='$form_annee_id' $selected>$form_annee_nom</option>\n");
        }

        print("</optgroup>
            </select>\n");
      }
      else
        print("<font class='Texte_menu'><i>Aucune année enregistrée</i></font>\n");

      print("</td>
          </tr>\n");

      // Sélection d'une mention
      $result=db_query($dbr, "SELECT $_DBC_mentions_id, $_DBC_mentions_nom FROM $_DB_mentions
                        WHERE $_DBC_mentions_comp_id='$_SESSION[comp_id]'
                      ORDER BY $_DBC_mentions_nom");
      $rows=db_num_rows($result);

      print("<tr>
            <td class='fond_menu2' align='right'>
              <font class='Texte_menu2' style='font-weight:bold;'>Mention : </font>
            </td>
            <td class='fond_menu'>\n");

      if($rows)
      {
        $_SESSION["tab_mentions"]=array();

        print("<select name='mention_id' size='1'>
              <option value='-1'>*</option>\n");

        for($i=0; $i<$rows; $i++)
        {
          list($form_mention_id, $form_mention_nom)=db_fetch_row($result, $i);

          $_SESSION["tab_mentions"][$form_mention_id]=$form_mention_nom;

          $selected=isset($cur_val_mention) && $cur_val_mention==$form_mention_id ? "selected" : "";

          print("<option value='$form_mention_id' $selected>$form_mention_nom</option>\n");
        }

        print("</optgroup>
            </select>\n");

      }
      else
        print("<font class='Texte_menu'><i>Aucune mention enregistrée</i></font>\n");

      print("</td>
          </tr>\n");

      // Sélection d'une spécialité
      $result=db_query($dbr, "SELECT $_DBC_specs_id, $_DBC_specs_nom, $_DBC_mentions_nom FROM $_DB_specs, $_DB_mentions
                        WHERE $_DBC_mentions_id=$_DBC_specs_mention_id
                        AND $_DBC_specs_comp_id='$_SESSION[comp_id]'
                      ORDER BY $_DBC_mentions_nom, $_DBC_specs_nom");
      $rows=db_num_rows($result);

      print("<tr>
            <td class='fond_menu2' align='right'>
              <font class='Texte_menu2' style='font-weight:bold;'>Spécialité : </font>
            </td>
            <td class='fond_menu'>\n");

      if($rows)
      {
        $_SESSION["tab_specs"]=array();

        print("<select name='spec_id' size='1'>
              <option value='-1'>*</option>\n");

        $old_mention="--";

        for($i=0; $i<$rows; $i++)
        {
          list($form_spec_id, $form_spec_nom, $mention_nom)=db_fetch_row($result, $i);

          $_SESSION["tab_specs"][$form_spec_id]=$form_spec_nom;

          if($mention_nom!=$old_mention)
          {
            $val=htmlspecialchars($mention_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE);

            print("</optgroup>
                 <optgroup label=\"- $mention_nom\">\n");

            $old_mention=$mention_nom;
          }

          $selected=isset($cur_val_spec) && $cur_val_spec==$form_spec_id ? "selected" : "";

          print("<option value='$form_spec_id' label=\"$form_spec_nom\" $selected>$form_spec_nom</option>\n");
        }

        print("</optgroup>
            </select>\n");

      }
      else
        print("<font class='Texte_menu'><i>Aucune spécialité enregistrée</i></font>\n");

      if(isset($cur_val_finalite))
      {
        switch($cur_val_finalite)
        {
          case  '-1'  : $select_all="selected";
                    $select_sans=$select_rech=$select_pro="";
                    break;

          case  '0' : $select_sans="selected";
                    $select_all=$select_rech=$select_pro="";
                    break;

          case  '1' : $select_rech="selected";
                    $select_all=$select_sans=$select_pro="";
                    break;

          case  '2' : $select_pro="selected";
                    $select_all=$select_sans=$select_rech="";
                    break;
        }
      }
      else
        $select_all=$select_sans=$select_rech=$select_pro="";

      print("</td>
          </tr>
          <tr>
            <td class='fond_menu2' align='right'>
              <font class='Texte_menu2' style='font-weight:bold;'>Finalité : </font>
            </td>
            <td class='fond_menu'>
              <select name='finalite' size='1'>
                <option value='-1' $select_all>*</option>
                <option value='0' $select_sans>Formations sans finalité</option>
                <option value='1' $select_rech>Recherche</option>
                <option value='2' $select_pro>Professionnelle</option>
              </select>
            </td>
          </tr>
          </table>

          <div class='centered_icons_box'>\n");

      if($_SESSION["etape"]==2)
        print("<a href='$php_self?e=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'  title='Retour'></a>\n");

      print("<a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
          <input type='image' class='icone' style='vertical-align:bottom;' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant' value='Suivant' title='[Etape suivante]'>
          </form>
        </div>\n");

      db_free_result($result);
    }
    elseif($_SESSION["etape"]==3) // Etape de confirmation + nommage du filtre
    {
      print("<table cellpadding='4' align='center'>
          <tr>
            <td class='fond_menu2' colspan='2'>
              <font class='Texte_menu2'><strong>Si un candidat choisit :</strong></font>
            </td>
          </tr>
          <tr>
            <td class='fond_menu' colspan='2'>
              <font class='Texte'>$_SESSION[filtre_condition]$_SESSION[filtre_condition_txt]</font>
            </td>
          </tr>
          <tr>
            <td class='fond_menu2' colspan='2'>
              <font class='Texte_menu2'><strong>Alors il ne peut plus choisir :</strong></font>
            </td>
          </tr>
          <tr>
            <td class='fond_menu' colspan='2'>
              <font class='Texte'>$filtre_cible$filtre_cible_txt</font>
            </td>
          </tr>
          <tr>
            <td class='fond_page' colspan='2' height='15px'></td>
           </tr>
           <tr>
            <td class='fond_menu2' colspan='2'>
              <font class='Texte_menu2'><strong>Option</strong></font>
            </td>
          </tr>
          <tr>
            <td class='fond_menu2' align='right'>
              <font class='Texte_menu2' style='font-weight:bold;'>Nom du filtre : </font>
            </td>
            <td class='fond_menu'>
              <input type='text' name='nom_filtre' value='$cur_val_nom' size='40' maxlength='40'>
              <font class='Texte_menu'><i>(40 caractères maximum)</i></font>
            </td>
          </tr>\n");

        if(isset($_SESSION["ajout"]) && $_SESSION["ajout"]==1)
        {
          print("<tr>
                <td class='fond_page' colspan='2' height='15px'></td>
              </tr>
              <tr>
                <td class='fond_menu2' align='right'>
                  <font class='Texte_menu2' style='font-weight:bold;'>Créer automatiquement le filtre réciproque ?</font>
                </td>
                <td class='fond_menu'>
                  <input style='vertical-align:middle; padding-right:5px;' type='radio' name='reciproque' value='1'><font class='Texte_menu'>Oui</font>
                  <input style='vertical-align:middle; padding-left:10px; padding-right:5px;' type='radio' name='reciproque' value='0' checked><font class='Texte_menu'>Non</font>
                </td>
              </tr>\n");
        }

        print("</table>

        <div class='centered_icons_box'>
          <a href='$php_self?e=2' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'  title='Retour'></a>
          <a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0' title='Annuler'></a>
          <input type='image' class='icone' style='vertical-align:bottom;' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider' value='Valider' title='[Valider le filtre]'>
          </form>
        </div>\n");
    }
  ?>
</div>
<?php
  pied_de_page();
?>
</body></html>
