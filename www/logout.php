<?php

session_start();
session_destroy();
// Riporto l'utente nella pagina dove si trovava prima.
header( "Location: $_SERVER[HTTP_REFERER]" );
exit;

?>
