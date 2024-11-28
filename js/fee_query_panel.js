function onCriteriaSelectorChanged() {
    const selectedFeeType = document.getElementById('fee-type').value;
    const selectedSchoolYear = document.getElementById('school-year').value;
    const queryButton = document.getElementById('query-button');

    const uniformSection = document.getElementById('uniform-section');
    const educationLevelSection = document.getElementById('education-level-section');
    const uniformTypeSelect = document.getElementById('uniform-type');
    const educationLevelSelect = document.getElementById('education-level');

    if (selectedFeeType === 'uniform') {
        uniformSection.hidden = false;
    } else {
        uniformSection.hidden = true;
    }

    if (selectedFeeType === 'maintenance' || 
        selectedFeeType === 'special_event'
    ) {
        educationLevelSection.hidden = true;
    } else {
        educationLevelSection.hidden = false;
    }
    
    let enableQueryButton = selectedFeeType !== 'none' && 
        selectedSchoolYear !== 'none';
    
    if (enableQueryButton) {
        queryButton.removeAttribute('disabled');
        uniformTypeSelect.removeAttribute('disabled');
        educationLevelSelect.removeAttribute('disabled');
    } else {
        queryButton.setAttribute('disabled', true);
        uniformTypeSelect.setAttribute('disabled', true);
        educationLevelSelect.setAttribute('disabled', true);
    }
}

function retrieveStudents(feeId) {
    // crea un cliente para realizar peticiones HTTP
    let xhr = new XMLHttpRequest();
    // adjunta un manejador de respuesta recibida
    xhr.onreadystatechange = function() {
        // verifica si el estado del cliente es completa y si el código de
        // respuesta es válido (200)
        if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
            // obtiene la respuesta en formato json
            let data = JSON.parse(this.responseText);

            // verifica si el resultado de la operación en la base de datos
            // fue exitoso
            if (data['status'] === 'ok') {
                // obtiene y despliega los resultados
                let results = data['data'];
                showStudentPaymens(results);
                
                console.log('Found student count:', results.length);
            } else {
                // imprime el mensaje de error
                console.warn('Error:', data['message']);
            }
        }
    };

    // establece el tipo de método y la URL de la petición adjuntando el 
    // identificador del grupo
    xhr.open('GET', 'queries/get_fee_students.php?fee_id=' + feeId);
    // envia la petición
    xhr.send();
}

function showStudentPaymens(list) {
    // obtiene cada una de los elementos necesarios
    const dialog = document.getElementById('show-students-dialog');
    const table = document.getElementById('students-group-table');
    const rowTemplate = document.getElementById('students-group-row-template');
    const tbody = table.getElementsByTagName('tbody')[0];

    // Remueve cada una de las filas presentes en la tabla
    while (tbody.childElementCount > 0) {
        tbody.firstElementChild.remove();
    }

    for (let i = 0; i < list.length; i++) {
        const payment = list[i];
        const paymentId = payment['payment_id'];
        const paymentDate = payment['date'];
        const studentId = payment['student']['student_id'];
        const name = payment['student']['name'];
        const firstSurname = payment['student']['first_surname'];
        const secondSurname = payment['student']['second_surname'];

        // agrega una nueva fila a la tabla
        tbody.appendChild(rowTemplate.content.cloneNode(true));
        let addedRow = tbody.lastElementChild;

        // obtiene los elementos que contendrán algún dato del alumno
        let paymentIdField = addedRow.querySelector('[data-field-name=\'payment_id\']');
        let paymentDateField = addedRow.querySelector('[data-field-name=\'payment_date\']');
        let studentIdField = addedRow.querySelector('[data-field-name=\'student_id\']');
        let nameField = addedRow.querySelector('[data-field-name=\'name\']');
        let firstSurnameField = addedRow.querySelector('[data-field-name=\'first_surname\']');
        let secondSurnameField = addedRow.querySelector('[data-field-name=\'second_surname\']');        
        let viewButton = addedRow.querySelector('[data-action-name=\'view\']');

        paymentIdField.textContent = paymentId;
        paymentDateField.textContent = paymentDate;
        studentIdField.textContent = studentId;
        nameField.textContent = name;
        firstSurnameField.textContent = firstSurname;
        secondSurnameField.textContent = secondSurname;
        viewButton.setAttribute('data-action-arg', paymentId);
        viewButton.onclick = function () {
            window.location.href = 'payment_panel.php?payment_id=' + paymentId;
        };
    }

    dialog.showModal();
}

window.addEventListener('load', () => onCriteriaSelectorChanged());
