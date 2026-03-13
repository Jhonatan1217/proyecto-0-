# Design System — Proyecto Z

Documento de referencia para UI/UX. Los nuevos módulos deben seguir estas reglas para mantener consistencia visual y de comportamiento.

---

## Autocomplete (evitar sugerencias del navegador)

Para que el único dropdown que vea el usuario sea el del sistema (combobox, selects) y no el historial de texto del navegador:

### Regla estándar

- **Todos los `<form>`** de los módulos (Zonas, Grupos, Historial, etc.) deben llevar **`autocomplete="off"`**.
- **Todos los `<input>`** y **`<select>`** dentro de esos módulos deben llevar **`autocomplete="off"`** en el HTML.

### Inputs de búsqueda (combobox)

- Los inputs de búsqueda de los comboboxes se generan en **`combobox.js`**. El script les asigna automáticamente **`autocomplete="one-time-code"`** (valor que muchos navegadores respetan cuando ignoran `"off"`), de modo que no se muestren sugerencias del navegador.
- En cualquier **nuevo input de búsqueda** (filtros, buscadores) que no use el combobox pero sea tipo “buscar/seleccionar”, se debe usar en HTML **`autocomplete="off"`** como mínimo; si el navegador sigue mostrando sugerencias, valorar **`autocomplete="one-time-code"`** en ese input.

### Resumen

| Contexto | Atributo |
|----------|----------|
| `<form>` | `autocomplete="off"` |
| `<input>` en formularios o filtros | `autocomplete="off"` |
| `<select>` en formularios o filtros | `autocomplete="off"` |
| Input de búsqueda generado por `combobox.js` | `autocomplete="one-time-code"` (asignado en JS) |

Objetivo: **limpieza visual** — que el usuario solo vea los desplegables de la aplicación, no el autocompletado del navegador.

---

## Combobox en fila de edición (estándar)

Cuando un módulo tiene **edición inline** (fila que pasa a modo editar con comboboxes, como en Zonas y Grupos), los comboboxes de la fila deben seguir este patrón para que la persistencia (X → placeholder → blur → restaurar valor inicial) funcione igual en todos los módulos.

### 1. HTML del `<select>` en la fila

- Clases: `cell-edit`, nombre del campo, clase del módulo para combobox (ej. `select-zona`, `select-grupo`), `input-enterprise w-full py-2.5 text-sm`.
- Atributo **`data-initial-value`**: valor actual del registro (ej. `id_area`, `id_programa`, `id_lider_grupo`), escapado para HTML (`"` → `&quot;`).

Ejemplo (Zonas):

```html
<select class="cell-edit area select-zona input-enterprise w-full py-2.5 text-sm" data-initial-value="${(idArea || '').replace(/"/g, '&quot;')}">${optsArea}</select>
```

Ejemplo (Grupos, programa y líder):

```html
<select class="cell-edit programa select-grupo input-enterprise w-full py-2.5 text-sm" data-initial-value="${idProgramaEsc}">${optsPrograma}</select>
<select class="cell-edit lider select-grupo input-enterprise w-full py-2.5 text-sm" data-initial-value="${idLiderEsc}">${optsLider}</select>
```

### 2. Después de mejorar los selects (enhance)

Llamar a **`ComboboxComponent.setInitialValue(selectElement, valorInicial)`** para cada combobox de la fila que use persistencia en edición. Así el wrapper recibe el valor inicial y, al pulsar X y salir en blanco, se restaura.

Ejemplo (Zonas):

```js
enhanceSelectsZona();
if (typeof ComboboxComponent !== 'undefined' && typeof ComboboxComponent.setInitialValue === 'function') {
  const selAreaEdit = row.querySelector('.cell-edit.area.select-zona');
  if (selAreaEdit) ComboboxComponent.setInitialValue(selAreaEdit, idArea || selAreaEdit.value);
}
```

Ejemplo (Grupos):

```js
enhanceSelectsGrupo();
if (typeof ComboboxComponent !== 'undefined' && typeof ComboboxComponent.setInitialValue === 'function') {
  const selPrograma = row.querySelector('.cell-edit.programa.select-grupo');
  const selLider = row.querySelector('.cell-edit.lider.select-grupo');
  if (selPrograma) ComboboxComponent.setInitialValue(selPrograma, g.id_programa ?? selPrograma.value);
  if (selLider) ComboboxComponent.setInitialValue(selLider, g.id_lider_grupo ?? selLider.value);
}
```

### 3. Resumen

| Paso | Acción |
|------|--------|
| 1 | Incluir `data-initial-value` en cada `<select>` combobox de la fila de edición (valor actual escapado). |
| 2 | Tras el `enhance` del módulo, llamar `setInitialValue(select, valor)` para cada uno de esos comboboxes. |
| 3 | Los selects que son listas fijas (jornada, modalidad, etc.) usan `.select-styled` y no necesitan `data-initial-value` ni `setInitialValue`. |

Referencia de implementación: **Zonas** (editar zona) y **Grupos** (editar grupo).
