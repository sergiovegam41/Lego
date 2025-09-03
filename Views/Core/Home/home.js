console.log("Home dashboard component loaded");

document.addEventListener('DOMContentLoaded', function() {
    
    // Agregar interactividad a las tarjetas del dashboard
    const dashboardCards = document.querySelectorAll('.dashboard-card');
    
    dashboardCards.forEach(card => {
        card.addEventListener('click', function() {
            const cardTitle = this.querySelector('h3').textContent;
            console.log(`Clicked on: ${cardTitle}`);
            
            // Aquí se podría agregar navegación o acciones específicas
            // Por ejemplo, abrir otros módulos del sistema
        });
        
        // Efecto de entrada suave
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, Math.random() * 200);
    });
    
    console.log("Dashboard initialized successfully");
});