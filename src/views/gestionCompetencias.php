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
    .no-programs [data-role="program-field"]{display:none !important;}   /* campos select de programa */
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
        <h2 class="text-3xl font-bold mb-1">Carga Masiva desde Excel</h2>
        <p class="text-sm text-zinc-500 mb-6">Importe programas, competencias y RAE desde un archivo Excel o CSV</p>
        <div class="max-w-8xl">
          <div class="rounded-2xl ring-1 ring-zinc-200 shadow-sm overflow-hidden">
            <div class="px-6 pt-6">
              <h3 class="text-lg font-semibold flex items-center gap-2">
                <i data-lucide="upload" class="w-5 h-5"></i> Subir Archivo
              </h3>
              <p class="text-sm text-zinc-500">Seleccione un archivo Excel (.xlsx) o CSV para importar</p>
            </div>
            <div class="px-6 pb-6 space-y-4">
              <label class="flex h-36 w-full cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 hover:bg-zinc-100 transition">
                <div class="text-center">
                  <i data-lucide="upload" class="mx-auto h-8 w-8 text-zinc-400"></i>
                  <p class="mt-2 text-sm text-zinc-500">Click para seleccionar archivo</p>
                </div>
                <input type="file" class="hidden" />
              </label>
              <button class="w-full rounded-xl bg-zinc-900 text-white py-2.5 text-sm font-medium hover:bg-black">Subir y Procesar</button>
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
            <h2 class="text-3xl font-bold">Competencias</h2>
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
            <button class="rounded-xl bg-zinc-900 text-white px-4 py-2 text-sm font-medium hover:bg-black flex items-center gap-2">
              <i data-lucide="plus" class="w-4 h-4"></i> Nueva Competencia
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
    // ====== Feature flag: desactivar "Programas" sin borrar base ======
    const REMOVE_PROGRAMS = true;

    // ====== Datos PHP -> JS ======
    const PROGRAMS = <?= php_to_js($PROGRAMS) ?>;
    const COMPETENCIES = <?= php_to_js($COMPETENCIES) ?>;
    const RAES = <?= php_to_js($RAES) ?>;

    // ====== Util ======
    const e = s => String(s ?? '')
      .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
      .replaceAll('"','&quot;').replaceAll("'","&#039;");

    const emptyHero = (labelPlural, btnId, btnText) => `
      <div class="py-16 text-center">
        <p class="text-zinc-500 text-lg">No hay ${labelPlural.toLowerCase()} registrados</p>
        <button id="${btnId}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-zinc-900 text-white px-4 py-2.5 text-sm font-medium hover:bg-black">
          <i data-lucide="plus" class="w-4 h-4"></i> ${btnText}
        </button>
      </div>
    `;

    const editingState = { type: null, id: null };
    function clearEditingState(){ editingState.type = null; editingState.id = null; }

    const tabButtons = document.querySelectorAll('[data-tab-btn]');
    const tabPanes = document.querySelectorAll('.tab-pane');

    function activateTab(key){
      // Si Programas está deshabilitado, redirigimos a upload
      if (REMOVE_PROGRAMS && key === 'programs') key = 'upload';
      tabPanes.forEach(p => p.classList.toggle('hidden', p.dataset.tab !== key));
      tabButtons.forEach(b=>{
        const active = b.dataset.tabBtn === key;
        b.classList.toggle('tabs-pill-active', active);
        b.setAttribute('aria-selected', active ? 'true' : 'false');
        b.classList.toggle('text-zinc-900', active);
        b.classList.toggle('text-zinc-700', !active);
      });
      if(key==='programs') renderPrograms();
      if(key==='competencies') renderCompetencies();
      if(key==='raes') { refreshRaeFilters(); renderRaes(); }
      window.lucide?.createIcons();
    }
    tabButtons.forEach(b=>b.addEventListener('click',()=>activateTab(b.dataset.tabBtn)));

    // ====== Ocultar todo lo de Programas si está deshabilitado
    if (REMOVE_PROGRAMS) {
      document.body.classList.add('no-programs');
      // Evitar render de Programas
      window.renderPrograms = function(){ /* no-op: programas desactivado */ };
      // Botón de pestaña Programas no interactivo
      const btnPrograms = document.querySelector('[data-tab-btn="programs"]');
      if (btnPrograms) {
        btnPrograms.setAttribute('tabindex', '-1');
        btnPrograms.setAttribute('aria-hidden', 'true');
      }
    }

    // Tab inicial
    activateTab('upload');

    // ====== Programs (mantengo funciones originales por compatibilidad) ======
    function renderPrograms(){
      const grid = document.getElementById('programsGrid');
      const empty = document.getElementById('programsEmpty');
      if(!grid || !empty) return;
      grid.innerHTML='';
      PROGRAMS.forEach(p=>{
        const activeBadge = p.active
          ? '<span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium">Activo</span>'
          : '<span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-500 px-2 py-0.5 text-xs font-medium">Inactivo</span>';
        const card = document.createElement('div');
        card.className = "rounded-2xl ring-1 ring-zinc-200 shadow-sm overflow-hidden hover:shadow transition program-card";
        card.setAttribute('data-program-id', p.id);
        card.setAttribute('role','button');
        card.setAttribute('tabindex','0');
        card.innerHTML = `
          <div class="px-6 pt-6 pb-2">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <h3 class="text-lg font-semibold leading-snug">${e(p.name)}</h3>
                <p class="mt-1 text-sm text-zinc-500">Código: ${e(p.code)}</p>
              </div>
              <div class="flex items-center gap-2">
                <button class="p-2 rounded-lg hover:bg-zinc-100" title="Editar" data-edit-program="${p.id}">
                  <i data-lucide="pencil" class="w-4 h-4"></i>
                </button>
                <label class="switch" data-stop-prop="1">
                  <input type="checkbox" ${p.active?'checked':''} data-program="${p.id}">
                  <span class="dot"></span>
                  <span class="track absolute inset-0 rounded-full"></span>
                </label>
              </div>
            </div>
          </div>
          <div class="px-6 pb-6">
            <p class="text-sm text-zinc-600">${e(p.description || 'Sin descripción')}</p>
            ${p.duration_hours? `<p class="mt-2 text-sm font-medium">Duración: ${p.duration_hours} horas</p>`:''}
            <div class="mt-2">${activeBadge}</div>
          </div>
        `;
        grid.appendChild(card);
      });

      grid.querySelectorAll('[data-program]').forEach(chk=>{
        chk.addEventListener('change',ev=>{
          ev.stopPropagation();
          const id = Number(ev.target.dataset.program);
          const idx = PROGRAMS.findIndex(x=>x.id===id);
          if(idx>-1){ PROGRAMS[idx].active = !PROGRAMS[idx].active; renderPrograms(); window.lucide?.createIcons(); }
        });
      });

      grid.querySelectorAll('.program-card').forEach(card=>{
        card.addEventListener('click',(ev)=>{
          const isControl = ev.target.closest('[data-stop-prop],label.switch,input,button');
          if(isControl) return;
          const id = Number(card.getAttribute('data-program-id'));
          openProgramForEdit(id);
        });
      });
      grid.querySelectorAll('[data-edit-program]').forEach(btn=>{
        btn.addEventListener('click', (ev)=>{
          ev.stopPropagation();
          const id = Number(btn.getAttribute('data-edit-program'));
          openProgramForEdit(id);
        });
      });

      const newBtn = document.querySelector('section[data-tab="programs"] button i[data-lucide="plus"]')?.parentElement;
      if (newBtn && !newBtn.dataset.bound) {
        newBtn.dataset.bound = "1";
        newBtn.addEventListener('click', ()=>{ clearEditingState(); openProgramModal(); setProgramSubmitMode('create'); });
      }

      if (PROGRAMS.length === 0) {
        grid.classList.add('hidden');
        empty.classList.remove('hidden');
        empty.innerHTML = emptyHero('programas','btnFirstProgram','Crear Primer Programa');
      } else {
        grid.classList.remove('hidden');
        empty.classList.add('hidden');
      }
      window.lucide?.createIcons();
    }

    // ====== Competencies ======
    function renderCompetencies(){
      const list = document.getElementById('competenciesList');
      const empty = document.getElementById('competenciesEmpty');
      if(!list || !empty) return;

      // Si Programas está deshabilitado, ignoramos filtro por programa
      let filtered = COMPETENCIES;
      const progFilterEl = document.getElementById('competencyProgramFilter');
      if (!REMOVE_PROGRAMS && progFilterEl) {
        const filter = progFilterEl.value;
        filtered = filter==='all' ? COMPETENCIES : COMPETENCIES.filter(c=>String(c.program_id)===String(filter));
      }

      list.innerHTML='';
      filtered.forEach(c=>{
        const hasRaes = Array.isArray(c.raes) && c.raes.length>0;
        const badgeActive = c.active
          ? '<span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium">Activo</span>'
          : '<span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-500 px-2 py-0.5 text-xs font-medium">Inactivo</span>';

        const card = document.createElement('div');
        card.className = "rounded-2xl ring-1 ring-zinc-200 shadow-sm overflow-hidden competency-card";
        card.setAttribute('data-competency-id', c.id);
        card.setAttribute('role','button');
        card.setAttribute('tabindex','0');

        card.innerHTML = `
          <div class="px-6 pt-6 pb-2">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  ${hasRaes ? `<button class="p-1.5 rounded-md hover:bg-zinc-100" data-expand="${c.id}" aria-expanded="false" title="Mostrar RAE"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>` : ''}
                  <h3 class="text-lg font-semibold">${e(c.name)}</h3>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                  <span class="text-zinc-700">Código: ${e(c.code)}</span>
                  <span class="text-zinc-400">•</span>
                  <span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-700 px-2 py-0.5 text-xs font-medium" data-role="program-chip">${e(c.program_name || 'Sin programa')}</span>
                  <span class="text-zinc-400">•</span>
                  ${badgeActive}
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button class="p-2 rounded-lg hover:bg-zinc-100" title="Editar" data-edit-competency="${c.id}"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                <label class="switch" data-stop-prop="1">
                  <input type="checkbox" ${c.active?'checked':''} data-competency="${c.id}">
                  <span class="dot"></span>
                  <span class="track absolute inset-0 rounded-full"></span>
                </label>
              </div>
            </div>
          </div>
          <div class="px-6 pb-6">
            <p class="text-sm text-zinc-600">${e(c.description || 'Sin descripción')}</p>
            ${hasRaes ? `
              <div class="mt-3"><span class="inline-flex items-center rounded-full border border-zinc-300 text-zinc-700 px-2 py-0.5 text-xs font-medium">${c.raes.length} RAE${c.raes.length!==1?'s':''}</span></div>
              <div class="mt-4 space-y-2 border-t border-zinc-200 pt-4 hidden" data-rae-list="${c.id}">
                <h4 class="text-sm font-semibold">Resultados de Aprendizaje Esperados (RAE)</h4>
                <div class="space-y-2">
                  ${c.raes.map(rae=>`
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 rae-mini" data-mini-rae="${rae.id}">
                      <div class="flex items-start gap-2">
                        <span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-700 px-2 py-0.5 text-xs font-medium">${e(rae.code)}</span>
                        <p class="text-sm">${e(rae.description)}</p>
                      </div>
                    </div>
                  `).join('')}
                </div>
              </div>` : '' }
          </div>
        `;
        list.appendChild(card);
      });

      list.querySelectorAll('[data-competency]').forEach(chk=>{
        chk.addEventListener('change',ev=>{
          ev.stopPropagation();
          const id = Number(ev.target.dataset.competency);
          const i = COMPETENCIES.findIndex(x=>x.id===id);
          if(i>-1){ COMPETENCIES[i].active = !COMPETENCIES[i].active; renderCompetencies(); window.lucide?.createIcons(); }
        });
      });

      list.querySelectorAll('[data-expand]').forEach(btn=>{
        btn.addEventListener('click',(ev)=>{
          ev.stopPropagation();
          const id = btn.dataset.expand;
          const box = list.querySelector(`[data-rae-list="${id}"]`);
          const isHidden = box.classList.contains('hidden');
          box.classList.toggle('hidden', !isHidden);
          btn.setAttribute('aria-expanded', String(isHidden));
          btn.innerHTML = `<i data-lucide="${isHidden?'chevron-down':'chevron-right'}" class="w-4 h-4"></i>`;
          window.lucide?.createIcons();
        });
      });

      list.querySelectorAll('.competency-card').forEach(card=>{
        card.addEventListener('click',(ev)=>{
          const isControl = ev.target.closest('[data-stop-prop],label.switch,input,button,[data-expand]');
          if(isControl) return;
          const id = Number(card.getAttribute('data-competency-id'));
          openCompetencyForEdit(id);
        });
      });
      list.querySelectorAll('[data-edit-competency]').forEach(btn=>{
        btn.addEventListener('click',(ev)=>{
          ev.stopPropagation();
          const id = Number(btn.getAttribute('data-edit-competency'));
          openCompetencyForEdit(id);
        });
      });

      // Estado vacío
      if (COMPETENCIES.length === 0) {
        empty.classList.remove('hidden');
        empty.innerHTML = emptyHero('competencias','btnFirstCompetency','Crear Primera Competencia');
        document.getElementById('btnFirstCompetency')?.addEventListener('click', ()=>{ clearEditingState(); openCompetencyModal(); setCompetencySubmitMode('create'); });
      } else {
        empty.classList.add('hidden');
      }

      window.lucide?.createIcons();

      const btnCompetency = document.querySelector('section[data-tab="competencies"] button i[data-lucide="plus"]')?.parentElement;
      if (btnCompetency && !btnCompetency.dataset.bound) {
        btnCompetency.dataset.bound = "1";
        btnCompetency.addEventListener('click', ()=>{ clearEditingState(); openCompetencyModal(); setCompetencySubmitMode('create'); });
      }
    }

    // Si existe el select, mantenemos compat; si no, no pasa nada
    document.getElementById('competencyProgramFilter')?.addEventListener('change', renderCompetencies);

    // ====== RAEs ======
    function refreshRaeFilters(){
      const pf = document.getElementById('raeProgramFilter');
      const cf = document.getElementById('raeCompetencyFilter');
      if(!pf || !cf) return;

      const programs = Array.from(new Set(RAES.map(r=>r.program_name).filter(Boolean)));
      const comps = Array.from(new Set(RAES.map(r=>r.competency_code).filter(Boolean)));

      const keepFirst = (sel, placeholder) => {
        const current = sel.value;
        sel.innerHTML = '';
        const optAll = document.createElement('option');
        optAll.value = 'all'; optAll.textContent = placeholder;
        sel.appendChild(optAll);
        return current;
      };

      const curP = keepFirst(pf,'Todos los programas');
      programs.forEach(pn=>{
        const o = document.createElement('option'); o.value = pn; o.textContent = pn; pf.appendChild(o);
      });
      pf.value = programs.includes(curP) ? curP : 'all';

      const curC = keepFirst(cf,'Todas las competencias');
      comps.forEach(cc=>{
        const o = document.createElement('option'); o.value = cc; o.textContent = cc; cf.appendChild(o);
      });
      cf.value = comps.includes(curC) ? curC : 'all';
    }

    function renderRaes(){
      const list = document.getElementById('raesList');
      const empty = document.getElementById('raesEmpty');
      if(!list || !empty) return;

      const pf = document.getElementById('raeProgramFilter').value;
      const cf = document.getElementById('raeCompetencyFilter').value;

      const filtered = RAES.filter(r=>{
        const okP = (pf==='all') || (r.program_name===pf);
        const okC = (cf==='all') || (r.competency_code===cf);
        return okP && okC;
      });

      list.innerHTML='';
      filtered.forEach(r=>{
        const badgeActive = r.active
          ? '<span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium">Activo</span>'
          : '<span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-500 px-2 py-0.5 text-xs font-medium">Inactivo</span>';

        const card = document.createElement('div');
        card.className = "rounded-2xl ring-1 ring-zinc-200 shadow-sm px-6 py-4 rae-card";
        card.setAttribute('data-rae-id', r.id);
        card.setAttribute('role','button');
        card.setAttribute('tabindex','0');

        card.innerHTML = `
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center rounded-full bg-zinc-100 text-zinc-700 px-2 py-0.5 text-xs font-medium">${e(r.code)}</span>
                <h3 class="text-base font-semibold">${e(r.description)}</h3>
              </div>
              <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                <span>Competencia: ${e(r.competency_code)}</span>
                <span class="text-zinc-400">•</span>
                <span>${e(r.competency_name || '')}</span>
                ${r.program_name ? `<span class="text-zinc-400">•</span><span class="inline-flex items-center rounded-full border border-zinc-300 text-zinc-700 px-2 py-0.5 text-xs font-medium" data-role="program-chip">${e(r.program_name)}</span>`:''}
                <span class="text-zinc-400">•</span>
                ${badgeActive}
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button class="p-2 rounded-lg hover:bg-zinc-100" title="Editar" data-edit-rae="${r.id}"><i data-lucide="pencil" class="w-4 h-4"></i></button>
              <label class="switch" data-stop-prop="1">
                <input type="checkbox" ${r.active?'checked':''} data-rae="${r.id}">
                <span class="dot"></span>
                <span class="track absolute inset-0 rounded-full"></span>
              </label>
            </div>
          </div>
        `;
        list.appendChild(card);
      });

      list.querySelectorAll('[data-rae]').forEach(chk=>{
        chk.addEventListener('change',ev=>{
          ev.stopPropagation();
          const id = Number(ev.target.dataset.rae);
          const i = RAES.findIndex(x=>x.id===id);
          if(i>-1){ RAES[i].active = !RAES[i].active; renderRaes(); window.lucide?.createIcons(); }
        });
      });

      list.querySelectorAll('.rae-card').forEach(card=>{
        card.addEventListener('click',(ev)=>{
          const isControl = ev.target.closest('[data-stop-prop],label.switch,input,button');
          if(isControl) return;
          const id = Number(card.getAttribute('data-rae-id'));
          openRaeForEdit(id);
        });
      });
      list.querySelectorAll('[data-edit-rae]').forEach(btn=>{
        btn.addEventListener('click',(ev)=>{
          ev.stopPropagation();
          const id = Number(btn.getAttribute('data-edit-rae'));
          openRaeForEdit(id);
        });
      });

      if (RAES.length === 0) {
        empty.classList.remove('hidden');
        empty.innerHTML = emptyHero('RAE','btnFirstRae','Crear Primer RAE');
        document.getElementById('btnFirstRae')?.addEventListener('click', ()=>{ clearEditingState(); openRaeModal(); setRaeSubmitMode('create'); });
      } else {
        if (filtered.length === 0) {
          empty.classList.remove('hidden');
          empty.innerHTML = `<div class="py-12 text-center"><p class="text-zinc-500">No hay RAE que coincidan con los filtros seleccionados.</p></div>`;
        } else {
          empty.classList.add('hidden');
        }
      }

      window.lucide?.createIcons();

      const btnRae = document.querySelector('section[data-tab="raes"] button i[data-lucide="plus"]')?.parentElement;
      if (btnRae && !btnRae.dataset.bound) {
        btnRae.dataset.bound = "1";
        btnRae.addEventListener('click', ()=>{ clearEditingState(); openRaeModal(); setRaeSubmitMode('create'); });
      }
    }
    document.getElementById('raeProgramFilter')?.addEventListener('change', renderRaes);
    document.getElementById('raeCompetencyFilter')?.addEventListener('change', renderRaes);

    // ====== Modal Programa (se mantiene por compatibilidad)
    const $modal = document.getElementById('modalProgram');
    const $backdrop = document.getElementById('modalProgramBackdrop');
    const $form = document.getElementById('formProgramNew');
    const $btnSubmitProgram = document.getElementById('btnSubmitProgram');
    let labelProgramDefault = $btnSubmitProgram?.textContent;

    function openProgramModal(){ $backdrop?.classList.remove('hidden'); $modal?.classList.remove('hidden'); document.getElementById('pg_code')?.focus(); window.lucide?.createIcons(); }
    function closeProgramModal(){
      $backdrop?.classList.add('hidden'); $modal?.classList.add('hidden');
      ['pg_code','pg_name','pg_hours'].forEach(id=>document.getElementById(id)?.classList.remove('ring-2','ring-red-300'));
      ['err_code','err_name','err_hours'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
      $form?.reset();
      setProgramSubmitMode('create');
      clearEditingState();
    }
    function setProgramSubmitMode(mode){
      if(!$btnSubmitProgram) return;
      if(mode==='edit') $btnSubmitProgram.textContent = 'Actualizar';
      else $btnSubmitProgram.textContent = labelProgramDefault || 'Guardar';
    }
    function openProgramForEdit(id){
      const p = PROGRAMS.find(x=>Number(x.id)===Number(id));
      if(!p) return;
      editingState.type = 'program'; editingState.id = Number(id);
      openProgramModal();
      setProgramSubmitMode('edit');
      document.getElementById('pg_code').value = p.code || '';
      document.getElementById('pg_name').value = p.name || '';
      document.getElementById('pg_desc').value = p.description || '';
      document.getElementById('pg_hours').value = (p.duration_hours ?? '') === null ? '' : (p.duration_hours ?? '');
    }
    document.getElementById('btnCloseProgram')?.addEventListener('click', closeProgramModal);
    document.getElementById('btnCancelProgram')?.addEventListener('click', closeProgramModal);
    $backdrop?.addEventListener('click', closeProgramModal);
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !$modal?.classList.contains('hidden')) closeProgramModal(); });
    $form?.addEventListener('submit', (ev)=>{
      ev.preventDefault();
      closeProgramModal();
      activateTab('upload');
    });

    // ====== Modal Competencia (sin exigir programa cuando REMOVE_PROGRAMS = true)
    const $modalC = document.getElementById('modalCompetency');
    const $backdropC = document.getElementById('modalCompetencyBackdrop');
    const $formC = document.getElementById('formCompetencyNew');
    const $btnSubmitCompetency = document.getElementById('btnSubmitCompetency');
    let labelCompetencyDefault = $btnSubmitCompetency?.textContent;

    function openCompetencyModal(){ $backdropC.classList.remove('hidden'); $modalC.classList.remove('hidden'); (document.getElementById('cp_program')||document.getElementById('cp_code'))?.focus(); window.lucide?.createIcons(); }
    function closeCompetencyModal(){
      $backdropC.classList.add('hidden'); $modalC.classList.add('hidden');
      ['cp_program','cp_code','cp_name'].forEach(id=>document.getElementById(id)?.classList.remove('ring-2','ring-red-300'));
      ['err_cprog','err_ccode','err_cname'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
      $formC.reset();
      setCompetencySubmitMode('create');
      clearEditingState();
    }
    function setCompetencySubmitMode(mode){
      if(!$btnSubmitCompetency) return;
      if(mode==='edit') $btnSubmitCompetency.textContent = 'Actualizar';
      else $btnSubmitCompetency.textContent = labelCompetencyDefault || 'Guardar';
    }
    function openCompetencyForEdit(id){
      const c = COMPETENCIES.find(x=>Number(x.id)===Number(id));
      if(!c) return;
      editingState.type = 'competency'; editingState.id = Number(id);
      openCompetencyModal();
      setCompetencySubmitMode('edit');
      const sel = document.getElementById('cp_program');
      if (sel) {
        sel.value = String(c.program_id ?? '');
        if(!Array.from(sel.options).some(o=>o.value===String(c.program_id))) sel.value = '';
      }
      document.getElementById('cp_code').value = c.code || '';
      document.getElementById('cp_name').value = c.name || '';
      document.getElementById('cp_desc').value = c.description || '';
    }
    document.getElementById('btnCloseCompetency')?.addEventListener('click', closeCompetencyModal);
    document.getElementById('btnCancelCompetency')?.addEventListener('click', closeCompetencyModal);
    $backdropC.addEventListener('click', closeCompetencyModal);
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !$modalC.classList.contains('hidden')) closeCompetencyModal(); });

    $formC.addEventListener('submit', (ev)=>{
      ev.preventDefault();
      const sel = document.getElementById('cp_program');
      // Si programas están deshabilitados, ignoramos selección de programa
      const programId = (REMOVE_PROGRAMS || !sel) ? '0' : sel.value.trim();
      const programOpt = sel ? sel.options[sel.selectedIndex] : null;
      const programName = (REMOVE_PROGRAMS || !programOpt) ? '' : (programOpt ? programOpt.text : '');
      const programCode = (REMOVE_PROGRAMS || !programOpt) ? '' : (programOpt.getAttribute('data-code') || '');

      const code = document.getElementById('cp_code').value.trim();
      const name = document.getElementById('cp_name').value.trim();
      const desc = document.getElementById('cp_desc').value.trim();

      let ok = true;
      if(!code){ ok=false; markErr('cp_code','err_ccode'); } else clearErr('cp_code','err_ccode');
      if(!name){ ok=false; markErr('cp_name','err_cname'); } else clearErr('cp_name','err_cname');

      // Solo validamos programa si está habilitado
      if(!REMOVE_PROGRAMS && sel && !programId){ ok=false; markErr('cp_program','err_cprog'); } 
      else { clearErr('cp_program','err_cprog'); }

      if(!ok) return;

      if(editingState.type === 'competency' && editingState.id != null){
        const idx = COMPETENCIES.findIndex(c=>Number(c.id)===Number(editingState.id));
        if(idx>-1){
          // Si programas deshabilitados, no tocamos program_* (se preserva lo que ya tenga)
          if(!REMOVE_PROGRAMS){
            COMPETENCIES[idx].program_id = Number(programId);
            COMPETENCIES[idx].program_name = programName;
            COMPETENCIES[idx].program_code = programCode;
          }
          COMPETENCIES[idx].code = code;
          COMPETENCIES[idx].name = name;
          COMPETENCIES[idx].description = desc;
        }
      } else {
        const nextId = (COMPETENCIES.reduce((m,c)=>Math.max(m, Number(c.id)||0),0) || 0) + 1;
        COMPETENCIES.push({
          id: nextId,
          program_id: REMOVE_PROGRAMS ? null : Number(programId),
          code,
          name,
          description: desc,
          program_name: REMOVE_PROGRAMS ? '' : programName,
          program_code: REMOVE_PROGRAMS ? '' : programCode,
          active: true,
          raes: []
        });
      }

      closeCompetencyModal();
      activateTab('competencies');
    });

    // ====== Modal RAE
    const $modalR = document.getElementById('modalRae');
    const $backdropR = document.getElementById('modalRaeBackdrop');
    const $formR = document.getElementById('formRaeNew');
    const $btnSubmitRae = document.getElementById('btnSubmitRae');
    let labelRaeDefault = $btnSubmitRae?.textContent;

    function openRaeModal(){ $backdropR.classList.remove('hidden'); $modalR.classList.remove('hidden'); document.getElementById('rae_competency')?.focus(); window.lucide?.createIcons(); }
    function closeRaeModal(){
      $backdropR.classList.add('hidden'); $modalR.classList.add('hidden');
      ['rae_competency','rae_code','rae_desc'].forEach(id=>document.getElementById(id)?.classList.remove('ring-2','ring-red-300'));
      ['err_rcomp','err_rcode','err_rdesc'].forEach(id=>document.getElementById(id)?.classList.add('hidden'));
      $formR.reset();
      setRaeSubmitMode('create');
      clearEditingState();
    }
    function setRaeSubmitMode(mode){
      if(!$btnSubmitRae) return;
      if(mode==='edit') $btnSubmitRae.textContent = 'Actualizar';
      else $btnSubmitRae.textContent = labelRaeDefault || 'Guardar';
    }
    function openRaeForEdit(id){
      const r = RAES.find(x=>Number(x.id)===Number(id));
      if(!r) return;
      editingState.type = 'rae'; editingState.id = Number(id);
      openRaeModal();
      setRaeSubmitMode('edit');
      const sel = document.getElementById('rae_competency');
      sel.value = String(r.competency_id ?? '');
      if(!Array.from(sel.options).some(o=>o.value===String(r.competency_id))) sel.value = '';
      document.getElementById('rae_code').value = r.code || '';
      document.getElementById('rae_desc').value = r.description || '';
    }
    document.getElementById('btnCloseRae')?.addEventListener('click', closeRaeModal);
    document.getElementById('btnCancelRae')?.addEventListener('click', closeRaeModal);
    $backdropR.addEventListener('click', closeRaeModal);
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !$modalR.classList.contains('hidden')) closeRaeModal(); });

    $formR.addEventListener('submit', (ev)=>{
      ev.preventDefault();
      const sel = document.getElementById('rae_competency');
      const compId = sel.value.trim();
      const opt = sel.options[sel.selectedIndex];
      const cCode = opt?.getAttribute('data-ccode') || '';
      const cName = opt?.getAttribute('data-cname') || '';
      const pName = opt?.getAttribute('data-pname') || '';
      const code = document.getElementById('rae_code').value.trim();
      const desc = document.getElementById('rae_desc').value.trim();

      let ok = true;
      if(!compId){ ok=false; markErr('rae_competency','err_rcomp'); } else clearErr('rae_competency','err_rcomp');
      if(!code){ ok=false; markErr('rae_code','err_rcode'); } else clearErr('rae_code','err_rcode');
      if(!desc){ ok=false; markErr('rae_desc','err_rdesc'); } else clearErr('rae_desc','err_rdesc');
      if(!ok) return;

      if(editingState.type === 'rae' && editingState.id != null){
        const idx = RAES.findIndex(r=>Number(r.id)===Number(editingState.id));
        if(idx>-1){
          RAES[idx].competency_id = Number(compId);
          RAES[idx].competency_code = cCode;
          RAES[idx].competency_name = cName;
          RAES[idx].program_name = pName;
          RAES[idx].code = code;
          RAES[idx].description = desc;
        }
        const ci = COMPETENCIES.findIndex(c=>Array.isArray(c.raes) && c.raes.some(rr=>rr.id===editingState.id));
        if(ci>-1){
          const ri = COMPETENCIES[ci].raes.findIndex(rr=>rr.id===editingState.id);
          if(ri>-1){
            COMPETENCIES[ci].raes[ri].code = code;
            COMPETENCIES[ci].raes[ri].description = desc;
          }
        }
      } else {
        const nextId = (RAES.reduce((m,r)=>Math.max(m, Number(r.id)||0),0) || 0) + 1;
        RAES.push({
          id: nextId,
          competency_id: Number(compId),
          code,
          description: desc,
          competency_code: cCode,
          competency_name: cName,
          program_name: pName,
          active: true
        });
        const ci = COMPETENCIES.findIndex(c=>String(c.id)===String(compId));
        if(ci>-1){
          if(!Array.isArray(COMPETENCIES[ci].raes)) COMPETENCIES[ci].raes = [];
          COMPETENCIES[ci].raes.push({ id: nextId, code, description: desc });
        }
      }

      closeRaeModal();
      refreshRaeFilters();
      activateTab('raes');
      renderCompetencies();
    });

    function markErr(inputId, errId){ const el = document.getElementById(inputId); if(!el) return; el.classList.add('ring-2','ring-red-300'); document.getElementById(errId)?.classList.remove('hidden'); }
    function clearErr(inputId, errId){ const el = document.getElementById(inputId); if(!el) return; el.classList.remove('ring-2','ring-red-300'); document.getElementById(errId)?.classList.add('hidden'); }

    // Delegados (compat con Lucide)
    document.addEventListener('click', (ev) => {
      const btnP = ev.target.closest('section[data-tab="programs"] button');
      if (btnP && (btnP.textContent || '').toLowerCase().includes('nuevo programa')) { clearEditingState(); openProgramModal(); setProgramSubmitMode('create'); }
      const btnC = ev.target.closest('section[data-tab="competencies"] button');
      if (btnC && (btnC.textContent || '').toLowerCase().includes('nueva competencia')) { clearEditingState(); openCompetencyModal(); setCompetencySubmitMode('create'); }
      const btnR = ev.target.closest('section[data-tab="raes"] button');
      if (btnR && (btnR.textContent || '').toLowerCase().includes('nuevo rae')) { clearEditingState(); openRaeModal(); setRaeSubmitMode('create'); }
    });

    window.lucide?.createIcons();
  </script>
</body>
</html>
