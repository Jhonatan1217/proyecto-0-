<?php /* views/grupos.php */ ?>
<?php if(!defined('BASE_URL')) { 
  $base = '/senlock/';
  define('BASE_URL', $base);
} ?>

<div class="max-w-6xl mx-auto px-4 py-10">

  <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">
    Gestión de Grupos
  </h1>
  <p class="text-gray-500 mb-6">Administra los Grupos</p>

  <div class="bg-white shadow rounded-2xl border border-gray-200">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-800">Grupos</h2>
        <p class="text-sm text-gray-500">Lista de todos los grupos registrados</p>
      </div>

      <button id="btnAbrirModalGrupo"
        class="w-full md:w-auto bg-[#0a3a57] hover:bg-[#00304D] active:scale-95 transition
               text-white px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-sm">
        <span>Nuevo Grupo</span>
      </button>
    </div>

    <!-- Filtros -->
    <div class="px-6 py-4 border-b">
      <div class="flex flex-col md:flex-row gap-4">

        <!-- Select -->
        <div class="relative w-full md:w-64">
          <select id="filtroPrograma"
            class="w-full appearance-none rounded-full border border-gray-300 bg-white
                   px-4 py-2.5 pr-10 text-sm
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
            <option value="">Todos los programas</option>
          </select>
          <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 9l-7 7-7-7" />
          </svg>
        </div>

        <!-- Buscador -->
        <div class="relative w-full md:w-64">
          <input type="text" id="buscadorGrupo"
            placeholder="Buscar grupo..."
            class="w-full rounded-full border border-gray-300 bg-white
                   pl-10 pr-4 py-2.5 text-sm
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

      </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
          <tr>
            <th class="px-6 py-3 font-medium">Número</th>
            <th class="px-6 py-3 font-medium">Programa</th>
            <th class="px-6 py-3 font-medium">Nivel</th>
            <th class="px-6 py-3 font-medium">Jornada</th>
            <th class="px-6 py-3 font-medium">Modalidad</th>
            <th class="px-6 py-3 font-medium">Líder</th>
            <th class="px-6 py-3 font-medium text-right">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbodyGrupos"
          class="text-sm divide-y divide-gray-100">
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ================= MODAL ================= -->

<div id="modalGrupo"
  class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">

  <!-- Caja Modal -->
  <div class="bg-white w-full max-w-xl rounded-2xl shadow-xl border border-gray-200
              px-8 py-10 relative">

    <!-- Botón cerrar -->
    <button id="btnCerrarModal"
      class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-lg">
      ✕
    </button>

    <h2 class="text-2xl font-bold text-[#39A900] mb-8 text-left">
      Nuevo Grupo
    </h2>

    <form id="formGrupo" class="space-y-6">

      <div class="flex flex-col gap-6">

        <!-- Número -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Número de Ficha
          </label>
          <input type="number" name="numero_ficha" required
            placeholder="Ingrese el número de grupo"
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
        </div>

        <!-- Programa -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Programa
          </label>
          <select name="id_programa" id="selectProgramaModal" required
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
            <option value="">Seleccione un programa</option>
          </select>
        </div>

        <!-- Jornada -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Jornada
          </label>
          <select name="jornada" required
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
            <option value="DIURNA">Diurna</option>
            <option value="NOCTURNA">Nocturna</option>
            <option value="MIXTA">Mixta</option>
          </select>
        </div>

        <!-- Modalidad -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Modalidad
          </label>
          <select name="modalidad" required
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
            <option value="PRESENCIAL">Presencial</option>
            <option value="VIRTUAL">Virtual</option>
            <option value="A DISTANCIA">A Distancia</option>
          </select>
        </div>

        <!-- Líder -->
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Líder de Grupo
          </label>
          <select name="id_lider_grupo" id="selectLiderModal" required
            class="w-full border border-gray-300 rounded-xl px-4 py-3
                   focus:ring-2 focus:ring-[#39A900]/20
                   focus:border-[#39A900] outline-none transition">
            <option value="">Seleccione líder</option>
          </select>
        </div>

      </div>

      <!-- Botones -->
      <div class="flex justify-end gap-4 pt-6">

        <button type="button" id="btnCancelar"
          class="px-6 py-3 rounded-xl border border-gray-300 text-gray-600
                 hover:bg-gray-50 transition">
          Cancelar
        </button>

        <button type="submit"
          class="px-6 py-3 rounded-xl bg-[#0a3a57] text-white
                 hover:bg-[#00304D] transition shadow-sm">
          Guardar Grupo
        </button>

      </div>

    </form>
  </div>
</div>

<script>
window.API_FICHA = "<?= BASE_URL ?>src/controllers/fichaController.php";
</script>
<script src="<?= BASE_URL ?>src/assets/js/gestionGrupos.js"></script>