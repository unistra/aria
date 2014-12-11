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

   if(isset($_SESSION["comp_id"]) && !isset($_GET["d"]) && !isset($_GET["co"]))
      $temp_comp_id=$_SESSION["comp_id"];

   session_unset();

   if(is_file("configuration/aria_config.php")) include "configuration/aria_config.php";
   else $not_found=1;

   if(is_file("include/vars.php")) include "include/vars.php";
   else $not_found=1;

   if(is_file("include/fonctions.php")) include "include/fonctions.php";
   else $not_found=1;

   if(is_file("include/db.php")) include "include/db.php";
   else $not_found=1;

   if(is_file("include/access_functions.php")) include "include/access_functions.php";
   else $not_found=1;

   if(isset($not_found) && $not_found==1)
      die("Configuration de l'interface incomplète - Accès impossible.\n");

   $dbr=db_connect();

   // Chargement de la configuration
   $load_config=__get_config($dbr);

   if($load_config==FALSE) // config absente : erreur
      $erreur_config=1;
   elseif($load_config==-1) // paramètre(s) manquant(s) : avertissement
      $warn_config=1;

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   if(isset($temp_comp_id))
      $_SESSION["comp_id"]=$temp_comp_id;
   elseif(isset($_GET["co"]) && ctype_digit($_GET["co"]))
   {
      $_GET["co"]=str_replace(" ", "", $_GET["co"]);
      
      if(db_num_rows(db_query($dbr, "SELECT  * FROM $_DB_composantes  WHERE $_DBC_composantes_id='$_GET[co]'")))
         $_SESSION["comp_id"]=$_GET["co"];
   }

   $_SESSION["auth"]=0;
   $_SESSION["interface_ouverte"]=0;

   // Sélection des dates limites dans les formations (au cas où l'une d'elles serait postérieure à la date de fermeture ou antérieure à celle d'ouverture)
   $result=db_query($dbr,"SELECT min($_DBC_session_ouverture), max($_DBC_session_fermeture) FROM $_DB_session");
   $rows=db_num_rows($result);

   if($rows)
   {
      list($ouverture, $fermeture)=db_fetch_row($result, 0);

      if(time() >= $ouverture && time() <= $fermeture)
         $_SESSION["interface_ouverte"]=1;
   }

   db_free_result($result);

   unset($_SESSION["conditions_ok"]);

   en_tete_index();
   menu_sup_simple();

?>

<div class='main'>
   <?php
      titre_page_icone("Bienvenue sur l'interface de Précandidatures en ligne", "", 15, "C");
   ?>

   <div style='width:80%; text-align:justify; margin-left:auto; margin-right:auto;'>
      <p class='Texte'>
         Cette interface permet d'accélérer le traitement de vos voeux en nous transmettant directement vos données. Seuls les justificatifs de
         vos diplômes et autres pièces administratives vous seront demandées par voie postale.
      </p>

      <ul>
         <li style='padding-bottom:5px;'><a href='<?php echo "$__DOC_DIR/documentation.php"; ?>' target='_blank' class='lien_bleu_14'><b>Cliquez ici pour consulter la documentation en ligne</b></a></li>
         <li style='padding-bottom:5px;'><a href='<?php echo "$__DOC_DIR/limites.php"; ?>' target='_self' class='lien_bleu_14'><b>Cliquez ici pour vérifier les dates limites de candidature à nos formations</b></a></li>
         <li style='padding-bottom:5px;'><a href='<?php echo "$__DOC_DIR/conditions.php"; ?>' target='_blank' class='lien_rouge_14'><b>IMPORTANT : Cliquez ici pour lire les conditions d'utilisation de l'interface</b></a></li>
      </ul>
   
      <table width='100%' cellpadding="0" cellspacing="0" border='0'>
      <tr>
         <td valign="left" colspan='2' style="background : url(<?php echo "$__IMG_DIR/fond_gradient_menu2.png"; ?>) no-repeat; padding:4px 0px 4px 4px; vertical-align:middle;">
            <font class='Texte3'><u><b>Téléchargements gratuits :</b></u></font>
         </td>
      </tr>
      <tr>
         <td align='center' width='40' style='vertical-align:middle; padding-top:2px; padding-bottom:2px;'>
            <a href='http://www.mozilla.com/firefox/' target='_blank'><img src='<?php echo "$__IMG_DIR/product-firefox.png"; ?>' border='0'></a>
         </td>
         <td align='left' style='vertical-align:middle; padding:4px 0px 2px 10px;'>
            <font class='Texte'>
               <a href='http://www.mozilla.com/firefox/' class='lien_bleu_14' target='_blank'><b>Mozilla Firefox</b></a> : Navigateur Internet
            </font>
         </td>
      </tr>
      <tr>
         <td align='center' width='40' style='vertical-align:middle; padding-top:2px; padding-bottom:2px;'>
            <a href='http://www.adobe.com/fr/' target='_blank'><img src='<?php echo "$__IMG_DIR/get_adobe_reader.gif"; ?>' border='0'></a>
         </td>
         <td align='left' style='vertical-align:middle; padding:4px 0px 2px 10px;'>
            <font class='Texte'>
               <a href='http://www.adobe.com/fr/' class='lien_bleu_14' target='_blank'><b>Adobe Acrobat Reader</b></a> : Ouverture des fichiers PDF
            </font>
         </td>
      </tr>
      </table>

      <?php
         print("<div class='centered_box'>
                  <form action='https://$_SERVER[SERVER_NAME]$__CAND_DIR/identification.php' method='POST'>
                     <input type='checkbox' name='conditions' value='1' style='vertical-align:middle;'>
                     &nbsp;&nbsp;
                     <font class='Texte'>J'ai lu et j'accepte les <a href='doc/conditions.php' target='_blank' class='lien_bleu_14'>conditions d'utilisation de l'interface</a>
                     <br><br>
                     <input type='image' src='$__ICON_DIR/forward_32x32_fond.png' border='0' name='Continuer' value='Continuer'>
                  </form>
               </div>\n");

         if(isset($_GET["conditions"]) && $_GET["conditions"]==0)
            message("Vous devez accepter les conditions d'utilisation de l'interface avant de poursuivre.", $__ERREUR);
      ?>

      
      <font class='Texte_10'>
         <strong><u>Informatique et Libertés</u></strong>
         <br>
         <div style='text-align:justify; padding-bottom:20px;'>
            <?php
               if(isset($GLOBALS["__INFORMATIQUE_ET_LIBERTES"]))
                  echo parse_macros($GLOBALS["__INFORMATIQUE_ET_LIBERTES"]);
            ?>
         </div>
      </font>

   </div>
</div>

<?php pied_de_page_candidat(); ?>

</body>
</html>

