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
// Ce fichier contient les données insérées par défaut lorsqu'une composante est créée (décision, motifs de refus, etc.)
// Si vous conservez ces données, vous devrez si besoin les modifier ou les compléter via l'interface de configuration.

// DECISIONS
// Insertion si le paramètre $__DEFAUT_DECISIONS=1 est présent dans le fichier de configuration
function insert_default_decisions($dbr, $comp_id)
{
  db_query($dbr, "INSERT INTO $GLOBALS[_DB_decisions_comp] (SELECT '$comp_id', $GLOBALS[_DBC_decisions_id] FROM $GLOBALS[_DB_decisions] ORDER BY id)");
}


// Motifs de refus : tableau de tableaux à trois éléments :
// - le texte court : utilisé dans les menus et dans les lettres lorsqu'il n'y a pas de texte long.
// - le texte long (facultatif) est utilisé comme paragraphe dans les lettres (si renseigné).
// - un booléen (0 ou 1) indiquant si le motif est exclusif (devant être utilisé seul, et prioritaire sur les autres) ou non.

// Pour ne pas insérer de motifs par défaut, deux solutions :
// - soit mettre à variable $__DEFAUT_MOTIFS dans le fichier de configuration (préféré)
// - soit commenter la variable $__DEFAUT_MOTIFS_REFUS
// - soit supprimer son contenu

$__DEFAUT_MOTIFS_REFUS=array(
  array("Résultats insuffisants", "", 0),
  array("Résultats insuffisants à l'examen du BTS", "", 0),
  array("Résultats insuffisants en 1er cycle", "", 0),
  array("Pas d'avis favorable de poursuite d'études", "", 0),
  array("Prérequis en informatique non satisfaits", "", 0),
  array("Cursus inadapté", "", 0),
  array("Nombre maximum d'inscriptions atteint",
      "Le dossier n'a pu etre retenu compte tenu du nombre de dossiers soumis et de leur qualité.", 1),
  array("Non présentation à l'entretien de sélection","", 0),
  array("Pas d'offre apprentissage adaptée",
      "Pas de proposition de contrat d'apprentissage en adéquation avec votre parcours", 0),
  array("Candidat sans contrat d'apprentissage",
      "Vous ne justifiez pas de la signature d'un contrat d'apprentissage.", 0));


// =================================================================
//      Fonctions relatives aux données décrites ci-dessus
// =================================================================

// Fonction d'insertion des motifs par défaut
// Deux arguments obligatoires :
// - $dbr : base de données ouverte
// - $comp_id : identifiant de la composante
 
function insert_default_motifs($dbr, $comp_id)
{
  if(array_key_exists("__DEFAUT_MOTIFS_REFUS", $GLOBALS) && is_array($GLOBALS["__DEFAUT_MOTIFS_REFUS"]))
  {
    foreach($GLOBALS["__DEFAUT_MOTIFS_REFUS"] as $array_decision)
    {
      // Chaque élément de $GLOBALS["__DEFAUT_MOTIFS_REFUS"] doit également être un tableau à
      // trois éléments (texte court / texte long / caractère exclusif)
      // Le contenu de ces éléments relève de l'administrateur ...
      if(is_array($array_decision) && count($array_decision)==3)
      {
        // Calcul du nouvel identifiant
        // Avec max(), on aura un résultat, même vide
        list($new_id)=db_fetch_row(db_query($dbr, "SELECT max($GLOBALS[_DBC_motifs_refus_id])+1 FROM $GLOBALS[_DB_motifs_refus]"), 0);

        if($new_id=="") $new_id=0;

        $exclusif=$array_decision[2]=='1' ? 1 : 0;
        $motif_court=preg_replace("/[']+/", "''", stripslashes($array_decision[0]));
        $motif_long=preg_replace("/[']+/", "''", stripslashes($array_decision[1]));

        // TODO : créer des fonctions d'accès pour toutes ces opérations ...
        db_query($dbr, "INSERT INTO $GLOBALS[_DB_motifs_refus] VALUES ('$new_id', '$motif_court', '$motif_long', '$exclusif', '$comp_id')");
      }
    }
  }
  
  // Mise à jour de la séquence
   db_query($dbr, "SELECT setval('motifs_refus_id_seq', (select max($GLOBALS[_DBU_motifs_refus_id]) from $GLOBALS[_DB_motifs_refus]))");
}
