<?php
/* ============================================================================
 *  cotizaciones.php - COTIZACIONES (vista del administrador)
 *  Muestra todas las solicitudes de los clientes. El admin puede marcar una
 *  como revisada cuando ya la atendio.
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion, sesion y funciones de ayuda.
$u = exigirRol('admin');                                   // Portero: solo el admin entra aqui.

$mensaje = '';                                             // Mensaje de confirmacion, vacio al inicio.

// Marcar una cotizacion como revisada (llega por ?revisar=id).
if (isset($_GET['revisar'])) {                             // Si en la direccion viene ?revisar=5 es que dieron clic en "Marcar como revisada".
    $taller->marcarRevisada((int) $_GET['revisar']);       // Convierte el id a entero y le pide a Taller que cambie el estado en la BD.
    $mensaje = 'Cotización marcada como revisada.';        // Prepara el mensaje de confirmacion.
}

$cotizaciones = $taller->getTodasCotizaciones();           // Trae TODAS las cotizaciones. Se hace despues del UPDATE para que ya salga el estado nuevo.

encabezado('cotiz');                                       // Dibuja la barra resaltando "Cotizaciones".
?>

<h1 class="titulo-pagina">Cotizaciones</h1>

<!-- Aviso verde si se acaba de marcar una como revisada -->
<?php if ($mensaje != '') { ?>
    <div class="aviso"><?php echo htmlspecialchars($mensaje); ?></div>   <!-- Muestra el texto del mensaje; htmlspecialchars evita que rompa el HTML -->
<?php } ?>   <!-- Cierra el if del mensaje -->

<!-- Si no hay ninguna, texto de vacio -->
<?php if (count($cotizaciones) == 0) { ?>
    <p class="vacio">Todav&iacute;a no hay cotizaciones.</p>
<?php } else { ?>   <!-- Si SI hay cotizaciones, empieza el bloque que las dibuja -->
    <!-- Una tarjeta por cotizacion. $fila es el par ['cot' => objeto, 'cliente' => nombre] -->
    <?php foreach ($cotizaciones as $fila) { ?>
        <?php $c = $fila['cot']; $v = $c->getVehiculo(); ?>   <!-- $c es el objeto Cotizacion y $v su objeto vehiculo -->
        <section class="panel">
            <div class="cotiz-cabeza">
                <!-- Numero de cotizacion y nombre del cliente que la pidio -->
                <h2>#<?php echo $c->getId(); ?> &middot; <?php echo htmlspecialchars($fila['cliente']); ?></h2>
                <!-- Etiqueta de estado con color segun sea pendiente o revisada -->
                <span class="estado estado-<?php echo $c->getEstado(); ?>"><?php echo $c->getEstado(); ?></span>
            </div>
            <p class="nota">
                <!-- Vehiculo, su tipo (getTipo polimorfico) y la fecha -->
                <?php echo htmlspecialchars($v->getMarca() . ' ' . $v->getModelo()); ?>
                (<?php echo $v->getTipo(); ?>) &middot; <?php echo htmlspecialchars($c->getFecha()); ?>   <!-- Tipo del vehiculo (polimorfico) y fecha de la cotizacion -->
            </p>
            <table class="tabla">
                <tbody>
                <!-- Una fila por servicio incluido -->
                <?php foreach ($c->getServicios() as $s) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s->getDescripcion()); ?></td>   <!-- Nombre del servicio -->
                        <td><span class="etiqueta etiqueta-<?php echo strtolower($s->getTipo()); ?>"><?php echo $s->getTipo(); ?></span></td>  <!-- Tipo con color -->
                    </tr>
                <?php } ?>   <!-- Cierra el foreach de servicios -->
                    <!-- Fila del total con 2 decimales -->
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="costo-final"><strong>$<?php echo number_format($c->getTotal(), 2); ?></strong></td>   <!-- Total guardado en la BD con 2 decimales -->
                    </tr>
                </tbody>
            </table>
            <!-- El boton solo aparece si todavia esta pendiente -->
            <?php if ($c->getEstado() == 'pendiente') { ?>
                <div class="fila-botones">
                    <!-- El enlace manda a esta misma pagina con ?revisar=ID, que se procesa arriba -->
                    <a href="cotizaciones.php?revisar=<?php echo $c->getId(); ?>" class="boton">Marcar como revisada</a>
                </div>
            <?php } ?>   <!-- Cierra el if de pendiente -->
        </section>
    <?php } ?>   <!-- Cierra el foreach de cotizaciones -->
<?php } ?>   <!-- Cierra el else de 'si hay cotizaciones' -->

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
