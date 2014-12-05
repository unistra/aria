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

   verif_auth("$__GESTION_DIR/login.php");

   if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
   {
      $_SESSION["current_user_periode"]=$_POST["periode"];
      
      header("Location:$php_self?succes=1");
      exit();
   }

   $dbr=db_connect();

   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>
<div class='main'>
   <?php
      titre_page_icone("Sélection de l'année universitaire courante", "", 30, "L");

      if(isset($_GET["succes"]))
         message("Année paramétrée avec succès, vous verrez maintenant les candidatures de l'année $_SESSION[current_user_periode]-".($_SESSION["current_user_periode"]+1).".", $__SUCCES);

      message("La sélection de l'année universitaire vous permet de voir et/ou traiter les candidatures des années précédentes", $__INFO);

      $result=db_query($dbr, "(SELECT distinct($_DBC_cand_periode) FROM $_DB_cand, $_DB_propspec
                                 WHERE $_DBC_cand_periode!='$__PERIODE_ABSOLUE'
                                 AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                 AND $_DBC_propspec_comp_id='$_SESSION[comp_id]')
                              UNION
                              (SELECT distinct($_DBC_session_periode) FROM $_DB_session, $_DB_propspec
                                 WHERE $_DBC_session_propspec_id=$_DBC_propspec_id
                                 AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                 AND $_DBC_session_periode!='$__PERIODE_ABSOLUE')
                              UNION 
                              (SELECT '$__PERIODE_ABSOLUE' as $_DBU_cand_periode)
                              ORDER BY $_DBU_cand_periode DESC");

      $rows=db_num_rows($result);

      print("<form action='$php_self' method='POST' name='form1'>
               <div class='centered_box'>
                  <font class='Texte'>Nouvelle année universitaire : </font>
                  <select name='periode' size='1'>\n");

      // Traitement spécial pour la période absolue : elle sont toujours proposées
      /*
      $selected=($__PERIODE==$__PERIODE_ABSOLUE) ? "selected" : "";
      print("<option value='$__PERIODE_ABSOLUE' $selected>$__PERIODE_ABSOLUE-".($__PERIODE_ABSOLUE+1)."</option>\n");
      */
      
      for($i=0; $i<$rows; $i++)
      {
         list($liste_periode)=db_fetch_row($result,$i);
         
         if(isset($_SESSION["current_user_periode"]))
            $selected=($_SESSION["current_user_periode"]==$liste_periode) ? "selected" : "";
         else
            $selected=($liste_periode==$__PERIODE) ? "selected" : "";
         
         print("<option value='$liste_periode' $selected>$liste_periode-".($liste_periode+1)."</option>\n");
      }

      db_free_result($result);

      print("</select>
         </div>

         <div class='centered_icons_box'>
            <a href='index.php' target='_self'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
            <input type='image' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Suivant' name='valider' value='Valider'>
            </form>
         </div>");

      db_close($dbr);
   ?>
</div>
<?php
   pied_de_page();
?>
<script language="javascript">
   document.form1.periode.focus()
</script>
</body></html>
