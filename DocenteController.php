<?php
namespace App\Controllers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use App\Models\Docente;

class DocenteController {
    private $docenteModel;
    private $twig;

    public function __construct($twig, $db) {
        $this->docenteModel = new Docente($db);
        $this->twig = $twig;
    }

    public function mostrarFormularioRegistro() {
        echo $this->twig->render('docente/crear.html.twig');
    }

    public function registrarDocente() {
        $cedula = $_POST['cedula'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefono = $_POST['telefono'] ?? '';

        $errores = [];

        // 1. Validación de la Cédula
        if (empty(trim($cedula))) {
            $errores['cedula'] = 'La cédula es requerida.';
        } elseif (strlen(trim($cedula)) < 6 || strlen(trim($cedula)) > 20) {
            $errores['cedula'] = 'La cédula debe tener entre 6 y 20 caracteres.';
        } else {
            $cedula = trim($cedula);
            
        }

        // 2. Validación del Nombre
        if (empty(trim($nombre))) {
            $errores['nombre'] = 'El nombre es requerido.';
        } elseif (strlen(trim($nombre)) < 3 || strlen(trim($nombre)) > 255) {
            $errores['nombre'] = 'El nombre debe tener entre 3 y 255 caracteres.';
        } else {
            $nombre = trim($nombre);
        }

        // 3. Validación del Apellido
        if (empty(trim($apellido))) {
            $errores['apellido'] = 'El apellido es requerido.';
        } elseif (strlen(trim($apellido)) < 3 || strlen(trim($apellido)) > 255) {
            $errores['apellido'] = 'El apellido debe tener entre 3 y 255 caracteres.';
        } else {
            $apellido = trim($apellido);
        }

        // 4. Validación del Email
        if (empty(trim($email))) {
            $errores['email'] = 'El email es requerido.';
        } elseif (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'El email no tiene un formato válido.';
        } else {
            $email = strtolower(trim($email));
            
        }

        // 5. Validación del Teléfono
        if (empty(trim($telefono))) {
            $errores['telefono'] = 'El teléfono es requerido.';
        } elseif (strlen(trim($telefono)) < 7 || strlen(trim($telefono)) > 20) {
            $errores['telefono'] = 'El teléfono debe tener entre 7 y 20 caracteres.';
        } else {
            $telefono = trim($telefono);
           
        }

        // Verificar si hay errores
        if (!empty($errores)) {
            echo $this->twig->render('registro_docente.html.twig', ['errores' => $errores, 'old_data' => $_POST]);
            return;
        }

        // Si no hay errores, proceder con la creación del docente en el modelo
        try {
            if ($this->docenteModel->crearDocente($cedula, $nombre, $apellido, $email, $telefono)) {
                // Registro exitoso: redirigir a una página de éxito o al inicio
                header('Location: /MiproyectoMASP/public/home?registro_docente_exitoso=1');
                exit();
            } else {
                // Error genérico al registrar
                echo $this->twig->render('docente/crear.html.twig', ['error' => 'Error al registrar el docente. Inténtelo de nuevo.', 'old_data' => $_POST]);
                return;
            }
        } catch (\PDOException $e) {
            // Manejar excepciones de base de datos (por ejemplo, cédula o email duplicados)
            if ($e->getCode() == '23000') {
                if (strpos($e->getMessage(), 'cedula') !== false) {
                    echo $this->twig->render('docente/crear.html.twig', ['error' => 'La cédula ingresada ya está registrada.', 'old_data' => $_POST]);
                    return;
                } elseif (strpos($e->getMessage(), 'email') !== false) {
                    echo $this->twig->render('docente/crear.html.twig', ['error' => 'El email ingresado ya está registrado.', 'old_data' => $_POST]);
                    return;
                }
            }
            // Otro error de base de datos
            error_log("Error de base de datos al registrar docente: " . $e->getMessage());
            echo $this->twig->render('docente/crear.html.twig', ['error' => 'Ocurrió un error en el servidor al registrar el docente.', 'old_data' => $_POST]);
            return;
        }

    }

    public function ListarDocentes() {
        $docentes = $this->docenteModel->obtenerTodosLosDocentes();
        echo $this->twig->render('docente/lista.html.twig', ['docentes' => $docentes]);
    }

    public function eliminarDocente($id) {
    if (!is_numeric($id)) {
        // Manejar el caso en que el ID no es válido
        echo "ID de docente no válido.";
        return;
    }

    // Aquí podrías agregar una confirmación antes de eliminar
    if ($this->docenteModel->eliminarDocente($id)) {
        header('Location: /MiproyectoMASP/public/docentes?eliminado_exitosamente=1');
        exit();
    } else {
        echo "Error al eliminar el docente.";
    }
    }

   public function mostrarFormularioEditar($id) {
    if (!is_numeric($id)) {
        // Manejar el caso en que el ID no es válido
        echo "ID de docente no válido.";
        return;
    }

    $docente = $this->docenteModel->obtenerDocentePorId($id);

    if ($docente) {
        echo $this->twig->render('docente/editar.html.twig', ['docente' => $docente]);
    } else {
        echo "Docente no encontrado.";
    }
}

    public function actualizarDocente() {
    $id = $_POST['id'] ?? '';
    $cedula = $_POST['cedula'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    $errores = [];

    // Validación de los datos (similar a registrarDocente, pero incluyendo la validación del ID)
    if (empty(trim($id)) || !is_numeric($id)) {
        // Error con el ID
        echo "ID de docente no válido.";
        return;
    }

    if (empty(trim($cedula))) {
        $errores['cedula'] = 'La cédula es requerida.';
    } elseif (strlen(trim($cedula)) < 6 || strlen(trim($cedula)) > 20) {
        $errores['cedula'] = 'La cédula debe tener entre 6 y 20 caracteres.';
    }

    if (empty(trim($nombre))) {
        $errores['nombre'] = 'El nombre es requerido.';
    } elseif (strlen(trim($nombre)) < 3 || strlen(trim($nombre)) > 255) {
        $errores['nombre'] = 'El nombre debe tener entre 3 y 255 caracteres.';
    }

    if (empty(trim($apellido))) {
        $errores['apellido'] = 'El apellido es requerido.';
    } elseif (strlen(trim($apellido)) < 3 || strlen(trim($apellido)) > 255) {
        $errores['apellido'] = 'El apellido debe tener entre 3 y 255 caracteres.';
    }

    if (empty(trim($email))) {
        $errores['email'] = 'El email es requerido.';
    } elseif (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no tiene un formato válido.';
    }

    if (empty(trim($telefono))) {
        $errores['telefono'] = 'El teléfono es requerido.';
    } elseif (strlen(trim($telefono)) < 7 || strlen(trim($telefono)) > 20) {
        $errores['telefono'] = 'El teléfono debe tener entre 7 y 20 caracteres.';
    }

    if (!empty($errores)) {
        // Volver a renderizar el formulario de edición con los errores y los datos antiguos
        $docente = $this->docenteModel->obtenerDocentePorId($id);
        echo $this->twig->render('docente/editar.html.twig', ['errores' => $errores, 'docente' => $_POST]);
        return;
    }

    try {
        if ($this->docenteModel->actualizarDocente($id, $cedula, $nombre, $apellido, $email, $telefono)) {
            header('Location: /MiproyectoMASP/public/docentes?actualizado_exitosamente=1');
            exit();
        } else {
            $docente = $this->docenteModel->obtenerDocentePorId($id);
            echo $this->twig->render('docente/editar.html.twig', ['error' => 'Error al actualizar el docente. Inténtelo de nuevo.', 'docente' => $_POST]);
            return;
        }
    } catch (\PDOException $e) {
        if ($e->getCode() == '23000') {
            if (strpos($e->getMessage(), 'cedula') !== false) {
                $docente = $this->docenteModel->obtenerDocentePorId($id);
                echo $this->twig->render('docente/editar.html.twig', ['error' => 'La cédula ingresada ya está registrada.', 'docente' => $_POST]);
                return;
            } elseif (strpos($e->getMessage(), 'email') !== false) {
                $docente = $this->docenteModel->obtenerDocentePorId($id);
                echo $this->twig->render('docente/editar.html.twig', ['error' => 'El email ingresado ya está registrado.', 'docente' => $_POST]);
                return;
            }
        }
        error_log("Error de base de datos al actualizar docente: " . $e->getMessage());
        $docente = $this->docenteModel->obtenerDocentePorId($id);
        echo $this->twig->render('docente/editar.html.twig', ['error' => 'Ocurrió un error en el servidor al actualizar el docente.', 'docente' => $_POST]);
        return;
    }
}
    public function mostrarMenuDocentes() {
        echo $this->twig->render('docente/menu.html.twig');
    }
}
