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

      message("Ce système d'aide permet de vous orienter en fonction de vos questions et de faciliter certaines demandes.", $__INFO);
   ?>

   <table align='center' style='padding-bottom:20px;'>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Enregistrement et connexion</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=navigateur' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;L'interface ne s'affiche pas correctement / je reviens toujours sur la page d'accueil, même lorsque je parviens à m'identifier.</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=auth' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je suis déjà enregistré(e), mais je n'ai plus mes identifiants et depuis, j'ai changé d'adresse électronique (<i>email</i>) ...</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=auth' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je me suis trompé(e) d'adresse électronique lors de mon enregistrement, que faire ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Candidature et formations</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=doc' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Comment déposer un dossier de précandidature en ligne ?</a>
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
         <!-- <a href='form_scolarite.php?t=1' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je souhaite ajouter une formation, mais la session est déjà fermée.</a> -->
         <a href='aide.php?s=scolarite' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je souhaite ajouter une formation, mais la session est déjà fermée.</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <!-- <a href='form_deverrouillage.php' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je souhaite déverrouiller certaines formations pour effectuer des modifications sur ma fiche.</a> -->
         <a href='aide.php?s=deverrouillage' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je souhaite déverrouiller certaines formations pour effectuer des modifications sur ma fiche.</a>
      </td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Liste des justificatifs et pièces à envoyer</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=justificatifs&v=1' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;La date de verrouillage est passée mais je n'ai pas reçu la liste des justificatifs, pourquoi ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=pdf' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai reçu un message contenant des fichiers au format PDF, mais je n'arrive pas à les ouvrir.</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=justificatifs&a=1' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai reçu la liste des justificatifs, à qui et comment dois-je envoyer tous ces documents ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=justificatifs&n=1' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai demandé plusieurs formations, combien de fois dois-je envoyer mes justificatifs ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=cursus' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Dans le menu "2-Cursus", toutes mes étapes sont marquées "En attente des justificatifs", comment changer ce statut ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=justificatifs&d=1' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je n'ai pas encore les derniers relevés de notes de mon année en cours, que dois-je faire ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=contact_scol2' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je suis un(e) candidat(e) étranger(e), on me demande d'envoyer des justificatifs ou des pièces qui n'existent pas dans mon pays. Que faire ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Admission et Inscription</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=resultats' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Quand et comment obtiendrai-je les résultats de mon admission ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=inscr' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai reçu une lettre d'admission, et je ne parviens pas à m'inscrire malgré les instructions reçues, que dois-je faire ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Autres :</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=contact_admin' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Mon problème ne se trouve pas dans ce tableau, à qui dois-je m'adresser ?</a>
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
