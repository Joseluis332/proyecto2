<?php
namespace App\Models;

use PDO;
use PDOException;

class Estudiante {
    private $db;
    private $table_name = "estudiantes";

    public $id;
    public $matricula;
    public $cedula;
    public $nombre;
    public $apellido;
    public $email;
    public $telefono;
    public $fecha_registro;

    public function __construct($db) {
        $this->db = $db;
    }

    public function crearEstudiante($matricula, $cedula, $nombre, $apellido, $email, $telefono) {
        $sql = "INSERT INTO estudiantes (matricula, cedula, nombre, apellido, email, telefono, fecha_registro)
                VALUES (:matricula, :cedula, :nombre, :apellido, :email, :telefono, NOW())";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            return $stmt->execute();
        } catch (\PDOException $e) {
            
            throw $e;
        }
    }

     public function obtenerTodosLosEstudiantes() {
        $query = "SELECT id, matricula, cedula, nombre, apellido, email, telefono, fecha_registro FROM " . $this->table_name . " ORDER BY fecha_registro DESC";
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Puedes manejar el error o relanzarlo
            echo "Error al obtener estudiantes: " . $e->getMessage();
            return []; // Retorna un array vacío en caso de error
        }
    }

    public function obtenerEstudiantePorId($id) {
        $query = "SELECT id, matricula, cedula, nombre, apellido, email, telefono FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row; // Retorna los datos del estudiante o false si no lo encuentra
    }

    // *** MÉTODO PARA ACTUALIZAR UN ESTUDIANTE ***
    public function actualizarEstudiante($id, $matricula, $cedula, $nombre, $apellido, $email, $telefono) {
        $query = "UPDATE " . $this->table_name . "
                  SET matricula = :matricula,
                      cedula = :cedula,
                      nombre = :nombre,
                      apellido = :apellido,
                      email = :email,
                      telefono = :telefono
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);

        // Limpiar y enlazar parámetros
        $stmt->bindParam(':matricula', htmlspecialchars(strip_tags($matricula)));
        $stmt->bindParam(':cedula', htmlspecialchars(strip_tags($cedula)));
        $stmt->bindParam(':nombre', htmlspecialchars(strip_tags($nombre)));
        $stmt->bindParam(':apellido', htmlspecialchars(strip_tags($apellido)));
        $stmt->bindParam(':email', htmlspecialchars(strip_tags($email)));
        $stmt->bindParam(':telefono', htmlspecialchars(strip_tags($telefono)));
        $stmt->bindParam(':id', $id);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Manejo de errores
            error_log("Error al actualizar estudiante: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarEstudiante($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar estudiante: " . $e->getMessage());
            return false;
        }
    }

}

    