// muestra un elemento
function showElement(elementId) {
    let element = document.getElementById(elementId);
    element.classList.remove('hidden');
}

// oculta un elemento
function hideElement(elementId) {
    let element = document.getElementById(elementId);
    element.classList.add('hidden');
}

const formatter = new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'USD' 
});
