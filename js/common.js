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

    formatDocumentDates();
}

// formatea todas las fechas presentes en el documento
function formatDocumentDates() {
    const elements = document.getElementsByTagName('date');
    for (let index = 0; index < elements.length; index++) {
        const element = elements[index];
        // crea una fecha estableciendo como hora 12:00am para asegurar que no
        // muestre la hora de UTC

        let date = new Date(element.textContent.trim() + 'T00:00:00');
        let format = 'short';
        let result;

        if (element.hasAttribute('format')) {
            format = element.getAttribute('format');
        }

        result = formatDate(date, format);

        if (format === 'long' || format === 'medium' || format === 'short') {
            let fullDate = date.toLocaleDateString('es-MX', {
                dateStyle: 'full'
            });

            element.setAttribute('title', fullDate);
        }

        element.textContent = result;
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


function formatDate(date, format) {
    switch (format) {
        case 'year-only':
            return date.toLocaleDateString('es-MX', {
                year: 'numeric'
            });
        case 'month-only': 
            return date.toLocaleDateString('es-MX', {
                month: 'long'
            });
        case 'full':
            return date.toLocaleDateString('es-MX', {
                dateStyle: 'full'
            });
        case 'long':
            return date.toLocaleDateString('es-MX', {
                dateStyle: 'long'
            });
        case 'medium':
            return date.toLocaleDateString('es-MX', {
                dateStyle: 'medium'
            });
        case 'short':
            return date.toLocaleDateString('es-MX', {
                dateStyle: 'short'
            });
        default:
            return date.toDateString();
    }
}

// formateador de números como moneda
const currencyFormatter = new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'USD'
});

// agrega un manejador de eventos para cuando la página termina de cargar
window.addEventListener('load', () => initCommon());
