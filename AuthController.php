<?php
namespace App\Controllers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use App\Models\Usuario;

class AuthController {
    private $usuarioModel;
    private $twig;

    public function __construct($db) {
        $this->usuarioModel = new Usuario($db);
        
        $loader = new FilesystemLoader(__DIR__.'../../views');
        $this->twig = new Environment($loader);
    }

    public function mostrarFormularioLogin() {
        echo $this->twig->render('login.html.twig');
    }

    public function mostrarFormularioRegistro() {
        echo $this->twig->render('registro_usuario.html.twig');
    }

    public function iniciarSesion() {
    $cedula = $_POST['cedula'] ?? '';
    $password = $_POST['password'] ?? '';

    $usuario = $this->usuarioModel->obtenerUsuarioPorCedula($cedula);

    if ($usuario) {
        // Usuario encontrado, verificar la contraseña
        if (password_verify($password, $usuario['password'])) {
            // Contraseña correcta, iniciar sesión
            session_start();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            header('Location: /MiproyectoMASP/public/home');
            exit();
        } else {
            // Contraseña incorrecta
            echo $this->twig->render('login.html.twig', ['error' => 'Contraseña incorrecta.']);
            return;
        }
    } else {
        // Usuario no encontrado
        echo $this->twig->render('login.html.twig', ['error' => 'Usuario no encontrado.']);
        return;
    }
}

public function cerrarSesion() {
    // Destruir todas las variables de sesión
    $_SESSION = [];

    // Si se desea destruir la sesión completamente, borra también la cookie de sesión.
    // Nota: Esto destruirá la sesión, y no solo los datos de la sesión!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finalmente, destruir la sesión.
    session_destroy();

    // Redirigir al usuario a la página de inicio de sesión
    header('Location: /MiproyectoMASP/public/login');
    exit();
}

 public function mostrarHome() {
    if (!isset($_SESSION['usuario_id'])) {
        // Si no hay sesión iniciada, redirigir al formulario de inicio de sesión
        header('Location: /MiproyectoMASP/public/login');
        exit();
    }
    $nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

    echo $this->twig->render('home.html.twig', ['nombre' => $nombreUsuario]);
 }      

    public function registrarUsuario() {
        $nombre = $_POST['nombre'] ?? '';
        $cedula = $_POST['cedula'] ?? '';
        $cargo = $_POST['cargo'] ?? '';
        $rol = $_POST['rol'] ?? '';
        $contrasena = $_POST['password'] ?? '';
        $confirmarContrasena = $_POST['confirm_password'] ?? '';

        if ($contrasena !== $confirmarContrasena) {
            echo $this->twig->render('registro_usuario.html.twig', ['error' => 'Las contraseñas no coinciden.']);
            return;
        }

        try {
        // Llamar a la función crearUsuario del modelo
        if ($this->usuarioModel->crearUsuario($nombre, $cedula, $cargo, $rol, $contrasena)) {
            // Registro exitoso: redirigir a la página de inicio de sesión
            header('Location: /MiproyectoMASP/public/login?registro_exitoso=1');
            exit();
        } else {
            // Error genérico al registrar (podría ser por otros fallos)
            echo $this->twig->render('registro_usuario.html.twig', ['error' => 'Error al registrar el usuario. Inténtelo de nuevo.']);
            return;
        }
    } catch (\PDOException $e) {
        // Capturar la excepción de clave duplicada (cédula)
        if ($e->getCode() == '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'cedula') !== false) {
            echo $this->twig->render('registro_usuario.html.twig', ['error' => 'La cédula ingresada ya está registrada. <a href="/MiproyectoMASP/public/login">¿Ya tienes una cuenta? Inicia sesión aquí</a>.']);
            return;
        } else {
            // Otro error de base de datos
            error_log("Error de base de datos al registrar usuario: " . $e->getMessage());
            echo $this->twig->render('registro_usuario.html.twig', ['error' => 'Ocurrió un error en el servidor. Inténtelo más tarde.']);
            return;
        }
    }
}
}