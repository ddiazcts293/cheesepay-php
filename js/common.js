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

const formatter = new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'USD' 
});
