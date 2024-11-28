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

// consulta la información de una cuota en la base de datos
function retrieveFee() {
    // obtiene el tipo de cuota seleccionado
    const feeType = document.getElementById('fee-type').value;
    let feeId = null;

    // procesea el tipo de cuota
    switch (feeType) {
        case 'stationery':
            // usar codigo de nivel educativo
            feeId = stationeryFee;
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
    xhr.open('GET', 'queries/get_fee.php?id=' + feeId);
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

function showMessageDialog(success, studentId, paymentId = null, reason = null) {
    // obtiene el cuadro de diálogo
    const dialog = document.getElementById('message-dialog');
    const form = document.getElementById('message-form');
    const successText = document.getElementById('message-dialog-success');
    const failText = document.getElementById('message-dialog-fail');
    const failReasonText = document.getElementById('message-dialog-fail-reason');

    if (success) {
        form['student_id'].value = studentId;
        form['payment_id'].value = paymentId;
        successText.hidden = false;
        failText.hidden = true;
        form.action = 'student_panel.php';
        form.method = 'GET'
    } else {
        form['student_id'].value = '';
        form['payment_id'].value = '';
        successText.hidden = true;
        failText.hidden = false;
        form.action = '#';
        form.method = 'DIALOG'
        failReasonText.textContent = reason;
    }

    dialog.showModal();
}

function registerStudent() {
    // verifica si se está registrando a un alumno
    if (!isNewStudent) {
        console.log('Invalid state');
        return;
    }

    // crea un arreglo para almacenar los identificadores de las cuotas a pagar
    let paymentFees = [];
    for (let index = 0; index < addedFees.length; index++) {
        const fee = addedFees[index];
        
        if (fee['number'] != enrollmentInfo['fee_id']) {
            paymentFees.push(fee['number']);
        }
    }

    // crea un nuevo archivo JSON con los datos requeridos
    let payload = {
        name: newStudentInfo['name'],
        first_surname: newStudentInfo['first_surname'],
        second_surname: newStudentInfo['second_surname'],
        birth_date: newStudentInfo['birth_date'],
        gender_id: newStudentInfo['gender_id'],
        curp: newStudentInfo['curp'],
        ssn: newStudentInfo['ssn'],
        address: newStudentInfo['address'],
        tutors: newStudentInfo['tutors'],
        payment_fees: paymentFees,
        enrollment: enrollmentInfo
    };

    let formData = new FormData();
    formData.append('new_student_info', JSON.stringify(payload));

    // crea un cliente para realizar peticiones HTTP
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        // verifica si el estado del cliente es completa y si el código de
        // respuesta es válido (200)
        if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
            // obtiene la respuesta en formato json
            let response = JSON.parse(this.responseText);

            // verifica si el resultado de la operación en la base de datos fue
            // existosa
            if (response['status'] === 'ok') {
                // imprime el mensaje obtenido en la operación
                console.log(response);

                // obtiene el identificador del alumno registrado
                let studentId = response['data']['student_id'];
                let paymentId = response['data']['payment_id'];
                console.log('Student was registered successfully, id:', studentId);

                showMessageDialog(true, studentId, paymentId);
            } else {
                let reason = response['message'];
                
                // imprime el mensaje obtenido en la operación
                console.error(reason);
                showMessageDialog(false, null, null, reason);
            }
        }
    };

    // establece el tipo de método y la URL de la petición
    xhr.open('POST', 'actions/register_student.php');
    // envia la petición con los datos
    xhr.send(formData);
}

// realiza el registro de un pago
function registerPayment() {
    // verifica si no se está registrando a un alumno
    if (isNewStudent) {
        console.log('Invalid state');
        return;
    }

    const tutorSelector = document.getElementById('student-tutor');
    if (tutorSelector.value === 'none') {
        return;
    }

    // crea un arreglo para almacenar los identificadores de las cuotas a pagar
    let paymentFees = [];
    for (let index = 0; index < addedFees.length; index++) {
        const fee = addedFees[index];
        paymentFees.push(fee['number']);
    }

    // crea un nuevo archivo JSON con los datos requeridos
    let payload = {
        student_id: studentId,
        tutor_id: parseInt(tutorSelector.value),
        fees: paymentFees
    };

    let formData = new FormData();
    formData.append('payment_info', JSON.stringify(payload));

    // crea un cliente para realizar peticiones HTTP
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {

        // verifica si el estado del cliente es completa y si el código de
        // respuesta es válido (200)
        if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
            // obtiene la respuesta en formato json
            let response = JSON.parse(this.responseText);

            // verifica si el resultado de la operación en la base de datos fue
            // existosa
            if (response['status'] === 'ok') {
                // imprime el mensaje obtenido en la operación
                console.log(response);

                // obtiene el identificador del pago registrado
                let paymentId = response['data']['payment_id'];
                let studentId = response['data']['student_id'];
                console.log('Payment was registered successfully, id:', paymentId);

                showMessageDialog(true, studentId, paymentId);
            } else {
                let reason = response['message'];

                // imprime el mensaje obtenido en la operación
                console.error(reason);
                showMessageDialog(false, null, null, reason);
            }
        }
    };

    // establece el tipo de método y la URL de la petición
    xhr.open('POST', 'actions/register_payment.php');
    // envia la petición con los datos
    xhr.send(formData);
}

function printInvoice(paymentId) {
    window.open(
        'actions/generate_invoice.php?payment_id=' + paymentId,
        'popup_window',
        'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=1200,height=800,left=100,top=100'
    );
}

/* manejo de eventos */

// cambia el tipo de cuota
function onFeeTypeChanged() {
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
            onMonthlyFeeChanged();
            break;
        case 'uniform':
            monthlySelector.hidden = true;
            uniformSelector.hidden = false;
            specialEventSelector.hidden = true;
            onUniformTypeChanged();
            break;
        case 'special_event':
            monthlySelector.hidden = true;
            uniformSelector.hidden = true;
            specialEventSelector.hidden = false;
            onSpecialEventFeeChanged();
            break;
        default:
            monthlySelector.hidden = true;
            uniformSelector.hidden = true;
            specialEventSelector.hidden = true;
            addFeeButton.setAttribute('disabled', true);
            break;
    }
}

// cambia a cuota de mensualidad
function onMonthlyFeeChanged() {
    const monthlyFee = document.getElementById('monthly-fee').value;
    const addFeeButton = document.getElementById('add-fee-button');

    if (monthlyFee === 'none') {
        addFeeButton.setAttribute('disabled', true);
    } else {
        addFeeButton.removeAttribute('disabled');
    }
}

// cambia a tipo de uniforme
function onUniformTypeChanged() {
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

// cambia a cuota de uniforme
function onUniformFeeChanged() {
    const uniformType = document.getElementById('uniform-type').value;
    const addFeeButton = document.getElementById('add-fee-button');

    if (uniformType === 'none') {
        addFeeButton.setAttribute('disabled', true);
    } else {
        addFeeButton.removeAttribute('disabled');
    }
}

// cambia a cuota de evento especial
function onSpecialEventFeeChanged() {
    const specialEvent = document.getElementById('special-event-fee').value;
    const addFeeButton = document.getElementById('add-fee-button');

    if (specialEvent === 'none') {
        addFeeButton.setAttribute('disabled', true);
    } else {
        addFeeButton.removeAttribute('disabled');
    }
}

// ocurre cuando se está enviando el formulario del pago
function onPaymentFormSubmitted(event) {
    // obtiene el formulario
    const form = event.target;

    // previene el comportamiento predeterminado
    event.preventDefault();
    
    // TODO: agregar validación de datos
    
    //let name = form['tutor'].value;
    
    /* A partir de este punto la información ya está validada */

    if (isNewStudent) {
        registerStudent();
    } else {
        registerPayment();
    }
}

function onMessageFormSubmitted(event) {
    const form = document.getElementById('message-form');
    const paymentId = form['payment_id'].value;

    if (paymentId !== null && paymentId.length > 0) {
        printInvoice(paymentId);
    }
}

window.addEventListener('load', () => init());
