<?php
namespace App\Models;

use ReturnTypeWillChange;

class Docente {
    private $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function crearDocente($cedula, $nombre, $apellido, $email, $telefono) {
        $stmt = $this->db->prepare("INSERT INTO docentes (cedula, nombre, apellido, email, telefono) VALUES (:cedula, :nombre, :apellido, :email, :telefono)");
        $stmt ->bindParam(':cedula', $cedula);
        $stmt ->bindParam(':nombre', $nombre);
        $stmt ->bindParam(':apellido', $apellido);
        $stmt ->bindParam(':email', $email);
        $stmt ->bindParam(':telefono', $telefono);
        return $stmt->execute() ?  $this ->db->lastInsertId() : false;
    }

    public function obtenerTodosLosDocentes() {
        $sql = "SELECT * FROM docentes";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    } 
    
    public function registrarDocente($cedula, $nombre, $apellido, $email, $telefono) {
        $sql = "INSERT INTO docentes (cedula, nombre, apellido, email, telefono, fecha_registro) VALUES (:cedula, :nombre, :apellido, :email, :telefono, NOW())";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function eliminarDocente($id) {
        $sql = "DELETE FROM docentes WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function obtenerDocentePorId($id) {
        $sql = "SELECT * FROM docentes WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function actualizarDocente($id, $cedula, $nombre, $apellido, $email, $telefono) {
        $sql = "UPDATE docentes SET cedula = :cedula, nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }
}