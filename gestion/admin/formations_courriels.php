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

  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  verif_auth("$__GESTION_DIR/login.php");

  if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
  {
    header("Location:$__GESTION_DIR/noaccess.php");
    exit();
  }


  $dbr=db_connect();

  // Suppression éventuelle
  if(isset($_GET["aid"]) && ctype_digit($_GET["aid"]) && isset($_GET["pid"]) && ctype_digit($_GET["pid"]) && isset($_GET["type"]) && ($_GET["type"]=="C" || $_GET["type"]=="F") 
    && (db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_id='$_GET[pid]'
                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'"))
     || db_num_rows(db_query($dbr, "SELECT * FROM $_DB_composantes WHERE $_DBC_composantes_id='$_GET[pid]'"))))
  {
    db_query($dbr, "DELETE FROM $_DB_courriels_propspec WHERE $_DBC_courriels_propspec_propspec_id='$_GET[pid]'
                                       AND $_DBC_courriels_propspec_acces_id='$_GET[aid]'
                                       AND $_DBC_courriels_propspec_type='$_GET[type]'");
  } // Affectation
  elseif(isset($_GET["aid"]) && ctype_digit($_GET["aid"])
       && db_num_rows($res_acces=db_query($dbr, "SELECT $_DBC_acces_nom, $_DBC_acces_prenom FROM $_DB_acces
                                    WHERE $_DBC_acces_id='$_GET[aid]'
                                    AND ($_DBC_acces_composante_id='$_SESSION[comp_id]'
                                        OR $_DBC_acces_id IN (SELECT $_DBC_acces_comp_acces_id FROM $_DB_acces_comp
                                                      WHERE $_DBC_acces_comp_acces_id='$_GET[aid]'
                                                      AND $_DBC_acces_comp_composante_id='$_SESSION[comp_id]'))")))
  {
    $affectation=1;

    list($a_nom, $a_prenom)=db_fetch_row($res_acces, 0);
    db_free_result($res_acces);

    $_SESSION["a_id"]=$a_id=$_GET['aid'];
  }
  elseif((isset($_POST["Valider"]) || isset($_POST["Valider_x"])) && isset($_SESSION["a_id"]))
  {
    // On liste les formations actuellement rattachées
    $res_actuels=db_query($dbr, "SELECT $_DBC_courriels_propspec_propspec_id FROM $_DB_courriels_propspec
                        WHERE $_DBC_courriels_propspec_acces_id='$_SESSION[a_id]'
                        AND $_DBC_courriels_propspec_type='F'
                        AND $_DBC_courriels_propspec_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
                                                      WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                                      AND $_DBC_propspec_active='1')");

    $rows_actuels=db_num_rows($res_actuels);

    $array_actuels=array();

    for($i=0; $i<$rows_actuels; $i++)
    {
      list($propspec_id)=db_fetch_row($res_actuels, $i);
      $array_actuels[$propspec_id]=1;
    }

    db_free_result($res_actuels);

    if(isset($_POST["msg_generiques"]) && !db_num_rows(db_query($dbr,"SELECT * FROM $_DB_courriels_propspec 
                                                                       WHERE $_DBC_courriels_propspec_acces_id='$_SESSION[a_id]'
                                                                       AND $_DBC_courriels_propspec_propspec_id='$_SESSION[comp_id]'
                                                                       AND $_DBC_courriels_propspec_type='C'")))
       db_query($dbr,"INSERT INTO $_DB_courriels_propspec VALUES('$_SESSION[a_id]', '$_SESSION[comp_id]', 'C')");
    elseif(!isset($_POST["msg_generiques"]))
       db_query($dbr,"DELETE FROM $_DB_courriels_propspec WHERE $_DBC_courriels_propspec_acces_id='$_SESSION[a_id]'
                                                          AND $_DBC_courriels_propspec_propspec_id='$_SESSION[comp_id]'
                                                          AND $_DBC_courriels_propspec_type='C'");

    if(isset($_POST["toutes_formations"]))
    {
      // Nettoyage avant insertion
      db_query($dbr, "DELETE FROM $_DB_courriels_propspec
                 WHERE $_DBC_courriels_propspec_acces_id='$_SESSION[a_id]'
                 AND $_DBC_courriels_propspec_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
                                              WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                              AND $_DBC_propspec_active='1')
                 AND $_DBC_courriels_propspec_type='F'");
                 
      $result=db_query($dbr, "SELECT $_DBC_propspec_id FROM $_DB_propspec
                      WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                      AND $_DBC_propspec_active='1'");

      $rows=db_num_rows($result);

      $requete="";

      for($i=0; $i<$rows; $i++)
      {
        list($propspec_id)=db_fetch_row($result, $i);

        $requete.="INSERT INTO $_DB_courriels_propspec VALUES('$_SESSION[a_id]', '$propspec_id', 'F');";
      }

      if(!empty($requete))
        db_query($dbr,"$requete");

      db_free_result($result);
    }
    else // Sélection individuelle
    {
      $requete="";

      if(array_key_exists("formation", $_POST))
      {
         foreach($_POST["formation"] as $formation_id)
         {
          if(!array_key_exists($formation_id, $array_actuels))
            $requete.="INSERT INTO $_DB_courriels_propspec VALUES('$_SESSION[a_id]', '$formation_id', 'F');";

          // Suppression de la formation traitée dans le tableau "actuels"
          unset($array_actuels[$formation_id]);
        }
      }

      if(!empty($requete))
        db_query($dbr,"$requete");

      // On supprime de la base les formations restantes dans le tableau $array_actuels
      foreach($array_actuels as $formation_id => $foo)
        db_query($dbr, "DELETE FROM $_DB_courriels_propspec WHERE $_DBC_courriels_propspec_propspec_id='$formation_id'
                                           AND $_DBC_courriels_propspec_acces_id='$_SESSION[a_id]'
                                           AND $_DBC_courriels_propspec_type='F'");
    }
  }

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    titre_page_icone("Messagerie : relations utilisateurs / formations", "email_32x32_fond.png", 30, "L");

    message("<center>Lorsqu'un candidat envoie un message concernant une formation particulière, les utilisateurs affectés à cette dernière en seront destinataires. 
             <br>En l'absence d'affectation, les utilisateurs rattachés aux <strong>messages génériques</strong> recevront le message.</center>", $__INFO);

    if(!isset($affectation))  // Affichage de toutes les affectations
    {
      // Nettoyage
      unset($_SESSION["a_id"]);

      // Liste des personnes
      // TODO : remplacer $__LVL_ADMIN par $__LVL_RESP après les tests
      $res_acces=db_query($dbr, "SELECT $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel FROM $_DB_acces
                          WHERE ($_DBC_acces_composante_id='$_SESSION[comp_id]'
                          OR $_DBC_acces_id IN (SELECT $_DBC_acces_comp_acces_id FROM $_DB_acces_comp
                                        WHERE $_DBC_acces_comp_composante_id='$_SESSION[comp_id]'))
                          AND $_DBC_acces_niveau IN ('$__LVL_SCOL_MOINS','$__LVL_SCOL_PLUS','$__LVL_RESP','$__LVL_SUPER_RESP','$__LVL_ADMIN')
                          AND $_DBC_acces_reception_msg_scol is TRUE 
                        ORDER BY $_DBC_acces_nom, $_DBC_acces_prenom");
      $rows_acces=db_num_rows($res_acces);

      if($rows_acces)
      {
        print("<table align='center' cellpadding='4'>
            <tr>
            <td align='left'>
              <font class='Texte'>
                <b><u>Liste des utilisateurs potentiels :</u></b> (cliquez sur un nom pour modifier les affectations)<br>\n");

        for($i=0; $i<$rows_acces; $i++)
        {
          list($a_id, $a_nom, $a_prenom)=db_fetch_row($res_acces, $i);

          if($i)
            echo "- ";

          print("<a href='$php_self?aid=$a_id' class='lien_bleu_10'>$a_nom $a_prenom</a> ");
        }

        print("</font>
            </td>
          </tr>
          </table>
          <br>\n");
      }

      db_free_result($res_acces);

         // Première requête à part : sélection des personnes recevant les messages "génériques" (lorsqu'un candidat ne sélectionne pas de formation particulière pour 
         // poser sa question)
         
         print("<table align='center' width='90%' style='padding-bottom:15px;'>
           <tr>
              <td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
                <font class='Texte_menu2'><b>Utilisateurs recevant les messages génériques (sans formation précisée)</b></font>
             </td>
           </tr>\n");
           
         $result=db_query($dbr,"SELECT $_DBC_acces_id, $_DBC_acces_nom as \"$_DBC_acces_nom\", $_DBC_acces_prenom as \"$_DBC_acces_prenom\",
                          $_DBC_acces_courriel as \"$_DBC_acces_courriel\"
                        FROM $_DB_acces, $_DB_courriels_propspec
                      WHERE $_DBC_courriels_propspec_type='C'
                      AND $_DBC_acces_id=$_DBC_courriels_propspec_acces_id
                      AND $_DBC_courriels_propspec_propspec_id='$_SESSION[comp_id]'
                      ORDER BY \"$_DBC_acces_nom\", \"$_DBC_acces_prenom\"");
         
         $rows=db_num_rows($result);
         
         if($rows)
         {
            for($i=0; $i<$rows; $i++)
            {
               list($a_id, $a_nom, $a_prenom, $a_email)=db_fetch_row($result, $i);
            
               if($a_id!="0")
                  print("<tr>
                           <td class='fond_menu' style='padding:2px 5px 2px 5px;'>
                             <font class='Texte_menu'>- <a href='mailto:$a_email' class='lien_bleu_12' title='$a_email'>$a_nom $a_prenom</a></font>
                         </td>
                         <td class='fond_menu' align='right' width='20' style='padding-right:20px;'>
                          <a href='$php_self?pid=$_SESSION[comp_id]&aid=$a_id&type=C' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick.png' alt='Supprimer' border='0'></a>
                         </td>
                        </tr>\n");
        }
         }         
         else
         {
            print("<tr>
                     <td class='fond_menu' style='padding:2px 5px 2px 5px;' colspan='2'>
                       <font class='Texte_menu'>- <i>Tous, par défaut (aucun utilisateur n'a été explicitement rattaché)</i>
                     </td>
                   </tr>\n");
         }
            
         db_free_result($result);
         
         
      // Sélection des formations
      // Union des formations pour lesquelles quelqu'un est rattaché et des formations sans rattachement
      $result=db_query($dbr,"(SELECT $_DBC_propspec_id, $_DBC_propspec_annee as \"$_DBC_propspec_annee\", $_DBC_annees_annee, $_DBC_annees_ordre as \"$_DBC_annees_ordre\",
                          $_DBC_specs_nom_court as \"$_DBC_specs_nom_court\", $_DBC_propspec_finalite as \"$_DBC_propspec_finalite\",
                          $_DBC_specs_mention_id as \"$_DBC_specs_mention_id\", $_DBC_mentions_nom, $_DBC_propspec_manuelle,
                          $_DBC_acces_id, $_DBC_acces_nom as \"$_DBC_acces_nom\", $_DBC_acces_prenom as \"$_DBC_acces_prenom\",
                          $_DBC_acces_courriel as \"$_DBC_acces_courriel\"
                        FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions, $_DB_acces, $_DB_courriels_propspec
                      WHERE $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_courriels_propspec_type='F'
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_specs_mention_id=$_DBC_mentions_id
                      AND $_DBC_acces_id=$_DBC_courriels_propspec_acces_id
                      AND $_DBC_courriels_propspec_propspec_id=$_DBC_propspec_id
                      AND $_DBC_propspec_active='1'
                      AND $_DBC_propspec_comp_id='$_SESSION[comp_id]')
                    UNION ALL
                      (SELECT $_DBC_propspec_id, $_DBC_propspec_annee,$_DBC_annees_annee, $_DBC_annees_ordre, $_DBC_specs_nom_court,
                          $_DBC_propspec_finalite, $_DBC_specs_mention_id, $_DBC_mentions_nom, $_DBC_propspec_manuelle,
                          '0','','',''
                        FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
                      WHERE $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_specs_mention_id=$_DBC_mentions_id
                      AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                      AND $_DBC_propspec_active='1'
                      AND $_DBC_propspec_id NOT IN (SELECT distinct($_DBC_courriels_propspec_propspec_id) FROM $_DB_courriels_propspec))
                        ORDER BY \"$_DBC_annees_ordre\", \"$_DBC_specs_mention_id\", \"$_DBC_propspec_finalite\",
                              \"$_DBC_specs_nom_court\", \"$_DBC_acces_nom\", \"$_DBC_acces_prenom\"");

      $rows=db_num_rows($result);

      $old_propspec_id=$old_annee_id=$old_mention="--"; // on initialise à n'importe quoi (sauf vide)
      $j=0;

      if($rows)
      {
        for($i=0; $i<$rows; $i++)
        {
          list($propspec_id, $annee_id, $annee, $annee_ordre, $spec_nom, $finalite, $mention, $mention_nom, $manuelle, $a_id, $a_nom, $a_prenom, $a_email)=db_fetch_row($result, $i);

          if($propspec_id!=$old_propspec_id)
          {
            if($i)
              print("<tr>
                    <td class='fond_menu' height='10' colspan='2'></td>
                  </tr>\n");

            $nom_finalite=$tab_finalite[$finalite];

            if($annee_id!=$old_annee_id)
            {
              // Fermeture propre du tableau précédent
              if($i) // Le premier résultat du tableau est particulier (i=0)
              {
                print("</table>
                    </td>\n");

                if(!$j && $count_mentions>1)
                  print("<td class='td-droite fond_menu'></td>\n");

                print("</tr>
                    </table>\n");
              }

              $annee=$annee=="" ? "Années particulières" : $annee;

              // Nombre de mentions dans cette année (pour l'affichage)
              $res_mentions=db_query($dbr, "SELECT count(distinct($_DBC_specs_mention_id)) FROM $_DB_specs
                                WHERE $_DBC_specs_id IN
                                  (SELECT distinct($_DBC_propspec_id_spec) FROM $_DB_propspec
                                    WHERE $_DBC_propspec_annee='$annee_id'
                                    AND $_DBC_propspec_comp_id='$_SESSION[comp_id]')");

              list($count_mentions)=db_fetch_row($res_mentions, 0);

              $count_mentions=$count_mentions=="" ? 0 : $count_mentions;

              if($count_mentions>1)
              {
                $colspan_annee=2;
                $colwidth="50%";
              }
              else
              {
                $colspan_annee=1;
                $colwidth="100%";
              }

              db_free_result($res_mentions);

              print("<table align='center' width='90%' style='padding-bottom:15px;'>
                  <tr>
                    <td class='fond_menu2' colspan='$colspan_annee' style='padding:4px 20px 4px 20px;'>
                      <font class='Texte_menu2'><b>$annee</b></font>
                    </td>
                  </tr>
                  <tr>
                    <td class='fond_menu' width='$colwidth' valign='top'>
                      <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                      <tr>
                        <td class='fond_menu2' colspan='2' height='20' align='center'>
                          <font class='Texte_menu2'><b>$mention_nom</b></font>
                        </td>
                      </tr>\n");

              $old_annee_id=$annee_id;
              $old_mention=$mention;
              $j=0;
            }

            if($old_mention!=$mention)
            {
              print("</table>
                  </td>\n");

              if($j)
                print("</tr>
                    <tr>\n");

              print("<td class='fond_menu' width='$colwidth' valign='top'>
                    <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                    <tr>
                      <td class='fond_menu2' colspan='2' height='20' align='center'>
                        <font class='Texte_menu2'><b>$mention_nom</b></font>
                      </td>
                    </tr>\n");

              $j=$j ? 0 : 1;

              $old_mention=$mention;
            }

            $manuelle_txt=$manuelle ? "- Gestion manuelle" : "";

            print("<tr>
                  <td colspan='2' class='td-gauche fond_menu' style='padding-left:4px;'>
                    <font class='Texte_menu'><b>$spec_nom $nom_finalite</b></font>
                  </td>
                </tr>\n");

            if($a_id!="0")
              print("<tr>
                    <td class='fond_menu' style='padding:2px 5px 2px 5px;'>
                      <font class='Texte_menu'>- <a href='mailto:$a_email' class='lien_bleu_12' title='$a_email'>$a_nom $a_prenom</a></font>
                    </td>
                    <td class='fond_menu' align='right' width='20' style='padding-right:20px;'>
                      <a href='$php_self?pid=$propspec_id&aid=$a_id&type=F' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick.png' alt='Supprimer' border='0'></a>
                    </td>
                  </tr>\n");
          }
          elseif($a_id!="0")
          {
            print("<tr>
                  <td class='fond_menu' style='text-align:left; padding:2px 5px 2px 5px;'>
                    <font class='Texte_menu'>- <a href='mailto:$a_email' class='lien_bleu_12' title='$a_email'>$a_nom $a_prenom</a></font>
                  </td>
                  <td class='fond_menu' align='right' width='20' style='padding-right:20px;'>
                    <a href='$php_self?pid=$propspec_id&aid=$a_id&type=F' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick.png' alt='Supprimer' border='0'></a>
                  </td>
                </tr>\n");
          }

          $old_propspec_id=$propspec_id;
        }

        print("<tr>
              <td class='fond_menu' height='10' colspan='2'></td>
            </tr>
            </table>
          </td>\n");

        if(!$j && $colspan_annee>1)
          print("<td class='fond_menu'></td>");

        print("</tr>
             </table>\n");
      }

      db_free_result($result);
    }
    elseif(isset($a_id))  // Modification des affectations personnes / formations
    {
  ?>
    <form method='post' action='<?php echo $php_self; ?>'>

    <table align='center' style='padding-bottom:25px;'>
    <tr>
      <td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
        <font class='Texte_menu2'>
          <b>&#8226;&nbsp;&nbsp;Informations</b>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'>
          <b>Utilisateur sélectionné : </b>
        </font>
      </td>
      <td class='td-droite fond_menu'>
        <font class='Texte_menu'>
          <?php
            print("<b>$a_nom $a_prenom</b>\n");
          ?>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'>
          <b>Sélectionner toutes les formations</b>
        </font>
      </td>
      <td class='td-droite fond_menu'>
        <font class='Texte_menu'>
          <input type='checkbox' name='toutes_formations' value='1'>
          &nbsp;(<i>si cochée, cette case est prioritaire sur la sélection individuelle</i>)
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'>
          <b>Affecter aux messages génériques</b>
        </font>
      </td>
      <td class='td-droite fond_menu'>
        <font class='Texte_menu'>
           <?php
             if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_courriels_propspec 
                                           WHERE $_DBC_courriels_propspec_acces_id='$a_id' 
                                           AND $_DBC_courriels_propspec_propspec_id='$_SESSION[comp_id]'
                                           AND $_DBC_courriels_propspec_type='C'")))
                $checked="checked='1'";
             else
                $checked="";
                
             print("<input type='checkbox' name='msg_generiques' value='1' $checked>\n");
           ?>
        </font>
      </td>
    </tr>
    </table>

    <table align='center'>
    <?php
      $result=db_query($dbr,"(SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
                        FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
                      WHERE $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_specs_mention_id=$_DBC_mentions_id
                      AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                      AND $_DBC_propspec_active='1'
                        ORDER BY $_DBC_annees_ordre, $_DBC_mentions_nom, $_DBC_specs_nom_court, $_DBC_propspec_finalite)");

      $rows=db_num_rows($result);

      $old_annee="===="; // on initialise à n'importe quoi (sauf vide)

      if($rows)
      {
        print("<tr>
              <td class='fond_menu2' align='center' colspan='2' style='padding:4px 20px 4px 20px;'>
                <font class='Texte_menu2'><b>Formations affectées à l'utilisateur</b></font>
              </td>
            </tr>\n");

        // On liste les formations actuellement rattachées
        $res_actuels=db_query($dbr, "SELECT $_DBC_courriels_propspec_propspec_id FROM $_DB_courriels_propspec
                            WHERE $_DBC_courriels_propspec_acces_id='$a_id'");

        $rows_actuels=db_num_rows($res_actuels);

        $array_actuels=array();

        for($i=0; $i<$rows_actuels; $i++)
        {
          list($propspec_id)=db_fetch_row($res_actuels, $i);
          $array_actuels[$propspec_id]=1;
        }

        db_free_result($res_actuels);

        $count=0;

        for($i=0; $i<$rows; $i++)
        {
          list($propspec_id, $annee, $spec_nom, $finalite)=db_fetch_row($result, $i);

          // $nom_finalite=$tab_finalite[$finalite];

          $checked=array_key_exists($propspec_id, $array_actuels) ? "checked" : "";

          $annee=$annee=="" ? "Années particulières" : $annee;

          if($annee!=$old_annee)
          {
            if($count%2)
              print("<td class='td-droite fond_menu'></td>\n");

            $count=0;

            $old_annee=$annee;

            if($i)
              print("</tr>
                  <tr>
                    <td class='fond_page' style='height:10px;'></td>
                  </tr>\n");

            print("<tr>
                  <td class='fond_menu2' align='left' colspan='2' style='padding:4px 20px 4px 20px;'>
                    <font class='Texte_menu2'><b>$annee</b></font>
                  </td>
                </tr>\n");
          }

          if(!($count%2))
            print("<tr>");

          print("<td class='td-gauche fond_menu'>
                <input type='checkbox' name='formation[]' value='$propspec_id' $checked>
                &nbsp;&nbsp;<font class='Texte_menu'>$spec_nom $tab_finalite[$finalite]</font>
              </td>\n");

          if($count%2)
            print("</tr>\n");

          $count++;
        }

        db_free_result($result);

        if($count%2)
          print("<td class='td-droite fond_menu'></td>\n");

        print("</tr>\n");
      }
    ?>
    </table>

    <div class='centered_icons_box'>
      <a href='formations_courriels.php' target='_self'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
      <input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='Valider' value='Valider'>
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
