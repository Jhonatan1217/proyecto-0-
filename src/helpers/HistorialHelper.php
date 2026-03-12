<?php
/**
 * Helpers para la vista de Historial (horarios inactivos).
 * Obtienen nombres/descripciones por ID desde tablas relacionadas.
 */

function historial_getNombre(PDO $conn, string $tabla, string $id_col, string $nombre_col, $id): string {
    if ($id === null || $id === '') return '';
    $sql = "SELECT $nombre_col FROM $tabla WHERE $id_col = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (string) $row[$nombre_col] : '';
}

function historial_getNombresMultiple(PDO $conn, string $tabla, string $id_col, string $nombre_col, $ids): string {
    if ($ids === null || $ids === '') return '';
    $parts = array_filter(array_map('trim', explode(',', (string) $ids)), static function ($v) { return $v !== ''; });
    if (!$parts) return '';
    $placeholders = implode(',', array_fill(0, count($parts), '?'));
    $sql = "SELECT $id_col, $nombre_col FROM $tabla WHERE $id_col IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array_values($parts));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $map = [];
    foreach ($rows as $r) {
        $map[(string) $r[$id_col]] = $r[$nombre_col];
    }
    $out = [];
    foreach ($parts as $id) {
        $key = (string) $id;
        $out[] = isset($map[$key]) && $map[$key] !== '' ? $map[$key] : $key;
    }
    return implode(' || ', $out);
}
