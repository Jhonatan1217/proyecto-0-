<?php

$cargo = $_SESSION['cargo'] ?? '';

if ($cargo === 'INSTRUCTOR') {
    header("Location: index.php?page=register_tables");
    exit;
}

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
                <div class="relative w-full md:w-64 shrink-0">
                <select id="upload_program" class="combobox-academicos-upload w-full">
                    <option value="">Seleccione un programa</option>
                    <?php foreach($programas as $p): ?>
                        <option value="<?= $p['id_programa'] ?>">
                            <?= $p['nombre_programa'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </div>

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
<script>
document.addEventListener("DOMContentLoaded", function () {

    const btn = document.getElementById("btnProcesarExcel");
    const input = document.getElementById("inputExcel");
    const select = document.getElementById("upload_program");
    const errorMsg = document.getElementById("err_upload_program");
    const fileName = document.getElementById("file-name");

    // ===============================
    // 🔄 RECARGAR PROGRAMAS AUTOMÁTICAMENTE
    // ===============================
    function recargarProgramas() {
        fetch("src/controllers/ProgramasController.php?accion=listar", {
            credentials: "same-origin"
        })
        .then(res => res.json())
        .then(data => {

            select.innerHTML = '<option value="">Seleccione un programa</option>';

            if (Array.isArray(data)) {
                data.forEach(p => {
                    const option = document.createElement("option");
                    option.value = p.id_programa;
                    option.textContent = p.nombre_programa;
                    select.appendChild(option);
                });
            }

            select.dispatchEvent(new Event("change", { bubbles: true }));

        })
        .catch(err => console.error("Error recargando programas:", err));
    }

    // 🔥 Se ejecuta apenas carga la vista
    recargarProgramas();

    // ===============================
    // Mostrar nombre del archivo
    // ===============================
    input.addEventListener("change", function () {
        if (this.files.length > 0) {
            fileName.textContent = this.files[0].name;
            fileName.classList.remove("hidden");
        }
    });

    // ===============================
    // SUBIR EXCEL
    // ===============================
    btn.addEventListener("click", function (e) {
        e.preventDefault();

        const archivo = input.files[0];
        const programa = select.value;

        if (!programa) {
            errorMsg.classList.remove("hidden");
            return;
        } else {
            errorMsg.classList.add("hidden");
        }

        if (!archivo) {
            alert("Seleccione un archivo Excel");
            return;
        }

        const formData = new FormData();
        formData.append("archivo", archivo);
        formData.append("programa", programa);

        btn.disabled = true;
        btn.innerText = "Procesando...";

        fetch("src/controllers/EtlController.php?accion=subir", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            btn.disabled = false;
            btn.innerText = "Subir y Procesar";

            if (data.success) {
                alert(
                    "Importación completada\n\n" +
                    "Competencias: " + data.competencias + "\n" +
                    "RAE: " + data.raes
                );
            } else {
                alert(data.error);
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerText = "Subir y Procesar";
            console.error(error);
            alert("Error en la petición");
        });

    });

});
</script>