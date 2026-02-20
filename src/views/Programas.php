<?php
/* ==========================================
   VISTA DE PROGRAMAS
   ========================================== */
?>
<!-- ========== PROGRAMAS ========== -->
<section data-tab="programs" class="tab-pane mt-8 hidden">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <div>
            <h2 class="text-3xl text-[#39a900] font-bold">Programas de Formación</h2>
            <p class="text-sm text-zinc-500">Gestione los programas de formación disponibles</p>
        </div>
        <button id="btnNewProgram" class="rounded-xl px-4 py-2 text-sm font-medium flex items-center gap-2 bg-[#0a3a57] text-[#fff] whitespace-nowrap">
            <img src="src/assets/img/plus.svg" class="w-4 h-4" alt="signo de mas"> Nuevo Programa
        </button>
        <div class="flex items-center gap-3 flex-wrap">
            <!-- FILTRO POR TIPO DE PROGRAMA -->
            <select id="programTypeFilter" class="w-full sm:w-48 border border-zinc-300 rounded-xl px-3 py-2 text-sm bg-white select-nice">
                <option value="all">Todos los tipos de programa</option>
                <option value="tecnico">Técnico</option>
                <option value="tecnologo">Tecnólogo</option>
                <option value="especializacion">Especialización</option>
            </select>
            <!-- BUSCADOR POR NOMBRE O CÓDIGO -->
            <input
                id="programSearchInput"
                type="text"
                placeholder="Buscar por nombre o código"
                class="w-full sm:w-72 border border-zinc-300 rounded-xl px-3 py-2 text-sm outline-none placeholder-zinc-400"
            >
        </div>
    </div>

    <!-- Grid de Programas (vacío inicialmente - JS lo llena) -->
    <div id="programsGrid" class="space-y-4">
        <!-- Los programas se cargarán dinámicamente con JavaScript -->
    </div>

    <!-- Mensaje cuando no hay programas -->
    <div id="programsEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm p-12 text-center text-zinc-500">
        No hay programas que coincidan con los filtros seleccionados.
    </div>

    <!-- PAGINACIÓN PROGRAMAS -->
    <div id="programsPagination" class="flex items-center justify-center mt-6 gap-2">
        <button id="pgPrev" class="w-10 h-10 rounded-xl border border-zinc-300 flex items-center justify-center text-zinc-600 hover:bg-zinc-50 transition disabled:opacity-40" disabled>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6" />
            </svg>
        </button>
        <span class="text-sm text-zinc-600 mx-2">Página 1 de 1</span>
        <button id="pgNext" class="w-10 h-10 rounded-xl border border-zinc-300 flex items-center justify-center text-zinc-600 hover:bg-zinc-50 transition">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6" />
            </svg>
        </button>
    </div>
</section>

<!-- ===== MODAL: Nuevo/Editar Programa ===== -->
<div id="modalProgramBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
<section id="modalProgram" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl" style="border:1px solid #e5e7eb">
        <div class="flex items-start justify-between p-6 pb-2">
            <div>
                <h2 id="modalProgramTitle" class="text-2xl font-bold text-zinc-900">
                    Nuevo Programa
                </h2>
                <p class="text-sm text-zinc-500">Complete la información del programa de formación</p>
            </div>
            <button id="btnCloseProgram" class="p-2 rounded-lg hover:bg-zinc-100">X</button>
        </div>

        <form id="formProgramNew" class="p-6 pt-4 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Código *</label>
                <input id="pg_code" name="id_programa" type="text"
                      placeholder="Ej: 228106"
                      class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Nombre *</label>
                <input id="pg_name" type="text"
                      placeholder="Ej: Análisis y Desarrollo de Software (ADSI)"
                      class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Descripción</label>
                <textarea id="pg_desc" rows="3"
                          placeholder="Ej: Programa orientado al diseño y desarrollo de aplicaciones empresariales."
                          class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Duración (horas)</label>
                <input id="pg_hours" type="number" min="0"
                      placeholder="Ej: 2640"
                      class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" id="btnCancelProgram"
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium">
                    Cancelar
                </button>
                <button type="submit" id="btnSubmitProgram"
                        class="rounded-xl px-4 py-2.5 text-sm font-medium bg-[#0a3a57] text-[#fff]">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</section>