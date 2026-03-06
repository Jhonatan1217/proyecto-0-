<?php
/* ==========================================
   VISTA DE COMPETENCIAS
   ========================================== */
?>
<!-- ========== COMPETENCIAS ========== -->
<section data-tab="competencies" class="tab-pane hidden mt-8">
    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold" style="color:#39a900">Competencias</h2>
            <p class="text-sm text-zinc-500">Visualice y edite las competencias cargadas</p>
        </div>

        <?php if (($_SESSION['usuario_cargo'] ?? '') !== 'INSTRUCTOR'): ?>
        <button id="btnNewCompetency" class="w-full sm:w-auto rounded-xl px-4 py-2 text-sm font-medium flex items-center justify-center gap-2 bg-[#0a3a57] text-[#fff]">
            <img src="src/assets/img/plus.svg" class="w-4 h-4" alt="icono añadir">
            Nueva Competencia
        </button>
        <?php endif; ?>
    </div>

        <div class="flex flex-col w-min gap-3 sm:flex-row sm:flex-wrap md:flex-nowrap md:justify-end competencias-filtros-wrapper">
            <select id="competencyProgramFilter" class="w-full sm:w-60 border border-zinc-300 rounded-xl px-3 py-2 text-sm select-nice">
                <option value="all">Todos los programas</option>
            </select>
            
            <input id="competencySearch" type="text" placeholder="Buscar por nombre o código"
            class="w-full sm:w-72 border border-zinc-300 rounded-xl px-3 py-2 text-sm outline-none placeholder-zinc-400">
            
        </div>

    <!-- Lista de competencias (vacía inicialmente) -->
    <div id="competenciesList" class="space-y-5"></div>
    
    <!-- Mensaje cuando no hay competencias -->
    <div id="competenciesEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm">
        <div class="py-12 text-center text-zinc-500">
            No hay competencias que coincidan con el filtro seleccionado.
        </div>
    </div>

    <!-- PAGINACIÓN COMPETENCIAS -->
    <div id="competenciasPagination" class="flex items-center justify-center mt-6 gap-2 hidden">
        <button id="cpPrev" class="px-4 py-2 rounded-xl border border-zinc-300 bg-white text-sm hover:bg-zinc-100 disabled:opacity-40"><</button>
        <div class="flex items-center gap-3">
            <span id="cpInfo" class="text-sm text-zinc-600"></span>
        </div>
        <button id="cpNext" class="px-4 py-2 rounded-xl border border-zinc-300 bg-white text-sm hover:bg-zinc-100 disabled:opacity-40">></button>
    </div>
</section>

<!-- ===== MODAL: Nueva/Editar Competencia ===== -->
<div id="modalCompetencyBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
<section id="modalCompetency" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl modal-card" style="border:1px solid #e5e7eb">
        <div class="flex items-start justify-between p-6 pb-2">
            <div>
                <h3 id="titleCompetency" class="text-2xl font-bold">Nueva Competencia</h3>
                <p class="text-sm text-zinc-500">Complete la información de la competencia</p>
            </div>
            <button id="btnCloseCompetency" class="p-2 rounded-lg hover:bg-zinc-100">X</button>
        </div>

        <form id="formCompetencyNew" class="p-6 pt-4 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Programa *</label>
                <select id="cp_program" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm bg-white select-nice">
                    <option value="">Seleccione el programa de formación asociado</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Código *</label>
                <input id="cp_code" type="text" placeholder="Ej: 220501046"
                       class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Nombre *</label>
                <input id="cp_name" type="text" placeholder="Ej: Desarrollar software aplicando metodologías ágiles"
                       class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" id="btnCancelCompetency" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium">Cancelar</button>
                <button type="submit" id="btnSubmitCompetency" class="rounded-xl px-4 py-2.5 text-sm font-medium bg-[#0a3a57] text-[#fff]">Guardar</button>
            </div>
        </form>
    </div>
</section>