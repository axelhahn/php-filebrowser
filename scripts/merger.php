<?php
/*

    ░█▀█░█░█░█▀▀░█░░░█▀▀░░░█▀▀░▀█▀░█░░░█▀▀░█▀▄░█▀▄░█▀█░█░█░█▀▀░█▀▀░█▀▄
    ░█▀█░▄▀▄░█▀▀░█░░░▀▀█░░░█▀▀░░█░░█░░░█▀▀░█▀▄░█▀▄░█░█░█▄█░▀▀█░█▀▀░█▀▄
    ░▀░▀░▀░▀░▀▀▀░▀▀▀░▀▀▀░░░▀░░░▀▀▀░▀▀▀░▀▀▀░▀▀░░▀░▀░▀▀▀░▀░▀░▀▀▀░▀▀▀░▀░▀
    
    MERGE SCRIPT TO CREATE DIST FILE

*/


chdir(dirname(__DIR__));
$sOutfile="dist/f.php";
$sOutfile="f.php";


$sFileHeader="
// ----------------------------------------------------------------------
// AXELS FILESEARCH * MERGED VERSION - ".date("Y-m-d H:i:s")."
// see uncompressed version on Github
// ----------------------------------------------------------------------
";

// ----------------------------------------------------------------------
// FUNCTIONS
// ----------------------------------------------------------------------

/**
 * get content of a php file, cut "<?php" and insert markers
 * @param string $sFile  filename
 * @return string
 */
function getFile($sFile){
    echo "Reading '$sFile' ...\n";
    if(!$s=file_get_contents($sFile)){
        die("ERROR in ".__FUNCTION__ . "(): File '$sFile' cannot be read.\n");
    }
    $s=str_replace("<?php","",$s);

    return "
// ---------==========##########|  START '$sFile'\n$s\n
// ---------==========##########|  END '$sFile'\n";
}


/**
 * minify php string
 * SOURCE: http://php.net/manual/en/function.php-strip-whitespace.php
 * with removed lowercase function
 * copied from https://github.com/axelhahn/ahwebinstall/blob/master/classes/ahwi-generator.class.php#L81
 * 
 * @staticvar array  $IW   integer constants for whitespaces surrounding chars
 * @param     string $src  php code or filename of a php file
 * @return    string
 */
function _compress_php_src(string $src): string
{
    // if (!$this->_bCompressInstaller) {
    //     return $src;
    // }

    // Whitespaces left and right from this signs can be ignored
    static $IW = [
    T_CONCAT_EQUAL, // .=
    T_DOUBLE_ARROW, // =>
    T_BOOLEAN_AND, // &&
    T_BOOLEAN_OR, // ||
    T_IS_EQUAL, // ==
    T_IS_NOT_EQUAL, // != or <>
    T_IS_SMALLER_OR_EQUAL, // <=
    T_IS_GREATER_OR_EQUAL, // >=
    T_INC, // ++
    T_DEC, // --
    T_PLUS_EQUAL, // +=
    T_MINUS_EQUAL, // -=
    T_MUL_EQUAL, // *=
    T_DIV_EQUAL, // /=
    T_IS_IDENTICAL, // ===
    T_IS_NOT_IDENTICAL, // !==
    T_DOUBLE_COLON, // ::
    T_PAAMAYIM_NEKUDOTAYIM, // ::
    T_OBJECT_OPERATOR, // ->
    T_DOLLAR_OPEN_CURLY_BRACES, // ${
    T_AND_EQUAL, // &=
    T_MOD_EQUAL, // %=
    T_XOR_EQUAL, // ^=
    T_OR_EQUAL, // |=
    T_SL, // <<
    T_SR, // >>
    T_SL_EQUAL, // <<=
    T_SR_EQUAL, // >>=
    ];
    if (is_file($src)) {
        if (!$src = file_get_contents($src)) {
            return '';
        }
    }
    $tokens = token_get_all($src);

    $new = "";
    $c = sizeof($tokens);
    $iw = false; // ignore whitespace
    $ih = false; // in HEREDOC
    $ls = "";    // last sign
    $ot = null;  // open tag
    for ($i = 0; $i < $c; $i++) {
        $token = $tokens[$i];
        if (is_array($token)) {
            list($tn, $ts) = $token; // tokens: number, string, line
            $tname = token_name($tn);
            if ($tn == T_INLINE_HTML) {
                $new .= $ts;
                $iw = false;
            } else {
                if ($tn == T_OPEN_TAG) {
                    if (strpos($ts, " ") || strpos($ts, "\n") || strpos($ts, "\t") || strpos($ts, "\r")) {
                        $ts = rtrim($ts);
                    }
                    $ts .= " ";
                    $new .= $ts;
                    $ot = T_OPEN_TAG;
                    $iw = true;
                } elseif ($tn == T_OPEN_TAG_WITH_ECHO) {
                    $new .= $ts;
                    $ot = T_OPEN_TAG_WITH_ECHO;
                    $iw = true;
                } elseif ($tn == T_CLOSE_TAG) {
                    if ($ot == T_OPEN_TAG_WITH_ECHO) {
                        $new = rtrim($new, "; ");
                    } else {
                        $ts = " " . $ts;
                    }
                    $new .= $ts;
                    $ot = null;
                    $iw = false;
                } elseif (in_array($tn, $IW)) {
                    $new .= $ts;
                    $iw = true;
                } elseif ($tn == T_CONSTANT_ENCAPSED_STRING || $tn == T_ENCAPSED_AND_WHITESPACE) {
                    if ($ts[0] == '"') {
                        $ts = addcslashes($ts, "\n\t\r");
                    }
                    $new .= $ts;
                    $iw = true;
                } elseif ($tn == T_WHITESPACE) {
                    $nt = @$tokens[$i + 1];
                    if (!$iw && (!is_string($nt) || $nt == '$') && !in_array($nt[0], $IW)) {
                        $new .= " ";
                    }
                    $iw = false;
                } elseif ($tn == T_START_HEREDOC) {
                    $new .= "<<<S\n";
                    $iw = false;
                    $ih = true; // in HEREDOC
                } elseif ($tn == T_END_HEREDOC) {
                    $new .= "S;";
                    $iw = true;
                    $ih = false; // in HEREDOC
                    for ($j = $i + 1; $j < $c; $j++) {
                        if (is_string($tokens[$j]) && $tokens[$j] == ";") {
                            $i = $j;
                            break;
                        } else if ($tokens[$j][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                } elseif ($tn == T_COMMENT || $tn == T_DOC_COMMENT) {
                    $iw = true;
                } else {
                    /*
                        * Axel: DISABLE lowercase - it has bad impact on constants
                        * 
                        if (!$ih) {
                        $ts = strtolower($ts);
                        }
                        * 
                        */
                    $new .= $ts;
                    $iw = false;
                }
            }
            $ls = "";
        } else {
            if (($token != ";" && $token != ":") || $ls != $token) {
                $new .= $token;
                $ls = $token;
            }
            $iw = true;
        }
    }
    // $new=preg_replace("/\n[ \t]*/", "\n", $new);
    return $new;
}


// ----------------------------------------------------------------------
// MAIN
// ----------------------------------------------------------------------

echo "Reading 'index.php' ...\n";
$sContent=file_get_contents("index.php");

echo "Replacing content of 'init_db.php' ...\n";
$sContent=preg_replace("/require_once.*init_db.php';/", getFile("init_db.php"), $sContent);
$sContent=preg_replace("/require_once.*pdo-db.class.php';/", getFile("vendor/php-abstract-dbo/src/pdo-db.class.php"), $sContent);

$sContent=preg_replace("/require_once 'pdo-db-base.constants.php';/", getFile("vendor/php-abstract-dbo/src/pdo-db-base.constants.php"), $sContent);


$sClass=getFile("classes/filesearch.class.php");

    $sPDO=getFile("vendor/php-abstract-dbo/src/pdo-db-base.class.php");
    $aReplace=[
        "#require_once.*pdo-db-attachments.class.php';#" => "",
        "#require_once.*pdo-db-relations.class.php';#" => getFile("vendor/php-abstract-dbo/src/pdo-db-relations.class.php"),
        "#namespace axelhahn;#" => "// REMOVED by merger.php: namespace axelhahn; ",

        // fix vendor/php-abstract-dbo/src/pdo-db-base.class.php
        // return basename(str_replace('\\', '/', $s));
        "#return basename\(str_replace\(.*\)\);#" => "return basename(str_replace('\\\\\\\\\', '/', \$s));",
        "#return __NAMESPACE__ . .*#" => "return __NAMESPACE__ . '\\\\\\\\\' . \$s;",

    ];
    // $sPDO=preg_replace("#require_once.*pdo-db-attachments.class.php';#", "", $sPDO);
    // $sPDO=preg_replace("#require_once.*pdo-db-relations.class.php';#", getFile("vendor/php-abstract-dbo/src/pdo-db-relations.class.php"), $sPDO);
    // $sPDO=preg_replace("#namespace axelhahn;#", "// REMOVED by merger.php: namespace axelhahn; ", $sPDO);

    // $sPDO=preg_replace("#return basename(str_replace('\', '/', \$s));#", "", $sPDO);

    $sPDO=preg_replace(array_keys($aReplace), array_values($aReplace), $sPDO);


$aReplace2=[
    "#require_once __DIR__ . '/classes/filesearch.class.php';#" => "$sPDO \n $sClass",
    "#namespace axelhahn;#"                                     => "// REMOVED in merger: namespace axelhahn;",
    "#use Exception;#"                                          => "// REMOVED in merger: use Exception;",
    "#new axelhahn\\\\#"                                        => "new ", // remove namespace when initializing a class

    "#require_once __DIR__.'/../vendor/php-abstract-dbo/src/pdo-db-base.class.php';#" => "",
    "#require_once 'obj_fileindex.class.php';#" => getFile("classes/obj_fileindex.class.php"),
    "#class obj_fileindex extends axelhahn.pdo_db_base#" => "class obj_fileindex extends pdo_db_base",
];


$sContent=preg_replace(array_keys($aReplace2), array_values($aReplace2), $sContent);


echo "Merged data: ".strlen($sContent) ." byte ... compressing ...\n";
$sContent=_compress_php_src($sContent);
echo "Compressed: ".strlen($sContent) ." byte ... adding header ...\n";
$sContent=preg_replace("#<\?php#",  "<?php$sFileHeader", $sContent);

echo "Writing '$sOutfile' (".strlen($sContent) ." byte)... ";
if (!file_put_contents($sOutfile,$sContent)){
    die("ERROR: cannot write '$sOutfile'!\n");
};
echo "OK.";

// ----------------------------------------------------------------------
