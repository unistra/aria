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

   $php_self=$_SERVER['PHP_SELF'];
   // $_SESSION['CURRENT_FILE']=$php_self;

   // EN-TETE
   en_tete_candidat();

   // MENU SUPERIEUR
   menu_sup_simple();
?>
<div class='main'>
   <?php
       titre_page_icone("[Assistance aux candidats]", "help-browser_32x32_fond.png", 15, "L");
   ?>

   <table align='center' style='padding:0px 40px 20px 40px;'>
   <?php
      if(isset($_GET["s"]) && $_GET["s"]=="auth")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Problèmes d'identification sur l'application :</strong>
            <br>&#8226;&nbsp;&nbsp;Je suis déjà enregistré(e), mais je n'ai plus mes identifiants et depuis, j'ai changé d'adresse électronique (<i>email</i>).
            <br>&#8226;&nbsp;&nbsp;Je me suis trompé(e) d'adresse électronique lors de mon enregistrement, que faire ?
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:top;'>
            Vous pouvez utiliser <a href='form_adresse.php' class='lien_bleu_12' style='vertical-align:top;'><strong>ce formulaire</strong></a> pour demander
            une modification de votre adresse électronique.
            <br><br>
            De nouveaux identifiants vous seront renvoyés.
         </font>
         <br><br>
         <font class='Texte_important'>
            <strong>Vous ne devez en aucun cas vous enregistrer plusieurs fois sur l'interface : si vous possédez plusieurs fiches, elles
            risquent d'être supprimées sans préavis et vos candidatures ne seront alors pas traitées.</strong>
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_utilisation.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="reception") {
        $domain = explode("@", $GLOBALS["__EMAIL_NOREPLY"]);
        $domain = count($domain) == 2 ? $domain[1] : $domain[0];
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Vous n'avez pas reçu vos identifiants après enregistrement</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:top;'>
            &#8226;&nbsp;&nbsp;Vérifiez bien que votre messagerie autorise les emails venant du domaine "<b><?php echo $domain; ?></b>"
            <br>&#8226;&nbsp;&nbsp;Vérifiez également votre dossier "<b>Spams</b>" ou "<b>Indésirables</b>"
            <br>&#8226;&nbsp;&nbsp;Si vous venez de vous enregistrer, merci de patienter quelques heures, il arrive parfois que des messages soient transmis après un certain délai.
            <br>&#8226;&nbsp;&nbsp;Si vous ne les avez toujours pas reçu passé ce délai, vous pouvez faire une demande de changement d'adresse <b><a href='form_adresse.php' class='lien_bleu_12' style='vertical-align:top;'>sur ce formulaire</a></b>
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_acces_interface.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="doc")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Comment déposer un dossier de précandidature en ligne ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:bottom;'>
            Nous vous conseillons de lire intégralement la documentation <a href='<?php echo "$__DOC_DIR/documentation.php"; ?>' target='_blank' class='lien_bleu_12' style='vertical-align:top;'><strong>sur cette page</strong></a>.
            <br><br>
            Le chapitre "<strong>I - Déroulement d'une précandidature en ligne</strong>" résume en particulier les différentes étapes d'un dépôt de dossier.
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_utilisation.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="cursus")
      {
      ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Dans le menu "2-Cursus", toutes mes étapes sont marquées "En attente des justificatifs", comment changer ce statut ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Lorsque vous ajoutez une étape à votre cursus, l'état est toujours "En attente des justificatifs" par défaut. Il indique que la scolarité
            attend les justificatifs ou qu'elle n'a pas encore traité ceux qu'elle a reçus (en fonction du nombre de dossiers reçus, le traitement
            peut prendre du temps).
            <br><br>
            Une fois les pièces reçues, la scolarité validera ou non chaque étape. S'il manque des documents, vous serez normalement averti(e) via un
            message de l'application.
            <br><br>
            <strong>
               - Si le statut du cursus ne change pas, cela peut simplement signifier que la scolarité n'a pas encore traité votre dossier
               <br>- Si votre dossier est marqué "recevable", cela signifie en général que les justificatifs ont été traités mais que la scolarité n'a 
               pas modifié le statut de votre cursus sur l'interface.
            </strong>.
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_justificatifs.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="deverrouillage")
      {
      ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Je souhaite déverrouiller certaines formations pour effectuer des modifications sur ma fiche.</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Vous pouvez demander le déverrouillage d'une ou plusieurs formations via le formulaire prévu à cet effet.
            <br><br>
            <u><strong>Avant de le compléter, merci de vérifier chacun des points suivants :</strong></u>
            <br>
            <ol style='list-style-type:decimal; text-align:justify'>
               <li>Si les informations à modifier sont dans le menu <strong>1 - Identité</strong>, aucun déverrouillage n'est
                  nécessaire : vous pouvez mettre à jour ces informations <strong>à tout moment</strong>.
               </li>
               <li style='padding-top:20px;'>Pour chaque voeu à déverrouiller, vérifiez bien que les candidatures sont <strong>encore ouvertes</strong>.
                  Si elles sont closes, vous devez contacter directement la scolarité et détailler les modifications à apporter à votre fiche.
               </li>
               <li style='padding-top:20px;'>Si vos voeux verrouillés sont répartis sur plusieurs composantes, alors la modification des 
                  menus <strong>2-Cursus</strong>, <strong>3-Langues</strong>, et <strong>4-Informations complémentaires</strong> est
                  vivement déconseillée, car ces informations sont communes à toutes les composantes de l'Université. Dans ce cas précis, il est également 
                  préférable de contacter directement la scolarité de l'une des composantes pour qu'elle effectue elle-même les modifications.
                  <br><br><strong><u>Attention :</u></strong> si les modifications concernent votre cursus (diplômes, notes, ...), veillez à bien prévenir
                  <u>chaque composante</u> de ces modifications, et vérifiez bien qu'elles ont reçu les justificatifs à jour.
               </li>
               <li style='padding-top:20px;'>Vous devez être <strong>authentifié(e) sur l'interface</strong> afin d'accéder au formulaire.</li>
            </ol>

            <div class='centered_box' style='padding-top:20px;'>
               <a href='form_deverrouillage.php' target='_self' class='lien_bleu_12'><strong>Accéder au formulaire</strong></a>
            </div>

         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_utilisation.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="formations")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Dans le menu "5-Précandidatures", je ne trouve pas la formation souhaitée dans la liste.</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <u><strong>Plusieurs cas possibles :</strong></u>
            <ul style='text-align:justify'>
               <li>
                  <strong>Aucun dépôt de dossier n'est nécessaire pour la formation voulue.</strong>
                  <br>C'est par exemple les cas de nombreuses <strong>1ère années de Licence (L1)</strong> (attention : certains L1 contingentés nécessitent le dépôt d'une candidature). Si vous avez obtenu votre baccalauréat (ou équivalent), seule <u>l'inscription</u> est nécessaire pour entrer en L1, pas le dépôt d'un dossier de candidature.
                  <br><br>
                  <u>Conseil</u> : en fonction de votre situation et des diplômes en votre possession, vérifiez toujours les conditions et les modalités d'accès à la formation que vous souhaitez auprès de la scolarité.
               </li>

               <li style='padding-top:20px;'><strong>Aucune session de candidatures n'est ouverte pour cette formation.</strong>
                  <br><u>Solution</u> : si les candidatures <strong>ne sont pas encore ouvertes</strong>, vous devez attendre l'ouverture de la session de candidatures. Si elles sont déjà closes, vous pouvez contacter la
                  scolarité pour savoir si elle accepte les candidatures tardives.
               </li>

               <li style='padding-top:20px;'><strong>Le nombre de dossiers que vous pouvez déposer est limité dans une composante de l'Université, et la limite est déjà atteinte sur votre fiche.</strong>
                  <br><u>Solution</u> : Vous devez réfléchir à la priorité de vos voeux afin de respecter la limite imposée par la composante, en supprimant éventuellement certaines formations sélectionnées.
               </li>
               
               <li style='padding-top:20px;'>
                  <strong>Vous avez déjà déposé un dossier pour cette formation cette année</strong>
                  <br>Si la décision a déjà été rendue par la Commission Pédagogique, vous ne pouvez pas déposer un second dossier, même s'il existe une seconde session de candidatures.
               </li>

               <li style='padding-top:20px;'><strong>La formation recherchée n'est pas proposée par la composante que vous avez sélectionnée.</strong>
                  <br><u>Solution</u> : Utilisez le menu "Rechercher une formation" (menu supérieur de votre fiche) pour trouver la formation souhaitée. En cas de réponse positive, la composante qui la propose sera indiquée.</strong>
               </li>

               <li style='padding-top:20px;'><strong>La formation n'est pas disponible via l'interface ARIA.</strong>
                  <br><u>Solution</u> :
                  <br>- Si la composante est bien enregistrée dans l'application (mais pas la formation souhaitée), il se peut
                  que la procédure de candidature soit particulière. Si aucune information n'est donnée sur le site Internet de 
                  la composante (ou sur la page d'information qui peut apparaitre lorsque vous la sélectionnez après votre identification),
                  <u>contactez directement la scolarité</u> pour obtenir des renseignements sur cette formation.
                  <br>- Si la composante n'est pas dans la liste proposée, alors celle-ci n'a aucun lien avec l'application ARIA. Vous
                  devez donc consulter son site Internet et/ou sa scolarité afin d'obtenir des détails sur la procédure de dépôt de dossier de 
                  candidature.
               </li>
            </ul>
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_utilisation.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="annulee")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai par erreur 'annulé' une candidature verrouillée, et je ne peux plus ajouter la formation. Comment rétablir ma candidature ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Nous vous conseillons d'utiliser <a href='form_scolarite.php' class='lien_bleu_12' style='vertical-align:top;'><strong>ce formulaire</strong></a>
            pour contacter directement la scolarité et demander le rétablissement de votre candidature.
            <br><br>
            <u>Attention :</u> vous devez déjà être identifié(e) sur l'interface pour pouvoir accéder à cette page.
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_utilisation.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="inscr")
      {
      ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai reçu une lettre (ou un message) confirmant mon admission, mais je ne parviens pas à m'inscrire malgré les instructions reçues, que dois-je faire ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            L'application Aria sur laquelle vous vous trouvez actuellement ne gère que le dépôt de dossiers de candidatures. L'<strong>inscription en
            ligne</strong> est une étape différente, gérée par une autre application.
            <br><br>
            Il est donc conseillé d'utiliser les adresses de contact (téléphone ou courriel) ou les formulaires d'aides de la page sur laquelle
            vous avez tenté de vous inscrire.
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_resultat.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="justificatifs")
      {
         if(isset($_GET["v"]) && $_GET["v"]=="1")
         {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>La date de verrouillage est passée mais je n'ai pas reçu la liste des justificatifs, pourquoi ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <u><strong>Plusieurs cas possibles :</strong></u>
             <ul style='text-align:justify'>
               <li><strong>Il manque des renseignements obligatoires dans le menu "6-Autres renseignements".</strong>
               <br><u>Solution</u> : l'interface vous a normalement envoyé un ou plusieurs messages de rappels.
               <br>Après avoir sélectionné la bonne composante, vérifiez que vous n'avez oublié aucune question (si ce menu 6 n'apparait pas, 
               aucune information supplémentaire n'est demandée). Une fois les informations manquantes complétées, l'interface retentera automatiquement de 
               verrouiller vos voeux le lendemain.</li>

               <li style='padding-top:20px;'><strong>La fiche a bien été verrouillée, mais vous n'avez pas reçu l'accusé de réception indiquant l'envoi des justificatifs.</strong>.
               <br><u>Solution</u> : vérifiez qu'aucun message non lu n'est en attente dans la messagerie de l'interface Aria.</li>

               <li style='padding-top:20px;'><strong>Aucun justificatif n'a été configuré pour cette formation.</strong>
               <br><u>Solution</u> : lorsque cette erreur est rencontrée, la scolarité est automatiquement prévenue et doit théoriquement résoudre
               rapidement ce problème. Vous devez simplement attendre que l'interface retente le verrouillage de votre voeu le lendemain.</li>

               <li style='padding-top:20px;'><strong>L'interface a rencontré une autre erreur logicielle et n'a pas réussi à générer la liste des pièces à fournir.</strong>
               <br><u>Solution</u> : l'administrateur de l'application doit normalement recevoir une notification automatique d'erreur. Une fois le problème
               résolu, votre voeu devrait être verrouillé dès le lendemain.</li>
            </ul>
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_justificatifs.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   
   <?php
         }
         elseif(isset($_GET["a"]) && $_GET["a"]=="1")
         {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai reçu la liste des justificatifs, à qui et comment dois-je envoyer tous ces documents ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:bottom;'>
            Toutes les pièces doivent être envoyées par <strong>voie postale</strong>, l'adresse de la scolarité est normalement
            indiquée sur chaque liste de justificatifs. Vous pouvez également consulter <a href='<?php echo "$__DOC_DIR/composantes.php"; ?>' target='_blank' class='lien_bleu_12'><strong>cette page</strong></a>
            au cas où l'adresse postale serait absente.
            <br><br>
            <font class='Texte_important'><strong><u>Important :</u></strong></font>
             <ul style='text-align:justify'>
               <li>N'envoyez jamais les pièces par courriel (<i>email</i>), sauf si la scolarité l'autorise explicitement. Elle doit alors vous fournir une adresse électronique spécifique. 
               <u>Vérifiez bien que les pièces numérisées sont lisibles</u> avant de les envoyer.
               <li style='padding-top:10px;'>Tous les documents doivent être <u>traduits en français</u> (sauf si la scolarité précise le contraire).</li>
               <li style='padding-top:10px;'>Certaines composantes suivent des procédures spécifiques, lisez bien les consignes données dans la liste des justificatifs, elles sont <strong>prioritaires</strong> sur certaines consignes indiquées par l'interface.</li>
            </ul>
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_justificatifs.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>

   <?php
         }
         elseif(isset($_GET["n"]) && $_GET["n"]=="1")
         {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai demandé plusieurs formations, combien de fois dois-je envoyer mes justificatifs ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Tout dépend de la composante proposant les formations que vous avez choisies. La procédure normale est d'envoyer <strong>un dossier complet</strong> pour 
            <strong>chaque formation</strong> choisie.
            <br><br>
            <font class='Texte_important'><strong><u>Important :</u></strong></font>
            <ul style='text-align:justify'>
               <li><strong>Lisez toujours intégralement les listes de justificatifs</strong> reçues : certaines composantes ne vous demanderont qu'un seul dossier.</li>
               <li>L'adresse postale peut être <strong>différente</strong> entre deux formations de la même composante.</li>
            </ul>
         </font>         
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_justificatifs.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>

   <?php
         }
         elseif(isset($_GET["d"]) && $_GET["d"]=="1")
         {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Je n'ai pas encore les derniers relevés de notes de mon année en cours, que dois-je faire ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Si vous ne possédez pas encore tous les documents, <strong>n'attendez pas pour envoyer ceux en votre possession</strong>, vous risqueriez d'être hors délai.
            <br><br>
            Vous pourrez envoyer le reste des pièces dès leur obtention (par voie postale, ou par courriel <u>si la scolarité vous l'a explicitement demandé</u>).
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_justificatifs.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   <?php
         }
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="navigateur")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>L'interface ne s'affiche pas correctement / je reviens toujours sur la page d'accueil même lorsque je parviens à m'identifier.</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Un navigateur récent est nécessaire pour utiliser l'application ARIA. Voici une liste <u>non exhaustive</u> de navigateurs recommandés :
            <br>
             <ul style='text-align:justify'>
               <li><a href='http://www.mozilla.com/firefox/' class='lien_bleu_12' target='_blank'>Mozilla Firefox</a> (gratuit - tous systèmes d'exploitation)</li>
               <li><a href='http://www.opera.com/' class='lien_bleu_12' target='_blank'>Opera</a> (gratuit - tous systèmes d'exploitation)</li>
               <li><a href='http://www.google.com/chrome' class='lien_bleu_12' target='_blank'>Google Chrome</a> (gratuit)</li>
               <li><a href='http://www.apple.com/fr/safari/' class='lien_bleu_12' target='_blank'>Apple Safari</a> (gratuit - Mac OS et Windows)</li>
               <li><a href='http://www.konqueror.org/' class='lien_bleu_12' target='_blank'>Konqueror</a> (gratuit - Linux)</li>
               <li>Microsoft Internet Explorer (version 7 ou supérieure)</a> (disponible par défaut sous Microsoft Windows XP et supérieur)</li>
            </ul>
            <br>
            Votre navigateur doit également supporter les <i>Cookies</i>, vous pouvez vous référer à la documentation du logiciel
            pour vérifier sa configuration.
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_acces_interface.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="pdf")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai reçu un message contenant des fichiers au format PDF, mais je n'arrive pas à les ouvrir.</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <u><strong>Trois cas possibles :</strong></u>
            <br>- votre navigateur ne sait pas ouvrir ces fichiers,
            <br>- aucun programme n'est disponible sur votre ordinateur pour les lire,
            <br>- le programme est bien installé mais il n'affiche plus le contenu des fichiers.
            <br>
            <br>L'installation de l'un des logiciels suivants devrait vous permettre d'ouvrir ces fichiers (exemples donnés à titre indicatif) :
            <ul style='text-align:justify'>
               <li><a href='http://www.adobe.com/fr/' class='lien_bleu_12' target='_blank'>Adobe Acrobat Reader</a> (gratuit - la plupart des systèmes d'exploitation est supportée)</li>
               <li><a href='http://www.foolabs.com/xpdf/index.html' class='lien_bleu_12' target='_blank'>Xpdf</a> (lecteur libre pour Linux)</li>
            </ul>
            <br>Dans le troisième cas, la <strong>réinstallation</strong> ou la mise à jour du programme existant peuvent résoudre le problème (les paramètres sont souvent réinitialisés).
            <br><br>
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_justificatifs.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="resultats")
      {
      ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Quand et comment obtiendrai-je les résultats de mon admission ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Pour chaque voeu, vous devez attendre que la commission pédagogique examine votre dossier (à condition qu'il ait été jugé <u>recevable</u>).
            <br><br>
            De plus :
            <br>
             <ul style='text-align:justify'>
               <li>Les dates des commissions peuvent être différentes entre les composantes et entre chaque formation, tous les résultats ne seront donc pas affichés au même moment ;</li>
               <li>Certaines composantes attendent d'avoir saisi tous les résultats avant de les publier sur l'interface (publication différée) ;</li>
               <li>Aucun résultat ne sera donné par téléphone ou par courriel, vous devez impérativement attendre la lettre officielle, c'est le seul document faisant foi.</li>
            </ul>
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_resultat.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
   
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="scolarite")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Je souhaite ajouter une formation, mais la session est déjà fermée.</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <u>Vérifiez tout d'abord les <a href='<?php echo "$__DOC_DIR/limites.php"; ?>' target='_blank' class='lien_bleu_12' style='vertical-align:top;'><strong>dates des différentes sessions</strong></a> pour cette formation :</u>
            <ol style='list-style-type:decimal; text-align:justify'>
               <li>
                  Si une nouvelle session de candidatures est programmée, vous devez attendre son ouverture afin de pouvoir sélectionner la formation.
               </li>
               <li style='padding-top:20px;'>
                  Si aucune session n'est prévue, utilisez <a href='form_scolarite.php' class='lien_bleu_12' style='vertical-align:top;'><strong>ce formulaire</strong></a>
                  pour contacter directement la scolarité.
                  <br />Vous devez être <strong>identifié(e) sur l'interface</strong> pour pouvoir accéder à cette page.
               </li>
            </ol>
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_utilisation.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>

   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="messagerie")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai un message en attente, comment accéder à ma messagerie Aria ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <u>Pour accéder à la messagerie :</u>
            <ol style='list-style-type:decimal; text-align:justify'>
               <li>Connectez-vous à l'interface à l'aide de votre identifiant et mot de passe,</li>
               <li style='padding-top:20px;'>Sélectionner une composante dans laquelle vous avez déposé un dossier,</li>
               <li style='padding-top:20px;'>Passez la page d'information (s'il y en a une) afin d'accéder à votre fiche,</li>
               <li style='padding-top:20px;'>L'interface doit vous proposer de lire le message non lu. Si ce n'est pas le cas, cliquez sur le lien "Messagerie" dans le menu supérieur de votre fiche.</li>
            </ol>
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_utilisation.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>

   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="contact_scol")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai une question concernant les modalités d'accès à une formation, à qui dois-je m'adresser ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <ol style='list-style-type:decimal; text-align:justify'>
               <li>
                  Vérifiez tout d'abord les informations sur le site de l'Université, <a href='http://www.unistra.fr/index.php?id=14881' target='_blank' class='lien_bleu_12' style='vertical-align:top;'><strong>sur cette page</strong></a>.
               </li>
               <li style='padding-top:20px;'>
                  Vous pouvez également consulter le site de la composante, en fonction de la formation souhaitée (<a href='<?php echo "$__DOC_DIR/composantes.php"; ?>' target='_blank' class='lien_bleu_12' style='vertical-align:top;'><strong>liste des composantes</strong></a>).
               </li>
               <li style='padding-top:20px;'>
                  Si vous n'avez pas trouvé l'information que vous cherchiez, utilisez <a href='form_scolarite.php' class='lien_bleu_12' style='vertical-align:top;'><strong>ce formulaire</strong></a>
                  pour contacter directement la scolarité.
                  <br />Vous devez être <strong>identifié(e) sur l'interface</strong> pour pouvoir accéder à cette page.
               </li>
            </ol>
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_utilisation.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>

   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="contact_scol2")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Je suis un(e) candidat(e) étranger(e), on me demande d'envoyer des justificatifs ou des pièces qui n'existent pas dans mon pays. Que faire ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:top;'>
            Nous vous conseillons d'utiliser <a href='form_scolarite.php' class='lien_bleu_12' style='vertical-align:top;'><strong>ce formulaire</strong></a>
            pour contacter directement la scolarité. <u>Attention :</u> vous devez être identifié(e) sur l'interface pour pouvoir accéder à cette page.
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_justificatifs.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>

   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="contact_admin")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Mon problème ne se trouve pas dans ce tableau, à qui dois-je m'adresser ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:top;'>
            Nous vous conseillons d'envoyer un courriel <a href='mailto:<?php echo $GLOBALS["__EMAIL_SUPPORT"]; ?>' class='lien_bleu_12' style='vertical-align:top;'><strong>à cette adresse</strong></a>
            pour une aide informatique. 
            <br /><br />
            <u><strong>Attention :</strong></u> précisez bien vos <strong>nom</strong>, <strong>prénom</strong> et <strong>date de naissance</strong> afin que nous puissions vous identifier 
            sur l'interface Aria.
         </font>
      </td>
   </tr>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index_autre.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>

   <?php
      }
   ?>
   
</div>
<?php
   pied_de_page_simple();
?>
</body></html>
