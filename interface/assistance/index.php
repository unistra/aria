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
   // Message affiché lorsqu'une page n'a pas été trouvée.
   // L'authentification n'est pas nécessaire.
   session_name("preinsc");
   session_start();

   include "../../configuration/aria_config.php";
   include "$__INCLUDE_DIR_ABS/vars.php";
   include "$__INCLUDE_DIR_ABS/fonctions.php";
   include "$__INCLUDE_DIR_ABS/db.php";

   if(isset($_SESSION["CURRENT_FILE"]))
      $_SESSION["from_page"]=$_SESSION["CURRENT_FILE"];
   else
      $_SESSION["from_page"]="../index.php";

   unset($_SESSION["form_composante_id"]);

   // EN-TETE
   en_tete_candidat();

   // MENU SUPERIEUR
   menu_sup_simple();
?>
<div class='main'>
   <?php
       titre_page_icone("[Assistance aux candidats] - Accueil", "help-browser_32x32_fond.png", 15, "L");

      message("Cet assistant vous permet de vous orienter en fonction de vos questions et de faciliter certaines demandes.", $__INFO);
   ?>

   <table align='center' style='padding-bottom:20px;'>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Avant de commencer, quelques liens utiles</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='<?php echo "$__DOC_DIR/documentation.php"; ?>' target='_blank' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Documentation de l'interface (procédure, composantes, messagerie...)</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='<?php echo "$__DOC_DIR/limites.php"; ?>' target='_blank' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Dates des sessions de candidatures</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='<?php echo "$__DOC_DIR/faq.php"; ?>' target='_blank' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Les questions fréquentes</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_page' height='15px'></td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Votre question concerne ...</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='index_acces_interface.php' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;L'accès à l'interface Aria (identifiant / mot de passe, changement d'adresse email, ...)</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='index_utilisation.php' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;L'utilisation de l'interface (trouver une formation, déposer un dossier, modifier des informations ...)</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='index_justificatifs.php' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Les documents (justificatifs et autres) à envoyer à la scolarité</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='index_resultat.php' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Le résultat de ma candidature.</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='index_autre.php' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Autre</a>
      </td>
   </tr>
   </table>
   
   <div class='centered_box' style='padding-bottom:20px;'>
      <a href='<?php echo $_SESSION["from_page"]; ?>' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
</div>
<?php
   pied_de_page_simple();
?>
</body></html>
