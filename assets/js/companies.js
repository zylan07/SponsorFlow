/**
 * SponsorFlow - Companies Frontend Enhancements
 */
document.addEventListener('DOMContentLoaded', () => {
    // Instant client filter on typing in the search box if not submitting form
    const searchInput = document.querySelector('input[name="q"]');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (!row.querySelector('td')) return;
                const text = row.textContent.toLowerCase();
                row.style.display = (!query || text.includes(query)) ? '' : 'none';
            });
        });
    }
});
