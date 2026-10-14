<?php
/* ============================================================================
 *  login.php - INICIAR SESION (para admin y cliente)
 *  Revisa el correo y la contrasena. Si son correctos, guarda al usuario en
 *  la sesion y lo manda a su zona (panel de admin o inicio de cliente).
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion a la BD, sesion y las funciones de ayuda.

// Si ya hay sesion, no tiene caso volver a entrar: se manda a su zona.
$yo = usuarioActual();                                     // Pregunta si ya hay alguien logueado.
if ($yo != null) {                                         // Si si hay alguien...
    header("Location: " . ($yo->esAdmin() ? "panel.php" : "cliente.php"));  // ...lo manda a su inicio segun su rol. El "? :" es un if corto.
    exit;                                                  // Y detiene la pagina.
}

$error = '';                                               // Texto del error a mostrar. Empieza vacio porque todavia no hay error.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {                // Esto es verdadero solo cuando el usuario envio el formulario.
    $correo = trim($_POST['correo']);                      // Toma el correo del formulario y le quita espacios al inicio y al final.
    $clave  = $_POST['clave'];                             // Toma la contrasena tal cual la escribio.

    $usuario = $taller->verificarLogin($correo, $clave);   // Le pide a Taller que revise correo y contrasena contra la BD.
    if ($usuario != null) {                                // Si devolvio un Usuario, los datos eran correctos.
        // Correcto: se guarda el id en la sesion y se redirige segun el rol.
        $_SESSION['usuario_id'] = $usuario->getId();       // Guarda SOLO el id en la sesion. Con eso las demas paginas saben quien es.
        header("Location: " . ($usuario->esAdmin() ? "panel.php" : "cliente.php"));  // Lo manda a su zona: admin al panel, cliente a su inicio.
        exit;                                              // Detiene la pagina.
    } else {                                               // Si devolvio null...
        $error = 'Correo o contraseña incorrectos.';       // ...se prepara el mensaje de error para mostrarlo abajo.
    }
}

encabezado('login');                                       // Dibuja la barra de arriba resaltando "Iniciar sesion".
?>

<!-- Caja centrada del formulario de acceso -->
<div class="caja-auth">
    <h1>Iniciar sesi&oacute;n</h1>

    <!-- Solo se muestra el aviso rojo si hubo error -->
    <?php if ($error != '') { ?>
        <div class="aviso aviso-error"><?php echo htmlspecialchars($error); ?></div>   <!-- Texto del error -->
    <?php } ?>   <!-- Cierra el if del error -->

    <!-- El formulario se envia por POST a esta misma pagina -->
    <form method="POST" class="form-auth">
        <label>Correo
            <input type="email" name="correo" required>    <!-- name="correo" es la llave con la que llega en $_POST -->
        </label>
        <label>Contrase&ntilde;a
            <input type="password" name="clave" required>  <!-- type="password" oculta lo que se escribe -->
        </label>
        <button type="submit" class="boton">Entrar</button>
    </form>

    <!-- Enlace para quien todavia no tiene cuenta -->
    <p class="auth-pie">&iquest;No tienes cuenta? <a href="registro.php">Reg&iacute;strate aqu&iacute;</a>.</p>
</div>

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
