function onCriteriaSelectorChanged() {
    const selectedSchoolYear = document.getElementById('school-year').value;
    const queryButton = document.getElementById('query-button');
    const educationLevelSelect = document.getElementById('education-level');

    let enableQueryButton = selectedSchoolYear !== 'none';
    
    if (enableQueryButton) {
        queryButton.removeAttribute('disabled');
        educationLevelSelect.removeAttribute('disabled');
    } else {
        queryButton.setAttribute('disabled', true);
        educationLevelSelect.setAttribute('disabled', true);
    }
}

function retrieveStudents(groupId) {
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
                showStudents(results);
                
                console.log('Found student count:', results.length);
            } else {
                // imprime el mensaje de error
                console.warn('Error:', data['message']);
            }
        }
    };

    // establece el tipo de método y la URL de la petición adjuntando el 
    // identificador del grupo
    xhr.open('GET', 'queries/get_group_students.php?group_id=' + groupId);
    // envia la petición
    xhr.send();
}

function showStudents(students) {
    // obtiene cada una de los elementos necesarios
    const dialog = document.getElementById('show-students-dialog');
    const table = document.getElementById('students-group-table');
    const rowTemplate = document.getElementById('students-group-row-template');
    const tbody = table.getElementsByTagName('tbody')[0];

    // Remueve cada una de las filas presentes en la tabla
    while (tbody.childElementCount > 0) {
        tbody.firstElementChild.remove();
    }

    for (let i = 0; i < students.length; i++) {
        const student = students[i];
        const studentId = student['student_id'];
        const name = student['name'];
        const firstSurname = student['first_surname'];
        const secondSurname = student['second_surname'];
        const enrollmentStatus = student['enrollment_status']['description'];

        // agrega una nueva fila a la tabla
        tbody.appendChild(rowTemplate.content.cloneNode(true));
        let addedRow = tbody.lastElementChild;

        // obtiene los elementos que contendrán algún dato del alumno
        let studentIdField = addedRow.querySelector('[data-field-name=\'student_id\']');
        let nameField = addedRow.querySelector('[data-field-name=\'name\']');
        let firstSurnameField = addedRow.querySelector('[data-field-name=\'first_surname\']');
        let secondSurnameField = addedRow.querySelector('[data-field-name=\'second_surname\']');
        let enrollmentStatusField = addedRow.querySelector('[data-field-name=\'enrollment_status\']');
        let viewButton = addedRow.querySelector('[data-action-name=\'view\']');

        studentIdField.textContent = studentId;
        nameField.textContent = name;
        firstSurnameField.textContent = firstSurname;
        secondSurnameField.textContent = secondSurname;
        enrollmentStatusField.textContent = enrollmentStatus;
        viewButton.setAttribute('data-action-arg', studentId);
        viewButton.onclick = function () {
            window.location.href = 'student_panel.php?student_id=' + studentId;
        };
    }

    dialog.showModal();
}

window.addEventListener('load', () => onCriteriaSelectorChanged());
