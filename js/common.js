// muestra un elemento
function showElement(elementId) {
    let element = document.getElementById(elementId);
    element.hidden = false;
}

// oculta un elemento
function hideElement(elementId) {
    let element = document.getElementById(elementId);
    element.hidden = true;
}

// formateador de números como moneda
const formatter = new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'USD' 
});

// agrega un manejador de eventos para cuando la página termina de cargar
window.addEventListener('load', function () {
    // obtener el ícono del menú y el contenedor del menú
    const toggleButton = document.getElementById('toggle-menu');
    const menu = document.getElementById('menu');

    // agrega un manejador de evento para hacer el toggle del menú
    toggleButton.addEventListener('click', function() {
        // alterna la visibilad del elemento
        menu.hidden = !menu.hidden;
    });
});
