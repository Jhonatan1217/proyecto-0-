<?php /* views/grupos.php */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Grupos | SENLOCK</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
  <style>
    * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    
    /* Scroll personalizado */
    #wrapTabla::-webkit-scrollbar { width: 8px; }
    #wrapTabla::-webkit-scrollbar-thumb { background-color: rgba(0, 0, 0, 0.15); border-radius: 10px; }
    #wrapTabla:hover::-webkit-scrollbar-thumb { background-color: rgba(0, 0, 0, 0.25); }

    /* Estilos para badges de estado */
    .badge-jornada {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 30px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    .badge-diurna { background-color: #e0f2e9; color: #0a5c3a; }
    .badge-nocturna { background-color: #e6e0ff; color: #4a2b9e; }
    .badge-mixta { background-color: #fff1dd; color: #b45b0a; }
    
    .badge-modalidad {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 30px;
      font-size: 0.75rem;
      font-weight: 500;
      background-color: #e6f0f9;
      color: #0a3a57;
    }

    /* Botón de acciones */
    .btn-accion {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: transparent;
      border: none;
      cursor: pointer;
      padding: 6px;
      border-radius: 9999px;
      transition: background-color 0.15s;
    }
    .btn-accion:hover { background-color: #e6edf4; }

    /* Buscador personalizado */
    .buscador-wrapper {
      position: relative;
      display: flex;
      align-items: center;
      flex: 1;
    }
    .buscador-icono {
      position: absolute;
      left: 12px;
      width: 18px;
      height: 18px;
      opacity: 0.5;
    }
    .buscador-input {
      padding-left: 40px;
      padding-right: 16px;
      padding-top: 10px;
      padding-bottom: 10px;
      border-radius: 40px;
      border: 1px solid #e5e7eb;
      width: 100%;
      font-size: 0.95rem;
      background-color: white;
      transition: all 0.2s;
    }
    .buscador-input:focus {
      outline: none;
      border-color: #39A900;
      box-shadow: 0 0 0 3px rgba(57, 169, 0, 0.1);
    }

    /* Select personalizado */
    .select-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }
    .select-programas {
      appearance: none;
      padding: 10px 40px 10px 16px;
      border-radius: 40px;
      border: 1px solid #e5e7eb;
      background-color: white;
      font-size: 0.95rem;
      cursor: pointer;
      min-width: 240px;
    }
    .select-programas:focus {
      outline: none;
      border-color: #39A900;
      box-shadow: 0 0 0 3px rgba(57, 169, 0, 0.1);
    }
    .select-icono {
      position: absolute;
      right: 12px;
      width: 18px;
      height: 18px;
      opacity: 0.5;
      pointer-events: none;
    }

    /* Filtros contenedor */
    .filtros-container {
      display: flex;
      gap: 1rem;
      align-items: center;
      flex-wrap: wrap;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .header-grupos { flex-direction: column; align-items: flex-start; gap: 1rem; }
      .filtros-container { width: 100%; }
      .buscador-wrapper { width: 100%; }
      .select-programas { width: 100%; min-width: auto; }
      .btn-nuevo-grupo { width: 100%; justify-content: center; }
      #wrapTabla { max-height: 400px; overflow-y: auto; }
      #tablaGrupos th, #tablaGrupos td { padding-left: 0.75rem; padding-right: 0.75rem; }
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">

  <?php if(!defined('BASE_URL')) { 
    $base = '/senlock';
    define('BASE_URL', $base);
  } ?>

  <div class="max-w-7xl mx-auto px-4 py-10">
    <!-- Título principal -->
    <h1 class="text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]">Gestión de Grupos</h1>
    <p class="text-gray-500 mb-6">Administra los Grupos</p>

    <!-- Tarjeta principal -->
    <div class="bg-white shadow rounded-2xl border border-gray-200">
      <!-- Cabecera con título y botón nuevo grupo -->
      <div class="flex items-center justify-between p-6 text-[#9FA0A2] header-grupos">
        <div>
          <h2 class="text-xl font-semibold text-gray-800">Grupos</h2>
          <p class="text-sm text-gray-500">Lista de todos los grupos registrados</p>
        </div>
        
        <!-- Botón nuevo grupo -->
        <button id="btnAbrirModalGrupo"
          class="bg-[#0a3a57] text-white px-5 py-2.5 rounded-xl flex items-center gap-2 hover:bg-[#00304D] active:scale-[0.98] transition btn-nuevo-grupo shadow-sm"
          type="button">
          <img class="w-5 h-5" src="<?= BASE_URL ?>src/assets/img/plus.svg" alt="+" 
               onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'white\' viewBox=\'0 0 24 24\'%3E%3Cpath d=\'M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z\'/%3E%3C/svg%3E'"/>
          <span>Nuevo Grupo</span>
        </button>
      </div>

      <!-- Filtros: Select de programas y buscador -->
      <div class="px-6 py-4 border-b">
        <div class="filtros-container">
          <!-- Select de programas -->
          <div class="select-wrapper text-[#9FA0A2] ">
            <select id="filtroPrograma" class="select-programas">
              <option value="" class="text-9FA0A2">Todos los programas de formación</option>
              <option value="Desarrollo de Software">Desarrollo de Software</option>
              <option value="Análisis y Desarrollo de Sistemas">Análisis y Desarrollo de Sistemas</option>
              <option value="Gestión Administrativa">Gestión Administrativa</option>
              <option value="Contabilidad y Finanzas">Contabilidad y Finanzas</option>
              <option value="Electricidad Industrial">Electricidad Industrial</option>
            </select>
            <img class="select-icono" src="<?= BASE_URL ?>src/assets/img/chevron-down.svg" alt="▼" 
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'%23666\' viewBox=\'0 0 24 24\'%3E%3Cpath d=\'M7 10l5 5 5-5z\'/%3E%3C/svg%3E'"/>
          </div>

          <!-- Buscador -->
          <div class="buscador-wrapper">
            <img class="buscador-icono" src="<?= BASE_URL ?>src/assets/img/search.svg" alt="buscar" 
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'%23666\' viewBox=\'0 0 24 24\'%3E%3Cpath d=\'M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z\'/%3E%3C/svg%3E'"/>
            <input type="text" id="buscadorGrupo" class="buscador-input" placeholder="Buscar grupo..." />
          </div>
        </div>
      </div>

      <!-- Tabla con scroll -->
      <div id="wrapTabla" class="overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left" id="tablaGrupos">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr class="text-gray-600 text-sm">
                <th class="px-6 py-3 font-medium">Número Grupo</th>
                <th class="px-6 py-3 font-medium">Programa de formación</th>
                <th class="px-6 py-3 font-medium">Nivel</th>
                <th class="px-6 py-3 font-medium">Jornada</th>
                <th class="px-6 py-3 font-medium">Modalidad</th>
                <th class="px-6 py-3 font-medium">Líder de Grupo</th>
                <th class="px-6 py-3 font-medium text-right">Acciones</th>
              </tr>
            </thead>
            <tbody id="tbodyGrupos" class="text-sm divide-y divide-gray-100">
              <!-- Las filas se cargarán dinámicamente -->
              <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">Cargando grupos...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para crear/editar grupo -->
  <div id="modalGrupo" class="fixed inset-0 z-50 hidden">
    <div id="modalBackdrop" class="absolute inset-0 bg-black/50 backdrop-blur-[2px] opacity-0 transition-opacity duration-200"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div id="modalPanel"
        class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8 relative
               opacity-0 scale-95 translate-y-2 transition-all duration-200 ease-out">
        
        <button id="btnCerrarModalGrupo"
          class="absolute right-4 top-4 p-2 rounded-full hover:bg-gray-100 transition text-gray-500"
          type="button">✕</button>

        <div class="space-y-6">
          <div>
            <h3 class="text-2xl font-semibold" id="modalTitle">Nuevo Grupo</h3>
            <p class="text-gray-400 mt-1" id="modalSubtitle">Ingresa los datos del nuevo grupo de formación</p>
          </div>

          <form id="formNuevoGrupo" class="space-y-5">
            <input type="hidden" name="id_grupo" id="grupoId" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <!-- Número de grupo -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Número de Grupo</label>
                <input type="text" name="numero_grupo" id="numero_grupo" placeholder="Ej: 1025884321"
                  class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm
                         focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition" />
              </div>

              <!-- Programa de formación -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Programa de formación</label>
                <div class="relative">
                  <select name="programa" id="programa"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-3 pr-10 shadow-sm
                           focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition">
                    <option disabled selected value="">Seleccione un programa</option>
                    <option value="Desarrollo de Software">Desarrollo de Software</option>
                    <option value="Análisis y Desarrollo de Sistemas">Análisis y Desarrollo de Sistemas</option>
                    <option value="Gestión Administrativa">Gestión Administrativa</option>
                    <option value="Contabilidad y Finanzas">Contabilidad y Finanzas</option>
                    <option value="Electricidad Industrial">Electricidad Industrial</option>
                  </select>
                  <img src="<?= BASE_URL ?>src/assets/img/chevron-down.svg" alt="▼" 
                       class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 opacity-60"/>
                </div>
              </div>

              <!-- Nivel -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Nivel</label>
                <div class="relative">
                  <select name="nivel" id="nivel"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-3 pr-10 shadow-sm
                           focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition">
                    <option disabled selected value="">Seleccione nivel</option>
                    <option value="Tecnólogo">Tecnólogo</option>
                    <option value="Técnico">Técnico</option>
                    <option value="Especialización">Especialización</option>
                    <option value="Complementaria">Complementaria</option>
                  </select>
                  <img src="<?= BASE_URL ?>src/assets/img/chevron-down.svg" alt="▼" 
                       class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 opacity-60"/>
                </div>
              </div>

              <!-- Jornada -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Jornada</label>
                <div class="relative">
                  <select name="jornada" id="jornada"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-3 pr-10 shadow-sm
                           focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition">
                    <option disabled selected value="">Seleccione jornada</option>
                    <option value="Diurna">Diurna</option>
                    <option value="Nocturna">Nocturna</option>
                    <option value="Mixta">Mixta</option>
                    <option value="Fin de semana">Fin de semana</option>
                  </select>
                  <img src="<?= BASE_URL ?>src/assets/img/chevron-down.svg" alt="▼" 
                       class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 opacity-60"/>
                </div>
              </div>

              <!-- Modalidad -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Modalidad</label>
                <div class="relative">
                  <select name="modalidad" id="modalidad"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-3 pr-10 shadow-sm
                           focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition">
                    <option disabled selected value="">Seleccione modalidad</option>
                    <option value="Presencial">Presencial</option>
                    <option value="Virtual">Virtual</option>
                    <option value="A Distancia">A Distancia</option>
                    <option value="Mixta">Mixta</option>
                  </select>
                  <img src="<?= BASE_URL ?>src/assets/img/chevron-down.svg" alt="▼" 
                       class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 opacity-60"/>
                </div>
              </div>

              <!-- Líder de Grupo -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Líder de Grupo</label>
                <div class="relative">
                  <select name="lider" id="lider"
                    class="w-full appearance-none rounded-xl border border-gray-300 bg-white px-4 py-3 pr-10 shadow-sm
                           focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition">
                    <option disabled selected value="">Seleccione líder</option>
                    <option value="JORGE RAIGOSA">JORGE RAIGOSA</option>
                    <option value="MARIA FERNANDA LOPEZ">MARIA FERNANDA LOPEZ</option>
                    <option value="CARLOS MENDOZA">CARLOS MENDOZA</option>
                    <option value="ANA PATRICIA GOMEZ">ANA PATRICIA GOMEZ</option>
                  </select>
                  <img src="<?= BASE_URL ?>src/assets/img/chevron-down.svg" alt="▼" 
                       class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 opacity-60"/>
                </div>
              </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-4">
              <button type="button" id="btnCancelarModalGrupo"
                class="px-5 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition font-medium">
                Cancelar
              </button>
              <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-[#0a3a57] text-white hover:bg-[#00304D] transition font-medium shadow-sm">
                Crear Grupo
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // Configuración de URLs
    window.API_URL = "<?= BASE_URL ?>src/controllers/GrupoController.php";
    window.BASE_URL = "<?= BASE_URL ?>";

    // Datos de ejemplo (simulando base de datos)
    const gruposMock = [
      { id: 1, numero: '1025884321', programa: 'Desarrollo de Software', nivel: 'Tecnólogo', jornada: 'Diurna', modalidad: 'Presencial', lider: 'JORGE RAIGOSA' },
      { id: 2, numero: '1025884322', programa: 'Análisis y Desarrollo de Sistemas', nivel: 'Tecnólogo', jornada: 'Nocturna', modalidad: 'Virtual', lider: 'MARIA FERNANDA LOPEZ' },
      { id: 3, numero: '1025884323', programa: 'Gestión Administrativa', nivel: 'Técnico', jornada: 'Diurna', modalidad: 'Presencial', lider: 'CARLOS MENDOZA' },
      { id: 4, numero: '1025884324', programa: 'Contabilidad y Finanzas', nivel: 'Tecnólogo', jornada: 'Mixta', modalidad: 'Mixta', lider: 'ANA PATRICIA GOMEZ' },
      { id: 5, numero: '1025884325', programa: 'Electricidad Industrial', nivel: 'Técnico', jornada: 'Nocturna', modalidad: 'Presencial', lider: 'JORGE RAIGOSA' },
      { id: 6, numero: '1025884326', programa: 'Desarrollo de Software', nivel: 'Tecnólogo', jornada: 'Diurna', modalidad: 'Presencial', lider: 'JORGE RAIGOSA' },
      { id: 7, numero: '1025884327', programa: 'Desarrollo de Software', nivel: 'Tecnólogo', jornada: 'Diurna', modalidad: 'Presencial', lider: 'JORGE RAIGOSA' }
    ];

    // Elementos del DOM
    const tbody = document.getElementById('tbodyGrupos');
    const totalSpan = document.getElementById('totalGrupos');
    const buscador = document.getElementById('buscadorGrupo');
    const filtroPrograma = document.getElementById('filtroPrograma');
    const modal = document.getElementById('modalGrupo');
    const backdrop = document.getElementById('modalBackdrop');
    const panel = document.getElementById('modalPanel');
    const btnAbrir = document.getElementById('btnAbrirModalGrupo');
    const btnCerrar = document.getElementById('btnCerrarModalGrupo');
    const btnCancelar = document.getElementById('btnCancelarModalGrupo');
    const form = document.getElementById('formNuevoGrupo');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    const grupoId = document.getElementById('grupoId');
    const numeroGrupo = document.getElementById('numero_grupo');
    const programa = document.getElementById('programa');
    const nivel = document.getElementById('nivel');
    const jornada = document.getElementById('jornada');
    const modalidad = document.getElementById('modalidad');
    const lider = document.getElementById('lider');

    // Función para renderizar tabla
    function renderizarGrupos(grupos) {
      if (!tbody) return;
      
      if (!grupos || grupos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">No hay grupos registrados</td></tr>`;
        if (totalSpan) totalSpan.innerText = '0';
        return;
      }

      let html = '';
      grupos.forEach(g => {
        // Badges para jornada
        let badgeJornada = 'badge-jornada ';
        if (g.jornada === 'Diurna') badgeJornada += 'badge-diurna';
        else if (g.jornada === 'Nocturna') badgeJornada += 'badge-nocturna';
        else if (g.jornada === 'Mixta') badgeJornada += 'badge-mixta';
        
        html += `<tr data-id="${g.id}">
          <td class="px-6 py-4 font-mono">${g.numero}</td>
          <td class="px-6 py-4">${g.programa}</td>
          <td class="px-6 py-4">${g.nivel}</td>
          <td class="px-6 py-4"><span class="${badgeJornada}">${g.jornada}</span></td>
          <td class="px-6 py-4"><span class="badge-modalidad">${g.modalidad}</span></td>
          <td class="px-6 py-4 font-medium">${g.lider}</td>
          <td class="px-6 py-4 text-right">
            <div class="flex justify-end items-center gap-2">
              <button class="btn-accion btn-editar" data-id="${g.id}" title="Editar grupo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0a3a57" stroke-width="1.5">
                  <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
              <button class="btn-accion btn-eliminar" data-id="${g.id}" title="Eliminar grupo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#b91c1c" stroke-width="1.5">
                  <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </div>
          </td>
        </tr>`;
      });
      
      tbody.innerHTML = html;
      if (totalSpan) totalSpan.innerText = grupos.length;
      
      // Eventos a botones de editar
      document.querySelectorAll('.btn-editar').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = btn.getAttribute('data-id');
          editarGrupo(id);
        });
      });
      
      // Eventos a botones de eliminar
      document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = btn.getAttribute('data-id');
          eliminarGrupo(id);
        });
      });
    }

    // Función para filtrar grupos
    function filtrarGrupos() {
      const termino = buscador.value.toLowerCase();
      const programaSeleccionado = filtroPrograma.value;
      
      let filtrados = gruposMock;
      
      // Filtrar por programa
      if (programaSeleccionado) {
        filtrados = filtrados.filter(g => g.programa === programaSeleccionado);
      }
      
      // Filtrar por texto de búsqueda
      if (termino) {
        filtrados = filtrados.filter(g => 
          g.numero.includes(termino) || 
          g.programa.toLowerCase().includes(termino) || 
          g.lider.toLowerCase().includes(termino)
        );
      }
      
      renderizarGrupos(filtrados);
    }

    // Función para abrir modal en modo creación
    function abrirModalCrear() {
      modalTitle.innerText = 'Nuevo Grupo';
      modalSubtitle.innerText = 'Ingresa los datos del nuevo grupo de formación';
      grupoId.value = '';
      form.reset();
      
      // Mostrar modal con animación
      modal.classList.remove('hidden');
      setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
      }, 10);
    }

    // Función para editar grupo
    function editarGrupo(id) {
      const grupo = gruposMock.find(g => g.id == id);
      if (!grupo) return;
      
      modalTitle.innerText = 'Editar Grupo';
      modalSubtitle.innerText = 'Modifica los datos del grupo';
      grupoId.value = grupo.id;
      numeroGrupo.value = grupo.numero;
      programa.value = grupo.programa;
      nivel.value = grupo.nivel;
      jornada.value = grupo.jornada;
      modalidad.value = grupo.modalidad;
      lider.value = grupo.lider;
      
      modal.classList.remove('hidden');
      setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
      }, 10);
    }

    // Función para eliminar grupo
    function eliminarGrupo(id) {
      Swal.fire({
        title: '¿Eliminar grupo?',
        text: 'Esta acción no se puede revertir',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#b91c1c',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          // Aquí iría la llamada a la API
          Swal.fire('Eliminado', 'El grupo ha sido eliminado', 'success');
        }
      });
    }

    // Función para cerrar modal
    function cerrarModal() {
      backdrop.classList.add('opacity-0');
      panel.classList.add('opacity-0', 'scale-95', 'translate-y-2');
      setTimeout(() => {
        modal.classList.add('hidden');
      }, 200);
    }

    // Event Listeners
    btnAbrir.addEventListener('click', abrirModalCrear);
    btnCerrar.addEventListener('click', cerrarModal);
    btnCancelar.addEventListener('click', cerrarModal);
    
    backdrop.addEventListener('click', cerrarModal);
    
    buscador.addEventListener('input', filtrarGrupos);
    filtroPrograma.addEventListener('change', filtrarGrupos);
    
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      
      // Validación básica
      if (!numeroGrupo.value || !programa.value || !nivel.value || !jornada.value || !modalidad.value || !lider.value) {
        Swal.fire('Campos incompletos', 'Por favor completa todos los campos', 'warning');
        return;
      }
      
      // Aquí iría la llamada a la API
      Swal.fire({
        icon: 'success',
        title: grupoId.value ? 'Grupo actualizado' : 'Grupo creado',
        text: grupoId.value ? 'El grupo se ha actualizado correctamente' : 'El grupo se ha creado correctamente',
        timer: 2000,
        showConfirmButton: false
      });
      
      cerrarModal();
    });

    // Inicializar tabla
    renderizarGrupos(gruposMock);
  </script>
</body>
</html>