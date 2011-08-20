<?php

require_once( 'framework.inc.php' );




?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it" dir="ltr">
<head>
<title>Database and Information System Group</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="MSSmartTagsPreventParsing" content="TRUE" />
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-2553857-10");
pageTracker._initData();
pageTracker._trackPageview();
</script>
<link href="css/oldsite/stile.css" rel="stylesheet" type="text/css" />
<!--[if IE]>
<link href="css/oldsite/stileIE.css" rel="stylesheet" type="text/css" />
<![endif]-->
<?php
// <link href="css/oldsite/stileENG.css" rel="stylesheet" type="text/css" />
?>
<link rel="shortcut icon" href="http://www.kweepy.it/unibs.ico" />


<link href="css/styles.css" rel="stylesheet" type="text/css" />

<!-- jQuery -->
<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>

<?php if ( ! empty( $_SESSION['loggato'] ) ): ?>
<!-- jQuery UI -->
<link href="css/jquery-ui-south-street/jquery-ui-1.8.15.custom.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery-ui-1.8.15.custom.min.js"></script>

<!-- Loading -->
<link href="css/jquery.loadmask.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery.loadmask.js"></script>

<!-- Validazione form -->
<script type="text/javascript" src="js/validate/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validate/additional-methods.min.js"></script>
<script type="text/javascript" src="js/validate/messages_it.js"></script>

<?php endif; ?>

<script type="text/javascript" src="js/onfocus.js"></script>
<script type="text/javascript" src="js/button.js"></script>
</head>

<body <?php 
	$page = basename( $_SERVER['PHP_SELF'] );
	preg_match( "/^(.+)\.php$/", $page, $match );
	echo "class='" . htmlspecialchars( $match[1] ) . "' ";
?>id="body">
<div id="container">

<div id="intestazione">
	&nbsp;
</div>

<div id="menu">
<ul class="menuNavigazione">
	<li><a id="home" href="home.php">Home</a></li>
	<li><a id="team" href="team.php" class="continua">Team</a>
		<ul>
			<li><a href="vda.php">Valeria De Antonellis</a></li>
			<li><a href="bianchin.php">Devis Bianchini</a></li>
			<li><a href="melchior.php">Michele Melchiori</a></li>
			<li><a href="salvi.php">Denise Salvi</a></li>
		</ul>
	</li>
	<li><a id="progetti" href="progetti.php">Research Projects</a></li>
	<li><a id="pubblicazioni" href="pubblicazioni.php">Publications</a></li>
	<li id="long"><a id="corso" href="corso.php" class="continua">Teaching (ita)</a>
		<ul>
<?php
	$query = <<<EOF
SELECT `id_corso`, c.`nome` AS nome_corso, f.`nome` AS nome_facolta
FROM `$config[db_prefix]corso` AS c
JOIN `$config[db_prefix]facolta` AS f
	USING (`id_facolta`)
ORDER BY f.`id_facolta`, c.`nome` ASC
EOF;
	$result = mysql_query( $query, $db );
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		echo str_repeat( "\t", 3 ) . "<li>";
		echo "<a href='corso.php?id=$riga[id_corso]'>";
		echo htmlspecialchars( $riga['nome_corso'] ) . ' - ' . htmlspecialchars( $riga['nome_facolta'] );
		echo "</a>";
		echo "</li>\n";
	}
?>
		</ul>
	</li>
	<li><a id="tesi" href="tesi.php">Thesis (ita)</a></li>
</ul>
</div><!-- #menu -->

<div id="flags">
	<a id="linguaITA" href="/~deantone/corso.php?ita" title="Vai al sito italiano!"></a>
	<a id="linguaENG" href="/~deantone/corso.php?eng" title="Go to English site!"></a>
</div>

<div id="centrale">
	<div id="corpo">
