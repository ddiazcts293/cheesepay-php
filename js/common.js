// initialize document 
function initCommon() {
    // obtener el ícono del menú y el contenedor del menú
    const toggleButton = document.getElementById('toggle-menu');
    const menu = document.getElementById('menu');

    // verifica si la página contiene un menú y un botón para mostrar/ocultar
    if (menu !== null && toggleButton !== null) {
        // verifica si el ancho de la página es menor a 480px
        if (document.body.clientWidth < 480) {
            // oculta el menu
            menu.classList.remove('show');
        }

        // agrega un manejador de evento para hacer el toggle del menú
        toggleButton.addEventListener('click', function() {
            // Alterna el valor de la varible al darle click.
            menu.classList.toggle('show'); 
        });
    }
}

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
window.addEventListener('load', () => initCommon());
