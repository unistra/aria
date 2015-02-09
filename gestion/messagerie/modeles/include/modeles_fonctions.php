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

// Remplacement des macros prédéfinies dans l'éditeur de lettres
// Entrée :
// - texte à traiter
// - informations du candidat (tableau)
// - filière
// - cursus (tableau)
// Note sur les macros : les majuscules sont importantes !!

function traitement_macros($txt, $cand_array, $cursus_array)
{
  // Civilité
  $txt=preg_replace("/%Civilit.%/u", ucfirst(strtolower($cand_array["civ_texte"])), $txt);
  $txt=preg_replace("/%civilit.%/u", strtolower($cand_array["civ_texte"]), $txt);
  $txt=preg_replace("/%CIVILIT.%/u", strtoupper($cand_array["civ_texte"]), $txt);

  // Nom
  $txt=preg_replace("/%Nom%/", ucfirst(mb_strtolower($cand_array["nom"]), "UTF-8"), $txt);
  $txt=preg_replace("/%nom%/", mb_strtolower($cand_array["nom"], "UTF-8"), $txt);
  $txt=preg_replace("/%NOM%/", mb_strtoupper($cand_array["nom"], "UTF-8"), $txt);

  // Prénom
  $txt=preg_replace("/%Pr.nom%/u", ucfirst(mb_strtolower($cand_array["prenom"]), "UTF-8"), $txt);
  $txt=preg_replace("/%pr.nom%/u", mb_strtolower($cand_array["prenom"], "UTF-8"), $txt);
  $txt=preg_replace("/%PR.NOM%/u", mb_strtoupper($cand_array["prenom"], "UTF-8"), $txt);

  // Date de naissance
  $txt=str_ireplace("%naissance%", $cand_array["naissance"], $txt);

  // Ville de naissance
  $txt=str_ireplace("%ville_naissance%", $cand_array["lieu_naissance"], $txt);

  // Pays de naissance
  $txt=str_ireplace("%pays_naissance%", $cand_array["pays_naissance"], $txt);

  // Année universitaire
  $Y=date("Y");
  $Z=$Y+1;
  $annee_txt="$Y-$Z";
  $txt=preg_replace("/%ann.e_universitaire%/u", $annee_txt, $txt);

  // Cursus
  $count_cursus=count($cursus_array);

  if($count_cursus)
  {
    // on ne prend que les 2 derniers diplomes obtenus
    // TODO : à vérifier
    $texte_cursus="";

    if($count_cursus>2)
      $i=$count_cursus-2;
    else
      $i=0;

    for(; $i<$count_cursus; $i++)
    {
      if(isset($cursus_array[$i]["lieu"]))
        $texte_cursus .=$cursus_array[$i]["cursus"] . " " . $cursus_array[$i]["lieu"] . " (". $cursus_array[$i]["date"] . ")\n";
      else
        $texte_cursus .=$cursus_array[$i]["cursus"] . " (". $cursus_array[$i]["date"] . ")\n";
    }

    $txt=str_ireplace("%cursus%", $texte_cursus, $txt);
  }
  else
    $txt=str_ireplace("%cursus%", "- Néant", $txt);

  // Grammaire : %masculin/féminin%

  if(preg_match_all("/%[a-zA-Zàáâãäåçèéêëìíîïñðòóôõöùúûüýÿ]+\/[a-zA-Zàáâãäåçèéêëìíîïñðòóôõöùúûüýÿ]+%/", $txt, $resultats))
   {
      foreach($resultats[0] as $valeur)
      {
         $vals=explode("/", $valeur);

         $masculin=str_replace("%","", $vals[0]);
         $feminin=str_replace("%","", $vals[1]);

         if($cand_array["civilite"]=="M")
            $txt=str_replace($valeur, $masculin, $txt);
         else
            $txt=str_replace($valeur, $feminin, $txt);
      }
   }

  // Date

  $txt=str_ireplace("%date%", date_fr("j F Y", time()), $txt);

  return $txt;
}

?>
