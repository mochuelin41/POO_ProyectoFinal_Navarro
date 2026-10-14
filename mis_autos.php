<?php
/* ============================================================================
 *  mis_autos.php - AUTOS DEL CLIENTE
 *  El cliente registra sus vehiculos (con todas sus caracteristicas) y ve la
 *  lista. Segun el tipo elegido se crea un Auto, Camioneta, Camion o Moto.
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion, sesion y funciones de ayuda.
$u = exigirRol('cliente');                                 // Portero: solo un cliente logueado entra aqui. Devuelve su objeto Usuario.

$mensaje = '';                                             // Mensaje para mostrar arriba (exito o error). Vacio al inicio.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {                // Solo entra aqui si se envio el formulario de registrar auto.
    $placas = trim($_POST['placas']);                      // Toma las placas y quita espacios sobrantes.
    $marca  = trim($_POST['marca']);                       // Toma la marca.
    $modelo = trim($_POST['modelo']);                      // Toma el modelo.
    $color  = trim($_POST['color']);                       // Toma el color.
    $anio   = (int) $_POST['anio'];                        // Toma el anio y lo convierte a numero entero.
    $km     = (int) $_POST['kilometraje'];                 // Toma el kilometraje y lo convierte a entero.
    $tipo   = $_POST['tipo'];   // 'auto','camioneta','camion','moto'   // Toma el tipo elegido en el menu desplegable.

    if ($placas == '' || $marca == '' || $modelo == '' || $color == '') {  // Validacion: que los textos no esten vacios.
        $mensaje = 'Faltan datos por llenar.';             // Si falta algo, mensaje de error.
    } else {                                               // Si todo viene lleno...
        // Se crea el objeto de la clase hija correcta segun el tipo.
        $args = [0, $placas, $marca, $modelo, $color, $anio, $km];  // Junta los 7 datos en un arreglo. El id es 0 porque todavia no existe en la BD; MySQL le asigna uno.
        switch ($tipo) {                                   // Segun lo que eligio el usuario...
            case 'camioneta': $vehiculo = new Camioneta(...$args); break;    // ...crea una Camioneta (factor 1.3). Los "..." reparten el arreglo como parametros.
            case 'camion':    $vehiculo = new Camion(...$args); break;       // ...o un Camion (factor 1.6).
            case 'moto':      $vehiculo = new Motocicleta(...$args); break;  // ...o una Motocicleta (factor 0.7).
            default:          $vehiculo = new Auto(...$args);                // ...o un Auto (factor 1.0) si es cualquier otra cosa.
        }
        $taller->agregarVehiculo($u->getId(), $vehiculo, $tipo);  // Le pide a Taller que lo guarde en la BD ligado a este cliente.
        $mensaje = 'Auto registrado correctamente.';       // Mensaje de exito.
    }
}

$autos = $taller->getVehiculosDeUsuario($u->getId());      // Trae la lista actualizada de autos del cliente (ya incluye el recien creado).

encabezado('autos');                                       // Dibuja la barra resaltando "Mis autos".
?>

<h1 class="titulo-pagina">Mis autos</h1>

<!-- Aviso de exito o error, solo si hay mensaje -->
<?php if ($mensaje != '') { ?>
    <div class="aviso"><?php echo htmlspecialchars($mensaje); ?></div>   <!-- Texto del mensaje -->
<?php } ?>   <!-- Cierra el if del mensaje -->

<!-- Formulario para registrar un vehiculo nuevo -->
<section class="panel">
    <h2>Registrar un auto</h2>
    <form method="POST" class="formulario">
        <label>Placas <input type="text" name="placas" required></label>          <!-- llega como $_POST['placas'] -->
        <label>Marca <input type="text" name="marca" required></label>            <!-- llega como $_POST['marca'] -->
        <label>Modelo <input type="text" name="modelo" required></label>          <!-- llega como $_POST['modelo'] -->
        <label>Color <input type="text" name="color" required></label>            <!-- llega como $_POST['color'] -->
        <label>A&ntilde;o <input type="number" name="anio" min="1950" max="2026" value="2020" required></label>  <!-- numero entre 1950 y 2026 -->
        <label>Kilometraje <input type="number" name="kilometraje" min="0" value="0" required></label>          <!-- numero que no puede ser negativo -->
        <label>Tipo de veh&iacute;culo
            <!-- El value de cada option es lo que llega en $_POST['tipo'] y lo que decide la clase hija -->
            <select name="tipo">
                <option value="auto">Auto</option>
                <option value="camioneta">Camioneta</option>
                <option value="camion">Cami&oacute;n</option>
                <option value="moto">Motocicleta</option>
            </select>
        </label>
        <button type="submit" class="boton">Registrar auto</button>
    </form>
</section>

<!-- Tabla con los vehiculos ya registrados -->
<section class="panel">
    <h2>Mis veh&iacute;culos</h2>
    <!-- Si no tiene ninguno, texto de vacio -->
    <?php if (count($autos) == 0) { ?>
        <p class="vacio">Todav&iacute;a no tienes autos registrados.</p>
    <?php } else { ?>   <!-- Si SI hay autos, se dibuja la tabla -->
        <table class="tabla">
            <thead>
                <tr>
                    <th>Tipo</th><th>Placas</th><th>Marca</th><th>Modelo</th>
                    <th>Color</th><th>A&ntilde;o</th><th>Kil&oacute;metros</th>
                </tr>
            </thead>
            <tbody>
            <!-- Una fila por vehiculo. $v es un OBJETO (Auto, Camioneta...), por eso se usan getters -->
            <?php foreach ($autos as $v) { ?>
                <tr>
                    <td><span class="etiqueta"><?php echo $v->getTipo(); ?></span></td>   <!-- POLIMORFISMO: cada clase hija responde con su propio nombre -->
                    <td><?php echo htmlspecialchars($v->getPlacas()); ?></td>             <!-- Placas -->
                    <td><?php echo htmlspecialchars($v->getMarca()); ?></td>              <!-- Marca -->
                    <td><?php echo htmlspecialchars($v->getModelo()); ?></td>             <!-- Modelo -->
                    <td><?php echo htmlspecialchars($v->getColor()); ?></td>              <!-- Color -->
                    <td><?php echo $v->getAnio(); ?></td>                                 <!-- Anio -->
                    <td><?php echo number_format($v->getKilometraje()); ?></td>           <!-- Kilometraje con comas de miles -->
                </tr>
            <?php } ?>   <!-- Cierra el foreach de autos -->
            </tbody>
        </table>
    <?php } ?>   <!-- Cierra el else de la tabla -->
</section>

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
