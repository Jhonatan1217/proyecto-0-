(function(){
    // Config: cantidad por página (ajustables)
    const CONFIG = {
      programsPerPage: 6,
      competenciesPerPage: 6,
      raePerPage: 6
    };
  
    // Generic pagination: hides/shows children to preserve event listeners
    function setupPagination(opts) {
      const {
        containerId,
        paginationWrapperId,
        prevBtnId,
        nextBtnId,
        infoId,
        perPage
      } = opts;
  
      const container = document.getElementById(containerId);
      const wrapper = document.getElementById(paginationWrapperId);
      const prev = document.getElementById(prevBtnId);
      const next = document.getElementById(nextBtnId);
      const info = document.getElementById(infoId);
  
      if (!container || !wrapper || !prev || !next || !info) return;
  
      let page = 1;
      let children = [];
  
      function refreshChildren() {
        // get live children (only element nodes)
        children = Array.from(container.children).filter(n => n.nodeType === 1);
      }
  
      function render() {
        refreshChildren();
        const total = children.length;
        if (total === 0) {
          wrapper.classList.add('hidden');
          info.textContent = '';
          return;
        }
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        if (page > totalPages) page = totalPages;
        const start = (page - 1) * perPage;
        const end = start + perPage;
  
        children.forEach((el, idx) => {
          if (idx >= start && idx < end) {
            el.classList.remove('hidden');
          } else {
            el.classList.add('hidden');
          }
        });
  
        info.textContent = `Página ${page} de ${totalPages} · ${total} items`;
        prev.disabled = page === 1;
        next.disabled = page === totalPages;
  
        wrapper.classList.remove('hidden');
      }
  
      // Observe container for child list changes (rebuilds)
      const mo = new MutationObserver((mutations) => {
        // if children added/removed, reset page to 1 for UX and render
        const relevant = mutations.some(m => m.type === 'childList');
        if (relevant) {
          // small async delay so other scripts finished wiring (if any)
          setTimeout(() => {
            page = 1;
            render();
          }, 10);
        }
      });
      mo.observe(container, { childList: true, subtree: false });
  
      prev.addEventListener('click', () => { if (page>1) { page--; render(); } });
      next.addEventListener('click', () => { page++; render(); });
  
      // initial render (in case items already present)
      setTimeout(render, 50);
      return { render, refreshChildren };
    }
  
    document.addEventListener('DOMContentLoaded', () => {
      // Programas
      setupPagination({
        containerId: 'programsGrid',
        paginationWrapperId: 'programsPagination',
        prevBtnId: 'pgPrev',
        nextBtnId: 'pgNext',
        infoId: 'pgInfo',
        perPage: CONFIG.programsPerPage
      });
  
      // Competencias (note: this paginates the list of competencia cards in #competenciesList)
      setupPagination({
        containerId: 'competenciesList',
        paginationWrapperId: 'competenciasPagination',
        prevBtnId: 'cpPrev',
        nextBtnId: 'cpNext',
        infoId: 'cpInfo',
        perPage: CONFIG.competenciesPerPage
      });
  
      // RAE (paginar items dentro de #raesList)
      setupPagination({
        containerId: 'raesList',
        paginationWrapperId: 'raePagination',
        prevBtnId: 'raePrev',
        nextBtnId: 'raeNext',
        infoId: 'raeInfo',
        perPage: CONFIG.raePerPage
      });
    });
  })();