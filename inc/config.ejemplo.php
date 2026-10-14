<?php
/* ============================================================================
 *  inc/config.ejemplo.php - PLANTILLA DE CONFIGURACION
 *  Copia este archivo como inc/config.php y escribe los datos reales de tu
 *  MySQL. El sistema lee config.php, no este.
 * ==========================================================================*/
return [                                                   // Devuelve un arreglo; inc/taller.php lo recibe con require.
    'servidor' => 'localhost',                             // Donde esta MySQL.
    'base'     => 'taller_icno',                           // Nombre de la base de datos.
    'usuario'  => 'taller',                                // Usuario de MySQL con permisos sobre esa base.
    'clave'    => 'CAMBIA_ESTA_CONTRASENA',                // Contrasena de ese usuario.
];
