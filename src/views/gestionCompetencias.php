<?php
/* ==============================
   DATOS ORIGINALES (sin modificar)
   ============================== */
$PROGRAMS = [ ["id"=>1,"code"=>"228106","name"=>"Análisis y Desarrollo de Sistemas de Información (ADSI)","description"=>"Programa de formación tecnológica enfocado en el desarrollo de software y sistemas de información empresariales.","duration_hours"=>2640,"active"=>true], ["id"=>2,"code"=>"123450","name"=>"Contabilidad y Finanzas","description"=>"Formación integral en procesos contables, financieros y tributarios para organizaciones.","duration_hours"=>2200,"active"=>true], ["id"=>3,"code"=>"122115","name"=>"Gestión Administrativa","description"=>"Programa orientado a la gestión de procesos administrativos y talento humano en las organizaciones.","duration_hours"=>1980,"active"=>false], ["id"=>4,"code"=>"228120","name"=>"Programación de Software","description"=>"Formación especializada en desarrollo de aplicaciones y soluciones tecnológicas.","duration_hours"=>2200,"active"=>true], ];

$COMPETENCIES = [
  [
    "id"=>1,"program_id"=>1,"code"=>"220501046",
    "name"=>"Desarrollar el sistema que cumpla con los requisitos de la solución informática",
    "description"=>"Construir el sistema de información que cumpla con los requisitos de la solución informática, aplicando buenas prácticas de programación.",
    "program_name"=>"Análisis y Desarrollo de Sistemas de Información (ADSI)","program_code"=>"228106","active"=>true,
    "raes"=>[
      ["id"=>1,"code"=>"220501046-01","description"=>"Construir la interfaz de usuario de acuerdo con el diseño y las tecnologías seleccionadas"],
      ["id"=>2,"code"=>"220501046-02","description"=>"Desarrollar la lógica de negocio según el diseño y las tecnologías de información"],
      ["id"=>3,"code"=>"220501046-03","description"=>"Implementar la persistencia de datos según el diseño y las tecnologías seleccionadas"],
    ],
  ],
  [
    "id"=>2,"program_id"=>1,"code"=>"220501047",
    "name"=>"Realizar mantenimiento de la solución informática de acuerdo con las políticas establecidas",
    "description"=>"Ejecutar el mantenimiento correctivo, preventivo y evolutivo de la solución informática según las políticas y procedimientos establecidos.",
    "program_name"=>"Análisis y Desarrollo de Sistemas de Información (ADSI)","program_code"=>"228106","active"=>true,
    "raes"=>[
      ["id"=>4,"code"=>"220501047-01","description"=>"Realizar el mantenimiento correctivo de la solución informática según procedimientos establecidos"],
      ["id"=>5,"code"=>"220501047-02","description"=>"Ejecutar el mantenimiento preventivo de la solución informática de acuerdo con las políticas"],
    ],
  ],
  [
    "id"=>3,"program_id"=>2,"code"=>"210101001",
    "name"=>"Contabilizar los recursos de operación, inversión y financiación",
    "description"=>"Registrar y clasificar las operaciones contables de acuerdo con las normas vigentes y los principios de contabilidad.",
    "program_name"=>"Contabilidad y Finanzas","program_code"=>"123450","active"=>false,
    "raes"=>[
      ["id"=>6,"code"=>"210101001-01","description"=>"Registrar las transacciones contables según las normas y principios vigentes"],
      ["id"=>7,"code"=>"210101001-02","description"=>"Clasificar y codificar las cuentas contables de acuerdo con el plan único de cuentas"],
    ],
  ],
  [
    "id"=>4,"program_id"=>3,"code"=>"210601010",
    "name"=>"Facilitar el servicio a los clientes internos y externos",
    "description"=>"Proporcionar atención y servicio de calidad a clientes internos y externos según protocolos establecidos.",
    "program_name"=>"Gestión Administrativa","program_code"=>"122115","active"=>true,
    "raes"=>[
      ["id"=>8,"code"=>"210601010-01","description"=>"Atender a los clientes de acuerdo con los protocolos de servicio establecidos"],
      ["id"=>9,"code"=>"210601010-02","description"=>"Gestionar las solicitudes y requerimientos según procedimientos organizacionales"],
    ],
  ],
  [
    "id"=>5,"program_id"=>4,"code"=>"220501048",
    "name"=>"Implementar la estructura de la base de datos",
    "description"=>"Construir la base de datos a partir del modelo de datos diseñado, aplicando las mejores prácticas.",
    "program_name"=>"Programación de Software","program_code"=>"228120","active"=>true,
    "raes"=>[
      ["id"=>10,"code"=>"220501048-01","description"=>"Crear la estructura de la base de datos según el modelo de datos diseñado"],
      ["id"=>11,"code"=>"220501048-02","description"=>"Implementar los procedimientos almacenados y triggers según requerimientos"],
    ],
  ],
  [
    "id"=>6,"program_id"=>4,"code"=>"220501049",
    "name"=>"Desarrollar aplicaciones móviles",
    "description"=>"Construir aplicaciones para dispositivos móviles aplicando las tecnologías y frameworks actuales.",
    "program_name"=>"Programación de Software","program_code"=>"228120","active"=>true,
    "raes"=>[
      ["id"=>12,"code"=>"220501049-01","description"=>"Desarrollar interfaces de usuario para dispositivos móviles según diseño"],
    ],
  ],
];

$RAES = [
  ["id"=>1,"competency_id"=>1,"code"=>"220501046-01","description"=>"Construir la interfaz de usuario de acuerdo con el diseño y las tecnologías seleccionadas","competency_code"=>"220501046","competency_name"=>"Desarrollar el sistema que cumpla con los requisitos de la solución informática","program_name"=>"ADSI","active"=>true],
  ["id"=>2,"competency_id"=>1,"code"=>"220501046-02","description"=>"Desarrollar la lógica de negocio según el diseño y las tecnologías de información","competency_code"=>"220501046","competency_name"=>"Desarrollar el sistema que cumpla con los requisitos de la solución informática","program_name"=>"ADSI","active"=>false],
  ["id"=>3,"competency_id"=>1,"code"=>"220501046-03","description"=>"Implementar la persistencia de datos según el diseño y las tecnologías seleccionadas","competency_code"=>"220501046","competency_name"=>"Desarrollar el sistema que cumpla con los requisitos de la solución informática","program_name"=>"ADSI","active"=>false],
  ["id"=>4,"competency_id"=>2,"code"=>"220501047-01","description"=>"Realizar el mantenimiento correctivo de la solución informática según procedimientos establecidos","competency_code"=>"220501047","competency_name"=>"Realizar mantenimiento de la solución informática","program_name"=>"ADSI","active"=>true],
  ["id"=>5,"competency_id"=>2,"code"=>"220501047-02","description"=>"Ejecutar el mantenimiento preventivo de la solución informática de acuerdo con las políticas","competency_code"=>"220501047","competency_name"=>"Realizar mantenimiento de la solución informática","program_name"=>"ADSI","active"=>true],
  ["id"=>6,"competency_id"=>3,"code"=>"210101001-01","description"=>"Registrar las transacciones contables según las normas y principios vigentes","competency_code"=>"210101001","competency_name"=>"Contabilizar los recursos de operación, inversión y financiación","program_name"=>"Contabilidad y Finanzas","active"=>true],
  ["id"=>7,"competency_id"=>3,"code"=>"210101001-02","description"=>"Clasificar y codificar las cuentas contables de acuerdo con el plan único de cuentas","competency_code"=>"210101001","competency_name"=>"Contabilizar los recursos de operación, inversión y financiación","program_name"=>"Contabilidad y Finanzas","active"=>false],
  ["id"=>8,"competency_id"=>4,"code"=>"210601010-01","description"=>"Atender a los clientes de acuerdo con los protocolos de servicio establecidos","competency_code"=>"210601010","competency_name"=>"Facilitar el servicio a los clientes internos y externos","program_name"=>"Gestión Administrativa","active"=>true],
  ["id"=>9,"competency_id"=>4,"code"=>"210601010-02","description"=>"Gestionar las solicitudes y requerimientos según procedimientos organizacionales","competency_code"=>"210601010","competency_name"=>"Facilitar el servicio a los clientes internos y externos","program_name"=>"Gestión Administrativa","active"=>true],
  ["id"=>10,"competency_id"=>5,"code"=>"220501048-01","description"=>"Crear la estructura de la base de datos según el modelo de datos diseñado","competency_code"=>"220501048","competency_name"=>"Implementar la estructura de la base de datos","program_name"=>"Programación de Software","active"=>true],
  ["id"=>11,"competency_id"=>5,"code"=>"220501048-02","description"=>"Implementar los procedimientos almacenados y triggers según requerimientos","competency_code"=>"220501048","competency_name"=>"Implementar la estructura de la base de datos","program_name"=>"Programación de Software","active"=>true],
  ["id"=>12,"competency_id"=>6,"code"=>"220501049-01","description"=>"Desarrollar interfaces de usuario para dispositivos móviles según diseño","competency_code"=>"220501049","competency_name"=>"Desarrollar aplicaciones móviles","program_name"=>"Programación de Software","active"=>true],
];

function php_to_js($arr){ return json_encode($arr, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Sistema de Gestión Académica</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .switch{--h:22px;--w:42px;position:relative;width:var(--w);height:var(--h);border-radius:999px;background:#e5e7eb;transition:.2s}
    .switch input{display:none}
    .switch .dot{position:absolute;inset:3px auto auto 3px;width:16px;height:16px;border-radius:999px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.15);transition:.2s}
    .switch input:checked + .dot{transform:translateX(20px)}
    .tabs-pill-active{background:#fff;border:1px solid #e5e7eb;box-shadow:0 1px 0 rgba(0,0,0,.04)}
    .rotate-90{transform:rotate(90deg)}
    .switch .track{transition:background-color .2s ease}

    /* ======= MODO SIN PROGRAMAS (no borra base, solo oculta) ======= */
    .no-programs [data-role="programs-only"]{display:none !important;}
    .no-programs [data-role="program-chip"]{display:none !important;}    /* chips que muestran nombre de programa */
  </style>
</head>
<body class="bg-white text-zinc-900 min-h-screen">

  <main class="max-w-7xl mx-auto px-4 py-8">
    <div class="w-full">
      <div class="bg-zinc-100 rounded-2xl p-1 flex items-center gap-1 justify-around">
        <button data-tab-btn="upload" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <i data-lucide="upload" class="w-4 h-4"></i><span class="hidden sm:inline">Carga Excel</span>
        </button>
        <!-- Botón Programas permanece pero se oculta con .no-programs -->
        <button data-tab-btn="programs" data-role="programs-only" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <i data-lucide="graduation-cap" class="w-4 h-4"></i><span class="hidden sm:inline">Programas</span>
        </button>
        <button data-tab-btn="competencies" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <i data-lucide="book-open" class="w-4 h-4"></i><span class="hidden sm:inline">Competencias</span>
        </button>
        <button data-tab-btn="raes" class="tab-btn flex items-center justify-center gap-2 px-4 py-2 rounded-xl w-full sm:w-auto text-zinc-700">
          <i data-lucide="target" class="w-4 h-4"></i><span class="hidden sm:inline">RAE</span>
        </button>
      </div>

      <!-- Upload -->
      <section data-tab="upload" class="tab-pane mt-8">
        <h2 class="text-3xl font-bold mb-1 text-[#39a900]">Carga Masiva desde Excel</h2>
        <p class="text-sm text-zinc-500 mb-6">Importe programas, competencias y RAE desde un archivo Excel</p>
        <div class="max-w-8xl">
          <div class="rounded-2xl ring-1 ring-zinc-200 shadow-sm overflow-hidden">
            <div class="px-6 pt-6">
              <h3 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="upload" class="w-5 h-5"></i> Subir Archivo
              </h3>
              <p class="text-sm text-zinc-500">Seleccione un archivo Excel (.xlsx) para importar</p>
            </div>

            <!-- ========= NUEVO: selector de Programa arriba del cargador ========= -->
            <div class="px-6 mt-4">
              <label class="block text-sm font-medium mb-1">Programa de formación <span class="text-red-500">*</span></label>
              <select id="upload_program" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none bg-white focus:ring-4 focus:ring-zinc-200">
                <option value="">Seleccione un programa</option>
                <?php foreach($PROGRAMS as $p): ?>
                  <option value="<?= (int)$p['id'] ?>" data-code="<?= htmlspecialchars($p['code']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <p id="err_upload_program" class="hidden mt-1 text-xs text-red-600">Seleccione un programa para asociar la carga.</p>
            </div>
            <!-- ================================================================== -->

            <div class="px-6 pb-6 space-y-4 mt-4">
              <label class="flex h-36 w-full cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 hover:bg-zinc-100 transition">
                <div class="text-center">
                  <i data-lucide="upload" class="mx-auto h-8 w-8 text-zinc-400"></i>
                  <p class="mt-2 text-sm text-zinc-500">Click para seleccionar archivo</p>
                </div>
                <input type="file" class="hidden" />
              </label>
              <button class="w-full rounded-xl  text-white py-2.5 text-sm font-medium  bg-[#00324d]">Subir y Procesar</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Programs (se mantiene pero se oculta y no se ejecuta) -->
      <section data-tab="programs" data-role="programs-only" class="tab-pane mt-8 hidden">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-3xl font-bold">Programas de Formación</h2>
            <p class="text-sm text-zinc-500">Gestione los programas de formación disponibles</p>
          </div>
          <button class="rounded-xl bg-zinc-900 text-white px-4 py-2 text-sm font-medium hover:bg-black flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Nuevo Programa
          </button>
        </div>
        <div id="programsGrid" class="grid gap-5 md:grid-cols-2 lg:grid-cols-3"></div>
        <div id="programsEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm"></div>
      </section>

      <!-- Competencies -->
      <section data-tab="competencies" class="tab-pane mt-8 hidden">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
          <div>
            <h2 class="text-3xl font-bold text-[#39a900]">Competencias</h2>
            <p class="text-sm text-zinc-500">Visualice y edite las competencias cargadas desde Excel</p>
          </div>
          
          <div class="flex items-center gap-3">
            <!-- Filtro por programa (se oculta si no-programs) -->
            <select id="competencyProgramFilter" data-role="program-field" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm">
              <option value="all">Todos los programas</option>
              <?php foreach($PROGRAMS as $p): ?>
                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; ?>
            </select>

            <button class="rounded-xl bg-[#00324d] text-white px-4 py-2 text-sm font-medium hover:bg-[#00263a] flex items-center gap-2 transition">
              <img src="src/assets/img/plus.svg" alt="Icono añadir" class="w-4 h-4">
              Nueva Competencia
            </button>
          </div>
        </div>

        <div id="competenciesList" class="space-y-5"></div>
        <div id="competenciesEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm">
          <div class="py-12 text-center text-zinc-500">No hay competencias que coincidan con el filtro seleccionado.</div>
        </div>
      </section>

      <!-- RAEs -->
      <section data-tab="raes" class="tab-pane mt-8 hidden">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
          <div>
            <h2 class="text-3xl font-bold">Resultados de Aprendizaje Esperados (RAE)</h2>
            <p class="text-sm text-zinc-500">Visualice y edite los RAE cargados desde Excel</p>
          </div>
        <button class="rounded-xl bg-zinc-900 text-white px-4 py-2 text-sm font-medium hover:bg-black flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Nuevo RAE
          </button>
        </div>

        <div class="flex gap-3 flex-wrap mb-5">
          <select id="raeProgramFilter" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm">
            <option value="all">Todos los programas</option>
            <?php
              $programNames = array_values(array_unique(array_filter(array_map(fn($r)=>$r['program_name']??null,$RAES))));
              foreach($programNames as $pn): ?>
              <option value="<?= htmlspecialchars($pn) ?>"><?= htmlspecialchars($pn) ?></option>
            <?php endforeach; ?>
          </select>
          <select id="raeCompetencyFilter" class="w-[260px] border border-zinc-300 rounded-xl px-3 py-2 text-sm">
            <option value="all">Todas las competencias</option>
            <?php
              $competencyCodes = array_values(array_unique(array_filter(array_map(fn($r)=>$r['competency_code']??null,$RAES))));
              foreach($competencyCodes as $cc): ?>
              <option value="<?= htmlspecialchars($cc) ?>"><?= htmlspecialchars($cc) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div id="raesList" class="space-y-4"></div>
        <div id="raesEmpty" class="hidden rounded-2xl ring-1 ring-zinc-200 shadow-sm">
          <div class="py-12 text-center text-zinc-500">No hay RAE que coincidan con los filtros seleccionados.</div>
        </div>
      </section>
    </div>
  </main>

  <!-- MODAL: Nuevo Programa (se conserva pero se oculta) -->
  <div id="modalProgramBackdrop" data-role="programs-only" class="hidden fixed inset-0 z-40 bg-black/40"></div>
  <section id="modalProgram" data-role="programs-only" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-zinc-200">
      <div class="flex items-start justify-between p-6 pb-2">
        <div>
          <h3 class="text-2xl font-bold">Nuevo Programa</h3>
          <p class="text-sm text-zinc-500">Complete la información del programa de formación</p>
        </div>
        <button id="btnCloseProgram" class="p-2 rounded-lg hover:bg-zinc-100" title="Cerrar">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <form id="formProgramNew" class="p-6 pt-4 space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Código <span class="text-red-500">*</span></label>
          <input id="pg_code" type="text" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:ring-4 focus:ring-zinc-200" placeholder="228106">
          <p id="err_code" class="hidden mt-1 text-xs text-red-600">El código es obligatorio.</p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Nombre <span class="text-red-500">*</span></label>
          <input id="pg_name" type="text" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:ring-4 focus:ring-zinc-200" placeholder="Nombre del programa">
          <p id="err_name" class="hidden mt-1 text-xs text-red-600">El nombre es obligatorio.</p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Descripción</label>
          <textarea id="pg_desc" rows="3" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:ring-4 focus:ring-zinc-200" placeholder="Descripción breve"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Duración (horas)</label>
          <input id="pg_hours" type="number" min="0" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:ring-4 focus:ring-zinc-200" placeholder="2200">
          <p id="err_hours" class="hidden mt-1 text-xs text-red-600">Ingrese un valor numérico válido (0 o más).</p>
        </div>
        <div class="flex items-center justify-end gap-3 pt-2">
          <button type="button" id="btnCancelProgram" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium hover:bg-zinc-50">Cancelar</button>
          <button type="submit" class="rounded-xl bg-zinc-900 text-white px-4 py-2.5 text-sm font-medium hover:bg-black" id="btnSubmitProgram">Guardar</button>
        </div>
      </form>
    </div>
  </section>

  <!-- MODAL: Nueva Competencia (campo Programa se oculta si no-programs) -->
  <div id="modalCompetencyBackdrop" class="hidden fixed inset-0 z-40 bg-black/40"></div>
  <section id="modalCompetency" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-zinc-200">
      <div class="flex items-start justify-between p-6 pb-2">
        <div>
          <h3 class="text-2xl font-bold">Nueva Competencia</h3>
          <p class="text-sm text-zinc-500">Complete la información de la competencia</p>
        </div>
        <button id="btnCloseCompetency" class="p-2 rounded-lg hover:bg-zinc-100" title="Cerrar">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form id="formCompetencyNew" class="p-6 pt-4 space-y-4">
        <div data-role="program-field">
          <label class="block text-sm font-medium mb-1">Programa <span class="text-red-500">*</span></label>
          <select id="cp_program" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none bg-white focus:ring-4 focus:ring-zinc-200">
            <option value="">Seleccione un programa</option>
            <?php foreach($PROGRAMS as $p): ?>
              <option value="<?= (int)$p['id'] ?>" data-code="<?= htmlspecialchars($p['code']) ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <p id="err_cprog" class="hidden mt-1 text-xs text-red-600">Seleccione un programa.</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Código <span class="text-red-500">*</span></label>
          <input id="cp_code" type="text" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:ring-4 focus:ring-zinc-200" placeholder="220501046">
          <p id="err_ccode" class="hidden mt-1 text-xs text-red-600">El código es obligatorio.</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Nombre <span class="text-red-500">*</span></label>
          <input id="cp_name" type="text" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:ring-4 focus:ring-zinc-200" placeholder="Nombre de la competencia">
          <p id="err_cname" class="hidden mt-1 text-xs text-red-600">El nombre es obligatorio.</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Descripción</label>
          <textarea id="cp_desc" rows="3" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:ring-4 focus:ring-zinc-200" placeholder="Descripción breve"></textarea>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button type="button" id="btnCancelCompetency" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium hover:bg-zinc-50">Cancelar</button>
          <button type="submit" class="rounded-xl bg-zinc-900 text-white px-4 py-2.5 text-sm font-medium hover:bg-black" id="btnSubmitCompetency">Guardar</button>
        </div>
      </form>
    </div>
  </section>

  <!-- MODAL: Nuevo RAE -->
  <div id="modalRaeBackdrop" class="hidden fixed inset-0 z-40 bg-black/40"></div>
  <section id="modalRae" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl border border-zinc-200">
      <div class="flex items-start justify-between p-6 pb-2">
        <div>
          <h3 class="text-2xl font-bold">Nuevo RAE</h3>
          <p class="text-sm text-zinc-500">Complete la información del Resultado de Aprendizaje Esperado</p>
        </div>
        <button id="btnCloseRae" class="p-2 rounded-lg hover:bg-zinc-100" title="Cerrar">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form id="formRaeNew" class="p-6 pt-4 space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Competencia <span class="text-red-500">*</span></label>
          <select id="rae_competency" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none bg-white focus:ring-4 focus:ring-zinc-200">
            <option value="">Seleccione una competencia</option>
            <?php foreach($COMPETENCIES as $c): ?>
              <option value="<?= (int)$c['id'] ?>" data-ccode="<?= htmlspecialchars($c['code']) ?>" data-cname="<?= htmlspecialchars($c['name']) ?>" data-pname="<?= htmlspecialchars($c['program_name']) ?>">
                <?= htmlspecialchars($c['code'].' — '.$c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <p id="err_rcomp" class="hidden mt-1 text-xs text-red-600">Seleccione una competencia.</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Código <span class="text-red-500">*</span></label>
          <input id="rae_code" type="text" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:ring-4 focus:ring-zinc-200" placeholder="220501046-01">
          <p id="err_rcode" class="hidden mt-1 text-xs text-red-600">El código es obligatorio.</p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Descripción <span class="text-red-500">*</span></label>
          <textarea id="rae_desc" rows="3" class="w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm outline-none focus:ring-4 focus:ring-zinc-200" placeholder="Descripción del RAE"></textarea>
          <p id="err_rdesc" class="hidden mt-1 text-xs text-red-600">La descripción es obligatoria.</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
          <button type="button" id="btnCancelRae" class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium hover:bg-zinc-50">Cancelar</button>
          <button type="submit" class="rounded-xl bg-zinc-900 text-white px-4 py-2.5 text-sm font-medium hover:bg-black" id="btnSubmitRae">Guardar</button>
        </div>
      </form>
    </div>
  </section>

  <script>
  window.PROGRAMS = <?= php_to_js($PROGRAMS) ?>;
  window.COMPETENCIES = <?= php_to_js($COMPETENCIES) ?>;
  window.RAES = <?= php_to_js($RAES) ?>;
</script>

  <script src="<?= BASE_URL ?>src/assets/js/gestionCompetencias.js?v=2" defer></script>
</body>
</html>
