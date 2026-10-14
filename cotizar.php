<?php
/* ============================================================================
 *  cotizar.php - EL CLIENTE ARMA UNA COTIZACION
 *  Elige uno de sus autos y marca los servicios que quiere. El sistema suma
 *  el costo de cada servicio multiplicado por el factor del tipo de auto
 *  (una camioneta paga mas que una moto) y guarda la cotizacion.
 *  ESTA ES LA PAGINA DONDE MEJOR SE VE EL POLIMORFISMO EN PANTALLA.
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion, sesion y funciones de ayuda.
$u = exigirRol('cliente');                                 // Portero: solo un cliente logueado entra aqui.

$autos     = $taller->getVehiculosDeUsuario($u->getId());  // Lista de objetos vehiculo del cliente, para el menu desplegable.
$servicios = $taller->getServicios();                      // Lista de objetos servicio del catalogo, para las casillas.

$resumen = null;   // aqui se guarda el resultado para mostrarlo tras cotizar   // null = todavia no se ha cotizado nada en esta carga de pagina.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {                // Solo entra aqui si se envio el formulario.
    $idVehiculo   = (int) $_POST['id_vehiculo'];           // Id del auto elegido, convertido a entero.
    $idsServicios = isset($_POST['servicios']) ? $_POST['servicios'] : [];  // Los ids de las casillas marcadas. Si no marco ninguna, $_POST['servicios'] no existe y se usa un arreglo vacio.

    if ($idVehiculo == 0 || count($idsServicios) == 0) {   // Validacion: que haya auto y al menos un servicio.
        $resumen = ['error' => 'Elige un auto y al menos un servicio.'];  // Guarda el error dentro de $resumen para mostrarlo abajo.
    } else {                                               // Si eligio bien...
        $taller->crearCotizacion($u->getId(), $idVehiculo, $idsServicios);  // Taller calcula el total y guarda la cotizacion en la BD.

        // Se vuelve a calcular para mostrar el desglose en pantalla.
        $vehiculo = $taller->buscarVehiculo($idVehiculo);  // Trae el objeto vehiculo (Auto, Camioneta, etc.) para saber su factor.
        $lineas = array();                                 // Lista vacia donde va cada servicio con su subtotal.
        $total  = 0;                                       // Acumulador del total.
        foreach ($idsServicios as $idServ) {               // Recorre cada servicio marcado.
            $s = $taller->buscarServicio($idServ);         // Trae el objeto servicio (Preventivo o Correctivo).
            if ($s != null) {                              // Si existe...
                $sub = $s->calcularCosto() * $vehiculo->getFactor();  // POLIMORFISMO DOBLE: el servicio calcula a su manera y el vehiculo da su factor. La linea no pregunta de que tipo es nada.
                $lineas[] = ['desc' => $s->getDescripcion(), 'sub' => $sub];  // Guarda nombre y subtotal para la tabla.
                $total += $sub;                            // Suma al total.
            }
        }
        $resumen = ['vehiculo' => $vehiculo, 'lineas' => $lineas, 'total' => $total];  // Junta todo en $resumen para pintarlo abajo.
    }
}

encabezado('cotizar');                                     // Dibuja la barra resaltando "Cotizar".
?>

<h1 class="titulo-pagina">Cotizar servicios</h1>

<!-- Aviso rojo si hubo error de validacion -->
<?php if ($resumen != null && isset($resumen['error'])) { ?>
    <div class="aviso aviso-error"><?php echo htmlspecialchars($resumen['error']); ?></div>   <!-- Texto del error de validacion -->
<?php } ?>   <!-- Cierra el if del error -->

<!-- Si hay resumen y NO es error, se acaba de cotizar: se muestra el desglose -->
<?php if ($resumen != null && !isset($resumen['error'])) { ?>
    <!-- Resultado de la cotizacion recien creada -->
    <section class="panel">
        <h2>Tu cotizaci&oacute;n</h2>
        <!-- Marca, modelo, tipo y factor del vehiculo. Aqui el cliente VE el factor que se le aplico -->
        <p class="nota">Veh&iacute;culo: <?php echo htmlspecialchars($resumen['vehiculo']->getMarca() . ' ' . $resumen['vehiculo']->getModelo()); ?>
           (<?php echo $resumen['vehiculo']->getTipo(); ?>, factor <?php echo $resumen['vehiculo']->getFactor(); ?>)</p>   <!-- Tipo del vehiculo y el factor que se le aplico -->
        <table class="tabla">
            <tbody>
            <!-- Una fila por servicio con su subtotal ya multiplicado por el factor -->
            <?php foreach ($resumen['lineas'] as $l) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($l['desc']); ?></td>                  <!-- Nombre del servicio -->
                    <td class="costo-final">$<?php echo number_format($l['sub'], 2); ?></td>  <!-- Subtotal con 2 decimales -->
                </tr>
            <?php } ?>   <!-- Cierra el foreach de lineas -->
                <!-- Fila del total -->
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="costo-final"><strong>$<?php echo number_format($resumen['total'], 2); ?></strong></td>   <!-- Total de la cotizacion con 2 decimales -->
                </tr>
            </tbody>
        </table>
        <p class="nota">Ya la guardamos. La puedes ver en <a href="mis_cotizaciones.php">Mis cotizaciones</a>.</p>
    </section>
<?php } ?>   <!-- Cierra el if del resumen -->

<!-- Antes de mostrar el formulario se revisa que haya con que cotizar -->
<?php if (count($autos) == 0) { ?>
    <!-- Sin autos no se puede cotizar: se manda a registrar uno -->
    <div class="aviso">Primero registra un auto en <a href="mis_autos.php">Mis autos</a>.</div>
<?php } else if (count($servicios) == 0) { ?>   <!-- Si hay autos pero no hay servicios en el catalogo -->
    <!-- Sin servicios en el catalogo tampoco -->
    <div class="aviso">El taller a&uacute;n no tiene servicios en su cat&aacute;logo.</div>
<?php } else { ?>   <!-- Si hay autos y servicios, se muestra el formulario -->
    <!-- Formulario para armar una cotizacion nueva -->
    <section class="panel">
        <h2>Nueva cotizaci&oacute;n</h2>
        <form method="POST">
            <label class="campo-simple">Elige tu auto
                <!-- Menu desplegable con los autos del cliente. El value es el id que llega en $_POST['id_vehiculo'] -->
                <select name="id_vehiculo">
                    <!-- Texto visible de cada opcion: marca, modelo, placas y tipo -->
                    <?php foreach ($autos as $v) { ?>
                        <option value="<?php echo $v->getId(); ?>">   <!-- El value es el id del vehiculo; es lo que llega en el POST -->
                            <?php echo htmlspecialchars($v->getMarca() . ' ' . $v->getModelo() . ' - ' . $v->getPlacas()); ?>   <!-- Texto visible: marca, modelo y placas -->
                            (<?php echo $v->getTipo(); ?>)   <!-- Tipo del vehiculo (polimorfico) -->
                        </option>
                    <?php } ?>   <!-- Cierra el foreach de autos -->
                </select>
            </label>

            <p class="etiqueta-campo">Servicios</p>
            <div class="lista-servicios">
                <!-- Una casilla por servicio del catalogo -->
                <?php foreach ($servicios as $s) { ?>
                    <label class="opcion-servicio">
                        <!-- name="servicios[]" con corchetes hace que PHP reciba TODAS las marcadas como un arreglo en $_POST['servicios'] -->
                        <input type="checkbox" name="servicios[]" value="<?php echo $s->getId(); ?>">
                        <span>
                            <strong><?php echo htmlspecialchars($s->getDescripcion()); ?></strong>   <!-- Nombre del servicio en negritas -->
                            <!-- Tipo y costo del servicio sin factor. calcularCosto() ya aplica el 10% o las horas segun la clase -->
                            <small><?php echo $s->getTipo(); ?> &middot; base $<?php echo number_format($s->calcularCosto(), 2); ?></small>
                        </span>
                    </label>
                <?php } ?>   <!-- Cierra el foreach de servicios -->
            </div>

            <button type="submit" class="boton">Calcular y guardar</button>
        </form>
        <p class="nota">El precio final se ajusta seg&uacute;n el tipo de tu veh&iacute;culo.</p>
    </section>
<?php } ?>   <!-- Cierra el else del formulario -->

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
