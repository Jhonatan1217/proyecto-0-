<?php /* views/gestionGrupos.php - Plantilla maestra: tabla + modal + filtros */ ?>
<?php
if (!defined("BASE_URL")) {
  $base = "/";
  define("BASE_URL", $base);
}
?>
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/combobox.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/table-edit.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/modal-enterprise.css">

<div class="max-w-6xl mx-auto px-4 py-10">
  <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Gestión de Grupos</h1>
  <p class="text-gray-500 mb-6">Administra los Grupos</p>

  <div class="bg-white shadow rounded-2xl border border-gray-200">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-800">Grupos</h2>
        <p class="text-sm text-gray-500">Lista de todos los grupos registrados</p>
      </div>
      <button id="btnAbrirModalGrupo" type="button"
        class="w-full md:w-auto bg-[#0a3a57] hover:bg-[#00304D] active:scale-95 transition text-white px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-sm">
        <span>Nuevo Grupo</span>
      </button>
    </div>

    <div class="px-6 py-4">
      <div class="flex flex-col md:flex-row gap-4">
        <div id="filtroProgramaWrap" class="relative w-full md:w-64">
          <select id="filtroPrograma" class="select-grupo w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition">
            <option value="">Todos los programas</option>
          </select>
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none select-grupo-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
        <div id="buscadorGrupoWrap" class="relative w-full md:w-64">
          <input type="text" id="buscadorGrupo" placeholder="Buscar grupo..."
            class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
    </div>

    <div class="table-grupos-wrap overflow-x-auto">
      <table class="w-full text-left table-grupos" id="tablaGrupos">
        <colgroup>
          <col style="width:10%"><col style="width:24%"><col style="width:12%"><col style="width:12%"><col style="width:14%"><col style="width:16%"><col style="width:12%">
        </colgroup>
        <thead class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
          <tr>
            <th class="font-medium col-numero">Número</th>
            <th class="font-medium col-programa">Programa</th>
            <th class="font-medium col-nivel">Nivel</th>
            <th class="font-medium col-jornada">Jornada</th>
            <th class="font-medium col-modalidad">Modalidad</th>
            <th class="font-medium col-lider">Líder</th>
            <th class="font-medium text-right col-acciones">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbodyGrupos" class="text-sm divide-y divide-gray-100"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Nuevo Grupo -->
<div id="modalGrupo" role="dialog" aria-labelledby="modalGrupoTitle" aria-modal="true"
  class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 modal-grupo-overlay hidden">
  <div class="modal-grupo-box bg-white w-full max-w-xl rounded-2xl flex flex-col overflow-hidden">
    <header class="modal-grupo-header flex items-center justify-between">
      <h2 id="modalGrupoTitle" class="text-xl font-bold text-[#39A900] tracking-tight">Nuevo Grupo</h2>
      <button type="button" id="btnCerrarModal" aria-label="Cerrar" class="p-2 -m-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>
    </header>
    <form id="formGrupo" class="flex flex-col flex-1 min-h-0">
      <div class="modal-grupo-body flex-1">
        <div class="space-y-5">
          <div>
            <label for="inputNumeroFicha" class="label-enterprise">Número de grupo</label>
            <input type="number" name="numero_ficha" id="inputNumeroFicha" max="999999999" min="1" required placeholder="Ingrese el número" class="input-enterprise">
            <p id="errorNumeroFicha" class="text-red-500 text-sm mt-1 hidden"></p>
          </div>
          <div>
            <label for="selectProgramaModal" class="label-enterprise">Programa</label>
            <div class="relative">
              <select name="id_programa" id="selectProgramaModal" required class="select-grupo input-enterprise pr-10 appearance-none cursor-pointer">
                <option value="">Seleccione un programa</option>
              </select>
              <span class="select-grupo-chevron pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </span>
            </div>
          </div>
          <div>
            <label for="selectJornada" class="label-enterprise">Jornada</label>
            <div class="relative">
              <select name="jornada" id="selectJornada" required class="select-grupo select-simple input-enterprise pr-10 appearance-none cursor-pointer">
                <option value="" disabled selected>Seleccione jornada</option>
                <option value="DIURNA">Diurna</option>
                <option value="NOCTURNA">Nocturna</option>
                <option value="MIXTA">Mixta</option>
              </select>
              <span class="select-grupo-chevron pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </span>
            </div>
          </div>
          <div>
            <label for="selectModalidad" class="label-enterprise">Modalidad</label>
            <div class="relative">
              <select name="modalidad" id="selectModalidad" required class="select-grupo select-simple input-enterprise pr-10 appearance-none cursor-pointer">
                <option value="" disabled selected>Seleccione modalidad</option>
                <option value="PRESENCIAL">Presencial</option>
                <option value="VIRTUAL">Virtual</option>
                <option value="A DISTANCIA">A Distancia</option>
              </select>
              <span class="select-grupo-chevron pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </span>
            </div>
          </div>
          <div>
            <label for="selectLiderModal" class="label-enterprise">Líder de grupo</label>
            <div class="relative">
              <select name="id_lider_grupo" id="selectLiderModal" required class="select-grupo input-enterprise pr-10 appearance-none cursor-pointer">
                <option value="">Seleccione líder</option>
              </select>
              <span class="select-grupo-chevron pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </span>
            </div>
          </div>
        </div>
      </div>
      <footer class="modal-grupo-footer flex justify-end gap-3">
        <button type="button" id="btnCancelar" class="btn-modal-secondary">Cancelar</button>
        <button type="submit" class="btn-modal-primary">Guardar Grupo</button>
      </footer>
    </form>
  </div>
</div>

<script>window.API_FICHA = <?= json_encode(BASE_URL . "src/controllers/fichaController.php") ?>;</script>
<script src="<?= BASE_URL ?>src/assets/js/components/combobox.js"></script>
<script src="<?= BASE_URL ?>src/assets/js/gestionGrupos.js"></script>
