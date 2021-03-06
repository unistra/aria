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
   session_name("preinsc_gestion");
   session_start();

   include "../configuration/aria_config.php";
   include "$__INCLUDE_DIR_ABS/vars.php";
   include "$__INCLUDE_DIR_ABS/fonctions.php";
   include "$__INCLUDE_DIR_ABS/db.php";

    /* ----------------------------------- */
   function arguments($args)
   {
      $ret=array('exec'      => '',
                 'options'   => array(),
                 'flags'     => array(),
                 'arguments' => array());
      
      $ret['exec']=array_shift($args);
      
      while(($arg=array_shift($args))!=NULL) 
      {
         // Is it a option? (prefixed with --)
         if(substr($arg, 0, 2)==='--') 
         {
            $option=substr($arg, 2);
         
            // is it the syntax '--option=argument'?
            if(strpos($option,'=') !== FALSE)
               array_push($ret['options'], explode('=',$option, 2));
            else
               array_push($ret['options'], $option);
         
            continue;
         }
         
         // Is it a flag or a serial of flags? (prefixed with -)
         if(substr( $arg, 0, 1 )==='-')
         {
            for($i=1; isset($arg[$i]) ; $i++)
               $ret['flags'][]=$arg[$i];
         
            continue;
         }
         
         // finally, it is not option, nor flag
         $ret['arguments'][] = $arg;
         continue;
      }
      
      return $ret;
   } //function arguments
   
   /* ----------------------------------- */

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   $dbr=db_connect();

   if(isset($argv))
   {
      $arguments=arguments($argv);
      
      if(array_key_exists("flags", $arguments) && array_key_exists("arguments", $arguments))
      {
         foreach($arguments["flags"] as $key => $flag)
         {
            if($flag=="o" && array_key_exists($key, $arguments["arguments"]))
               $objet=$arguments["arguments"][$key];
            elseif($flag=="a" && array_key_exists($key, $arguments["arguments"]))
               $annee=$arguments["arguments"][$key];
         }
      }
      
      if(array_key_exists("options", $arguments))
      {
         foreach($arguments["options"] as $option)
         {
            if($option=="avant_incluse")
               $quand="avant1";
            elseif($option=="avant")
               $quand="avant0";
            elseif($option=="test")
               $mode_test=1;
         }
      }
     
      if(!isset($quand))
         $quand="en";
         
      if(!isset($mode_test))
         $mode_test=0;
   }
   else
      $arguments=array();
      
   // print_r($arguments);

   if(!isset($annee) || !isset($quand) || !isset($objet) || ($objet!="orph" && $objet!="all") || !ctype_digit($annee) || strlen($annee)!=4)
   {
      print("Usage : php nettoyeur.php -o <objet> -a <annee> [--avant|--avant_incluse] [-t]\n
Param�tres :
-o <objet>  : objet peut �tre  \"orph\" (fiches orphelines) ou \"all\" (candidatures + fiches orphelines r�sultantes) ;
-a <annee>  : annee concern�e par la suppression (format AAAA). Si l'option -o vaut \"all\", \"annee\" d�signe l'ann�e universitaire ; 
--avant|--avant_incluse : Indique si l'ann�e pr�cis�e est incluse ou non dans les donn�es � supprimer.
                           Si aucun param�tre n'est pr�cis�, seule l'ann�e indiqu�e sera prise en compte.\n
--test      : mode test/debug (aucune suppression).\n\n");
                           
      die();
   }
   
   $annee_univ_txt="$annee-".($annee+1);
                                
   if($quand=="avant0")
   {
      $quand_orph=$quand_all="avant";
      $quand2="(ann�e non incluse)";
   }
   elseif($quand=="avant1")
   {
      $quand_orph=$quand_all="avant";
      $quand2="(ann�e incluse)";
   }
   else
   {
      $quand_orph="en";
      $quand_all="de";
      $quand2="";
   }
   
   $test_txt=$mode_test ? "[TEST] " : "";
   
   
   if($objet=="orph")
      $txt="fiches orphelines cr��es $quand_orph $annee $quand2";
   elseif($objet=="all")
      $txt="candidatures (et fiches orphelines r�sultantes) $quand_all l'ann�e universitaire $annee_univ_txt $quand2";

   print("\nNote: en cas de suppression de candidats, les arborescences suivantes seront examin�es et nettoy�es : 
- $GLOBALS[__PUBLIC_DIR_ABS]
- $GLOBALS[__CAND_MSG_STOCKAGE_DIR_ABS]\n");

   $confirmation="";

   while($confirmation!="o" && $confirmation!="N" && $confirmation!="n")
   {
      print("\n$test_txt"."Supprimer toutes les $txt ? [o/N] ");
      $confirmation=str_replace("\n", "", fgets(STDIN));
      
      if($confirmation=="" || $confirmation=="n" || $confirmation=="N")
         die("Annul�.\n");
   }
   
   print("$test_txt"."\nTraitement ... \n");
   
   // Id des composantes pour le nettoyage des fichiers temporaires (requ�te commune aux suppressions compl�tes et des fiches orphelines)
   $res_comp=db_query($dbr, "SELECT $_DBC_composantes_id FROM $_DB_composantes");
   $rows_comp=db_num_rows($res_comp);

   if($objet=="orph")
   {
      $annee_orph=ltrim(substr($annee,2,2), "0");
      
      switch($quand)
      {
         case "en"     : $condition_candidat="$_DBC_candidat_id like '$annee_orph%'";
                         break;

         case "avant0" : $limite=$annee_orph;   // ann�e non incluse
                         $condition_candidat="$_DBC_candidat_id < '$limite"."010100000000000'";
                         break;

         case "avant1" : $limite=$annee_orph+1; // ann�e incluse
                         $condition_candidat="$_DBC_candidat_id < '$limite"."010100000000000'";
                         break;
      }
      
      $res_cand_annee=db_query($dbr, "SELECT $_DBC_candidat_id FROM $_DB_candidat WHERE $condition_candidat");
      
      $rows_cand_annee=db_num_rows($res_cand_annee);
   
      $suppr_reps=0;      
      $nb_suppr_candidats=0;   
   
      if($rows_cand_annee)
      {
         $ten_percent=floor($rows_cand_annee/10);
         
         for($i=0; $i<$rows_cand_annee; $i++)
         {
            if(($i % $ten_percent)==0)
            {
               $q=($i/$ten_percent)*10;
               
               print("$q%...");
            }
            
            list($c_id)=db_fetch_row($res_cand_annee, $i);

            if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$c_id'")))
            {
               // Suppression des messages et des r�pertoires de l'utilisateur sur le disque
               
               // Sous r�pertoire du candidat
               $sous_rep=sous_rep_msg($c_id);
               
               if(ctype_digit($c_id) && is_dir("$GLOBALS[__CAND_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$c_id") && is_writable("$GLOBALS[__CAND_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$c_id"))
               {
                  if($mode_test)
                     print("$test_txt"."deltree(\"$GLOBALS[__CAND_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$c_id\")\n");
                  else
                     deltree("$GLOBALS[__CAND_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$c_id");
                     
                  $suppr_reps++;
                  
                  for($j=0; $j<$rows_comp; $j++)
                  {
                     list($rm_comp_id)=db_fetch_row($res_comp, $j);
                  
                     if(ctype_digit($rm_comp_id) && is_dir("$GLOBALS[__PUBLIC_DIR_ABS]/$rm_comp_id") && is_dir("$GLOBALS[__PUBLIC_DIR_ABS]/$rm_comp_id/$c_id") && is_writable("$GLOBALS[__PUBLIC_DIR_ABS]/$rm_comp_id/$c_id"))
                     {
                        if($mode_test)
                           print("$test_txt"."deltree(\"$GLOBALS[__PUBLIC_DIR_ABS]/$rm_comp_id/$c_id\")\n");
                        else
                           deltree("$GLOBALS[__PUBLIC_DIR_ABS]/$rm_comp_id/$c_id");
                           
                        $suppr_reps++;                  
                     }                  
                  }
               }
               
               if($test_txt)
               {
                  print("$test_txt"."DELETE FROM $_DB_candidat WHERE $_DBC_candidat_id='$c_id';\n$test_txt"."DELETE FROM $_DB_hist WHERE $_DBC_hist_c_id='$c_id';\n");
               }
               else
               {
                  db_query($dbr, "DELETE FROM $_DB_candidat WHERE $_DBC_candidat_id='$c_id'; 
                                  DELETE FROM $_DB_hist WHERE $_DBC_hist_c_id='$c_id';");
               }
               
               $nb_suppr_candidats++;
            }
         }
      }
      
      db_free_result($res_cand_annee);
   }
   elseif($objet=="all")
   {
      switch($quand)
      {
         case "en"     : $condition_candidature="$_DBC_cand_periode='$annee'";
                         $condition_candidature_test="$_DBC_cand_periode!='$annee'";
                         break;

         case "avant0" : $condition_candidature="$_DBC_cand_periode<'$annee'";
                         $condition_candidature_test="$_DBC_cand_periode>='$annee'";
                         break;

         case "avant1" : $condition_candidature="$_DBC_cand_periode<='$annee'";
                         $condition_candidature_test="$_DBC_cand_periode>'$annee'";
                         break;
           
      }
      
      // Pour la suppression des candidatures :
      // 1 - on r�cup�re d'abord les candidats concern�s, 
      // 2 - on supprime les candidatures
      // 3 - on reprend la liste des candidats et on supprime les fiches devenues orphelines
      $res_cand_annee=db_query($dbr, "SELECT distinct($_DBC_cand_candidat_id) FROM $_DB_cand WHERE $condition_candidature");
      
      // Suppression des lignes de l'historique portant sur les candidatures � supprimer
      if($mode_test)
         print("$test_text"."DELETE FROM $_DB_hist WHERE $_DBC_hist_element_id IN (SELECT $_DBC_cand_id FROM $_DB_cand WHERE $condition_candidature)\n");
      else
         db_query($dbr, "DELETE FROM $_DB_hist WHERE $_DBC_hist_element_id IN (SELECT $_DBC_cand_id FROM $_DB_cand WHERE $condition_candidature)");
      
      // Suppression des lignes des candidatures
      $res_cand=db_query($dbr, "SELECT count(*) FROM $_DB_cand WHERE $condition_candidature");
      
      list($nb_suppr_candidatures)=db_fetch_row($res_cand, 0);
      
      if($nb_suppr_candidatures=="")
         $nb_suppr_candidatures=0;
         
      db_free_result($res_cand);
      
      if($mode_test)
         print("$test_txt"."DELETE FROM $_DB_cand WHERE $condition_candidature\n");
      else
         db_query($dbr, "DELETE FROM $_DB_cand WHERE $condition_candidature");
      
      // Suppression des fiches devenues orphelines
      $rows_cand_annee=db_num_rows($res_cand_annee);
   
      $nb_suppr_orph=0;
      $suppr_reps=0;
   
      $ten_percent=floor($rows_cand_annee/10);
   
      if($rows_cand_annee)
      {
         for($i=0; $i<$rows_cand_annee; $i++)
         {
            if(($i % $ten_percent)==0)
            {
               $q=($i/$ten_percent)*10;
               
               print("$q%...");
            }  
            
            list($c_id)=db_fetch_row($res_cand_annee, $i);

            if($mode_test) // en mode test, la requ�te est diff�rente puisque la suppression des candidatures n'a pas eu lieu
               $requete="SELECT * FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$c_id' AND $_DBC_cand_periode!='$annee'";
            else
               $requete="SELECT * FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$c_id'";
               
            if(!db_num_rows(db_query($dbr, "$requete")))
            {
               // Suppression des messages et des r�pertoires de l'utilisateur sur le disque
              
               // Sous r�pertoire du candidat
               $sous_rep=sous_rep_msg($c_id);
        
               if(ctype_digit($c_id) && is_dir("$GLOBALS[__CAND_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$c_id") && is_writable("$GLOBALS[__CAND_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$c_id"))
               {
                  if($mode_test)
                     print("$test_txt"."[ORPH] deltree(\"$GLOBALS[__CAND_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$c_id\")\n");
                  else
                     deltree("$GLOBALS[__CAND_MSG_STOCKAGE_DIR_ABS]/$sous_rep/$c_id");
                     
                  $suppr_reps++;
               
                  for($j=0; $j<$rows_comp; $j++)
                  {
                     list($rm_comp_id)=db_fetch_row($res_comp, $j);
                  
                     if(ctype_digit($rm_comp_id) && is_dir("$GLOBALS[__PUBLIC_DIR_ABS]/$rm_comp_id") && is_dir("$GLOBALS[__PUBLIC_DIR_ABS]/$rm_comp_id/$c_id") && is_writable("$GLOBALS[__PUBLIC_DIR_ABS]/$rm_comp_id/$c_id"))
                     {
                        if($mode_test)
                           print("$test_txt"."[ORPH] deltree(\"$GLOBALS[__PUBLIC_DIR_ABS]/$rm_comp_id/$c_id\")\n");
                        else
                           deltree("$GLOBALS[__PUBLIC_DIR_ABS]/$rm_comp_id/$c_id");
                           
                        $suppr_reps++;                  
                     }                  
                  }
               }
               
               if($mode_test)
                  print("$test_txt"."[ORPH] DELETE FROM $_DB_candidat WHERE $_DBC_candidat_id='$c_id';\n$test_txt"."[ORPH] DELETE FROM $_DB_hist WHERE $_DBC_hist_c_id='$c_id';\n");
               else
                  db_query($dbr, "DELETE FROM $_DB_candidat WHERE $_DBC_candidat_id='$c_id'; 
                                  DELETE FROM $_DB_hist WHERE $_DBC_hist_c_id='$c_id';");               
                      
               $nb_suppr_orph++;                         
            }
         }
      }
      
      db_free_result($res_cand_annee);
   }
   /*   
   print("Reindex des tables \"candidat\", \"candidatures\" et \"historique\" ...");      

   db_query($dbr, "REINDEX TABLE $_DB_candidat;
                   REINDEX TABLE $_DB_cand; 
                   REINDEX TABLE $_DB_hist"); 
   
   print("Ok.\n");
   */
   
   db_free_result($res_comp);   

   if($mode_test)
      print("\n======== $test_txt ========\n");

   if(isset($nb_suppr_candidats))
   {
      if($nb_suppr_candidats==0)
         print("Aucune fiche n'a �t� supprim�e.\n");
      elseif($nb_suppr_candidats==1)
         print("Une fiche orpheline a �t� supprim�e.\n");
      else
         print("$nb_suppr_candidats fiches orphelines ont �t� supprim�es.\n");
   }
   
   if(isset($nb_suppr_candidatures))
   {
      if($nb_suppr_candidatures==0)
         print("Aucune candidature n'a �t� supprim�e.\n");
      elseif($nb_suppr_candidatures==1)
         print("Une candidature a �t� supprim�e.\n");
      else
         print("$nb_suppr_candidatures candidatures ont �t� supprim�es.\n");
   }
    
   // Suppression des anciennes candidatures et des fiches orphelines r�sultantes
   if(isset($nb_suppr_orph))
   {
      if($nb_suppr_orph==0)
         print("Aucune fiche orpheline n'a �t� supprim�e par la suite.\n");
      elseif($nb_suppr_orph==1)
         print("Une fiche orpheline a �t� supprim�e par la suite.\n");
      else
         print("$nb_suppr_orph fiches orphelines ont �t� supprim�es par la suite.\n");
   }
      
   
   if(isset($suppr_reps))
   {
      if($suppr_reps==0)
         print("Aucun r�pertoire utilisateur n'a �t� supprim�.\n");
      elseif($suppr_reps==1)
         print("Un r�pertoire utilisateur a �t� supprim�.\n");
      else
         print("$suppr_reps r�pertoires utilisateurs ont �t� supprim�s.\n");
   }
   
   db_close($dbr);

?>

