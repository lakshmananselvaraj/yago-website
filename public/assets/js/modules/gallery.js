/* =========================================================================
   Vipasa Yoga — Gallery page
   Category filter (client-side show/hide), grid/masonry view toggle, and a
   simple dependency-free lightbox with keyboard + prev/next navigation.
   ========================================================================= */

export function initGallery() {
    const grid = document.getElementById('gallery-grid');
    if (!grid) return;

    const items = Array.from(grid.querySelectorAll('.gallery-item'));
    const filterChips = document.querySelectorAll('.gallery-filters__chip');
    const viewButtons = document.querySelectorAll('.gallery-view-toggle__btn');
    const emptyMessage = document.querySelector('.gallery-empty');

    filterChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            filterChips.forEach((c) => c.classList.remove('is-selected'));
            chip.classList.add('is-selected');

            const filter = chip.dataset.filter;
            let visibleCount = 0;

            items.forEach((item) => {
                const matches = filter === 'all' || item.dataset.category === filter;
                item.hidden = !matches;
                if (matches) visibleCount += 1;
            });

            if (emptyMessage) emptyMessage.hidden = visibleCount > 0;
        });
    });

    viewButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            viewButtons.forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            grid.dataset.view = btn.dataset.view;
        });
    });

    initLightbox(items);
}

function initLightbox(items) {
    const lightbox = document.getElementById('lightbox');
    const imgEl = document.getElementById('lightbox-img');
    const captionEl = document.getElementById('lightbox-caption');
    if (!lightbox || !imgEl || !captionEl) return;

    let currentIndex = 0;

    function visibleItems() {
        return items.filter((item) => !item.hidden);
    }

    function show(index) {
        const list = visibleItems();
        if (!list.length) return;
        currentIndex = (index + list.length) % list.length;
        const item = list[currentIndex];
        const img = item.querySelector('img');
        imgEl.src = img.src;
        imgEl.alt = img.alt;
        captionEl.textContent = item.querySelector('.gallery-item__caption')?.textContent ?? '';
    }

    function open(index) {
        show(index);
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function close() {
        lightbox.hidden = true;
        document.body.style.overflow = '';
    }

    items.forEach((item) => {
        const trigger = item.querySelector('.gallery-item__trigger');
        trigger?.addEventListener('click', () => {
            const list = visibleItems();
            const idx = list.indexOf(item);
            open(idx === -1 ? 0 : idx);
        });
    });

    lightbox.querySelector('[data-lightbox-close]')?.addEventListener('click', close);
    lightbox.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => show(currentIndex - 1));
    lightbox.querySelector('[data-lightbox-next]')?.addEventListener('click', () => show(currentIndex + 1));

    lightbox.addEventListener('click', (event) => {
        if (event.target === lightbox) close();
    });

    document.addEventListener('keydown', (event) => {
        if (lightbox.hidden) return;
        if (event.key === 'Escape') close();
        if (event.key === 'ArrowLeft') show(currentIndex - 1);
        if (event.key === 'ArrowRight') show(currentIndex + 1);
    });
}
