<?php
namespace App\Controllers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use App\Models\Estudiante;

class EstudianteController {
    private $estudianteModel;
    private $twig;

    public function __construct($twig, $db) {
        $this->estudianteModel = new Estudiante($db);
        $this->twig = $twig;
    }

    public function mostrarFormularioRegistro() {
        echo $this->twig->render('estudiante/crear.html.twig');
    }
    // *** MÉTODO PARA REGISTRAR UN NUEVO ESTUDIANTE ***
    // Este método se encarga de recibir los datos del formulario y validarlos
    public function registrarEstudiante() {
        $matricula = $_POST['matricula'] ?? '';
        $cedula = $_POST['cedula'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefono = $_POST['telefono'] ?? '';

        $errores = [];

        // 1. Validación de la Matrícula
        if (empty(trim($matricula))) {
            $errores['matricula'] = 'La matrícula es requerida.';
        } elseif (strlen(trim($matricula)) < 5 || strlen(trim($matricula)) > 20) {
            $errores['matricula'] = 'La matrícula debe tener entre 5 y 20 caracteres.';
        } else {
            $matricula = strtoupper(trim($matricula)); 
        }

        // 2. Validación de la Cédula
        if (empty(trim($cedula))) {
            $errores['cedula'] = 'La cédula es requerida.';
        } elseif (strlen(trim($cedula)) < 6 || strlen(trim($cedula)) > 20) {
            $errores['cedula'] = 'La cédula debe tener entre 6 y 20 caracteres.';
        } else {
            $cedula = trim($cedula);
            
        }

        // 3. Validación del Nombre
        if (empty(trim($nombre))) {
            $errores['nombre'] = 'El nombre es requerido.';
        } elseif (strlen(trim($nombre)) < 3 || strlen(trim($nombre)) > 255) {
            $errores['nombre'] = 'El nombre debe tener entre 3 y 255 caracteres.';
        } else {
            $nombre = trim($nombre);
        }

        // 4. Validación del Apellido
        if (empty(trim($apellido))) {
            $errores['apellido'] = 'El apellido es requerido.';
        } elseif (strlen(trim($apellido)) < 3 || strlen(trim($apellido)) > 255) {
            $errores['apellido'] = 'El apellido debe tener entre 3 y 255 caracteres.';
        } else {
            $apellido = trim($apellido);
        }

        // 5. Validación del Email
        if (empty(trim($email))) {
            $errores['email'] = 'El email es requerido.';
        } elseif (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El email no tiene un formato válido.';
        } else {
            $email = strtolower(trim($email));
            // Aquí podrías agregar una validación adicional para la unicidad
        }

        // 6. Validación del Teléfono
        if (empty(trim($telefono))) {
            $errores['telefono'] = 'El teléfono es requerido.';
        } elseif (strlen(trim($telefono)) < 7 || strlen(trim($telefono)) > 20) {
            $errores['telefono'] = 'El teléfono debe tener entre 7 y 20 caracteres.';
        } else {
            $telefono = trim($telefono);
            
        }

        // Verificar si hay errores
        if (!empty($errores)) {
            echo $this->twig->render('estudiante/crear.html.twig', ['errores' => $errores, 'old_data' => $_POST]);
            return;
        }

        // Si no hay errores, proceder con la creación del estudiante en el modelo
        try {
            if ($this->estudianteModel->crearEstudiante($matricula, $cedula, $nombre, $apellido, $email, $telefono)) {
                // Registro exitoso: redirigir a una página de éxito o al inicio
                header('Location: /MiproyectoMASP/public/home?registro_estudiante_exitoso=1');
                exit();
            } else {
                // Error genérico al registrar
                echo $this->twig->render('estudiante/crear.html.twig', ['error' => 'Error al registrar el estudiante. Inténtelo de nuevo.', 'old_data' => $_POST]);
                return;
            }
        } catch (\PDOException $e) {
            var_dump($e->getMessage()); // Imprime el mensaje de error completo

            if ($e->getCode() == '23000') {
                if (strpos($e->getMessage(), 'matricula') !== false) {
                    echo $this->twig->render('estudiante/crear.html.twig', ['error' => 'La matrícula ingresada ya está registrada.', 'old_data' => $_POST]);
                    return;
                } elseif (strpos($e->getMessage(), 'cedula') !== false) {
                    echo $this->twig->render('estudiante/crear.html.twig', ['error' => 'La cédula ingresada ya está registrada.', 'old_data' => $_POST]);
                    return;
                } elseif (strpos($e->getMessage(), 'email') !== false) {
                    echo $this->twig->render('estudiante/crear.html.twig', ['error' => 'El email ingresado ya está registrado.', 'old_data' => $_POST]);
                    return;
                }
            }

            // Otro error de base de datos (si no es un error de unicidad específico)
            error_log("Error de base de datos al registrar estudiante: " . $e->getMessage());
            echo $this->twig->render('estudiante/crear.html.twig', ['error' => 'Ocurrió un error en el servidor al registrar el estudiante.', 'old_data' => $_POST]);
            return;
        }
    }

    public function mostrarMenuEstudiantes() {
        echo $this->twig->render('estudiante/menu.html.twig');
    }
    public function listarEstudiantes() {
        $estudiantes = $this->estudianteModel->obtenerTodosLosEstudiantes();
        echo $this->twig->render('estudiante/lista.html.twig', ['estudiantes' => $estudiantes]);
    }

    // *** MÉTODO PARA MOSTRAR EL FORMULARIO DE EDICIÓN DE ESTUDIANTE ***
    public function mostrarFormularioEditar($id) {
        $estudiante = $this->estudianteModel->obtenerEstudiantePorId($id);

        if ($estudiante) {
            echo $this->twig->render('estudiante/editar.html.twig', ['estudiante' => $estudiante]);
        } else {
            
            header('Location: /MiproyectoMASP/public/estudiantes?mensaje=Estudiante no encontrado');
            exit;
        }
    }

    // *** MÉTODO PARA ACTUALIZAR ESTUDIANTE ***
    public function actualizarEstudiante() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $matricula = $_POST['matricula'] ?? '';
            $cedula = $_POST['cedula'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $apellido = $_POST['apellido'] ?? '';
            $email = $_POST['email'] ?? '';
            $telefono = $_POST['telefono'] ?? '';

            // Validación básica
            if (empty($id) || empty($matricula) || empty($cedula) || empty($nombre) || empty($apellido) || empty($email) || empty($telefono)) {
                // Recuperar el estudiante para mostrar el formulario con errores
                $estudiante = $this->estudianteModel->obtenerEstudiantePorId($id);
                echo $this->twig->render('estudiante/editar.html.twig', ['estudiante' => $estudiante, 'error' => 'Todos los campos son obligatorios.']);
                return;
            }

            if ($this->estudianteModel->actualizarEstudiante($id, $matricula, $cedula, $nombre, $apellido, $email, $telefono)) {
                // Redirigir a la lista de estudiantes con un mensaje de éxito
                header('Location: /MiproyectoMASP/public/estudiantes?mensaje=Estudiante actualizado exitosamente');
                exit;
            } else {
                // Recuperar el estudiante para mostrar el formulario con errores
                $estudiante = $this->estudianteModel->obtenerEstudiantePorId($id);
                echo $this->twig->render('estudiante/editar.html.twig', ['estudiante' => $estudiante, 'error' => 'Error al actualizar el estudiante.']);
            }
        } else {
            // Si no es un POST, redirigir o mostrar un error
            header('Location: /MiproyectoMASP/public/estudiantes?mensaje=Acceso no permitido');
            exit;
        }
    }

    public function eliminarEstudiante($id) {
        if (empty($id)) {
            header('Location: /MiproyectoMASP/public/estudiantes?mensaje=ID de estudiante no proporcionado');
            exit;
        }

        try {
            if ($this->estudianteModel->eliminarEstudiante($id)) {
                header('Location: /MiproyectoMASP/public/estudiantes?mensaje=Estudiante eliminado exitosamente');
                exit;
            } else {
                header('Location: /MiproyectoMASP/public/estudiantes?mensaje=Error al eliminar el estudiante');
                exit;
            }
        } catch (\PDOException $e) {
            error_log("Error de base de datos al eliminar estudiante: " . $e->getMessage());
            header('Location: /MiproyectoMASP/public/estudiantes?mensaje=Error en la base de datos al eliminar el estudiante');
            exit;
        }
    }
}
