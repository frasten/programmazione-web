<?php

$id = intval( $_GET['id'] );

admin_menu( array(
	array( "?action=edit&id=$id", 'Modifica corso', 'table_edit.png' ),
	array( "?", 'Lista corsi', 'table.png' )
) );




?>
