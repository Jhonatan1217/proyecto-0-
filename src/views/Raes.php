<?php
/* ==========================================
   VISTA DE RAES
   ========================================== */
?>
<!-- ========== RAES ========== -->
<section data-tab="raes" class="tab-pane mt-8 hidden">
    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <div>
            <h2 class="text-3xl text-[#39a900] font-bold">Resultados de Aprendizaje Esperados (RAE)</h2>
            <p class="text-sm text-zinc-500">Visualice y edite los RAE cargados</p>
        </div>
        <button id="btnNewRae" class="rounded-xl px-4 py-2 text-sm font-medium flex items-center gap-2 bg-[#0a3a57] text-[#fff]">
            <img src="src/assets/img/plus.svg" class="w-4 h-4" alt="icono añadir"> Nuevo RAE
        </button>
    </div>

    <div class="flex gap-3 flex-wrap mb-5">
        <select id="raeProgramFilter" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm bg-white select-nice">
            <option value="all">Todos los programas</option>
        </select>
        <select id="raeCompetencyFilter" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm bg-white select-nice">
            <option value="all">Todas las competencias</option>
        </select>
    </div>

    <!-- Lista de RAEs -->
    <div id="raesList" class="space-y-4"></div>
    
    <!-- Mensaje cuando no hay RAEs -->
    <div id="raesEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm">
        <div class="py-12 text-center text-zinc-500">No hay RAE que coincidan con los filtros seleccionados.</div>
    </div>

    <!-- PAGINACIÓN RAE -->
    <div id="raePagination" class="flex items-center justify-center mt-6 gap-2 hidden">
        <button id="raePrev" class="px-4 py-2 rounded-xl border border-zinc-300 bg-white text-sm hover:bg-zinc-100 disabled:opacity-40"><</button>
        <div class="flex items-center gap-3">
            <span id="raeInfo" class="text-sm text-zinc-600"></span>
        </div>
        <button id="raeNext" class="px-4 py-2 rounded-xl border border-zinc-300 bg-white text-sm hover:bg-zinc-100 disabled:opacity-40">></button>
    </div>
</section>

<!-- ===== MODAL: Nuevo/Editar RAE ===== -->
<div id="modalRaeBackdrop" class="hidden fixed inset-0 z-40" style="background:rgba(0,0,0,.4)"></div>
<section id="modalRae" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl" style="border:1px solid #e5e7eb">
        <div class="flex items-start justify-between p-6 pb-2">
            <div>
                <h3 id="modalRaeTitle" class="text-2xl font-bold">Nuevo RAE</h3>
                <p class="text-sm text-zinc-500">Complete la información del Resultado de Aprendizaje Esperado</p>
            </div>
            <button id="btnCloseRae" class="p-2 rounded-lg hover:bg-zinc-100" aria-label="Cerrar modal">✕</button>
        </div>

        <form id="formRaeNew" class="p-6 pt-4 space-y-4">
            <!-- PROGRAMA DE FORMACIÓN (nuevo) -->
            <div>
                <label class="block text-sm font-medium mb-1">Programa de formación *</label>
                <select id="rae_program" class="select-nice w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm bg-white outline-none">
                    <option value="">Seleccione programa</option>
                </select>
            </div>

            <!-- COMPETENCIA -->
            <div>
                <label class="block text-sm font-medium mb-1">Competencia *</label>
                <select id="rae_competency" class="select-nice w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm bg-white outline-none" disabled>
                    <option value="">Primero seleccione un programa</option>
                </select>
            </div>

            <!-- CÓDIGO RAE -->
            <div>
                <label class="block text-sm font-medium mb-1">Código RAE *</label>
                <input id="rae_code" type="text" placeholder="Ej: 220501032-01"
                       class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:border-[#0a3a57] focus:ring-1 focus:ring-[#0a3a57] transition">
            </div>

            <!-- DESCRIPCIÓN -->
            <div>
                <label class="block text-sm font-medium mb-1">Descripción *</label>
                <textarea id="rae_desc" rows="3" placeholder="Describe el resultado de aprendizaje..."
                          class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:border-[#0a3a57] focus:ring-1 focus:ring-[#0a3a57] transition resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" id="btnCancelRae" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium hover:bg-zinc-50 transition">Cancelar</button>
                <button type="submit" id="btnSubmitRae" class="rounded-xl px-4 py-2.5 text-sm font-medium bg-[#0a3a57] text-white hover:bg-[#052433] transition">Crear RAE</button>
            </div>
        </form>
    </div>
</section>

<style>
/* Switch styles */
.switch {
    font-size: 0;
}
.switch .dot {
    transition: transform 0.2s ease;
}
/* Scroll personalizado */
.custom-scroll::-webkit-scrollbar {
    width: 4px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #0a3a57;
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: #052433;
}
</style>