<?php
/* ============================================================================
 *  registro.php - REGISTRO DE CLIENTE
 *  Un cliente nuevo crea su cuenta con nombre, correo y contrasena. Al
 *  terminar queda con la sesion iniciada y pasa a registrar su auto.
 * ==========================================================================*/

require 'inc/taller.php';                                      // Carga clases, conexion, sesion y funciones de ayuda.

// Si ya hay sesion, se manda a su zona.
$yo = usuarioActual();                                     // Pregunta si ya hay alguien logueado.
if ($yo != null) {                                         // Si ya entro alguien, no tiene sentido registrarse...
    header("Location: " . ($yo->esAdmin() ? "panel.php" : "cliente.php"));  // ...asi que lo manda a su inicio segun el rol.
    exit;                                                  // Detiene la pagina.
}

$error = '';                                               // Mensaje de error, vacio al inicio.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {                // Solo entra aqui cuando se envio el formulario.
    $nombre = trim($_POST['nombre']);                      // Toma el nombre y le quita espacios sobrantes.
    $correo = trim($_POST['correo']);                      // Toma el correo y le quita espacios sobrantes.
    $clave  = $_POST['clave'];                             // Toma la contrasena tal cual.

    if ($nombre == '' || $correo == '' || $clave == '') {  // Validacion 1: que no falte ningun dato.
        $error = 'Faltan datos por llenar.';               // Si falta algo, mensaje de error.
    } else if (strlen($clave) < 4) {                       // Validacion 2: que la contrasena tenga al menos 4 letras.
        $error = 'La contraseña debe tener al menos 4 caracteres.';  // Si es muy corta, mensaje de error.
    } else if (!$taller->registrarCliente($nombre, $correo, $clave)) {  // Intenta registrarlo. Si devuelve false es que el correo ya existia.
        $error = 'Ese correo ya está registrado.';         // Mensaje de error por correo repetido.
    } else {                                               // Si paso todas las validaciones y se registro bien...
        // Registro correcto: se inicia sesion automaticamente.
        $usuario = $taller->buscarUsuarioPorCorreo($correo);  // Busca al usuario recien creado para obtener su id.
        $_SESSION['usuario_id'] = $usuario->getId();       // Guarda el id en la sesion: queda logueado sin pasar por login.
        header("Location: mis_autos.php");                 // Lo manda directo a registrar su primer auto.
        exit;                                              // Detiene la pagina.
    }
}

encabezado('registro');                                    // Dibuja la barra resaltando "Registrarse".
?>

<!-- Caja centrada del formulario de registro -->
<div class="caja-auth">
    <h1>Crear cuenta</h1>

    <!-- El aviso rojo solo aparece si hubo error -->
    <?php if ($error != '') { ?>
        <div class="aviso aviso-error"><?php echo htmlspecialchars($error); ?></div>   <!-- Texto del error -->
    <?php } ?>   <!-- Cierra el if del error -->

    <!-- Formulario que se envia por POST a esta misma pagina -->
    <form method="POST" class="form-auth">
        <label>Nombre completo
            <input type="text" name="nombre" required>     <!-- llega como $_POST['nombre'] -->
        </label>
        <label>Correo
            <input type="email" name="correo" required>    <!-- type="email" hace que el navegador valide el formato -->
        </label>
        <label>Contrase&ntilde;a
            <input type="password" name="clave" required>  <!-- llega como $_POST['clave'] -->
        </label>
        <button type="submit" class="boton">Registrarme</button>
    </form>

    <!-- Enlace para quien ya tiene cuenta -->
    <p class="auth-pie">&iquest;Ya tienes cuenta? <a href="login.php">Inicia sesi&oacute;n</a>.</p>
</div>

<!-- pie() cierra el <main>, dibuja el pie de pagina y cierra el HTML -->
<?php pie(); ?>
