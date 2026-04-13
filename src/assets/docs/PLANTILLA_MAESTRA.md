# Plantilla Maestra – Módulos de Gestión

Patrón de referencia para vistas con tabla editable, filtros, modal y comboboxes. **Objetivo: ≤200 líneas por vista.**

---

## Estructura de Archivos

```
src/
├── assets/
│   ├── css/components/
│   │   ├── combobox.css      # Combobox con dropup
│   │   ├── table-edit.css    # Tabla + modo edición
│   │   └── modal-enterprise.css  # Modal, inputs, botones
│   └── js/components/
│       └── combobox.js       # Combobox centralizado
├── views/
│   ├── gestionZonas.php     # Ejemplo (~135 líneas)
│   └── gestionGrupos.php    # Ejemplo (~165 líneas)
└── assets/js/
    ├── gestionZonas.js
    └── gestionGrupos.js
```

---

## Orden de Carga en la Vista

```php
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/combobox.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/table-edit.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/modal-enterprise.css">
<!-- Estilos específicos del módulo (mínimos) -->
<style>...</style>

<!-- HTML: Header + Filtros + Tabla + Modal -->

<script>window.BASE_URL = <?= json_encode(BASE_URL ?? '') ?>;</script>
<script src="<?= BASE_URL ?>src/assets/js/components/combobox.js"></script>
<script src="<?= BASE_URL ?>src/assets/js/[gestionModulo].js"></script>
```

---

## Estructura HTML Tipo

### 1. Contenedor principal
```html
<div class="max-w-6xl mx-auto px-4 py-10">
  <h1 class="text-4xl font-extrabold...">Título</h1>
  <p class="text-gray-500 mb-6">Descripción</p>
```

### 2. Card con header
```html
  <div class="bg-white shadow rounded-2xl border border-gray-200">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6">
      <div><h2>...</h2><p>...</p></div>
      <button id="btnAbrirModal...">+ Nuevo</button>
    </div>
```

### 3. Filtros
```html
    <div class="px-6 py-4">
      <div class="flex flex-col md:flex-row gap-4">
        <div id="filtroXWrap" class="relative w-full md:w-64">
          <select id="filtroX" class="...">...</select>
          <span class="...-chevron">...</span>
        </div>
        <div id="buscadorWrap" class="relative w-full md:w-64">
          <input id="buscador" placeholder="Buscar..." class="pl-10 pr-10 ..." />
          <svg class="absolute left-3...">  <!-- icono búsqueda --> </svg>
        </div>
      </div>
    </div>
```

### 4. Tabla
```html
    <div id="wrapTablaX" class="table-[modulo]-wrap overflow-x-auto">
      <table id="tablaX" class="table-[modulo]">
        <thead>...</thead>
        <tbody id="tbodyX"></tbody>
      </table>
    </div>
  </div>
</div>
```

### 5. Modal
```html
<div id="modalX" class="fixed inset-0 z-50 ... modal-[modulo]-overlay hidden">
  <div id="modalBackdrop" class="absolute inset-0 -z-0"></div>
  <div class="modal-[modulo]-box ...">
    <header class="modal-[modulo]-header">...</header>
    <form id="formX">
      <div class="modal-[modulo]-body">...</div>
      <footer class="modal-[modulo]-footer">...</footer>
    </form>
  </div>
</div>
```

---

## Clases Requeridas

| Elemento | Clases |
|----------|--------|
| Wrapper tabla | `table-[modulo]-wrap` (o `table-zonas-wrap`, `table-grupos-wrap`) |
| Tabla | `table-[modulo]` |
| Fila en edición | `tr.editando` |
| Select combobox | `select-zona`, `select-grupo` según módulo |
| Chevron (ocultar) | `select-zona-chevron`, `filtro-area-chevron`, `select-grupo-chevron` |
| Botones guardar/cancelar | `btn-icon-check`, `btn-icon-x` |
| Celdas edición | `cell-edit-wrap`, `input-enterprise`, `cell-edit numero` |

---

## Lógica JS del Módulo

1. **Cargar datos** y poblar selects
2. **Llamar a `ComboboxComponent.enhance()`** para filtros y selects de modal/fila
3. **Renderizar tabla** con `data-id`, `data-id-area` (o similar)
4. **Botón editar**: reemplazar celdas por inputs/selects con clase `select-[modulo]`, volver a `enhance`
5. **Guardar / Cancelar**: recargar tabla sin resetear filtros ni buscador; cerrar modal con `ComboboxComponent.reset()`
6. **Buscador**: opcional botón clear (`btn-clear-buscador`) para no borrar texto manualmente
7. **Delegación tabla**: `mousedown` en tabla para abrir combobox en filas (`_cbOpen`)

---

## Dropup (última fila)

El `ComboboxComponent` aplica automáticamente `position: fixed` y orientación hacia arriba cuando:
- La fila es la última
- El elemento queda en el tercio inferior de la ventana
- Hay poco espacio debajo

---

## Regla de prioridad

> Si el límite de 200 líneas impide que algo funcione bien, prioriza el funcionamiento y documenta el motivo de las líneas extra.

---

## Aplicar a nuevos módulos (Usuarios, Ambientes, Reportes)

1. Copiar estructura de `gestionZonas.php` o `gestionGrupos.php`
2. Ajustar IDs y clases al módulo
3. Reutilizar `combobox.css`, `table-edit.css`, `modal-enterprise.css`
4. Crear `gestion[Modulo].js` siguiendo el patrón de `gestionZonas.js` / `gestionGrupos.js`
5. Usar siempre `ComboboxComponent.enhance()` para selects con búsqueda
