<?php
/* ============================================================================
 *  cliente.php - INICIO DEL CLIENTE
 *  Es lo primero que ve el cliente al entrar. Le da la bienvenida y los
 *  accesos rapidos a sus autos y a cotizar.
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion, sesion y funciones de ayuda.
$u = exigirRol('cliente');   // solo entra un cliente con sesion   // El "portero": si no hay sesion manda al login; si es admin lo manda a su panel. Devuelve el Usuario.

$autos       = $taller->getVehiculosDeUsuario($u->getId());        // Trae la lista de vehiculos de este cliente (objetos Auto, Camioneta, etc.).
$cotizaciones = $taller->getCotizacionesDeUsuario($u->getId());    // Trae la lista de cotizaciones de este cliente (objetos Cotizacion).

encabezado('cliente');                                     // Dibuja la barra de arriba resaltando "Inicio" del menu de cliente.
?>

<!-- Saludo con el nombre del usuario. htmlspecialchars evita que un nombre con simbolos rompa el HTML -->
<h1 class="titulo-pagina">Hola, <?php echo htmlspecialchars($u->getNombre()); ?></h1>

<!-- Tres tarjetas con numeros resumen; cada una es un enlace a su pagina -->
<section class="tarjetas">
    <a class="tarjeta" href="mis_autos.php">
        <!-- count() cuenta cuantos elementos tiene la lista de autos -->
        <span class="tarjeta-numero"><?php echo count($autos); ?></span>
        <span class="tarjeta-titulo">Mis autos</span>
    </a>
    <a class="tarjeta" href="mis_cotizaciones.php">
        <!-- Cuantas cotizaciones ha hecho -->
        <span class="tarjeta-numero"><?php echo count($cotizaciones); ?></span>
        <span class="tarjeta-titulo">Mis cotizaciones</span>
    </a>
    <a class="tarjeta" href="cotizar.php">
        <!-- Esta tarjeta no muestra numero, solo un "+" para crear una nueva -->
        <span class="tarjeta-numero">+</span>
        <span class="tarjeta-titulo">Nueva cotizaci&oacute;n</span>
    </a>
</section>

<!-- Texto de ayuda fijo que explica las tres opciones -->
<section class="panel">
    <h2>&iquest;Qu&eacute; puedes hacer?</h2>
    <ul class="lista-ayuda">
        <li><strong>Mis autos:</strong> registra los veh&iacute;culos que quieres traer al taller.</li>
        <li><strong>Cotizar:</strong> elige un auto y los servicios, y obt&eacute;n el costo estimado.</li>
        <li><strong>Mis cotizaciones:</strong> revisa lo que has solicitado y su estado.</li>
    </ul>
</section>

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
