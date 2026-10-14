<?php
/* ============================================================================
 *  index.php - PAGINA DE INICIO (estilo landing)
 *  Tiene un carrusel de servicios en la parte de arriba, una seccion con los
 *  servicios en tarjetas con foto, los pasos para usar el sistema y un cierre.
 *  Es solo presentacion para quien no ha entrado. El trabajo real esta en las
 *  paginas del cliente (mis_autos, cotizar) y del admin (servicios, etc.).
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion, sesion y la plantilla (encabezado y pie).

/*
 * Servicios destacados que se muestran en el carrusel y en las tarjetas.
 * Cada uno tiene su imagen, nombre, una descripcion corta y un precio de
 * ejemplo. Es contenido de la portada; los servicios reales se administran
 * en la pagina de Servicios.
 */
$destacados = [                                            // Arreglo de 4 arreglos. No viene de la BD, es contenido fijo de la portada.
    ["img" => "serv_detailing.png", "nombre" => "Detailing y lavado premium",   // Destacado 1: imagen y nombre; el renglon de abajo trae descripcion y precio.
     "desc" => "Lavado a detalle, encerado y protección de pintura.", "precio" => "desde $900"],      // Destacado 1.
    ["img" => "serv_aceite.png", "nombre" => "Cambio de aceite y filtro",   // Destacado 2.
     "desc" => "Aceite sintético y filtro nuevo para tu motor.", "precio" => "desde $1,080"],           // Destacado 2.
    ["img" => "serv_frenos.png", "nombre" => "Reparación de frenos",   // Destacado 3.
     "desc" => "Balatas, discos y revisión completa del sistema.", "precio" => "desde $2,800"],         // Destacado 3.
    ["img" => "serv_motor.png", "nombre" => "Afinación de motor",   // Destacado 4.
     "desc" => "Diagnóstico y puesta a punto del motor.", "precio" => "desde $2,250"],                  // Destacado 4.
];   // Cierra el arreglo de destacados.

encabezado('inicio');                                      // Dibuja la barra resaltando "Inicio".
?>

<!-- ===================== HERO: CARRUSEL DE SERVICIOS ===================== -->
<section class="hero-carrusel" id="carrusel">
    <!-- Un slide por cada destacado. $i es la posicion (0,1,2,3) y $s el arreglo del servicio -->
    <?php foreach ($destacados as $i => $s) { ?>
        <!-- Cada slide tiene su foto de fondo y su texto. Solo se ve el activo. -->
        <!-- El primer slide ($i == 0) arranca con la clase "activo"; los demas estan ocultos por CSS -->
        <div class="slide <?php echo ($i == 0) ? 'activo' : ''; ?>"
             style="background-image: linear-gradient(90deg, rgba(11,11,12,0.92) 5%, rgba(11,11,12,0.45) 55%, rgba(11,11,12,0.15) 100%), url('img/<?php echo $s['img']; ?>');">  <!-- Degradado oscuro encima de la foto para que el texto se lea -->
            <div class="slide-texto">
                <p class="slide-eyebrow">Taller ICNO</p>
                <h1><?php echo $s['nombre']; ?></h1>       <!-- Nombre del servicio destacado -->
                <p class="slide-desc"><?php echo $s['desc']; ?></p>      <!-- Descripcion corta -->
                <p class="slide-precio"><?php echo $s['precio']; ?></p>  <!-- Precio de ejemplo -->
                <a href="registro.php" class="boton">Cotizar mi auto</a> <!-- Boton que lleva a crear cuenta -->
            </div>
        </div>
    <?php } ?>   <!-- Cierra el foreach de slides -->

    <!-- Flechas para cambiar de slide; llaman a la funcion JavaScript de abajo -->
    <button class="carrusel-flecha izq" onclick="moverCarrusel(-1)" aria-label="Anterior">&#10094;</button>
    <button class="carrusel-flecha der" onclick="moverCarrusel(1)" aria-label="Siguiente">&#10095;</button>

    <!-- Puntos indicadores: uno por slide, clic para saltar directo -->
    <div class="carrusel-puntos">
        <?php foreach ($destacados as $i => $s) { ?>   <!-- Un punto por cada destacado; $i es su posicion -->
            <span class="punto <?php echo ($i == 0) ? 'activo' : ''; ?>" onclick="irASlide(<?php echo $i; ?>)"></span>   <!-- El primero empieza activo; el clic llama a irASlide con su posicion -->
        <?php } ?>   <!-- Cierra el foreach de puntos -->
    </div>
</section>

<!-- ===================== SERVICIOS EN TARJETAS ===================== -->
<section class="seccion">
    <h2 class="seccion-titulo">Nuestros servicios</h2>
    <div class="grid-servicios">
        <!-- Las mismas 4 entradas, ahora como tarjetas con foto -->
        <?php foreach ($destacados as $s) { ?>
            <article class="carta-servicio">
                <div class="carta-foto" style="background-image: url('img/<?php echo $s['img']; ?>');"></div>  <!-- Foto como fondo -->
                <div class="carta-cuerpo">
                    <h3><?php echo $s['nombre']; ?></h3>   <!-- Nombre -->
                    <p><?php echo $s['desc']; ?></p>       <!-- Descripcion -->
                    <span class="carta-precio"><?php echo $s['precio']; ?></span>  <!-- Precio -->
                </div>
            </article>
        <?php } ?>   <!-- Cierra el foreach de tarjetas -->
    </div>
</section>

<!-- ===================== PASOS ===================== -->
<!-- Tres pasos fijos que explican como usa el sistema un cliente -->
<section class="seccion">
    <h2 class="seccion-titulo">C&oacute;mo funciona</h2>
    <div class="grid-pasos">
        <div class="paso">
            <span class="paso-num">1</span>
            <h3>Registra tu veh&iacute;culo</h3>
            <p>Crea tu cuenta y guarda las placas, marca, modelo y tipo de tu auto.</p>
        </div>
        <div class="paso">
            <span class="paso-num">2</span>
            <h3>Elige los servicios</h3>
            <p>Marca los servicios que necesitas, preventivos o correctivos.</p>
        </div>
        <div class="paso">
            <span class="paso-num">3</span>
            <h3>Recibe tu cotizaci&oacute;n</h3>
            <p>El sistema calcula el total seg&uacute;n el tipo de tu veh&iacute;culo y el taller la revisa.</p>
        </div>
    </div>
</section>

<!-- ===================== CIERRE ===================== -->
<!-- Llamado final a la accion -->
<section class="cierre">
    <h2>Todo tu taller, en un solo lugar.</h2>
    <a href="registro.php" class="boton">Crear cuenta</a>
</section>

<script>
/* Carrusel sencillo en JavaScript (esto corre en el navegador, no en PHP):
   - Guarda todos los slides y puntos.
   - Muestra uno a la vez cambiando la clase "activo".
   - Avanza solo cada 3 segundos y tambien con las flechas o los puntos. */
let indiceActual = 0;                                      // Posicion del slide que se esta viendo. Empieza en el primero.
const slides = document.querySelectorAll('.hero-carrusel .slide');   // Agarra todos los div.slide de la pagina.
const puntos = document.querySelectorAll('.carrusel-puntos .punto'); // Agarra todos los puntos indicadores.

function mostrarSlide(n) {                                 // Muestra el slide numero n.
    // n da la vuelta si se pasa del primero o del ultimo
    indiceActual = (n + slides.length) % slides.length;    // El % (modulo) hace que despues del ultimo vuelva al primero y antes del primero vaya al ultimo.
    slides.forEach((s, i) => s.classList.toggle('activo', i === indiceActual));  // Pone "activo" solo al slide cuya posicion coincide; a los demas se lo quita.
    puntos.forEach((p, i) => p.classList.toggle('activo', i === indiceActual));  // Lo mismo con los puntos.
}
function moverCarrusel(paso) { mostrarSlide(indiceActual + paso); }  // Las flechas llaman esto con -1 (atras) o 1 (adelante).
function irASlide(n)        { mostrarSlide(n); }           // Los puntos llaman esto con su posicion.

// Avanza solo cada 3 segundos
setInterval(() => moverCarrusel(1), 3000);                 // Cada 3000 milisegundos avanza un slide automaticamente.
</script>

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
