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
  session_name("preinsc");
  session_start();

  include "../configuration/aria_config.php";
  include "$__INCLUDE_DIR_ABS/vars.php";
  include "$__INCLUDE_DIR_ABS/fonctions.php";
  include "$__INCLUDE_DIR_ABS/db.php";

  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"])) // validation du formulaire
  {
    // vérification des valeurs entrées dans le formulaire
    // TODO : vérifications poussées ?

    $nom=mb_strtoupper(trim($_POST["nom"]));

    $jour=trim($_POST["jour"]);
    $mois=trim($_POST["mois"]);
    $annee=trim($_POST["annee"]);

    $email=mb_strtolower(trim($_POST["email"]));

    $champs_obligatoires=array($nom,$jour,$mois,$annee,$email);
    $cnt_obl=count($champs_obligatoires);

    for($i=0; $i<$cnt_obl; $i++) // vérification des champs obligatoires
    {
      if($champs_obligatoires[$i]=="")
      {
        $champ_vide=1;
        $i=$cnt_obl;
      }
    }

    if(!ctype_digit($jour) || !ctype_digit($mois) || !ctype_digit($annee) || $jour<1 || $jour>31 || $mois<1 || $mois>12 || $annee<1900 | $annee>3000)
      $bad_date=1;
    else
      $date_naissance=MakeTime(12,0,0,$mois,$jour,$annee);

    // $date_naissance=MakeTime(12,0,0,$mois,$jour,$annee);

    if(!isset($champ_vide) && !isset($bad_date))
    {
      // Vérification de présence dans la base

      $dbr=db_connect();
      $result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_prenom, $_DBC_candidat_identifiant,
                          $_DBC_candidat_code_acces
                        FROM $_DB_candidat
                      WHERE $_DBC_candidat_nom ILIKE '$nom'
                      AND $_DBC_candidat_date_naissance='$date_naissance'
                      AND $_DBC_candidat_email ILIKE '$email'");
      $rows=db_num_rows($result);

      if(!$rows)
        $not_found=1;
      else // si le résultat est positif, on ne devrait en n'avoir qu'un seul
      {
        list($cand_id,$cand_civilite,$cand_prenom,$cand_identifiant,$cand_code)=db_fetch_row($result,0);

        db_free_result($result);

        // génération du Code Personnel
        srand((double)microtime()*1000000);
        $code_conf=mb_strtoupper(md5(rand(0,9999)));
        $new_code=substr($code_conf, 17, 8);
        // on supprime le chiffre 1, le zéro et la lettre O : portent à confusion - on les remplace par d'autres caractères
        $new_code=str_replace("0","A", $new_code);
        $new_code=str_replace("O","H", $new_code);
        $new_code=str_replace("1","P", $new_code);

        db_query($dbr,"UPDATE $_DB_candidat SET $_DBU_candidat_code_acces='$new_code' WHERE $_DBU_candidat_id='$cand_id'");

        // envoi du mail de confirmation
        # $headers = "From: $__EMAIL_NOREPLY" . "\r\n" . "Reply-To: $__EMAIL_NOREPLY";
        $headers = "MIME-Version: 1.0\r\nFrom: $__EMAIL_NOREPLY\r\nReply-To: $__EMAIL_NOREPLY\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";
        
        $corps_message="============================================================\nCeci est un message automatique, merci de ne pas y répondre.\n============================================================\n\n
Bonjour $cand_civilite ". stripslashes($nom) . ",\n\n
Les nouvelles informations vous permettant d'accéder à l'interface de précandidatures sont les suivantes:
- Identifiant : ". stripslashes($cand_identifiant) . "
- Code Personnel : $new_code   (respectez bien les majuscules !)\n
Ne perdez surtout pas votre identifiant car vous devrez le joindre aux éventuels justificatifs de diplômes à envoyer à la scolarité.\n\n
Cordialement,\n\n\n--
$__SIGNATURE_COURRIELS";

        $ret=mail($email,"[Précandidatures] - Nouveaux identifiants", $corps_message, $headers);

        // Debug : envoi d'un courriel à l'administrateur
        if($GLOBALS["__DEBUG"]=="t" && $GLOBALS["__DEBUG_RAPPEL_IDENTIFIANTS"]=="t" && $GLOBALS["__EMAIL_ADMIN"]!="")
          mail($GLOBALS["__EMAIL_ADMIN"], "$GLOBALS[__DEBUG_SUJET] - Nouveaux identifiants - $cand_civilite $nom $cand_prenom", "ID : $cand_id\nCandidat : $cand_civilite $nom $cand_prenom\nAdresse électronique : $email\n\n" . $corps_message, $headers);

        write_evt($dbr, $__EVT_ID_C_RECUP, "Demande de renvoi de nouveaux identifiants", $cand_id, $cand_id);

        db_close($dbr);

        if($ret==TRUE)
        {
          $_SESSION["email"]=$email;

          session_write_close();
          header("Location:validation.php");
          exit();
        }
      }
    }
  }
  
  en_tete_candidat();
  menu_sup_simple();
?>

<div class='main'>
  <?php titre_page_icone("Récupération de vos identifiants", "password_32x32_fond.png", 15, "L"); ?>

  <form name='form1' action="<?php print("$php_self"); ?>" method="POST">
  
  <?php
    $prev_periode=$__PERIODE-1 . "-$__PERIODE";
    
    $message = "";

    if(isset($champ_vide))
      $message = "<li>tous les champs sont <strong>obligatoires</strong></li>";

    if(isset($bad_date)) {
      $style = $message!="" ? "style='padding-top:15px;'" : "";
      $message .= "\n<li $style>le format de votre date de naissance est incorrect (JJ / MM / AAAA)</li>";
    }
    
    if($message!="")
      message("<ul>$message</ul>", $__ERREUR);

    if(isset($not_found))
      message("<b>Erreur : ces données ne se trouvent pas dans notre base.</b>
            <br>
            <ul>
              <li>Vérifiez que les données que vous avez entrées sont <strong>rigoureusement identiques</strong> à celles entrées lors de votre enregistrement</li>
              <li style='padding-top:15px;'>Si vous n'avez pas effectué la procédure d'enregistrement, merci de compléter <a href='enregistrement.php' class='lien2a'>ce formulaire</a>.</li>
              <li style='padding-top:15px;'>Si vous avez changé d'adresse email, ou en cas d'erreur de saisie, merci  de compléter <a href='$GLOBALS[__CAND_DIR]/assistance/form_adresse.php' class='lien2a'>celui-ci</a>.</li>
              <li style='padding-top:15px;'>Si tout le reste a échoué, merci <a href='mailto:$__EMAIL_SUPPORT?subject=Identifiants Aria' class='lien2a'>d'envoyer un courriel à cette adresse</a>.
            avec toutes les données du formulaire.</li>", $__ERREUR);

    if(!isset($not_found) && !isset($bad_date) && !isset($champ_vide))
      message("Merci de compléter le formulaire suivant.
            <font class='Textebleu'>
            <ul>
              <li style='padding-top:15px;'>Les données à entrer sont </font><font class='Texte_important_14'><b>celles que vous avez entrées lors de votre premier enregistrement</b></font></li>
              <li style='padding-top:15px;'>Si vous étiez déjà enregistré en $prev_periode et que vous avez changé d'adresse électronique, retournez à l'écran précédent et utilisez le lien \"Signaler un problème technique\" en précisant votre identité complète.</li>
              <li style='padding-top:15px;'>Vous recevrez une nouvelle fois, par courriel, les codes d'accès qui vous permettront d'accéder aux précandidatures en ligne</li>
            </ul>
            </font>", $__INFO);
  ?>

  <table style="margin-left:auto; margin-right:auto;">
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'><b>Nom : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='nom' value='<?php if(isset($nom)) echo htmlspecialchars($nom,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size="25" maxlength="30">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'><b>Date de naissance (JJ/MM/AAAA) : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='jour' value='<?php if(isset($jour)) echo htmlspecialchars($jour,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size="2" maxlength="2">/
      <input type='text' name='mois' value='<?php if(isset($mois)) echo htmlspecialchars($mois,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size="2" maxlength="2">/
      <input type='text' name='annee' value='<?php if(isset($annee)) echo htmlspecialchars($annee,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size="4" maxlength="4">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' style="text-align:right;">
      <font class='Texte_menu2'><b>Adresse électronique (<i>e-mail</i>) : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='email' value='<?php if(isset($email)) echo htmlspecialchars($email,ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size="25" maxlength="255">
    </td>
  </tr>
  </table>
  
  <div class='centered_icons_box'>
    <a href='identification.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go_valider" value="Valider">
    </form>
  </div>
</div>

<?php
  pied_de_page_candidat();
?>

<script language="javascript">
  document.form1.nom.focus()
</script>

</body></html>

