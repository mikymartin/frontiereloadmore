(() => {
    'use strict';
    function initializeSliders() {
        if (typeof window.Swiper !== 'function') return;
        document.querySelectorAll('[data-frontiere-related] [data-frontiere-swiper]').forEach(slider => {
            if (slider.swiper) return;
            const section = slider.closest('[data-frontiere-related]');
            new window.Swiper(slider, {
                slidesPerView: 1,
                breakpoints: { 576: { slidesPerView: 3 } },
                slidesPerGroup: 1,
                spaceBetween: 40,
                loop: false,
                autoplay: false,
                pagination: { el: section.querySelector('[data-frontiere-progress]'), type: 'progressbar' },
                navigation: { nextEl: section.querySelector('[data-frontiere-forward]'), prevEl: section.querySelector('[data-frontiere-prev]') }
            });
        });
    }

    document.addEventListener('click', async event => {
        const link = event.target.closest('[data-frontiere-next]');
        if (!link) return;
        const control = link.closest('[data-frontiere-load-more]');
        if (control?.dataset.failed === 'true') return;
        const related = link.closest('[data-frontiere-related]');
        const host = related || control.closest('.container');
        const list = host?.querySelector('[data-frontiere-items]');
        if (!list || control.dataset.loading === 'true') return;
        event.preventDefault();
        const targetUrl = link.href;
        control.dataset.loading = 'true';
        link.setAttribute('aria-disabled', 'true');
        link.textContent = 'Caricamento…';
        try {
            const response = await fetch(targetUrl, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const documentNext = new DOMParser().parseFromString(await response.text(), 'text/html');
            const selector = related
                ? `[data-frontiere-related="${CSS.escape(related.dataset.frontiereRelated)}"]`
                : `[data-frontiere-instance="${CSS.escape(control.dataset.frontiereInstance)}"]`;
            const nextHost = documentNext.querySelector(selector);
            const nextList = nextHost?.querySelector('[data-frontiere-items]') || nextHost?.closest('.container')?.querySelector('[data-frontiere-items]');
            const nextControl = nextHost?.matches('[data-frontiere-load-more]') ? nextHost : nextHost?.querySelector('[data-frontiere-load-more]');
            const nextPage = Number(nextControl?.dataset.frontierePage);
            if (!nextList || !nextControl || nextPage <= Number(control.dataset.frontierePage)) throw new Error('Risposta non valida');
            const added = nextList.children.length;
            if (!added) throw new Error('Nessuna notizia ricevuta');
            list.append(...Array.from(nextList.children).map(node => document.importNode(node, true)));
            control.dataset.frontierePage = String(nextPage);
            const following = nextControl.querySelector('[data-frontiere-next]');
            if (following) {
                link.href = following.href;
                link.textContent = 'Vedi altro';
            } else {
                link.remove();
            }
            const count = control.querySelector('[data-frontiere-count]');
            if (count) count.textContent = `${list.children.length} di ${control.dataset.frontiereTotal}`;
            const status = control.querySelector('[data-frontiere-status]');
            if (status) status.textContent = `Caricate altre ${added} notizie`;
            if (related) list.closest('.swiper')?.swiper?.update();
        } catch (error) {
            link.textContent = 'Vedi altro';
            control.dataset.failed = 'true';
            const status = control.querySelector('[data-frontiere-status]');
            if (status) status.textContent = 'Caricamento non riuscito. Attiva nuovamente Vedi altro per aprire la pagina successiva.';
        } finally {
            link.removeAttribute('aria-disabled');
            delete control.dataset.loading;
        }
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeSliders);
    else initializeSliders();
})();
