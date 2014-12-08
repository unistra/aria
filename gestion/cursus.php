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

  if(!in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
  {
    header("Location:$__MOD_DIR/gestion/noaccess.php");
    exit();
  }

  // Condition : la fiche doit être verrouillée ou être une fiche manuelle
  if(!isset($_SESSION["tab_candidat"]) || ((!isset($_SESSION["tab_candidat"]["lock"]) || $_SESSION["tab_candidat"]["lock"]!=1)
                             && $_SESSION["tab_candidat"]["manuelle"]!=1))
  {
    header("Location:edit_candidature.php");
    exit;
  }

  // identifiant de l'étudiant
  $candidat_id=$_SESSION["candidat_id"];

  $dbr=db_connect();

  // Verrouillage exclusif
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

  if(isset($_GET["cu_id"]) && is_numeric($_GET["cu_id"])) // modification d'un élément existant : l'identifiant est en paramètre
    $_SESSION["cu_id"]=$cu_id=$_GET["cu_id"];
  elseif(isset($_SESSION["cu_id"]))
    $cu_id=$_SESSION["cu_id"];
  else // pas de paramètre : ajout d'une candidature extérieure
    $cu_id=0;

  if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
  {
    $diplome=$_POST["filiere"];
/*
    if(empty($diplome)) // filière venant du champ libre ?
      $diplome=html_entity_decode(ucfirst(trim($_POST["filiere_libre"])));
*/
    // même traitement avec l'intitulé
/*
    $intitule=html_entity_decode($_POST["intitule"]);
    if(empty($intitule)) // pays venant du champ libre ?
*/
    $intitule=html_entity_decode(ucfirst(trim($_POST["intitule_libre"])));

    // presque pareil avec la spécialité, la ville et l'école
    $specialite=html_entity_decode(ucfirst(strtolower(trim($_POST["specialite"]))));
    $ville=html_entity_decode(ucfirst(trim($_POST["ville"])));
    $ecole=html_entity_decode(trim($_POST["ecole"]));

    // format strict
    $annee_obtention=trim($_POST["annee"]);
    if($annee_obtention=="")
      $annee_obtention=date("Y");
    else
    {
      if(!ctype_digit($annee_obtention) || (ctype_digit($annee_obtention) && $annee_obtention>(date("Y")+1)))
        $annee_format=1;
      elseif($annee_obtention!=0 && strlen($annee_obtention)!=4)
        $annee_format=1;
    }

    $pays=$_POST["pays"];
    
    if(empty($diplome) || empty($intitule) || empty($pays) || $pays=="00" || empty($ville) || empty($ecole))
      $champ_vide=1;

    // champ facultatifs
    $rang=html_entity_decode(trim($_POST["rang"]));
    $mention=html_entity_decode(trim($_POST["mention"]));
    
    $note_moyenne=html_entity_decode(trim($_POST["note"]));
    $note_moyenne=preg_replace("/,/",".",$note_moyenne);

    if(!isset($champ_vide) && !isset($annee_format))
    {
      // ajout ?
      
      if($cu_id==0)
      {
        $cursus_id=db_locked_query($dbr, $_DB_cursus, "INSERT INTO $_DB_cursus VALUES('##NEW_ID##','$candidat_id','$diplome','$intitule','$specialite','$annee_obtention','$ecole','$ville','$pays','$note_moyenne','$mention','$rang')");

        write_evt($dbr, $__EVT_ID_G_CURSUS, "Ajout cursus $cursus_id", $candidat_id, $cursus_id, "INSERT INTO $_DB_cursus VALUES('$cursus_id','$candidat_id','$diplome','$intitule','$specialite','$annee_obtention','$ecole','$ville','$pays','$note_moyenne','$mention','$rang')");
      }
      else
      {
        $req="UPDATE $_DB_cursus SET $_DBU_cursus_diplome='$diplome',
                            $_DBU_cursus_intitule='$intitule',
                            $_DBU_cursus_spec='$specialite',
                            $_DBU_cursus_annee='$annee_obtention',
                            $_DBU_cursus_ecole='$ecole',
                            $_DBU_cursus_ville='$ville',
                            $_DBU_cursus_pays='$pays',
                            $_DBU_cursus_moyenne='$note_moyenne',
                            $_DBU_cursus_mention='$mention',
                            $_DBU_cursus_rang='$rang'
            WHERE $_DBU_cursus_id='$cu_id' AND $_DBU_cursus_candidat_id='$candidat_id'";

        db_query($dbr, $req);

        write_evt($dbr, $__EVT_ID_G_CURSUS, "Modification cursus $cu_id", $candidat_id, $cu_id, $req);
      }

      db_close($dbr);

      header("Location:edit_candidature.php");
      exit();
    }
  }

  if($cu_id!=0)
  {
    // récupération des valeurs courantes
    $result=db_query($dbr,"SELECT $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_annee,
                                      $_DBC_cursus_ecole, $_DBC_cursus_ville, $_DBC_cursus_pays, $_DBC_cursus_moyenne,
                                      $_DBC_cursus_mention, $_DBC_cursus_rang
                              FROM $_DB_cursus WHERE $_DBC_cursus_id='$cu_id'");
    $rows=db_num_rows($result);

    if(!$rows) // erreur
    {
      db_free_result($result);
      db_close($dbr);
      header("Location:edit_candidature.php");
      exit();
    }
    else
    {
      list($cur_diplome,$cur_intitule,$cur_specialite,$cur_annee_obtention,$cur_ecole,$cur_ville,$cur_pays,$cur_note_moyenne,$cur_mention,$cur_rang)=db_fetch_row($result,0);
      db_free_result($result);
    }
  }
  else // nouvelle étape : initialisation des valeurs
    $cur_diplome=$cur_intitule=$cur_specialite=$cur_annee_obtention=$cur_ecole=$cur_ville=$cur_pays=$cur_note_moyenne=$cur_mention=$cur_rang="";


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

    titre_page_icone("Ajouter / Modifier une étape du cursus scolaire", "edit_32x32_fond.png", 15, "L");

    if(isset($champ_vide))
      message("Formulaire incomplet : les champs en gras sont <u>obligatoires</u>", $__ERREUR);
    elseif(isset($annee_format))
      message("Erreur : la valeur du champ 'Année' est incorrecte (valeur numérique à 4 chiffres, années futures interdites)", $__ERREUR);
    else
      message("Rappel : les champs en gras sont <u>obligatoires</u>", $__WARNING);
  ?>
  <table align='center'>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_important_menu2'><b>Diplôme / Niveau d'études</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <select name='filiere' size='1'>
        <?php

          $result=db_query($dbr,"SELECT $_DBC_cursus_diplomes_intitule, $_DBC_cursus_diplomes_niveau
                            FROM $_DB_cursus_diplomes
                          ORDER BY $_DBC_cursus_diplomes_niveau,lower($_DBC_cursus_diplomes_intitule)");
  /*
          $result=db_query($dbr,"SELECT $_DBC_cursus_apogee_code, $_DBC_cursus_apogee_libelle_long
                            FROM $_DB_cursus_apogee
                          ORDER BY $_DBC_cursus_apogee_code");
  */
          $rows=db_num_rows($result);

          $current_niveau=-10; // initialisé à n'importe quelle valeur inférieure à -1

          if(empty($cur_diplome))
            print("<option value='' selected=1></option>");
          else
            print("<option value=''></option>");

          if(isset($cur_diplome) && $cur_diplome=="Autre")
            $selected="selected";
          else
            $selected="";

          print("<option value='Autre' $selected>Autre (préciser dans le champ \"Mention - Intitulé\"</option>
               <option value=''></option>\n");

          $value2=preg_replace("/_/","",htmlspecialchars(stripslashes($cur_diplome), ENT_QUOTES, $default_htmlspecialchars_encoding));

          for($i=0; $i<$rows; $i++)
          {
            list($diplome_intitule, $diplome_niveau)=db_fetch_row($result,$i);
            $value=htmlspecialchars($diplome_intitule, ENT_QUOTES, $default_htmlspecialchars_encoding);

            if($diplome_niveau!=$current_niveau)
            {
              switch($diplome_niveau)
              {
                case -1 : $type_niveau="------ Filières particulières ------";
                            break;

                case 0    : $type_niveau="------ Niveau Baccalauréat ------";
                            break;

                default : $type_niveau="------ Niveau Bac + $diplome_niveau ------";
                            break;
              }

              print("<option value='' disabled>$type_niveau</option>");

              $current_niveau=$diplome_niveau;
            }

            if(isset($cur_diplome) && $value2==$value)
            {
              $selected="selected=1";
            }
            else
              $selected="";
            print("<option value='$value' $selected>$value</option>\n");
          }
          db_free_result($result);
        ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_important_menu2'><b>Mention / Intitulé</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='intitule_libre' value='<?php if(isset($cur_intitule) && !isset($intitule_liste)) echo htmlspecialchars(str_replace("_","",stripslashes($cur_intitule)),ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="80" maxlength="256">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Spécialité / Parcours (si applicable)</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='specialite' value='<?php if(isset($specialite)) echo htmlspecialchars(stripslashes(str_replace("_","",$specialite)), ENT_QUOTES, $default_htmlspecialchars_encoding);  else echo htmlspecialchars(preg_replace("/_/","",$cur_specialite),ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='50' size='30'>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_important_menu2'><b>Année</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='annee' value='<?php if(isset($annee_obtention)) echo $annee_obtention; else echo $cur_annee_obtention;?>' maxlength='4' size='15'>&nbsp;&nbsp;
      <font class='Texte_menu'>
        <i><u>Format</u> : AAAA
        <br>Si le champ est vide, la valeur <strong><?php echo date("Y"); ?></strong>  sera automatiquement prise en compte.
        </i>
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_important_menu2'><b>Etablissement</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='ecole' value='<?php if(isset($ecole)) $cur_ecole=$ecole; echo htmlspecialchars(str_replace("_","",stripslashes($cur_ecole)),ENT_QUOTES, $default_htmlspecialchars_encoding);?>' maxlength='128' size='30'>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_important_menu2'><b>Ville</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='ville' value='<?php if(isset($ville)) $cur_ville=$ville; echo htmlspecialchars(str_replace("_","",stripslashes($cur_ville)),ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='128' size='30'>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_important_menu2'><b>Pays</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <select style='padding-right:10px;' name='pays' size='1'>
        <?php
          $res_pays_nat=db_query($dbr, "SELECT $_DBC_pays_nat_ii_iso, $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii
                              ORDER BY unaccent($_DBC_pays_nat_ii_pays)");
                      
          $rows_pays_nat=db_num_rows($res_pays_nat);
  
          for($p=0; $p<$rows_pays_nat; $p++)
          {
            list($code_iso, $table_pays)=db_fetch_row($res_pays_nat, $p);
    
            $selected=(isset($pays) && $pays==$code_iso) || (isset($cur_pays) && $cur_pays==$code_iso) ? "selected=1" : "";

            print("<option value='$code_iso' $selected>$table_pays</option>\n");
          }
        ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Mention / Résultat obtenu</font>
    </td>
    <td class='td-droite fond_menu'>
      <select name='mention' size='1'>
        <?php
          $result=db_query($dbr,"SELECT $_DBC_cursus_mentions_intitule FROM $_DB_cursus_mentions ORDER BY id");
          $rows=db_num_rows($result);

          if(isset($mention))
            $cur_mention=$mention;

          $value2=htmlspecialchars($cur_mention,ENT_QUOTES, $default_htmlspecialchars_encoding);

          for($i=0; $i<$rows; $i++)
          {
            list($mention)=db_fetch_row($result,$i);
            $value=htmlspecialchars($mention,ENT_QUOTES, $default_htmlspecialchars_encoding);

            if(isset($value2) && !strcmp($value,$value2))
              $selected="selected=1";
            else
              $selected="";

            print("<option value='$value' $selected>$value</option>\n");
          }
        ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Moyenne rapportée à 20 (si connue)</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='note' value='<?php if(isset($note_moyenne) && $note_moyenne!=0) echo str_replace("_","",$note_moyenne); elseif($cur_note_moyenne != "0") echo preg_replace("/_/","",$cur_note_moyenne);?>' maxlength='10' size='10'>&nbsp;&nbsp;<font class='Texte_menu'><i>Exemple : 14,54/20</i></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Rang (si connu)</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='rang' value='<?php if(isset($rang)) echo preg_replace("/_/","",$rang); else echo str_replace("_","",$cur_rang);?>' maxlength='15' size='10'>&nbsp;&nbsp;<font class='Texte_menu'><i>Exemple : 22/80</i></font>
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

<script language="javascript">
  document.form1.diplome.focus()
</script>
</body></html>

