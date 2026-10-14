<?php
/* ============================================================================
 *  PROYECTO FINAL - PROGRAMACION ORIENTADA A OBJETOS
 *  Sistema: TALLER ICNO (administracion de un taller mecanico)
 *  Autor: Isaac Navarro
 *  Lenguaje: PHP con base de datos MySQL
 * ----------------------------------------------------------------------------
 *  Este archivo es el "cerebro" del sistema. Tiene todo lo que no se ve:
 *    PARTE 1 - Las clases (Servicio y sus hijas, Vehiculo y sus hijas,
 *              Usuario, Cotizacion y la clase principal Taller).
 *    PARTE 2 - La conexion a la base de datos, la sesion y el control de
 *              acceso (quien puede entrar a cada pagina).
 *    PARTE 3 - Las funciones que dibujan la barra de arriba y el pie.
 *
 *  Todas las demas paginas empiezan con "require 'inc/taller.php'", o sea que
 *  cargan este archivo completo antes de hacer cualquier otra cosa.
 * ==========================================================================*/


/* ============================================================================
 *  PARTE 1 - LAS CLASES
 * ==========================================================================*/


/* ---------- SERVICIO (CLASE PADRE) ----------
 *  Representa un servicio del taller en general. De aqui heredan
 *  ServicioPreventivo y ServicioCorrectivo. */
class Servicio {                                           // Empieza la definicion de la clase Servicio.
    // Encapsulamiento: atributos private, se usan con getters y setters.
    private $id;                                           // Numero unico del servicio en la base de datos. Nadie lo toca directo.
    private $descripcion;                                  // Texto que dice que es el servicio, por ejemplo "Cambio de aceite".
    private $costoBase;                                    // Precio antes de aplicar descuentos o extras.

    // El constructor se ejecuta solo cuando alguien hace "new Servicio(...)".
    public function __construct($id, $descripcion, $costoBase) {   // Recibe los tres datos para armar el objeto.
        $this->id = $id;                                   // Guarda el id recibido en el atributo privado del objeto.
        $this->descripcion = $descripcion;                 // Guarda la descripcion recibida.
        $this->costoBase = $costoBase;                     // Guarda el costo base recibido.
    }

    // POLIMORFISMO: en el padre el costo es el costo base tal cual.
    public function calcularCosto() {                      // Metodo que las hijas van a reescribir con su propia formula.
        return $this->costoBase;                           // En el padre no hay formula especial: devuelve el precio tal cual.
    }

    public function getTipo() { return "General"; }       // Devuelve el nombre del tipo. Las hijas lo cambian a "Preventivo" o "Correctivo".

    public function getId() { return $this->id; }         // Getter: permite leer el id desde afuera sin tocar el atributo.
    public function setId($id) { $this->id = $id; }       // Setter: permite cambiar el id desde afuera de forma controlada.
    public function getDescripcion() { return $this->descripcion; }                 // Getter de la descripcion.
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; } // Setter de la descripcion.
    public function getCostoBase() { return $this->costoBase; }                     // Getter del costo base.
    public function setCostoBase($costoBase) { $this->costoBase = $costoBase; }     // Setter del costo base.
}                                                          // Termina la clase Servicio.

/* ---------- SERVICIOPREVENTIVO (HIJA) ----------
 *  Mantenimiento programado. Hereda todo de Servicio y agrega el kilometraje. */
class ServicioPreventivo extends Servicio {               // "extends Servicio" significa: esta clase hereda todo lo de Servicio.
    private $kilometraje;                                  // Atributo propio de esta hija: a que kilometraje toca el servicio.
    public function __construct($id, $descripcion, $costoBase, $kilometraje) {  // Recibe lo del padre mas el kilometraje.
        parent::__construct($id, $descripcion, $costoBase);                      // Le pasa al constructor del padre lo que a el le toca guardar.
        $this->kilometraje = $kilometraje;                                       // Y guarda aqui lo que es solo de esta clase.
    }
    // Mantenimiento programado: 10% de descuento.
    #[\Override]                                           // Marca que avisa a PHP: "este metodo reescribe uno del padre".
    public function calcularCosto() { return $this->getCostoBase() * 0.9; }      // Su propia formula: costo base menos el 10%.
    #[\Override]                                           // Otra sobrescritura.
    public function getTipo() { return "Preventivo"; }     // Ahora el tipo dice "Preventivo" en vez de "General".
    public function getKilometraje() { return $this->kilometraje; }              // Getter del kilometraje.
    public function setKilometraje($k) { $this->kilometraje = $k; }              // Setter del kilometraje.
}                                                          // Termina ServicioPreventivo.

/* ---------- SERVICIOCORRECTIVO (HIJA) ----------
 *  Una reparacion. Hereda de Servicio y agrega las horas de mano de obra. */
class ServicioCorrectivo extends Servicio {               // Segunda hija de Servicio.
    private $horasManoObra;                                // Atributo propio: cuantas horas de trabajo lleva la reparacion.
    public function __construct($id, $descripcion, $costoBase, $horasManoObra) { // Recibe lo del padre mas las horas.
        parent::__construct($id, $descripcion, $costoBase);                      // El padre guarda id, descripcion y costo base.
        $this->horasManoObra = $horasManoObra;                                   // Esta clase guarda las horas.
    }
    // Reparacion: al costo base se le suman 250 por cada hora de mano de obra.
    #[\Override]                                           // Sobrescribe calcularCosto del padre.
    public function calcularCosto() { return $this->getCostoBase() + ($this->horasManoObra * 250); } // Formula: base + 250 por hora.
    #[\Override]                                           // Sobrescribe getTipo del padre.
    public function getTipo() { return "Correctivo"; }     // El tipo ahora dice "Correctivo".
    public function getHorasManoObra() { return $this->horasManoObra; }          // Getter de las horas.
    public function setHorasManoObra($h) { $this->horasManoObra = $h; }          // Setter de las horas.
}                                                          // Termina ServicioCorrectivo.


/* ---------- VEHICULO (CLASE PADRE) ----------
 *  Segunda jerarquia de herencia. El metodo getFactor() es polimorfico:
 *  cada tipo de vehiculo ajusta el precio del servicio (una camioneta cuesta
 *  mas que una moto). El total de una cotizacion usa este factor. */
class Vehiculo {                                           // Empieza la clase padre de los vehiculos.
    private $id;                                           // Numero unico del vehiculo en la base de datos.
    private $placas;                                       // Las placas del vehiculo.
    private $marca;                                        // Marca, por ejemplo "Nissan".
    private $modelo;                                       // Modelo, por ejemplo "Versa".
    private $color;                                        // Color del vehiculo.
    private $anio;                                         // Anio de fabricacion.
    private $kilometraje;                                  // Kilometros recorridos.

    public function __construct($id, $placas, $marca, $modelo, $color, $anio, $kilometraje) { // Recibe las 7 caracteristicas.
        $this->id = $id;                                   // Guarda el id.
        $this->placas = $placas;                           // Guarda las placas.
        $this->marca = $marca;                             // Guarda la marca.
        $this->modelo = $modelo;                           // Guarda el modelo.
        $this->color = $color;                             // Guarda el color.
        $this->anio = $anio;                               // Guarda el anio.
        $this->kilometraje = $kilometraje;                 // Guarda el kilometraje.
    }

    // POLIMORFISMO: cada hija devuelve su propio factor y su nombre de tipo.
    public function getFactor() { return 1.0; }            // Factor base: 1.0 significa que no cambia el precio. Las hijas lo ajustan.
    public function getTipo()   { return "Vehiculo"; }     // Nombre generico del tipo. Las hijas devuelven "Auto", "Camioneta", etc.

    public function getId() { return $this->id; }         // Getter del id.
    public function setId($id) { $this->id = $id; }       // Setter del id.
    public function getPlacas() { return $this->placas; } // Getter de placas.
    public function setPlacas($p) { $this->placas = $p; } // Setter de placas.
    public function getMarca() { return $this->marca; }   // Getter de marca.
    public function setMarca($m) { $this->marca = $m; }   // Setter de marca.
    public function getModelo() { return $this->modelo; } // Getter de modelo.
    public function setModelo($m) { $this->modelo = $m; } // Setter de modelo.
    public function getColor() { return $this->color; }   // Getter de color.
    public function setColor($c) { $this->color = $c; }   // Setter de color.
    public function getAnio() { return $this->anio; }     // Getter de anio.
    public function setAnio($a) { $this->anio = $a; }     // Setter de anio.
    public function getKilometraje() { return $this->kilometraje; }  // Getter de kilometraje.
    public function setKilometraje($k) { $this->kilometraje = $k; }  // Setter de kilometraje.
}                                                          // Termina la clase Vehiculo.

/* ---------- Cuatro tipos de vehiculo (HIJAS de Vehiculo) ----------
 *  No agregan atributos. Solo cambian el comportamiento: el factor de precio
 *  y el nombre del tipo. Esa es la forma mas pura de polimorfismo. */
class Auto extends Vehiculo {                              // Auto hereda las 7 caracteristicas de Vehiculo.
    #[\Override] public function getFactor() { return 1.0; }        // Un auto paga el precio normal.
    #[\Override] public function getTipo()   { return "Auto"; }     // Su nombre de tipo.
}
class Camioneta extends Vehiculo {                         // Camioneta hereda de Vehiculo.
    #[\Override] public function getFactor() { return 1.3; }        // Una camioneta paga 30% mas.
    #[\Override] public function getTipo()   { return "Camioneta"; }// Su nombre de tipo.
}
class Camion extends Vehiculo {                            // Camion hereda de Vehiculo.
    #[\Override] public function getFactor() { return 1.6; }        // Un camion paga 60% mas.
    #[\Override] public function getTipo()   { return "Camion"; }   // Su nombre de tipo.
}
class Motocicleta extends Vehiculo {                       // Motocicleta hereda de Vehiculo.
    #[\Override] public function getFactor() { return 0.7; }        // Una moto paga 30% menos.
    #[\Override] public function getTipo()   { return "Motocicleta"; } // Su nombre de tipo.
}


/* ---------- USUARIO ----------
 *  Guarda a quien usa el sistema. El rol dice si es admin o cliente. */
class Usuario {                                            // Empieza la clase Usuario.
    private $id;                                           // Numero unico del usuario en la base de datos.
    private $nombre;                                       // Nombre completo.
    private $correo;                                       // Correo, que se usa para iniciar sesion.
    private $rol;                                          // 'admin' o 'cliente'. Define que paginas puede ver.

    public function __construct($id, $nombre, $correo, $rol) {  // Recibe los 4 datos.
        $this->id = $id;                                   // Guarda el id.
        $this->nombre = $nombre;                           // Guarda el nombre.
        $this->correo = $correo;                           // Guarda el correo.
        $this->rol = $rol;                                 // Guarda el rol.
    }

    public function esAdmin() { return $this->rol == 'admin'; }   // Devuelve verdadero si el rol es admin. Lo usan los menus y las redirecciones.

    public function getId() { return $this->id; }         // Getter del id.
    public function getNombre() { return $this->nombre; } // Getter del nombre.
    public function getCorreo() { return $this->correo; } // Getter del correo.
    public function getRol() { return $this->rol; }       // Getter del rol.
}                                                          // Termina la clase Usuario. No tiene setters porque el usuario no se edita desde el sistema.


/* ---------- COTIZACION ----------
 *  Es la solicitud de un cliente: un vehiculo y una lista de servicios.
 *  Su total ya trae aplicado el factor del tipo de vehiculo. */
class Cotizacion {                                         // Empieza la clase Cotizacion.
    private $id;                                           // Numero unico de la cotizacion.
    private $vehiculo;    // objeto Vehiculo (o una de sus hijas)              // Aqui se guarda un OBJETO, no un id. Composicion: la cotizacion "tiene un" vehiculo.
    private $servicios;   // arreglo de objetos Servicio                       // Lista de objetos Servicio (preventivos o correctivos).
    private $total;                                        // Total ya calculado con el factor del vehiculo.
    private $estado;                                       // 'pendiente' o 'revisada'.
    private $fecha;                                        // Fecha y hora en que se creo.

    public function __construct($id, $vehiculo, $servicios, $total, $estado, $fecha) { // Recibe los 6 datos.
        $this->id = $id;                                   // Guarda el id.
        $this->vehiculo = $vehiculo;                       // Guarda el objeto vehiculo.
        $this->servicios = $servicios;                     // Guarda la lista de servicios.
        $this->total = $total;                             // Guarda el total.
        $this->estado = $estado;                           // Guarda el estado.
        $this->fecha = $fecha;                             // Guarda la fecha.
    }

    public function getId() { return $this->id; }         // Getter del id.
    public function getVehiculo() { return $this->vehiculo; }    // Getter que devuelve el objeto vehiculo completo.
    public function getServicios() { return $this->servicios; }  // Getter que devuelve la lista de servicios.
    public function getTotal() { return $this->total; }   // Getter del total.
    public function getEstado() { return $this->estado; } // Getter del estado.
    public function getFecha() { return $this->fecha; }   // Getter de la fecha.
}                                                          // Termina la clase Cotizacion.


/* ---------- TALLER (clase principal) ----------
 *  Habla con la base de datos y ofrece todo lo que necesitan las paginas:
 *  registro y login de usuarios, vehiculos, servicios (CRUD) y cotizaciones.
 *  Es la UNICA clase que escribe SQL. Las paginas nunca tocan la base directo. */
class Taller {                                             // Empieza la clase principal.
    private $bd;                                           // La conexion PDO a MySQL. Privada: nadie fuera de Taller la usa.
    public function __construct($bd) { $this->bd = $bd; } // Recibe la conexion ya abierta y la guarda.

    /* ----- USUARIOS Y LOGIN ----- */

    // Convierte una fila de la tabla usuarios en un objeto Usuario.
    private function usuarioDesdeFila($f) {                // Es private porque solo lo usa Taller internamente.
        return new Usuario($f['id'], $f['nombre'], $f['correo'], $f['rol']);  // Toma cada columna de la fila y arma el objeto.
    }

    // Registra un cliente nuevo. La contrasena se guarda cifrada con
    // password_hash. Devuelve true, o false si el correo ya existe.
    public function registrarCliente($nombre, $correo, $clave) {  // Recibe los datos del formulario de registro.
        if ($this->buscarUsuarioPorCorreo($correo) != null) {     // Pregunta si ya existe alguien con ese correo.
            return false;                                  // Si ya existe, no registra y avisa con false.
        }
        $hash = password_hash($clave, PASSWORD_DEFAULT);   // Cifra la contrasena. Nunca se guarda en texto plano.
        $sql = "INSERT INTO usuarios (nombre, correo, clave, rol) VALUES (?, ?, ?, 'cliente')"; // SQL con "?" en vez de valores pegados: evita inyeccion SQL.
        $this->bd->prepare($sql)->execute([$nombre, $correo, $hash]);  // Prepara la consulta y la ejecuta rellenando los "?" en orden.
        return true;                                       // Avisa que todo salio bien.
    }

    public function buscarUsuarioPorCorreo($correo) {      // Busca un usuario por su correo.
        $c = $this->bd->prepare("SELECT * FROM usuarios WHERE correo = ?");  // Prepara la consulta con un hueco para el correo.
        $c->execute([$correo]);                            // La ejecuta poniendo el correo en el hueco.
        $f = $c->fetch();                                  // Trae la primera fila que coincida (o false si no hay).
        return $f ? $this->usuarioDesdeFila($f) : null;    // Si hubo fila la convierte en objeto Usuario; si no, devuelve null.
    }

    public function buscarUsuarioPorId($id) {              // Igual que la anterior pero buscando por id.
        $c = $this->bd->prepare("SELECT * FROM usuarios WHERE id = ?");      // Prepara la consulta.
        $c->execute([$id]);                                // La ejecuta con el id.
        $f = $c->fetch();                                  // Trae la fila.
        return $f ? $this->usuarioDesdeFila($f) : null;    // Devuelve el objeto o null.
    }

    // Revisa correo y contrasena. Si coinciden devuelve el Usuario; si no, null.
    // password_verify compara el texto contra el hash guardado.
    public function verificarLogin($correo, $clave) {      // Lo llama login.php.
        $c = $this->bd->prepare("SELECT * FROM usuarios WHERE correo = ?");  // Busca al usuario por correo.
        $c->execute([$correo]);                            // Ejecuta la busqueda.
        $f = $c->fetch();                                  // Trae la fila.
        if ($f && password_verify($clave, $f['clave'])) {  // Si existe Y la contrasena escrita coincide con el hash guardado...
            return $this->usuarioDesdeFila($f);            // ...devuelve el usuario como objeto.
        }
        return null;                                       // Si no existe o la contrasena esta mal, devuelve null.
    }

    /* ----- VEHICULOS ----- */

    // Segun la columna tipo arma el objeto de la clase hija correcta.
    private function vehiculoDesdeFila($f) {               // AQUI se materializa la herencia desde la base de datos.
        $args = [$f['id'], $f['placas'], $f['marca'], $f['modelo'], $f['color'], $f['anio'], $f['kilometraje']]; // Junta las 7 columnas en un arreglo.
        switch ($f['tipo']) {                              // Mira la columna "tipo" de la tabla para decidir que clase crear.
            case 'camioneta': return new Camioneta(...$args);   // Si dice camioneta, crea una Camioneta. Los "..." reparten el arreglo como parametros.
            case 'camion':    return new Camion(...$args);      // Si dice camion, crea un Camion.
            case 'moto':      return new Motocicleta(...$args); // Si dice moto, crea una Motocicleta.
            default:          return new Auto(...$args);        // Cualquier otra cosa se trata como Auto.
        }
    }

    // Agrega un vehiculo a un cliente. $tipo es 'auto','camioneta','camion','moto'.
    public function agregarVehiculo($idUsuario, $vehiculo, $tipo) {  // Recibe el dueno, el objeto vehiculo y el texto del tipo.
        // SQL de insercion con 8 huecos "?". Se escribe en dos renglones solo para que se lea mejor.
        $sql = "INSERT INTO vehiculos (id_usuario, placas, marca, modelo, color, anio, kilometraje, tipo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";          // Aqui termina el texto SQL.
        $this->bd->prepare($sql)->execute([               // Prepara y ejecuta rellenando los huecos...
            $idUsuario, $vehiculo->getPlacas(), $vehiculo->getMarca(), $vehiculo->getModelo(),     // ...con los datos que saca del objeto usando sus getters...
            $vehiculo->getColor(), $vehiculo->getAnio(), $vehiculo->getKilometraje(), $tipo        // ...y el tipo como texto para la columna.
        ]);
    }

    // Vehiculos de un cliente.
    public function getVehiculosDeUsuario($idUsuario) {    // Devuelve la lista de vehiculos de un cliente.
        $c = $this->bd->prepare("SELECT * FROM vehiculos WHERE id_usuario = ? ORDER BY id");  // Filtra por dueno y ordena por id.
        $c->execute([$idUsuario]);                         // Ejecuta con el id del cliente.
        $lista = array();                                  // Arreglo vacio donde se van a ir metiendo los objetos.
        foreach ($c->fetchAll() as $f) { $lista[] = $this->vehiculoDesdeFila($f); }  // Por cada fila, la convierte a objeto y la agrega a la lista.
        return $lista;                                     // Devuelve la lista de objetos Vehiculo (o hijas).
    }

    // Todos los vehiculos (para el admin), con el nombre del dueno.
    public function getTodosVehiculos() {                  // Lo usa el admin en vehiculos.php.
        $filas = $this->bd->query(                         // query() sirve cuando no hay valores externos que rellenar.
            // "v.*" trae todas las columnas de vehiculos y "u.nombre AS dueno" agrega el nombre del usuario con el alias "dueno".
            "SELECT v.*, u.nombre AS dueno FROM vehiculos v
             JOIN usuarios u ON u.id = v.id_usuario ORDER BY v.id")->fetchAll();  // JOIN: junta cada vehiculo con el nombre de su dueno.
        return $filas;   // aqui devolvemos las filas con el dueno incluido      // Devuelve filas (arreglos), no objetos, porque necesita la columna extra "dueno".
    }

    public function buscarVehiculo($id) {                  // Busca un vehiculo por id.
        $c = $this->bd->prepare("SELECT * FROM vehiculos WHERE id = ?");     // Prepara la consulta.
        $c->execute([$id]);                                // Ejecuta con el id.
        $f = $c->fetch();                                  // Trae la fila.
        return $f ? $this->vehiculoDesdeFila($f) : null;   // Devuelve el objeto de la clase correcta o null.
    }

    /* ----- SERVICIOS: CRUD (lo maneja el admin) ----- */

    private function servicioDesdeFila($f) {               // Convierte una fila de la tabla servicios en el objeto hijo correcto.
        if ($f['tipo'] == 'preventivo') {                  // Si la columna tipo dice preventivo...
            return new ServicioPreventivo($f['id'], $f['descripcion'], $f['costo_base'], $f['extra']);  // ...crea un ServicioPreventivo. "extra" aqui es el kilometraje.
        }
        return new ServicioCorrectivo($f['id'], $f['descripcion'], $f['costo_base'], $f['extra']);      // Si no, un ServicioCorrectivo. "extra" aqui son las horas.
    }

    public function agregarServicio($servicio) {           // CREATE del CRUD. Recibe un objeto Servicio (hijo).
        if ($servicio instanceof ServicioPreventivo) { $tipo = 'preventivo'; $extra = $servicio->getKilometraje(); }  // instanceof pregunta de que clase es el objeto. Si es preventivo, el extra es el kilometraje.
        else { $tipo = 'correctivo'; $extra = $servicio->getHorasManoObra(); }                                       // Si no, es correctivo y el extra son las horas.
        $sql = "INSERT INTO servicios (tipo, descripcion, costo_base, extra) VALUES (?, ?, ?, ?)";                  // SQL de insercion.
        $this->bd->prepare($sql)->execute([$tipo, $servicio->getDescripcion(), $servicio->getCostoBase(), $extra]); // Ejecuta con los datos del objeto.
    }

    public function getServicios() {                       // READ del CRUD. Devuelve todos los servicios.
        $filas = $this->bd->query("SELECT * FROM servicios ORDER BY id")->fetchAll();  // Trae todas las filas ordenadas por id.
        $lista = array();                                  // Lista vacia.
        foreach ($filas as $f) { $lista[] = $this->servicioDesdeFila($f); }  // Convierte cada fila en su objeto hijo.
        return $lista;                                     // Devuelve la lista de objetos.
    }

    public function buscarServicio($id) {                  // Busca un servicio por id.
        $c = $this->bd->prepare("SELECT * FROM servicios WHERE id = ?");     // Prepara.
        $c->execute([$id]);                                // Ejecuta.
        $f = $c->fetch();                                  // Trae la fila.
        return $f ? $this->servicioDesdeFila($f) : null;   // Devuelve objeto o null.
    }

    public function modificarServicio($id, $descripcion, $costoBase) {  // UPDATE del CRUD.
        if ($this->buscarServicio($id) == null) { return false; }       // Si no existe ese servicio, avisa con false.
        $sql = "UPDATE servicios SET descripcion = ?, costo_base = ? WHERE id = ?";  // SQL que cambia descripcion y costo del servicio con ese id.
        $this->bd->prepare($sql)->execute([$descripcion, $costoBase, $id]);          // Ejecuta con los nuevos valores.
        return true;                                       // Avisa que se actualizo.
    }

    public function eliminarServicio($id) {                // DELETE del CRUD.
        $c = $this->bd->prepare("DELETE FROM servicios WHERE id = ?");       // SQL que borra el servicio con ese id.
        $c->execute([$id]);                                // Ejecuta.
        return $c->rowCount() > 0;                         // rowCount dice cuantas filas se borraron. Si fue mas de 0, devuelve true.
    }

    /* ----- COTIZACIONES ----- */

    // Crea una cotizacion: calcula el total (servicio x factor del vehiculo)
    // y guarda la cotizacion junto con sus servicios.
    public function crearCotizacion($idUsuario, $idVehiculo, $idsServicios) {  // Recibe el cliente, el vehiculo elegido y la lista de ids de servicios marcados.
        $vehiculo = $this->buscarVehiculo($idVehiculo);    // Trae el vehiculo como objeto (Auto, Camioneta, etc.).
        if ($vehiculo == null || count($idsServicios) == 0) { return false; }  // Si no existe el vehiculo o no marcaron servicios, no hace nada.

        // Sumar el costo de cada servicio aplicando el factor del vehiculo.
        $total = 0;                                        // Acumulador del total, empieza en cero.
        foreach ($idsServicios as $idServ) {               // Recorre cada id de servicio marcado.
            $s = $this->buscarServicio($idServ);           // Trae el servicio como objeto (Preventivo o Correctivo).
            if ($s != null) { $total += $s->calcularCosto() * $vehiculo->getFactor(); }  // POLIMORFISMO DOBLE: cada servicio calcula a su manera y cada vehiculo da su factor. No se pregunta de que tipo es ninguno.
        }

        // Guardar la cotizacion y obtener su id.
        $sql = "INSERT INTO cotizaciones (id_usuario, id_vehiculo, total) VALUES (?, ?, ?)";  // SQL de insercion de la cotizacion. estado y fecha los pone MySQL por defecto.
        $this->bd->prepare($sql)->execute([$idUsuario, $idVehiculo, $total]);              // Ejecuta.
        $idCot = $this->bd->lastInsertId();                // Pregunta a MySQL que id le asigno a la fila que acaba de insertar.

        // Guardar cada servicio de la cotizacion.
        $ins = $this->bd->prepare("INSERT INTO cotizacion_servicios (id_cotizacion, id_servicio) VALUES (?, ?)");  // Prepara una sola vez la insercion en la tabla intermedia.
        foreach ($idsServicios as $idServ) { $ins->execute([$idCot, $idServ]); }  // La ejecuta una vez por cada servicio, ligandolo a la cotizacion nueva.
        return true;                                       // Avisa que se guardo.
    }

    // Arma un objeto Cotizacion a partir de una fila (con su vehiculo y servicios).
    private function cotizacionDesdeFila($f) {             // Convierte una fila de cotizaciones en un objeto completo.
        $vehiculo = $this->buscarVehiculo($f['id_vehiculo']);  // Trae el vehiculo de esa cotizacion como objeto.
        // Traer los servicios de esta cotizacion.
        $c = $this->bd->prepare(                           // Prepara una consulta que...
            // "s.*" trae las columnas de servicios; "cs" es la tabla intermedia que liga cotizaciones con servicios.
            "SELECT s.* FROM cotizacion_servicios cs
             JOIN servicios s ON s.id = cs.id_servicio WHERE cs.id_cotizacion = ?");  // ...junta la tabla intermedia con servicios para traer los servicios de esta cotizacion.
        $c->execute([$f['id']]);                           // Ejecuta con el id de la cotizacion.
        $servicios = array();                              // Lista vacia de servicios.
        foreach ($c->fetchAll() as $fs) { $servicios[] = $this->servicioDesdeFila($fs); }  // Convierte cada fila en su objeto hijo.
        return new Cotizacion($f['id'], $vehiculo, $servicios, $f['total'], $f['estado'], $f['fecha']);  // Arma la Cotizacion con el objeto vehiculo y la lista de objetos servicio.
    }

    public function getCotizacionesDeUsuario($idUsuario) { // Cotizaciones de un cliente, para mis_cotizaciones.php.
        $c = $this->bd->prepare("SELECT * FROM cotizaciones WHERE id_usuario = ? ORDER BY id DESC");  // Las del cliente, la mas nueva primero.
        $c->execute([$idUsuario]);                         // Ejecuta.
        $lista = array();                                  // Lista vacia.
        foreach ($c->fetchAll() as $f) { $lista[] = $this->cotizacionDesdeFila($f); }  // Convierte cada fila en objeto Cotizacion.
        return $lista;                                     // Devuelve la lista.
    }

    // Todas las cotizaciones (para el admin), con el nombre del cliente.
    public function getTodasCotizaciones() {               // Para cotizaciones.php del admin.
        $filas = $this->bd->query(                         // Consulta sin parametros externos.
            // "c.*" trae todas las columnas de cotizaciones y "u.nombre AS cliente" agrega el nombre del usuario con el alias "cliente".
            "SELECT c.*, u.nombre AS cliente FROM cotizaciones c
             JOIN usuarios u ON u.id = c.id_usuario ORDER BY c.id DESC")->fetchAll();  // JOIN para traer el nombre del cliente junto con cada cotizacion. DESC = la mas nueva primero.
        $lista = array();                                  // Lista vacia.
        foreach ($filas as $f) {                           // Recorre cada fila.
            $cot = $this->cotizacionDesdeFila($f);         // La convierte en objeto Cotizacion.
            $lista[] = ['cot' => $cot, 'cliente' => $f['cliente']];  // Guarda un par: el objeto y el nombre del cliente, porque Cotizacion no tiene atributo cliente.
        }
        return $lista;                                     // Devuelve la lista de pares.
    }

    public function marcarRevisada($idCotizacion) {        // El admin marca una cotizacion como atendida.
        $this->bd->prepare("UPDATE cotizaciones SET estado = 'revisada' WHERE id = ?")->execute([$idCotizacion]);  // UPDATE que cambia el estado de esa cotizacion.
    }
}                                                          // Termina la clase Taller.


/* ============================================================================
 *  PARTE 2 - BASE DE DATOS, SESION Y CONTROL DE ACCESO
 *  Esto ya no son clases: es codigo que se ejecuta cada vez que se carga
 *  cualquier pagina, porque todas hacen require de este archivo.
 * ==========================================================================*/

$config    = require __DIR__ . '/config.php';            // Lee los datos de conexion desde inc/config.php. Ese archivo NO se sube a GitHub (esta en .gitignore); hay un config.ejemplo.php para copiar.
$servidor  = $config['servidor'];                          // Donde esta MySQL. localhost = en la misma maquina que PHP.
$nombreBD  = $config['base'];   $usuarioBD = $config['usuario'];   // Nombre de la base de datos y usuario de MySQL con permisos sobre ella.
$claveBD   = $config['clave'];                             // Contrasena de ese usuario. Nunca va escrita en el codigo fuente.

try {                                                      // Intenta conectarse. Si falla, salta al catch.
    $bd = new PDO("mysql:host=$servidor;dbname=$nombreBD;charset=utf8mb4", $usuarioBD, $claveBD);  // Abre la conexion con PDO. utf8mb4 para que acepte acentos y enies.
    $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);          // Si una consulta falla, que lance una excepcion en vez de fallar en silencio.
    $bd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);     // Que las filas lleguen como arreglos con nombre de columna: $f['nombre'] en vez de $f[1].
} catch (PDOException $e) {                                // Si la conexion fallo...
    die("No se pudo conectar a la base de datos: " . $e->getMessage());    // ...detiene todo y muestra el motivo.
}

// La sesion guarda quien inicio sesion (solo su id).
session_start();                                           // Arranca la sesion de PHP. Sin esto $_SESSION no funciona.

$taller = new Taller($bd);                                 // Crea el UNICO objeto Taller del sistema y le pasa la conexion. Todas las paginas usan este $taller.

// Devuelve el Usuario que tiene la sesion abierta, o null si nadie entro.
function usuarioActual() {                                 // Funcion de ayuda que usan todas las paginas.
    global $taller;                                        // Pide acceso al $taller de afuera, porque dentro de una funcion no se ve por defecto.
    if (isset($_SESSION['usuario_id'])) {                  // Si en la sesion hay un id guardado (o sea, alguien hizo login)...
        return $taller->buscarUsuarioPorId($_SESSION['usuario_id']);  // ...busca ese usuario en la base y lo devuelve como objeto.
    }
    return null;                                           // Si no hay nadie, devuelve null.
}

// Obliga a haber iniciado sesion con cierto rol. Si no, manda al login.
// $rol puede ser 'admin', 'cliente' o '' (cualquiera con sesion).
function exigirRol($rol) {                                 // Es el "portero" de cada pagina protegida.
    $u = usuarioActual();                                  // Averigua quien esta logueado.
    if ($u == null) {                                      // Si nadie...
        header("Location: login.php");                     // ...lo manda al login.
        exit;                                              // Y detiene la pagina para que no se dibuje nada mas.
    }
    if ($rol != '' && $u->getRol() != $rol) {              // Si la pagina pide un rol y el usuario tiene otro...
        // Si el rol no corresponde, se manda a su propia zona.
        header("Location: " . ($u->esAdmin() ? "panel.php" : "cliente.php"));  // ...lo manda a su inicio: panel si es admin, cliente si es cliente.
        exit;                                              // Detiene la pagina.
    }
    return $u;                                             // Si todo bien, devuelve el usuario para que la pagina lo use.
}


/* ============================================================================
 *  PARTE 3 - PLANTILLA (barra de arriba y pie)
 *  Para no repetir el mismo HTML en cada pagina, se escribe una vez aqui.
 * ==========================================================================*/

// Dibuja la barra de arriba. El menu cambia segun quien esta usando el sistema.
function encabezado($activo) {                             // $activo dice que opcion del menu hay que resaltar.
    $u = usuarioActual();                                  // Averigua quien esta logueado para elegir el menu.
?>
<!-- A partir de aqui es HTML tal cual; PHP lo manda al navegador sin cambiarlo -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller ICNO</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <!-- Carga las fuentes Sora y Manrope desde Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- La hoja de estilos de todo el sitio -->
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<!-- Barra de navegacion -->
<header class="cabecera">
    <div class="cabecera-interna">
        <!-- Logo y nombre; al hacer clic regresan al inicio -->
        <a class="marca" href="index.php">
            <img src="img/logo.png" alt="Logo de Taller ICNO" class="marca-logo">
            <span class="marca-texto">Taller ICNO</span>
        </a>
        <div class="nav-derecha">
            <nav class="menu">
            <!-- Tres menus posibles. Se elige uno segun quien este logueado ($u viene de usuarioActual() arriba) -->
            <?php if ($u == null) { ?>                     <!-- Caso 1: nadie ha iniciado sesion -->
                <!-- Menu para quien no ha iniciado sesion -->
                <!-- Cada enlace recibe la clase "activo" solo si coincide con la pagina actual; el CSS lo pinta resaltado -->
                <a href="index.php"    class="<?php echo ($activo=='inicio')   ? 'activo' : ''; ?>">Inicio</a>             <!-- Enlace a la portada -->
                <a href="login.php"    class="<?php echo ($activo=='login')    ? 'activo' : ''; ?>">Iniciar sesi&oacute;n</a>  <!-- Enlace al login -->
                <a href="registro.php" class="<?php echo ($activo=='registro') ? 'activo' : ''; ?>">Registrarse</a>        <!-- Enlace al registro -->
            <?php } else if ($u->esAdmin()) { ?>           <!-- Caso 2: el logueado es administrador -->
                <!-- Menu del administrador -->
                <a href="panel.php"        class="<?php echo ($activo=='panel')      ? 'activo' : ''; ?>">Panel</a>          <!-- Resumen del taller -->
                <a href="servicios.php"    class="<?php echo ($activo=='servicios')  ? 'activo' : ''; ?>">Servicios</a>      <!-- El CRUD -->
                <a href="vehiculos.php"    class="<?php echo ($activo=='vehiculos')  ? 'activo' : ''; ?>">Veh&iacute;culos</a> <!-- Autos de todos los clientes -->
                <a href="cotizaciones.php" class="<?php echo ($activo=='cotiz')      ? 'activo' : ''; ?>">Cotizaciones</a>   <!-- Solicitudes de los clientes -->
                <a href="logout.php">Salir</a>             <!-- Cerrar sesion -->
            <?php } else { ?>                              <!-- Caso 3: el logueado es cliente -->
                <!-- Menu del cliente -->
                <a href="cliente.php"         class="<?php echo ($activo=='cliente') ? 'activo' : ''; ?>">Inicio</a>          <!-- Bienvenida del cliente -->
                <a href="mis_autos.php"       class="<?php echo ($activo=='autos')   ? 'activo' : ''; ?>">Mis autos</a>       <!-- Registrar y ver sus vehiculos -->
                <a href="cotizar.php"         class="<?php echo ($activo=='cotizar') ? 'activo' : ''; ?>">Cotizar</a>         <!-- Armar una cotizacion -->
                <a href="mis_cotizaciones.php" class="<?php echo ($activo=='miscotiz') ? 'activo' : ''; ?>">Mis cotizaciones</a> <!-- Historial -->
                <a href="logout.php">Salir</a>             <!-- Cerrar sesion -->
            <?php } ?>                                     <!-- Fin de los tres casos del menu -->
            </nav>
            <!-- Logo de la institucion a la derecha del menu -->
            <img src="img/idci_trans.png" alt="Instituto de Desarrollo y Capacitaci&oacute;n Intensiva" class="nav-idci">
        </div>
    </div>
</header>

<!-- Aqui empieza el contenido de cada pagina. Se cierra en pie() -->
<main class="contenido">
<?php
}                                                          // Termina la funcion encabezado().

function pie() {                                           // Dibuja el cierre de la pagina. Siempre va al final de cada archivo.
?>
</main>
<!-- Pie de pagina -->
<footer class="pie">
    <img src="img/idci_trans.png" alt="IDCI" class="pie-logo">
    <p>Proyecto Final de Programaci&oacute;n Orientada a Objetos. Autor: Isaac Navarro.</p>
</footer>
</body>
</html>
<?php
}                                                          // Termina la funcion pie().
