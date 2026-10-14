<?php
/* ============================================================================
 *  vehiculos.php - VEHICULOS (vista del administrador)
 *  Muestra todos los autos que los clientes han registrado, con su dueno.
 *  Es solo de consulta; quien registra autos es cada cliente.
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion, sesion y funciones de ayuda.
$u = exigirRol('admin');                                   // Portero: solo el admin puede ver esta pagina.

$vehiculos = $taller->getTodosVehiculos();   // filas con el nombre del dueno   // Aqui llegan FILAS (arreglos), no objetos, porque traen la columna extra "dueno" del JOIN.

encabezado('vehiculos');                                   // Dibuja la barra resaltando "Vehiculos".
?>

<h1 class="titulo-pagina">Veh&iacute;culos de clientes</h1>

<section class="panel">
    <!-- Si la lista esta vacia se muestra un aviso en vez de la tabla -->
    <?php if (count($vehiculos) == 0) { ?>
        <p class="vacio">Todav&iacute;a no hay veh&iacute;culos registrados.</p>
    <?php } else { ?>   <!-- Si SI hay vehiculos, se dibuja la tabla -->
        <table class="tabla">
            <!-- Encabezados de la tabla -->
            <thead>
                <tr>
                    <th>Due&ntilde;o</th><th>Tipo</th><th>Placas</th><th>Marca</th>
                    <th>Modelo</th><th>Color</th><th>A&ntilde;o</th><th>Kil&oacute;metros</th>
                </tr>
            </thead>
            <tbody>
            <!-- Una fila de tabla por cada vehiculo. $v es un arreglo, por eso se usa $v['columna'] -->
            <?php foreach ($vehiculos as $v) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($v['dueno']); ?></td>                 <!-- Nombre del cliente que viene del JOIN -->
                    <td><span class="etiqueta"><?php echo htmlspecialchars(ucfirst($v['tipo'])); ?></span></td>  <!-- ucfirst pone mayuscula la primera letra: "auto" -> "Auto" -->
                    <td><?php echo htmlspecialchars($v['placas']); ?></td>                <!-- Placas -->
                    <td><?php echo htmlspecialchars($v['marca']); ?></td>                 <!-- Marca -->
                    <td><?php echo htmlspecialchars($v['modelo']); ?></td>                <!-- Modelo -->
                    <td><?php echo htmlspecialchars($v['color']); ?></td>                 <!-- Color -->
                    <td><?php echo (int) $v['anio']; ?></td>                              <!-- (int) lo fuerza a numero entero -->
                    <td><?php echo number_format($v['kilometraje']); ?></td>              <!-- number_format pone comas de miles: 15000 -> 15,000 -->
                </tr>
            <?php } ?>   <!-- Cierra el foreach de vehiculos -->
            </tbody>
        </table>
    <?php } ?>   <!-- Cierra el else de la tabla -->
</section>

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
