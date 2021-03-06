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
   session_name("preinsc");
   session_start();

   include "../configuration/aria_config.php";
   include "$__INCLUDE_DIR_ABS/vars.php";
   include "$__INCLUDE_DIR_ABS/fonctions.php";
   include "$__INCLUDE_DIR_ABS/db.php";

   // Include du module PEAR:Mail_Mime
   include('Mail.php');
   include('Mail/mime.php');

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;
/*
   if(!isset($_SESSION["interface_ouverte"]) || $_SESSION["interface_ouverte"]==0)
   {
      session_write_close();
      header("Location:identification.php");
      exit();
   }
*/
   $dbr=db_connect();

   if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
   {
      $liste_champs=array("civilite", "nom", "nom_naissance", "prenom", "prenom2", "jour", "mois", "annee","adresse_1","adr_cp","adr_ville","adr_pays","lieu_naissance","dpt_naissance",
                          "pays_naissance","email","emailconf", "telephone","telephone_portable", "nationalite","num_ine","code_conf");

      // V�rification de la pr�sence de ces champs dans le formulaire.
      // Si l'un de ces champs manque, c'est probablement le navigateur qui n'est pas adapt�

      foreach($liste_champs as $post_key)
      {
         if(!array_key_exists("$post_key", $_POST))
         {
            session_write_close();
            header("Location:../navigateur.php");
            exit();
         }
      }

      // nouvelle v�rification de la pr�sence du code (perdu si timeout)
      if(!isset($_SESSION["code_conf"]))
      {
         session_write_close();
         header("Location:../session.php");
         exit();
      }

      // v�rification des valeurs entr�es dans le formulaire
      // TODO : v�rifications pouss�es ?

      $civilite=$_POST["civilite"];

      $nom=stripslashes(str_replace("'","''", mb_strtoupper(trim($_POST["nom"]))));
      $nom_naissance=stripslashes(str_replace("'","''", mb_strtoupper(trim($_POST["nom_naissance"]))));
      
      if($nom_naissance=="")
         $nom_naissance=$nom;
      
      $prenom=stripslashes(str_replace("'","''", ucwords(mb_strtolower(trim($_POST["prenom"])))));
      $deuxieme_prenom=stripslashes(str_replace("'","''", ucwords(mb_strtolower(trim($_POST["prenom2"])))));

      $jour=trim($_POST["jour"]);
      $mois=trim($_POST["mois"]);
      $annee=trim($_POST["annee"]);

      $adresse_1=mb_strtolower(trim($_POST["adresse_1"]));
      $adresse_2=mb_strtolower(trim($_POST["adresse_2"]));
      $adresse_3=mb_strtolower(trim($_POST["adresse_3"]));
      $adr_cp=$_POST["adr_cp"];
      $adr_ville=$_POST["adr_ville"];
      $adr_pays=$_POST["adr_pays"];

      $lieu_naissance=ucwords(strtolower(trim($_POST["lieu_naissance"])));
      $dpt_naissance=trim($_POST["dpt_naissance"]);
      $pays_naissance=$_POST["pays_naissance"];

      $email=mb_strtolower(trim($_POST["email"]));
      $emailconf=mb_strtolower(trim($_POST["emailconf"]));

      if(strcmp($email, $emailconf)) // si les 2 adresses sont diff�rentes ...
         $email_inegaux=1;

      $telephone=trim($_POST["telephone"]);
      $telephone_portable=trim($_POST["telephone_portable"]);

      $deja_inscrit=$_POST["deja_inscrit"];

      if($deja_inscrit!="0" && $deja_inscrit!="1")
         $err_deja_inscrit="1";

      $premiere_inscr=trim($_POST["premiere_inscr"]);

      if($deja_inscrit==0)
         $premiere_inscr="";
      elseif(!ctype_digit($premiere_inscr) || strlen($premiere_inscr)!=4 || $premiere_inscr<1900) // || $premiere_inscr>"$__PERIODE")
         $err_premiere_inscr=1;

      $serie_bac=$_POST["serie_bac"];

      if($serie_bac=="")
         $err_serie_bac=1;

      // Ajouter le cas "sans bac"
      $baccalaureat=trim($_POST["baccalaureat"]);

      if(!ctype_digit($baccalaureat) || strlen($baccalaureat)!=4 || $baccalaureat<1900) // || $baccalaureat>"$__PERIODE")
         $err_baccalaureat=1;

      $nationalite=$_POST["nationalite"];

      $num_ine=str_replace(" ", "", $_POST["num_ine"]);

      if($num_ine!="" && check_ine_bea($num_ine))
         $erreur_ine_bea=1;
         
      if($deja_inscrit==1 && $num_ine=="")
         $erreur_ine_obligatoire=1;

      $code_conf=str_replace(" ", "", $_POST["code_conf"]);

      $champs_obligatoires=array($nom,$prenom,$jour,$mois,$annee,$lieu_naissance,$pays_naissance,$adresse_1,$email,$emailconf, $nationalite,$code_conf,$adr_cp, $adr_ville,$adr_pays,$deja_inscrit,$baccalaureat,$serie_bac);
      $cnt_obl=count($champs_obligatoires);

      for($i=0; $i<$cnt_obl; $i++) // v�rification des champs obligatoires
      {
         if($champs_obligatoires[$i]=="")
         {
            $champ_vide=1;
            $i=$cnt_obl;
         }
      }

      // Le d�partement de naissance est obligatoire pour ceux n�s en France
      if($pays_naissance=="FR" && $dpt_naissance!="2A" && $dpt_naissance!="2B" && (!ctype_digit($dpt_naissance) || $dpt_naissance<1 || ($dpt_naissance>95 && ($dpt_naissance<971 || $dpt_naissance>987))))
         $bad_dpt_naissance=1;

      if(!ctype_digit($jour) || !ctype_digit($mois) || !ctype_digit($annee) || $annee>=date('Y'))
         $bad_date=1;
      else
         $date_naissance=MakeTime(12,0,0,ltrim($mois,"0"),ltrim($jour,"0"),$annee); // heure : midi (pour �viter les probl�mes de d�callages horaires)

      if($code_conf!=$_SESSION["code_conf"])
         $badcode=1;
         
      // V�rification d'unicit� - On se base sur le nom, le pr�nom et la date de naissance
      // TODO : v�rifier si ces crit�res sont suffisants

      if(!isset($bad_date))
      {
         $result=db_query($dbr,"SELECT $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance
                                    FROM $_DB_candidat
                                 WHERE $_DBC_candidat_nom ILIKE '$nom'
                                 AND $_DBC_candidat_prenom ILIKE '$prenom'
                                 AND $_DBC_candidat_date_naissance='$date_naissance'");
         $rows=db_num_rows($result);

         if($rows)
            $id_existe=1;

         db_free_result($result);
      }
      
      if(!isset($id_existe) && !isset($email_inegaux))
      {
         $result=db_query($dbr,"SELECT * FROM $_DB_candidat
                                WHERE $_DBC_candidat_email ILIKE '$email'");
                                
         $rows=db_num_rows($result);

         if($rows)
            $mail_existe=1;

         db_free_result($result);
      }

      if(!isset($mail_existe) && !isset($champ_vide) && !isset($id_existe) && !isset($badcode) && !isset($email_inegaux) && !isset($bad_date) && !isset($bad_dpt_naissance) && !isset($erreur_ine_bea) 
         && !isset($err_deja_inscrit) && !isset($err_premiere_inscr) && !isset($err_serie_bac) && !isset($err_baccalaureat) && !isset($erreur_ine_obligatoire))
      {
         // Les donn�es du nouvel utilisateur sont compl�tes (pas forc�ment bonnes, mais �a le p�nalisera)
         // On peut cr�er l'identifiant et le code, l'ins�rer dans la base et envoyer le mail

         // Cr�ation de l'identifiant
         $new_identifiant=str_replace(" ","",mb_strtolower($nom)); // base de l'identifiant
         $new_identifiant=str_replace("-","",$new_identifiant);
         $base_identifiant=$new_identifiant=preg_replace("/[']+/","",$new_identifiant);
         
         $base_prenom=$prenom2=str_replace(" ","", str_replace("-","", preg_replace("/[']+/","", mb_strtolower($prenom))));

         // initialisation de la boucle
         $nb_lettres_prenom=1;
         $iteration=0;
         $len_prenom=strlen($base_prenom);

         while(db_num_rows(db_query($dbr,"SELECT $_DBC_candidat_id FROM $_DB_candidat WHERE $_DBC_candidat_identifiant like '$new_identifiant'")))
         {
            if($nb_lettres_prenom<=$len_prenom) // si on peut encore utiliser le pr�nom
            {
               $new_identifiant=substr($base_prenom, 0, $nb_lettres_prenom) . "." . $base_identifiant;
               $nb_lettres_prenom++;
            }
            else
            {
               $iteration++;
               $new_identifiant=$base_prenom . "." . $base_identifiant . $iteration;
            }
         }

         // g�n�ration du Code Personnel
         srand((double)microtime()*1000000);
         $code_conf=mb_strtoupper(md5(rand(0,9999)));
         $new_code=substr($code_conf, 17, 8);
         // on supprime le chiffre 1, les lettres I, L, O et le z�ro : portent � confusion - on les remplace par d'autres caract�res
         $new_code=str_replace("0","A", $new_code);
         $new_code=str_replace("O","H", $new_code);
         $new_code=str_replace("1","P", $new_code);
         $new_code=str_replace("I","F", $new_code);
         $new_code=str_replace("L","K", $new_code);

         if(isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]!="")
            $url_acces="$__URL_CANDIDAT/acces.php?co=$_SESSION[comp_id]";
         else
            $url_acces="$__URL_CANDIDAT";

         // envoi du mail de confirmation

         $headers = "MIME-Version: 1.0\r\nFrom: $__EMAIL_ADMIN\r\nReply-To: $__EMAIL_ADMIN\r\nContent-Type: text/plain; charset=ISO-8859-15\r\nContent-transfer-encoding: 8bit\r\n\r\n";

         $corps_message="============================================================\nCeci est un message automatique, merci de ne pas y r�pondre.\n============================================================\n\n
Bonjour $civilite ". preg_replace("/[']+/", "'", $nom) . ",\n
Les informations vous permettant d'acc�der � l'interface de pr�candidatures sont les suivantes:
- Adresse : $url_acces
- Identifiant : ". stripslashes($new_identifiant) . "
- Code Personnel : $new_code\n
Attention : respectez bien les minuscules et majuscules lorsque vous entrez ces codes !\n
Ne perdez surtout pas ces informations : elles vous serviront � consulter certains documents et r�sultats par la suite.\n\n
Cordialement,\n\n
--
$__SIGNATURE_COURRIELS";

         $ret=mail($email,"[Pr�candidatures] - Enregistrement", $corps_message, $headers);
/*
         $mime->setTXTBody($corps_message);
         $body = $mime->get();
         $hdrs = $mime->headers($hdrs);

         $mail =& Mail::factory('mail');
         $ret=$mail->send($email, $hdrs, $body);
*/

         if($ret==TRUE)
         {
            // Debug : envoi d'un mail � l'admin
            if($GLOBALS["__DEBUG"]=="t" && $GLOBALS["__DEBUG_ENREGISTREMENT"]=="t" && $GLOBALS["__EMAIL_ADMIN"]!="")
               mail($GLOBALS["__EMAIL_ADMIN"], "$GLOBALS[__DEBUG_SUJET] - Enregistrement : $civilite " . preg_replace("/[']+/", "'", $nom) . " " . preg_replace("/[']+/", "'", $prenom), "Courriel : $email\n\n" . $corps_message, $headers);

            $last_ip=$_SERVER["REMOTE_ADDR"];
            $last_host=&gethostbyaddr($_SERVER['REMOTE_ADDR']);
            $last_user_agent=$_SERVER["HTTP_USER_AGENT"];
            $fiche_manuelle=$candidat_lock=$candidat_lockdate=$derniere_connexion=$cursus_en_cours=0;
            $derniere_erreur="";

            if($browser=&get_browser(null, true))
            {
               if(isset($browser["parent"]) && isset($browser["platform"]) && isset($browser["browser"]) && isset($browser["version"]) && isset($browser["css"]))
                  $last_user_agent="$browser[parent] - $browser[platform] - $browser[browser] $browser[version] / CSS : $browser[css]";
            }

            $new_id=db_locked_query($dbr, $_DB_candidat, "INSERT INTO $_DB_candidat ($_DBU_candidat_id,
                                                                                     $_DBU_candidat_civilite, 
                                                                                     $_DBU_candidat_nom, 
                                                                                     $_DBU_candidat_nom_naissance, 
                                                                                     $_DBU_candidat_prenom, 
                                                                                     $_DBU_candidat_prenom2, 
                                                                                     $_DBU_candidat_date_naissance, 
                                                                                     $_DBU_candidat_lieu_naissance,
                                                                                     $_DBU_candidat_dpt_naissance, 
                                                                                     $_DBU_candidat_pays_naissance, 
                                                                                     $_DBU_candidat_nationalite, 
                                                                                     $_DBU_candidat_telephone, 
                                                                                     $_DBU_candidat_telephone_portable, 
                                                                                     $_DBU_candidat_adresse_1, 
                                                                                     $_DBU_candidat_adresse_2, 
                                                                                     $_DBU_candidat_adresse_3, 
                                                                                     $_DBU_candidat_adresse_cp, 
                                                                                     $_DBU_candidat_adresse_ville, 
                                                                                     $_DBU_candidat_adresse_pays, 
                                                                                     $_DBU_candidat_numero_ine, 
                                                                                     $_DBU_candidat_email, 
                                                                                     $_DBU_candidat_identifiant, 
                                                                                     $_DBU_candidat_code_acces, 
                                                                                     $_DBU_candidat_connexion, 
                                                                                     $_DBU_candidat_derniere_ip, 
                                                                                     $_DBU_candidat_dernier_host, 
                                                                                     $_DBU_candidat_dernier_user_agent, 
                                                                                     $_DBU_candidat_derniere_erreur_code, 
                                                                                     $_DBU_candidat_manuelle, 
                                                                                     $_DBU_candidat_cursus_en_cours, 
                                                                                     $_DBU_candidat_lock, 
                                                                                     $_DBU_candidat_lockdate, 
                                                                                     $_DBU_candidat_deja_inscrit, 
                                                                                     $_DBU_candidat_annee_premiere_inscr, 
                                                                                     $_DBU_candidat_annee_bac, 
                                                                                     $_DBU_candidat_serie_bac)
             
                                                                                   VALUES('##NEW_ID##',
                                                                                          '$civilite',
                                                                                          '$nom',
                                                                                          '$nom_naissance',
                                                                                          '$prenom',
                                                                                          '$deuxieme_prenom',
                                                                                          '$date_naissance',
                                                                                          '$lieu_naissance',
                                                                                          '$dpt_naissance',
                                                                                          '$pays_naissance',
                                                                                          '$nationalite',
                                                                                          '$telephone',
                                                                                          '$telephone_portable',
                                                                                          '$adresse_1',
                                                                                          '$adresse_2',
                                                                                          '$adresse_3',
                                                                                          '$adr_cp',
                                                                                          '$adr_ville',
                                                                                          '$adr_pays',
                                                                                          '$num_ine',
                                                                                          '$email',
                                                                                          '$new_identifiant',
                                                                                          '$new_code',
                                                                                          '$derniere_connexion',
                                                                                          '$last_ip',
                                                                                          '$last_host',
                                                                                          '$last_user_agent',
                                                                                          '$derniere_erreur',
                                                                                          '$fiche_manuelle',
                                                                                          '$cursus_en_cours',
                                                                                          '$candidat_lock',
                                                                                          '$candidat_lockdate',
                                                                                          '$deja_inscrit',
                                                                                          '$premiere_inscr',
                                                                                          '$baccalaureat',
                                                                                          '$serie_bac')");
            
            $_SESSION["email"]=$email;

            // Historique : log de l'enregistrement
            write_evt("", $__EVT_ID_C_REG, "Enregistrement : $nom $prenom", $new_id, $new_id);

            // Message de bienvenue
            $corps_message="Bonjour $civilite ". preg_replace("/[']+/", "'", $nom) .",

Bienvenue sur l'Interface de Candidatures !

Cette interface vous permet de d�poser un ou plusieurs dossiers de candidatures dans les composantes enregistr�es.

<strong><u>Quelques conseils pour d�buter :</u></strong>

&#8226;&nbsp;&nbsp;<a href='$__DOC_DIR/documentation.php' target='_blank' class='lien_bleu_12'><b>la documentation</b> : elle r�sume toute la proc�dure</a>

&#8226;&nbsp;&nbsp;<b>le menu sup�rieur</b> : il vous permet d'acc�der aux fonctionnalit�s de l'interface :
&nbsp;&nbsp;- \"Choisir une autre composante\" pour d�poser un dossier dans un autre �tablissement,
&nbsp;&nbsp;- \"Votre fiche\" pour compl�ter vos informations (menu par d�faut),
&nbsp;&nbsp;- \"Rechercher une formation\" pour trouver la composante proposant la formation que vous cherchez,
&nbsp;&nbsp;- \"Messagerie\" : l'application vous enverra automatiquement des messages (avec notification de r�ception sur votre adresse �lectronique), et vous pourrez �galement l'utiliser pour contacter la scolarit�,
&nbsp;&nbsp;- \"Mode d'emploi\" : un lien permanent vers la documentation.

N'h�sitez pas � explorer cette interface !


Vous pouvez maintenant cliquer sur <strong>\"Votre fiche\"</strong> et commencer � compl�ter les informations demand�es.


Bien cordialement,


$__SIGNATURE_COURRIELS";

            $dest_array=array("0" => array("id"       => "$new_id",
                                           "civ"      => "$civilite",
                                           "nom"       => preg_replace("/[']+/", "'", $nom),
                                           "prenom"    => preg_replace("/[']+/", "'", $prenom),
                                           "email"      => "$email"));

            write_msg("", array("id" => "0", "nom" => "Syst�me", "prenom" => "", "composante" => "", "universite" => "$__SIGNATURE_COURRIELS"),
                          $dest_array, "Bienvenue !", $corps_message, "$nom $prenom", $__FLAG_MSG_NO_NOTIFICATION);

            db_close($dbr);
            
            session_write_close();
            header("Location:validation.php");
            exit();
         }
         else
            $erreur_mail=1;
      }
   }

   // Construction de la liste des pays et nationalit�s (codes ISO) pour son utilisation dans le formulaire
   $_SESSION["liste_pays_nat_iso"]=array();
   
   $res_pays_nat=db_query($dbr, "SELECT $_DBC_pays_nat_ii_iso, $_DBC_pays_nat_ii_insee, $_DBC_pays_nat_ii_pays, $_DBC_pays_nat_ii_nat
                                 FROM $_DB_pays_nat_ii
                                 ORDER BY to_ascii($_DBC_pays_nat_ii_pays)");
                                 
   $rows_pays_nat=db_num_rows($res_pays_nat);
   
   for($p=0; $p<$rows_pays_nat; $p++)
   {
      list($code_iso, $code_insee, $table_pays, $table_nationalite)=db_fetch_row($res_pays_nat, $p);
      
      // Construction uniquement si le code insee est pr�sent (pour les exports APOGEE ou autres)
      if($code_insee!="")
         $_SESSION["liste_pays_nat_iso"]["$code_iso"]=array("pays" => "$table_pays", "nationalite" => $table_nationalite);
/*      
      if($code_insee!="")
         $_SESSION["liste_pays_nat_insee"]["$code_insee"]=array("pays" => "$table_pays", "nationalite" => $table_nationalite);
*/
   }

   en_tete_candidat_simple();
   menu_sup_simple();
?>

<div class="main">
   <?php
      titre_page_icone("Enregistrement d'un nouveau candidat", "add_32x32_fond.png", 15, "L");

      if(isset($id_existe))
      {
         if(array_key_exists("config", $_SESSION) && array_key_exists("__ASSISTANCE", $_SESSION["config"]) && $_SESSION["config"]["__ASSISTANCE"]=="t")
         {
            message("<strong>Erreur</strong> : une fiche � ce nom existe d�j� dans la base Aria.</b>
                     <br><br><b>1/</b> Si vous avez oubli� votre identifiant/code personnel, <a href='recuperation_identifiants.php' class='lien2a'>cliquez ici</a>.
                     <br><br><b>2/</b> Si vous n'avez jamais rempli ce formulaire (ou si vous avez chang� d'adresse �lectronique) merci de compl�ter
                     <br /> le formulaire simplifi� <a href='$GLOBALS[__CAND_DIR]/assistance/form_adresse.php' class='lien2a'>� cette adresse</a> pour une demande de changement d'adresse �lectronique.", $__ERREUR);
         }
         else
         {
            message("<strong>Erreur</strong> : une fiche � ce nom existe d�j� dans la base Aria.</b>
                     <br><br><b>1/</b> Si vous avez oubli� votre identifiant/code personnel, <a href='recuperation_identifiants.php' class='lien2a'>cliquez ici</a>.
                     <br><br><b>2/</b> Si vous n'avez jamais rempli ce formulaire (ou si vous avez chang� d'adresse �lectronique), 
                     <br />merci <a href='mailto:$__EMAIL_SUPPORT' class='lien2a'>d'envoyer un courriel � cette adresse</a>
                     <strong>avec toutes les donn�es du formulaire</strong>.", $__ERREUR);
         }
      }
      elseif(isset($mail_existe))
      {
         if(array_key_exists("config", $_SESSION) && array_key_exists("__ASSISTANCE", $_SESSION["config"]) && $_SESSION["config"]["__ASSISTANCE"]=="t")
         {
            message("<strong>Erreur</strong> : cette adresse �lectronique est d�j� utilis�e par une autre fiche dans la base Aria.</b>
                     <br><br><b>1/</b> Si vous avez oubli� votre identifiant/code personnel, <a href='recuperation_identifiants.php' class='lien2a'>cliquez ici</a>.
                     <br><br><b>2/</b> En cas de non r�ception des identifiants � l'adresse indiqu�e, vous pouvez compl�ter le formulaire <a href='$GLOBALS[__CAND_DIR]/assistance/form_adresse.php' class='lien2a'>� cette adresse</a> 
                     <br>pour une demande de changement d'adresse �lectronique.", $__ERREUR);
         }
         else
         {
            message("<strong>Erreur</strong> : une fiche � ce nom existe d�j� dans la base Aria.</b>
                     <br><br><b>1/</b> Si vous avez oubli� votre identifiant/code personnel, <a href='recuperation_identifiants.php' class='lien2a'>cliquez ici</a>.
                     <br><br><b>2/</b> Si vous avez chang� d'adresse �lectronique, merci <a href='mailto:$__EMAIL_SUPPORT' class='lien2a'>d'envoyer un courriel � cette adresse</a> 
                     <br /><strong>avec toutes les donn�es du formulaire</strong>.", $__ERREUR);
         }
      }
      else
      {
         $message_erreur="";

         if(isset($champ_vide))
            $message_erreur.="Formulaire incomplet: les champs en gras sont <u>obligatoires</u>";

         if(isset($badcode))
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- le code de confirmation est incorrect";
         }

         if(isset($email_inegaux))
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- les deux adresses �lectroniques ne correspondent pas";
         }

         if(isset($bad_dpt_naissance))
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- si vous �tes n�(e) en France, le d�partement de naissance est obligatoire";
         }

         if(isset($bad_date))
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- le format de votre date de naissance est incorrect (JJ / MM / AAAA)";
         }

         if(isset($erreur_ine_bea))
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- le numero INE ou BEA est incorrect";
         }

         if(isset($erreur_ine_obligatoire))
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- vous avez indiqu� avoir d�j� �t� inscrit(e) dans cette Universit� : le numero INE ou BEA est <strong>obligatoire</strong>";
         }

         if(isset($err_deja_inscrit))
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- vous devez indiquer si vous avez d�j� �t� inscrit(e) ou non dans cette Universit�";
         }

         if(isset($err_premiere_inscr) && $err_premiere_inscr=="1")
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- le format de l'ann�e de premi�re inscription dans cette Universit� est incorrect";
         }

         if(isset($err_baccalaureat) && $err_baccalaureat=='1')
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- le format de l'ann�e d'obtention du baccalaur�at est incorrect";
         }

         if(isset($err_serie_bac))
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- vous devez s�lectionner la s�rie de votre baccalaur�at (ou �quivalence). Si vous n'avez pas obtenu le baccalaur�at, s�lectionnez \"Sans bac\" dans le menu d�roulant.";
         }

         if($message_erreur!="")
         {
            $message_erreur="<strong>Erreur(s) : </strong><br>$message_erreur";
            message("$message_erreur", $__ERREUR);
         }
      }

      if(isset($erreur_mail))
         message("Erreur lors de l'envoi du courriel de confirmation.
                  <br>Merci de v�rifier la validit� de votre adresse �lectronique.", $__ERREUR);

      if(!isset($champ_vide) && !isset($badcode) && !isset($email_inegaux) && !isset($bad_date) && !isset($erreur_mail) && !isset($id_existe))
         message("Veuillez compl�ter le formulaire suivant (les champs <font class='Texte_important' style='vertical-align:top;'><b>en rouge</b></font> sont <font class='Texte_important' style='vertical-align:top;'><b><u>obligatoires</u></b></font>).
                  <br>Vous recevrez alors, par courriel, les codes d'acc�s qui vous permettront d'acc�der aux pr�candidatures en ligne.", $__INFO);
   ?>

   <form name='form1' action="<?php print("$php_self"); ?>" method="POST">

   <table style="margin-left:auto; margin-right:auto;">
   <tr>
      <td class='td-complet fond_menu2' colspan='2'>
         <font class='Texte_menu2' style="font-size:14px"><strong>Identit�</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Civilit� : </b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <?php
         if(isset($civilite))
         {
            if($civilite=="M")
            {
               $selected_M="selected='1'";
               $selected_Mlle="";
               $selected_Mme="";
            }
            else
            {
               if($civilite=="Mme")
               {
                  $selected_Mme="selected='1'";
                  $selected_M="";
                  $selected_Mlle="";
               }
               else
               {
                  $selected_Mlle="selected='1'";
                  $selected_M="";
                  $selected_Mme="";
               }
            }
         }
         else
            $selected_M=$selected_Mlle=$selected_Mme="";

         print("
         <select name='civilite' size='1'>
            <option value='Mme' $selected_Mme>Madame</option>
            <option value='Mlle' $selected_Mlle>Mademoiselle</option>
            <option value='M' $selected_M>Monsieur</option>
         </select>");
         ?>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Nom usuel :</b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='nom' value='<?php if(isset($nom)) echo htmlspecialchars($nom,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'>Nom de naissance (si diff�rent) :</font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='nom_naissance' value='<?php if(isset($nom_naissance)) echo htmlspecialchars($nom_naissance,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Pr�nom : </b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='prenom' value='<?php if(isset($prenom)) echo htmlspecialchars($prenom,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_menu2'>Deuxi�me pr�nom (recommand�) : </font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='prenom2' value='<?php if(isset($deuxieme_prenom)) echo htmlspecialchars($deuxieme_prenom,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Date de naissance (JJ/MM/AAAA) : </b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='jour' value='<?php if(isset($jour)) echo htmlspecialchars($jour,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="2" maxlength="2">/
         <input type='text' name='mois' value='<?php if(isset($mois)) echo htmlspecialchars($mois,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="2" maxlength="2">/
         <input type='text' name='annee' value='<?php if(isset($annee)) echo htmlspecialchars($annee,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="4" maxlength="4">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Ville de naissance : </b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='lieu_naissance' value='<?php if(isset($lieu_naissance)) echo htmlspecialchars($lieu_naissance,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="60">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Si vous �tes n�(e) en France, veuillez<br>indiquer le N� de d�partement de naissance : </b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <select name='dpt_naissance'>
            <option value=''></option>
            <?php
               $res_departements=db_query($dbr, "SELECT $_DBC_departements_fr_numero, $_DBC_departements_fr_nom
                                                 FROM $_DB_departements_fr
                                                 ORDER BY $_DBC_departements_fr_numero");

               $nb_dpts_fr=db_num_rows($res_departements);

               for($dpt=0; $dpt<$nb_dpts_fr; $dpt++)
               {
                  list($dpt_num, $dpt_nom)=db_fetch_row($res_departements, $dpt);

                  $selected=(isset($dpt_naissance) && $dpt_naissance==$dpt_num) || (isset($_SESSION["dpt_naissance"]) && $_SESSION["dpt_naissance"]==$dpt_num) ? "selected='1'" : "";
                  
                  print("<option value='$dpt_num' $selected>$dpt_num - $dpt_nom</option>\n");
               }

               db_free_result($res_departements);
            ?>
         </select>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Pays de naissance : </b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <select name='pays_naissance' size='1'>
             <option value=''></option>
            <?php
               foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
               {
                  if($array_pays_nat["pays"]!="")
                  {
                     $selected=(isset($pays_naissance) && $pays_naissance==$code_iso) ? "selected='1'" : "";
                     
                     print("<option value='$code_iso' $selected>$array_pays_nat[pays]</option>\n");
                  }
               }
            ?>
         </select>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Nationalit� : </b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <select name='nationalite' size='1'>
             <option value=''></option>
            <?php
               foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
               {
                  if($array_pays_nat["nationalite"]!="")
                  {
                     $selected=(isset($nationalite) && $nationalite==$code_iso) ? "selected='1'" : "";
                     
                     print("<option value='$code_iso' $selected>$array_pays_nat[nationalite]</option>\n");
                  }
               }
            ?>
         </select>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style='text-align:right;'>
         <font class='Texte_important_menu2'><b>Adresse �lectronique valide (<i>email</i>) : </b></font>
      </td>
      <td class='td-droite fond_menu' style='text-align:left;'>
         <input type='text' name='email' value='<?php if(isset($email)) echo htmlspecialchars($email,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="255">
         &nbsp;&nbsp;<font class='Texte_menu'><b><u>Une seule adresse</u> dans ce champ</b></font>
         <br>
         <font class='Texte_important_menu'>
            <strong>Attention : </strong>
            <br>- v�rifiez bien que les courriels ne sont pas redirig�s dans votre dossier <strong>"Spams"</strong> ou <strong>"Courriers Ind�sirables"</strong>,
            <br>- configurez les <strong>filtres</strong> de votre messagerie pour autoriser l'adresse <strong>"<?php echo $__EMAIL_ADMIN; ?>"</strong> � vous envoyer des courriels.
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style='text-align:right;'>
         <font class='Texte_important_menu2'><b>Veuillez confirmer l'adresse �lectronique : </b></font>
      </td>
      <td class='td-droite fond_menu' style='text-align:left;'>
         <input type='text' name='emailconf' value='<?php if(isset($emailconf)) echo htmlspecialchars($emailconf,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="255">
      </td>
   </tr>
   <tr>
      <td colspan='2' style='height:10px;'></td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' colspan='2'>
         <font class='Texte_menu2' style="font-size:14px"><strong>Adresse postale pour la r�ception des courriers</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_1' value="<?php if(isset($adresse_1)) echo htmlspecialchars(stripslashes($adresse_1), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse (suite) : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_2' value="<?php if(isset($adresse_2)) echo htmlspecialchars(stripslashes($adresse_2), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse (suite) : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_3' value="<?php if(isset($adresse_3)) echo htmlspecialchars(stripslashes($adresse_3), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Code Postal :</b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='adr_cp' value='<?php if(isset($adr_cp)) echo htmlspecialchars($adr_cp,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="15">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Ville :</b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='adr_ville' value='<?php if(isset($adr_ville)) echo htmlspecialchars($adr_ville,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="60">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Pays :</b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <select name='adr_pays' size='1'>
             <option value=''></option>
            <?php
               foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
               {
                  if($array_pays_nat["pays"]!="")
                  {
                     $selected=(isset($adr_pays) && $adr_pays==$code_iso) ? "selected='1'" : "";
                     
                     print("<option value='$code_iso' $selected>$array_pays_nat[pays]</option>\n");
                  }
               }
            ?>
         </select>
         <!-- <input type='text' name='adr_pays' value='<?php if(isset($adr_pays)) echo htmlspecialchars($adr_pays,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="60"> -->
      </td>
   </tr>
   <tr>
      <td colspan='2' style='height:10px;'></td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' colspan='2'>
         <font class='Texte_menu2' style="font-size:14px"><strong>Autres informations</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'>
            <b>Ann�e d'obtention du baccalaur�at (ou �quivalent) : </b>
         </font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='baccalaureat' value='<?php if(isset($baccalaureat)) echo trim("$baccalaureat"); ?>' size="25" maxlength="4"><font class='Texte'><i>(Format : AAAA)</i></font>
         <br><font class='Texte_menu_10'><i>Si vous n'avez pas le baccalaur�at (et que vous ne le pr�parez pas cette ann�e), s�lectionnez "Sans bac" dans<br>la liste et indiquez l'ann�e du dernier dipl�me obtenu</i></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>S�rie de votre baccalaur�at :</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <select name='serie_bac' size='1'>
            <option value=''></option>
            <?php
               $result=db_query($dbr,"SELECT $_DBC_diplomes_bac_code, $_DBC_diplomes_bac_intitule
                                    FROM $_DB_diplomes_bac ORDER BY $_DBC_diplomes_bac_intitule");
               $rows=db_num_rows($result);

               if(isset($serie_bac))
                  $cur_serie_bac=$serie_bac;

               for($i=0; $i<$rows; $i++)
               {
                  list($serie_bac, $intitule_bac)=db_fetch_row($result,$i);

                  $selected=isset($cur_serie_bac) && $cur_serie_bac==$serie_bac ? "selected=1" : "";

                  print("<option value='$serie_bac' $selected>$intitule_bac</option>\n");
               }
            ?>
         </select>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Avez vous d�j� �t� inscrit(e) dans cette Universit� ?</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <?php
            if(isset($deja_inscrit) && $deja_inscrit==1)
            {
               $oui_checked="checked";
               $non_checked="";
            }
            else
            {
               $oui_checked="";
               $non_checked="checked";
            }

            print("<input type='radio' name='deja_inscrit' value='1' $oui_checked><font class='Texte_menu'>&nbsp;Oui&nbsp;&nbsp;</font><input type='radio' name='deja_inscrit' value='0' $non_checked><font class='Texte'>&nbsp;Non</font>\n");

            if(isset($__REMARQUE_FUSION_TEMPORAIRE))
               print("$__REMARQUE_FUSION_TEMPORAIRE");
         ?>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b><u>Si oui</u>, indiquez l'ann�e de premi�re inscription :</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='premiere_inscr' value='<?php if(isset($premiere_inscr)) echo trim("$premiere_inscr"); ?>' size="25" maxlength="4"><font class='Texte'><i>(Format : AAAA)</i></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_menu2'>Num�ro INE <b>ou</b> BEA : </font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='num_ine' value='<?php if(isset($num_ine)) echo htmlspecialchars($num_ine,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="11"> <font class='Texte_menu'>(<b>obligatoire</b> si vous avez d�j� �t� inscrit(e) dans cette Universit�)</font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_menu2'>Num�ro de t�l�phone fixe : </font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='telephone' value='<?php if(isset($telephone)) echo htmlspecialchars($telephone,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="15">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_menu2'>Num�ro de t�l�phone portable : </font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input type='text' name='telephone_portable' value='<?php if(isset($telephone_portable)) echo htmlspecialchars($telephone_portable,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="15">
      </td>
   </tr>
   </table>

   <div class="centered_box">
      <font class='Texte'>
         <br><b>Code de confirmation</b> : veuillez recopier le code suivant dans le champ pr�vu � cet effet :
      </font>
   </div>
   <?php
      // g�n�ration du code al�atoire
      if(!isset($_SESSION["code_conf"]))
      {
         srand((double)microtime()*1000000);
         $nouveau_code=strtoupper(md5(rand(0,9999)));
         $_SESSION["code_conf"]=substr($nouveau_code, 17, 5);

         // on supprime le chiffre 1, le z�ro et la lettre O : portent � confusion - on les remplace par d'autres caract�res
         $_SESSION["code_conf"]=str_replace("0","A", $_SESSION["code_conf"]);
         $_SESSION["code_conf"]=str_replace("O","H", $_SESSION["code_conf"]);
         $_SESSION["code_conf"]=str_replace("1","P", $_SESSION["code_conf"]);
      }

      db_close($dbr);
   ?>

   <div class="centered_box">
      <img style='vertical-align:middle;' name="confirmation" src="code_confirmation.php" border="1">
      <font class='Texte'><strong>Code : </strong><input type='text' name='code_conf' value='<?php if(isset($code_conf)) echo htmlspecialchars($code_conf,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="15" maxlength="5"></font>
   </div>

   <div class="centered_icons_box">
      <a href='identification.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
      <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go_valider" value="Valider">
      </form>
   </div>
</div>
   
<?php
   pied_de_page_candidat();
?>

<script language="javascript">
   document.form1.civilite.focus()
</script>

</body></html>

