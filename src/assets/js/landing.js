/* ========== Modal (aislado, sin colisiones de nombres) ========== */
      (() => {
        const landing_openBtn   = document.getElementById("btnAbrirModal");
        const landing_modal     = document.getElementById("modalCrearLanding");
        const landing_backdrop  = document.getElementById("modalBackdrop");
        const landing_card      = document.getElementById("modalCard");
        const landing_closeBtn  = document.getElementById("btnCerrarModal");

        let landing_lastFocused = null;

        function openLandingModal(e){
          if (e) e.preventDefault();
          landing_lastFocused = document.activeElement;

          landing_modal.classList.remove("hidden");

          landing_card.classList.add("modal-enter");
          landing_backdrop.classList.add("backdrop-enter");
          requestAnimationFrame(() => {
            landing_card.classList.add("modal-enter-active");
            landing_backdrop.classList.add("backdrop-enter-active");
            setTimeout(() => {
              landing_card.classList.remove("modal-enter","modal-enter-active");
              landing_backdrop.classList.remove("backdrop-enter","backdrop-enter-active");
            }, 160);
          });

          const first = landing_modal.querySelector("select, input, textarea, button");
          first && first.focus();
          document.body.style.overflow = "hidden";
        }

        function closeLandingModal(){
          landing_modal.classList.add("hidden");
          document.body.style.overflow = "";
          landing_lastFocused && landing_lastFocused.focus();
        }

        landing_openBtn?.addEventListener("click", openLandingModal);
        landing_closeBtn?.addEventListener("click", closeLandingModal);
        landing_backdrop?.addEventListener("click", closeLandingModal);

        window.addEventListener("keydown", (e) => {
          if (!landing_modal.classList.contains("hidden") && e.key === "Escape") closeLandingModal();
        });

        landing_modal.addEventListener("keydown", (e) => {
          if (e.key !== "Tab") return;
          const focusables = landing_modal.querySelectorAll('a, button, textarea, input, select, [tabindex]:not([tabindex="-1"])');
          if (!focusables.length) return;
          const first = focusables[0];
          const last  = focusables[focusables.length - 1];
          if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
          else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        });

        // Cierre por delegación (por si el handler directo no se engancha)
        landing_modal.addEventListener("click", (e) => {
          const closeBtn = e.target.closest("[data-close='true']");
          if (closeBtn) {
            e.preventDefault();
            e.stopPropagation();
            closeLandingModal();
          }
        });

      })();

      // Redirige al hacer clic en el botón "Visualizar horario"
      const btnVisualizar = document.getElementById("btnVisualizarHorario");
      if (btnVisualizar) {
        btnVisualizar.addEventListener("click", () => {
          window.location.href = `${BASE_URL}index.php?page=src/views/register_tables`;
        });
      }



      