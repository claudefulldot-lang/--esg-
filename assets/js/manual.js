document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('manualSearch');
    const sections = [...document.querySelectorAll('.manual-section[data-search]')];
    const countLabel = document.getElementById('manualSearchCount');
    const emptyState = document.getElementById('manualEmpty');
    const tocLinks = [...document.querySelectorAll('.manual-toc a')];

    const normalize = value => value.toLocaleLowerCase('zh-Hant-TW').replace(/\s+/g, ' ').trim();
    const updateSearch = () => {
        const terms = normalize(searchInput.value).split(' ').filter(Boolean);
        let visible = 0;
        sections.forEach(section => {
            const haystack = normalize(`${section.dataset.search} ${section.textContent}`);
            const matches = terms.length === 0 || terms.every(term => haystack.includes(term));
            section.classList.toggle('is-hidden', !matches);
            if (matches) visible++;
        });
        countLabel.textContent = terms.length ? `找到 ${visible} 個主題` : `${sections.length} 個主題`;
        emptyState.classList.toggle('is-visible', visible === 0);
    };

    searchInput?.addEventListener('input', updateSearch);
    document.querySelectorAll('[data-manual-query]').forEach(button => {
        button.addEventListener('click', () => {
            searchInput.value = button.dataset.manualQuery || '';
            updateSearch();
            searchInput.focus();
        });
    });

    const dialog = document.getElementById('manualImageDialog');
    const dialogImage = document.getElementById('manualDialogImage');
    document.querySelectorAll('[data-manual-image]').forEach(button => {
        button.addEventListener('click', () => {
            dialogImage.src = button.dataset.manualImage;
            dialogImage.alt = button.dataset.manualAlt || '系統操作畫面放大圖';
            dialog.showModal();
        });
    });
    document.getElementById('manualDialogClose')?.addEventListener('click', () => dialog.close());
    dialog?.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            tocLinks.forEach(link => link.classList.toggle('active', link.hash === `#${entry.target.id}`));
        });
    }, { rootMargin: '-20% 0px -70% 0px' });
    sections.forEach(section => observer.observe(section));
    updateSearch();
});
