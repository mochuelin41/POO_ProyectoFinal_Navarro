<?php
/* ============================================================================
 *  logout.php - CERRAR SESION
 *  Borra la sesion y regresa a la pagina de inicio.
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga las clases, la conexion y arranca la sesion (session_start esta ahi).

session_destroy();          // borra los datos de la sesion   // Destruye la sesion: se olvida el usuario_id guardado, o sea, ya nadie esta logueado.
header("Location: index.php");                             // Le dice al navegador que vaya a la pagina de inicio.
exit;                                                      // Detiene el script aqui para que no se ejecute nada mas.
