<?php
$MODULE=array(

// Nom du module
"MOD_NAME"      =>   "Module Apogée",

// Sous répertoire de gestion/admin/modules/
"MOD_DIR"       =>   "apogee",

// Page(s) utilisée(s) pour accéder au module via la page d'administration
// MOD_CONFIG doit TOUJOURS être un tableau (array) contenant lui même un ou plusieurs arrays
// => Rien n'empêche de rediriger vers une page de configuration plus complète du module
// => la gestion du niveau d'accès requis doit être gérée directement dans les pages

"MOD_CONFIG"   =>   array(
                          array("MOD_CONFIG_TITLE" => "Configuration et messages par défaut",
                                "MOD_CONFIG_PAGE"  => "configuration.php",
                                "MOD_NIVEAU_MIN"   => "$GLOBALS[__LVL_RESP]"),
/*                                
                          array("MOD_CONFIG_TITLE" => "Messages spécifiques",
                                "MOD_CONFIG_PAGE"  => "messages_formations.php",
                                "MOD_NIVEAU_MIN"   => "$GLOBALS[__LVL_RESP]"),      
*/
                          array("MOD_CONFIG_TITLE" => "Activation par composante",
                                "MOD_CONFIG_PAGE"  => "activation.php",
                                "MOD_NIVEAU_MIN"   => "$GLOBALS[__LVL_RESP]"),

                          array("MOD_CONFIG_SEP"   => "Pour la composante courante :"),

                          array("MOD_CONFIG_TITLE" => "Centres de gestion",
                                "MOD_CONFIG_PAGE"  => "centres_gestion.php",
                                "MOD_NIVEAU_MIN"   => "$GLOBALS[__LVL_RESP]"),

                          array("MOD_CONFIG_TITLE" => "Codes et versions d'étape",
                                "MOD_CONFIG_PAGE"  => "codes_formations.php",
                                "MOD_NIVEAU_MIN"   => "$GLOBALS[__LVL_SCOL_PLUS]")
                         ),

"MOD_INCLUDE"   => array("include/db.php",
                        "include/fonctions.php")
);

?>