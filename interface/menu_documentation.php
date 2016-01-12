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
  // Vérifications complémentaires au cas où ce fichier serait appelé directement
  if(!isset($_SESSION["authentifie"]))
  {
    session_write_close();
    header("Location:../index.php");
    exit();
  }

  if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
  {
    session_write_close();
    header("Location:composantes.php");
    exit();
  }

  print("<div class='centered_box'>
        <font class='TitrePage_16'>$_SESSION[onglet] - Documentation</font>
      </div>\n");

  // Si l'enregistrement date d'une année précédente, on affiche un avertissement
  
  if(strlen($_SESSION['authentifie'])==16) {
     $annee_len=1;
     $shift=0; // Décallage pour mb_substr
  }
  else {
     $annee_len=2;
     $shift=1;
  }

  $year_reg=mb_substr($_SESSION['authentifie'], 0, $annee_len, "UTF-8");

  if($shift)
     $year_reg="20" . $year_reg;
  else
     $year_reg="200" . $year_reg;

  $month_reg=mb_substr($_SESSION['authentifie'], (1+$shift), 2, "UTF-8");
  
  if($year_reg < date('Y', time())) {
      message("<center>
                  Votre enregistrement date d'une année antérieure.
                  <br>Merci de vérifier et mettre à jour chaque champ du menu 1 - Identité si nécessaire
                  <br>(en particulier votre numéro INE et l'année de première inscription dans cette université)
               </center>",$__WARNING);
  }

?>

<div class='fond_menu margin_10'>
  <font class='Texte_menu_3'><strong>Vous êtes enregistré(e) dans l'application : que faire maintenant ?</strong></font>
</div>
<div class='margin_10'>
  <font class='Texte'>
    <ol>
      <li>Complétez ou mettez à jour les <strong>menus 1 à 4</strong> du menu latéral (identité, cursus, ...) ; ces informations sont <strong>communes à toutes les composantes</strong>.</li>
      <li style='padding-top:20px'>Sélectionnez <strong>une ou plusieurs formations</strong> dans le menu "5-Précandidatures".</li>
      <li style='padding-top:20px'>Si vous souhaitez déposer un dossier dans une autre composante, cliquez sur "<a href='<?php echo "$GLOBALS[__CAND_DIR]/composantes.php"; ?>' class='lien2'>Choisir une autre composante</a>" dans le menu supérieur.</li>
      <li style='padding-top:20px'>Si un onglet <strong>"6 - Autres renseignements"</strong> apparait, complétez-le également (ceci dépend des formations choisies).</li>
      <li style='padding-top:20px'>Une fois les formations choisies, attendez leur verrouillage automatique (généralement après un délai de 48h). La date est indiquée sur chacun de vos choix.
      <br>Vous recevrez alors la liste des documents à envoyer <strong>par voie postale</strong> à la scolarité.</li>
      <li style='padding-top:20px'>Attention : dès le premier verrouillage, les menus Cursus, Langues et Infos Complémentaires seront également <strong>verrouillés</strong>.</li>
      <li style='padding-top:20px'>N'hésitez pas à vous référer au <a class='lien_rouge12' href='<?php echo "$__DOC_DIR/documentation.php"; ?>' target='_blank'><strong>mode d'emploi</strong></a> ainsi qu'au <a href='<?php echo "https://$_SERVER[SERVER_NAME]$GLOBALS[__CAND_DIR]/assistance/index.php"; ?>' class='lien2'>formulaire d'aide</a> présent en bas de chaque page.</li>
    </ol>
</div>    

