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
<!--    
    <p class='no_margin'>Sur l'Interface de Précandidatures, il n'est <u>plus nécessaire</u> de télécharger de dossier papier ou PDF : cette
    interface EST votre dossier, elle devra contenir toutes les informations qui vous sont demandées.</p>

    <p>Dans le menu gauche, vous devez <strong>COMPLETER CHAQUE SECTION</strong>, de l'Identité (numéro 1) aux précandidatures (numéro 5).</p>

    <p class='Texte_important'>Attention : les menus 2, 3 et 4 sont <strong>communs à toutes les composantes</strong> (au cas où vous voudriez déposer des voeux dans
    plusieurs établissements). Remplissez ces informations <strong>une fois pour toutes</strong>, car si l'un de vos voeux est verrouillé
    par une composante, vous ne pourrez plus les modifier !</p>

    <p>Tous ces renseignements sont <b>OBLIGATOIRES</b>. Si vous ne les complétez pas, votre dossier risque de <strong>NE PAS
    ETRE EXAMINE</strong>.</p>
  </font>
</div>

<div class='fond_menu margin_10'>
  <font class='Texte_menu'><strong>2. Onglet Spécial : "Autres renseignements"</strong></font>
</div>
<div class='margin_10'>
  <font class='Texte'>
    <p class='no_margin'>Pour certaines formations choisies, des <strong>renseignements supplémentaires</strong> vous sont demandés.</p>

    <p>Si c'est le cas, <strong>après avoir sélectionné au moins l'une de ces formations</strong>, vous verrez apparaître
    une <strong>SECTION N°6</strong> que vous devrez <strong>également compléter</strong>. Les informations demandées
    sont là encore <strong>OBLIGATOIRES</strong>.</p>
  </font>
</div>

<div class='fond_menu margin_10'>
  <font class='Texte_menu'><strong>3. Ensuite ?</strong></font>
</div>
<div class='margin_10'>
  <font class='Texte'>
    <p class='no_margin'>Une fois votre fiche remplie, vous devez <strong>attendre le verrouillage (automatique) de chaque formation
    demandée</strong>. La date de ce verrouillage est visible dans le menu <strong>5 - Précandidatures</strong>, sur
    chaque voeu formulé. Pendant ce temps d'attente, vous pouvez modifier librement les voeux sélectionnés dans cette
    composante.</p>

    <p>Dès qu'un voeu est verrouillé, la <strong>liste des justificatifs</strong> à transmettre à la scolarité
    <strong>PAR VOIE POSTALE UNIQUEMENT</strong> vous sera envoyée.</p>

    <p class='Texte_important'>N'oubliez pas de consulter <strong>REGULIEREMENT</strong> votre <strong>messagerie
    électronique</strong> afin de suivre l'évolution de votre fiche et les demandes qui pourraient vous être
    faites.</p>
  </font>
</div>

<div class='fond_menu margin_10'>
  <font class='Texte_menu'><strong>4. Mode d'emploi complet</strong></font>
</div>
<div class='margin_10'>
  <font class='Texte'>
    <p class='no_margin'>Avant de poser une question à la scolarité, merci de lire <strong>LE MODE D'EMPLOI</strong> à l'adresse suivante :</p>
    <div style='text-align:center; padding-top:10px;'>
      <a class='lien_rouge_14' href='<?php echo "$__DOC_DIR/documentation.php"; ?>' target='_blank'><strong>Mode d'emploi</strong></a>
    </div>
  </font>
</div>
-->
