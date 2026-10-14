<?php
/* ============================================================================
 *  servicios.php - CRUD COMPLETO DE SERVICIOS
 *  Aqui estan las cuatro operaciones que pide el proyecto:
 *    CREAR     -> el formulario de arriba crea un servicio preventivo o correctivo.
 *    CONSULTAR -> la tabla de abajo lista todos los servicios y su costo final.
 *    ACTUALIZAR-> el boton Editar carga el servicio en un formulario y lo cambia.
 *    ELIMINAR  -> el boton Eliminar lo borra de la lista.
 *  Cada operacion termina en un metodo de Taller que ejecuta SQL.
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion, sesion y funciones de ayuda.
$u = exigirRol('admin');   // el CRUD de servicios solo lo usa el administrador   // Portero: un cliente que escriba esta direccion a mano es redirigido.

$mensaje = '';                                             // Mensaje de confirmacion o error. Vacio al inicio.

// ----- Procesar los formularios (crear o actualizar) -----
if ($_SERVER['REQUEST_METHOD'] == 'POST') {                // Solo entra si se envio alguno de los dos formularios.

    // Un campo oculto llamado "accion" nos dice que formulario se envio.
    $accion = $_POST['accion'];                            // Vale 'crear' o 'actualizar' segun el input hidden del formulario.

    if ($accion == 'crear') {                              // ===== CREATE =====
        $tipo        = $_POST['tipo'];                     // 'preventivo' o 'correctivo', del menu desplegable.
        $descripcion = trim($_POST['descripcion']);        // Texto del servicio, sin espacios sobrantes.
        $costoBase   = (float) $_POST['costoBase'];   // (float) lo convierte a numero   // Precio base como numero decimal.
        $extra       = (float) $_POST['extra'];            // Kilometraje si es preventivo, horas si es correctivo.

        if ($descripcion == '') {                          // Validacion: la descripcion es obligatoria.
            $mensaje = 'La descripcion no puede estar vacia.';  // Error.
        } else {                                           // Si hay descripcion...
            // Segun el tipo elegido se crea una clase hija distinta.
            // Aqui se ve la herencia y el polimorfismo en accion.
            if ($tipo == 'preventivo') {                   // Si el admin eligio preventivo...
                $servicio = new ServicioPreventivo(0, $descripcion, $costoBase, $extra);  // ...crea un ServicioPreventivo. El id 0 es temporal; MySQL asigna el real. $extra aqui es kilometraje.
            } else {                                       // Si no...
                $servicio = new ServicioCorrectivo(0, $descripcion, $costoBase, $extra);  // ...crea un ServicioCorrectivo. $extra aqui son horas de mano de obra.
            }
            $taller->agregarServicio($servicio);           // Taller hace el INSERT. Adentro usa instanceof para saber que tipo guardar.
            $mensaje = 'Servicio creado correctamente.';   // Mensaje de exito.
        }
    }

    if ($accion == 'actualizar') {                         // ===== UPDATE =====
        $id          = (int) $_POST['id'];                 // Id del servicio que se esta editando (viene de un input hidden).
        $descripcion = trim($_POST['descripcion']);        // Nueva descripcion.
        $costoBase   = (float) $_POST['costoBase'];        // Nuevo costo base.

        // modificarServicio devuelve true si encontro el servicio.
        if ($taller->modificarServicio($id, $descripcion, $costoBase)) {  // Taller hace el UPDATE y dice si lo encontro.
            $mensaje = 'Servicio actualizado.';            // Exito.
        } else {                                           // Si devolvio false...
            $mensaje = 'No se encontro el servicio.';      // ...es que ese id no existe.
        }
    }
}

// ----- Eliminar: llega por la direccion, por ejemplo servicios.php?eliminar=2 -----
if (isset($_GET['eliminar'])) {                            // ===== DELETE ===== Si la direccion trae ?eliminar=ID es que dieron clic en Eliminar.
    $id = (int) $_GET['eliminar'];                         // Toma el id de la direccion y lo convierte a entero.
    if ($taller->eliminarServicio($id)) {                  // Taller hace el DELETE y devuelve true si borro algo.
        $mensaje = 'Servicio eliminado.';                  // Exito.
    }
}

// ----- Editar: si viene ?editar=2, se busca ese servicio para llenar el formulario -----
$editar = null;                                            // null = no se esta editando nada, se muestra el formulario de crear.
if (isset($_GET['editar'])) {                              // Si la direccion trae ?editar=ID...
    $editar = $taller->buscarServicio((int) $_GET['editar']);  // ...trae ese servicio como objeto para precargar sus datos en el formulario.
}

encabezado('servicios');                                   // Dibuja la barra resaltando "Servicios".
?>

<h1 class="titulo-pagina">Servicios</h1>

<!-- Aviso con el resultado de la ultima operacion -->
<?php if ($mensaje != '') { ?>
    <div class="aviso"><?php echo htmlspecialchars($mensaje); ?></div>   <!-- Texto del mensaje -->
<?php } ?>   <!-- Cierra el if del mensaje -->

<!-- Se muestra UNO de dos formularios: crear (si $editar es null) o actualizar (si hay un servicio cargado) -->
<?php if ($editar == null) { ?>
    <!-- FORMULARIO PARA CREAR (se muestra cuando NO se esta editando) -->
    <section class="panel">
        <h2>Crear servicio</h2>
        <form method="POST" class="formulario">
            <!-- Campo oculto: le dice al PHP de arriba que esta es la accion "crear" -->
            <input type="hidden" name="accion" value="crear">
            <label>Tipo de servicio
                <!-- El value decide si se crea ServicioPreventivo o ServicioCorrectivo -->
                <select name="tipo">
                    <option value="preventivo">Preventivo (10% de descuento)</option>
                    <option value="correctivo">Correctivo (+$250 por hora)</option>
                </select>
            </label>
            <label>Descripci&oacute;n
                <input type="text" name="descripcion" required>              <!-- llega como $_POST['descripcion'] -->
            </label>
            <label>Costo base
                <input type="number" name="costoBase" step="0.01" min="0" value="0" required>  <!-- step 0.01 permite centavos -->
            </label>
            <label>Kilometraje / Horas de mano de obra
                <input type="number" name="extra" step="0.01" min="0" value="0" required>      <!-- un solo campo que significa distinto segun el tipo -->
            </label>
            <button type="submit" class="boton">Crear</button>
        </form>
        <p class="nota">En un servicio preventivo el ultimo campo es el kilometraje;
            en uno correctivo son las horas de mano de obra.</p>
    </section>
<?php } else { ?>   <!-- Si $editar NO es null, se esta editando: formulario de actualizar -->
    <!-- FORMULARIO PARA ACTUALIZAR (se muestra cuando se dio clic en Editar) -->
    <section class="panel panel-editar">
        <h2>Modificar servicio #<?php echo $editar->getId(); ?></h2>   <!-- Id del servicio que se edita, sacado del objeto -->
        <form method="POST" class="formulario">
            <!-- Campo oculto: accion "actualizar" -->
            <input type="hidden" name="accion" value="actualizar">
            <!-- Campo oculto con el id, para que el PHP sepa cual servicio cambiar -->
            <input type="hidden" name="id" value="<?php echo $editar->getId(); ?>">
            <label>Descripci&oacute;n
                <!-- El value ya viene lleno con la descripcion actual, sacada del objeto -->
                <input type="text" name="descripcion"
                       value="<?php echo htmlspecialchars($editar->getDescripcion()); ?>" required>   <!-- Precarga la descripcion actual -->
            </label>
            <label>Costo base
                <!-- El value ya viene lleno con el costo actual -->
                <input type="number" name="costoBase" step="0.01" min="0"
                       value="<?php echo $editar->getCostoBase(); ?>" required>   <!-- Precarga el costo actual -->
            </label>
            <div class="fila-botones">
                <button type="submit" class="boton">Guardar cambios</button>
                <!-- Cancelar solo regresa a la pagina sin ?editar, o sea, vuelve al formulario de crear -->
                <a href="servicios.php" class="boton boton-gris">Cancelar</a>
            </div>
        </form>
    </section>
<?php } ?>   <!-- Cierra el if/else de crear o editar -->

<!-- ===== READ: la tabla con todo el catalogo ===== -->
<section class="panel">
    <h2>Cat&aacute;logo de servicios</h2>
    <?php $servicios = $taller->getServicios(); ?>         <!-- Se piden aqui, despues de crear/editar/borrar, para que la tabla ya salga actualizada -->
    <?php if (count($servicios) == 0) { ?>   <!-- Si el catalogo esta vacio -->
        <p class="vacio">Todav&iacute;a no hay servicios.</p>
    <?php } else { ?>   <!-- Si hay servicios, se dibuja la tabla -->
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th><th>Tipo</th><th>Descripci&oacute;n</th>
                    <th>Costo base</th><th>Costo final</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <!-- Una fila por servicio. $s es un objeto ServicioPreventivo o ServicioCorrectivo -->
            <?php foreach ($servicios as $s) { ?>
                <tr>
                    <td><?php echo $s->getId(); ?></td>    <!-- Id de la BD -->
                    <td>
                        <!-- getTipo() cambia segun la clase: aqui se nota el polimorfismo -->
                        <span class="etiqueta etiqueta-<?php echo strtolower($s->getTipo()); ?>">
                            <?php echo $s->getTipo(); ?>   <!-- Nombre del tipo: Preventivo o Correctivo segun la clase -->
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($s->getDescripcion()); ?></td>       <!-- Descripcion -->
                    <td>$<?php echo number_format($s->getCostoBase(), 2); ?></td>        <!-- Costo base tal cual se guardo -->
                    <!-- calcularCosto() calcula distinto en cada tipo de servicio -->
                    <td class="costo-final">$<?php echo number_format($s->calcularCosto(), 2); ?></td>  <!-- Preventivo: base * 0.9. Correctivo: base + horas * 250. La llamada es la misma -->
                    <td class="acciones">
                        <!-- Editar manda a esta pagina con ?editar=ID -->
                        <a href="servicios.php?editar=<?php echo $s->getId(); ?>" class="mini-boton">Editar</a>
                        <!-- Eliminar manda con ?eliminar=ID; el confirm() pide confirmacion antes de ir -->
                        <a href="servicios.php?eliminar=<?php echo $s->getId(); ?>"
                           class="mini-boton mini-rojo"
                           onclick="return confirm('Eliminar este servicio?');">Eliminar</a>
                    </td>
                </tr>
            <?php } ?>   <!-- Cierra el foreach de servicios -->
            </tbody>
        </table>
    <?php } ?>   <!-- Cierra el else de la tabla -->
</section>

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
