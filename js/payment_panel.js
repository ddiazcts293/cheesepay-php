// crea una lista para almacenar las cuotas agregadas
let addedFees = [];

// almacenar el identificador de filas de la tabla cuotas
let feeRowId = 1;

// inicializa el document
function init() {
    console.log('Initializing document...');
    
    if (isNewStudent) {
        addedFees = defaultFeeList;
        console.log('Default fees for new students added');
    }
}

// cambia el tipo de cuota
function changeFeeType() {
    // obtiene el tipo de cuota seleccionado
    const feeType = document.getElementById('fee-type').value;
    const monthlySelector = document.getElementById('monthly-selector');
    const uniformSelector = document.getElementById('uniform-selector');
    const specialEventSelector = document.getElementById('special-event-selector');
    const addFeeButton = document.getElementById('add-fee-button');

    // procesea el tipo de cuota
    switch (feeType) {
        case 'enrollment':
        case 'maintenance':
        case 'stationery':
            monthlySelector.hidden = true;
            uniformSelector.hidden = true;
            specialEventSelector.hidden = true;
            addFeeButton.removeAttribute('disabled');
            break;
        case 'monthly':
            monthlySelector.hidden = false;
            uniformSelector.hidden = true;
            specialEventSelector.hidden = true;
            changeMonthlyFee();
            break;
        case 'uniform':
            monthlySelector.hidden = true;
            uniformSelector.hidden = false;
            specialEventSelector.hidden = true;
            changeUniformType();
            break;
        case 'special_event':
            monthlySelector.hidden = true;
            uniformSelector.hidden = true;
            specialEventSelector.hidden = false;
            changeSpecialEventFee();
            break;
        default:
            monthlySelector.hidden = true;
            uniformSelector.hidden = true;
            specialEventSelector.hidden = true;
            addFeeButton.setAttribute('disabled', true);
            break;
    }
}

function changeMonthlyFee() {
    const monthlyFee = document.getElementById('monthly-fee').value;
    const addFeeButton = document.getElementById('add-fee-button');

    if (monthlyFee === 'none') {
        addFeeButton.setAttribute('disabled', true);
    } else {
        addFeeButton.removeAttribute('disabled');
    }
}

function changeUniformType() {
    const uniformType = document.getElementById('uniform-type').value;
    const uniformFeeSelector = document.getElementById('uniform-fee');
    const addFeeButton = document.getElementById('add-fee-button');

    // Remueve cada una de las opciones presentes en el selector
    // mientras haya elementos (excepto el predeterminado)
    while (uniformFeeSelector.childElementCount > 1) {
        // obtiene la última opción agregada
        const option = uniformFeeSelector.lastElementChild;
        // verifica si esta no es la predeterminada
        if (option.value !== 'none') {
            // quita
            option.remove();
        }
    }
    
    if (uniformType === 'none') {
        uniformFeeSelector.setAttribute('disabled', true);
        addFeeButton.setAttribute('disabled', true);
    } else {
        // recorre las cuotas de uniforme para agregar aquellas cuyo tipo de 
        // uniforme coincida
        uniformFees.forEach(uniform => {
            // obtiene un valor que indica si el nivel educativo y grado coinciden

            if (uniform['type']['number'] == uniformType) {
                let option = document.createElement('option');
                option.value = uniform['number'];
                option.textContent = uniform['concept'];
                uniformFeeSelector.appendChild(option);
            }
        });

        uniformFeeSelector.removeAttribute('disabled');
    }
}

function changeUniformFee() {
    const uniformType = document.getElementById('uniform-type').value;
    const addFeeButton = document.getElementById('add-fee-button');

    if (uniformType === 'none') {
        addFeeButton.setAttribute('disabled', true);
    } else {
        addFeeButton.removeAttribute('disabled');
    }
}

function changeSpecialEventFee() {
    const specialEvent = document.getElementById('special-event-fee').value;
    const addFeeButton = document.getElementById('add-fee-button');

    if (specialEvent === 'none') {
        addFeeButton.setAttribute('disabled', true);
    } else {
        addFeeButton.removeAttribute('disabled');
    }
}

function loadFeeFromDb() {
    // obtiene el tipo de cuota seleccionado
    const feeType = document.getElementById('fee-type').value;
    let feeId = null;

    // procesea el tipo de cuota
    switch (feeType) {
        case 'maintenance':
            // usar codigo de ciclo escolar
        case 'enrollment':
        case 'stationery':
            // usar codigo de nivel educativo
            return;
            break;
        case 'monthly':
            feeId = document.getElementById('monthly-fee').value;
            break;
        case 'uniform':
            feeId = document.getElementById('uniform-fee').value;
            break;
        case 'special_event':
            feeId = document.getElementById('special-event-fee').value;
            break;
        default:
            console.warn('Invalid fee type');
            return;
    }

    // verifica si la cuota ya se agregó
    if (addedFees.some(fee => fee['number'] == feeId)) {
        console.warn('The selected fee is already added');
        return;
    }

    // crea un cliente para realizar peticiones HTTP
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        // verifica si el estado del cliente es completa y si el código de
        // respuesta es válido (200)
        if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
            // obtiene la respuesta en formato json
            let response = JSON.parse(this.responseText);
            
            // verifica si el resultado de la operación en la base de
            // datos fue existos
            if (response['status'] === 'ok') {
                // agrega la cuota obtenida a la lista
                addFeeToList(response['data']);
            } else {
                // imprime el mensaje de error
                console.warn('Error:', response['message']);
            }
        }
    };

    // establece el tipo de método y la URL de la petición
    xhr.open('GET', 'queries/get.php?type=fee&id=' + feeId);
    // envia la petición
    xhr.send();
}

// agrega una cuota a la lista
function addFeeToList(fee) {
    // obtiene el elemento en donde están las filas de los tutores agregados
    const tbody = document.querySelector('#fees-table tbody');
    // obtiene la plantilla de la fila
    const rowTemplate = document.getElementById('fees-table-row-template');
    
    // agrega una nueva fila a la tabla
    tbody.appendChild(rowTemplate.content.cloneNode(true));
    let addedRow = tbody.lastElementChild;

    // obtiene los elementos que contendrán algún dato del tutor
    let idField = addedRow.querySelector('[data-field-name=\'id\']');
    let conceptField = addedRow.querySelector('[data-field-name=\'concept\']');
    let costField = addedRow.querySelector('[data-field-name=\'cost\']');
    let removeButton = addedRow.querySelector('[data-action-name=\'remove\']');
    let rowId = feeRowId++;
    
    // establece los valores
    idField.textContent = fee['number'];
    conceptField.textContent = fee['concept'];
    costField.textContent = formatter.format(fee['cost']);
    addedRow.setAttribute('data-row-id', rowId);
    addedRow.setAttribute('data-attachment', fee['number']);
    removeButton.setAttribute('data-action-arg', rowId);
    removeButton.onclick = function() {
        removeFeeFromList(this.attributes['data-action-arg'].value);
    };

    addedFees.push(fee);

    // actualiza el total del pago
    updatePaymentTotal();
}

// quita una cuota de la lista
function removeFeeFromList(rowId) {
    // obtiene el elemento en donde están las filas de los tutores agregados
    const tbody = document.querySelector('#fees-table tbody');
    // localiza la fila con el identificador del tutor asignado
    const row = tbody.querySelector(`tr[data-row-id=\'${rowId}\']`);
    const feeId = row.getAttribute('data-attachment');

    // quita la fila de la tabla
    row.remove();
    // quita la cuota de la lista
    const feeIndex = addedFees.findIndex(fee => fee['number'] == feeId);
    if (feeIndex !== -1) {
        addedFees.splice(feeIndex, 1);
    }

    updatePaymentTotal();
}

// actualiza el total del pago
function updatePaymentTotal() {
    // obtiene todas las filas de las cuotas agregadas
    const totalAmountCell = document.getElementById('fees-table-total-cell');
    let total = 0;

    addedFees.forEach(fee => { total += fee['cost']; });
    totalAmountCell.textContent = formatter.format(total);
}

/* manejo de envío de formularios */

function onPaymentFormSubmitted(event) {
    // obtiene el formulario
    const form = event.target;

    // previene el comportamiento predeterminado
    event.preventDefault();
    
    // TODO: agregar validación de datos
    
    //let name = form['tutor'].value;
    
    // crea una lista de identificadores de las cuotas en el pago
    let feeList = [];
    addedFees.forEach(fee => {
        feeList.push(fee['number']);
    });

    /* A partir de este punto la información ya está validada */

    const url = (isNewStudent) ? 
        'actions/register_student.php' :
        'actions/register_payment.php';

    let payload = new FormData();
    if (isNewStudent) {
        payload.append('new_student_info', JSON.stringify(newStudentInfo));
    } else {
        payload.append('student_id', studentId);
    }

    payload.append('fees', JSON.stringify(feeList));

    // crea un cliente para realizar peticiones HTTP
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        // verifica si el estado del cliente es completa y si el código de
        // respuesta es válido (200)
        if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
            // obtiene la respuesta recibida
            console.log(this.responseText);
        }
    };

    // establece el tipo de método y la URL de la petición
    xhr.open('POST', url);
    // envia la petición
    xhr.send(payload);
}

window.addEventListener('load', () => init());
