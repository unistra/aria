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

   error_reporting(0);
/*
   include "../../configuration/aria_config.php";
   include "$__INCLUDE_DIR_ABS/vars.php";
   include "$__INCLUDE_DIR_ABS/fonctions.php";
   include "$__INCLUDE_DIR_ABS/db.php";
*/
   // $config_file=fopen("../../configuration/aria_config.php", "w+b");

   if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
   {
      if(array_key_exists("db_host", $_POST) && !empty($_POST["db_host"])) 
         $db_host=trim($_POST["db_host"]);
      else
         $host_error=1;

      if(array_key_exists("db_port", $_POST) && !empty($_POST["db_port"]))
         $db_port=trim($_POST["db_port"]);
      else
         $port_error=1;

      if(array_key_exists("db_user", $_POST) && !empty($_POST["db_user"]))
         $db_user=trim($_POST["db_user"]);
      else
         $user_error=1;

      if(array_key_exists("db_pass", $_POST) && !empty($_POST["db_pass"]))
         $db_pass=trim($_POST["db_pass"]);
      elseif(isset($_SESSION["db_pass"]))
         $db_pass=$_SESSION["db_pass"];
      else
         $pass_error=1;

      if(array_key_exists("db_base", $_POST) && !empty($_POST["db_base"]))
         $db_base=trim($_POST["db_base"]);
      else
         $base_error=1;

      if(array_key_exists("db_ssl", $_POST) && !empty($_POST["db_ssl"]))
         $db_ssl=$_POST["db_ssl"];
      else
         $db_ssl="";

      if(array_key_exists("rootdir", $_POST) && !empty($_POST["rootdir"]))
      {
         $rootdir=preg_replace("/\/+/", "/", trim($_POST["rootdir"]));

         if(!is_dir("$rootdir"))
            $rootdir_exist=1;
      }
      else
         $rootdir_error=1;

      if(array_key_exists("appdir", $_POST) && !empty($_POST["appdir"]))
      {
         $appdir=preg_replace("/\/+/", "/", trim($_POST["appdir"]));

         if(!isset($rootdir_error) && !isset($rootdir_exist) && !is_dir("$rootdir/$appdir"))
            $appdir_exist=1;
      }
      else
         $appdir_error=1;

      if(!isset($host_error) && !isset($port_error) && !isset($user_error) && !isset($pass_error) && !isset($base_error) && !isset($rootdir_error)
         && !isset($appdir_error) && !isset($rootdir_exist) && !isset($appdir_exist))
      {
         include "../../include/db.php";

         $dbr=pg_connect("host=$db_host port=$db_port dbname=$db_base user=$db_user password=$db_pass sslmode=$db_ssl");

         if($dbr===FALSE)
         {
            $erreur_connexion="1";
            $error_msg=pg_last_error($dbr);
         }
         else
         {
            pg_close($dbr);

            $db_succes=1;        

            // Ecriture du fichier de configuration
            $config_file=fopen("../../configuration/aria_config.php", "w+b");

            if($config_file===FALSE)
               $erreur_fichier=1;
            else
            {
               $file_str="<?php
// ARIA - Configuration générée par le script \"gestion/admin/config.php\"\n
// Paramètres de connexion à la base de données PostgreSQL

// Adresse du serveur
\$__DB_HOST = \"$db_host\";

// Port
\$__DB_PORT = \"$db_port\";

// Nom de la base
\$__DB_BASE = \"$db_base\";

// Utilisation du chiffrement SSL
\$__DB_SSLMODE = \"$db_ssl\";

// Utilisateur
\$__DB_USER = \"$db_user\";

// Mot de passe
\$__DB_PASS = \"".quotemeta($db_pass)."\";

// Répertoires de l'application
// le reste de la configuration est construite à partir des deux paramètres suivants

// Racine du serveur HTTP (i.e DOCUMENT_ROOT)
\$__ROOT_DIR = \"$rootdir\";

// Répertoire contenant l'application, relativement à rootdir
\$__MOD_DIR = \"$appdir\";

// Répertoire contenant les fichiers includes (absolu)
\$__INCLUDE_DIR_ABS= \"\$__ROOT_DIR/\$__MOD_DIR/include\";

?>";

              fwrite($config_file, $file_str);
              fclose($config_file);

              chmod("../../configuration/aria_config.php", 0600);

              $succes=1;
            }
         }
      }
   }
   elseif(is_file("../../configuration/aria_config.php") && is_readable("../../configuration/aria_config.php")
        || is_file("../../configuration/config.php") && is_readable("../../configuration/config.php")) // Lecture du fichier s'il existe (sinon, on le créera plus tard)
   {
      if(is_file("../../configuration/aria_config.php") && is_readable("../../configuration/aria_config.php"))
         include "../../configuration/aria_config.php";
      else
      {
         $config_not_found=1;
         $old_loaded=1;
         include "../../configuration/config.php";
      }

      // Vérification des variables
      if(isset($__DB_HOST) && trim($__DB_HOST)!="")
         $file_db_host=$__DB_HOST;
      else
         $host_error=1;

      if(isset($__DB_PORT) && trim($__DB_PORT)!="")
         $file_db_port=$__DB_PORT;
      else
         $port_error=1;

      if(isset($__DB_USER) && trim($__DB_USER)!="")
         $file_db_user=$__DB_USER;
      else
         $user_error=1;

      if(isset($__DB_PASS) && trim($__DB_PASS)!="")
         $file_db_pass=$__DB_PASS;
      else
         $pass_error=1;

      if(isset($__DB_BASE) && trim($__DB_BASE)!="")
         $file_db_base=$__DB_BASE;
      else
         $base_error=1;

      if(isset($__DB_SSLMODE) && str_ireplace("sslmode=","",trim($__DB_SSLMODE))!="")
         $file_db_ssl=str_ireplace("sslmode=","", trim($__DB_SSLMODE));
      else
         $file_db_ssl="";

      if(isset($__ROOT_DIR) && trim($__ROOT_DIR)!="")
         $file_rootdir=$__ROOT_DIR;
      else
         $rootdir_error=1;

      if(isset($__MOD_DIR) && trim($__MOD_DIR)!="")
         $file_appdir=$__MOD_DIR;
      else
         $appdir_error=1;

      if(is_file("../../configuration/aria_config.php") && is_readable("../../configuration/aria_config.php") && !isset($host_error) && !isset($port_error)
         && !isset($user_error) && !isset($pass_error) && !isset($base_error) && !isset($rootdir_error) && !isset($appdir_error))
         $config_succes=1;
   }
   else
      $config_not_found=1;


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head><title>ARIA - Gestion des précandidatures - Configuration</title>

<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Pragma' content='no-cache'>
<link rel='stylesheet' type='text/css' href='../../static/typo.css'></head>

<body class='main' leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' vlink='black' alink='black' link='black'>

<table border='0' cellpadding='0' cellspacing='0' width='100%' align='center'>
<tr>
   <td height='150' align='center'>
      <font class='TitrePage2'>
         <strong>ARIA - Gestion des précandidatures
         <br>Configuration initiale</strong>
      </font>
   </td>
</tr>
</table>

<div class='menu_haut_central' style='background-image:url(../../images/fond_menu_haut.jpg);'></div>

<div class='main'>
   <?php
      print("<form action='$_SERVER[PHP_SELF]' method='POST' name='form1'>\n");

      if(isset($succes) || isset($db_succes) || isset($config_succes) || isset($config_not_found) || isset($host_error) || isset($port_error)
      || isset($user_error) || isset($pass_error) || isset($base_error) || isset($erreur_connexion) || isset($erreur_fichier)  || isset($rootdir_error)
      || isset($appdir_error) || isset($rootdir_exist) || isset($appdir_exist))
      {
         print("<table cellpadding='0' border='0' align='center' style='padding-top:20px;'>\n");

         if(isset($config_not_found) || isset($host_error) || isset($port_error) || isset($user_error) || isset($pass_error) || isset($base_error)
         || isset($erreur_connexion) || isset($erreur_fichier) || isset($rootdir_error) || isset($appdir_error) || isset($rootdir_exist) || isset($appdir_exist))
            $erreur=1;

         if(isset($succes) || isset($db_succes) || isset($config_succes))
         {
            $message="<strong>Configuration</strong> :<br>";
            $message.=isset($config_succes) ? "- fichier de configuration \"configuration/aria_config.php\" trouvé, vérifiez les paramètres chargés.<br>- la validation du formulaire les testera et les (ré)enregistrera.<br>" : "";
            $message.=isset($db_succes) ? "- connexion à la base de données réussie<br>" : "";
            $message.=isset($succes) ? "- fichier de configuration \"configuration/aria_config.php\" enregistré<br>- <strong>vous devez maintenant supprimer le fichier \"". preg_replace("/\/+/", "/", "$rootdir/$appdir/gestion/admin/config.php") . "\"pour pouvoir utiliser l'interface</strong><br>- les paramètres peuvent à tout moment être modifiés dans le fichier <strong>\"configuration/aria_config.php\"</strong>" : "";

            if(!isset($erreur))
               print("<tr>
                        <td align='left' valign='middle'>
                           <font class='Textebleu'>$message</font>
                           
                           <div style='text-align:center; padding-top:10px;'>
                              <a href='../login.php' target='_self' class='lien_bleu_12'><img class='icone' src='../../images/icones/forward_32x32_fond.png' alt='Retour' border='0'></a>
                           </div>
                        </td>
                     </tr>");
         }

         if(isset($config_not_found) || isset($host_error) || isset($port_error) || isset($user_error) || isset($pass_error) || isset($base_error)
         || isset($erreur_connexion) || isset($erreur_fichier) || isset($rootdir_error) || isset($appdir_error) || isset($rootdir_exist) || isset($appdir_exist))
         {
            $message="<strong>Erreur(s) ou avertissement(s)</strong> :<br>";
            $message.=isset($config_not_found) ? "- fichier \"configuration/aria_config.php\" non trouvé : complétez le formulaire puis validez pour le créer.<br>" : "";
            $message.=isset($old_loaded) ? "- le formulaire a été complété à l'aide de l'ancien fichier de configuration \"configuration/config.php\".<br>" : "";
            $message.=isset($host_error) ? "- adresse du serveur PostgreSQL manquante ou incorrecte<br>" : "";
            $message.=isset($port_error) ? "- port du serveur PostgreSQL manquant ou incorrect<br>" : "";
            $message.=isset($base_error) ? "- nom de la base de donnée manquant ou incorrect<br>" : "";
            $message.=isset($user_error) ? "- identifiant de l'utilisateur manquant ou incorrect<br>" : "";
            $message.=isset($pass_error) ? "- mot de passe manquant ou incorrect<br>" : "";
            $message.=isset($rootdir_error) ? "- répertoire racine manquant ou incorrect<br>" : "";
            $message.=isset($rootdir_exist) ? "- le répertoire racine <strong>\"$rootdir\"</strong> n'existe pas (répertoire relatif <strong>\"$appdir\"</strong> non testé)<br>" : "";
            $message.=isset($appdir_error) ? "- répertoire de l'application manquant ou incorrect<br>" : "";
            $message.=isset($appdir_exist) ? "- le répertoire de l'application <strong>\"" . preg_replace("/\/+/", "/", "$rootdir/$appdir") ."\"</strong> n'existe pas<br>" : "";

            $message.=isset($erreur_connexion) ? "- echec de la connexion à la base de données : veuillez vérifier les paramètres et le bon fonctionnement du serveur PostgreSQL<br>" : "";

            if(isset($erreur_connexion) && isset($error_msg) && $error_msg!="")
               $message.="- Détails : $error_msg<br>";

            $message.=isset($erreur_fichier) ? "- impossible d'écrire le fichier de configuration /configuration/aria_config.php : veuillez vérifier les droits d'accès et d'écriture du répertoire<br>" : "";

            print("<tr>
                     <td align='left' valign='middle'>
                        <font class='Texte_important'>$message</font>
                     </td>
                  </tr>\n");
         }

         print("</table>\n");
      }
   ?>

   <table style='margin-left:auto; margin-right:auto; padding-top:20px;'>
   <tr>
      <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Accès à la base de données</strong></font>
      </td>
   </tr>
   <tr>
      <td class='fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Adresse IP ou nom du serveur PostgreSQL :</strong></font>
      </td>
      <td class='fond_menu' style='padding:4px;'>
         <?php
            if(isset($db_host))
               $cur_db_host=$db_host;
            elseif(isset($file_db_host))
               $cur_db_host=$file_db_host;
            else // défaut
               $cur_db_host="localhost";
         ?>
         <font class='Texte_menu'><input type='text' name='db_host' value='<?php echo htmlspecialchars(stripslashes($cur_db_host), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'></font>
      </td>
   </tr>
   <tr>
      <td class='fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Port du serveur PostgreSQL :</strong></font>
      </td>
      <td class='fond_menu' style='padding:4px;'>
         <?php
            if(isset($db_port))
               $cur_db_port=$db_port;
            elseif(isset($file_db_port))
               $cur_db_port=$file_db_port;
            else // défaut
               $cur_db_port="5432";
         ?>
         <font class='Texte_menu'><input type='text' name='db_port' value='<?php echo htmlspecialchars(stripslashes($cur_db_port), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'></font>
      </td>
   </tr>
   <tr>
      <td class='fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Utilisation du chiffrement SSL :</strong></font>
      </td>
      <td class='fond_menu' style='padding:4px;'>
         <?php
            if(isset($db_ssl))
               $cur_db_ssl=$db_ssl;
            elseif(isset($file_db_ssl))
               $cur_db_ssl=$file_db_ssl;
            else // défaut
               $cur_db_ssl="";
         ?>
         <select name='db_ssl'>
            <option value='disable' <?php if(strtolower($cur_db_ssl)=="disable") echo "selected='1'"; ?>>Désactiver</option>
            <option value='allow' <?php if(strtolower($cur_db_ssl)=="allow") echo "selected='1'"; ?>>Essayer sans SSL, puis avec en cas d'échec</option>
            <option value='prefer' <?php if(strtolower($cur_db_ssl)=="prefer") echo "selected='1'"; ?>>Essayer avec SSL, puis sans en cas d'échec</option>
            <option value='require' <?php if(strtolower($cur_db_ssl)=="require") echo "selected='1'"; ?>>Obligatoire</option>
         </select>
      </td>
   </tr>
   <tr>
      <td class='fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Nom de la base de données :</strong></font>
      </td>
      <td class='fond_menu' style='padding:4px;'>
         <?php
            if(isset($db_base))
               $cur_db_base=$db_base;
            elseif(isset($file_db_base))
               $cur_db_base=$file_db_base;
            else // défaut
               $cur_db_base="aria";
         ?>
         <font class='Texte_menu'><input type='text' name='db_base' value='<?php echo htmlspecialchars(stripslashes($cur_db_base), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'></font>
      </td>
   </tr>
   <tr>
      <td class='fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Identifiant de l'utilisateur de la base :</strong></font>
      </td>
      <td class='fond_menu' style='padding:4px;'>
         <?php
            if(isset($db_user))
               $cur_db_user=$db_user;
            elseif(isset($file_db_user))
               $cur_db_user=$file_db_user;
            else // défaut
               $cur_db_user="login";
         ?>
         <font class='Texte_menu'><input type='text' name='db_user' value='<?php echo htmlspecialchars(stripslashes($cur_db_user), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='60' maxlength='128'></font>
      </td>
   </tr>
   <tr>
      <td class='fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Mot de passe de cet utilisateur :</strong></font>
      </td>
      <td class='fond_menu' style='padding:4px;'>
         <?php
            if(isset($db_pass))
               $_SESSION["db_pass"]=$db_pass;
            elseif(isset($file_db_pass))
               $_SESSION["db_pass"]=$file_db_pass;
         ?>
         <font class='Texte_menu'><input type='password' name='db_pass' value='' size='60' maxlength='128'></font>
      </td>
   </tr>
   <tr>
      <td colspan='2' style='height:10px;'></td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' colspan='2' style='padding:4px; white-space:normal;'>
         <font class='Texte_menu2'>
            <strong>Installation de l'application</strong>
            <br>Les répertoires suivants sont primordiaux pour la configuration de l'interface.
            <br>Le reste de l'arborescence sera déduit de ces deux variables, il est donc <u>fortement déconseillé</u> de modifier la structure de l'application.
         </font>
      </td>
   </tr>
    <tr>
      <td class='fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Répertoire racine du serveur HTTP :</strong></font>
      </td>
      <td class='fond_menu' style='padding:4px;'>
         <?php
            if(isset($rootdir) && !empty($rootdir))
               $cur_rootdir=$rootdir;
            elseif(isset($file_rootdir) && !empty($file_rootdir))
               $cur_rootdir=$file_rootdir;
            else // défaut
            {
               $cur_rootdir=$_SERVER["DOCUMENT_ROOT"];
               $root_auto=1;
            }
         ?>
         <font class='Texte_menu'>
            <input type='text' name='rootdir' value='<?php echo htmlspecialchars(stripslashes($cur_rootdir), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='40' maxlength='128'>
            <?php
               if(isset($root_auto))
                  echo "<strong>(autodétecté)</strong>";
            ?>
         </font>
      </td>
   </tr>
   <tr>
      <td class='fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Répertoire contenant l'application (relativement au répertoire racine) :</strong></font>
      </td>
      <td class='fond_menu' style='padding:4px;'>
         <?php
            if(isset($appdir) && !empty($appdir))
               $cur_appdir=$appdir;
            elseif(isset($file_appdir) && !empty($file_appdir))
               $cur_appdir=$file_appdir;
            else // défaut : autodétection
            {
               $cur_appdir=str_replace("/gestion/admin/config.php", "", $_SERVER["PHP_SELF"]);
               $app_auto=1;
            }
         ?>
         <font class='Texte_menu'>
            <input type='text' name='appdir' value='<?php echo htmlspecialchars(stripslashes($cur_appdir), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='40' maxlength='128'>
            <?php
               if(isset($app_auto))
                  echo "<strong>(autodétecté)</strong>";
            ?>
         </font>
      </td>
   </tr>
<!--
   <tr>
      <td colspan='2' style='height:10px;'></td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Autres paramètres</strong></font>
      </td>
   </tr>
-->
   </table>

   <div class='centered_icons_box'>
      <input type="image" class='icone' src="<?php echo "../../images/icones/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
   </div>

   </form>
</div>

<div class='footer' style='background-image:url(../../images/fond_menu_haut.jpg);'></div>

</body>
</html>
