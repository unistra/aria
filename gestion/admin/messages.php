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

   if(!in_array($_SESSION["niveau"], array("$__LVL_ADMIN")))
   {
      header("Location:$__GESTION_DIR/noaccess.php");
      exit();
   }

   $dbr=db_connect();
   
   if(isset($_GET["succes"]) && ctype_digit($_GET["succes"]))
      $succes=$_GET["succes"];

   if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
   {
      $message_type=$_POST["message_type"];
      
      if($message_type!="" && ctype_digit($message_type))
      {
         $result=db_query($dbr, "SELECT $_DBC_messages_contenu, $_DBC_messages_actif
                                 FROM $_DB_messages
                                 WHERE $_DBC_messages_type='$message_type'
                                 AND $_DBC_messages_comp_id='$_SESSION[comp_id]'");
                                 
         if(db_num_rows($result)) // normalement 1 seul résultat unique
            list($current_msg_contenu, $current_msg_actif)=db_fetch_row($result, 0);
         else
         {
            if(array_key_exists($message_type, $__MSG_TYPES) && array_key_exists("defaut", $__MSG_TYPES["$message_type"]))
            {
               $current_msg_contenu=$__MSG_TYPES["$message_type"]["defaut"];
               $current_msg_actif="f";
            }
         }

         db_free_result($result);
      }
   }
   elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
   {
      $corps_message=$_POST["corps_message"];
      
      $type_message=$_POST["message_type"];
      
      if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_messages WHERE $_DBC_messages_comp_id='$_SESSION[comp_id]' AND $_DBC_messages_type='$type_message'")))
         db_query($dbr, "UPDATE $_DB_messages SET $_DBU_messages_contenu='$corps_message',
                                                  $_DBU_messages_actif='t'
                         WHERE $_DBC_messages_comp_id='$_SESSION[comp_id]' 
                         AND $_DBC_messages_type='$type_message'");
      else
         db_query($dbr, "INSERT INTO $_DB_messages VALUES ('$_SESSION[comp_id]', '$type_message', '0', '0', '$corps_message', 't')");
         
      $succes=1;      
   }
      
/*
   elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
   {
      $univ_id=isset($_POST["univ_id"]) ? $_POST["univ_id"] : "";

      $univ_lettre_code_apogee=mb_strtoupper(trim($_POST['lettre_code_apogee']));
      $prefixe_opi=mb_strtoupper(trim($_POST["prefixe_opi"]));

      $message_primo=trim($_POST['message_primo']);

      if(db_num_rows(db_query($dbr,"SELECT * FROM $_module_apogee_DB_config WHERE $_module_apogee_DBC_config_univ_id='$univ_id'")))
         db_query($dbr,"UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_code='$univ_lettre_code_apogee',
                                                                $_module_apogee_DBU_config_prefixe_opi='$prefixe_opi',
                                                                $_module_apogee_DBU_config_message_primo='$message_primo'
                           WHERE $_module_apogee_DBU_config_univ_id='$univ_id'");
      else
         db_query($dbr,"INSERT INTO $_module_apogee_DB_config VALUES ('$univ_id','$univ_lettre_code_apogee','$prefixe_opi','$message_primo')");

      write_evt($dbr, $__EVT_ID_G_ADMIN, "MOD_APOGEE : modification de la configuration - Université id#$univ_id", "", $univ_id);

      // Si l'université modifiée est celle courante, on met à jour les variables de session de l'utilisateur
      $_SESSION["comp_lettre_apogee"]=$univ_lettre_code_apogee;
      db_close($dbr);

      header("Location:$php_self?succes=1");
      exit;
   }
*/
   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>

<div class='main'>
   <form action='<?php echo $php_self; ?>' method='POST'>
   <?php
      titre_page_icone("Textes et messages propres à cet établissement", "edit_32x32_fond.png", 15, "L");

      if(isset($succes))
         message("Message mis à jour avec succès.", $__SUCCES);

      print("<form name=\"form1\" method=\"POST\" action=\"$php_self\">\n");

      if(!isset($current_msg_contenu))
      {
         // Choix des messages à modifier
   ?>		
   
   <table align='center'>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Sélection du message : </b></font>
      </td>
      <td class='td-milieu fond_menu'>
         <select name='message_type'>
            <option value='' disable='1'></option>
            <?php
               foreach($__MSG_TYPES as $type => $array_type)
               {
                  print("<option value='$type'>$array_type[titre]</option>\n");
               }
            ?>
         </select>
         <?php
/*         
            $res_messages=db_query($dbr, "SELECT $_DBC_messages_type, $_DBC_messages_titre
                                             FROM $_DB_messages
                                          WHERE $_DBC_messages_comp_id='$_SESSION[comp_id]' 
                                          ORDER BY $_DBC_messages_titre");
                                       
            $nb_messages=db_num_rows($res_messages);
  
   	      if($nb_messages)
            {
               print("<select name='message_id'>\n");
 
               for($i=0; $i<$nb_messages; $i++)
               {
                  list($msg_id, $msg_titre)=db_fetch_row($res_messages, $i);
                  
                  print("<option value='$msg_id'>$msg_titre</value>\n");
               }
               
               print("</select>\n");
            }
            else
               print("Configuration incomplète : aucun message à modifier\n");
*/
         ?>
      </td>
      <td width='20' class='td-droite fond_menu'>
        <input type='image' class='icone' src='<?php echo "$__ICON_DIR/forward_22x22_menu.png"; ?>' alt='Suivant' title='[Suivant]' name='suivant' value='Suivant'>
      </td>
   </tr>   
   </table>
   
   <div class='centered_icons_box'>
      <?php
         print("<a href='index.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>");
      ?>
   </div>
   
   <?php
      }
      else
      {
   ?>
   <input type='hidden' name='message_type' value='<?php echo $message_type; ?>'>
   <table align='center'>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Message : </b></font>
      </td>
      <td class='td-droite fond_menu'>
         <font class='Texte_menu'><?php echo $__MSG_TYPES[$message_type]["titre"]; ?></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Description : </b></font>
      </td>
      <td class='td-droite fond_menu'>
         <font class='Texte_menu'><?php echo nl2br($__MSG_TYPES[$message_type]["desc"]); ?></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Contenu actuel : </b></font>
      </td>
      <td class='td-droite fond_menu'>
         <textarea name='corps_message' rows='25' cols='100'><?php echo $current_msg_contenu; ?></textarea>
      </td>
   </tr>
   <?php
      if($__MSG_TYPES[$message_type]["liste_macros"]!="")
      {
   ?>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Les macros suivantes sont<br />utilisables dans le message : </b></font>
      </td>
      <td class='td-droite fond_menu'>
         <font class='Texte_menu'><?php echo $__MSG_TYPES[$message_type]["liste_macros"]; ?></font>
      </td>
   </tr>
   <?php
      }
   ?>
   </table>

   <div class='centered_icons_box'>
      <?php
         if(isset($succes))
            print("<a href='$php_self' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>");
         else
            print("<a href='$php_self' target='_self'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>");
      ?>
      <input type='image' class='icone' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' title='[Valider]' name='valider' value='Valider'>
      </form>
   </div>

   <script language="javascript">
      document.form1.corps_message.focus()
   </script>

   <?php
      }
      db_close($dbr);
   ?>
</div>
<?php
   pied_de_page();
?>

</body></html>
