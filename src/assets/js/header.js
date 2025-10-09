// --- Modal desde el header ---
    const abrirHeader = document.getElementById("btnAbrirModalHeader");
    const modal = document.getElementById("modalCrearHeader");
    const backdrop = document.getElementById("modalBackdrop");
    const card = document.getElementById("modalCard");
    const cerrar = document.getElementById("btnCerrarModal");

    let lastFocused = null;

    function openModal(e){
      if(e) e.preventDefault(); // evita navegar al href; si quieres navegar, quita esta línea
      lastFocused = document.activeElement;

      modal.classList.remove("hidden");

      // Animación de entrada
      card.classList.add("modal-enter");
      backdrop.classList.add("backdrop-enter");
      requestAnimationFrame(() => {
        card.classList.add("modal-enter-active");
        backdrop.classList.add("backdrop-enter-active");
        setTimeout(()=>{
          card.classList.remove("modal-enter","modal-enter-active");
          backdrop.classList.remove("backdrop-enter","backdrop-enter-active");
        },160);
      });

      // Focus al primer campo
      const first = modal.querySelector("select, input, textarea, button");
      first && first.focus();

      document.body.style.overflow = "hidden"; // bloquea scroll del fondo
    }

    function closeModal(){
      modal.classList.add("hidden");
      document.body.style.overflow = "";
      lastFocused && lastFocused.focus();
    }

    abrirHeader?.addEventListener("click", openModal);
    cerrar?.addEventListener("click", closeModal);
    backdrop?.addEventListener("click", closeModal);

    // Cerrar con ESC
    window.addEventListener("keydown", (e)=>{
      if(!modal.classList.contains("hidden") && e.key === "Escape") closeModal();
    });

    // Focus trap simple dentro del modal
    modal.addEventListener("keydown",(e)=>{
      if(e.key !== "Tab") return;
      const focusables = modal.querySelectorAll('a, button, textarea, input, select, [tabindex]:not([tabindex="-1"])');
      if(!focusables.length) return;
      const first = focusables[0];
      const last = focusables[focusables.length - 1];
      if(e.shiftKey && document.activeElement === first){ e.preventDefault(); last.focus(); }
      else if(!e.shiftKey && document.activeElement === last){ e.preventDefault(); first.focus(); }
    });