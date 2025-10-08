<?php

require_once __DIR__ . '/../helpers/InstructorHelper.php';
require_once __DIR__ . '/../helpers/CompetenciaHelper.php';
require_once __DIR__ . '/../helpers/HorarioHelper.php';
require_once __DIR__ . '/../helpers/ZonaHelper.php';
require_once __DIR__ . '/../models/Instructor.php';
require_once __DIR__ . '/../models/Competencia.php';
require_once __DIR__ . '/../models/Horario.php';
require_once __DIR__ . '/../models/Ficha.php';
require_once __DIR__ . '/../models/Zona.php';
require_once __DIR__ . '/../../config/database.php';

// Ejemplo de uso del main
echo "<h1>Panel Principal</h1>";

// Mostrar instructores
echo "<h2>Instructores</h2>";
$instructores = getInstructores($conn);
echo "<ul>";
foreach ($instructores as $instructor) {
    echo "<li>{$instructor['nombre']} ({$instructor['tipo_instructor']})</li>";
}
echo "</ul>";

// Mostrar competencias
echo "<h2>Competencias</h2>";
$competencias = getCompetencias($conn);
echo "<ul>";
foreach ($competencias as $competencia) {
    echo "<li>{$competencia['nombre_competencia']} ({$competencia['tipo_competencia']})</li>";
}
echo "</ul>";

// Mostrar zonas
echo "<h2>Zonas</h2>";
$zonas = getZonas();
echo "<ul>";
foreach ($zonas as $id => $nombre) {
    echo "<li>$nombre</li>";
}
echo "</ul>";

// Mostrar horarios de ejemplo para una ficha
echo "<h2>Horarios de Ficha (Ejemplo)</h2>";
$idFicha = 3172293;
$horariosFicha = getHorarioFicha($conn, $idFicha);
if ($horariosFicha) {
    echo "<table border='1'><tr><th>Día</th><th>Hora Inicio</th><th>Hora Fin</th></tr>";
    foreach ($horariosFicha as $horario) {
        echo "<tr><td>{$horario['dia']}</td><td>{$horario['hora_inicio']}</td><td>{$horario['hora_fin']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "No hay horarios para la ficha.";
}

?>
