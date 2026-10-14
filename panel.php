<?php
/* ============================================================================
 *  panel.php - INICIO DEL ADMINISTRADOR
 *  Resumen del taller: cuantos servicios, vehiculos y cotizaciones hay, y
 *  cuantas cotizaciones estan pendientes de revisar.
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion, sesion y funciones de ayuda.
$u = exigirRol('admin');   // solo el administrador entra aqui   // Portero: si no hay sesion va al login; si es cliente lo manda a cliente.php.

$servicios    = $taller->getServicios();                   // Lista de todos los servicios del catalogo (objetos).
$vehiculos    = $taller->getTodosVehiculos();              // Lista de todos los vehiculos de todos los clientes (filas con dueno).
$cotizaciones = $taller->getTodasCotizaciones();           // Lista de todas las cotizaciones (pares: objeto + nombre del cliente).

// Contar cuantas cotizaciones siguen pendientes.
$pendientes = 0;                                           // Contador que empieza en cero.
foreach ($cotizaciones as $c) {                            // Recorre cada par de la lista.
    if ($c['cot']->getEstado() == 'pendiente') { $pendientes++; }  // $c['cot'] es el objeto Cotizacion. Si su estado es pendiente, suma uno.
}

encabezado('panel');                                       // Dibuja la barra resaltando "Panel".
?>

<h1 class="titulo-pagina">Panel del taller</h1>

<!-- Tres tarjetas resumen que tambien funcionan como enlaces -->
<section class="tarjetas">
    <a class="tarjeta" href="servicios.php">
        <!-- Cuantos servicios hay en el catalogo -->
        <span class="tarjeta-numero"><?php echo count($servicios); ?></span>
        <span class="tarjeta-titulo">Servicios en cat&aacute;logo</span>
    </a>
    <a class="tarjeta" href="vehiculos.php">
        <!-- Cuantos vehiculos han registrado los clientes -->
        <span class="tarjeta-numero"><?php echo count($vehiculos); ?></span>
        <span class="tarjeta-titulo">Veh&iacute;culos de clientes</span>
    </a>
    <a class="tarjeta" href="cotizaciones.php">
        <!-- El contador que se calculo arriba con el foreach -->
        <span class="tarjeta-numero"><?php echo $pendientes; ?></span>
        <span class="tarjeta-titulo">Cotizaciones pendientes</span>
    </a>
</section>

<!-- Texto de ayuda fijo para el administrador -->
<section class="panel">
    <h2>Acciones</h2>
    <ul class="lista-ayuda">
        <li><strong>Servicios:</strong> crea, edita y borra los servicios y sus precios.</li>
        <li><strong>Veh&iacute;culos:</strong> consulta los autos que registraron los clientes.</li>
        <li><strong>Cotizaciones:</strong> revisa las solicitudes de los clientes y m&aacute;rcalas como atendidas.</li>
    </ul>
</section>

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
