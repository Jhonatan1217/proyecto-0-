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
