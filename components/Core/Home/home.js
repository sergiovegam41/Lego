console.log("Home dashboard component loaded");

// Hacer que las tarjetas del dashboard sean clicables
document.addEventListener('DOMContentLoaded', function() {
    const dashboardCards = document.querySelectorAll('.dashboard-card[data-url]');

    dashboardCards.forEach(card => {
        card.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });
    });
});
