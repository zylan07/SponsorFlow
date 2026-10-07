/**
 * SponsorFlow - Reports Frontend Enhancements
 */
document.addEventListener('DOMContentLoaded', () => {
    // Animate bars in reports
    document.querySelectorAll('.bars i').forEach(bar => {
        const target = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.transition = 'width 0.5s ease-out';
            bar.style.width = target;
        }, 50);
    });
});
