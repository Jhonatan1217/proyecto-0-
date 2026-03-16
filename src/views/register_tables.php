<?php

require_once __DIR__ . '/../../config/database.php';

$areas        = [];
$zonas        = [];
$instructores = [];
$trimestres   = [];
$programas    = [];
$competencias = [];
$grupos       = [];
$isAuthenticated = isset($_SESSION['usuario_id']);

if (isset($conn)) {
  try {
    $s = $conn->prepare("SELECT id_area, nombre_area FROM areas WHERE estado = 1 ORDER BY nombre_area ASC");
    $s->execute();
    $areas = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    try {
      $s = $conn->prepare("SELECT id_area, nombre_area FROM area WHERE estado = 1 ORDER BY nombre_area ASC");
      $s->execute();
      $areas = $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
      $areas = [];
    }
  }

  try {
    $s = $conn->prepare("SELECT id_zona, id_area FROM zonas WHERE estado = 1 ORDER BY id_zona ASC");
    $s->execute();
    $zonas = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $zonas = [];
  }

  try {
    $s = $conn->prepare("SELECT id_instructor, nombre_instructor, tipo_instructor FROM instructores WHERE estado = 1 ORDER BY nombre_instructor ASC");
    $s->execute();
    $instructores = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    try {
      $s = $conn->prepare("SELECT id_usuario AS id_instructor, nombre_completo AS nombre_instructor, tipo_instructor FROM usuarios WHERE cargo = 'INSTRUCTOR' AND estado = 1 ORDER BY nombre_completo ASC");
      $s->execute();
      $instructores = $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
      $instructores = [];
    }
  }

  try {
    $s = $conn->prepare("SELECT numero_trimestre FROM trimestre WHERE estado = 1 ORDER BY numero_trimestre ASC");
    $s->execute();
    $trimestres = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    try {
      $s = $conn->prepare("SELECT numero_trimestre FROM trimestres WHERE estado = 1 ORDER BY numero_trimestre ASC");
      $s->execute();
      $trimestres = $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
      $trimestres = [];
    }
  }

  try {
    $s = $conn->prepare("SELECT id_programa, nombre_programa FROM programas WHERE estado = 1 ORDER BY nombre_programa ASC");
    $s->execute();
    $programas = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $programas = [];
  }
  if (empty($programas)) {
    try {
      $s = $conn->prepare("SELECT id_programa, nombre_programa FROM programas ORDER BY nombre_programa ASC");
      $s->execute();
      $programas = $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $programas = [];
    }
  }

  try {
    $s = $conn->prepare("SELECT id_competencia, nombre_competencia, id_programa FROM competencias WHERE estado = 1 ORDER BY nombre_competencia ASC");
    $s->execute();
    $competencias = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $competencias = [];
  }
  if (empty($competencias)) {
    try {
      $s = $conn->prepare("SELECT id_competencia, nombre_competencia, id_programa FROM competencias ORDER BY nombre_competencia ASC");
      $s->execute();
      $competencias = $s->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $competencias = [];
    }
  }

  try {
    $s = $conn->prepare("SELECT DISTINCT numero_ficha FROM fichas WHERE numero_ficha IS NOT NULL AND numero_ficha <> '' ORDER BY numero_ficha ASC");
    $s->execute();
    $grupos = $s->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $grupos = [];
  }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proyecto 0 - Visualización de registro de tablas</title>

  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/output.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/css/fonts.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/formulario_crear_trimestralizacion.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/register_tables.css">
  <script src="<?= BASE_URL ?>src/assets/js/sweetalert2.all.min.js"></script>

  <style>
  #modalCard .form-grid {
    display: flex;
    flex-direction: column;
  }

  #id_competencia {
    display: block;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  #id_competencia option {
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Evita doble flecha (nativa + custom) en los combobox superiores */
  #selectModalidad,
  #selectArea,
  #selectZona {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: none !important;
  }

  #selectModalidad::-ms-expand,
  #selectArea::-ms-expand,
  #selectZona::-ms-expand {
    display: none;
  }

  .custom-combobox {
    position: relative;
  }

  .custom-combobox-panel {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    z-index: 80;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05);
    overflow: hidden;
  }

  .custom-combobox-panel.hidden {
    display: none;
  }

  .custom-combobox-list {
    max-height: 240px;
    overflow-y: auto;
    padding: 4px;
  }

  .custom-combobox-list::-webkit-scrollbar {
    width: 8px;
  }

  .custom-combobox-list::-webkit-scrollbar-thumb {
    background: #9ca3af;
    border-radius: 999px;
  }

  .custom-combobox-option,
  .custom-combobox-empty {
    width: 100%;
    border: 0;
    background: transparent;
    text-align: left;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.875rem;
    color: #1f2937;
  }

  .custom-combobox-option {
    cursor: pointer;
    transition: background-color 0.18s ease, color 0.18s ease;
  }

  .custom-combobox-option:hover,
  .custom-combobox-option.is-active {
    background: #f3f4f6;
    color: #111827;
  }

  .custom-combobox-empty {
    color: #6b7280;
    cursor: default;
  }

  /* ===== MODAL GESTIÓN DE HORAS ===== */
  .gh-backdrop {
    background: rgba(15, 23, 42, 0.45);
  }

  .gh-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(15, 23, 42, 0.18);
    border: 1px solid #e2e8f0;
  }

  /* Header */
  .gh-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 14px;
    border-bottom: 1px solid #e5e7eb;
  }

  .gh-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
  }

  .gh-close {
    background: none;
    border: none;
    font-size: 1.4rem;
    color: #6b7280;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    line-height: 1;
    transition: color 0.15s;
  }

  .gh-close:hover { color: #111827; }

  /* Tabs */
  .gh-tabs {
    display: flex;
    gap: 8px;
    border: 1.5px dashed #c4cdd6;
    border-radius: 10px;
    padding: 8px 10px;
    margin: 14px 0;
  }

  .gh-tab {
    padding: 6px 20px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all 0.15s;
    background: transparent;
    color: #374151;
  }

  .gh-tab.is-active {
    background: #39a900;
    color: #fff;
    border-color: #39a900;
  }

  .gh-tab:not(.is-active) {
    border-color: #d1d5db;
    color: #374151;
  }

  /* Resumen simple */
  .gh-resumen {
    margin-bottom: 10px;
  }

  .gh-resumen-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 2px 0;
  }

  .gh-resumen-sub {
    font-size: 0.8rem;
    color: #6b7280;
    margin: 0;
  }

  /* Filtros */
  .gh-filtros {
    display: flex;
    gap: 10px;
    border: 1.5px dashed #c4cdd6;
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 14px;
  }

  .gh-filtros-input,
  .gh-filtros-select {
    flex: 1;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.875rem;
    color: #374151;
    background: #fff;
    outline: none;
    transition: border-color 0.15s;
  }

  .gh-filtros-input:focus,
  .gh-filtros-select:focus {
    border-color: #39a900;
  }

  .gh-filtros-select {
    max-width: 220px;
  }

  /* Tabla */
  .gh-table-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 340px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
  }

  .gh-table-wrapper::-webkit-scrollbar { width: 6px; height: 6px; }
  .gh-table-wrapper::-webkit-scrollbar-thumb { background: #c4cdd6; border-radius: 99px; }

  .gh-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
    white-space: nowrap;
  }

  .gh-table thead th {
    background: #f9fafb;
    color: #374151;
    font-weight: 600;
    text-align: left;
    padding: 10px 14px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 0.82rem;
  }

  .gh-table thead th.center { text-align: center; }

  .gh-table tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: background 0.1s;
  }

  .gh-table tbody tr:hover { background: #f9fafb; }
  .gh-table tbody tr:last-child { border-bottom: none; }

  .gh-table tbody td {
    padding: 11px 14px;
    color: #374151;
    font-size: 0.875rem;
  }

  .gh-table tbody td.center { text-align: center; }

  .gh-excedente-neg {
    color: #dc2626;
    font-weight: 600;
  }

  .gh-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: 1px solid #d1d5db;
    border-radius: 7px;
    background: #fff;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s;
  }

  .gh-action-btn:hover {
    border-color: #39a900;
    color: #39a900;
  }

  /* Footer */
  .gh-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 16px;
  }

  /* ===== TABLA HORARIO (ESTILO REFERENCIA) ===== */
  #tabla-horarios {
    border: 1px solid #b6b6b6;
    border-radius: 10px;
    overflow: hidden;
    background: #ededed;
  }

  #tabla-horarios table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    background: #ededed;
  }

  #tabla-horarios thead th {
    background: #9ea0a3 !important;
    color: #f3f4f6 !important;
    border: 1px solid #b6b6b6 !important;
    font-weight: 700;
    font-size: 14px;
    padding: 10px 8px !important;
    text-align: center;
  }

  #tabla-horarios tbody td {
    background: #ffffff;
    border: 1px solid #c0c0c0;
    color: #5f5f5f;
    font-size: 13px;
    vertical-align: top;
  }

  #tabla-horarios tbody td.hora-col {
    background: #e0e0e0 !important;
    color: #5a5a5a !important;
    font-weight: 700;
    text-align: center;
    white-space: nowrap;
  }

  #tabla-horarios .registro {
    background: #dce8f4 !important;
    border: 1px solid #c5d3e3;
    border-left: 3px solid #39a900 !important;
    border-radius: 0.45rem;
    box-shadow: none !important;
    margin-bottom: 4px !important;
    padding: 6px 7px !important;
  }

  #tabla-horarios .zona-libre {
    background: #ffffff !important;
  }

  #tabla-horarios .zona-libre:hover {
    background: #f5f7fa !important;
  }

  @media (max-width: 600px) {
    .gh-filtros { flex-direction: column; }
    .gh-filtros-select { max-width: 100%; }
    .gh-footer { flex-direction: column; }
  }
  </style>
</head>

<body class="text-center font-sans min-h-screen flex flex-col bg-gray-50 overflow-x-hidden">

<header class="mt-10 text-center px-4 sm:px-8" id="cabecera-trimestralizacion">
  <h1 class="inline-block text-2xl sm:text-3xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">
    VISUALIZACIÓN DE REGISTRO TRIMESTRALIZACIÓN - ZONA 
    <?php echo isset($_GET['id_zona']) ? htmlspecialchars($_GET['id_zona']) : '—'; ?>
  </h1>
  <h2 class="text-lg sm:text-xl font-semibold text-gray-700 tracking-wide mb-6">
    Sistema de gestión de trimestralización <br> SENA
  </h2>

  <!-- Contenedor principal de selects y botón -->
  <div class="flex flex-col md:flex-row justify-between items-center md:items-end w-full px-4 sm:px-8 lg:px-16 my-6 gap-4 md:gap-6">
    
    <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 w-full md:w-auto">


    <!-- Selector de Modalidad -->
      <div class="relative w-full sm:w-auto">
        <label for="selectModalidad" class="block mb-2 text-sm sm:text-base font-semibold text-[#00324D] tracking-wide text-left">Seleccione la Modalidad</label>
        <div class="relative">
          <select id="selectModalidad" name="id_modalidad"
            class="appearance-none w-full sm:w-64 lg:w-72 xl:w-80 2xl:w-96 px-4 py-2.5 border border-gray-300 text-sm 
            rounded-xl
            text-gray-700
            bg-white
            focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] focus:outline-none
            transition-all duration-200 cursor-pointer pr-10">
            <option value="" class="text-[#00324D]" selected hidden>SELECCIONE LA MODALIDAD</option>
            <option value="presencial"> Presencial </option>
            <option value="virtual"> Virtual </option>
            <option value="mixto"> Mixta </option>
          </select>
<<<<<<< HEAD
          <img 
            src="<?= BASE_URL ?>src/assets/img/icons/Acction/chevron-down.svg" 
            alt="arrow" 
            class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 opacity-70"
          />
=======
          <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
>>>>>>> develop
        </div>
      </div>



  
      <!-- Selector de Área -->
      <div class="relative w-full sm:w-auto">
        <label for="selectArea" class="block mb-2 text-sm sm:text-base font-semibold text-[#00324D] tracking-wide text-left">Seleccione el Área</label>
        <div class="relative">
          <select id="selectArea" name="id_area"
            class="appearance-none w-full sm:w-64 lg:w-72 xl:w-80 2xl:w-96 px-4 py-2.5 border border-gray-300 text-sm 
            rounded-xl
            text-gray-700
            bg-white
            focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] focus:outline-none
            transition-all duration-200 cursor-pointer pr-10">
            <option value="" class="text-[#00324D]" selected hidden>SELECCIONE EL ÁREA</option>
          </select>
          <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>




      <!-- Selector de Zona -->
      <div class="relative w-full sm:w-auto">
        <label for="selectZona" class="block mb-2 text-sm sm:text-base font-semibold text-[#00324D] tracking-wide text-left">Seleccione la Zona</label>
        <div class="relative">
          <select id="selectZona" name="id_zona"
            class="appearance-none w-full sm:w-64 lg:w-72 xl:w-80 2xl:w-96 px-4 py-2.5 border border-gray-300 text-sm 
            rounded-xl
            text-gray-700
            bg-white
            focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] focus:outline-none
            transition-all duration-200 cursor-pointer pr-10">
            <option value="" class="text-[#00324D]" selected hidden>SELECCIONE LA ZONA</option>
          </select>
          <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>
    </div>




    <!-- Input para filtrar por grupo -->
    <div id="contenedorGrupoFiltro" class="relative w-full sm:w-auto hidden">
      <label for="inputGrupoTexto" class="block mb-2 text-sm sm:text-base font-semibold text-[#00324D] tracking-wide text-left">Filtrar por grupo</label>
      <div class="custom-combobox w-full sm:w-64 lg:w-72 xl:w-80 2xl:w-96">
        <input type="text" id="inputGrupoTexto" autocomplete="off" placeholder="Seleccione o escriba número de grupo" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 bg-white focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] focus:outline-none transition-all duration-200">
        <div id="panelGrupoFiltro" class="custom-combobox-panel hidden">
          <div class="custom-combobox-list"></div>
        </div>
      </div>
    </div>



    <!-- Botón de crear nueva trimestralización (abre el mismo modal que en la landing) -->
    <button id="btnAbrirModal" 
      class="group flex items-center justify-center gap-2
         w-full sm:w-60 lg:w-72 xl:w-80 2xl:w-96
         px-6 py-2.5
         rounded-xl
         bg-[#00324d]
         text-white font-medium tracking-wider
         text-sm sm:text-base
         shadow-md hover:shadow-lg
         hover:bg-[#00304D]
         active:scale-[0.98]
         transition-all duration-200
         <?= $isAuthenticated ? '' : 'hidden' ?>
         focus:outline-none focus:ring-2 focus:ring-[#00324d]/20">
        <img class="w-5 h-5" src="<?= BASE_URL ?>src/assets/img/plus.svg" />
      Nueva trimestralización
    </button>
  </div>
</header>




  <!-- Contenido principal -->
  <main class="flex flex-col items-center flex-grow px-2 sm:px-4 pb-6">
    <section id="tabla-horarios"
  class="w-full shadow-xl rounded-2xl border border-gray-200 p-0 overflow-hidden">
      <table class="w-full text-xs min-w-[900px] sm:text-sm border-separate border-spacing-0">
        <colgroup>
          <col style="width: 130px;">
          <col style="width: calc((100% - 130px)/6);">
          <col style="width: calc((100% - 130px)/6);">
          <col style="width: calc((100% - 130px)/6);">
          <col style="width: calc((100% - 130px)/6);">
          <col style="width: calc((100% - 130px)/6);">
          <col style="width: calc((100% - 130px)/6);">
        </colgroup>
        <thead class="sticky top-0 bg-gray-300 text-gray-700 z-10">
          <tr>
            <th class="border border-gray-400 p-3 sm:p-4 font-semibold text-sm">Hora</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Lunes</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Martes</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Miércoles</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Jueves</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Viernes</th>
            <th class="border border-gray-400 p-3 font-semibold text-sm">Sábado</th>
          </tr>
        </thead>
        <tbody id="tbody-horarios">
          <tr><td colspan="7" class="p-4 text-gray-500 text-center">Cargando datos...</td></tr>
        </tbody>
      </table>
    </section>

    <div id="empty-state" class="hidden w-full py-16">
      <div class="mx-auto flex max-w-3xl flex-col items-center justify-center text-center">
        <svg
          width="120"
          height="120"
          viewBox="0 0 24 24"
          fill="none"
          class="mb-4 block text-[#00324D] mx-auto"
          xmlns="http://www.w3.org/2000/svg">
          <rect x="3" y="4" width="18" height="17" rx="2" ry="2" stroke="currentColor" stroke-width="1.5"/>
          <line x1="16" y1="2.5" x2="16" y2="5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="8" y1="2.5" x2="8" y2="5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="3" y1="9" x2="21" y2="9" stroke="currentColor" stroke-width="1.5"/>
          <path d="M9 14l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">
          Seleccione un horario
        </h3>
        <p class="text-sm sm:text-base text-gray-500 max-w-md">
          Elige la modalidad y completa los filtros
          correspondientes para ver el horario.
        </p>
      </div>
    </div> 


    <!-- Botones de acciones -->
    <div id="botones-principales" 
  class="mt-6 mb-6 flex flex-col sm:flex-row flex-wrap justify-end items-stretch gap-3 sm:gap-6 w-full px-2">

      <button onclick="descargarPDF()" 
        class="border border-black bg-white text-black px-6 py-2 rounded-lg transition flex items-center justify-center gap-2 w-full sm:w-auto hover:bg-gray-100 hover:border-gray-800"   style="border: 2px solid #333333c5 !important;">
        <img src="<?= BASE_URL ?>src/assets/img/descargar.png" class="w-5 h-5" alt="descargar" style="filter: brightness(0);">
        Descargar PDF
      </button>

      <?php if ($isAuthenticated): ?>
      <button id="btn-actualizar" class="bg-[#39a900] text-white px-6 py-2 rounded-lg hover:bg-[#4ebe15] transition flex items-center justify-center w-full sm:w-auto">
        Gestionar horas
      </button>

      <button onclick="mostrarModalEliminar()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition flex items-center justify-center w-full sm:w-auto">
        Limpiar Trimestralización
      </button>

      <button onclick="enviarHorario()" class="bg-[#0a3a57] text-white px-6 py-2 rounded-lg hover:bg-[#00304D] transition flex items-center justify-center w-full sm:w-auto">
        Enviar horario
      </button>
      <?php endif; ?>
      

      
    </div>
  </main>

  <!-- Modal Eliminar -->
  <div id="modalEliminar" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-3xl shadow-2xl p-6 sm:p-8 max-w-2xl w-full sm:w-11/12 max-h-[90vh] overflow-y-auto">
      <div class="flex justify-center mb-4">
        <img class="w-10 h-10" src="<?= BASE_URL ?>src/assets/img/triangle-alert.svg" />
      </div>
      <h2 class="text-xl sm:text-2xl font-bold text-center mb-8 text-gray-900">
        Limpiar datos de trimestralizacion
      </h2>
      <p class="text-center mb-8">Esta acción limpiará permanentemente los datos de este día. No podrás recuperarlo después.</p>
      <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center">
        <button onclick="cerrarModal()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition flex items-center justify-center w-full sm:w-auto">
          Cancelar
        </button>
        <button onclick="confirmarEliminar()" class="bg-[#39a900] text-white px-6 py-2 rounded-lg hover:bg-[#4ebe15] transition flex items-center justify-center w-full sm:w-auto">
          Aceptar
        </button>
      </div>
    </div>
  </div>

  <div id="modalGestionHoras" class="hidden fixed inset-0 z-[70] items-center justify-center px-4 py-6">
    <div class="gh-backdrop absolute inset-0"></div>
    <div class="gh-card relative z-10 w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">

      <!-- Header -->
      <div class="gh-header">
        <h2 class="gh-title">Gestión de horas</h2>
        <button type="button" id="btnCerrarGestionHoras" class="gh-close" aria-label="Cerrar modal">×</button>
      </div>

      <!-- Tabs -->
      <div class="gh-tabs">
        <button type="button" id="tabGestionHorasInstructores" class="gh-tab is-active">Instructores</button>
        <button type="button" id="tabGestionHorasGrupos" class="gh-tab">Grupos</button>
      </div>

      <!-- Resumen (texto simple) -->
      <div id="gestionHorasResumen" class="gh-resumen"></div>

      <!-- Filtros -->
      <div id="gestionHorasFiltros" class="gh-filtros"></div>

      <!-- Tabla -->
      <div class="gh-table-wrapper">
        <table class="gh-table">
          <thead id="gestionHorasHead"></thead>
          <tbody id="gestionHorasBody"></tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="gh-footer">
        <button type="button" id="btnIrGestionInstructores" class="rounded-lg border border-gray-400 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">Gestionar instructores</button>
        <button type="button" id="btnAceptarGestionHoras" class="rounded-lg bg-[#00324d] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#00263b] transition">Aceptar</button>
      </div>
    </div>
  </div>

  <script>
    window.BASE_URL = window.BASE_URL || "<?= BASE_URL ?>";
    window.IS_AUTHENTICATED = <?= $isAuthenticated ? 'true' : 'false' ?>;
  </script>

  <!-- Scripts de la vista de tablas (SOLO una vez registerTables.js para evitar el error de urlParams) -->
  <script src="<?= BASE_URL ?>src/assets/js/registerTables.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/html2canvas.min.js"></script>
  <script src="<?= BASE_URL ?>src/assets/js/jspdf.umd.min.js"></script>




  <!-- ============== MODAL CREAR TRIMESTRALIZACIÓN (Mismo que en la landing) ============== -->
  <div id="modalCrearLanding" class="fixed inset-0 z-40 hidden" role="dialog" aria-modal="true" aria-labelledby="tituloModalCrear">
      <!-- Backdrop -->
      <div id="modalBackdrop" class="fixed inset-0 bg-black/40"></div>

      <!-- Contenedor centrado -->
      <div class="fixed inset-0 flex items-center justify-center p-4 z-50">
        <div
      id="modalCard"
      class="bg-white !w-[480px] rounded-xl shadow-lg border border-gray-300 px-5 py-4 max-h-[90vh] overflow-y-auto" style="width: 480px !important; max-width: 480px !important;">
          <!-- Cabecera con botón cerrar -->
          <div class="flex items-start justify-between mb-2">
            <h2 id="tituloModalCrear" class="text-center w-full text-lg mb-0 text-black font-semibold">
              Crear trimestralización
            </h2>
            <button id="btnCerrarModal" class="ml-3 -mt-2 text-gray-500 hover:text-gray-700" aria-label="Cerrar modal" title="Cerrar" type="button" data-close="true">
              ✕
            </button>
          </div>
          <div class="border-b border-gray-300 mb-3"></div>

          <!-- Formulario -->
          <form id="formTrimestralizacion" action="<?= BASE_URL ?>src/controllers/TrimestralizacionController.php?accion=crear" method="POST" autocomplete="off" class="trimestralizacion-form space-y-0 text-xs">
            
            <!-- GRID -->
            <div class="form-grid">


            <!-- PROGRAMA -->
              <div class="field">
                <label for="id_programa_select" class="block text-xs font-semibold text-gray-800 mb-1">Programa de formación</label>
                <select id="id_programa_select" name="id_programa_select" class="select-chev form-field w-full h-9 px-3 text-sm rounded-lg border border-gray-300 outline-none bg-white">
                  <option value="">Ingrese el programa de formación</option>
                  <?php if (empty($programas)): ?>
                    <option disabled>Sin datos disponibles</option>
                  <?php else: ?>
                    <?php foreach ($programas as $prog): ?>
                      <option value="<?= htmlspecialchars($prog['id_programa']) ?>">
                        <?= htmlspecialchars($prog['nombre_programa']) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>


              <!-- INSTRUCTOR -->
               <div class="field">
                <label for="nombre_instructor" class="block text-xs font-semibold text-gray-800 mb-1">Instructor</label>
                <select name="nombre_instructor" id="nombre_instructor"
                  class="select-chev form-field w-full h-9 px-3 text-sm rounded-lg border border-gray-300 outline-none bg-white">
                  <option value="">Seleccione el instructor</option>
                  <?php foreach ($instructores as $ins): ?>
                    <option value="<?= htmlspecialchars($ins['id_instructor'] ?? '') ?>" data-tipo="<?= htmlspecialchars($ins['tipo_instructor'] ?? '') ?>">
                      <?= htmlspecialchars($ins['nombre_instructor']) ?> <?= isset($ins['tipo_instructor']) ? "— " . htmlspecialchars($ins['tipo_instructor']) : "" ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>


              <!-- NUMERO DE FICHA -->
               <div class="field">
                <label for="numero_ficha" class="block text-xs font-semibold text-gray-800 mb-1">Número de grupo</label>
                <div class="custom-combobox w-full">
                  <input
                    type="text"
                    name="numero_ficha"
                    id="numero_ficha"
                    autocomplete="off"
                    placeholder="Seleccione o escriba número de grupo"
                    class="form-field w-full h-9 px-3 text-sm rounded-xl border border-gray-300 outline-none bg-white focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900]"/>
                  <div id="panelGrupoCrear" class="custom-combobox-panel hidden">
                    <div class="custom-combobox-list"></div>
                  </div>
                </div>
                <datalist id="listaGruposData">
                  <?php foreach ($grupos as $g): ?>
                    <?php if (!empty($g['numero_ficha'])): ?>
                      <option value="<?= htmlspecialchars($g['numero_ficha']) ?>"></option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </datalist>
              </div>



              <!-- TRIMESTRE -->
              <div class="field">
                <label for="numero_trimestre" class="block text-xs font-semibold text-gray-800 mb-1">Trimestre de grupo</label>
                <select name="numero_trimestre" id="numero_trimestre"
                  class="select-chev form-field w-full h-9 px-3 text-sm rounded-lg border border-gray-300 outline-none bg-white">
                  <option value="">Seleccione el trimestre que cursa el grupo</option>
                  <?php foreach ($trimestres as $t): ?>
                    <option value="<?= htmlspecialchars($t['numero_trimestre']) ?>">
                      <?= "Trimestre " . htmlspecialchars($t['numero_trimestre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- MODALIDAD -->
              <div class="field">
                <label for="modalidad" class="block text-xs font-semibold text-gray-800 mb-1">Modalidad</label>
                <select name="modalidad" id="modalidad"
                  class="select-chev form-field w-full h-9 px-3 text-sm rounded-lg border border-gray-300 outline-none bg-white">
                  <option value="presencial" selected>Presencial</option>
                  <option value="virtual">Virtual</option>
                  <option value="mixta">Mixta</option>
                </select>
              </div>

              <!-- AREA + ZONA-->
              <div class="field">
                <div class="flex flex-minw-0 gap-2">
                  <div class="flex-1">
                    <label for="id_area_combo" class="block text-xs font-semibold text-gray-800 mb-1">Área</label>
                    <div class="custom-combobox w-full">
                      <input
                        type="text"
                        id="id_area_combo"
                        autocomplete="off"
                        placeholder="Seleccione o escriba el área"
                        class="form-field w-full h-9 px-3 text-sm rounded-xl border border-gray-300 outline-none bg-white focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900]" />
                      <div id="panelAreaCrear" class="custom-combobox-panel hidden">
                        <div class="custom-combobox-list"></div>
                      </div>
                    </div>
                    <datalist id="listaAreasCombo">
                      <?php foreach ($areas as $a): ?>
                        <option
                          value="<?= htmlspecialchars($a['nombre_area']) ?>"
                          data-id="<?= htmlspecialchars($a['id_area']) ?>"></option>
                      <?php endforeach; ?>
                    </datalist>

                    <select name="area" id="id_area" class="hidden" tabindex="-1" aria-hidden="true">
                      <option value="">Seleccione el área</option>
                      <?php foreach ($areas as $a): ?>
                        <option value="<?= htmlspecialchars($a['id_area']) ?>"><?= htmlspecialchars($a['nombre_area']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="flex-1">
                    <label for="id_zona_combo" class="block text-xs font-semibold text-gray-800 mb-1">Zona</label>
                    <div class="custom-combobox w-full">
                      <input
                        type="text"
                        id="id_zona_combo"
                        autocomplete="off"
                        placeholder="Seleccione o escriba la zona"
                        class="form-field w-full h-9 px-3 text-sm rounded-xl border border-gray-300 outline-none bg-white focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900]"
                        disabled />
                      <div id="panelZonaCrear" class="custom-combobox-panel hidden">
                        <div class="custom-combobox-list"></div>
                      </div>
                    </div>
                    <datalist id="listaZonasCombo"></datalist>

                    <select name="zona" id="id_zona" class="hidden" tabindex="-1" aria-hidden="true">
                      <option value="">Seleccione la zona</option>
                      <?php foreach ($zonas as $z): ?>
                        <?php $label = isset($z['id_zona']) ? "Zona " . $z['id_zona'] : "Zona"; ?>
                        <option value="<?= htmlspecialchars($z['id_zona']) ?>" data-area="<?= htmlspecialchars($z['id_area'] ?? '') ?>">
                          <?= htmlspecialchars($label) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>


              <!-- DÍA SEMANA -->
              <div class="field">
                <label for="dia" class="block text-xs font-semibold text-gray-800 mb-1">Día</label>
                <select name="dia_semana" id="dia" 
                  class="select-chev select-cal form-field w-full h-9 px-3 text-sm rounded-lg border border-gray-300 outline-none bg-white">
                  <option value="">Seleccione fecha de inicio</option>
                  <option value="lunes">Lunes</option>
                  <option value="martes">Martes</option>
                  <option value="miercoles">Miércoles</option>
                  <option value="jueves">Jueves</option>
                  <option value="viernes">Viernes</option>
                  <option value="sabado">Sábado</option>
                </select>
              </div>



              <!-- HORAS -->
              <div class="field-full">
                <div class="flex flex-minw-0 gap-3">
                  <div class="flex-1">
                    <label for="hora_inicio" class="block text-xs font-semibold text-gray-800 mb-1">Hora de inicio</label>
                    <select name="hora_inicio" id="hora_inicio" 
                      class="select-chev form-field w-full h-9 px-3 text-sm rounded-lg border border-gray-300 outline-none bg-white">
                      <option value="">Seleccione hora de inicio</option>
                    <?php for ($i = 6; $i <= 22; $i++): ?>
                      <option value="<?= $i ?>:00"><?= $i ?>:00</option>
                    <?php endfor; ?>
                  </select>
                  </div>
                  <div class="flex-1">
                    <label for="hora_fin" class="block text-xs font-semibold text-gray-800 mb-1">Hora de fin</label>
                    <select name="hora_fin" id="hora_fin" 
                      class="select-chev form-field w-full h-9 px-3 text-sm rounded-lg border border-gray-300 outline-none bg-white">
                      <option value="">Seleccione hora de fin</option>
                    <?php for ($i = 7; $i <= 22; $i++): ?>
                      <option value="<?= $i ?>:00"><?= $i ?>:00</option>
                    <?php endfor; ?>
                  </select>
                  </div>
                </div>
              </div>



              <!-- COMPETENCIA -->
              <div class="field-full">
                <label for="id_competencia" class="block text-xs font-semibold text-gray-800 mb-1">Competencia</label>
                <div class="relative">
                  <select id="id_competencia" name="id_competencia" class="select-chev form-field w-full h-9 px-3 text-sm rounded-lg border border-gray-300 outline-none bg-white">
                    <option value="">Seleccione competencia</option>
                    <?php if (empty($competencias)): ?>
                      <option disabled>Sin datos disponibles</option>
                    <?php else: ?>
                      <?php foreach ($competencias as $comp): ?>
                        <?php
                          $nombreCompFull = (string)($comp['nombre_competencia'] ?? '');
                          $nombreCompShort = function_exists('mb_strimwidth')
                            ? mb_strimwidth($nombreCompFull, 0, 58, '…', 'UTF-8')
                            : (strlen($nombreCompFull) > 58 ? substr($nombreCompFull, 0, 58) . '…' : $nombreCompFull);
                        ?>
                        <option
                          value="<?= htmlspecialchars($comp['id_competencia']) ?>"
                          data-programa="<?= htmlspecialchars($comp['id_programa']) ?>"
                          title="<?= htmlspecialchars($nombreCompFull) ?>">
                          <?= htmlspecialchars($nombreCompShort) ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>


              <!-- Descripción de la competencia (solo informativa, no se guarda) -->
              <div class="field-full">
                <label for="descripcion_competencia" class="block text-xs font-semibold text-gray-800 mb-1">Descripción de la competencia</label>
                <textarea 
                  id="descripcion_competencia" 
                  name="descripcion_competencia" 
                  rows="5"
                  class="form-field w-full px-3 py-2 text-sm rounded-lg border border-gray-300 outline-none bg-white text-gray-700 resize-none overflow-auto">
                </textarea>
              </div>


              <!-- BOTÓN RAEs + CONTADOR -->
              <div class="field-full">
                <div class="flex items-center justify-between gap-3">
                  
                  <!-- Botón a la izquierda -->
                  <button type="button" id="btnSeleccionarRaes" class="flex-1 h-10 bg-white border border-gray-300 rounded-lg text-xs font-medium text-[#00324D] hover:bg-[#f4f4f5] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Seleccionar RAEs de la competencia
                  </button>

                  <!-- Contador a la derecha -->
                  <small id="textoResumenRaes" class="text-[15px] text-gray-500 whitespace-nowrap px-3 py-2 bg-gray-50 rounded-md border border-gray-200 text-center">
                    0 seleccionados
                  </small>
                </div>
              </div>

            </div><!-- /form-grid -->

            <!-- Campos ocultos -->
            <input type="hidden" name="id_rae" id="id_rae_field" value="">
            <input type="hidden" name="id_programa" id="id_programa_field" value="">

            <button type="submit" class="w-full h-10 bg-[#0b2d5b] text-white rounded-lg text-sm font-medium
              hover:bg-[#082244] transition-all duration-200 mt-4">
              Guardar trimestralización
            </button>
          </form>
        </div>
      </div>
    </div>




    <!-- ============== MODAL RAEs POR COMPETENCIA ============== -->
    <div id="modalRaes" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="tituloModalRaes">
      <!-- Backdrop RAEs -->
      <div id="modalRaesBackdrop" class="fixed inset-0 bg-black/40"></div>

      <!-- Contenedor centrado RAEs -->
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <div id="modalRaesCard" class="bg-white w-full max-w-[420px] sm:max-w-[520px] md:max-w-[560px] rounded-2xl shadow-md border border-[#d8d8d8] px-4 sm:px-6 pt-12 pb-6 mx-3 max-h-[90vh] overflow-y-auto">
          <div class="flex items-start justify-between mb-2 mt-4">
            <div class="text-left">
              <h3 id="tituloModalRaes" class="text-xl text-[#0c2443] font-semibold">
                RAEs asociadas a la competencia
              </h3>
              <p id="subtituloModalRaes" class="text-base text-gray-500 mt-1"></p>
            </div>
            <button type="button" id="btnCerrarModalRaes" class="ml-3 -mt-1 text-gray-500 hover:text-gray-700" aria-label="Cerrar"> ✕ </button>
          </div>

          <div class="border-b border-[#dcdcdc] mb-3"></div>

          <!-- Select all -->
          <div class="flex items-center justify-between mb-2">
            <label class="flex items-center gap-2 text-xs sm:text-sm text-gray-700">
              <input type="checkbox" id="chkRaesTodos" class="rounded border-gray-300 mb-4">
              <span class="text-sm">Seleccionar todas las RAEs</span>
            </label>
            <span id="contadorRaesSeleccionadas" class="text-sm text-gray-500"></span>
          </div>

          <!-- Contenedor de lista de RAEs -->
          <div id="listaRaesModal"
               class="mt-2 max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-left text-sm sm:text-base">
            <p class="text-gray-500 text-xs">Cargando RAEs...</p>
          </div>

          <!-- Acciones -->
          <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
            <button
              type="button" id="btnCancelarRaes" class="px-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-300 font-semibold hover:bg-gray-50 w-full sm:w-auto">
              Cancelar
            </button>
            <button type="button" id="btnGuardarRaes" class="px-3 py-2 text-xs sm:text-sm rounded-lg bg-[#0b2d5b] text-white font-medium hover:bg-[#082244] w-full sm:w-auto">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- ============== /MODAL RAEs ============== -->




    <!-- ============== MODAL DUPLICAR HORARIO ============== -->
    <div id="modalDuplicarHorario" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="tituloModalDuplicar">
      <div id="modalDuplicarBackdrop" class="fixed inset-0 bg-black/40"></div>

      <!-- Contenedor centrado -->
      <div class="fixed inset-0 flex items-center justify-center p-4">
        <div
          class="bg-white w-full max-w-[420px] sm:max-w-[520px] md:max-w-[560px] rounded-2xl shadow-md border border-[#dd8d8] px-4 sm:px-6 pt-10 pb-6 mx-3 max-h-[90vh] overflow-y-auto"
        >
          <!-- Header -->
          <div class="flex items-start justify-between mb-2 mt-4">
            <div class="text-left">
              <h3 id="tituloModalDuplicar" class="text-[1rem] text-[#0c2443] font-semibold">
                ¿Aplicar este horario a otros días?
              </h3>
              <p class="text-xs text-gray-500 mt-1">
                Marca los días en los que también quieres usar este mismo horario.
              </p>
            </div>
            <button type="button" id="btnCerrarModalDuplicar" class="ml-3 -mt-1 text-gray-500 hover:text-gray-700" aria-label="Cerrar"> ✕ </button>
            </div>
            
            <div class="border-b border-[#dcdcdc] mb-3"></div>
          
          <!-- Checklist + select oculto -->
          <div class="mb-4 text-left">
            <label class="block text-xs sm:text-sm text-gray-700 mb-1">
              Selecciona el/los día(s) al que deseas aplicar también este horario:
            </label>

            <!-- 📝 Checklist visible -->
            <div id="checklistDias" class="mt-1 grid grid-cols-2 gap-2 text-xs sm:text-sm">
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="lunes">
                <span>Lunes</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="martes">
                <span>Martes</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="miercoles">
                <span>Miércoles</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="jueves">
                <span>Jueves</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="viernes">
                <span>Viernes</span>
              </label>
              <label class="flex items-center gap-2">
                <input type="checkbox" class="chk-dia-duplicar rounded border-gray-300" value="sabado">
                <span>Sábado</span>
              </label>
            </div>

            <!-- Select oculto para que lo use formulario_trimestralizacion.js -->
            <select id="selectDiaDuplicar" multiple class="hidden">
              <option value="lunes">lunes</option>
              <option value="martes">martes</option>
              <option value="miercoles">miercoles</option>
              <option value="jueves">jueves</option>
              <option value="viernes">viernes</option>
              <option value="sabado">sabado</option>
            </select>

            <small id="mensajeErrorDuplicar" class="mt-1 block text-[11px] text-red-500 hidden">
              Debes seleccionar al menos un día diferente al original.
            </small>
          </div>

          <!-- Acciones -->
          <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-end">
            <button type="button" id="btnSoloEsteDia" class="w-full sm:w-auto px-3 py-2 text-xs sm:text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
              No, solo este día
            </button>
            <button type="button" id="btnDuplicarDia" class="w-full sm:w-auto px-3 py-2 text-xs sm:text-sm rounded-lg bg-[#0b2d5b] text-white font-medium hover:bg-[#082244]">
              Sí, duplicar horario
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- ============== /MODAL DUPLICAR HORARIO ============== -->

    <script>
      const BASE_URL = "<?= BASE_URL ?>";
    </script>

    <!-- Scripts compartidos: apertura de modal, validaciones, flujo duplicar, etc -->
    <script src="<?= BASE_URL ?>src/assets/js/landing.js"></script>
    <script src="<?= BASE_URL ?>src/assets/js/formulario_trimestralizacion.js"></script>

    <!-- Combobox área/zona en modal crear -->
    <script>
      (function(){
        function initStyledCombobox({ input, panel, getItems, onSelect, getLabel, emptyText = 'Sin datos disponibles' }) {
          if (!input || !panel || typeof getItems !== 'function') return;

          const list = panel.querySelector('.custom-combobox-list');
          if (!list) return;

          const normalize = (value) => String(value || '').trim().toLowerCase();

          function closePanel() {
            panel.classList.add('hidden');
          }

          function openPanel() {
            if (input.disabled) return;
            panel.classList.remove('hidden');
          }

          function render(query = '') {
            const search = normalize(query);
            const items = (getItems() || []).filter((item) => {
              const label = normalize(getLabel ? getLabel(item) : item.label);
              return !search || label.includes(search);
            });

            list.innerHTML = '';

            if (!items.length) {
              const empty = document.createElement('div');
              empty.className = 'custom-combobox-empty';
              empty.textContent = emptyText;
              list.appendChild(empty);
              openPanel();
              return;
            }

            items.forEach((item) => {
              const option = document.createElement('button');
              option.type = 'button';
              option.className = 'custom-combobox-option';
              option.textContent = getLabel ? getLabel(item) : item.label;
              option.addEventListener('click', () => {
                input.value = getLabel ? getLabel(item) : item.label;
                if (typeof onSelect === 'function') onSelect(item);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                closePanel();
              });
              list.appendChild(option);
            });

            openPanel();
          }

          input.addEventListener('focus', () => render(input.value));
          input.addEventListener('input', () => render(input.value));
          input.addEventListener('click', () => render(input.value));

          document.addEventListener('click', (event) => {
            const wrapper = input.closest('.custom-combobox');
            if (wrapper && !wrapper.contains(event.target)) {
              closePanel();
            }
          });

          input._styledCombobox = { render, closePanel, openPanel };
        }

        const grupoData = document.getElementById('listaGruposData');
        const inputGrupoFiltro = document.getElementById('inputGrupoTexto');
        const panelGrupoFiltro = document.getElementById('panelGrupoFiltro');
        const inputGrupoCrear = document.getElementById('numero_ficha');
        const panelGrupoCrear = document.getElementById('panelGrupoCrear');
        const selArea = document.getElementById('id_area');
        const selZona = document.getElementById('id_zona');
        const inpArea = document.getElementById('id_area_combo');
        const inpZona = document.getElementById('id_zona_combo');
        const listAreas = document.getElementById('listaAreasCombo');
        const listZonas = document.getElementById('listaZonasCombo');
        const panelArea = document.getElementById('panelAreaCrear');
        const panelZona = document.getElementById('panelZonaCrear');

        if (!selArea || !selZona || !inpArea || !inpZona || !listAreas || !listZonas) return;

        const grupos = grupoData
          ? Array.from(grupoData.options)
              .map((opt) => ({ label: String(opt.value || '').trim() }))
              .filter((item) => item.label !== '')
          : [];

        function findDatalistOptionByValue(listEl, value) {
          const target = String(value || '').trim().toLowerCase();
          if (!target) return null;
          return Array.from(listEl.options).find((opt) =>
            String(opt.value || '').trim().toLowerCase() === target
          ) || null;
        }

        function cargarZonasSegunArea(idArea) {
          const allZonas = Array.from(selZona.options).filter((opt) => opt.value !== '');
          listZonas.innerHTML = '';

          allZonas.forEach((opt) => {
            const areaOpt = String(opt.dataset.area || '');
            if (!idArea || areaOpt !== String(idArea)) return;

            const op = document.createElement('option');
            op.value = String(opt.textContent || '').trim();
            op.dataset.id = String(opt.value || '');
            op.dataset.area = areaOpt;
            listZonas.appendChild(op);
          });

          if (inpZona._styledCombobox) {
            inpZona._styledCombobox.render(inpZona.value);
          }
        }

        function syncAreaFromInput() {
          const areaOption = findDatalistOptionByValue(listAreas, inpArea.value);
          const idArea = areaOption ? String(areaOption.dataset.id || '') : '';

          selArea.value = idArea;
          inpZona.value = '';
          selZona.value = '';
          inpZona.disabled = !idArea;

          cargarZonasSegunArea(idArea);
        }

        function syncZonaFromInput() {
          const zonaOption = findDatalistOptionByValue(listZonas, inpZona.value);
          const idZona = zonaOption ? String(zonaOption.dataset.id || '') : '';
          selZona.value = idZona;
        }

        initStyledCombobox({
          input: inputGrupoFiltro,
          panel: panelGrupoFiltro,
          getItems: () => grupos,
          onSelect: () => {},
          getLabel: (item) => item.label
        });

        initStyledCombobox({
          input: inputGrupoCrear,
          panel: panelGrupoCrear,
          getItems: () => grupos,
          onSelect: () => {},
          getLabel: (item) => item.label
        });

        initStyledCombobox({
          input: inpArea,
          panel: panelArea,
          getItems: () => Array.from(listAreas.options).map((opt) => ({
            label: String(opt.value || '').trim(),
            id: String(opt.dataset.id || '')
          })),
          onSelect: (item) => {
            selArea.value = item.id || '';
            inpZona.value = '';
            selZona.value = '';
            inpZona.disabled = !item.id;
            cargarZonasSegunArea(item.id || '');
          },
          getLabel: (item) => item.label
        });

        initStyledCombobox({
          input: inpZona,
          panel: panelZona,
          getItems: () => Array.from(listZonas.options).map((opt) => ({
            label: String(opt.value || '').trim(),
            id: String(opt.dataset.id || ''),
            area: String(opt.dataset.area || '')
          })),
          onSelect: (item) => {
            selZona.value = item.id || '';
          },
          getLabel: (item) => item.label
        });

        inpArea.addEventListener('change', syncAreaFromInput);
        inpArea.addEventListener('blur', syncAreaFromInput);
        inpZona.addEventListener('change', syncZonaFromInput);
        inpZona.addEventListener('blur', syncZonaFromInput);

        document.addEventListener('DOMContentLoaded', () => {
          inpZona.disabled = true;
          listZonas.innerHTML = '';
        });
      })();
    </script>

    <!-- Filtrar competencias según programa (igual lógica que la landing, sin descripcion) -->
    <script>
      (function () {
        const base = (window.BASE_URL || "").replace(/\/+$/, "/");

        async function cargarProgramasFallback() {
          const selProg = document.getElementById('id_programa_select');
          if (!selProg) return;

          const yaTieneProgramas = Array.from(selProg.options).some((opt) => {
            if (!opt.value) return false;
            return !String(opt.textContent || '').toLowerCase().includes('sin datos disponibles');
          });

          if (yaTieneProgramas) return;

          try {
            const res = await fetch(base + 'src/controllers/ProgramasController.php?accion=listar');
            if (!res.ok) return;
            const data = await res.json();
            const arr = Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : []);
            if (!arr.length) return;

            const activos = arr.filter((p) => p && (String(p.estado) === '1' || p.estado === 1 || typeof p.estado === 'undefined'));
            const lista = activos.length ? activos : arr;

            selProg.innerHTML = '<option value="">Ingrese el programa de formación</option>';
            lista.forEach((p) => {
              const id = p.id_programa ?? '';
              const nombre = p.nombre_programa ?? '';
              if (!id || !nombre) return;
              const opt = document.createElement('option');
              opt.value = String(id);
              opt.textContent = String(nombre);
              selProg.appendChild(opt);
            });
          } catch (e) {
            console.warn('No se pudieron cargar programas (fallback):', e);
          }
        }

        document.addEventListener('DOMContentLoaded', cargarProgramasFallback);

        const selProg = document.getElementById('id_programa_select');
        const selComp = document.getElementById('id_competencia');
        if (!selProg || !selComp) return;

        function filtrarCompetenciasPorPrograma() {
          const progVal = selProg.value;
          let hayCoincidencias = false;

          for (const opt of selComp.options) {
            if (opt.value === "") {
              // Placeholder siempre visible
              opt.hidden = false;
              opt.disabled = false;
              continue;
            }

            const optProg = opt.getAttribute('data-programa') ?? "";
            const show = progVal !== "" && (String(optProg) === String(progVal));

            if (show) hayCoincidencias = true;

            opt.hidden = !show;
            opt.disabled = !show;
          }

          if (progVal !== "" && !hayCoincidencias) {
            console.warn(
              '[Trimestralización] Ninguna competencia tiene data-programa =',
              progVal,
              '. Revisa la columna id_programa de la tabla competencias.'
            );
          }

          const selectedOpt = selComp.selectedOptions[0];
          if (selectedOpt && selectedOpt.hidden) {
            selComp.value = "";
          }
        }

        selProg.addEventListener('change', filtrarCompetenciasPorPrograma);
        document.addEventListener('DOMContentLoaded', filtrarCompetenciasPorPrograma);
      })();
    </script>

    <!-- Lógica del modal de RAEs por competencia -->
    <script>
    (function () {
        const BASE_URL = window.BASE_URL || '';
        const API_RAES = (BASE_URL + 'src/controllers/RaeController.php?accion=listar').replace(/\/+$/, '');
        
        const form = document.getElementById('formTrimestralizacion');
        if (!form) return;

        const selComp = document.getElementById('id_competencia');
        const hiddenRaes = document.getElementById('id_rae_field');
        const resumenRaes = document.getElementById('textoResumenRaes');
        const btnRaes = document.getElementById('btnSeleccionarRaes');

        const modalRaes = document.getElementById('modalRaes');
        const backdropRaes = document.getElementById('modalRaesBackdrop');
        const btnCerrarModalRaes = document.getElementById('btnCerrarModalRaes');
        const btnCancelarRaes = document.getElementById('btnCancelarRaes');
        const btnGuardarRaes = document.getElementById('btnGuardarRaes');
        const listaRaesModal = document.getElementById('listaRaesModal');
        const chkRaesTodos = document.getElementById('chkRaesTodos');
        const contadorRaesSeleccionadas = document.getElementById('contadorRaesSeleccionadas');
        const subtituloModalRaes = document.getElementById('subtituloModalRaes');

        if (!selComp || !btnRaes || !modalRaes) return;

        function toast(msg, type = 'info') {
          if (window.Swal) {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: type,
              title: msg,
              showConfirmButton: false,
              timer: 2200,
              timerProgressBar: true
            });
          } else {
            alert(msg);
          }
        }

        function actualizarResumen() {
          const valor = (hiddenRaes.value || '').trim();
          if (!valor) {
            resumenRaes.textContent = 'No hay RAEs seleccionadas.';
            return;
          }
          const partes = valor.split(',').map(v => v.trim()).filter(Boolean);
          if (!partes.length) {
            resumenRaes.textContent = 'No hay RAEs seleccionadas.';
            return;
          }
          resumenRaes.textContent = partes.length === 1
            ? '1 RAE seleccionada.'
            : partes.length + ' RAEs seleccionadas.';
        }

        function abrirModalRaes() {
          modalRaes.classList.remove('hidden');
          modalRaes.style.display = '';
          modalRaes.style.pointerEvents = '';
          modalRaes.style.visibility = '';
          modalRaes.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => { el.style.pointerEvents = ''; });
        }

        function cerrarModalRaes() {
          const activeEl = document.activeElement;
          if (activeEl && modalRaes.contains(activeEl)) activeEl.blur();
          modalRaes.classList.add('hidden');
          modalRaes.classList.remove('flex', 'block', 'items-center', 'justify-center');
          modalRaes.style.display = 'none';
          modalRaes.style.pointerEvents = 'none';
          modalRaes.style.visibility = 'hidden';
          modalRaes.querySelectorAll(".absolute.inset-0, .fixed.inset-0, [class*='inset-0']").forEach((el) => { el.style.pointerEvents = 'none'; });
          document.body.style.overflow = '';
          document.body.classList.remove('overflow-hidden');
        }

        function contarSeleccionadas() {
          const checks = listaRaesModal.querySelectorAll('.chk-rae-modal:checked');
          const cantidad = checks.length;

          contadorRaesSeleccionadas.textContent =
            cantidad === 0 ? '' :
            cantidad === 1 ? '1 RAE seleccionada' :
            cantidad + ' RAEs seleccionadas';
        }

        function actualizarHiddenAuto() {
          const checks = listaRaesModal.querySelectorAll('.chk-rae-modal:checked');
          const ids = Array.from(checks).map(ch => ch.value);
          hiddenRaes.value = ids.join(',');
          actualizarResumen();
        }

        function aplicarSeleccionTodos() {
          const checks = listaRaesModal.querySelectorAll('.chk-rae-modal');
          const checked = chkRaesTodos.checked;
          checks.forEach(ch => { ch.checked = checked; });
          contarSeleccionadas();
          actualizarHiddenAuto();
        }

        async function cargarRaesPorCompetencia(idComp) {
          listaRaesModal.innerHTML = '<p class="text-gray-500 text-xs">Cargando RAEs...</p>';
          chkRaesTodos.checked = false;
          contadorRaesSeleccionadas.textContent = '';

          try {
            const resp = await fetch(API_RAES + '&id_competencia=' + encodeURIComponent(idComp));
            const data = await resp.json();

            const lista = Array.isArray(data) ? data : (data.data || []);
            if (!lista.length) {
              listaRaesModal.innerHTML = '<p class="text-gray-500 text-xs">No hay RAEs asociadas a esta competencia.</p>';
              hiddenRaes.value = "";
              actualizarResumen();
              return;
            }

            const seleccionadasPrevias = (hiddenRaes.value || '').split(',')
              .map(v => v.trim())
              .filter(Boolean);

            const frag = document.createDocumentFragment();

            lista.forEach((r) => {
              const id = r.id_rae || r.id || r.ID_RAE;
              const codigo = r.codigo_rae || r.codigo || r.codigoRAE || '';
              const desc = r.descripcion || r.descripcion_rae || r.nombre_rae || r.nombre || '';

              if (!id) return;

              const label = document.createElement('label');
              label.className = 'flex items-start gap-2 py-1 border-b border-gray-100 last:border-b-0 cursor-pointer text-sm sm:text-xs text-gray-800';

              const input = document.createElement('input');
              input.type = 'checkbox';
              input.value = id;
              input.className = 'mt-[3px] chk-rae-modal rounded border-gray-300';

              if (seleccionadasPrevias.includes(String(id))) {
                input.checked = true;
              }

              const span = document.createElement('span');
              span.innerHTML = (codigo ? ('<strong>' + codigo + '</strong> — ') : '') +
                               (desc || '(sin descripción)');

              label.appendChild(input);
              label.appendChild(span);
              frag.appendChild(label);
            });

            listaRaesModal.innerHTML = '';
            listaRaesModal.appendChild(frag);

            contarSeleccionadas();
            actualizarHiddenAuto();
          } catch (err) {
            console.error(err);
            listaRaesModal.innerHTML = '<p class="text-red-500 text-xs">Error al cargar las RAEs.</p>';
          }
        }

        function toggleBotonRaes() {
          btnRaes.disabled = !selComp.value;
        }

        toggleBotonRaes();
        actualizarResumen();

        selComp.addEventListener('change', () => {
          toggleBotonRaes();
          hiddenRaes.value = "";
          actualizarResumen();
        });

        btnRaes.addEventListener('click', async () => {
          const idComp = selComp.value;
          if (!idComp) {
            toast('Primero selecciona una competencia.', 'warning');
            return;
          }

          const opt = selComp.selectedOptions[0];
          const nombreComp = opt ? (opt.textContent || '').trim() : '';
          subtituloModalRaes.textContent = nombreComp;

          await cargarRaesPorCompetencia(idComp);
          abrirModalRaes();
        });

        [btnCerrarModalRaes, btnCancelarRaes].forEach(btn => {
          if (btn) btn.addEventListener('click', cerrarModalRaes);
        });

        if (backdropRaes) backdropRaes.addEventListener('click', cerrarModalRaes);

        chkRaesTodos.addEventListener('change', aplicarSeleccionTodos);

        listaRaesModal.addEventListener('change', (e) => {
          if (e.target.classList.contains('chk-rae-modal')) {
            contarSeleccionadas();
            actualizarHiddenAuto();
          }
        });

        btnGuardarRaes.addEventListener('click', () => {
          cerrarModalRaes();
        });
    })();
    </script>

</body>
</html>
