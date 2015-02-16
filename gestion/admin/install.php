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
//  session_name("preinsc");
//  session_start();

  include "../../configuration/aria_config.php";
  include "$__INCLUDE_DIR_ABS/vars.php";

  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

/*
  en_tete_simple();
  menu_sup_simple();
*/

  // Conversion des valeurs de type 'tailles mémoires' (xM ou xG) utilisées dans les fichiers de configuration en octets
  // Source : http://php.net/manual/en/function.ini-get.php (cf "Exemple #1") avec les remarques de "peter" (commentaire du 29/07/2008)
  function return_bytes($val)
  {
    $val=trim($val);
    $unit=strtolower(mb_substr($val,strlen($val/1),1, "UTF-8"));

    switch($unit)
    {
      case 'g'  : $val *= 1024;
      case 'm'  : $val *= 1024;
      case 'k'  : $val *= 1024;
    }

    return $val;
  }

  // Test de connexion à la base, pour la vérification de l'installation
  // Presque identifique à db_connect, mais l'échec de connexion n'entraine rien

  function db_test()
  {
      $ssl_config=(isset($GLOBALS["__DB_SSLMODE"]) && $GLOBALS["__DB_SSLMODE"]!="") ? "sslmode=$GLOBALS[__DB_SSLMODE]" : "";

    $dbr=pg_connect("host=$GLOBALS[__DB_HOST] port=$GLOBALS[__DB_PORT] dbname=$GLOBALS[__DB_BASE] user=$GLOBALS[__DB_USER] password=$GLOBALS[__DB_PASS] $ssl_config");

    $res=($dbr==FALSE) ? 0 : 1;

    pg_close($dbr);

    return $res;
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
   <head>
      <title>UFR de Mathématique et d'Informatique - Gestion des précandidatures</title>
      <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
      <meta http-equiv='Pragma' content='no-cache'>
   </head>

   <body class='main' leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' vlink='black' alink='black' link='black'>

<style type="text/css">
.fond_menu2
{
  background-color: #CCDDEE;
}

.fond_menu
{
  background-color: #DDEEFF;
}

font.Texte
{
  font-family: Arial, Helvetica, sans-serif;
  vertical-align:middle;
  font-size: 12px;
  font-weight:normal;
  color:black;
}
</style>

<div>
  <?php
//    titre_page_icone("Vérification de l'installation", "ksysv_32x32_fond.png", 10, "L");

    print("<table style='margin-left:auto; margin-right:auto; padding-bottom:20px;'>
         <tr>
          <td class='fond_menu2' style='padding:4px;'>
            <font class='Texte'><strong>Test</strong></font>
          </td>
          <td class='fond_menu2' style='padding:4px; text-align:center; width:30px;'>
            <font class='Texte'><strong>Statut</strong></font>
          </td>
          <td class='fond_menu2' style='padding:4px;'>
            <font class='Texte'><strong>Erreurs/Détail</strong></font>
          </td>
          <td class='fond_menu2' style='padding:4px;'>
            <font class='Texte'><strong>Commentaires</strong></font>
          </td>
         </tr>\n");

    // Test des fonctionnalités PHP
    
    //  Version PHP (>5)
    $php_version=phpversion();

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>Version PHP (version 5 minimum requise)</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>\n");

    if((int)mb_substr($php_version, 0, 1, "UTF-8")<"5")
      print("<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>
          </td>
          <td class='fond_menu' style='padding:4px;'>
            <font class='Texte'>Mettre à jour PHP - module HTTP et Ligne de commande (CLI) - en version 5</font>
          </td>
          <td class='fond_menu' style='padding:4px;'></td>
        </tr>\n");
    else
      print("<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>
          </td>
          <td class='fond_menu' style='padding:4px;'></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>Version $php_version installée</font></td>
        </tr>\n");

    // PHP-CLI
    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP-CLI (ligne de commande)</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>\n");

    $array_result=array();
    $retstr=exec("which php 2>&1", $array_result, $retval); // retval vaut 0 si la commande a réussi

    if($retval)
      print("<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>
          </td>
          <td class='fond_menu' style='padding:4px;'>
            <font class='Texte'>Exécutable \"php\" non trouvé.</font>
          </td>
          <td class='fond_menu' style='padding:4px;'>
              <font class='Texte'>
              Vérifier le chemin d'accès ou installer la version \"CLI\" de PHP en plus du module HTTP.
            </font>
          </td>
        </tr>\n");
    else
    {
      print("<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>
          </td>
          <td class='fond_menu' style='padding:4px;'></td>          
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>
            Exécutable trouvé : ");

      foreach($array_result as $line)
        print("$line<br>\n");

      print("</font></td>
        </tr>\n");
    }

    // Tests des modules complémentaires
    // posix
    // Informations pouvant être nécessaires le cas échéant (nécessitent le support des fonctions posix)
    if(function_exists("posix_getpwuid") && function_exists("posix_geteuid") && function_exists("posix_getgrgid") && function_exists("posix_getegid"))
    {
      $current_process_uid=posix_geteuid();
      $array_process=posix_getpwuid($current_process_uid);
      $current_process_username=$array_process["name"];

      $array_groupe=posix_getgrgid(posix_getegid());
      $current_process_group_name=$array_groupe["name"];
      $current_process_gid=$array_groupe["gid"];

      unset($array_process);
      unset($array_groupe);

      $fonctions_posix=1;
    }
    else
      $fonctions_posix=0;

    // mcrypt
    $fonctions_mcrypt=function_exists("mcrypt_module_open") && function_exists("mcrypt_create_iv") ? "1" : "0";

    // ctype
    $fonctions_ctype=function_exists("ctype_digit") ? "1" : "0";

    // gmp : gmp_strval gmp_mod gmp_init
    $fonctions_gmp=function_exists("gmp_strval") && function_exists("gmp_mod") && function_exists("gmp_init") ? "1" : "0";

    // gd (images)
    $fonctions_gd=function_exists("imagecreatefrompng") && function_exists("imagepng") ? "1" : "0";

    // postgresql
    $fonctions_postgresql=function_exists("pg_connect") && function_exists("pg_query") ? "1" : "0";

      // mbstring
      $fonctions_mbstring=function_exists("mb_strtoupper") && function_exists("mb_strtolower") ? "1" : "0";

    // sessions
    $fonctions_sessions=function_exists("session_start") && function_exists("session_destroy") ? "1" : "0";

    // PEAR:Mail
    // récupération et découpage de la variable include_path, puis recherche du ou des fichiers voulus
    // TODO : autre méthode pour tester une classe, sans inclure le fichier php et sans créer d'objet ?

    $array_path=explode(":", get_include_path());

    $fonctions_mime=$fonctions_mails="0";

    if(is_array($array_path))
    {
      foreach($array_path as $dir)
      {
        if(is_file("$dir/Mail.php") && is_readable("$dir/Mail.php"))
          $fonctions_mails=1;

        if(is_file("$dir/Mail/mime.php") && is_readable("$dir/Mail/mime.php"))
          $fonctions_mime="1";
      }
    }

    $fonctions_pdf=file_exists("$__FPDF_DIR_ABS/fpdf.php") && is_readable("$__FPDF_DIR_ABS/fpdf.php") ? "1" : "0";

    // Affichage

    $txt_erreurs=$fonctions_posix ? "" : "Vérifier la présence de l'extension \"posix\"";
    $icone=$fonctions_posix ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
    $detail="Fonctions testées : posix_getpwuid, posix_geteuid, posix_getgrgid, posix_getegid";

    print("<tr>
          <td style='padding:4px; background-color:#CCDDEE;' colspan='4'>
            <font class='Texte'><strong>PHP : Extensions</strong></font>
          </td>
        </tr>
        <tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions posix</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
        </tr>\n");

    $txt_erreurs=$fonctions_mcrypt ? "" : "Vérifier la présence de l'extension \"Mcrypt\"";
    $icone=$fonctions_mcrypt ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
    $detail="Fonctions testées : mcrypt_module_open, mcrypt_create_iv";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions mcrypt</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
          </tr>\n");

      $txt_erreurs=$fonctions_mbstring ? "" : "Vérifier la présence de l'extension \"mbstring\"";
      $icone=$fonctions_mbstring ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
      $detail="Fonctions testées : mb_strtoupper, mb_strtolower";

      print("<tr>
               <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions mbstring</strong></font></td>
               <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
               <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
               <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
            </tr>\n");

    $txt_erreurs=$fonctions_ctype ? "" : "Vérifier la présence de l'extension \"Ctype\"";
    $icone=$fonctions_ctype ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
    $detail="Fonction testée : ctype_digit";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions ctype</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
        </tr>\n");

    $txt_erreurs=$fonctions_gmp ? "" : "Vérifier la présence de l'extension \"GMP\"";
    $icone=$fonctions_gmp ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
    $detail="Fonctions testées : gmp_init, gmp_mod, gmp_strval";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions GMP</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
        </tr>\n");

    $txt_erreurs=$fonctions_gd ? "" : "Vérifier la présence de l'extension \"gd\"";
    $icone=$fonctions_gd ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
    $detail="Fonctions testées : imagecreatefrompng, imagepng";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions gd</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
          </tr>\n");

    $txt_erreurs=$fonctions_postgresql ? "" : "Vérifier la présence de l'extension \"PostgreSQL\"";
    $icone=$fonctions_postgresql ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
    $detail="Fonctions testées : pg_connect, pg_query";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions PostgreSQL</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
          </tr>\n");

    $txt_erreurs=$fonctions_sessions ? "" : "Vérifier la présence de l'extension \"Sessions\"";
    $icone=$fonctions_sessions ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
    $detail="Fonctions testées : session_start, session_destroy";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions Sessions</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
          </tr>\n");

    $txt_erreurs=$fonctions_mails ? "" : "Vérifier la présence de l'extension \"PEAR:Mail\"";
    $icone=$fonctions_mails ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
    $detail="Fichier \"Mail.php\" dans le <i>path</i>
          <br>(".get_include_path().")";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions PEAR:Mail</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
          </tr>\n");

    $txt_erreurs=$fonctions_mime ? "" : "Vérifier la présence de l'extension \"PEAR:Mail_Mime\"";
    $icone=$fonctions_mime ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
    $detail="Fichier \"Mail/mime.php\" dans le <i>path</i>
          <br>(".get_include_path().")";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions PEAR:Mail_Mime</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
          </tr>\n");

    $txt_erreurs=$fonctions_pdf ? "" : "Vérifier la présence de l'extension \"FPDF\"";
    $icone=$fonctions_pdf ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";
    $detail="Fichier cherché : ". preg_replace("/[\/]+/","/", "$__FPDF_DIR_ABS/fpdf.php");

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>PHP : Fonctions FPDF</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$detail</font></td>
          </tr>\n");


    // PARAMETRES et références pour les valeurs numériques
    $ref_max_execution_time=3600; // en secondes
    $ref_max_input_time=600; // en secondes
    $ref_memory_limit=512*pow(1024, 2); // 512M, en octets
    $ref_post_max_size=16*pow(1024, 2); // 16M, en octets
    $ref_upload_max_filesize=16*pow(1024, 2); // 16M, en octets

    $conf_max_execution_time=ini_get("max_execution_time");
    $conf_max_input_time=ini_get("max_input_time");
    $conf_memory_limit=return_bytes(ini_get("memory_limit"));
    $conf_post_max_size=return_bytes(ini_get("post_max_size"));
    $conf_file_uploads=ini_get("file_uploads");
    $conf_upload_max_filesize=return_bytes(ini_get("upload_max_filesize"));
    $conf_sendmail_path=ini_get("sendmail_path");

    $txt_erreurs=$ref_max_execution_time<=$conf_max_execution_time ? "Valeur actuelle : $conf_max_execution_time secs" : "Valeur recommandée : 3600 secondes (actuelle : $conf_max_execution_time)";
    $icone=$ref_max_execution_time<=$conf_max_execution_time ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";

    print("<tr>
          <td class='fond_menu2' style='padding:4px;' colspan='4'>
            <font class='Texte'><strong>PHP : options php.ini</strong></font>
          </td>
        </tr>
        <tr>
          <td class='fond_menu2' style='padding:4px;' colspan='4'>
            <font class='Texte'>
              <strong>Attention : ces options doivent être paramétrées à la fois pour le serveur HTTP et php en ligne de commande (CLI) (fichiers de configuration distincts)
              <br>Seule le configuration HTTP est vérifiée ici.</strong>
            </font>
          </td>
        </tr>
        <tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>max_execution_time</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'></td>
          </tr>\n");

    $txt_erreurs=$ref_max_input_time<=$conf_max_input_time ? "Valeur actuelle : $conf_max_input_time secs" : "Valeur recommandée : 600 secondes (actuelle : $conf_max_input_time)";
    $icone=$ref_max_input_time<=$conf_max_input_time ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>max_input_time</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'></td>
          </tr>\n");
    
    $txt_erreurs=$ref_memory_limit<=$conf_memory_limit ? $conf_memory_limit/pow(1024,2)."M" : "Valeur recommandée : 512M (actuelle : ".$conf_memory_limit/pow(1024,2)."M)";
    $icone=$ref_memory_limit<=$conf_memory_limit ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>post_max_size</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'></td>
          </tr>\n");

    $txt_erreurs=$ref_post_max_size<=$conf_post_max_size ? $conf_post_max_size/pow(1024,2)."M" : "Valeur recommandée : 16M (actuelle : ".$conf_post_max_size/pow(1024,2)."M)";
    $icone=$ref_post_max_size<=$conf_post_max_size ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>post_max_size</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'></td>
          </tr>\n");

    $txt_erreurs=$conf_file_uploads=="1" ? "" : "Autoriser l'envoi de fichier (\"file_uploads = On\")";
    $icone=$conf_file_uploads=="1" ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>file_uploads</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'></td>
          </tr>\n");

    $txt_erreurs=$ref_upload_max_filesize<=$conf_upload_max_filesize ? $conf_upload_max_filesize/pow(1024,2)."M" : "Valeur recommandée : 16M (actuelle : ".$conf_upload_max_filesize/pow(1024,2)."M)";
    $icone=$ref_upload_max_filesize<=$conf_upload_max_filesize ? "<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>" : "<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>";

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>upload_max_filesize</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>$icone</td>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'>$txt_erreurs</font></td>
          <td class='fond_menu' style='padding:4px;'></td>
          </tr>\n");
    /*
      TODO / Question :
      - Utilisateur "administrateur" : vérifier le changement du mot de passe par défaut ?
    */

    // Accès à la base de données
    // Possible uniquement si les fonctions postgres sont disponibles

    print("<tr>
          <td class='fond_menu2' style='padding:4px;' colspan='4'>
            <font class='Texte'><strong>Application</strong></font>
          </td>
        </tr>
        <tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>Accès à la base de données</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>\n");

    if($fonctions_postgresql)
    {
      if(!@db_test())
        print("<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>
            </td>
            <td class='fond_menu' style='padding:4px;' colspan='2'>
              <font class='Texte'><strong>Vérifier que :</strong>
                <br>- les paramètres du fichier de configuration (configuration/aria_config.php) sont corrects,
                <br>- le serveur de bases de données est en cours d'exécution aux paramètres (droits d'accès, SSL, etc)
                <br>- la configuration de la base de données correspond,
                <br>- la base de données a bien été créée,
                <br>- le schéma de la base a bien été défini (fichier aria_schema.sql),
                <br>- l'utilisateur défini dans le fichier de configuration a accès à la base de données.
              </font>
            </td>
          </tr>\n");
      else
        print("<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>
            </td>
            <td class='fond_menu' style='padding:4px;'></td>
            <td class='fond_menu' style='padding:4px;'></td>
          </tr>\n");
    }
    else
      print("<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>
            </td>
            <td class='fond_menu' style='padding:4px;'>
              <font class='Texte'>Test impossible : extension \"PostgreSQL\" absente</font>
            </td>
            <td class='fond_menu' style='padding:4px;'></td>
          </tr>\n");

      // Présence du fichier de configuration gestion/admin/config.php (bloquante)
      print("<tr>
               <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>Fichier config.php</strong></font></td>
               <td class='fond_menu' style='padding:4px; text-align:center'>\n");

      if(is_file("$GLOBALS[__ROOT_DIR]/$GLOBALS[__GESTION_DIR]/admin/config.php"))
      {
         $txt_erreurs="Présent<br>";
         $commentaire="Une fois la configuration terminée, vous devez <b>supprimer le fichier gestion/admin/config.php</b><br>pour débloquer l'accès à l'application.";
         print("<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>\n");
      }
      else
      {
         $txt_erreurs="Absent (ok)";
         $commentaire="";
         print("<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>\n");
      }

      print("</td>
             <td class='fond_menu' style='padding:4px;'>
               <font class='Texte'>
                  $txt_erreurs
               </font>
             </td>
             <td class='fond_menu' style='padding:4px;'><font class='Texte'>$commentaire</font></td>
           </tr>\n");
    
    // Arborescence

    // Création du tableau des répertoires à vérifier, avec les droits (Lecture, Ecriture)
    $all_dirs=array($GLOBALS["__ROOT_DIR"] => "L",
               $GLOBALS["__MOD_DIR_ABS"] => "L",
               $GLOBALS["__INCLUDE_DIR_ABS"]=> "L",
               $GLOBALS["__FPDF_DIR_ABS"] => "L",
               $GLOBALS["__STATIC_DIR_ABS"] => "L",
               $GLOBALS["__IMG_DIR_ABS"] => "L",
               $GLOBALS["__ICON_DIR_ABS"] => "L",
               "$GLOBALS[__ROOT_DIR]/$GLOBALS[__DOC_DIR]" => "L",
               "$GLOBALS[__ROOT_DIR]/$GLOBALS[__CAND_DIR]" => "L",
               "$GLOBALS[__ROOT_DIR]/$GLOBALS[__GESTION_DIR]" => "L",
               "$GLOBALS[__ROOT_DIR]/$GLOBALS[__GESTION_AIDE_DIR]" => "L",
               "$GLOBALS[__ROOT_DIR]/$GLOBALS[__CAND_MSG_DIR]" => "L",
               "$GLOBALS[__ROOT_DIR]/$GLOBALS[__GESTION_MSG_DIR]" => "L",
               $GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"] => "LE",
               $GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"] => "LE",
                      "$GLOBALS[__MOD_DIR_ABS]/configuration" => "LE");

    // Pour les répertoires suivants, on doit également ajouter chaque sous-répertoire (dédiés aux composantes)
    $subdir_array=array($GLOBALS["__PUBLIC_DIR_ABS"],
                  $GLOBALS["__CAND_COMP_STOCKAGE_DIR_ABS"],
                  $GLOBALS["__GESTION_COMP_STOCKAGE_DIR_ABS"]);

    foreach($subdir_array as $subdir)
    {
      $all_dirs["$subdir"]="LE";

      if(is_readable($subdir))
      {
        $contenu_repertoire  = scandir("$subdir", 0);

        // On ne considère que les sous-répertoires : suppression des fichiers de l'index: ".svn" "." et ".."
        foreach($contenu_repertoire as $key => $dir)
        {
          if($dir!="." && $dir!=".." && $dir!=".svn" && is_dir("$subdir/$dir"))
            $all_dirs["$subdir/$dir"]="LE";
        }
      }
    }

    // Affichage en deux étapes :
    // 1/ Présence des répertoires
    // 2/ Droits lecture / écriture dans les répertoires adéquats

    $dir_erreurs_presence="";
    $dir_erreurs_droits="";
    $details_droits="";

    foreach($all_dirs as $repertoire => $verif_droits)
    {
      $dir_erreurs_presence.=is_dir("$repertoire") ? "" : "<li>".preg_replace("/[\/]+/", "/", $repertoire)." manquant</li>\n";

      if(is_dir("$repertoire"))
      {
        $droits="";

        if(strstr($verif_droits, "L"))
          $droits.=is_readable("$repertoire") ? "" : "L";

        if(strstr($verif_droits, "E") && !is_writable("$repertoire"))
          $droits.=$droits!="" ? "+E)" : "E)";

        if($droits!="")
        {
          $droits="(" . $droits;
          $dir_erreurs_droits.="<li>".preg_replace("/[\/]+/", "/", $repertoire)." <strong>$droits</strong></li>\n";

          if($fonctions_posix)
          {
            $array_pw=posix_getpwuid(fileowner($repertoire));
            $array_gr=posix_getgrgid(filegroup($repertoire));

            if(is_array($array_pw) && is_array($array_gr))
            {
              $details_droits.="<br><strong>".preg_replace("/[\/]+/","/", $repertoire)." :</strong>
                          <br>- <u>Répertoire</u> : Propriétaire : $array_pw[name] - Groupe : $array_gr[name]
                          <br>- <u>Serveur HTTP</u> - Utilisateur : $current_process_username - Groupe : $current_process_group_name
                          <br>";
            }
          }
        }
      }
      else
        $dir_erreurs_droits.="<li><i>".preg_replace("/[\/]+/", "/", $repertoire)." manquant</i><li>\n";
    }

    print("<tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>Arborescence</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>\n");

    if($dir_erreurs_presence!="")
    {
      $txt_erreurs="Répertoires manquants ou inaccessibles :<br>";
      print("<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>\n");
    }
    else
    {
      $txt_erreurs="";
      print("<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>\n");
    }

    print("</td>
         <td class='fond_menu' style='padding:4px;'>
               <font class='Texte'>
            $txt_erreurs
            <ul style='list-style-type:none; padding-left:0px;'>
              $dir_erreurs_presence
            </ul>
          </font>
         </td>
         <td class='fond_menu' style='padding:4px;'></td>
        </tr>
        <tr>
          <td class='fond_menu' style='padding:4px;'><font class='Texte'><strong>Droits lecture/écriture</strong></font></td>
          <td class='fond_menu' style='padding:4px; text-align:center'>\n");

    if($dir_erreurs_droits!="")
    {
      $txt_erreurs="<strong>Droits manquants (<u>L</u>ecture/<u>E</u>criture) :</strong>";
      print("<img src='$__ICON_DIR/stop_22x22.png' border='0' desc='Echec' title='Echec'>\n");
    }
    else
    {
      $txt_erreurs="";      
      print("<img src='$__ICON_DIR/button_ok_22x22.png' border='0' desc='Succès' title='Succès'>\n");
    }

    print("</td>
         <td class='fond_menu' style='padding:4px;'>
          <font class='Texte'>
            $txt_erreurs
            <ul style='list-style-type:none; padding-left:0px;'>
              $dir_erreurs_droits
            </ul>
          </font>
         </td>
         <td class='fond_menu' style='padding:4px;'>\n");

    if(isset($details_droits) && $details_droits!="")
      print("<font class='Texte'>$details_droits</font>");
    
    print("</td>
        </tr>");
    

/*    
|-- aria_doc            
|   `-- fpdf            
|-- configuration       
|-- doc                 
|-- fichiers            
|   `-- composantes     
|-- gestion             
|   |-- admin           
|   |   |-- commissions 
|   |   |-- comp_infos
|   |   |   `-- include
|   |   |-- dossiers
|   |   |   `-- include
|   |   |-- editeur
|   |   |   `-- include
|   |   |-- filtres
|   |   |-- justificatifs
|   |   |   `-- include
|   |   `-- sessions
|   |-- aide
|   |   `-- admin
|   |       |-- commissions
|   |       |-- comp_infos
|   |       |-- dossiers
|   |       |-- editeur
|   |       |-- justificatifs
|   |       `-- sessions
|   |-- fichiers
|   |   |-- composantes
|   |   |   `-- 101
|   |   `-- messagerie
|   |-- lettres
|   |   `-- include
|   `-- messagerie
|       `-- modeles
|           `-- include
|-- images
|   `-- icones
|-- include
|   `-- fpdf
|       |-- doc
|       |-- font
|       |   `-- makefont
|       `-- tutoriel
|-- interface
|   |-- fichiers
|   |   |-- composantes
|   |   `-- messagerie
|   `-- messagerie
|-- licences
|-- scripts
`-- static
*/
    print("</table>\n");
  ?>
</div>
<?php
  // pied_de_page();
?>
</body>
</html>

