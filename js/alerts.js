// inicializa todos los elementos de alerta
function initAlerts() {
    // obtiene todas los elementos con la clase alert-close-btn
    let closeButtons = document.getElementsByClassName('alert-close-btn');
    
    for (let i = 0; i < closeButtons.length; i++) {
        let closeButton = closeButtons[i];
        closeButton.onclick = function() {
            let alert = getParentAlert(this);
            // establece la opacidad del elemento div a cero
            alert.style.opacity = '0';
            // oculta el elemento div después de 600ms
            setTimeout(
                function() { alert.classList.add('alert-hidden'); }, 
                600
            );
        };
    }
}

// muestra una alerta
function showAlert(alertElementId) {
    let alert = document.getElementById(alertElementId);

    alert.style.opacity = 1;
    alert.classList.remove('alert-hidden');
}

// oculta una alerta
function hideAlert(alertElementId) {
    let alert = document.getElementById(alertElementId);
    alert.classList.add('alert-hidden');
}

function getParentAlert(element) {
    // obtiene el elemento padre
    let parent = element;
    // recorre los elementos padre recursivamente
    while (parent !== undefined && !parent.classList.contains('alert')) {
        parent = parent.parentElement;
    }

    return parent;
}

// agrega un oyente para el evento de carga
window.addEventListener('load', () => initAlerts());
