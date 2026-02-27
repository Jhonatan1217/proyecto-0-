<?php
/* ==========================================
   VISTA DE CARGA EXCEL
   ========================================== */
?>
<!-- ========== CARGA EXCEL ========== -->
<section data-tab="upload" class="tab-pane hidden mt-8">
    <h2 class="text-3xl font-bold mb-1" style="color:#39a900">Carga Masiva desde Excel</h2>
    <p class="text-sm text-zinc-500 mb-6">Importe programas, competencias y RAE desde un archivo Excel</p>

    <div class="max-w-8xl">
        <div class="rounded-2xl ring-1 ring-zinc-200 shadow-sm overflow-hidden bg-white">
            <div class="px-6 pt-6">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <img src="src/assets/img/upload.svg" class="w-4 h-4"> Subir Archivo
                </h3>
                <p class="text-sm text-zinc-500">Seleccione un archivo Excel (.xlsx) para importar</p>
            </div>

            <div class="px-6 mt-4">
                <label class="block text-sm font-medium mb-1">Programa de formación <span class="text-red-500">*</span></label>
                <select id="upload_program" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none bg-white select-nice">
                    <option value="">Seleccione un programa</option>
                    <?php foreach($programas as $p): ?>
                        <option value="<?= $p['id_programa'] ?>">
                            <?= $p['nombre_programa'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <p id="err_upload_program" class="hidden mt-1 text-xs" style="color:#dc2626">Seleccione un programa para asociar la carga.</p>
            </div>

            <div class="px-6 pb-6 space-y-4 mt-4">
                <label class="flex h-36 w-full cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50">
                    <div class="flex flex-col items-center text-center">
                        <img src="src/assets/img/upload-white.svg" class="w-4 h-4 ">
                        <p class="mt-2 text-sm text-zinc-500">Click para seleccionar archivo</p>
                        <span id="file-name" class="text-base text-gray-700 mt-2 hidden"></span>
                    </div>
                    <input type="file" id="inputExcel" name="archivo" class="hidden" accept=".xlsx,.xls" required />
                </label>
                <button id="btnProcesarExcel" class="w-full rounded-xl" style="background:#0a3a57;color:#fff;padding:.65rem 1rem;font-size:.875rem;font-weight:500">
                    Subir y Procesar
                </button>
            </div>
        </div>
    </div>
</section>