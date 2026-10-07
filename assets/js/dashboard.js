/**
 * SponsorFlow - Dashboard Frontend Enhancements
 */
document.addEventListener('DOMContentLoaded', () => {
    // Animate progress bars on dashboard load
    document.querySelectorAll('.bars i, .prog i').forEach(bar => {
        const targetWidth = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.transition = 'width 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            bar.style.width = targetWidth;
        }, 50);
    });
});
