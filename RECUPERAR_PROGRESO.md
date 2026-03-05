# Pasos para recuperar el progreso del módulo Gestión de Perfil

## Opción A: Revisar Git (por si algo quedó guardado)

1. **Ver historial del archivo del header**
   - En la terminal: `git log --oneline -10 -- src/includes/header-private.php`
   - Revisa si algún commit antiguo tiene más líneas/cambios.

2. **Ver si hay stashes guardados**
   - En la terminal: `git stash list`
   - Si aparece algo: `git stash show -p stash@{0}` para ver cambios y `git stash apply stash@{0}` para recuperarlos (si son los correctos).

3. **Ver reflog por si hiciste reset**
   - En la terminal: `git reflog`
   - Si ves un commit anterior al que perdiste: `git checkout <hash> -- src/includes/header-private.php` (cambia `<hash>` por el valor que veas).

4. **Otras ramas**
   - `git branch -a` para listar ramas.
   - Si tienes otra rama donde sí esté el progreso: `git checkout otra-rama -- src/includes/header-private.php src/assets/js/gestionPerfil.js` (y demás archivos).

---

## Opción B: Restaurar desde copia de seguridad

- Si usas **Cursor/VS Code**: a veces hay "Local History" (clic derecho en el archivo → "Open Timeline" o "Local History").
- Si tienes **copia en otro equipo o carpeta**: copia de nuevo los archivos:
  - `src/includes/header-private.php`
  - `src/assets/js/gestionPerfil.js`
  - `src/views/gestion_perfil.php` (si existía)

---

## Opción C: Que se vuelva a aplicar todo el progreso (recomendado)

Si con las opciones A y B no recuperas nada, se puede **recrear todo el avance** en el código:

1. **Archivos a tocar**
   - `src/includes/header-private.php`: widget de usuario (Ver perfil, Editar perfil, Cerrar sesión), 4 modales (Ver perfil, Solicitar cambios, Cambiar contraseña, Cerrar sesión), estilos de toasts y modales, enlace "Mi perfil" en el menú, script de `gestionPerfil.js`.
   - `src/assets/js/gestionPerfil.js`: lógica de los modales, validación “sin cambios”, toggles de contraseña, confirmación de cierre de sesión (crear el archivo si no existe).
   - `src/views/gestion_perfil.php`: vista de “Mi perfil” (crear si no existe).
   - Rutas y menú para que “Mi perfil” funcione.

2. **Qué incluye ese progreso**
   - Modal **Ver perfil** (avatar, nombre, rol, datos).
   - Modal **Solicitar cambios de perfil** (formulario, validación “no se detectaron cambios”, toast, botón Enviar solicitud visible).
   - Modal **Cambiar contraseña** (Shield-Check, título visible, lock blanco, ojo/ojo cerrado en inputs).
   - Modal **Cerrar sesión** (confirmación antes de salir).
   - Toasts con z-index alto para verse sobre modales.
   - Iconos desde `assets/icons` (x, Key, Shield-Check, lock, eye, eye-off).

Cuando quieras, di: **“Recupera todo el progreso del módulo de perfil”** y se reaplicará todo lo anterior en los archivos.
