document.addEventListener("DOMContentLoaded", () => {
  const menuBtn = document.getElementById("menu-hamburguesa");
  const menuLateral = document.getElementById("menu-lateral");
  const cerrarMenu = document.getElementById("cerrar-menu");
  const academicosToggle = document.getElementById('academicos-toggle');
  const academicosSubmenu = document.getElementById('academicos-submenu');
  const academicosFlecha = document.getElementById('academicos-flecha');

  // Aseguramos que no bloquee clics cuando está oculto
  if (menuLateral) {
    menuLateral.classList.add("pointer-events-none");
  }

  // Abrir el sidebar 
  if (menuBtn) {
    menuBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      if (menuLateral) {
        menuLateral.classList.remove("translate-x-full", "pointer-events-none");
      }
    });
  }

  // Cierre con el botón "x"
  if (cerrarMenu) {
    cerrarMenu.addEventListener("click", () => {
      if (menuLateral) {
        menuLateral.classList.add("translate-x-full", "pointer-events-none");
      }
      // Resetear el submenú cuando se cierra el menú principal
      if (academicosSubmenu && !academicosSubmenu.classList.contains('hidden')) {
        academicosSubmenu.classList.add('hidden');
        if (academicosFlecha) {
          academicosFlecha.classList.remove('rotate-180');
        }
      }
    });
  }

  // Funcionalidad para el submenú de Académicos (DEFINIRLO ANTES DE USARLO)
  if (academicosToggle && academicosSubmenu && academicosFlecha) {
    academicosToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      e.preventDefault();
      
      // Alternar la visibilidad del submenú
      academicosSubmenu.classList.toggle('hidden');
      
      // Rotar la flecha
      academicosFlecha.classList.toggle('rotate-180');
    });
  }

  // Cierra al hacer clic fuera del sidebar
  document.addEventListener("click", (e) => {
    if (menuLateral && 
        !menuLateral.classList.contains("translate-x-full") && 
        !menuLateral.contains(e.target) && 
        menuBtn && !menuBtn.contains(e.target)) {
      
      menuLateral.classList.add("translate-x-full", "pointer-events-none");
      
      // Resetear el submenú cuando se cierra el menú principal
      if (academicosSubmenu && !academicosSubmenu.classList.contains('hidden')) {
        academicosSubmenu.classList.add('hidden');
        if (academicosFlecha) {
          academicosFlecha.classList.remove('rotate-180');
        }
      }
    }
  });
});