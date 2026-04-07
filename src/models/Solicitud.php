<?php
class Solicitud {
    private $conn;
    private $table = "solicitudes";
    private $table_detalle = "solicitudes_detalle";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Función para crear una nueva solicitud CON detalle
    public function crear($tipo_solicitud, $id_instructor_solicitante, $detalles = []) {
        try {
            // Iniciar transacción
            $this->conn->beginTransaction();

            // Insertar la solicitud principal
            $sql = "INSERT INTO {$this->table} 
                    (tipo_solicitud, id_instructor_solicitante, estado) 
                    VALUES (:tipo_solicitud, :id_instructor_solicitante, 'PENDIENTE')";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':tipo_solicitud', $tipo_solicitud);
            $stmt->bindParam(':id_instructor_solicitante', $id_instructor_solicitante, PDO::PARAM_INT);
            $stmt->execute();

            $id_solicitud = $this->conn->lastInsertId();

            // Insertar detalles si existen
            if (!empty($detalles)) {
                $this->insertarDetalles($id_solicitud, $detalles);
            }

            // Confirmar transacción
            $this->conn->commit();

            // Obtener la solicitud completa con detalles
            return [
                "status" => "success", 
                "message" => "Solicitud creada correctamente.",
                "data" => $this->obtenerPorId($id_solicitud)
            ];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ["status" => "error", "message" => "Error al crear solicitud: " . $e->getMessage()];
        }
    }

    // Función para insertar detalles
    private function insertarDetalles($id_solicitud, $detalles) {
        $sql_detalle = "INSERT INTO {$this->table_detalle} 
                        (id_solicitud, campo_modificado, valor_anterior, valor_nuevo) 
                        VALUES (:id_solicitud, :campo_modificado, :valor_anterior, :valor_nuevo)";
        
        $stmt_detalle = $this->conn->prepare($sql_detalle);
        
        foreach ($detalles as $detalle) {
            $campo_modificado = $detalle['campo_modificado'] ?? null;
            $valor_anterior = $detalle['valor_anterior'] ?? null;
            $valor_nuevo = $detalle['valor_nuevo'] ?? null;
            
            if ($campo_modificado && $valor_nuevo) {
                $stmt_detalle->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
                $stmt_detalle->bindParam(':campo_modificado', $campo_modificado);
                $stmt_detalle->bindParam(':valor_anterior', $valor_anterior);
                $stmt_detalle->bindParam(':valor_nuevo', $valor_nuevo);
                $stmt_detalle->execute();
            }
        }
    }

    // Función para responder una solicitud (aprobar o devolver)
    public function responder($id_solicitud, $estado, $observacion_respuesta, $id_coordinador_aprobador) {
        try {
            // Validar que el estado sea válido para respuesta
            if (!in_array($estado, ['APROBADO', 'DEVUELTO'])) {
                return ["status" => "error", "message" => "Estado no válido para respuesta. Use APROBADO o DEVUELTO"];
            }

            // Verificar que la solicitud existe y está pendiente
            $solicitud = $this->obtenerPorId($id_solicitud);
            if (!$solicitud) {
                return ["status" => "error", "message" => "Solicitud no encontrada"];
            }
            
            if ($solicitud['estado'] !== 'PENDIENTE') {
                return ["status" => "error", "message" => "La solicitud ya ha sido respondida anteriormente"];
            }

            // Si la solicitud es de HORARIO y se APRUEBA, aplicar los cambios
            if ($solicitud['tipo_solicitud'] === 'HORARIO' && $estado === 'APROBADO') {
                $this->aplicarCambiosHorario($id_solicitud);
            }

            $sql = "UPDATE {$this->table} 
                    SET estado = :estado, 
                        observacion_respuesta = :observacion_respuesta,
                        fecha_respuesta = CURRENT_TIMESTAMP,
                        id_coordinador_aprobador = :id_coordinador_aprobador
                    WHERE id_solicitud = :id_solicitud";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':observacion_respuesta', $observacion_respuesta);
            $stmt->bindParam(':id_coordinador_aprobador', $id_coordinador_aprobador, PDO::PARAM_INT);
            $stmt->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return [
                    "status" => "success", 
                    "message" => "Solicitud respondida correctamente. Cambios aplicados.", 
                    "data" => $this->obtenerPorId($id_solicitud)
                ];
            } else {
                return ["status" => "warning", "message" => "No se pudo responder la solicitud o no hubo cambios."];
            }
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Error al responder solicitud: " . $e->getMessage()];
        }
    }

    // Función auxiliar para aplicar cambios de horario
    private function aplicarCambiosHorario($id_solicitud) {
        try {
            // Obtener los detalles de la solicitud
            $sql_detalles = "SELECT valor_nuevo FROM {$this->table_detalle} 
                            WHERE id_solicitud = :id_solicitud AND campo_modificado = 'HORARIO'";
            $stmt_detalles = $this->conn->prepare($sql_detalles);
            $stmt_detalles->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            $stmt_detalles->execute();
            
            $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($detalles)) {
                return false;
            }

            // Parsear el texto de cambios
            $texto_cambios = $detalles[0]['valor_nuevo'] ?? '';
            
            if (empty($texto_cambios)) {
                return false;
            }

            // Dividir por bloques de cambios (separados por ID:)
            $cambios_aplicados = 0;
            
            // Expresión regular para extraer: ID, Nuevo Día y horas
            if (preg_match_all('/ID:\s*(\d+)\s*.*?Nuevo\s+Dia:\s*(\w+)\s+(\d{2}):(\d{2})\s*-\s*(\d{2}):(\d{2})/is', $texto_cambios, $matches)) {
                
                for ($i = 0; $i < count($matches[0]); $i++) {
                    $id_horario = $matches[1][$i];
                    $nuevo_dia = strtoupper($matches[2][$i]);
                    $hora_inicio = $matches[3][$i] . ':' . $matches[4][$i];
                    $hora_fin = $matches[5][$i] . ':' . $matches[6][$i];
                    
                    // Actualizar en la tabla de horarios
                    $sql_update = "UPDATE horarios 
                                   SET dia = :dia, 
                                       hora_inicio = :hora_inicio, 
                                       hora_fin = :hora_fin
                                   WHERE id_horario = :id_horario";
                    
                    $stmt_update = $this->conn->prepare($sql_update);
                    $stmt_update->bindParam(':dia', $nuevo_dia);
                    $stmt_update->bindParam(':hora_inicio', $hora_inicio);
                    $stmt_update->bindParam(':hora_fin', $hora_fin);
                    $stmt_update->bindParam(':id_horario', $id_horario, PDO::PARAM_INT);
                    
                    if ($stmt_update->execute()) {
                        $cambios_aplicados++;
                    }
                }
            }
            
            return $cambios_aplicados > 0;
            
        } catch (PDOException $e) {
            error_log("Error al aplicar cambios de horario: " . $e->getMessage());
            return false;
        }
    }

    // Función para actualizar una solicitud y sus detalles
    public function actualizar($id_solicitud, $tipo_solicitud, $detalles = []) {
        try {
            // Verificar que la solicitud está pendiente
            $solicitud = $this->obtenerPorId($id_solicitud);
            if (!$solicitud) {
                return ["status" => "error", "message" => "Solicitud no encontrada"];
            }
            
            if ($solicitud['estado'] !== 'PENDIENTE') {
                return ["status" => "error", "message" => "No se puede actualizar una solicitud que ya fue respondida"];
            }

            // Iniciar transacción
            $this->conn->beginTransaction();

            // Actualizar solicitud principal
            $sql = "UPDATE {$this->table} 
                    SET tipo_solicitud = :tipo_solicitud
                    WHERE id_solicitud = :id_solicitud AND estado = 'PENDIENTE'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':tipo_solicitud', $tipo_solicitud);
            $stmt->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            $stmt->execute();

            // Si hay nuevos detalles, eliminar los anteriores y insertar los nuevos
            if (!empty($detalles)) {
                // Eliminar detalles anteriores
                $sql_delete = "DELETE FROM {$this->table_detalle} WHERE id_solicitud = :id_solicitud";
                $stmt_delete = $this->conn->prepare($sql_delete);
                $stmt_delete->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
                $stmt_delete->execute();

                // Insertar nuevos detalles
                $this->insertarDetalles($id_solicitud, $detalles);
            }

            // Confirmar transacción
            $this->conn->commit();

            return [
                "status" => "success", 
                "message" => "Solicitud actualizada correctamente.", 
                "data" => $this->obtenerPorId($id_solicitud)
            ];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ["status" => "error", "message" => "Error al actualizar solicitud: " . $e->getMessage()];
        }
    }

    // Función para agregar un detalle a una solicitud existente
    public function agregarDetalle($id_solicitud, $campo_modificado, $valor_anterior, $valor_nuevo) {
        try {
            // Verificar que la solicitud existe y está pendiente
            $solicitud = $this->obtenerPorId($id_solicitud);
            if (!$solicitud) {
                return ["status" => "error", "message" => "Solicitud no encontrada"];
            }
            
            if ($solicitud['estado'] !== 'PENDIENTE') {
                return ["status" => "error", "message" => "No se puede agregar detalles a una solicitud que ya fue respondida"];
            }

            $sql = "INSERT INTO {$this->table_detalle} 
                    (id_solicitud, campo_modificado, valor_anterior, valor_nuevo) 
                    VALUES (:id_solicitud, :campo_modificado, :valor_anterior, :valor_nuevo)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            $stmt->bindParam(':campo_modificado', $campo_modificado);
            $stmt->bindParam(':valor_anterior', $valor_anterior);
            $stmt->bindParam(':valor_nuevo', $valor_nuevo);
            $stmt->execute();

            return [
                "status" => "success", 
                "message" => "Detalle agregado correctamente.",
                "data" => $this->obtenerDetalleSolicitud($id_solicitud)
            ];
            
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Error al agregar detalle: " . $e->getMessage()];
        }
    }

    // Función para eliminar un detalle específico
    public function eliminarDetalle($id_detalle) {
        try {
            // Verificar que el detalle pertenece a una solicitud pendiente
            $sql_check = "SELECT sd.id_detalle, s.estado 
                         FROM {$this->table_detalle} sd
                         INNER JOIN {$this->table} s ON sd.id_solicitud = s.id_solicitud
                         WHERE sd.id_detalle = :id_detalle";
            
            $stmt_check = $this->conn->prepare($sql_check);
            $stmt_check->bindParam(':id_detalle', $id_detalle, PDO::PARAM_INT);
            $stmt_check->execute();
            $result = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return ["status" => "error", "message" => "Detalle no encontrado"];
            }

            if ($result['estado'] !== 'PENDIENTE') {
                return ["status" => "error", "message" => "No se puede eliminar detalles de una solicitud que ya fue respondida"];
            }

            $sql = "DELETE FROM {$this->table_detalle} WHERE id_detalle = :id_detalle";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_detalle', $id_detalle, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ["status" => "success", "message" => "Detalle eliminado correctamente."];
            } else {
                return ["status" => "warning", "message" => "No se encontró el detalle."];
            }
        } catch (PDOException $e) {
            return ["status" => "error", "message" => "Error al eliminar detalle: " . $e->getMessage()];
        }
    }

    // Función para eliminar una solicitud (solo si está pendiente)
    public function eliminar($id_solicitud) {
        try {
            // Verificar que la solicitud está pendiente
            $solicitud = $this->obtenerPorId($id_solicitud);
            if (!$solicitud) {
                return ["status" => "error", "message" => "Solicitud no encontrada"];
            }
            
            if ($solicitud['estado'] !== 'PENDIENTE') {
                return ["status" => "error", "message" => "No se puede eliminar una solicitud que ya fue respondida"];
            }

            // Iniciar transacción
            $this->conn->beginTransaction();

            // Eliminar detalles primero (por la FK)
            $sql_delete_detalles = "DELETE FROM {$this->table_detalle} WHERE id_solicitud = :id_solicitud";
            $stmt_detalles = $this->conn->prepare($sql_delete_detalles);
            $stmt_detalles->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            $stmt_detalles->execute();

            // Eliminar solicitud principal
            $sql = "DELETE FROM {$this->table} WHERE id_solicitud = :id_solicitud";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            $stmt->execute();

            // Confirmar transacción
            $this->conn->commit();

            return ["status" => "success", "message" => "Solicitud eliminada correctamente."];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ["status" => "error", "message" => "Error al eliminar solicitud: " . $e->getMessage()];
        }
    }

    // Función para listar todas las solicitudes con información de usuarios
    public function listar() {
        try {
            $sql = "SELECT 
                        s.id_solicitud,
                        s.codigo_solicitud,
                        s.tipo_solicitud,
                        s.estado,
                        DATE_FORMAT(s.fecha_solicitud, '%Y-%m-%d %H:%i:%s') as fecha_solicitud,
                        DATE_FORMAT(s.fecha_respuesta, '%Y-%m-%d %H:%i:%s') as fecha_respuesta,
                        s.observacion_respuesta,
                        s.id_instructor_solicitante,
                        instructor.nombre_completo as nombre_instructor,
                        instructor.correo_electronico as correo_instructor,
                        s.id_coordinador_aprobador,
                        coordinador.nombre_completo as nombre_coordinador
                    FROM {$this->table} s
                    LEFT JOIN usuarios instructor ON s.id_instructor_solicitante = instructor.id_usuario
                    LEFT JOIN usuarios coordinador ON s.id_coordinador_aprobador = coordinador.id_usuario
                    ORDER BY s.fecha_solicitud DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agregar detalles a cada solicitud
            foreach ($solicitudes as &$sol) {
                $sol['detalles'] = $this->obtenerDetalleSolicitud($sol['id_solicitud']);
            }

            return $solicitudes;
        } catch (PDOException $e) {
            return [];
        }
    }

    // Función para listar solicitudes por estado
    public function listarPorEstado($estado) {
        try {
            if (!in_array($estado, ['PENDIENTE', 'APROBADO', 'DEVUELTO'])) {
                return ["status" => "error", "message" => "Estado no válido"];
            }

            $sql = "SELECT 
                        s.id_solicitud,
                        s.codigo_solicitud,
                        s.tipo_solicitud,
                        s.estado,
                        DATE_FORMAT(s.fecha_solicitud, '%Y-%m-%d %H:%i:%s') as fecha_solicitud,
                        DATE_FORMAT(s.fecha_respuesta, '%Y-%m-%d %H:%i:%s') as fecha_respuesta,
                        s.observacion_respuesta,
                        s.id_instructor_solicitante,
                        instructor.nombre_completo as nombre_instructor,
                        instructor.correo_electronico as correo_instructor,
                        s.id_coordinador_aprobador,
                        coordinador.nombre_completo as nombre_coordinador
                    FROM {$this->table} s
                    LEFT JOIN usuarios instructor ON s.id_instructor_solicitante = instructor.id_usuario
                    LEFT JOIN usuarios coordinador ON s.id_coordinador_aprobador = coordinador.id_usuario
                    WHERE s.estado = :estado
                    ORDER BY s.fecha_solicitud DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();
            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agregar detalles a cada solicitud
            foreach ($solicitudes as &$sol) {
                $sol['detalles'] = $this->obtenerDetalleSolicitud($sol['id_solicitud']);
            }

            return $solicitudes;
        } catch (PDOException $e) {
            return [];
        }
    }

    // Función para listar solicitudes por instructor
    public function listarPorInstructor($id_instructor) {
        try {
            $sql = "SELECT 
                        s.id_solicitud,
                        s.codigo_solicitud,
                        s.tipo_solicitud,
                        s.estado,
                        DATE_FORMAT(s.fecha_solicitud, '%Y-%m-%d %H:%i:%s') as fecha_solicitud,
                        DATE_FORMAT(s.fecha_respuesta, '%Y-%m-%d %H:%i:%s') as fecha_respuesta,
                        s.observacion_respuesta,
                        s.id_instructor_solicitante,
                        instructor.nombre_completo as nombre_instructor,
                        s.id_coordinador_aprobador,
                        coordinador.nombre_completo as nombre_coordinador
                    FROM {$this->table} s
                    LEFT JOIN usuarios instructor ON s.id_instructor_solicitante = instructor.id_usuario
                    LEFT JOIN usuarios coordinador ON s.id_coordinador_aprobador = coordinador.id_usuario
                    WHERE s.id_instructor_solicitante = :id_instructor
                    ORDER BY s.fecha_solicitud DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_instructor', $id_instructor, PDO::PARAM_INT);
            $stmt->execute();
            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agregar detalles a cada solicitud
            foreach ($solicitudes as &$sol) {
                $sol['detalles'] = $this->obtenerDetalleSolicitud($sol['id_solicitud']);
            }

            return $solicitudes;
        } catch (PDOException $e) {
            return [];
        }
    }

    // Función para obtener una solicitud por ID con sus detalles
    public function obtenerPorId($id_solicitud) {
        try {
            $sql = "SELECT 
                        s.id_solicitud,
                        s.codigo_solicitud,
                        s.tipo_solicitud,
                        s.estado,
                        DATE_FORMAT(s.fecha_solicitud, '%Y-%m-%d %H:%i:%s') as fecha_solicitud,
                        DATE_FORMAT(s.fecha_respuesta, '%Y-%m-%d %H:%i:%s') as fecha_respuesta,
                        s.observacion_respuesta,
                        s.id_instructor_solicitante,
                        instructor.nombre_completo as nombre_instructor,
                        instructor.correo_electronico as correo_instructor,
                        instructor.numero_documento as documento_instructor,
                        s.id_coordinador_aprobador,
                        coordinador.nombre_completo as nombre_coordinador
                    FROM {$this->table} s
                    LEFT JOIN usuarios instructor ON s.id_instructor_solicitante = instructor.id_usuario
                    LEFT JOIN usuarios coordinador ON s.id_coordinador_aprobador = coordinador.id_usuario
                    WHERE s.id_solicitud = :id_solicitud";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            $stmt->execute();

            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($solicitud) {
                $solicitud['detalles'] = $this->obtenerDetalleSolicitud($id_solicitud);
            }

            return $solicitud;
        } catch (PDOException $e) {
            return null;
        }
    }

    // Función para obtener los detalles de una solicitud
    public function obtenerDetalleSolicitud($id_solicitud) {
        try {
            $sql = "SELECT 
                        id_detalle,
                        campo_modificado,
                        valor_anterior,
                        valor_nuevo
                    FROM {$this->table_detalle}
                    WHERE id_solicitud = :id_solicitud
                    ORDER BY id_detalle ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Función para contar solicitudes por estado (estadísticas)
    public function contarPorEstado() {
        try {
            $sql = "SELECT 
                        SUM(CASE WHEN estado = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN estado = 'APROBADO' THEN 1 ELSE 0 END) as aprobadas,
                        SUM(CASE WHEN estado = 'DEVUELTO' THEN 1 ELSE 0 END) as devueltas,
                        COUNT(*) as total
                    FROM {$this->table}";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'pendientes' => (int)$result['pendientes'],
                'aprobadas' => (int)$result['aprobadas'],
                'devueltas' => (int)$result['devueltas'],
                'total' => (int)$result['total']
            ];
        } catch (PDOException $e) {
            return ['pendientes' => 0, 'aprobadas' => 0, 'devueltas' => 0, 'total' => 0];
        }
    }

    // Métodos de conveniencia para listar por estado específico
    public function listarPendientes() {
        return $this->listarPorEstado('PENDIENTE');
    }

    public function listarAprobadas() {
        return $this->listarPorEstado('APROBADO');
    }

    public function listarDevueltas() {
        return $this->listarPorEstado('DEVUELTO');
    }
}
?>