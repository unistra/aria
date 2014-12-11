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

   unset($_SESSION["form_composante_id"]);

   // EN-TETE
   en_tete_candidat();

   // MENU SUPERIEUR
   menu_sup_simple();
?>
<div class='main'>
   <?php
       titre_page_icone("[Assistance aux candidats] - Utilisation de l'interface", "help-browser_32x32_fond.png", 15, "L");
   ?>

   <table align='center' style='padding-bottom:20px;'>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Questions relatives à l'utilisation de l'interface Aria</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=doc' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Comment déposer un dossier de précandidature en ligne ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=messagerie' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai un message en attente, comment accéder à ma messagerie Aria ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=contact_scol' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai une question concernant les modalités d'accès à une formation, à qui dois-je m'adresser ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=formations' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Dans le menu "5-Précandidatures", je ne trouve pas la formation souhaitée dans la liste.</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=scolarite' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je souhaite ajouter une formation, mais la session est déjà fermée.</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=annulee' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai par erreur 'annulé' une candidature verrouillée, et je ne peux plus ajouter la formation. Comment rétablir ma candidature ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=deverrouillage' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je souhaite déverrouiller certaines formations pour effectuer des modifications sur ma fiche.</a>
      </td>
   </tr>
   </table>
   
   <div class='centered_box' style='padding-bottom:20px;'>
      <a href='index.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
</div>
<?php
   pied_de_page_simple();
?>
</body></html>
