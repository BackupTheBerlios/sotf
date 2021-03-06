<?php // -*- tab-width: 3; indent-tabs-mode: 1; -*- 

/*  
 * $Id: docGen.php,v 1.3 2003/05/14 15:30:39 andras Exp $
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: Andr�s Micsik, M�t� Pataki, Tam�s D�ri 
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 */

require("config.inc.php");
require($config['classdir'] . '/sotf_Utils.class.php');

// where phpdocgen files are located
$phpdocgendir = 'C:/sotf/helpers/phpdocgen';

// where perl is located
$perl = 'C:/perl/bin/perl.exe';

// where to put documentation
$docdir = realpath($config['basedir'] . "/code/doc/php");

$phpDirFiles = array();
$phpClassFiles = array();
if ($dir = opendir("."))
{
	while (($file = readdir($dir)) !== false)
		if (preg_match("/\.php$/",$file)) {
			$phpDirFiles[] = realpath($file);
    }
	closedir($dir);
}
if ($dir = opendir($config['classdir']))
{
	while (($file = readdir($dir)) !== false)
		if (preg_match("/\.class\.php$/",$file)) {
      $phpClassFiles[] = $config['classdir'] . "/$file";
    }
	closedir($dir);
}

$script = realpath("$phpdocgendir/phpdocgen.pl");
if (is_dir($docdir))
	sotf_Utils::erase($docdir);
?>
<html>
<head>
<title>Documentation generation</title>
</head>
<body>
<p><b>Target dir</b>: <?php echo $docdir ?></p>
<p><b>phpdocgen output</b>:</p>
<pre>
<?php
passthru("$perl $script --output=$docdir --defpack=StreamOnTheFly " . /* implode(" ",$phpDirFiles) .*/ " "  . implode(" ",$phpClassFiles)." 2>&1");
?>
</pre>
</body>
</html>
