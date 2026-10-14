<?php
/* ============================================================================
 *  mis_cotizaciones.php - COTIZACIONES DEL CLIENTE
 *  Lista las cotizaciones que el cliente ha hecho, con su vehiculo, los
 *  servicios, el total y su estado (pendiente o revisada por el taller).
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion, sesion y funciones de ayuda.
$u = exigirRol('cliente');                                 // Portero: solo un cliente logueado entra aqui.

$cotizaciones = $taller->getCotizacionesDeUsuario($u->getId());  // Lista de objetos Cotizacion de este cliente, la mas nueva primero.

encabezado('miscotiz');                                    // Dibuja la barra resaltando "Mis cotizaciones".
?>

<h1 class="titulo-pagina">Mis cotizaciones</h1>

<!-- Si no tiene ninguna, aviso con enlace a Cotizar -->
<?php if (count($cotizaciones) == 0) { ?>
    <div class="aviso">Todav&iacute;a no tienes cotizaciones. Crea una en <a href="cotizar.php">Cotizar</a>.</div>
<?php } else { ?>   <!-- Si SI hay cotizaciones, empieza el bloque que las dibuja -->
    <!-- Una tarjeta (section) por cada cotizacion -->
    <?php foreach ($cotizaciones as $c) { ?>
        <?php $v = $c->getVehiculo(); ?>                   <!-- Saca el objeto vehiculo de la cotizacion para usarlo abajo -->
        <section class="panel">
            <div class="cotiz-cabeza">
                <h2>Cotizaci&oacute;n #<?php echo $c->getId(); ?></h2>   <!-- Numero de la cotizacion -->
                <!-- La clase CSS cambia segun el estado: estado-pendiente o estado-revisada, para pintarla de distinto color -->
                <span class="estado estado-<?php echo $c->getEstado(); ?>"><?php echo $c->getEstado(); ?></span>
            </div>
            <p class="nota">
                <!-- Marca y modelo del vehiculo, luego su tipo (polimorfismo: getTipo da Auto, Camioneta, etc.) y la fecha -->
                <?php echo htmlspecialchars($v->getMarca() . ' ' . $v->getModelo()); ?>
                (<?php echo $v->getTipo(); ?>) &middot; <?php echo htmlspecialchars($c->getFecha()); ?>   <!-- Tipo del vehiculo (polimorfico) y fecha -->
            </p>
            <table class="tabla">
                <tbody>
                <!-- Una fila por cada servicio incluido en la cotizacion -->
                <?php foreach ($c->getServicios() as $s) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s->getDescripcion()); ?></td>   <!-- Nombre del servicio -->
                        <!-- Etiqueta con el tipo; strtolower lo pasa a minusculas para armar la clase CSS etiqueta-preventivo / etiqueta-correctivo -->
                        <td><span class="etiqueta etiqueta-<?php echo strtolower($s->getTipo()); ?>"><?php echo $s->getTipo(); ?></span></td>
                    </tr>
                <?php } ?>   <!-- Cierra el foreach de servicios -->
                    <!-- Ultima fila: el total guardado en la BD, con 2 decimales -->
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="costo-final"><strong>$<?php echo number_format($c->getTotal(), 2); ?></strong></td>   <!-- Total guardado en la BD con 2 decimales -->
                    </tr>
                </tbody>
            </table>
        </section>
    <?php } ?>   <!-- Cierra el foreach de cotizaciones -->
<?php } ?>   <!-- Cierra el else de 'si hay cotizaciones' -->

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
