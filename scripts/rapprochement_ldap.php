<?php
  if(is_file("../include/vars.php")) include "../include/vars.php";
  else die("Fichier \"include/vars.php\" non trouvé");
   
  if(is_file("../include/fonctions.php")) include "../include/fonctions.php";
  else die("Fichier \"include/fonctions.php\" non trouvé");
         
  if(is_file("../include/db.php")) include "../include/db.php";
  else die("Fichier \"include/db.php\" non trouvé");
               
  if(is_file("../include/access_functions.php")) include "../include/access_functions.php";
  else die("Fichier \"include/access_functions.php\" non trouvé");
                     
  if(is_file("../include/fonctions_ldap.php")) include "../include/fonctions_ldap.php";
  else die("Fichier \"include/fonctions_ldap\" non trouvé");


  $dbr=db_connect();

  // Chargement de la configuration
  $load_config=__get_config($dbr);
     
  if($load_config===FALSE) // config absente : erreur
     $erreur_config=1;
  elseif($load_config==-1) // paramètre(s) manquant(s) : avertissement
     $warn_config=1;
     
  $ldap=aria_ldap_connect();
  
  $res_all=db_query($dbr,"SELECT id,nom,prenom,courriel,login, 
                          CASE WHEN id IN (SELECT id FROM acces_cnx) THEN (SELECT derniere_connexion FROM acces_cnx WHERE acces_cnx.id=acces.id) ELSE '0' END as last
                             FROM acces 
                          WHERE source!='1' 
                          AND niveau!='-10'                           
                             ORDER BY nom,prenom,login");
 
  $rows=db_num_rows($res_all);
  
  if($rows)
  {
     $all_filtres=0;
     
     for($i=0; $i<$rows; $i++)
     {
        list($acces_id, $nom, $prenom, $courriel, $login, $last)=db_fetch_row($res_all, $i);

        if($last!=0)
           $date_cnx=date_fr("j F Y", id_to_date($last));
        else
           $date_cnx="Pas dans l'historique";
           
        $date_cr=date_fr("j F Y", id_to_date($acces_id));

        echo "[(".($i+1)."/$rows) $nom - $prenom - $courriel - $login - Création : $date_cr - Dernière connexion : $date_cnx]\n";

        /* TODO : paramétrer les attributs LDAP */
        $attr_ldap_login="uid";
        $attr_ldap_prenom="givenname";
        $attr_ldap_nom="sn";
        $attr_ldap_mail="udscanonicaladdress";
        $attr_ldap_mail2="udsAlternateAddress";
        $attr_ldap_mail3="mail";
        /* ===== */

        // Construction du filtre
        
        $array_filtres=array("1" => "(|($attr_ldap_login=$login)(udscanonicaladdress=$courriel)(udsAlternateAddress=$courriel)(mail=$courriel))",
                             "2" => "(&($attr_ldap_nom=$nom)($attr_ldap_prenom=$prenom))",
                             "3" => "($attr_ldap_nom=$nom)");          
        
        /*
        $filtre_1="(|($attr_ldap_login=$login)(udscanonicaladdress=$courriel)(udsAlternateAddress=$courriel)(mail=$courriel))";
        $filtre_2="(&($attr_ldap_nom=$nom)($attr_ldap_prenom=$prenom))";
        $filtre_3="($attr_ldap_nom=$nom)";
        */

        $filtre_tri=array("$attr_ldap_prenom","$attr_ldap_nom");
   
        $attributs=array("$attr_ldap_login","$attr_ldap_nom","$attr_ldap_prenom","$attr_ldap_mail","$attr_ldap_mail2","$attr_ldap_mail3");

        $filtre_n=$count=0;
        
        if($all_filtres==1)        
        {
           $filtre="(|($attr_ldap_login=$login)(udscanonicaladdress=$courriel)(udsAlternateAddress=$courriel)(mail=$courriel)($attr_ldap_nom=$nom))";
           $result_ldap=ldap_search($ldap, $GLOBALS["__LDAP_BASEDN"], $filtre, $attributs) or die("Erreur LDAP : ldap_search()\n");
           $entries_ldap=ldap_get_entries($ldap, $result_ldap) or die("Erreur LDAP : ldap_get_entries()\n");
              
           $count=$entries_ldap["count"];
        }
        else
        {
           while($count==0 && $filtre_n<count($array_filtres))
           {
              $filtre_n++;
              
              $result_ldap=ldap_search($ldap, $GLOBALS["__LDAP_BASEDN"], $array_filtres[$filtre_n], $attributs) or die("Erreur LDAP : ldap_search()\n");
   
              // Tri
              /*
              foreach($filtre_tri as $tri_attr)
              ldap_sort($cnx_ldap, $result_ldap, $tri_attr);
              */
            
              // Récupération du résultat trié
              $entries_ldap=ldap_get_entries($ldap, $result_ldap) or die("Erreur LDAP : ldap_get_entries()\n");
              
              $count=$entries_ldap["count"];
           }
        }
        
        // traitement
        if($entries_ldap["count"])
        {
           $i_choix=1;
           $array_choix=array();
              
           foreach($entries_ldap as $key => $user)
           {
              if(is_array($user))
              {
                 echo "[$i_choix] Login : [".$user["$attr_ldap_login"]["0"]."] Nom : [".$user["$attr_ldap_nom"]["0"]." ".$user["$attr_ldap_prenom"]["0"]."] Mail : [".$user["$attr_ldap_mail"]["0"]."]\n";
                 $array_choix[$i_choix]=$key;
                 $i_choix++;
                 
              /*
                 if($user["$attr_ldap_login"]["0"]==$login)
                 {
                    $ok=1;
                    echo " [$i_choix] ".$user["$attr_ldap_login"]["0"]." ".$user["$attr_ldap_nom"]["0"]." ".$user["$attr_ldap_prenom"]["0"]." ".$user["$attr_ldap_mail"]["0"]."\n";
                    $array_choix[$i_choix]=$key;
                    $i_choix++;
                 }
                 
                 if(array_key_exists($attr_ldap_mail, $user) && count($user["$attr_ldap_mail"]))
                 {
                    $done=0;
                    
                    foreach($user["$attr_ldap_mail"] as $current_mail)
                    {
                       if($current_mail==$courriel)
                       {
                          $done=$ok=1;
                          echo " [$i_choix] ".$user["$attr_ldap_login"]["0"]." ".$user["$attr_ldap_nom"]["0"]." ".$user["$attr_ldap_prenom"]["0"]." ".$user["$attr_ldap_mail"]["0"]."\n";
                          $array_choix[$i_choix]=$key;
                          $i_choix++;
                          break;
                       }
                    }
                    
                    if($done)
                      break;
                 }
                 
                 if(array_key_exists($attr_ldap_mail2, $user) && count($user["$attr_ldap_mail2"]))
                 {
                    $done=0;
                    
                    foreach($user["$attr_ldap_mail2"] as $current_mail)
                    {
                       if($current_mail==$courriel)
                       {
                          $done=$ok=1;
                          echo "  > ".$user["$attr_ldap_login"]["0"]." ".$user["$attr_ldap_nom"]["0"]." ".$user["$attr_ldap_prenom"]["0"]." ".$user["$attr_ldap_mail"]["0"]."($attr_ldap_mail2 = $current_mail)\n";
                          break;
                       }
                    }
                    
                    if($done)
                       break;
                 }
                 
                 if(array_key_exists($attr_ldap_mail3, $user) && count($user["$attr_ldap_mail3"]))
                 {
                    $done=0;
                    
                    foreach($user["$attr_ldap_mail3"] as $current_mail)
                    {
                       if($current_mail==$courriel)
                       {
                          $done=$ok=1;
                          echo "  > ".$user["$attr_ldap_login"]["0"]." ".$user["$attr_ldap_nom"]["0"]." ".$user["$attr_ldap_prenom"]["0"]." ".$user["$attr_ldap_mail"]["0"]."($attr_ldap_mail3 = $current_mail)\n";
                          break;
                       }
                    }
                    
                    if($done)
                       break;
                 }

                 if(clean_str(mb_strtolower(utf8_decode($user["$attr_ldap_nom"]["0"])))==clean_str(mb_strtolower($nom)) && clean_str(mb_strtolower(utf8_decode($user["$attr_ldap_prenom"]["0"])))==clean_str(mb_strtolower($prenom)))
                 {
                    echo "  > ".$user["$attr_ldap_login"]["0"]." ".$user["$attr_ldap_nom"]["0"]." ".$user["$attr_ldap_prenom"]["0"]." ".$user["$attr_ldap_mail"]["0"]."\n";
                    $ok=1;
                    break;
                 }
              */
              }
           }
           
           $defaut=$i_choix==2 ? "1" : "";
           $defaut_txt=$defaut!="" ? "($defaut)" : "";        
           
           echo "[+] Voir plus de choix (recherches complémentaires dans l'annuaire LDAP)\n";
           echo "[D] Désactiver le compte\n";
           echo "[R] Ne rien faire\n";           
           echo "\nVotre choix : $defaut_txt";
           
           $choix=trim(str_replace("\n", "", fgets(STDIN)));
           
           if($choix=="" && $defaut!="")
              $choix=$defaut;

           if(array_key_exists($choix, $array_choix))
           {
              print("Correspondance :"
                 ."\n=> nom = ".$entries_ldap[$array_choix[$choix]]["$attr_ldap_nom"]["0"]
                 ."\n   prenom = ".$entries_ldap[$array_choix["$choix"]]["$attr_ldap_prenom"]["0"]
                 ."\n   mail = ".$entries_ldap[$array_choix["$choix"]]["$attr_ldap_mail"]["0"]
                 ."\n   login = ".$entries_ldap[$array_choix["$choix"]]["$attr_ldap_login"]["0"]."\n");
                 
              db_query($dbr, "UPDATE acces set login='".$entries_ldap[$array_choix["$choix"]]["$attr_ldap_login"]["0"]."', 
                                               courriel='".$entries_ldap[$array_choix["$choix"]]["$attr_ldap_mail"]["0"]."',
                                               source='1'
                              WHERE id='$acces_id'");
                              
              $all_filtres=0;
           }
           elseif($choix=="D")
           {
              $all_filtres=0;
              
              print("Désactivation\n");
              
              db_query($dbr, "UPDATE acces set niveau='-10' WHERE id='$acces_id'");
           }
           elseif($choix=="+")
           {
              $all_filtres=1;
              $i--;
           }
        }
        else
           echo "  > non trouvé(e) - fiche manuelle\n";

        echo "\n";
     }
  }

  aria_ldap_close($ldap);
  db_close($dbr);

?>        