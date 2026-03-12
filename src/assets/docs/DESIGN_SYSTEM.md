# Design System — Proyecto Z

**Single Source of Truth** para UI/UX. Todo nuevo módulo debe referenciar este documento y los componentes en `src/assets/css/components/` y `src/assets/js/components/` sin duplicar estilos en las vistas.

---

## 1. Design Tokens

### 1.1 Colores

| Token | Valor | Uso |
|-------|--------|-----|
| **Verde SENA (primario)** | `#39A900` | Títulos h1, focus inputs/selects, botones de éxito, icono check |
| **Verde SENA hover** | `#2d8000` | Hover en botones verdes / `btn-icon-check` |
| **Azul institucional (primario botones)** | `#0a3a57` | Botones principales (Nuevo X, Guardar, Crear) |
| **Azul institucional hover** | `#00304D` | Hover en botones primarios |
| **Texto oscuro** | `#00324D` | Texto de selects de filtros (`filter-select-enterprise`) |
| **Texto estándar** | `#1f2937` | Inputs (`input-enterprise`) |
| **Texto secundario** | `#374151` | Labels, botón secundario |
| **Texto placeholder** | `#9ca3af` | Placeholder inputs |
| **Borde estándar** | `#d1d5db` | Inputs, botones secundarios |
| **Borde hover** | `#9ca3af` | Inputs/selects hover |
| **Borde divisor** | `#e5e7eb` | Headers/footers modales, tablas |
| **Fondo deshabilitado** | `#f3f4f6` | Inputs/selects disabled |
| **Fondo gris claro** | `#f9fafb` | Cabeceras tabla, empty combobox |
| **Focus ring verde** | `rgba(57, 169, 0, 0.2)` | Box-shadow focus (3px) |
| **Focus ring azul** | `rgba(10, 58, 87, 0.4)` | Box-shadow focus botón primario |

### 1.2 Sombras

| Token | Valor CSS | Uso |
|-------|-----------|-----|
| **Card** | `0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)` | `shadow-sm` en cards |
| **Modal** | `0 25px 50px -12px rgba(0, 0, 0, 0.25)` | Cajas modales (`.modal-*-box`) |
| **Dropdown** | `0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05)` | `.combobox-dropdown` |

### 1.3 Radios de borde

| Token | Clase Tailwind | Valor | Uso |
|-------|----------------|--------|-----|
| **Input/Select/Button** | `rounded-xl` | 12px | Inputs, selects, botones, triggers combobox |
| **Card/Modal** | `rounded-2xl` | 16px | Cards principales, modales |
| **Pill** | `rounded-full` / `rounded-lg` | 9999px / 8px | Tags, chips, botón clear combobox |

### 1.4 Tipografía (Tailwind)

| Uso | Clases |
|-----|--------|
| **Título de página (h1)** | `text-4xl font-extrabold tracking-tight mb-2 text-[#39A900]` |
| **Descripción página** | `text-gray-500 mb-6` |
| **Título de card (h2)** | `text-xl font-semibold text-gray-800` |
| **Subtítulo de card** | `text-sm text-gray-500` |
| **Label formulario** | `.label-enterprise` (0.875rem, font-medium, #374151) |
| **Input/Select texto** | `.input-enterprise` (color #1f2937); filtros: `.filter-select-enterprise` (13px, #00324D) |
| **Tabla cabecera** | `text-sm text-gray-600` |
| **Botón primario** | `text-white` + `.btn-modal-primary` |
| **Botón secundario** | `.btn-modal-secondary` |

### 1.5 Inputs y selects (clases del sistema)

- **Input estándar:** `input-enterprise` — borde `#d1d5db`, focus borde `#39A900` + ring 3px verde, hover borde `#9ca3af`, disabled fondo `#f3f4f6`.
- **Select con chevron:** mismo que arriba; chevron vía `background-image` en `select.input-enterprise` (no duplicar SVG en la vista).
- **Select de filtros (altura 42px):** `input-enterprise filter-select-enterprise` — 13px, color `#00324D`, height 42px.
- **Filtros nativos (sin combobox):** `rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] outline-none transition` (+ chevron oculto cuando hay combobox).

### 1.6 Botones

- **Primario (Nuevo X, Guardar):** `btn-modal-primary` o bloque: `bg-[#0a3a57] hover:bg-[#00304D] active:scale-95 transition text-white px-5 py-2.5 rounded-xl ... shadow-sm`.
- **Secundario (Cancelar, Limpiar):** `btn-modal-secondary`.
- **Botón filtros (misma altura que selects):** `btn-modal-secondary filter-action-secondary` (42px).
- **Icono check (guardar fila):** `btn-icon-check` (#39A900, hover #2d8000).
- **Icono X (cancelar edición):** `btn-icon-x` (#dc2626, hover #b91c1c).

---

## 2. Blueprint de Módulos (vistas ≤160 líneas)

Estructura mínima en PHP para mantener vistas cortas y consistentes.

### 2.1 Cabecera de vista (líneas 1–15 aprox.)

```php
<?php /* views/nombreModulo.php */ ?>
<?php
$cargo = $_SESSION['cargo'] ?? '';
if ($cargo === 'INSTRUCTOR' && !permisoParaModulo()) { header('Location: index.php?page=...'); exit; }
if (!defined('BASE_URL')) { $base = '/'; define('BASE_URL', $base); }
?>
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/combobox.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/table-edit.css">
<link rel="stylesheet" href="<?= BASE_URL ?>src/assets/css/components/modal-enterprise.css">
<?php /* Solo estilos específicos del módulo que NO estén en el Design System */ ?>
<style>.table-nombre-modulo { ... }</style>
```

- No incluir en la vista: colores de marca, focus ring, sombras estándar, radios estándar; eso vive en los CSS de componentes.

### 2.2 Estructura HTML (una sola “caja” por módulo)

1. **Contenedor página:** `max-w-6xl mx-auto px-4 py-10`
2. **Título y descripción:** `h1` (token h1) + `p` (token descripción)
3. **Card única:** `bg-white shadow rounded-2xl border border-gray-200`
   - **Cabecera card:** `flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6`
     - Izquierda: `h2` (título card) + `p` (subtítulo)
     - Derecha (opcional): botón primario “+ Nuevo X” o “Nuevo Y”
   - **Filtros:** `px-6 py-4` → fila de selects/inputs con clases del Design System (sin labels si se acuerda así; con `filter-select-enterprise` si aplica)
   - **Contenido:** tabla (`.table-*-wrap` + `table.table-*`) o listado de cards
4. **Modal:** mismo patrón que Zonas/Grupos (overlay + box + header/body/footer), clases `modal-*-overlay`, `modal-*-box`, `modal-*-header/body/footer`, `btn-modal-primary` / `btn-modal-secondary`.

### 2.3 Scripts al final

```php
<script>window.BASE_URL = <?= json_encode(BASE_URL ?? '') ?>;</script>
<script src="<?= BASE_URL ?>src/assets/js/components/combobox.js"></script>
<script src="<?= BASE_URL ?>src/assets/js/[nombreModulo].js"></script>
```

- Lógica de negocio y listeners en el JS externo; en la vista solo lo mínimo (config global si hace falta).

### 2.4 Regla de 160 líneas

- Si la vista supera ~160 líneas, extraer: (1) bloques repetidos a partials PHP, (2) estilos a componentes CSS, (3) contenido de tabla/filas a generación por JS desde datos (API).

---

## 3. Behavior Patterns

### 3.1 Dropup (última fila / tabla)

- **Definición:** El dropdown del combobox debe abrir hacia arriba cuando no hay espacio suficiente debajo o cuando el trigger está en la última fila de la tabla.
- **Implementación:** `combobox.js` → `applyDropdownPosition`: calcula `spaceBelow` / `spaceAbove`, y si `spaceBelow < maxH + MARGIN` o es última fila (`tbody.lastElementChild === tr`) o está en el tercio inferior de la ventana, aplica clase `dropdown-up` y posicionamiento `position: fixed` con `bottom` en lugar de `top`.
- **CSS:** `.combobox-dropdown.dropdown-up` en `combobox.css` (margin-bottom, top auto, bottom, sombra invertida). No duplicar lógica en las vistas.

### 3.2 Cierre de modales

- **Clicks:** Botón “Cerrar” / “Cancelar” cierra el modal; click en el backdrop (si existe `#modalBackdrop` o overlay) cierra el modal.
- **Escape:** Tecla Escape cierra el modal abierto (recomendado en el JS del módulo).
- **Patrón:** Una sola función que oculte overlay + modal (ej. `classList.add('hidden')`) y opcionalmente limpie el formulario. Reutilizar en todos los puntos de cierre.

### 3.3 Modo de edición único (tabla)

- **Regla:** Solo una fila puede estar en modo edición a la vez.
- **Implementación:** Al entrar en edición en una fila, salir de edición en cualquier otra (quitar clase `editando`, restaurar celdas a solo texto, ocultar inputs/combobox de la otra fila).
- **Clases:** La fila en edición tiene `tr.editando`; las celdas editables usan inputs/combobox con clases del Design System. Estilos en `table-edit.css` (`.table-*-wrap tr.editando ...`).
- **Acciones:** Botones “check” (guardar) y “X” (cancelar) por fila; al guardar o cancelar, se quita `editando` y se actualiza la fila.

### 3.4 Combobox — Empty State

- **Sin datos:** Si el `<select>` no tiene opciones (o solo opción vacía), el componente entra en estado vacío: trigger en apariencia deshabilitada/readonly, placeholder `"Sin registros disponibles"`, y el dropdown al abrir muestra el mensaje: *"No se encontraron opciones. Por favor, configure este parámetro en el módulo correspondiente."*
- **Clases:** `.combobox-wrapper.combobox-empty` para el trigger; `.combobox-empty-message` para el texto dentro del dropdown. Definido en `combobox.css` y lógica en `combobox.js`.

---

## 4. Componentes CSS (referencia rápida)

| Archivo | Contenido |
|---------|-----------|
| **modal-enterprise.css** | Overlay/box modales, `.input-enterprise`, `select.input-enterprise`, `.filter-select-enterprise`, `.filter-action-secondary`, `.label-enterprise`, `.btn-modal-primary`, `.btn-modal-secondary`, `.card-hover-enterprise`, estados hover/focus/disabled |
| **combobox.css** | `.combobox-wrapper`, `.combobox-trigger` (hover/focus-within), `.combobox-input`, `.chevron-combobox` (transición/hover), `.btn-clear-combobox` (hover/transición), `.combobox-empty`, `.combobox-empty-message`, `.combobox-dropdown`, `.dropdown-up`, opciones (hover/selected) |
| **table-edit.css** | Wrappers tabla, scrollbar, sticky head, celdas 71px, `.editando`, `.btn-editar`, `.acciones-edit`, `.btn-icon-check`, `.btn-icon-x`, `.tag-pill`, columnas por módulo |

Las vistas no deben redefinir colores de marca, focus ring, sombras estándar ni radios que ya estén en estos archivos.

---

## 5. Checklist nuevo módulo

- [ ] Vista PHP ≤160 líneas; solo `<link>` a los 3 CSS de componentes + `<style>` mínimo específico.
- [ ] Uso de tokens (colores, tipografía, botones) vía clases del Design System, sin `style="..."` ni clases Tailwind ad-hoc para color/sombra/radio.
- [ ] Filtros con `input-enterprise` + `filter-select-enterprise` si aplica; combobox con `combobox.js` y selectores documentados.
- [ ] Modal con clases `modal-*-overlay`, `modal-*-box`, header/body/footer; cierre por botón y, si aplica, backdrop y Escape.
- [ ] Tabla con modo edición único y estilos en `table-edit.css`; dropup manejado por `combobox.js` sin código extra en la vista.
- [ ] Empty state del combobox: sin opciones → placeholder “Sin registros disponibles” y mensaje en dropdown según Design System.
