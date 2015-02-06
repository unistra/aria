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
// Fonctions de l'application ARIA (hors base de données)

// Construction des URLs pour la fonction header()
// l'argument est le fichier ($_SERVER["php_self"]) à partir duquel est appelée la fonction
function base_url($current_location)
{
   // $__host=$_SERVER["HTTP_HOST"];
   // $__uri=rtrim(dirname($_SERVER["PHP_SELF"]), '/\\');
   $__BASE_URL="https://" . $_SERVER["HTTP_HOST"] . rtrim(dirname($current_location), '/\\') . "/";

   return $__BASE_URL;
}

// Suppression complète d'un répertoire et de ses sous-répertoires
// fonction récursive à utiliser AVEC UNE EXTREME PRUDENCE
// Source : php.net

function deltree($repertoire)
{
  if(is_dir($repertoire))
  {
    foreach(glob($repertoire.'/*') as $object) // on liste tout ce qui se trouve dans le répertoire
    {
      if(is_dir($object) && !is_link($object))   // On vérifie que "$object" n'est pas un lien symbolique
        deltree($object);   // si objet est un répertoire, on appelle récursivement la fonction
      else
        unlink($object);   // si c'est un fichier, on le supprime
    }

    rmdir($repertoire);      // Une fois le contenu de notre répertoire supprimé, on peut supprimer le répertoire de base
  }
  else
    die("Erreur de suppression : \"$repertoire\" n'est pas un répertoire.");
}


// Création  d'un identifiant unique basé sur une date lisible
// ATTENTION : retourne une chaine de caractères qui devra être interpretée comme un BigInt dans Postgresql (entier 64bits)

function new_id()
{
   // Argument optionnel : date UNIX à partir de laquelle on construit l'identifiant)
   if(func_num_args())
      $time=func_get_arg(0);
   else
      $time=time();

   // Format : année mois jour heures minutes secondes microsecondes
   // Le paramètre TRUE de microtime() indique que la fonction retourne un nombre à virgule (utile pour le découpage avec strstr)


   $new_id=date("ymdHis",$time) . substr(current(explode(" ", microtime())) . "00000", 2, 5);

   // On enlève les 0 à gauche une bonne fois pour toutes (postgresql le fait par défaut lors de l'insertion)
   return ltrim($new_id, "0");
}

// Fonctions de tri personnalisées (pour la fonction usort)

function cmp_lieu($a, $b)
{
   $cnt_a=count($a['cursus']);
   $cnt_b=count($b['cursus']);
   if($cnt_a && $cnt_b)
      return strcasecmp($a['cursus'][0]['ville'],$b['cursus'][0]['ville']);
   else
   {
      if($cnt_a<$cnt_b)
         return -1;
      else
      {
         if($cnt_a>$cnt_b)
            return 1;
         else
            return 0;
      }
   }
}

// Tri par diplôme
function cmp_diplome($a, $b)
{
   $cnt_a=count($a['cursus']);
   $cnt_b=count($b['cursus']);
   if($cnt_a && $cnt_b)
   {
      $res=strcasecmp($a['cursus'][0]['diplome'],$b['cursus'][0]['diplome']);
      if(!$res)
         return strcmp($a['cursus'][0]['intitule'],$b['cursus'][0]['intitule']);
      else
         return $res;
   }
   else
   {
      if($cnt_a>$cnt_b)
         return 1;
      else
         return 0;
   }
}

// Fonction de tri de candidats en fonction de la moyenne
function cmp_moyenne_diplome($a, $b)
{
   $note_a=str_replace(" ", "", $a['moyenne']);
   $note_b=str_replace(" ", "", $b['moyenne']);

   $note_a=str_replace("/20", "", $note_a);
   $note_b=str_replace("/20", "", $note_b);

   $note_a=str_replace(",", ".", $note_a);
   $note_b=str_replace(",", ".", $note_b);

   if(is_numeric($note_a) && is_numeric($note_b) && $note_a!="" && $note_b!="")
   {
      // ordre décroissant : si moyenne_a < moyenne_b : moyenne_b doit être devant (retourne 1)
      if($note_a<$note_b) return 1;
      else return 0;
   }
   elseif(is_numeric($note_a)) return 0;
   elseif(is_numeric($note_b)) return 1;
   elseif($note_a!="" && $note_b=="") return 0;
   elseif($note_b!="" && $note_a=="") return 1;

   // si les notes ne sont pas bien entrées, on compare sur la chaine de caractères
   else return strcasecmp($a['moyenne'],$b['moyenne']);
}

// Tri par rang sur liste complémentaire
function cmp_rangs_liste_complementaire($a, $b)
{
   if(!array_key_exists("rang_liste", $a) || $a["rang_liste"]=="0" || $a["rang_liste"]=="" || !ctype_digit($a["rang_liste"]))
      return 0;
   
   if(!array_key_exists("rang_liste", $b) || $b["rang_liste"]=="0" || $b["rang_liste"]=="" || !ctype_digit($b["rang_liste"]))
      return 1;

   if($a["rang_liste"]>$b["rang_liste"])
      return 1;
   else
      return 0;
}


// En fonction de l'ID d'une composante et d'une formation, vérification des droits d'accès et de gestion de l'utilisateur
// Si les formations ne sont pas précisées dans les droits de l'utilisateur, ce dernier a accès à toutes les formations de la composante
// Retourne 1 si les droits sont corrects, 0 sinon
function verif_droits_formations($composante_id, $formation_id)
{
   if(!isset($_SESSION["niveau"]))
      return 0;
   elseif($_SESSION["niveau"]==$GLOBALS["__LVL_ADMIN"]) // Administrateur : tous les droits
      return 1;
   elseif(isset($_SESSION["auth_droits"])) // Examen des restrictions d'accès
   {
      if(array_key_exists($composante_id, $_SESSION["auth_droits"])) // Accès à la composante
      {   
         if(in_array($_SESSION["niveau"], array("$GLOBALS[__LVL_SCOL_PLUS]", "$GLOBALS[__LVL_RESP]", "$GLOBALS[__LVL_SUPER_RESP]"))) // Niveau suffisant : accès à toutes les formations de cette composante
            return 1;
         // Si le tableau contient des formations, les droits sont restreints. Sinon, l'utilisateur a accès à toutes les formations de la composante.
         elseif(is_array($_SESSION["auth_droits"]["$composante_id"]) && count($_SESSION["auth_droits"]["$composante_id"])) // Restrictions sur des formations particulière
         {
            if(in_array($formation_id, $_SESSION["auth_droits"]["$composante_id"])) // La formation précisée en paramêtre a été trouvée : droits OK
               return 1;
            else
               return 0;
         }
         else   // Accès à la composante sans précision sur les formations : toutes les formations sont accessibles.
            return 1;
      }
      else // Pas d'accès à cette composante
         return 0;
   } // Pas de droits trouvés
   else
      return 0;
}

// En fonction de l'ID d'une composante, construction d'une portion de requête restreignant les droits d'un utilisateur sur les formations
// Si l'utilisateur a tous les droits, une chaine vide est renvoyée (pas de restriction)
// Attention à l'utilisation de la chaine renvoyée : elle doit bien s'intégrer dans la requête (table $_DB_propspec dans le FROM, etc)
function requete_auth_droits($composante_id)
{
   $requete_droits_formations="";
   
   if(isset($_SESSION["auth_droits"]) && isset($_SESSION["niveau"]))
   {
      if(array_key_exists($composante_id, $_SESSION["auth_droits"]))
      {
         // Si le niveau est inférieur à "SCOL_PLUS" et si le tableau contient des formations, les droits sont restreints. Sinon, l'utilisateur a accès à toutes les formations de la composante
         if(!in_array($_SESSION["niveau"], array("$GLOBALS[__LVL_SCOL_PLUS]", "$GLOBALS[__LVL_RESP]", "$GLOBALS[__LVL_SUPER_RESP]", "$GLOBALS[__LVL_ADMIN]"))
            && is_array($_SESSION["auth_droits"]["$composante_id"]) && count($_SESSION["auth_droits"]["$composante_id"]))
         {
            $requete_droits_formations="AND $GLOBALS[_DBC_propspec_id] IN (";
            
            foreach($_SESSION["auth_droits"]["$composante_id"] as $droits_propspec_id)
               $requete_droits_formations.="'$droits_propspec_id',";
               
            // Suppression de la dernière virgule et terminaison avec une parenthèse
            $requete_droits_formations=substr($requete_droits_formations, 0, -1) . ")";
         }
      }
   }
   else
      return -1;
      
   return $requete_droits_formations;
}


// Vérification de validité d'un numéro INE ou BEA
// On teste les deux algorithmes sur le numéro passé en paramètre, si l'un est bon, la vérification est positive.
// Retourne "0" en cas de réussite, "1" sinon
function check_ine_bea($numero)
{
   // La longueur du numéro doit être égale à 11 caractères
   if(strlen($numero)==11)
   {
      /* TEST INE */
      $univ=substr($numero, 0, 5);
      $serie=substr($numero, 5, 1);
      $ordre=substr($numero, 6, 4);
      $controle=substr($numero, 10, 1);

      $sum=0;

      for($c=0; $c<9; $c++)
         $sum+=6*(base_convert($numero[$c], 36, 10));

      $sum+=base_convert($numero[9], 36, 10);

      if(!strcasecmp(substr($sum, -1), $controle))
         $ine_ok=1;

      /* TEST BEA */
      // Le caractère de contrôle se trouve parmi la liste suivante.
      // Le rang dans la liste est déterminé par le reste modulo 23 des 10 premiers chiffres du matricule
      if(ctype_digit(substr($numero, 0, 10)))
      {
         $controle_array=array("0" => "a",
                               "1" => "b", 
                               "2" => "c", 
                               "3" => "d", 
                               "4" => "e", 
                               "5" => "f", 
                               "6" => "g", 
                               "7" => "h", 
                               "8" => "j", 
                               "9" => "k", 
                               "10" => "l",
                               "11" => "m", 
                               "12" => "n", 
                               "13" => "p", 
                               "14" => "r", 
                               "15" => "s", 
                               "16" => "t", 
                               "17" => "u", 
                               "18" => "v", 
                               "19" => "w", 
                               "20" => "x", 
                               "21" => "y", 
                               "22" => "z");

         $academie=substr($numero, 0, 2);
         $annee_immat=substr($numero, 2, 2);
         $ordre=substr($numero, 4, 6);
         $controle=substr($numero, 10, 1);

         $nombre=substr($numero, 0, 10);

         $reste=gmp_strval(gmp_mod(gmp_init($nombre, 10), gmp_init(23, 10)));

         if(array_key_exists("$reste", $controle_array) && !strcasecmp($controle_array["$reste"], $controle))
            $bea_ok=1;
      }

      if(!isset($bea_ok) && !isset($ine_ok))
         return 1;
      else
         return 0;
   }
   else
      return 1;
}


// Ecriture des événements dans la table "historique"
function write_evt()
{
   $numargs = func_num_args();

   if($numargs>=3)
   {
      // 3 arguments indispensables : connexion à la base, type d'évenement (evt_ID) et texte associé (évenement et requete)
/*
      if(func_get_arg(0)=="" || db_connection_status(func_get_arg(0))) // la connexion doit être (re)faite dans la fonction
      {
         $db=db_connect();
         $flag_dec=1;
      }
      else
         $db=func_get_arg(0);
*/
      $evt_id=func_get_arg(1);

      $evt_txt=func_get_arg(2);
      // $evt_txt=str_replace("'","''", $evt_txt);
      $evt_txt=preg_replace("/[']+/","''", stripslashes($evt_txt));
      
      // les 3 suivants sont optionnels : ID du candidat concerné, ID de l'élément modifié/ajouté/... et requête exécutée

      $hist_cand_id=$numargs<4 ? -1 : func_get_arg(3);

      if($hist_cand_id=="")
         $hist_cand_id="-1";

      if($hist_cand_id!=-1 && (isset($_SESSION["tab_candidat"]) || (isset($_SESSION["authentifie"]) && isset($_SESSION["prenom"]) && isset($_SESSION["nom"]) && isset($_SESSION["email"]))))
      {
         // Gestion : candidat actuel = id du candidat passé en paramètre ?
         if(isset($_SESSION["tab_candidat"]))
         {
            if(isset($_SESSION["candidat_id"]) && $hist_cand_id!=$_SESSION["candidat_id"])
            {
               $res_cand=db_query($GLOBALS["dbr"], "SELECT $GLOBALS[_DBC_candidat_nom], $GLOBALS[_DBC_candidat_prenom],
                                                $GLOBALS[_DBC_candidat_email]
                                         FROM $GLOBALS[_DB_candidat] WHERE $GLOBALS[_DBC_candidat_id]='$hist_cand_id'");

               if(db_num_rows($res_cand))
               {
                  list($cand_nom, $cand_prenom, $c_email)=db_fetch_row($res_cand, 0);
                  
                  // ereg obsolete
                  /*
                  $c_nom=ereg_replace("[']+","''", stripslashes($cand_nom));
                  $c_prenom=ereg_replace("[']+","''", stripslashes($cand_prenom));
                  */
                  $c_nom=preg_replace("/[']+/","''", stripslashes($cand_nom));
                  $c_prenom=preg_replace("/[']+/","''", stripslashes($cand_prenom));
               }
               else
                  $c_nom=$c_prenom=$c_email="";

               db_free_result($res_cand);
            }
            elseif(isset($_SESSION["candidat_id"]))
            {
               /*
               $c_nom=ereg_replace("[']+","''", stripslashes($_SESSION["tab_candidat"]["nom"]));
               $c_prenom=ereg_replace("[']+","''", stripslashes($_SESSION["tab_candidat"]["prenom"]));
               */
               $c_nom=preg_replace("/[']+/","''", stripslashes($_SESSION["tab_candidat"]["nom"]));
               $c_prenom=preg_replace("/[']+/","''", stripslashes($_SESSION["tab_candidat"]["prenom"]));               
               $c_email=$_SESSION["tab_candidat"]["email"];
            }
            else
               $c_nom=$c_prenom=$c_email="";
         }
         elseif(isset($_SESSION["nom"]) && isset($_SESSION["prenom"]) && isset($_SESSION["email"])) // Candidat
         {
            // $c_nom=str_replace("'","''", stripslashes($_SESSION["nom"]));
            // $c_prenom=str_replace("'","''", stripslashes($_SESSION["prenom"]));
            
            // $c_nom=ereg_replace("[']+","''", stripslashes($_SESSION["nom"])); // ereg_replace est obsolète
            // $c_prenom=ereg_replace("[']+","''", stripslashes($_SESSION["prenom"]));
            $c_nom=preg_replace("/[']+/","''", stripslashes($_SESSION["nom"]));
            $c_prenom=preg_replace("/[']+/","''", stripslashes($_SESSION["prenom"]));
            
            $c_email=$_SESSION["email"];
         }
         else
            $c_nom=$c_prenom=$c_email="";
      }
      else
         $c_nom=$c_prenom=$c_email="";

      $element_id=$numargs<5 ? 0 : func_get_arg(4);

      $requete=$numargs<6 ? "" : func_get_arg(5);

      $requete=str_replace("\t", "", str_replace("\r", "", str_replace("\n", "", preg_replace("/[']+/","''", stripslashes($requete)))));

      $date=new_id();

      if(isset($_SESSION["auth_ip"]))
         $auth_ip=$_SESSION["auth_ip"];
      elseif(isset($_SERVER['REMOTE_ADDR']))
         $auth_ip=$_SERVER['REMOTE_ADDR'];
      else
         $auth_ip="";

      if(isset($_SESSION["auth_host"]))
         $auth_host=$_SESSION["auth_host"];
      elseif(isset($_SERVER['REMOTE_ADDR']))
         $auth_host=&gethostbyaddr($_SERVER['REMOTE_ADDR']);
      else
         $auth_host="";

      $auth_id=isset($_SESSION["auth_id"]) ? $_SESSION["auth_id"] : 0;
      $g_nom=isset($_SESSION["auth_nom"]) ? preg_replace("/[']+/","''", stripslashes($_SESSION["auth_nom"])) : "";
      $g_prenom=isset($_SESSION["auth_prenom"]) ? preg_replace("/[']+/","''", stripslashes($_SESSION["auth_prenom"])) : "";
      $auth_email=isset($_SESSION["auth_email"]) ? $_SESSION["auth_email"] : "";
      $comp_id=(isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]!="") ? $_SESSION["comp_id"] : 0;
      $niveau=isset($_SESSION["niveau"]) ? $_SESSION["niveau"] : 0;

      db_query($GLOBALS["dbr"],"INSERT INTO $GLOBALS[_DB_hist] VALUES('$date','$auth_ip','$auth_host','$auth_id','$g_nom', '$g_prenom','$auth_email', '$comp_id','$niveau',
                                                 '$hist_cand_id','$c_nom','$c_prenom','$c_email', '$element_id','$evt_id','$evt_txt','$requete')");
   }
/*
   if(isset($flag_dec) && $flag_dec==1 && !db_connection_status($db))
      db_close($db);
*/
   return 0;
}


// Affichage d'un message avec une icone devant
// type=0 : erreur
// type=1 : succès
// type=2 : warning

function message($texte, $type)
{
   switch($type)
   {
      case $GLOBALS["__ERREUR"] :   $icone="messagebox_critical_32x32.png";
                                    $font="Texte_important";
                                    $dt_class="erreur";
                                    $message_class="Message_erreur_warning";
                                    break;

      case $GLOBALS["__SUCCES"] :   $icone="idea_32x32.png";
                                    $font="Textebleu";
                                    $dt_class="succes";
                                    $message_class="Message_info_succes";
                                    break;

      case $GLOBALS["__WARNING"] :  $icone="messagebox_warning_32x32.png";
                                    $font="Texte_important";
                                    $dt_class="warning";
                                    $message_class="Message_erreur_warning";
                                    break;

      case $GLOBALS["__QUESTION"] : $icone="help_32x32.png";
                                    $font="Textebleu";
                                    $dt_class="question";
                                    $message_class="Message_question";
                                    break;

      case $GLOBALS["__INFO"] :     $icone="messagebox_info_32x32.png";
                                    $font="Textebleu";
                                    $dt_class="info";
                                    $message_class="Message_info_succes";
                                    break;

      default :   $font="Texte";
                  $icone="";
                  $fond="";
                  $couleur_bord="";
   }

   // Note : l'alignement (gauche, centre, ...) est à contrôler avant l'appel de la fonction
   // TODO : remplacer le tableau par un <div>

   print("<STYLE type='text/css'>
            <!--
               dt.$dt_class { background:url($GLOBALS[__ICON_DIR]/$icone) no-repeat bottom; width:32px; height:32px; vertical-align:center; }
               *html dt.$dt_class { background-image:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$GLOBALS[__ICON_DIR]/$icone'); vertical-align:center; }
            -->
          </STYLE>
         <table cellpadding='4' border='0' align='center' style='border-collapse:collapse;'>
         <tr>
            <td align='center' width='40' nowrap='true' valign='middle' class='$message_class' style='border-width:1px 0px 1px 1px;'>
               <div style='text-align:center; vertical-align:center; margin-left:auto; margin-right:auto;'>
                  <dt class='$dt_class'></dt>
               </div>
            </td>
            <td align='left' valign='middle' class='$message_class' style='border-width:1px 1px 1px 0px;'>
               <font class='$font'>$texte</font>
            </td>
         </tr>
         </table>
         <br>\n");
}

// fonction d'échange de 2 valeurs

function switch_vals (&$val1, &$val2)
{
   $temp=$val1;
   $val1=$val2;
   $val2=$temp;
}

// vérification d'authentification, exécutée au début de chaque script du répertoire gestion/

function verif_auth()
{
   $numargs = func_num_args();
   if($numargs==1) // argument potentiel : script vers lequel on redirige en cas d'erreur
      $redirect=func_get_arg(0);
   else
      $redirect="login.php";

   if(!isset($_SESSION["auth_user"]) || !isset($_SESSION["auth_id"]))
   {
//      session_unset();
//      session_destroy();
      // $redirect="login.php";
      header("Location:$redirect");
      exit;
   }
}

// vérification du niveau des droits d'accès

function verif_niveau($dbr)
{
   if(!isset($_SESSION['auth_id']))
   {
      header("Location:$GLOBALS[__GESTION_DIR]/login.php");
      exit;
   }

   $res_verif=db_query($dbr, "SELECT $GLOBALS[_DBC_acces_niveau] FROM $GLOBALS[_DB_acces]
                              WHERE $GLOBALS[_DBC_acces_id]='$_SESSION[auth_id]'");
   if(db_num_rows($result))
      list($niveau)=db_fetch_row($res_verif, 0);
   else
   {
      db_free_result($res_verif);

      header("Location:$GLOBALS[__GESTION_DIR]/login.php");
      exit;
   }

   db_free_result($res_verif);

   return $niveau;
}

// Chiffrage des paramètres pour les variables type GET

function crypt_params($txt)
{
   if(!isset($GLOBALS["arg_key"]) || $GLOBALS["arg_key"]=="")
   {
      srand((double)microtime()*1000000);
      $_SESSION["config"]["arg_key"]=$GLOBALS["arg_key"]=substr(md5(rand(0,9999)), 12, 8);
   }

   // initialisation
   $td = mcrypt_module_open("tripledes", "", "cbc", "");
   // $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
   mcrypt_generic_init($td, $GLOBALS["arg_key"], $_SESSION["iv"]);
   
   // encryption
   $encrypted_data = mcrypt_generic($td, $txt);
   
   // nettoyage
   mcrypt_generic_deinit($td);

   // php < 4.3
   // mcrypt_generic_end($td); 
   mcrypt_module_close($td);
   
   $encrypted_data=bin2hex($encrypted_data);
   return $encrypted_data;
}

// récupération des paramètres chiffrés passés par la méthode GET

function get_params($txt)
{
   if(isset($_SESSION["iv"]))
   {
      $c = '';
      for ($i=0; $i <= strlen($txt)-2; $i = $i + 2)
      {
         $h = substr($txt, $i, 2);
         $c .= chr(hexdec($h));
      } 
      
      $td = mcrypt_module_open("tripledes", "", "cbc", "");
      // $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
      
      mcrypt_generic_init($td, $GLOBALS["arg_key"],$_SESSION["iv"]);
      
      $dec=trim(mdecrypt_generic($td,$c));

      mcrypt_generic_deinit($td);
      // php < 4.3
      // mcrypt_generic_end($td);
      mcrypt_module_close($td);
      
      $params=explode("&",$dec);
      $final_params=array();
      
      foreach($params as $val)
      {
         $key_val=explode("=",$val);

         if(array_key_exists(0, $key_val) && array_key_exists(1, $key_val))
         {
            $key=$key_val[0];
            $val=$key_val[1];

            $final_params[$key]=$val;
         }
         else
            return $final_params;
      }
         
      return $final_params;
   }
   else
      return -1;
}


// fonctions de vérification de validité de chaînes de caractères
function str_is_clean($str)
{
   // $clean=ereg("^[-.:_0-9A-Za-z,\n\r\'\!\?çûâîêéèàôäëïöü@ù\(\)\/=\" ]*$", $str);
   // return $clean;
   
   if(!preg_match("/^[-.:_0-9A-Za-z,\n\r\'\!\?çûâîêéèàôäëïöü@ù\(\)\/=\" ]*$/", $str))
      return FALSE;
   else
      return 1;   
}
/* Fonction obsolètes à supprimer

function str_is_clean2($str) // validité d'URL (à revoir)
{
   $clean=ereg("^[-.:_0-9A-Za-z,\n\r\'\!\?çûâîêéèàôäëïöü@ù\(\)\/=<>~ ]*$", $str); // < > doivent être autorisés pour les liens html ?
   
   if($clean)
   {
      $clean=!ereg("<\?", $str); // on interdit les tags php, au cas où
      $clean=!ereg("\?>", $str);
   }
   return $clean;
}

function str_is_clean3($str) // pour les noms, prénoms, ...
{
   $clean=ereg("^[-.0-9A-Za-z,\'çûâîêéèàùôäëïöü ]*$", $str);
   return $clean;
}

function str_is_clean4($str) // emails
{
   $clean=ereg("^[-._0-9A-Za-z@]*$", $str);

   return $clean;
}
*/

// Nettoyage d'une chaine de caractères : remplacement des caractères non conformes par une expression
// rationnelle pour chercher les caractères voisins
// caractères à traiter : * ( ) à á â ã ä å  ç  è é ê ë  ì í î ï  ñ  ð ò ó ô õ ö  ù ú û ü  ý ÿ
// Les parenthèses doivent être traitées AVANT

function clean_str_requete($str)
{
   $new_str=str_replace("(","\\\\(", $str);
   $new_str=str_replace(")","\\\\)", $new_str);
   $new_str=str_replace("]","\\\\]", $new_str);
   $new_str=str_replace("[","\\\\[", $new_str);

   $new_str=str_replace("*","\\\*", $new_str);
   $new_str=str_replace("+","\\\+", $new_str);
/*
   $new_str=preg_replace("/[aàáâãäå]/", "(a|à|á|â|ã|ä|å)", $new_str);
   $new_str=preg_replace("/[cç]/", "(c|ç)", $new_str);
   $new_str=preg_replace("/[eèéêë]/", "(e|è|é|ê|ë)", $new_str);
   $new_str=preg_replace("/[iìíîï]/", "(i|ì|í|î|ï)", $new_str);
   $new_str=preg_replace("/[nñ]/", "(n|ñ)", $new_str);
   $new_str=preg_replace("/[oðòóôõö]/", "(o|ð|ò|ó|ô|õ|ö)", $new_str);
   $new_str=preg_replace("/[uùúûü]/", "(u|ù|ú|û|ü)", $new_str);
   $new_str=preg_replace("/[yýÿ]/", "(y|ý|ÿ)", $new_str);
*/
   return $new_str;
}


// Même fonction mais en remplaçant chaque caractère de manière unique
function clean_str($str)
{
   $new_str=preg_replace("/[aàáâãäå]/", "a", $str);
   $new_str=preg_replace("/[cç]/", "c", $new_str);
   $new_str=preg_replace("/[eèéêë]/", "e", $new_str);
   $new_str=preg_replace("/[iìíîï]/", "i", $new_str);
   $new_str=preg_replace("/[nñ]/", "n", $new_str);
   $new_str=preg_replace("/[oðòóôõö]/", "o", $new_str);
   $new_str=preg_replace("/[uùúûü]/", "u", $new_str);
   $new_str=preg_replace("/[yýÿ]/", "y", $new_str);

   return $new_str;
}

// Conversion des caractères spéciaux MS WORD (source : php.net | "yes at king22 dot com" sur la fonction get_html_translation_table

function clean_word_str($str)
{
   return str_replace(array("&#8217;", "&#65292;", "&#61623;", "&#9658;", "&#8217;", "&#8211;", "&#8220;", "&#8221;"),
                      array("'",       ",",        "-",        "-",       "'",       "-",       "\"",      "\""),
                      $str);
}

// Transformation d'un identifiant en timestamp UNIX (pour l'affichage d'une date uniquement)

function id_to_date($identifiant)
{
   // Format de l'identifiant : A MM JJ HH MM SS MS (le tout sans espace, MS = microsecondes tronquées à 5 chiffres)
   if(strlen($identifiant)==16) // année sur un seul chiffre (le 0 du début est tronqué par PostgreSQL)
   {
      $annee_len=1;
      $shift=0; // Décallage pour substr
   }
   else
   {
      $annee_len=2;
      $shift=1;
   }

   $annee=substr($identifiant, 0, $annee_len);

   if($shift)
      $annee="20" . $annee;
   else
      $annee="200" . $annee;

   $mois=substr($identifiant, (1+$shift), 2);
   $jour=substr($identifiant, (3+$shift), 2);
   $heure=substr($identifiant, (5+$shift), 2);
   $minutes=substr($identifiant, (7+$shift), 2);
   $secondes=substr($identifiant, (9+$shift), 2);
   
   $timestamp=MakeTime($heure,$minutes,$secondes,$mois,$jour,$annee);

   return $timestamp;
}


// date en français (par Nob)

function date_fr() 
{
   switch (func_num_args())
   { 
      case 1:    $format=func_get_arg(0); 
               $strDate = date($format);
               break;
                  
      case 2:    $format=func_get_arg(0); 
               $timestamp=func_get_arg(1);   //variable intermédiaire autrement, on a une erreur... (?) 
               $strDate = date($format, $timestamp);
                 break;
                  
        default: return false; 
  } 
  
  /*CONVERSION*/ 
  //Format "F" 

   $mois_en = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"); 
   $mois_fr = array("janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"); 
   $strDate = str_replace ($mois_en, $mois_fr, $strDate); 
   
   //Format "M" (et "r") 
   
   $mois_en = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"); 
   $mois_fr = array("jan", "fév", "mar", "avr", "mai", "juin", "juil", "août", "sep", "oct", "nov", "déc"); 
   $strDate = str_replace ($mois_en, $mois_fr, $strDate); 
   
   //Format "l" 
   $jour_en = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday",
   "Friday", "Saturday"); 
   $jour_fr = array("dimanche", "lundi", "mardi", "mercredi", "jeudi",
   "vendredi", "samedi"); 
   $strDate = str_replace ($jour_en, $jour_fr, $strDate); 
   
   //Format "D" (et "r") 
   
   $jour_en = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"); 
   $jour_fr = array("dim", "lun", "mar", "mer", "jeu", "ven", "sam"); 
   $strDate = str_replace ($jour_en, $jour_fr, $strDate); 
   
   //Format "S" - st, th, nd et rd 
   //-On a besoin d'outils plus puissant pour 
   // remplacer "st" par "er" après 1 et supprimer le "st" après 21 et 31. 
   // ne pas supprimer les lettres "st", "nd" et "rd" des mots français!
   // (luNDi, veNDredi, maRDi, eST) 
   
   $strDate = preg_replace("/(\D)1st/", "\${1}1er", $strDate); //1st qui n'est pas précédé par un chiffre 
   $strDate = preg_replace("/(\d)(st|th|nd|rd)/", "\${1}", $strDate); //st, th, nd ou rd qui est précédé d'un chifre 
   
   return $strDate; 
} 


// pour les dates < 1970 (source : contribution d'un utilisateur sur php.net)

function MakeTime()
{
   $objArgs = func_get_args();
   $nCount = count($objArgs);
   if ($nCount < 7)
   {
       $objDate = getdate();
       if ($nCount < 1)
           $objArgs[] = $objDate["hours"];
       if ($nCount < 2)
           $objArgs[] = $objDate["minutes"];
       if ($nCount < 3)
           $objArgs[] = $objDate["seconds"];
       if ($nCount < 4)
           $objArgs[] = $objDate["mon"];
       if ($nCount < 5)
           $objArgs[] = $objDate["mday"];
       if ($nCount < 6)
           $objArgs[] = $objDate["year"];
       if ($nCount < 7)
           $objArgs[] = -1;
   }
   $nYear = $objArgs[5];
   $nOffset = 0;
   if ($nYear < 1970)
   {
       if ($nYear < 1902)
           return 0;
       else if ($nYear < 1952)
       {
           $nOffset = -2650838400;
           $objArgs[5] += 84;
           // Apparently dates before 1942 were never DST
           if ($nYear < 1942)
               $objArgs[6] = 0;
       }
       else
       {
           $nOffset = -883612800;
           $objArgs[5] += 28;
       }
   }
  
   return call_user_func_array("mktime", $objArgs) + $nOffset;
}


// Génération d'un mot de passe aléatoire de 8 caractères
// ATTENTION CETTE FONCTION NE DOIT PAS ETRE MODIFIEE
// => ELLE EST EGALEMENT UTILISEE POUR GENERER UN VECTEUR D'ENCRYPTION
// (contrainte à respecter : 8 caractère, de préférence a-z,A-Z,0-9)
function generate_pass()
{
   $pass="";
   
   for($i=0; $i<8; $i++)
   {
      $lettre=rand(0,61);
      switch($lettre)
      {
         case 10 :    $pass .= "a";   break;
         case 11 :    $pass .= "b"; break;
         case 12 :    $pass .= "c"; break;
         case 13 :    $pass .= "d"; break;
         case 14 :    $pass .= "e"; break;
         case 15 :    $pass .= "f"; break;
         case 16 :    $pass .= "g"; break;
         case 17 :    $pass .= "h"; break;
         case 18 :    $pass .= "i"; break;
         case 19 :    $pass .= "j"; break;
         case 20 :    $pass .= "k"; break;
         case 21 :    $pass .= "l"; break;
         case 22 :    $pass .= "m"; break;
         case 23 :    $pass .= "n"; break;
         case 24 :    $pass .= "o"; break;
         case 25 :    $pass .= "p"; break;
         case 26 :    $pass .= "q"; break;
         case 27 :    $pass .= "r"; break;
         case 28 :    $pass .= "s"; break;
         case 29 :    $pass .= "t"; break;
         case 30 :    $pass .= "u"; break;
         case 31 :    $pass .= "v"; break;
         case 32 :    $pass .= "w"; break;
         case 33 :    $pass .= "x"; break;
         case 34 :    $pass .= "y"; break;
         case 35 :    $pass .= "z"; break;
         case 36 :    $pass .= "A"; break;
         case 37 :    $pass .= "B"; break;
         case 38 :    $pass .= "C"; break;
         case 39 :    $pass .= "D"; break;
         case 40 :    $pass .= "E"; break;
         case 41 :    $pass .= "F"; break;
         case 42 :    $pass .= "G"; break;
         case 43 :    $pass .= "H"; break;
         case 44 :    $pass .= "I"; break;
         case 45 :    $pass .= "J"; break;
         case 46 :    $pass .= "K"; break;
         case 47 :    $pass .= "L"; break;
         case 48   :    $pass .= "M"; break;
         case 49 :    $pass .= "N"; break;
         case 50 :    $pass .= "O"; break;
         case 51 :    $pass .= "P"; break;
         case 52 :    $pass .= "Q"; break;
         case 53 :    $pass .= "R"; break;
         case 54 :    $pass .= "S"; break;
         case 55 :    $pass .= "T"; break;
         case 56 :    $pass .= "U"; break;
         case 57 :    $pass .= "V"; break;
         case 58 :    $pass .= "W"; break;
         case 59 :    $pass .= "X"; break;
         case 60 :    $pass .= "Y"; break;
         case 61 :    $pass .= "Z"; break;
                  
         default :    $pass .= "$lettre"; break;      
      }
   }

   return $pass;
}

function validate_filename($nom)
{
   $nom=preg_replace("/[aàáâãäå]/", "a", $nom);
   $nom=preg_replace("/[cç]/", "c", $nom);
   $nom=preg_replace("/[eèéêë]/", "e", $nom);
   $nom=preg_replace("/[iìíîï]/", "i", $nom);
   $nom=preg_replace("/[nñ]/", "n", $nom);
   $nom=preg_replace("/[oðòóôõö]/", "o", $nom);
   $nom=preg_replace("/[uùúûü]/", "u", $nom);
   $nom=preg_replace("/[yýÿ]/", "y", $nom);
   
   $nom=str_replace(" ","_",$nom);
   $nom=str_replace("'","_",$nom);
   $nom=str_replace("|","_",$nom);
   $nom=str_replace("\"","_",$nom);
   $nom=str_replace("\\","_",$nom);
   $nom=str_replace("\/","_",$nom);
   
   return $nom;
}


function get_infos_elements($db, $info_id)
{
   // fonction qui recherche tous les éléments d'un article et qui retourne un tableau contenant ces éléments triés

   // initialisation du tableau d'éléments
   $elements=array();

   // ENCADRES (type_element = 2)
   $result=db_query($db,"SELECT $GLOBALS[_DBC_comp_infos_encadre_info_id], $GLOBALS[_DBC_comp_infos_encadre_texte],
                                $GLOBALS[_DBC_comp_infos_encadre_txt_align], $GLOBALS[_DBC_comp_infos_encadre_ordre]
                           FROM    $GLOBALS[_DB_comp_infos_encadre]
                           WHERE $GLOBALS[_DBC_comp_infos_encadre_info_id]='$info_id'
                           ORDER BY $GLOBALS[_DBC_comp_infos_encadre_ordre] ASC");

   $rows=db_num_rows($result);

   // on met chaque encadré dans le tableau
   for($i=0; $i<$rows ; $i++)
   {
      list($id,$texte,$txt_align,$ordre)=db_fetch_row($result, $i);
      if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
      {
         $err_file=realpath(__FILE__);
         $line=__LINE__;
         
         if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
         {
            mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de données incohérente'\nIdentifiant : $_SESSION[auth_user]");
            die("Erreur : base de données incohérente. Un courriel a été envoyé à l'administrateur.");
         }
         else
            die("Erreur : base de données incohérente. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
      }
      else
         $elements["$ordre"]=array("type" => 2, "id" => $id, "texte" => $texte, "txt_align" => $txt_align);
   }
   db_free_result($result);

   // PARAGRAPHES (type_element = 5)
   $result=db_query($db,"SELECT    $GLOBALS[_DBC_comp_infos_para_info_id], $GLOBALS[_DBC_comp_infos_para_texte],
                                 $GLOBALS[_DBC_comp_infos_para_align], $GLOBALS[_DBC_comp_infos_para_ordre],
                                 $GLOBALS[_DBC_comp_infos_para_gras], $GLOBALS[_DBC_comp_infos_para_italique],
                                 $GLOBALS[_DBC_comp_infos_para_taille]
                           FROM $GLOBALS[_DB_comp_infos_para] WHERE $GLOBALS[_DBC_comp_infos_para_info_id]='$info_id'
                           ORDER BY $GLOBALS[_DBC_comp_infos_para_ordre] ASC");

   $rows=db_num_rows($result);

   // on met chaque paragraphe dans le tableau
   for($i=0; $i<$rows ; $i++)
   {
      list($id,$texte,$txt_align,$ordre, $gras, $italique, $taille)=db_fetch_row($result, $i);
      if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
      {
         $err_file=realpath(__FILE__);
         $line=__LINE__;
         
         if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
         {
            mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de données incohérente'\nIdentifiant : $_SESSION[auth_user]");
            die("Erreur : base de données incohérente. Un courriel a été envoyé à l'administrateur.");
         }
         else
            die("Erreur : base de données incohérente. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
      }
      else
         $elements["$ordre"]=array("type" => 5, "id" => $id, "texte" => $texte, "txt_align" => $txt_align, "gras" => $gras, "italique" => $italique, "taille" => $taille);
   }
   db_free_result($result);

   // FICHIERS (type_element = 6)
   $result=db_query($db,"SELECT $GLOBALS[_DBC_comp_infos_fichiers_info_id], $GLOBALS[_DBC_comp_infos_fichiers_texte],
                                $GLOBALS[_DBC_comp_infos_fichiers_fichier], $GLOBALS[_DBC_comp_infos_fichiers_ordre]
                            FROM $GLOBALS[_DB_comp_infos_fichiers]
                        WHERE $GLOBALS[_DBC_comp_infos_fichiers_info_id]='$info_id'
                           ORDER BY $GLOBALS[_DBC_comp_infos_fichiers_ordre] ASC");

   $rows=db_num_rows($result);

   // on met chaque encadré dans le tableau
   for($i=0; $i<$rows ; $i++)
   {
      list($id,$texte,$fichier,$ordre)=db_fetch_row($result, $i);
      if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
      {
         $err_file=realpath(__FILE__);
         $line=__LINE__;
         
         if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
         {
            mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de données incohérente'\nIdentifiant : $_SESSION[auth_user]");
            die("Erreur : base de données incohérente. Un courriel a été envoyé à l'administrateur.");
         }
         else
            die("Erreur : base de données incohérente. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
      }
      else
         $elements["$ordre"]=array("type" => 6, "id" => $id, "texte" => $texte, "fichier" => $fichier);
   }
   db_free_result($result);

   // Séparateurs (type 8)
   $result=db_query($db,"SELECT $GLOBALS[_DBC_comp_infos_sepa_info_id], $GLOBALS[_DBC_comp_infos_sepa_ordre]
                           FROM $GLOBALS[_DB_comp_infos_sepa]
                         WHERE $GLOBALS[_DBC_comp_infos_sepa_info_id]='$info_id'
                           ORDER BY $GLOBALS[_DBC_comp_infos_sepa_ordre] ASC");

   $rows=db_num_rows($result);

   // on met chaque séparateur dans le tableau
   for($i=0; $i<$rows ; $i++)
   {
      list($id,$ordre)=db_fetch_row($result, $i);
      if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
      {
         $err_file=realpath(__FILE__);
         $line=__LINE__;
         
         if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
         {
            mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de données incohérente'\nIdentifiant : $_SESSION[auth_user]");
            die("Erreur : base de données incohérente. Un courriel a été envoyé à l'administrateur.");
         }
         else
            die("Erreur : base de données incohérente. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
      }
      else
         $elements["$ordre"]=array("type" => 8, "id" => $id);
   }
   db_free_result($result);
   
   return($elements);
}

function get_info_align($int_align)
{
   if(!is_numeric($int_align))
      return "left";
      
   switch($int_align)
   {
      case 0 : return "left";
               break;
      case 1 : return "center";
               break;
      case 2 : return "right";
               break;
      case 3 : return "justify";
               break;
                     
      default : return "left";
   }
}

// Verrouillage d'une fiche candidat
// pour des modifications sur les parties communes à toutes les composantes

function cand_lock($dbr, $candidat_id)
{
   $result=db_query($dbr, "BEGIN; SELECT $GLOBALS[_DBC_candidat_lock], $GLOBALS[_DBC_candidat_lockdate]
                                    FROM $GLOBALS[_DB_candidat]
                                  WHERE $GLOBALS[_DBC_candidat_id]='$candidat_id' FOR UPDATE");

   if(db_num_rows($result))
   {
      list($lock, $lockdate)=db_fetch_row($result, 0);
      db_free_result($result);

      // La personne a déjà le verrouillage : on met la date à jour (+ 10 minutes)
      if($lock==$_SESSION['auth_id'])
      {
         $new_lockdate=time()+600;

         db_query($dbr, "UPDATE $GLOBALS[_DB_candidat] SET $GLOBALS[_DBU_candidat_lockdate]='$new_lockdate'
                         WHERE $GLOBALS[_DBU_candidat_id]='$candidat_id'; COMMIT TRANSACTION;");

         return 0;
      }
      else // On regarde si le délai n'est pas expiré
      {
         if($lockdate<time()) // il l'est : on prend le verrouillage
         {
            $new_lockdate=time()+600;

            db_query($dbr, "UPDATE $GLOBALS[_DB_candidat] SET $GLOBALS[_DBU_candidat_lock]='$_SESSION[auth_id]',
                                                              $GLOBALS[_DBU_candidat_lockdate]='$new_lockdate'
                            WHERE $GLOBALS[_DBU_candidat_id]='$candidat_id'; COMMIT TRANSACTION;");

            return 0;
         }
         else // Encore verrouillé
         {
            $temps_restant=$lockdate-time();
            db_query($dbr, "COMMIT TRANSACTION;");

            return $temps_restant;
         }
      }
   }
   else
   {
      db_query($dbr, "COMMIT TRANSACTION;");
      return -1;
   }
}

function cand_unlock($dbr, $candidat_id)
{
   $result=db_query($dbr, "BEGIN; SELECT $GLOBALS[_DBC_candidat_lock], $GLOBALS[_DBC_candidat_lockdate]
                                     FROM $GLOBALS[_DB_candidat]
                                  WHERE $GLOBALS[_DBC_candidat_id]='$candidat_id' FOR UPDATE");

   if(db_num_rows($result))
   {
      list($lock, $lockdate)=db_fetch_row($result, 0);
      db_free_result($result);

      // La personne a le verrouillage : on déverrouille
      if($lock==$_SESSION['auth_id'])
         db_query($dbr, "UPDATE $GLOBALS[_DB_candidat] SET $GLOBALS[_DBU_candidat_lockdate]='0'
                         WHERE $GLOBALS[_DBU_candidat_id]='$candidat_id'");
   }

   db_query($dbr, "COMMIT TRANSACTION;");

   return TRUE;
}


// ============================================================================================
//                           FONCTIONS SPECIFIQUES A LA PARTIE GESTION
// ============================================================================================

// Menu supérieur pour la partie gestion

function menu_sup_gestion()
{
   print("<div class='menu_haut_gauche' style='width:100%; background-image:url($GLOBALS[__IMG_DIR]/fond_menu_haut.jpg);'>\n");

   if($_SESSION['niveau']==$GLOBALS["__LVL_SUPPORT"])
   {
      print("<a href='$GLOBALS[__GESTION_DIR]/recherche.php'><img src='$GLOBALS[__ICON_DIR]/filefind_22x22_menu_haut.png' alt='[Recherche]' title='Recherche' border='0'></a>");
   }
   else
   {
      if(in_array($_SESSION['niveau'], array("$GLOBALS[__LVL_SCOL_MOINS]","$GLOBALS[__LVL_SCOL_PLUS]","$GLOBALS[__LVL_RESP]","$GLOBALS[__LVL_SUPER_RESP]","$GLOBALS[__LVL_ADMIN]")))
         print("<a href='$GLOBALS[__GESTION_DIR]/creation.php'><img src='$GLOBALS[__ICON_DIR]/contact-new_22x22_menu_haut.png' alt='[Nouvelle fiche]' title='Nouvelle fiche' border='0'></a>\n");
   
      print("<a href='$GLOBALS[__GESTION_DIR]/index.php'><img src='$GLOBALS[__ICON_DIR]/document-open_22x22_menu_haut.png' alt='[Fiches en attente]' title='Fiches en attente' border='0'></a>
             <a href='$GLOBALS[__GESTION_DIR]/candidats.php'><img src='$GLOBALS[__ICON_DIR]/system-file-manager_22x22_menu_haut.png' alt='[Toutes les fiches]' title='Toutes les fiches' border='0'></a>
             <a href='$GLOBALS[__GESTION_DIR]/fiches_traitees.php'><img src='$GLOBALS[__ICON_DIR]/flag-green_22x22_menu_haut.png' alt='[Toutes les fiches]' title='Fiches traitées' border='0'></a>
             <a href='$GLOBALS[__GESTION_DIR]/recherche.php'><img src='$GLOBALS[__ICON_DIR]/filefind_22x22_menu_haut.png' alt='[Recherche]' title='Recherche' border='0'></a>
             <a href='$GLOBALS[__GESTION_DIR]/listes_complementaires.php'><img src='$GLOBALS[__ICON_DIR]/liste_22x22_menu_haut.png' alt='[Listes complémentaires]' title='Listes complémentaires' border='0'></a>\n");
   
      if(in_array($_SESSION['niveau'], array("$GLOBALS[__LVL_SCOL_MOINS]","$GLOBALS[__LVL_SCOL_PLUS]","$GLOBALS[__LVL_RESP]","$GLOBALS[__LVL_SUPER_RESP]","$GLOBALS[__LVL_ADMIN]")))
         print("<a href='$GLOBALS[__GESTION_DIR]/masse.php'><img src='$GLOBALS[__ICON_DIR]/kpersonalizer_22x22_menu_haut.png' alt='[Gestion en masse]' title='Gestion en masse' border='0'></a>\n");
   
      print("<a href='$GLOBALS[__GESTION_DIR]/tabs_stats.php'><img src='$GLOBALS[__ICON_DIR]/kpercentage_22x22_menu_haut.png' alt='[Statistiques]' title='Statistiques' border='0'></a>\n");
   
      if(in_array($_SESSION['niveau'], array("$GLOBALS[__LVL_SCOL_PLUS]","$GLOBALS[__LVL_RESP]","$GLOBALS[__LVL_SUPER_RESP]","$GLOBALS[__LVL_ADMIN]")))
         print("<a href='$GLOBALS[__GESTION_DIR]/admin/index.php'><img src='$GLOBALS[__ICON_DIR]/preferences_22x22_menu_haut.png' alt='[Administration]' title='Administration' border='0'></a>\n");
   
      if($nb_msg=check_messages())
         $nb_msg_txt=$nb_msg==1 ? "<a href='$GLOBALS[__GESTION_MSG_DIR]/index.php' class='lien_blanc' style='vertical-align:40%;'><b>($nb_msg non lu)</b></a>" : "<a  href='$GLOBALS[__GESTION_MSG_DIR]/index.php' class='lien_blanc' style='vertical-align:40%;'><b>($nb_msg non lus)</b></a>";
      else
         $nb_msg_txt="";
   
      print("<a href='$GLOBALS[__GESTION_MSG_DIR]/index.php'><img src='$GLOBALS[__ICON_DIR]/email_22x22_menu_haut.png' alt='[Messagerie]' title='Messagerie' border='0'></a>
             $nb_msg_txt
             <a style='position:absolute; right:200px;' href='$GLOBALS[__GESTION_DIR]/periode.php'><img src='$GLOBALS[__ICON_DIR]/1day_22x22_menu_haut.png' alt='[Année]' title='Année Universitaire' border='0'></a>\n");
   }
   
   if(in_array($_SESSION['niveau'], array("$GLOBALS[__LVL_SUPPORT]", "$GLOBALS[__LVL_SUPER_RESP]","$GLOBALS[__LVL_ADMIN]")) || (isset($_SESSION["multi_composantes"]) && $_SESSION["multi_composantes"]==1))
      print("<a style='position:absolute; right:150px;' href='$GLOBALS[__GESTION_DIR]/select_composante.php'><img src='$GLOBALS[__ICON_DIR]/gohome_22x22_menu_haut.png' alt='[Composante]' title='Composante' border='0'></a>\n");

   // Aide : on regarde le répertoire d'aide de l'application ou bien le sous répertoire direct "aide/" dans le cas d'un module
   $HELP_FILE=str_replace($GLOBALS["__GESTION_DIR"], $GLOBALS["__GESTION_AIDE_DIR"], $_SERVER["SCRIPT_FILENAME"]);

   if(is_file("$HELP_FILE") || is_file("aide/".basename($HELP_FILE)))
   {
      $HELP_FILE_LINK=is_file("$HELP_FILE") ? str_replace($GLOBALS["__ROOT_DIR"],"", $HELP_FILE) : "aide/".basename($HELP_FILE);
      print("<a style='position:absolute; right:100px;' href='#aide' onclick=\"window.open('$HELP_FILE_LINK','nw','height=650,width=900,location=no,toolbar=0,directories=no,status=no,menubar=no,resizable=yes,scrollbars=yes')\"><img src='$GLOBALS[__ICON_DIR]/help-browser_22x22_menu_haut.png' alt='[Aide]' title='Aide' border='0'></a>\n");
   }

   print("<a style='position:absolute; right:20px;' href='$GLOBALS[__GESTION_DIR]/login.php'><img src='$GLOBALS[__ICON_DIR]/application-exit2_22x22_menu_haut.png' alt='[Déconnexion]' title='[Déconnexion]' border='0'></a>
      </div>
      <div class='clearer'></div>\n");
}


function en_tete_gestion()
{
   // Calcul du la taille du logo pour avoir un titre bien centré
   if(is_file("$GLOBALS[__IMG_DIR_ABS]/logo.jpg"))
   {
      $logo="$GLOBALS[__IMG_DIR]/logo.jpg";
      $array_logo=getimagesize("$GLOBALS[__IMG_DIR_ABS]/logo.jpg");
   }
   elseif(is_file("$GLOBALS[__LOGO_DEFAUT_ABS]"))
   {
      $logo=$GLOBALS["__LOGO_DEFAUT"];
      $array_logo=getimagesize("$GLOBALS[__LOGO_DEFAUT_ABS]");
   }
   else
   {
      $logo="";
      $no_logo=1;
      $largeur_logo=150;
      $hauteur_logo=106;
   }

   if(!isset($no_logo))
   {
      $largeur_logo=array_key_exists(0, $array_logo) ? $array_logo["0"] : 150;
      $hauteur_logo=array_key_exists(1, $array_logo) ? $array_logo["1"] : 106;
   }

   // Accès direct à la fiche du dernier candidat consulté, depuis n'importe où
   if(isset($_SERVER['PHP_SELF']) && $_SERVER['PHP_SELF']!="$GLOBALS[__GESTION_DIR]/edit_candidature.php" && isset($_SESSION["candidat_id"]) && ctype_digit($_SESSION["candidat_id"]) && isset($_SESSION["tab_candidat"]))
   {
      $civ=$_SESSION['tab_candidat']['civ_texte'];
      $nom=ucwords( $_SESSION['tab_candidat']['nom']);
   
      $adresse_retour="<a href='$GLOBALS[__GESTION_DIR]/edit_candidature.php?cid=$_SESSION[candidat_id]' target='_self' class='lien2a'>Retour à la fiche de<br>$civ $nom</a>";
   }
   else
      $adresse_retour="";

   print("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
            <html><head><title>$_SESSION[composante] - Gestion des précandidatures $GLOBALS[__PERIODE]-".($GLOBALS["__PERIODE"]+1)."</title>

            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
            <meta http-equiv='Pragma' content='no-cache'>
            <link rel='stylesheet' type='text/css' href='$GLOBALS[__STATIC_DIR]/$_SESSION[css]'></head>

            <body class='main' leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' vlink='black' alink='black' link='black'>

            <table border='0' cellpadding='0' cellspacing='0' width='100%' align='center'>
            <tr>
               <td width='$largeur_logo' height='$hauteur_logo'>\n");

   if(!isset($no_logo))
      print("<img align='left' border='0' src='$logo' alt='logo'>\n");

   print("</td>
          <td height='$hauteur_logo' align='center'>\n");

   if(in_array($_SESSION['niveau'], array("$GLOBALS[__LVL_SUPER_RESP]","$GLOBALS[__LVL_ADMIN]")) || (isset($_SESSION["multi_composantes"]) && $_SESSION["multi_composantes"]==1))
      print("<a href='$GLOBALS[__GESTION_DIR]/select_composante.php' class='lien_titre_page'><b>$_SESSION[composante]<br>Gestion des précandidatures $GLOBALS[__PERIODE]-".($GLOBALS["__PERIODE"]+1)."</b></a>\n");
   else
      print("<font class='TitrePage2'><b>$_SESSION[composante]<br>Gestion des précandidatures $GLOBALS[__PERIODE]-".($GLOBALS["__PERIODE"]+1)."</b></font>\n");

   print("</td>
          <td width='$largeur_logo' height='$hauteur_logo' align='center'>$adresse_retour</td>
      </tr>
       </table>\n");
}

function en_tete_simple()
{
   // Calcul du la taille du logo pour avoir un titre bien centré
   if(is_file("$GLOBALS[__IMG_DIR_ABS]/logo.jpg"))
   {
      $logo="$GLOBALS[__IMG_DIR]/logo.jpg";
      $array_logo=getimagesize("$GLOBALS[__IMG_DIR_ABS]/logo.jpg");
   }
   elseif(is_file("$GLOBALS[__LOGO_DEFAUT_ABS]"))
   {
      $logo=$GLOBALS["__LOGO_DEFAUT"];
      $array_logo=getimagesize("$GLOBALS[__LOGO_DEFAUT_ABS]");
   }
   else
   {
      $logo="";
      $no_logo=1;
      $largeur_logo=150;
      $hauteur_logo=106;
   }

   if(!isset($no_logo))
   {
      $largeur_logo=array_key_exists(0, $array_logo) ? $array_logo["0"] : 150;
      $hauteur_logo=array_key_exists(1, $array_logo) ? $array_logo["1"] : 106;
   }
   
   print("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
            <html><head><title>ARIA - Gestion des précandidatures $GLOBALS[__PERIODE]-".($GLOBALS["__PERIODE"]+1)."</title>

            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
            <meta http-equiv='Pragma' content='no-cache'>
            <link rel='stylesheet' type='text/css' href='$GLOBALS[__STATIC_DIR]/$_SESSION[css]'></head>

            <body class='main' leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' vlink='black' alink='black' link='black'>

            <table border='0' cellpadding='0' cellspacing='0' width='100%' align='center'>
            <tr>
               <td width='$largeur_logo' height='$hauteur_logo'>\n");

   if(!isset($no_logo))
      print("<img align='left' border='0' src='$logo' alt='logo'>\n");

   print("</td>
         <td height='$hauteur_logo' align='center'>
            <font class='TitrePage2'><b>ARIA - Gestion des précandidatures $GLOBALS[__PERIODE]-".($GLOBALS["__PERIODE"]+1)."</b></font>
         </td>
         <td width='$largeur_logo' height='$hauteur_logo'></td>
      </tr>
      </table>\n");
}


// Cet fonction en-tête est particulière :
// - elle n'est utilisée que pour la page d'accueil coté candidat (avant authentification)
// - elle intègre les Meta pour les moteurs de recherche

function en_tete_index()
{
   // Calcul du la taille du logo pour avoir un titre bien centré
   if(is_file("$GLOBALS[__IMG_DIR_ABS]/logo.jpg"))
   {
      $logo="$GLOBALS[__IMG_DIR]/logo.jpg";
      $array_logo=getimagesize("$GLOBALS[__IMG_DIR_ABS]/logo.jpg");
   }
   elseif(is_file("$GLOBALS[__LOGO_DEFAUT_ABS]"))
   {
      $logo=$GLOBALS["__LOGO_DEFAUT"];
      $array_logo=getimagesize("$GLOBALS[__LOGO_DEFAUT_ABS]");
   }
   else
   {
      $logo="";
      $no_logo=1;
      $largeur_logo=150;
      $hauteur_logo=106;
   }

   if(!isset($no_logo))
   {
      $largeur_logo=array_key_exists(0, $array_logo) ? $array_logo["0"] : 150;
      $hauteur_logo=array_key_exists(1, $array_logo) ? $array_logo["1"] : 106;
   }
   
   print("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
            <html><head><title>". htmlspecialchars($GLOBALS["__TITRE_HTML"], ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]) ."</title>

            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
            <meta http-equiv=\"Pragma\" content=\"no-cache\">
            <meta name=\"keywords\" content=\"". htmlspecialchars($GLOBALS["__META"], ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]) ."\">
            <link rel=\"stylesheet\" type=\"text/css\" href=\"$GLOBALS[__STATIC_DIR]/$_SESSION[css]\"></head>

            <body class=\"main\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" vlink=\"black\" alink=\"black\" link=\"black\">

            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\">
            <tr>
               <td width=\"$largeur_logo\" height=\"$hauteur_logo\">\n");

   if(!isset($no_logo))
      print("<img align=\"left\" border=\"0\" src=\"$logo\" alt=\"logo\">\n");

   print("</td>
          <td height=\"$hauteur_logo\" align=\"center\">
            <font class=\"TitrePage2\"><b>". htmlspecialchars($GLOBALS["__TITRE_PAGE"], ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]) ."</b></font>
         </td>
         <td width=\"$largeur_logo\" height=\"$hauteur_logo\"></td>
      </tr>
      </table>\n");
}


function menu_sup_simple()
{
   print("<div class='menu_haut_central' style='background-image:url($GLOBALS[__IMG_DIR]/fond_menu_haut.jpg);'></div>");
}


function titre_page_icone($titre, $icone, $padding, $align)
{
   switch($align)
   {
      case "C"   : $align="center"; break;
      case "L"   : $align="left"; break;
      case "R"   : $align="right"; break;
      default   : $align="left";
   }

   // rajout de l'unité ?
   $padding.=ctype_digit(trim($padding)) ? "px" : "";


   if(trim($icone)!="" && is_file("$GLOBALS[__ICON_DIR_ABS]/$icone"))
      $img="<img class='icone_titre_page' src='$GLOBALS[__ICON_DIR]/$icone' border='0' alt=''>";
   else
      $img="";

   print("<div class='titre_page' style='padding-bottom:$padding; text-align:$align;'>
            $img $titre
         </div>\n");


// ********** TEST POUR LA TRANSPARENCE DES PNG *********
// ********** REMPLACER LE TABLEAU PAR UN DIV *********
/*
   if(trim($icone)!="" && is_file("$GLOBALS[__ICON_DIR_ABS]/$icone"))
   {
      $array_icone=getimagesize("$GLOBALS[__ICON_DIR_ABS]/$icone");
      $largeur_icone=array_key_exists(0, $array_icone) ? $array_icone["0"] . "px" : "32px";
      $hauteur_icone=array_key_exists(1, $array_icone) ? $array_icone["1"] . "px" : "32px";

      print("<style>
               dt.icone_tr { background:url($GLOBALS[__ICON_DIR]/$icone) no-repeat bottom; width:$largeur_icone; height:$hauteur_icone; vertical-align:center; }
                *html dt.icone_tr { background-image:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$GLOBALS[__ICON_DIR]/$icone'); vertical-align:center; }
            </style>\n");

      $img="<dt class='icone_tr'></dt>";
   }
   else
      $img="";

   print("<table cellpadding='0' border='0' align='$align' style='padding-bottom:$padding;'>
         <tr>
            <td align='center' width='40' nowrap='true' valign='middle'>
               <div style='text-align:center; vertical-align:center; margin-left:auto; margin-right:auto;'>
                  $img
               </div>
            </td>
            <td align='left' valign='middle'>
               <font class='titre_page'>$titre</font>
            </td>
         </tr>
         </table>\n");
*/
// *************************

}

function pied_de_page()
{
   print("<div class='footer' style='background-image:url($GLOBALS[__IMG_DIR]/fond_menu_haut.jpg);'>
             <a href='mailto:$GLOBALS[__EMAIL_SUPPORT]' class='lien_blanc'>
                <img class='icone' src='$GLOBALS[__ICON_DIR]/mail_send_22x22_fond.png' border='0' alt='Contact' desc='Contact'>
                Support Informatique
             </a>
          </div>\n");
}


// ============================================================================================
//                           FONCTIONS SPECIFIQUES A LA PARTIE CANDIDAT
// ============================================================================================


function menu_sup_candidat()
{
   // Menu actif (pour afficher le lien de la page active d'une couleur différente)
   if($args=func_num_args())
      $actif=func_get_arg(0);
   else
      $actif=0;

   print("<div style='height:24px; background-image:url($GLOBALS[__IMG_DIR]/fond_menu_haut.jpg);'>
            <ul class='menu_haut'>\n");

   $lien=$actif==$GLOBALS["__MENU_COMP"] ? "menu_haut_actif" : "menu_haut";
   print("<li class='menu_haut'><a href='$GLOBALS[__CAND_DIR]/composantes.php' class='$lien'>Choisir une autre composante</a></li>\n");

   $lien=$actif==$GLOBALS["__MENU_FICHE"] ? "menu_haut_actif" : "menu_haut";
   print("<li class='menu_haut'><a href='$GLOBALS[__CAND_DIR]/precandidatures.php' class='$lien'>Votre fiche</a></li>\n");

   $lien=$actif==$GLOBALS["__MENU_RECH"] ? "menu_haut_actif" : "menu_haut";
   print("<li class='menu_haut'><a href='$GLOBALS[__CAND_DIR]/recherche_formation.php' class='$lien'>Rechercher une formation</a></li>\n");

   $lien=$actif==$GLOBALS["__MENU_MSG"] ? "menu_haut_actif" : "menu_haut";
   print("<li class='menu_haut'><a href='$GLOBALS[__CAND_MSG_DIR]/index.php' class='$lien'>Messagerie</a></li>\n");

   $lien=$actif==$GLOBALS["__MENU_DOC"] ? "menu_haut_actif" : "menu_haut";
   print("<li class='menu_haut'><a href='$GLOBALS[__DOC_DIR]/documentation.php' target='_blank' class='$lien'>Mode d'emploi</a></li>
      </ul>
      <ul class='menu_haut' style='float:right;'>\n");

   if(isset($_SESSION["derniere_connexion"]) && $_SESSION["derniere_connexion"]!=0)
      print("<li class='menu_haut' style='padding-right:30px;'>Dernière connexion : " . date("d-m-Y - H:i") . "</li>");

   print("<li class='menu_haut'><a href='$GLOBALS[__MOD_DIR]/index.php?d=1' class='menu_haut' style='border-right:0'>Valider et déconnecter</a></li>
         </ul>
         </div>
         <div class='clearer'></div>\n");
}


function en_tete_candidat()
{
   // Calcul du la taille du logo pour avoir un titre bien centré
   if(is_file("$GLOBALS[__IMG_DIR_ABS]/logo.jpg"))
   {
      $logo="$GLOBALS[__IMG_DIR]/logo.jpg";
      $array_logo=getimagesize("$GLOBALS[__IMG_DIR_ABS]/logo.jpg");
   }
   elseif(is_file("$GLOBALS[__LOGO_DEFAUT_ABS]"))
   {
      $logo=$GLOBALS["__LOGO_DEFAUT"];
      $array_logo=getimagesize("$GLOBALS[__LOGO_DEFAUT_ABS]");
   }
   else
   {
      $logo="";
      $no_logo=1;
      $largeur_logo=150;
      $hauteur_logo=106;
   }

   if(!isset($no_logo))
   {
      $largeur_logo=array_key_exists(0, $array_logo) ? $array_logo["0"] : 150;
      $hauteur_logo=array_key_exists(1, $array_logo) ? $array_logo["1"] : 106;
   }

   print("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
         <html><head><title>". htmlspecialchars($GLOBALS["__TITRE_HTML"], ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]) ."</title>

         <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
         <meta http-equiv=\"Pragma\" content=\"no-cache\">
         <link rel=\"stylesheet\" type=\"text/css\" href=\"$GLOBALS[__STATIC_DIR]/$_SESSION[css]\"></head>

         <body class=\"main\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" vlink=\"black\" alink=\"black\" link=\"black\">

         <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\">
         <tr>
            <td width=\"$largeur_logo\" height=\"$hauteur_logo\">\n");

   if(!isset($no_logo))
      print("<img align='left' border='0' src='$logo' alt='logo'>\n");

   print("</td>
          <td height='$hauteur_logo' align='center'>
            <font class='TitrePage2'>\n");

   if(isset($_SESSION["composante"]))
      print("<b>$_SESSION[composante]</b><br>\n");
   else
      print("<b>". htmlspecialchars($GLOBALS["__TITRE_PAGE"], ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]) ."</b><br>\n");

   print("         <b>Précandidatures en ligne</b>
               </font>
            </td>
            <td width='$largeur_logo' height='$hauteur_logo'>\n");

   if(isset($_SESSION["authentifie"]) && isset($_SESSION["composante"]))
   {
      if($nb_new_msg=check_messages())
      {
         $txt_msg=$nb_new_msg>1 ? "<a href='$GLOBALS[__CAND_MSG_DIR]/index.php' class='lien_bleu_10'><b>$nb_new_msg messages<br>non lus</b></a>" : "<a href='$GLOBALS[__CAND_MSG_DIR]/index.php' class='lien_bleu_10'><b>$nb_new_msg message<br>non lu</b></a>";
         print("$txt_msg");
      }
      else
         print("<font class='Texte'><i>Aucun message<br>non lu</i></font>");
   }

   print("   </td>
         </tr>
         </table>\n");

   if(isset($nb_new_msg) && $nb_new_msg>0)
      return $nb_new_msg;
   else
      return 0;
}

function en_tete_candidat_simple()
{
   // Calcul du la taille du logo pour avoir un titre bien centré
   if(is_file("$GLOBALS[__IMG_DIR_ABS]/logo.jpg"))
   {
      $logo="$GLOBALS[__IMG_DIR]/logo.jpg";
      $array_logo=getimagesize("$GLOBALS[__IMG_DIR_ABS]/logo.jpg");
   }
   elseif(is_file("$GLOBALS[__LOGO_DEFAUT_ABS]"))
   {
      $logo=$GLOBALS["__LOGO_DEFAUT"];
      $array_logo=getimagesize("$GLOBALS[__LOGO_DEFAUT_ABS]");
   }
   else
   {
      $logo="";
      $no_logo=1;
      $largeur_logo=150;
      $hauteur_logo=106;
   }

   if(!isset($no_logo))
   {
      $largeur_logo=array_key_exists(0, $array_logo) ? $array_logo["0"] : 150;
      $hauteur_logo=array_key_exists(1, $array_logo) ? $array_logo["1"] : 106;
   }
   
   print("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
            <html><head><title>". htmlspecialchars($GLOBALS["__TITRE_HTML"], ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]) ."</title>

            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
            <meta http-equiv=\"Pragma\" content=\"no-cache\">
            <link rel=\"stylesheet\" type=\"text/css\" href=\"$GLOBALS[__STATIC_DIR]/$_SESSION[css]\"></head>

            <body class=\"main\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" vlink=\"black\" alink=\"black\" link=\"black\">

            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\">
            <tr>
               <td width=\"$largeur_logo\" height=\"$hauteur_logo\">\n");

   if(!isset($no_logo))
      print("<img align='left' border='0' src='$logo' alt='logo'>\n");

   print("</td>
          <td height=\"$hauteur_logo\" align=\"center\">
            <font class='TitrePage2'><b>". htmlspecialchars($GLOBALS["__TITRE_PAGE"], ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]) ."<br>Précandidatures en ligne</b></font>
         </td>
         <td width=\"$largeur_logo\" height=\"$hauteur_logo\"></td>
      </tr>
      </table>\n");
}

function pied_de_page_simple()
{
   print("<div class='footer' style='background-image:url($GLOBALS[__IMG_DIR]/fond_menu_haut.jpg);'></div>\n");
}

function pied_de_page_candidat()
{
   if(array_key_exists("config", $_SESSION) && array_key_exists("__ASSISTANCE", $_SESSION["config"]) && $_SESSION["config"]["__ASSISTANCE"]=="t")
      print("<div class='footer' style='background-image:url($GLOBALS[__IMG_DIR]/fond_menu_haut.jpg);'>
                <a href='https://$_SERVER[SERVER_NAME]$GLOBALS[__CAND_DIR]/assistance/index.php' class='lien_blanc'>
                   <img class='icone' src='$GLOBALS[__ICON_DIR]/help-browser_22x22_menu_haut.png' border='0' alt='Aide' desc='Aide'>
                   Formulaire d'aide
                </a>
             </div>\n");
   else
      print("<div class='footer' style='background-image:url($GLOBALS[__IMG_DIR]/fond_menu_haut.jpg);'>
                <a href='mailto:$GLOBALS[__EMAIL_SUPPORT]' class='lien_blanc'>
                   <img class='icone' src='$GLOBALS[__ICON_DIR]/mail_send_22x22_fond.png' border='0' alt='Contact' desc='Contact''>
                   Support Informatique
               </a>
            </div>\n");
}


// ============================================================================================
//                           FONCTIONS SPECIFIQUES A LA MESSAGERIE
// ============================================================================================

// Détermination du niveau d'arborescence complémentaire de la messagerie, en fonction de l'identifiant (gestion ou candidat) passé en paramètre
// Le nom du sous répertoire est l'année de création, il dépend de la longueur de l'identifiant : 
// <2010 : longueur = 16
// >=2010 : longueur = 17

function sous_rep_msg($identifiant)
{
   // Normalement, ce cas ne se présente jamais puisque l'identifiant provient directement de la base après authentification
   if(!ctype_digit($identifiant))
      return FALSE;
      
   if(strlen($identifiant)==17)
      return substr($identifiant, 0, 2);
   elseif(strlen($identifiant)==16)
      return substr($identifiant, 0, 1);
}

function dossiers_messagerie()
{
   // Messagerie de la partie Gestion
   if(isset($_SESSION["auth_id"]))
   {
      $DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__DIR_REDIRECT=$GLOBALS["__REDIRECTION_GESTION"];

      $ID=$_SESSION["auth_id"];
   }
   elseif(isset($_SESSION["authentifie"])) // Messagerie de la partie Candidats
   {
      $DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__DIR_REDIRECT=$GLOBALS["__REDIRECTION_CANDIDAT"];

      $ID=$_SESSION["authentifie"];
   }

   foreach($GLOBALS["__MSG_DOSSIERS"] as $dossier_id => $nom_dossier)
   {
      if(!is_dir("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id"))
      {
         if(FALSE==mkdir("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id", 0770, TRUE))
         {
            mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de répertoire", "Répertoire : $DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id");
            die("Erreur système lors de la création d'un répertoire. Un message a été envoyé à l'administrateur.");
         }
         else
         {
            if(FALSE===($redir_file=fopen("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/index.php", "w+")))
            {
               mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de fichier", "Fichier : $DIR/$_SESSION[MSG_SOUS_REP]/$ID/index.php");
               die("Erreur système lors de la création d'un fichier. Un message a été envoyé à l'administrateur.");
            }

            fwrite($redir_file, $__DIR_REDIRECT);
            fclose($redir_file);

            @copy("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/index.php", "$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id/index.php");
         }
      }

      $array_dir=scandir("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id");

      // Décompte des messages non lus (fichiers se terminant par .0)
      $count_new=0;

      foreach($array_dir as $element)
      {
         if(is_file("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id/$element") && substr($element, -2)==".0")
            $count_new++;
         elseif(is_dir("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id/$element") && $element!="." && $element!="..")   // Message avec pièces jointes = sous répertoire
         {
            $array_subdir=scandir("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id/$element");

            foreach($array_subdir as $sub_element)
            {
               if(is_file("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id/$element/$sub_element") && substr($sub_element, -1)=="0")
                  $count_new++;
            }
         }
      }

      if($count_new)
         $nb_msg_texte=" <b>[$count_new]</b>";
      else
         $nb_msg_texte="";

      if($_SESSION["current_dossier"]!=$dossier_id)
         print("<li class='menu_gauche'><a href='index.php?dossier=$dossier_id' class='lien_menu_gauche' target='_self'>$nom_dossier$nb_msg_texte</a></li>\n");
      else
         print("<li class='menu_gauche'><a href='index.php?dossier=$dossier_id' class='lien_menu_gauche_select' target='_self'>$nom_dossier$nb_msg_texte</a>\n");
   }
}

// Copie d'un message
// Possible uniquement de gestion => gestion

function copy_msg()
{
   if(!isset($_SESSION["auth_id"]) || !isset($_SESSION["auth_nom"]))
      return FALSE;
      
   // Arguments
   // 0 : connexion à la BDD
   // 1 : dossier source
   // 2 : nom du fichier contenant le message (identifiant.0 ou identifiant.1)
   // 3 : identifiant du destinataire
   // 4 : Optionnel : "nom prenom" du destinataire
   $num_args=func_num_args();

   if($num_args<4)
      die("Erreur : utilisation incorrecte de la fonction copy_msg()");

   $foo=func_get_arg(0); // connexion (finalement abandonné : TODO : à nettoyer.

   $dossier_source=func_get_arg(1);
   $msg=func_get_arg(2);
   $destinataire_id=func_get_arg(3);

   // Niveau supplémentaire dans l'arborescence des messages
   $_MSG_DEST_SOUS_REP=sous_rep_msg($destinataire_id);

   if($num_args>4)
      $dest_nom_prenom=func_get_arg(4);
   elseif(isset($destinataire_id) && ctype_digit($destinataire_id))
   {
      $res_dest=db_query($GLOBALS["dbr"], "SELECT $GLOBALS[_DBC_acces_nom], $GLOBALS[_DBC_acces_prenom] FROM $GLOBALS[_DB_acces]
                                           WHERE $GLOBALS[_DBC_acces_id]='$destinataire_id'");

      if(db_num_rows($res_dest))
      {
         list($dest_nom, $dest_prenom)=db_fetch_row($res_dest, 0);

         $dest_nom_prenom=trim("$dest_nom $dest_prenom");

         db_free_result($res_dest);
      }
      else
         return FALSE;
   }
   else
      return FALSE;

   // Ouverture du fichier
  
  echo "$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$dossier_source/$msg<br>\n";
  
  if(is_dir("$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$dossier_source/$msg"))
  {
      $is_dir=1;

      $nom_rep="$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$dossier_source/$msg";
    
      if(is_file("$nom_rep/$msg".".1"))
         $nom_fichier="$msg".".1";
      elseif(is_file("$nom_rep/$msg".".0"))
         $nom_fichier="$msg".".0";
    
      $path_complet="$nom_rep/$nom_fichier";
  }
  else
  {
      $is_dir=0;
      $nom_rep="$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$dossier_source";
      $nom_fichier=$msg;
      $path_complet="$nom_rep/$nom_fichier";    
  }
  
   if(($array_file=@file($path_complet))!==FALSE)
   {
      // Récupération du contenu
      $msg_exp_id=trim($array_file["0"]);
      $msg_exp=trim($array_file["1"]);

      $msg_to_id=$destinataire_id;
      $msg_to=trim($array_file["3"]);

      $msg_sujet=stripslashes(trim($array_file["4"])) . " [Message transféré par $_SESSION[auth_prenom] $_SESSION[auth_nom]]";

      $msg_message=array_slice($array_file, 5);
      $msg_message_txt=stripslashes(implode($msg_message));

      // création du nouveau message
      $new_file_name=new_id() . ".0";

      $array_message=array("from_id"    => "$msg_exp_id\n",
                           "from"       => "$msg_exp\n",
                           "dest_id"    => "$destinataire_id\n",
                           "dest"       => "$dest_nom_prenom\n",
                           "sujet"       => "$msg_sujet\n",
                           "corps"       => "$msg_message_txt\n");

      if(!is_dir("$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_MSG_DEST_SOUS_REP/$destinataire_id/$GLOBALS[__MSG_INBOX]"))
      {
         if(FALSE===(mkdir("$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_MSG_DEST_SOUS_REP/$destinataire_id/$GLOBALS[__MSG_INBOX]", 0770, TRUE)))
         {
            mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de répertoire", "Utilisateur : $_SESSION[auth_nom] $_SESSION[auth_prenom]\n\nRépertoire : $GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_MSG_DEST_SOUS_REP/$destinataire_id/$GLOBALS[__MSG_INBOX]");
            die("Erreur lors de la création du dossier de réception du destinataire sélectionné.\n<br>Un message a été envoyé à l'administrateur.");
         }         
      }
    
    if($is_dir)
    {
         $dest_path="$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_MSG_DEST_SOUS_REP/$destinataire_id/$GLOBALS[__MSG_INBOX]/$msg/$new_file_name";
      
         if(FALSE===(mkdir("$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_MSG_DEST_SOUS_REP/$destinataire_id/$GLOBALS[__MSG_INBOX]/$msg", 0770, TRUE)))
         {
            mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de répertoire", "Utilisateur : $_SESSION[auth_nom] $_SESSION[auth_prenom]\n\nRépertoire : $GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_MSG_DEST_SOUS_REP/$destinataire_id/$GLOBALS[__MSG_INBOX]/$msg");
            die("Erreur lors de la création du dossier de réception du destinataire sélectionné.\n<br>Un message a été envoyé à l'administrateur.");
         }  
      } 
    else
         $dest_path="$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_MSG_DEST_SOUS_REP/$destinataire_id/$GLOBALS[__MSG_INBOX]/$new_file_name";

      if(FALSE===file_put_contents($dest_path, $array_message))
      {
         mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de copie de message", "Utilisateur : $_SESSION[auth_nom] $_SESSION[auth_prenom]\n\nMessage : $array_message");
         die("Erreur lors du transfert de votre message. Un message a été envoyé à l'administrateur.");
      }

      write_evt("", $GLOBALS["__EVT_ID_G_MSG"], "Message transféré : $_SESSION[auth_nom] $_SESSION[auth_prenom] => $dest_nom_prenom", $msg_exp_id);
   }
   else
      return FALSE;

   return 1;
}


function write_msg()
{
   $count_sent=0;

   // Messagerie Gestion => Candidats
   if(isset($_SESSION["auth_id"]))
   {
      $__DEST_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_ID=$_SESSION["auth_id"];
      $evenement_id=$GLOBALS["__EVT_ID_G_MSG"];
   }
   elseif(isset($_SESSION["authentifie"])) // Messagerie Candidats => Gestion
   {
      $__DEST_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_ID=$_SESSION["authentifie"];
      $evenement_id=$GLOBALS["__EVT_ID_C_MSG"];
      $candidat_id=$_SESSION["authentifie"];
   }
   else // Message système
   {
      $__DEST_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_ID=0;
      $evenement_id=$GLOBALS["__EVT_ID_G_MSG"];
   }
   
   // Arguments : 5 obligatoires
   // 0 : connexion à la BDD
   // 1 : expéditeur (array)
   // 2 : destinataire (array contenant au moins un array)
   // 3 : sujet
   // 4 : corps

   $num_args=func_num_args();

   if($num_args<5)
      die("Erreur : utilisation incorrecte de la fonction write_msg()");

   $foo=func_get_arg(0); // Obsolète : à supprimer

   $array_from=func_get_arg(1);
   $array_dests=func_get_arg(2);
   $sujet=stripslashes(func_get_arg(3));
   $corps=stripslashes(func_get_arg(4));

   if($num_args>5)
      $dest_nom_prenom=func_get_arg(5);

   // Notification par mail
   if($num_args>6)
      $flag_notification=func_get_arg(6);
   else
      $flag_notification=1;

   // Pièces jointes
   // Actuellement, les liens vers les pièces jointes sont directement dans le corps du message
   // S'il y a une pièce jointe, on crée un répertoire, sinon le message reste un fichier simple
   // TODO 1 : généraliser le répertoire pour tous les messages, même ceux sans PJ ? 
   // TODO 2 : utilité de détacher ces liens ? (gestion via le menu à la place du corps)
   if($num_args>7)
      $array_pj=func_get_arg(7);

   // On détermine dans quelle messagerie on se situe grâce aux variables de session
   // Messagerie Gestion => Candidats
   if($array_from["id"]!=0 && isset($_SESSION["auth_id"])) // Message de la gestion vers un candidat
   {
      $__DEST_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__DEST_DIR_REDIRECT=$GLOBALS["__REDIRECTION_CANDIDAT"];

      $__FROM_ID=$_SESSION["auth_id"];
      $__FROM_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR_REDIRECT=$GLOBALS["__REDIRECTION_GESTION"];
      
      $evenement_id=$GLOBALS["__EVT_ID_G_MSG"];
   }
   elseif($array_from["id"]!=0 && isset($_SESSION["authentifie"])) // Message d'un candidats vers la gestion
   {
      $__DEST_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__DEST_DIR_REDIRECT=$GLOBALS["__REDIRECTION_GESTION"];

      $__FROM_ID=$_SESSION["authentifie"];
      $__FROM_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR_REDIRECT=$GLOBALS["__REDIRECTION_CANDIDAT"];

      $evenement_id=$GLOBALS["__EVT_ID_C_MSG"];
      $candidat_id=$_SESSION["authentifie"];
   }
   elseif($array_from["id"]==0) // Message système vers un candidat
   {

      $__DEST_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_ID=0;
      $evenement_id=$GLOBALS["__EVT_ID_G_MSG"];
      $__DEST_DIR_REDIRECT=$GLOBALS["__REDIRECTION_CANDIDAT"];
      $__FROM_DIR_REDIRECT=$GLOBALS["__REDIRECTION_GESTION"];
   }

   // En cas de destinataires multiples, on modifie légèrement le titre du message pour la copie dans le dossier d'envoi
   if(count($array_dests)>1)
      $sujet_message_envoi=$sujet . " (copie d'un message à destinataires multiples)";
   else
      $sujet_message_envoi=$sujet;

   foreach($array_dests as $destinataire_array)
   {
      if(array_key_exists("id", $destinataire_array) && !empty($destinataire_array["id"]))
      {
         // Calcul du niveau supplémentaire dans l'arborescence des messages
         $_MSG_SOUS_REP=sous_rep_msg($destinataire_array["id"]);
      
         // Ecriture dans la messagerie gestion ...
         if(!is_dir("$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]"))
         {
            if(FALSE==mkdir("$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]", 0770, TRUE))
            {
               mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de répertoire", "Répertoire : $__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]");
               die("Erreur système lors de la création d'un répertoire. Un message a été envoyé à l'administrateur.");
            }

            if(FALSE===($redir_file=fopen("$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/index.php", "w+")))
            {
               mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de fichier", "Fichier : $__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/index.php");
               die("Erreur système lors de la création d'un fichier. Un message a été envoyé à l'administrateur.");
            }

            fwrite($redir_file, $__DEST_DIR_REDIRECT);
            fclose($redir_file);

            @copy("$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/index.php", "$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]/index.php");
         }


         // Récupération du nom du destinataire, si inconnu
         if(!array_key_exists("nom", $destinataire_array) || empty($destinataire_array["nom"])
            || !array_key_exists("prenom", $destinataire_array) || empty($destinataire_array["prenom"]))
         {
            if(isset($_SESSION["auth_id"])) // Gestion => Candidat
            {
               $res_dest=db_query($GLOBALS["dbr"], "SELECT $GLOBALS[_DBC_candidat_civilite], $GLOBALS[_DBC_candidat_nom],
                                                           $GLOBALS[_DBC_candidat_prenom], $GLOBALS[_DBC_candidat_email]
                                                        FROM $GLOBALS[_DB_candidat]
                                                    WHERE $GLOBALS[_DBC_candidat_id]='$destinataire_array[id]'");

               if(db_num_rows($res_dest))
               {
                  list($destinataire_array["civ"], $destinataire_array["nom"], $destinataire_array["prenom"], $destinataire_array["email"])=db_fetch_row($res_dest, 0);

                  db_free_result($res_dest);
               }
            }
            elseif(isset($_SESSION["authentifie"])) // Candidat => Gestion
            {
               $res_dest=db_query($GLOBALS["dbr"], "SELECT $GLOBALS[_DBC_acces_nom], $GLOBALS[_DBC_acces_prenom]
                                                      FROM $GLOBALS[_DB_acces] WHERE $GLOBALS[_DBC_acces_id]='$destinataire_array[id]'");
               if(db_num_rows($res_dest))
               {
                  list($destinataire_array["nom"], $destinataire_array["prenom"])=db_fetch_row($res_dest, 0);

                  db_free_result($res_dest);
               }
            }
            else
               return -1; // erreur : identifiant introuvable
         }

         $array_message=array("from_id"    => "$__FROM_ID\n",
                              "from"       => "$array_from[nom] $array_from[prenom]\n",
                              "dest_id"    => "$destinataire_array[id]\n",
                              "dest"       => "$destinataire_array[nom] $destinataire_array[prenom]\n",
                              "sujet"       => "$sujet\n",
                              "corps"       => "$corps\n");

         $new_file_id=new_id();

         // Pièces jointes ? => répertoire, sinon fichier
         if(isset($array_pj) && is_array($array_pj) && count($array_pj))
         {
            mkdir("$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]/$new_file_id/files", 0770, TRUE);

            $fichier_destination="$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]/$new_file_id/$new_file_id.0";
         
            // On copie chaque PJ dans le répertoire destination
            foreach($array_pj as $array_file)
               @copy($array_file["file"], "$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]/$new_file_id/files/$array_file[realname]");
         }
         else
            $fichier_destination="$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]/$new_file_id.0";

         if(FALSE==file_put_contents($fichier_destination, $array_message))
         {
            mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur d'envoi de message", "Utilisateur : $array_from[prenom] $array_from[nom]\n\nMessage : $array_message");
            die("Erreur lors de l'envoi de votre message. Un message a été envoyé à l'administrateur.");
         }

         // Mail de notification de réception, si le destinataire est un candidat et si le flag est ok
         if($flag_notification && $__DEST_DIR==$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"] && strstr($destinataire_array["email"], "@")) // test très très sommaire ...
         {
            if(isset($array_from["composante"]))
               $comp_nom=$array_from["composante"];
            elseif(isset($_SESSION["composante"]))
               $comp_nom=$_SESSION["composante"];
            else
               $comp_nom="";

            if(isset($array_from["universite"]))
               $univ_nom=$array_from["universite"];
            elseif(isset($_SESSION["universite"]))
               $univ_nom=$_SESSION["universite"];
            else
               $univ_nom="";

            $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_NOREPLY]\r\nReply-To: $GLOBALS[__EMAIL_NOREPLY]\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";

            // TODO URGENT déc. 2008 : intégrer le contenu de ce message dans la BDD
            $corps_message="============================================================\nCeci est un message automatique, merci de ne pas y répondre.\n============================================================\n\nBonjour $destinataire_array[civ] $destinataire_array[nom], \n\nVous avez reçu un message sur l'interface de Candidatures $GLOBALS[__COURRIEL_ETABLISSEMENT_SOURCE].\n\nSujet : $sujet\n\nPour lire ce message, connectez vous à l'adresse suivante : \n\n$GLOBALS[__URL_CANDIDAT]\n\nUtilisez votre identifiant et votre mot de passe pour vous authentifier, puis cliquez sur le menu \"Messagerie\".\n\nBien cordialement,\n\n\n$comp_nom\n$GLOBALS[__SIGNATURE_COURRIELS]";
            $ret=mail($destinataire_array["email"], "Candidatures - Notification de réception", $corps_message, $headers);
         }
         

         // Copie unique dans le dossier "Envoyés" (copie effectuée lors du premier envoi : $count_sent=0)
         // Les pièces jointes éventuelles sont également copiées
         // Pas de copie si l'expéditeur est le Système (on a déjà des logs normaux, par mail)
         // TODO : y réfléchir
         if($count_sent==0 && $__FROM_ID!=0)
         {
            // Calcul du niveau supplémentaire dans l'arborescence des messages
            $_MSG_FROM_SOUS_REP=sous_rep_msg($__FROM_ID);

            if(!is_dir("$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]"))
            {
               if(FALSE==mkdir("$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]", 0770, TRUE))
               {
                  mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de répertoire", "Répertoire : $__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]");
                  die("Erreur système lors de la création d'un répertoire. Un message a été envoyé à l'administrateur.");
               }

               if(FALSE===($redir_file=fopen("$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/index.php", "w+")))
               {
                  mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de fichier", "Fichier : $__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/index.php");
                  die("Erreur système lors de la création d'un fichier. Un message a été envoyé à l'administrateur.");
               }

               fwrite($redir_file, $__FROM_DIR_REDIRECT);
               fclose($redir_file);

               @copy("$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/index.php", "$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]/index.php");
            }

            $new_file_id=new_id();

            if(isset($array_pj) && is_array($array_pj) && count($array_pj))
            {
               mkdir("$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]/$new_file_id/files", 0770, TRUE);

               $fichier_destination="$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]/$new_file_id/$new_file_id.1";
            
               // On copie chaque PJ dans le répertoire destination
               foreach($array_pj as $array_file)
                  @copy($array_file["file"], "$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]/$new_file_id/files/$array_file[realname]");
            }
            else
               $fichier_destination="$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]/$new_file_id.1";

            // Sujet modifié
            $array_message["sujet"]="$sujet_message_envoi\n";

            if(FALSE==file_put_contents($fichier_destination, $array_message))
            {
               $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_NOREPLY]\r\nReply-To: $GLOBALS[__EMAIL_NOREPLY]\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";

               mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de copie d'un message dans le dossier Envoyés", "Utilisateur : $array_from[prenom] $array_from[nom]\n\nMessage : " . $array_message, $headers);
               die("Erreur lors de la copie du message dans le dossier \"Envoyés\". Un message a été envoyé à l'administrateur.");
            }
         }

         // Historique (Normalement le test de vérification est toujours vrai)
         if(isset($destinataire_array["nom"]) && isset($destinataire_array["prenom"]) && isset($array_from["nom"]) && isset($array_from["prenom"]))
         {
            if(isset($_SESSION["auth_id"]) || $__FROM_ID==0)
               $candidat_id=$destinataire_array["id"];

            write_evt("", $evenement_id, "Message : $array_from[nom] $array_from[prenom] => $destinataire_array[nom] $destinataire_array[prenom]", $candidat_id);
         }

         $count_sent++;
      }
   }
/*
   if(isset($flag_dec) && $flag_dec==1 && !db_connection_status($db))
      db_close($db);
*/
   return $count_sent;
}

// Variante plus efficace (utilisation à généraliser, sans doute)
function write_msg_2()
{
   $count_sent=0;

   // Messagerie Gestion => Candidats
   if(isset($_SESSION["auth_id"]))
   {
      $__DEST_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_ID=$_SESSION["auth_id"];
      $evenement_id=$GLOBALS["__EVT_ID_G_MSG"];
   }
   elseif(isset($_SESSION["authentifie"])) // Messagerie Candidats => Gestion
   {
      $__DEST_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_ID=$_SESSION["authentifie"];
      $evenement_id=$GLOBALS["__EVT_ID_C_MSG"];
      $candidat_id=$_SESSION["authentifie"];
   }
   else // Message système
   {
      $__DEST_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_ID=0;
      $evenement_id=$GLOBALS["__EVT_ID_G_MSG"];
   }
   
   // Arguments : 5 obligatoires
   // 0 : connexion à la BDD
   // 1 : expéditeur (array)
   // 2 : destinataire (array contenant au moins un array)
   // 3 : sujet
   // 4 : corps

   $num_args=func_num_args();

   if($num_args<5)
      die("Erreur : utilisation incorrecte de la fonction write_msg()");

   $foo=func_get_arg(0); // Obsolète : à supprimer

   $array_from=func_get_arg(1);
   $array_dests=func_get_arg(2);
   $sujet=stripslashes(func_get_arg(3));
   $corps=stripslashes(func_get_arg(4));

   // Obsolète
   if($num_args>5)
      $dest_nom_prenom=func_get_arg(5);

   // Notification par mail
   if($num_args>6)
      $flag_notification=func_get_arg(6);
   else
      $flag_notification=1;

   // Pièces jointes
   // Actuellement, les liens vers les pièces jointes sont directement dans le corps du message
   // S'il y a une pièce jointe, on crée un répertoire, sinon le message reste un fichier simple
   // TODO 1 : généraliser le répertoire pour tous les messages, même ceux sans PJ ? 
   // TODO 2 : utilité de détacher ces liens ? (gestion via le menu à la place du corps)
   if($num_args>7)
      $array_pj=func_get_arg(7);

   // On détermine dans quelle messagerie on se situe grâce aux variables de session

   // SOURCE
   if($array_from["src_type"]=="candidat")
   {
      $__FROM_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR_REDIRECT=$GLOBALS["__REDIRECTION_CANDIDAT"];
   }
   else
   {
      $__FROM_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__FROM_DIR_REDIRECT=$GLOBALS["__REDIRECTION_GESTION"];
   }
   
   $__FROM_ID=$array_from["id"];

   // En cas de destinataires multiples, on modifie légèrement le titre du message pour la copie dans le dossier d'envoi
   if(count($array_dests)>1)
      $sujet_message_envoi=$sujet . " (copie d'un message à destinataires multiples)";
   else
      $sujet_message_envoi=$sujet;

   foreach($array_dests as $destinataire_array)
   {
      // DESTINATION
      if(array_key_exists("dest_type", $destinataire_array) && ($destinataire_array["dest_type"]=="candidat" || $destinataire_array["dest_type"]=="gestion"))
      {
         if($destinataire_array["dest_type"]=="candidat")
         {
            $__DEST_DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
            $__DEST_DIR_REDIRECT=$GLOBALS["__REDIRECTION_CANDIDAT"];

            $evenement_id=$GLOBALS["__EVT_ID_C_MSG"];
         }
         else
         {
            $__DEST_DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
            $__DEST_DIR_REDIRECT=$GLOBALS["__REDIRECTION_GESTION"];

            $evenement_id=$GLOBALS["__EVT_ID_G_MSG"];
         }

         if(array_key_exists("id", $destinataire_array) && $destinataire_array["id"]!=="")
         {
            // Niveau supplémentaire dans l'arborescence des messages
            $_MSG_SOUS_REP=sous_rep_msg($destinataire_array["id"]);
            
            if(!is_dir("$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]"))
            {
               if(FALSE==mkdir("$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]", 0770, TRUE))
               {
                  mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de répertoire", "Répertoire : $__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]");
                  die("Erreur système lors de la création d'un répertoire. Un message a été envoyé à l'administrateur.");
               }

               if(FALSE===($redir_file=fopen("$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/index.php", "w+")))
               {
                  mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de fichier", "Fichier : $__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/index.php");
                  die("Erreur système lors de la création d'un fichier. Un message a été envoyé à l'administrateur.");
               }

               fwrite($redir_file, $__DEST_DIR_REDIRECT);
               fclose($redir_file);

               @copy("$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/index.php", "$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]/index.php");
            }

            // Récupération du nom du destinataire, si inconnu
            if(!array_key_exists("nom", $destinataire_array) || empty($destinataire_array["nom"])
               || !array_key_exists("prenom", $destinataire_array) || empty($destinataire_array["prenom"]))
            {
               if($destinataire_array["dest_type"]=="candidat") // Destinataire : candidat
               {
                  $res_dest=db_query($GLOBALS["dbr"], "SELECT $GLOBALS[_DBC_candidat_civilite], $GLOBALS[_DBC_candidat_nom],
                                                            $GLOBALS[_DBC_candidat_prenom], $GLOBALS[_DBC_candidat_email]
                                                         FROM $GLOBALS[_DB_candidat]
                                                      WHERE $GLOBALS[_DBC_candidat_id]='$destinataire_array[id]'");

                  if(db_num_rows($res_dest))
                  {
                     list($destinataire_array["civ"], $destinataire_array["nom"], $destinataire_array["prenom"], $destinataire_array["email"])=db_fetch_row($res_dest, 0);

                     db_free_result($res_dest);
                  }
               }
               elseif($destinataire_array["dest_type"]=="gestion") // Destinataire : gestionnaire
               {
                  $res_dest=db_query($GLOBALS["dbr"], "SELECT $GLOBALS[_DBC_acces_nom], $GLOBALS[_DBC_acces_prenom]
                                                      FROM $GLOBALS[_DB_acces] WHERE $GLOBALS[_DBC_acces_id]='$destinataire_array[id]'");
                  if(db_num_rows($res_dest))
                  {
                     list($destinataire_array["nom"], $destinataire_array["prenom"])=db_fetch_row($res_dest, 0);

                     db_free_result($res_dest);
                  }
               }
               else
                  return -1; // erreur : identifiant introuvable
            }

            $array_message=array("from_id"    => "$__FROM_ID\n",
                                 "from"       => "$array_from[nom] $array_from[prenom]\n",
                                 "dest_id"    => "$destinataire_array[id]\n",
                                 "dest"       => "$destinataire_array[nom] $destinataire_array[prenom]\n",
                                 "sujet"       => "$sujet\n",
                                 "corps"       => "$corps\n");

            $new_file_id=new_id();

            // Pièces jointes ? => répertoire, sinon fichier
            if(isset($array_pj) && is_array($array_pj) && count($array_pj))
            {
               mkdir("$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]/$new_file_id/files", 0770, TRUE);

               $fichier_destination="$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]/$new_file_id/$new_file_id.0";
            
               // On copie chaque PJ dans le répertoire destination
               foreach($array_pj as $array_file)
                  @copy($array_file["file"], "$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]/$new_file_id/files/$array_file[realname]");
            }
            else
               $fichier_destination="$__DEST_DIR/$_MSG_SOUS_REP/$destinataire_array[id]/$GLOBALS[__MSG_INBOX]/$new_file_id.0";

            if(FALSE==file_put_contents($fichier_destination, $array_message))
            {
               if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
               {
                  mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur d'envoi de message\nUtilisateur : $array_from[prenom] $array_from[nom]\n\nMessage : $array_message");
                  die("Erreur lors de l'envoi de votre message. Un courriel a été envoyé à l'administrateur.");
               }
               else
                  die("Erreur lors de l'envoi de votre message. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
            }

            // Mail de notification de réception, si le destinataire est un candidat et si le flag est ok
            if($flag_notification && $destinataire_array["dest_type"]=="candidat" && strstr($destinataire_array["email"], "@")) // test très très sommaire ...
            {
               if(isset($array_from["composante"]))
                  $comp_nom=$array_from["composante"];
               elseif(isset($_SESSION["composante"]))
                  $comp_nom=$_SESSION["composante"];
               else
                  $comp_nom="";

               if(isset($array_from["universite"]))
                  $univ_nom=$array_from["universite"];
               elseif(isset($_SESSION["universite"]))
                  $univ_nom=$_SESSION["universite"];
               else
                  $univ_nom="";

               $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_NOREPLY]\r\nReply-To: $GLOBALS[__EMAIL_NOREPLY]\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";

               // TODO URGENT déc. 2008 : intégrer le contenu de ce message dans la BDD
               $corps_message="============================================================\nCeci est un message automatique, merci de ne pas y répondre.\n============================================================\n\nBonjour $destinataire_array[civ] $destinataire_array[nom], \n\nVous avez reçu un message sur l'interface de Candidatures $GLOBALS[__COURRIEL_ETABLISSEMENT_SOURCE].\n\nSujet : $sujet\n\nPour lire ce message, connectez vous à l'adresse suivante : \n\n$GLOBALS[__URL_CANDIDAT]\n\nUtilisez votre identifiant et votre mot de passe pour vous authentifier, puis cliquez sur le menu \"Messagerie\".\n\nBien cordialement,\n\n\n$comp_nom\n$GLOBALS[__SIGNATURE_COURRIELS]";
               $ret=mail($destinataire_array["email"], "Candidatures - Notification de réception", $corps_message, $headers);
            }
            

            // Copie unique dans le dossier "Envoyés" (copie effectuée lors du premier envoi : $count_sent=0)
            // Les pièces jointes éventuelles sont également copiées
            // Pas de copie si l'expéditeur est le Système (on a déjà des logs normaux, par mail)
            // TODO : y réfléchir
            if($count_sent==0 && $__FROM_ID!=0)
            {
               // Niveau supplémentaire dans l'arborescence des messages
               $_MSG_FROM_SOUS_REP=sous_rep_msg($__FROM_ID);
            
               if(!is_dir("$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]"))
               {
                  if(FALSE==mkdir("$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]", 0770, TRUE))
                  {
                     mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de répertoire", "Répertoire : $__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]");
                     die("Erreur système lors de la création d'un répertoire. Un message a été envoyé à l'administrateur.");
                  }

                  if(FALSE===($redir_file=fopen("$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/index.php", "w+")))
                  {
                     mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de fichier", "Fichier : $__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/index.php");
                     die("Erreur système lors de la création d'un fichier. Un message a été envoyé à l'administrateur.");
                  }

                  fwrite($redir_file, $__FROM_DIR_REDIRECT);
                  fclose($redir_file);

                  @copy("$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/index.php", "$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]/index.php");
               }

               $new_file_id=new_id();

               if(isset($array_pj) && is_array($array_pj) && count($array_pj))
               {
                  mkdir("$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]/$new_file_id/files", 0770, TRUE);

                  $fichier_destination="$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]/$new_file_id/$new_file_id.1";
               
                  // On copie chaque PJ dans le répertoire destination
                  foreach($array_pj as $array_file)
                     @copy($array_file["file"], "$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]/$new_file_id/files/$array_file[realname]");
               }
               else
                  $fichier_destination="$__FROM_DIR/$_MSG_FROM_SOUS_REP/$__FROM_ID/$GLOBALS[__MSG_SENT]/$new_file_id.1";
               
               // Sujet modifié
               $array_message["sujet"]="$sujet_message_envoi\n";
               
               if(FALSE==file_put_contents($fichier_destination, $array_message))
               {
                  $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_NOREPLY]\r\nReply-To: $GLOBALS[__EMAIL_NOREPLY]\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";

                  mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de copie d'un message dans le dossier Envoyés", "Utilisateur : $array_from[prenom] $array_from[nom]\n\nMessage : " . $array_message, $headers);
                  die("Erreur lors de la copie du message dans le dossier \"Envoyés\". Un message a été envoyé à l'administrateur.");
               }
            }

            // Historique (Normalement le test de vérification est toujours vrai)
            if(isset($destinataire_array["nom"]) && isset($destinataire_array["prenom"]) && isset($array_from["nom"]) && isset($array_from["prenom"]))
            {
               if($destinataire_array["dest_type"]=="candidat")
                  $candidat_id=$destinataire_array["id"];
               else
                  $candidat_id="";

               write_evt("", $evenement_id, "Message : $array_from[nom] $array_from[prenom] => $destinataire_array[nom] $destinataire_array[prenom]", $candidat_id);
            }

            $count_sent++;
         }
      }
   }
/*
   if(isset($flag_dec) && $flag_dec==1 && !db_connection_status($db))
      db_close($db);
*/
   return $count_sent;
}

// Vérification de la présence de nouveaux messages
// La fonction retourne le nombre de nouveaux messages
function check_messages()
{
   // Fichiers de messagerie de la partie Gestion
   if(isset($_SESSION["auth_id"]))
   {
      $DIR=$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"];
      $__DIR_REDIRECT=$GLOBALS["__REDIRECTION_GESTION"];

      $ID=$_SESSION["auth_id"];
   }
   elseif(isset($_SESSION["authentifie"])) // Fichiers de messagerie de la partie Candidats
   {
      $DIR=$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"];
      $__DIR_REDIRECT=$GLOBALS["__REDIRECTION_CANDIDAT"];

      $ID=$_SESSION["authentifie"];
   }
   else
      return 0;

   // Nombre de nouveaux messages, quel que soit le dossier
   $count_new=0;

   foreach($GLOBALS["__MSG_DOSSIERS"] as $dossier_id => $nom_dossier)
   {
      // Vérification de l'existence de chaque dossier et création s'ils n'existent pas
      if(!is_dir("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id"))
      {
         if(FALSE==mkdir("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id", 0770, TRUE))
         {
            mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de répertoire", "Répertoire : $DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id");
            die("Erreur système lors de la création d'un répertoire. Un message a été envoyé à l'administrateur.");
         }
         else
         {
            if(FALSE===($redir_file=fopen("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/index.php", "w+")))
            {
               mail($GLOBALS["__EMAIL_ADMIN"], "[Précandidatures] - Erreur de création de fichier", "Fichier : $DIR/$_SESSION[MSG_SOUS_REP]/$ID/index.php");
               die("Erreur système lors de la création d'un fichier. Un message a été envoyé à l'administrateur.");
            }

            fwrite($redir_file, $__DIR_REDIRECT);
            fclose($redir_file);

            @copy("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/index.php", "$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id/index.php");
         }
      }

      $array_dir=scandir("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id");

      // Décompte des messages non lus (fichiers se terminant par .0)
      foreach($array_dir as $element)
      {
         if(is_file("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id/$element") && substr($element, -1)=="0")
            $count_new++;
         elseif(is_dir("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id/$element") && $element!="." && $element!="..")   // Message avec pièces jointes = sous répertoire
         {
            $array_subdir=scandir("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id/$element");

            foreach($array_subdir as $sub_element)
            {
               if(is_file("$DIR/$_SESSION[MSG_SOUS_REP]/$ID/$dossier_id/$element/$sub_element") && substr($sub_element, -1)=="0")
                  $count_new++;
            }
         }
      }
   }

   return $count_new;
}

// Remplace les macros de type [macros...]texte[/macros] par l'équivalent HTML dans divers paragraphes

function parse_macros($txt)
{
   setlocale(LC_ALL, "fr_FR");
   $new_txt="";

   // Liens HTML
   // $txt=preg_replace("/\[lien=([[:alpha:]]+:\/\/[^<>[:space:]]*[[:alnum:]]*)\](.*)?\[\/lien\]/", "<a class='lien2' target='_blank' href=\"\\1\">\\2</a>", $txt);
   
//   foreach(preg_split("/(\[lien=[[:alpha:]]+:\/\/[^<>[:space:]]*[[:alnum:]]*\].*?\[\/lien\])/", $txt, -1, PREG_SPLIT_DELIM_CAPTURE) as $texte)
//   {
//      // Transformation des url au format [lien=...]description[/lien]
//      if(preg_match("/\[lien=[[:alpha:]]+:\/\/[^<>[:space:]]*[[:alnum:]]*\].*?\[\/lien\]/", $texte))
//         $new_txt.=preg_replace("/\[lien=([[:alpha:]]+:\/\/[^<>[:space:]]*[[:alnum:]]*)\](.*)?\[\/lien\]/", "<a class='lien2' target='_blank' href=\"\\1\">\\2</a>", $texte);         
//      // Transformation des url brutes en liens
//      elseif(!preg_match("/href=/i", $texte)) 
//         $new_txt.=preg_replace("/[[:alpha:]]+:\/\/[^<>[:space:]]*[[:alnum:]\/]*/", "<a class='lien2' target='_blank' href=\"\\0\">\\0</a>", $texte);
//      else
//         $new_txt.=$texte;
//   }
/*
   if(isset($new_txt))
   {
      $txt=$new_txt;
      $new_txt="";
   }
*/

   $new_txt=preg_replace("/\[lien=([[:alpha:]]+:\/\/[^<>[:space:]]*[[:alnum:]]*)\](.*)?\[\/lien\]/U", "<a class='lien2_macro' target='_blank' href=\"\\1\">\\2</a>", $txt);

   // Mails
   
   // $new_txt=preg_replace("/[[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]\/]*/U", "<a class='lien2' href=\"mailto:\\0\">\\0</a>", $new_txt);
   
//   $new_txt=preg_replace("/[[^\[mail=][:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]\/]*/U", "<a class='lien2' href=\"mailto:\\0\">\\0</a>", $txt);
   
   $txt=$new_txt;
   
   $new_txt=preg_replace("/\[mail=([[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]]*)\]([[:print:][^\[\/mail\]]*)\[\/mail\]/U", "<a class='lien2_macro' href=\"mailto:\\1\">\\2</a>", $txt);   
   
//   $txt=$new_txt;
   


//   foreach(preg_split("/(\[mail=[[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]]*\][[:print:][^\[\/mail\]]*)\[\/mail\]/", $txt, -1, PREG_SPLIT_DELIM_CAPTURE) as $texte)
//   {
//      if(preg_match("/\[mail=[[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]]*\][[:print:][^\[\/mail\]]*\[\/mail\]/", $texte))
////       $new_txt.=preg_replace("/\[mail=([[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]]*)\]([[:punct:][:alnum:][^\[\/mail\]]*)\[\/mail\]/", "<a class='lien2' href=\"mailto:\\1\">\\2</a>", $texte);
//         $new_txt.=preg_replace("/\[mail=([[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]]*)\]([[:print:][^\[\/mail\]]*)\[\/mail\]/", "<a class='lien2' href=\"mailto:\\1\">\\2</a>", $texte);
//      elseif(!preg_match("/mailto/i", $texte) && !preg_match("/value=['|\"][[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]]*['|\"]/", $texte))
//         $new_txt.=preg_replace("/[[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]\/]*/", "<a class='lien2' href=\"mailto:\\0\">\\0</a>", $texte);
//      else
//         $new_txt.=$texte;
//   }

//   foreach(preg_split("/(\[mail=[[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]]*\].*?\[\/mail\])/", $txt, -1, PREG_SPLIT_DELIM_CAPTURE) as $texte)
//   {
      // Transformation des mails au format [mail=...]description[/lien]
//      if(preg_match("/\[mail=[[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]]*\].*?\[\/mail\]/", $texte))
//         $new_txt.=preg_replace("/\[mail=([[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]]*)\](.*)?\[\/mail\]/", "<a class='lien2' href=\"mailto:\\1\">\\2</a>", $texte);
//      elseif(!preg_match("/mailto/i", $texte)) // Transformation des emails bruts en liens
//         $new_txt.=preg_replace("/[[:alnum:][:punct:]]+@[^<>[:space:]]*[[:alnum:]\/]*/", "<a class='lien2' href=\"mailto:\\0\">\\0</a>", $texte);
// //         $new_txt.=preg_replace("/[^<>[:punct]]*[[:alnum:]]+@[^<>[:space:]]*[[:alnum:]]+/", "<a class='lien2' href=\"mailto:\\0\">\\0</a>", $texte);
//      else
//         $new_txt.=$texte;
//   }

   if(isset($new_txt))
   {
      $txt=$new_txt;
      $new_txt="";
   }

   // Mails et Lien bruts
/*
   $txt=preg_replace("/[[:alpha:]]+\:\/\/[^<>[:space:]]+[[:alnum:]\/]/", "<a class='lien2' target='_blank' href=\"\\0\">\\0</a>", $txt);
   $txt=preg_replace("/[[:alnum:]]+@[^<>[:space:]]+[[:alnum:]]/", "<a class='lien_bleu_10' href=\"mailto:\\0\">\\0</a>", $txt);
*/
   // Répertoires
   // ATTENTION : pas de remplacement automatique de ###[A-Z]*### par $GLOBALS["A-Z"] car il faut
   // impérativement contrôler les variables utilisables dans les messages.
   // => Les utilisateurs ne DOIVENT PAS pouvoir exploiter cette fonction pour afficher une variable
   $new_txt=preg_replace("/###__PUBLIC_DIR###/i", $GLOBALS["__PUBLIC_DIR"], $txt);
   $new_txt=preg_replace("/###__CAND_DIR###/i", $GLOBALS["__CAND_DIR"], $new_txt);

   // Mise en forme HTML
   $new_txt=preg_replace("/\[italique\]/i", "<i>", $new_txt);
   $new_txt=preg_replace("/\[\/italique\]/i", "</i>", $new_txt);

   $new_txt=preg_replace("/\[souligner\]/i", "<u>", $new_txt);
   $new_txt=preg_replace("/\[\/souligner\]/i", "</u>", $new_txt);

   $new_txt=preg_replace("/\[gras\]/i", "<strong>", $new_txt);
   $new_txt=preg_replace("/\[\/gras\]/i", "</strong>", $new_txt);

   $new_txt=preg_replace("/\[centrer\]/i", "<center>", $new_txt);
   $new_txt=preg_replace("/\[\/centrer\]/i", "</center>", $new_txt);

   $new_txt=preg_replace("/\[important\]/i", "<font class='Texte_important'>", $new_txt);
   $new_txt=preg_replace("/\[\/important\]/i", "</font>", $new_txt);

//   $new_txt=preg_replace("/%signature%/i", $GLOBALS["__SIGNATURE_COURRIELS"], $new_txt);
   $new_txt=preg_replace("/\[signature\]/i", $GLOBALS["__SIGNATURE_COURRIELS"], $new_txt);

   return $new_txt;
}

// JUSTIFICATIFS ET RECAPITULATIFS
function send_recap_justifs()
{
   // arg 0 : obligatoire : id du candidat
   // arg 1 : obligatoire : id de la composante
   // arg 2 : facultatif : id de la candidature. Si absent, on prend toutes les candidatures enregistrées dans la composante

   $numargs = func_num_args();

   if($numargs<2)
      return 0;

   $candidat_id=func_get_arg(0);
   $composante_id=func_get_arg(1);

   if($numargs==3)
   {
      $candidature_id=func_get_arg(2);
      $condition_candidature="AND $GLOBALS[_DBC_cand_id]='$candidature_id'";
   }
   else
      $condition_candidature="";

   $result=db_query($GLOBALS["dbr"],"SELECT $GLOBALS[_DBC_candidat_id], $GLOBALS[_DBC_candidat_civilite], $GLOBALS[_DBC_candidat_nom], $GLOBALS[_DBC_candidat_prenom],
                                 $GLOBALS[_DBC_candidat_date_naissance], $GLOBALS[_DBC_candidat_email], $GLOBALS[_DBC_cand_id], $GLOBALS[_DBC_cand_lockdate], 
                                 $GLOBALS[_DBC_composantes_id], $GLOBALS[_DBC_composantes_nom], $GLOBALS[_DBC_composantes_scolarite], $GLOBALS[_DBC_composantes_courriel_scol], 
                                 $GLOBALS[_DBC_annees_annee], $GLOBALS[_DBC_specs_nom], $GLOBALS[_DBC_propspec_id], $GLOBALS[_DBC_propspec_finalite], 
                                 $GLOBALS[_DBC_session_fermeture], $GLOBALS[_DBC_session_reception], $GLOBALS[_DBC_propspec_frais],
                                 $GLOBALS[_DBC_cand_groupe_spec], $GLOBALS[_DBC_cand_ordre_spec], $GLOBALS[_DBC_universites_nom]
                              FROM $GLOBALS[_DB_candidat], $GLOBALS[_DB_composantes], $GLOBALS[_DB_universites], $GLOBALS[_DB_propspec], $GLOBALS[_DB_annees], $GLOBALS[_DB_specs],
                                   $GLOBALS[_DB_session], $GLOBALS[_DB_cand]
                           WHERE $GLOBALS[_DBC_composantes_univ_id]=$GLOBALS[_DBC_universites_id]
                           AND $GLOBALS[_DBC_candidat_id]=$GLOBALS[_DBC_cand_candidat_id]
                           AND $GLOBALS[_DBC_propspec_id]=$GLOBALS[_DBC_cand_propspec_id]
                           AND $GLOBALS[_DBC_propspec_id]=$GLOBALS[_DBC_session_propspec_id]
                           AND $GLOBALS[_DBC_annees_id]=$GLOBALS[_DBC_propspec_annee]
                           AND $GLOBALS[_DBC_propspec_id_spec]=$GLOBALS[_DBC_specs_id]
                           AND $GLOBALS[_DBC_cand_session_id]=$GLOBALS[_DBC_session_id]
                           AND $GLOBALS[_DBC_composantes_id]=$GLOBALS[_DBC_propspec_comp_id]
                           AND $GLOBALS[_DBC_cand_periode]='$GLOBALS[__PERIODE]'
                           AND $GLOBALS[_DBC_session_periode]='$GLOBALS[__PERIODE]'
                           AND $GLOBALS[_DBC_composantes_id]='$composante_id'
                           AND $GLOBALS[_DBC_candidat_id]='$candidat_id'
                           $condition_candidature
                           AND $GLOBALS[_DBC_cand_lock]='1'
                              ORDER BY $GLOBALS[_DBC_candidat_id], $GLOBALS[_DBC_propspec_comp_id], $GLOBALS[_DBC_cand_groupe_spec], $GLOBALS[_DBC_cand_ordre_spec]");

   $rows=db_num_rows($result);

   if($rows)
   {
      $old_candidat_id="";

      // message spécifique à la composante ?
      $res_message=db_query($GLOBALS["dbr"], "SELECT $GLOBALS[_DBC_messages_contenu] FROM $GLOBALS[_DB_messages]
                                   WHERE $GLOBALS[_DBC_messages_type]='$GLOBALS[__MSG_TYPE_VERROUILLAGE]'
                                   AND $GLOBALS[_DBC_messages_comp_id]='$composante_id'
                                   AND $GLOBALS[_DBC_messages_actif]='t'");
                                         
      if(db_num_rows($res_message))
         list($corps_message_composante)=db_fetch_row($res_message, 0);
      else
         $corps_message_composante=$GLOBALS["__MSG_TYPES"][$GLOBALS["__MSG_TYPE_VERROUILLAGE"]]['defaut'];
         
      db_free_result($res_message);

      for($i=0; $i<$rows; $i++) // boucle for() sur les candidature du candidat passé en paramètre, dans la composante également en paramètre
      {
         list($candidat_id,$cand_civ,$cand_nom,$cand_prenom,$cand_naissance,$cand_email, $cand_id, $cand_lockdate, $comp_id, 
              $comp_nom, $adr_scol, $courriel_scol, $annee, $spec_nom, $propspec_id, $finalite, $date_fermeture, $date_reception, $frais,
              $groupe_spec, $ordre_spec, $univ_nom)=db_fetch_row($result,$i);

         $formation=$annee=="" ? "$spec_nom" : "$annee $spec_nom";
         $formation.=$GLOBALS['tab_finalite'][$finalite]=="" ? "" : " " . $GLOBALS['tab_finalite'][$finalite];

         switch($cand_civ)
         {
            case "M" :       $ne_le="Né le";
                           $civ_mail="M.";
                           break;

            case   "Mlle" :   $ne_le="Née le";
                           $civ_mail="Mlle";
                           break;

            case   "Mme"   :    $ne_le="Née le";
                           $civ_mail="Mme";
                           break;

            default      :   $ne_le="Né le";
                           $civ_mail="M.";
         }

         // ================================================================
         //          JUSTIFICATIFS A ENVOYER : 1 message par voeu
         // ================================================================

         // Cette requête est uniquement faite pour vérifier la présence de justificatifs pour cette formation
         // TODO 8/1/2008 : SIMPLIFIER en intégrant dans la requête globale ? (avec un CASE)
         $result3=db_query($GLOBALS["dbr"], "SELECT $GLOBALS[_DBC_justifs_id], $GLOBALS[_DBC_justifs_titre], $GLOBALS[_DBC_justifs_texte],
                                          $GLOBALS[_DBC_justifs_jf_nationalite]
                                    FROM $GLOBALS[_DB_justifs], $GLOBALS[_DB_justifs_jf]
                                    WHERE $GLOBALS[_DBC_justifs_jf_propspec_id]='$propspec_id'
                                    AND $GLOBALS[_DBC_justifs_jf_justif_id]=$GLOBALS[_DBC_justifs_id]
                                    ORDER BY $GLOBALS[_DBC_justifs_jf_ordre]");

         $rows3=db_num_rows($result3);
         db_free_result($result3);

         if(!$rows3) // Aucun élément : on prévient l'administrateur
            $justificatifs_vides[$propspec_id]="$comp_id - $formation\n";
         else
         {
            // Autres fichiers liés aux justificatifs
            $result4=db_query($GLOBALS["dbr"], "SELECT distinct($GLOBALS[_DBC_justifs_fichiers_nom])
                                       FROM $GLOBALS[_DB_justifs_fichiers], $GLOBALS[_DB_justifs_ff]
                                       WHERE $GLOBALS[_DBC_justifs_fichiers_id]=$GLOBALS[_DBC_justifs_ff_fichier_id]
                                       AND $GLOBALS[_DBC_justifs_ff_propspec_id]='$propspec_id'
                                       AND $GLOBALS[_DBC_justifs_fichiers_comp_id]='$comp_id'");

            $rows4=db_num_rows($result4);

            if($rows4)
            {
               $liste_fichiers="";

               for($l=0; $l<$rows4; $l++)
               {
                  list($fichier_nom)=db_fetch_row($result4, $l);

                  // On n'utilise pas de variables de chemins dans les messages, car si les chemins changent,
                  // les liens ne seront plus valides
                  // Solution : utilisation de la macro ###texte### le "texte" sera automatiquement remplacé par
                  // $GLOBALS[texte] lors de l'ouverture du message
                  if(is_file("$GLOBALS[__PUBLIC_DIR_ABS]/$comp_id/justificatifs/$fichier_nom"))
                     $liste_fichiers.="<br>- <a href='###__PUBLIC_DIR###/$comp_id/justificatifs/$fichier_nom' target='_blank' class='lien_bleu_12'><b>$fichier_nom</b></a>";
                  else
                  {
                     if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
                        $hdrs_err = array("From" => "$GLOBALS[__EMAIL_ADMIN]",
                                          "Subject" => "$GLOBALS[__ERREUR_SUJET] Erreur de fichier");
                     else
                        $hdrs_err = array("From" => "$courriel_scol",
                                          "Subject" => "$GLOBALS[__ERREUR_SUJET] Erreur de fichier");

                     mail($courriel_scol,"$GLOBALS[__ERREUR_SUJET] - Fichier non trouvé", "Bonjour,\n\nCeci est un message automatique de l'Application de Gestion des Candidatures en ligne.\n\nLors de l'envoi des Justificatifs, le fichier suivant n'a pu être trouvé sur le serveur : \n\nFichier : $GLOBALS[__PUBLIC_DIR_ABS]/$comp_id/justificatifs/$fichier_nom\n\n(Il est possible que ce fichier ait été supprimé par erreur, le candidat ne l'a alors pas reçu)\n\nUne copie de ce courriel a été envoyé à l'administrateur.\n\nCordialement,\n\nL'Application :)");

                     // Copie à l'admin si son adresse a été configurée
                     if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
                        mail($GLOBALS['__EMAIL_ADMIN'],"$GLOBALS[__ERREUR_SUJET] - Fichier non trouvé", "Lors de l'envoi des Justificatifs, le fichier suivant n'a pu être trouvé sur le serveur : \n\nFichier : $GLOBALS[__PUBLIC_DIR_ABS]/$comp_id/justificatifs/$fichier_nom\n\nCandidat : $civ_mail $cand_nom $cand_prenom\n\nUne copie de ce courriel a été envoyé à la scolarité concernée.\n\nCordialement,\n\nL'Application :)");
                  }
               }

               if($liste_fichiers!="")
               {
                  $les_fichiers_suivants=$rows4==1 ? "le fichier suivant" : "les fichiers suivants";
                  $les_pieces_jointes_suivantes=$rows4==1 ? "la pièce jointe suivante" : "les pièces jointes suivantes";

                  $corps_fichiers="4/ Vous devez également télécharger $les_fichiers_suivants et suivre les instructions : " . $liste_fichiers;
               }
               else
                  $corps_fichiers="";
            }
            else
               $corps_fichiers="";

            unset($liste_specs);

            // candidature à choix multiples ?
            if($groupe_spec!=-1)
            {
               $liste_specs=array("$cand_id" => array("annee" => "$annee",
                                                      "spec" => "$spec_nom",
                                                      "finalite" => "$finalite",
                                                      "formation" => "$formation"));

               // on regarde le nombre de candidatures concernées (elles sont triées dans la requete globale)
               // attention, algo un peu limite ...
               for($search=($i+1); $search<$rows; $search++)
               {
                  $next_groupe=db_fetch_result($result, $search, 19); //   /!\

                  // print("DBG : groupe : current : $groupe_spec Next : $next_groupe\n");

                  if($next_groupe!=$groupe_spec)
                     $search=$rows; // = break;
                  else
                  {
                     $next_cand_id=db_fetch_result($result, $search, 6);

                     $liste_specs[$next_cand_id]=array();

                     $liste_specs[$next_cand_id]["annee"]=db_fetch_result($result, $search, 12);
                     $liste_specs[$next_cand_id]["spec"]=db_fetch_result($result, $search, 13);
                     $liste_specs[$next_cand_id]["finalite"]=db_fetch_result($result, $search, 15);

                     $liste_specs[$next_cand_id]["formation"]=$liste_specs[$next_cand_id]["annee"]=="" ? $liste_specs[$next_cand_id]["spec"] : $liste_specs[$next_cand_id]["annee"] . " " . $liste_specs[$next_cand_id]["spec"];

                     $liste_specs[$next_cand_id]["formation"].=$GLOBALS["tab_finalite"][$liste_specs[$next_cand_id]["finalite"]]=="" ? "" : " " . $GLOBALS['tab_finalite'][$liste_specs[$next_cand_id]["finalite"]];
                  }
               }
            }

            // On a tout : verrouillage de la formation et envoi du message
            if(isset($liste_specs) && count($liste_specs)>1)
            {
               $sujet="[$comp_nom] - Renvoi des justificatifs - candidature à choix multiples";

               $corps_message="\nCandidature à choix multiples :\n";

               $nom_formation_corps="ce groupe de formations";

               foreach($liste_specs as $next_cand_id => $array_specs)
               {
                  $corps_message.="<b>- " . $liste_specs[$next_cand_id]["formation"] . "</b>\n";

                  // db_query($GLOBALS["dbr"],"UPDATE $GLOBALS[_DB_cand] SET $GLOBALS[_DBU_cand_lock]='1' WHERE $GLOBALS[_DBU_cand_id]='$next_cand_id'");

                  // on avance la boucle d'autant de candidatures que de choix multiples du même groupe
                  $i++;
               }
            }
            else
            {
               $sujet="[$comp_nom] - Renvoi des justificatifs et du récapitulatif - $formation";

               // db_query($GLOBALS["dbr"],"UPDATE $GLOBALS[_DB_cand] SET $GLOBALS[_DBU_cand_lock]='1' WHERE $GLOBALS[_DBU_cand_id]='$cand_id'");

               $corps_message="Candidature : <b>$formation</b>\n";

               $nom_formation_corps="la formation \"$formation\"";
            }

            $limite_reception_txt=date_fr("j F Y", $date_reception);

            $prefixe=$corps_message; // conservation de l'entête pour le message spécifique à la composante

            $corps_message.="\n\nBonjour $civ_mail ". ucwords(mb_strtolower(stripslashes($cand_nom))) .",

Le délai imparti pour modifier cette formation est échu. Après réception de l'ensemble des pièces requises (liste dans ce message), vos demandes pourront être traitées par la ou les scolarités.

La procédure à suivre est maintenant la suivante :

1/ Cliquez sur chacun des liens suivants :
<a href='###__CAND_DIR###/gen_recapitulatif.php?comp_id=$comp_id' target='_blank' class='lien_bleu_12'><b>- récapitulatif des informations que vous avez saisies</b> (format PDF)</a>
<a href='###__CAND_DIR###/gen_justificatifs.php?cand_id=$cand_id' target='_blank' class='lien_bleu_12'><b>- liste des justificatifs à nous faire parvenir par voie postale pour $nom_formation_corps</b> (format PDF)</a>

2/ Enregistrez puis imprimez ces documents PDF. Conservez-les car ils pourront vous reservir plus tard.

3/ Envoyez ces documents ainsi que les pièces demandées dans le document \"Justificatifs\" par courrier à l'adresse postale indiquée dans ce message (<b>sauf</b> si une adresse spécifique est précisée dans la liste des justificatifs).

$corps_fichiers

<font class='Texte_important_14'><b>IMPORTANT</b> :

Sauf consignes contraires de la scolarité <b>(vérifiez bien le document \"Liste des justificatifs\" ci-dessus)</b> :

- vous devez envoyer vos justificatifs à la scolarité le plus rapidement possible (n'attendez pas la date limite du $limite_reception_txt). Les dossiers hors délais seront examinés lors de la session suivante. Si aucune autre session n'est prévue, votre dossier risque de ne pas être traité.
- pour les candidatures à choix multiples (spécialités regroupées dans le menu 5-Précandidatures), vous devez envoyer <b>autant d'exemplaires</b> de vos justificatifs <b>que de formations sélectionnées</b> dans cette composante. Si vous n'envoyez pas vos justificatifs en plusieurs exemplaires, toutes vos candidatures <b>ne pourront pas être traitées</b>.</font>


Vous pouvez dès à présent suivre l'évolution de votre fiche en ligne (sur cette interface) et vous recevrez prochainement d'autres messages concernant le traitement de votre dossier.

Aucune information supplémentaire sur l'état de votre candidature ne sera donnée par téléphone.


<b>Rappel</b> : le dépôt d'une précandidature en ligne ne constitue en aucun cas une admission dans la ou les formations demandées.


Cordialement,


--
$adr_scol

$comp_nom
$univ_nom";

            $corps_message2=parse_macros($corps_message_composante);

            // Macros spécifiques aux justificatifs (à intégrer dans une autre fonction ?)
            $new_corps=preg_replace("/%justificatifs%/i", "<a href='###__CAND_DIR###/gen_justificatifs.php?cand_id=$cand_id' target='_blank' class='lien_bleu_12'><b>- liste des justificatifs à nous faire parvenir par voie postale pour $nom_formation_corps</b> (format PDF)</a>", $corps_message2);
            $new_corps=preg_replace("/%recapitulatif%/i", "<a href='###__CAND_DIR###/gen_recapitulatif.php?comp_id=$comp_id' target='_blank' class='lien_bleu_12'><b>- récapitulatif des informations que vous avez saisies</b> (format PDF)</a>", $new_corps);
            $new_corps=preg_replace("/%date_limite%/i", $limite_reception_txt, $new_corps);
            $new_corps=preg_replace("/%adresse_scolarite%/i", $adr_scol, $new_corps);
            $new_corps=preg_replace("/%composante%/i", $comp_nom, $new_corps);
            $new_corps=preg_replace("/%universite%/i", $univ_nom, $new_corps);
            $new_corps=preg_replace("/%civ%/i", $civ_mail, $new_corps);
            $new_corps=preg_replace("/%nom%/i", ucwords(mb_strtolower(stripslashes($cand_nom))), $new_corps);
                     
            if($liste_fichiers!="")
               $prefixe.="Ce message contient $les_pieces_jointes_suivantes : $liste_fichiers\n\nCliquez sur les liens pour les télécharger, puis suivez les instructions.\n";
                     
            $message_complet="$prefixe"."$new_corps";

            $dest_array=array("0" => array("id"       => "$candidat_id",
                                           "civ"      => "$cand_civ",
                                           "nom"       => "$cand_nom",
                                           "prenom"    => "$cand_prenom",
                                           "email"      => "$cand_email"));

            // Test nouveau système
            
            write_msg("", array("id" => "0", "nom" => "Système", "prenom" => "", "composante" => "$comp_nom", "universite" => "$univ_nom"),
                     $dest_array, $sujet, $message_complet, "$cand_nom $cand_prenom");
            
            /* avec l'ancien corps de message :
            write_msg("", array("id" => "0", "nom" => "Système", "prenom" => "", "composante" => "$comp_nom", "universite" => "$univ_nom"),
                     $dest_array, $sujet, $corps_message, "$cand_nom $cand_prenom");
            */
            // write_evt("", $GLOBALS['__EVT_ID_S_LOCK'], "Verrouillage automatique", $candidat_id, $comp_id);
         }

         $envoi_ok=1;

         $old_candidat_id=$candidat_id;
      }  // fin de la boucle for() globale sur les candidats
   } // fin du if($rows)

   // S'il y a des justificatifs vides : mail direct à l'admin (les fiches concernées n'ont normalement pas été verrouillées)
   if(isset($justificatifs_vides) && count($justificatifs_vides))
   {
      $justifs_txt="";

      foreach($justificatifs_vides as $propspec_id => $comp_formation)
         $justifs_txt.="$comp_formation ($propspec_id)\n";

      $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_NOREPLY]\r\nReply-To: $GLOBALS[__EMAIL_NOREPLY]\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";
      mail($GLOBALS['__EMAIL_ADMIN'], "[$univ_nom - Verrouillages : justificatifs vides]", "Fiches NON verrouillées pour les formations suivantes : \n\n" . $justifs_txt, $headers);

      return 1;
   }

   db_free_result($result);

   return 0;
}


// ===============================================================================
//       FONCTIONS POUR LES LANGUES DANS CERTAINES PAGES (LETTRES, NOTAMMENT)
// ===============================================================================

// Civlité
// En entrée : 
// $civ : civilité courte telle qu'elle est enregistrée dans la base
// $lang : code de la langue de sortie (ajouter en fonction des besoins)
// $flag : flag permettant de déterminer si on retourne la version abbrégée (=0), longue (=1) ou destinée à l'adresse (=2)

function civ_lang($civ, $lang, $flag)
{
   // "Mademoiselle" n'est normalement plus utilisé, seuls "Madame" et "Monsieur" sont retournés
   switch($lang)
   {
      case 'EN' : if(!strcasecmp($civ, "Mlle") || !strcasecmp($civ, "Mme") || !strcasecmp($civ, "Madame") || !strcasecmp($civ, "Mademoiselle"))
                  {
                     switch($flag)
                     {
                        case "0" :   $civ_lang="Ms";
                                    break;

                        case "1" :   $civ_lang="Miss";
                                    break;

                        case "2" :   $civ_lang="Ms.";
                                    break;
                     }
                  }
                  else
                  {
                     switch($flag)
                     {
                        case "0" :   $civ_lang="Mr";
                                    break;

                        case "1" :   $civ_lang="Mister";
                                    break;

                        case "2" :   $civ_lang="Mr.";
                                    break;
                     }
                  }
                  break;
                  
      default :   if(!strcasecmp($civ, "Mlle") || !strcasecmp($civ, "Mademoiselle"))
                     $civ_lang=$flag==1 ? "Mademoiselle" : "Mlle.";
                  elseif(!strcasecmp($civ, "Mme") || !strcasecmp($civ, "Madame"))
                     $civ_lang=$flag==1 ? "Madame" : "Mme.";
                   else
                     $civ_lang=$flag==1 ? "Monsieur" : "M.";
   }

   return $civ_lang;
}

// date
// En entrée :
// $timestamp : timestamp Unix à convertir en texte
// $lang : langue de sortie
// $format : abbrégé (=0 : 20/11/2009 par exemple) ou long (=1 : Vendredi 20 Novembre 2009 en français / Friday 20th of November 2009 en anglais)
// $jour : 1 si le nom du jour doit être affiché, 0 sinon
function date_lang($timestamp, $lang, $format, $jour)
{
   if($jour!=0 && $jour!=1)
      $jour=1;
      
   // Utiliser set_locale ?

   if($format==0) // format court : la langue importe peu
       $date_lang=date("j/m/Y", $timestamp);
   else
   {      
      switch($lang)
      {
         case "EN" : if($jour)
                         $date_lang=date("l jS \of F Y", $timestamp);
                     else
                         $date_lang=date("jS \of F Y", $timestamp);
                     break;
                  
         default : if($jour)
                      $date_lang=date_fr("l d F Y", $timestamp);
                   else
                      $date_lang=date_fr("d F Y", $timestamp);   
      }
   }
   
   return $date_lang;
}



// ==========================================================================
//                   GESTION DES MODULES SUPPLEMENTAIRES
// ==========================================================================

// Découverte et inclusion automatique des modules

function add_modules()
{
   $_SESSION["PLUGINS"]=array();
   $cnt=0;

   // Création de la variable de session qui contiendra les nouvelles macros définies par les modules
   if(!isset($_SESSION["__MACROS_USERS"]) || !is_array($_SESSION["__MACROS_USERS"]))
      $_SESSION["__MACROS_USERS"]=array();

   // Vérification du répertoire contenant les plugins
   if(isset($GLOBALS["__PLUGINS_DIR_ABS"]) && is_dir($GLOBALS["__PLUGINS_DIR_ABS"]) && is_readable($GLOBALS["__PLUGINS_DIR_ABS"]))
   {
      // Lecture des fichiers de définition dans le répertoire admin/modules
      // Chaque fichier doit contenir une unique variable $MODULE : tableau contenant tous les paramètres
      // Pour ajouter les modules, on inclut les fichiers de définition puis on vérifie le contenu de la variable $MODULE

      // ATTENTION : SUPPOSE QUE LA SYNTAXE DES FICHIERS A ETE VERIFIEE AU PREALABLE
      if(FALSE!=($all_files=scandir("$GLOBALS[__PLUGINS_DIR_ABS]")))
      {
         foreach($all_files as $entry)
         {
            // Fichiers "mod_*.php"
            if(0!=preg_match("/mod_[a-zA-Z1-9_\-]+\.php\$/", $entry))
            {
               // Réinitialisation de la variable créée dans le fichier précédent
               unset($MODULE);

               if(is_file("$GLOBALS[__PLUGINS_DIR_ABS]/$entry") && is_readable("$GLOBALS[__PLUGINS_DIR_ABS]/$entry"))
               {
                  include "$GLOBALS[__PLUGINS_DIR_ABS]/$entry";

                  if(isset($MODULE) && is_array($MODULE) && array_key_exists("MOD_NAME", $MODULE) && $MODULE["MOD_NAME"]!="" 
                                                         && array_key_exists("MOD_DIR", $MODULE) && $MODULE["MOD_DIR"]!=""
                                                         && array_key_exists("MOD_CONFIG", $MODULE)
                     && is_array($MODULE["MOD_CONFIG"]))
                  {
                     $stop=0;

                     foreach($MODULE["MOD_CONFIG"] as $CONFIG)
                     {
                        if(!is_array($CONFIG) || ((!array_key_exists("MOD_CONFIG_TITLE", $CONFIG) || empty($CONFIG["MOD_CONFIG_TITLE"])
                                              || !array_key_exists("MOD_CONFIG_PAGE", $CONFIG) || empty($CONFIG["MOD_CONFIG_PAGE"]))
                                                   && (!array_key_exists("MOD_CONFIG_SEP", $CONFIG) || empty($CONFIG["MOD_CONFIG_SEP"]))))
                           $stop=1;
                     }

                     // Les vérifications sont passées, on ajoute le module
                     if(!$stop)
                     {
                        $_SESSION["PLUGINS"]["$cnt"]=$MODULE;

                        // inclusion d'autres fichiers ?
                        // ==> A ajouter en paramètre dans le fichier de définition du module

                        if(is_array($MODULE["MOD_INCLUDE"]))
                        {
                           foreach($MODULE["MOD_INCLUDE"] as $include_file)
                           {
                              if(is_file("$GLOBALS[__PLUGINS_DIR_ABS]/$MODULE[MOD_DIR]/$include_file"))
                                 include "$GLOBALS[__PLUGINS_DIR_ABS]/$MODULE[MOD_DIR]/$include_file";
                           }
                        }

                        $cnt++;
                     }
                  }
               }
            }
         }
      }
   }
}

?>
