<?php

namespace App\Models;

use PDO;

class Usuario {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function crearUsuario(string $nombre, string $cedula, string $cargo, string $rol, string $contrasena): bool {
        // 1. Validar datos (puedes añadir más validaciones si es necesario)
        if (empty($nombre) || empty($cedula) || empty($cargo) || empty($rol) || empty($contrasena)) {
            return false; // O podrías lanzar una excepción con un mensaje más específico
        }

        // 2. Hashear la contraseña de forma segura
        $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

        try {
            // 3. Intentar insertar el nuevo usuario en la base de datos
            $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, cedula, cargo, rol, password) VALUES (:nombre, :cedula, :cargo, :rol, :password)");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':cargo', $cargo);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':password', $contrasenaHash);
            $stmt->execute();

            // 4. Devolver true si la inserción fue exitosa
            return true;

        } catch (PDOException $e) {
            // 5. Manejar posibles errores (ej: cédula duplicada)
            if ($e->getCode() == '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'cedula') !== false) {
                // Error de clave duplicada en la columna 'cedula'
                return false; // O podrías lanzar una excepción específica para este caso
            } else {
                // Otro error de base de datos (loggear o mostrar un mensaje genérico)
                error_log("Error al crear usuario: " . $e->getMessage());
                return false;
            }
        }
    }

    public function obtenerUsuarioPorCedula(string $cedula): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE cedula = :cedula");
            $stmt->bindParam('cedula', $cedula);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            return $usuario ?: null; // Retorna null si no se encuentra el usuario

        } catch (PDOException $e) {
            error_log("Error al obtener usuario por cedula: " . $e->getMessage());
            return null; // O podrías lanzar una excepción
        
        }
    }
}