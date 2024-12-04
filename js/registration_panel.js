// almacena el identificador de filas de la tabla tutores
let tutorRowId = 1;

// abre el cuadro de diálogo de búsqueda de tutor
function openSearchTutorDialog() {
    showDialog('search-tutor-dialog');
}

// abre el cuadro de dialogo de registro de tutor
function openRegisterTutorDialog() {
    // obtiene el formulario de registro de tutor y lo reestablece
    const form = document.getElementById('register-tutor-form');
    form.reset();
    form['event_type'].value = 'registration';

    // muestra el dialogo
    showDialog('register-tutor-dialog');
}

// cierra el cuadro de diálogo de registro de tutor
function closeRegisterTutorDialog() {
    hideDialog('register-tutor-dialog');
}

// cambia el nivel educativo para ajustar los valores en el selector de grado
function changeEducationLevel() {
    // actualiza el estado del botón enviar formulario
    updateSubmitButtonStatus();

    // obtiene el código del nivel educativo seleccionado
    const levelCode = document.getElementById('student-education-level').value;
    // obtiene el elemento del selector de grado
    const gradeInput = document.getElementById('student-grade');
    // obtiene el selector de grupo
    const groupSelector = document.getElementById('student-group');
    // obtiene el selector de fecha de nacimiento
    const birthDateSelector = document.getElementById('student-birth-date');
    
    // recorre los niveles educativos hasta encontrar uno que esté asociado al
    // código recibido
    educationLevels.forEach(level => {
        if (level['code'] === levelCode) {
            const maxGrade = level['grade_count'];
            const minimumAge = level['minimum_age'];
            const maximumAge = level['maximum_age'];
            
            // actualiza los controles de grado y grupo
            gradeInput.value = 1;
            gradeInput.max = maxGrade;
            // habilita los selectores de grado, grupo y fecha de nacimiento
            gradeInput.removeAttribute('disabled');
            groupSelector.removeAttribute('disabled');
            birthDateSelector.removeAttribute('disabled');

            // calcula la fecha máxima y fecha mínima seleccionable
            /*
                Para el límite superior:
                - se calcula a partir de la fecha actual restándole la edad 
                mínima.
                Para el límite inferior:
                - se calcula a partir de la fecha actual restándole la edad
                máxima.
            */
           let maxDate = new Date(Date.now());
           maxDate.setFullYear(maxDate.getFullYear() - minimumAge);
           maxDate.setHours(0, 0, 1);

           let minDate = new Date(Date.now());
           minDate.setFullYear(minDate.getFullYear() - (maximumAge + 1));
           minDate.setHours(24);
           
            //establece los límites de fechas
            birthDateSelector.min = minDate.toISOString().substring(0,10);
            birthDateSelector.max = maxDate.toISOString().substring(0, 10);
            birthDateSelector.value = minDate.toISOString().substring(0,10);

            // carga los grupos para el grado seleccionado
            changeGrade();

            return;
        }
    });
}

// cambia el grado para mostrar los grupos correspondientes
function changeGrade() {
    // obtiene el código del nivel educativo seleccionado
    const levelCode = document.getElementById('student-education-level').value;
    // obtiene el grado seleccionado
    const grade = Number.parseInt(document.getElementById('student-grade').value);
    // obtiene el selector de grupo
    const groupSelector = document.getElementById('student-group');
    
    // Remueve cada una de las grupos presentes en el selector de grupos
    // mientras haya elementos (excepto el predeterminado)
    while (groupSelector.childElementCount > 1) {
        // obtiene la última opción agregada
        const option = groupSelector.lastElementChild;
        // verifica si esta no es la predeterminada
        if (option.value !== 'none') {
            // quita
            option.remove();
        }
    }

    // recorre los grupos hasta encontrar uno que cuyo grado y nivel educativo
    // coincidan
    groups.forEach(group => {
        // obtiene un valor que indica si el nivel educativo y grado coinciden
        let doesMatch = group['education_level']['code'] === levelCode && 
            group['grade'] === grade;

        if (doesMatch) {
            let option = document.createElement('option');
            option.value = group['number'];
            option.textContent = `${grade}-${group['letter']}`;
            
            groupSelector.appendChild(option);
        }
    });
}

// realiza la búsqueda de tutores
function searchTutors(query) {
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
                showFoundTutors(results);
                
                console.log('Found tutor count:', results.length);
            } else {
                // imprime el mensaje de error
                console.warn('Error:', data['message']);
            }
        }
    };

    // establece el tipo de método y la URL de la petición adjuntando la 
    // consulta recibida
    xhr.open('GET', 'queries/search.php?type=tutor&q=' + query);
    // envia la petición
    xhr.send();
}

// muestra a los tutores encontrados
function showFoundTutors(results) {
    // obtiene la tabla de resultados
    const table = document.getElementById('search-tutor-results-table');
    // obtiene la plantilla de la fila
    const rowTemplate = document.getElementById('found-tutor-row-template');
    // obtiene el elemento en donde están las filas
    const tbody = table.getElementsByTagName('tbody')[0];

    // Remueve cada una de las filas presentes en la tabla
    while (tbody.childElementCount > 0) {
        tbody.firstElementChild.remove();
    }

    // agrega una fila a la tabla por cada registro
    for (let i = 0; i < results.length; i++) {
        // obtiene los datos del tutor
        const item = results[i];
        const tutorName = item['full_name'];
        const tutorRfc = item['rfc'];
        const tutorId = item['id'];

        // agrega una nueva fila a la tabla
        tbody.appendChild(rowTemplate.content.cloneNode(true));
        let addedRow = tbody.lastElementChild;

        // obtiene los elementos que contendrán algún dato del tutor
        let nameField = addedRow.querySelector('[data-field-name=\'name\']');
        let rfcField = addedRow.querySelector('[data-field-name=\'rfc\']');
        let addButton = addedRow.querySelector('[data-action-name=\'add\']');
        
        // establece los valores
        nameField.textContent = tutorName;
        rfcField.textContent = tutorRfc;
        addButton.setAttribute('data-action-arg', JSON.stringify(item));
        addButton.onclick = function() {
            addTutorToList(
                JSON.parse(this.attributes['data-action-arg'].value)
            );
        };
    }

    if (results.length > 0) {
        table.hidden = false;
    } else {
        table.hidden = true;
    }
}

// edita a un tutor
function editStudentTutor(rowId) {
    // obtiene el elemento en donde están las filas de los tutores agregados
    const tbody = document.querySelector('#student-tutors-table tbody');
    // localiza la fila con el identificador del tutor asignado
    let row = tbody.querySelector(`tr[data-row-id=\'${rowId}\']`);
    
    if (row != null) {
        // obtiene los datos del tutor en la fila seleccionada
        const attachment = row.getAttribute('data-attachment');
        const tutorData = JSON.parse(attachment);

        // obtiene el formulario de registro de tutor
        const form = document.getElementById('register-tutor-form');
        form.reset();

        form['event_type'].value = 'update';
        form['row_id'].value = rowId;
        form['name'].value = tutorData['name'];
        form['first_surname'].value = tutorData['first_surname'];
        form['second_surname'].value = tutorData['second_surname'];
        form['rfc'].value = tutorData['rfc'];
        form['email'].value = tutorData['email'];
        form['phone_number'].value = tutorData['phone_number'];
        
        // muestra el dialogo
        showDialog('register-tutor-dialog');
    }
}

// agrega a un tutor a la lista
function addTutorToList(tutorData, canEdit = false) {
    // obtiene el elemento en donde están las filas de los tutores agregados
    const tbody = document.querySelector('#student-tutors-table tbody');
    // obtiene la plantilla de la fila
    const rowTemplate = document.getElementById('student-tutor-row-template');
    
    // agrega una nueva fila a la tabla
    tbody.appendChild(rowTemplate.content.cloneNode(true));
    let addedRow = tbody.lastElementChild;

    // obtiene los elementos que contendrán algún dato del tutor
    let nameField = addedRow.querySelector('[data-field-name=\'name\']');
    let removeButton = addedRow.querySelector('[data-action-name=\'remove\']');
    let rowId = tutorRowId++;
    
    if (canEdit) {
        // obtiene el boton de edición
        let editButton = addedRow.querySelector('[data-action-name=\'edit\']');
        
        // establece el identificador de fila y la acción
        editButton.setAttribute('data-action-arg', rowId);
        editButton.onclick = function() {
            editStudentTutor(this.attributes['data-action-arg'].value);
        };
        
        // hace que el botón sea visible
        editButton.hidden = false;
    }
    
    // establece los valores
    nameField.textContent = tutorData['full_name'];
    addedRow.setAttribute('data-row-id', rowId);
    addedRow.setAttribute('data-attachment', JSON.stringify(tutorData));
    removeButton.setAttribute('data-action-arg', rowId);
    removeButton.onclick = function() {
        removeTutorFromList(this.attributes['data-action-arg'].value);
    };

    // actualiza la visibilidad de los botones de agregar y buscar
    updateTutorSection();
}

// actualiza a un tutor en la lista
function updateTutorInList(tutorData, rowId) {
    // obtiene el elemento en donde están las filas de los tutores agregados
    const tbody = document.querySelector('#student-tutors-table tbody');
    // localiza la fila con el identificador del tutor asignado
    let row = tbody.querySelector(`tr[data-row-id=\'${rowId}\']`);
    
    if (row != null) {
        // actualiza los datos adjuntos
        row.setAttribute('data-attachment', JSON.stringify(tutorData));
        // obtiene el elemento que contiene el nombre del tutor
        let nameField = row.querySelector('[data-field-name=\'name\']');
        // establece el nombre del tutor
        nameField.textContent = tutorData['full_name'];
    }
}

// quita a un tutor de la lista
function removeTutorFromList(rowId) {
    // obtiene el elemento en donde están las filas de los tutores agregados
    const tbody = document.querySelector('#student-tutors-table tbody');
    // localiza la fila con el identificador del tutor asignado
    let row = tbody.querySelector(`tr[data-row-id=\'${rowId}\']`);
    // quita la fila de la lista
    row.remove();

    updateTutorSection();
}

// actualiza la visibilidad de los botones de buscar y registrar tutor
function updateTutorSection() {
    // obtiene el elemento en donde están las filas de los tutores agregados
    const tbody = document.querySelector('#student-tutors-table tbody');
    
    // verifica si ya se han agregado a dos tutores
    if (tbody.childElementCount >= 2) {
        hideElement('search-tutor-button');
        hideElement('register-tutor-button');
    } else {
        showElement('search-tutor-button');
        showElement('register-tutor-button');
    }

    // determina si se debe ocultar o mostrar la tabla de tutores
    if (tbody.childElementCount == 0) {
        hideElement('student-tutors-table');
    } else {
        showElement('student-tutors-table');
    }
}

// actualiza el estado del botón para enviar el formulario
function updateSubmitButtonStatus() {
    // obtiene los elementos clave cuyos valores seleccionados determinan el
    // estado del botón
    const submitButton = document.getElementById('registration-form-submit');
    const educationLevelSelect = document.getElementById('student-education-level');
    const groupSelect = document.getElementById('student-group');
    const genderSelect = document.getElementById('student-gender');
    const tutorsTable = document.getElementById('student-tutors-table');
    const tutorRows = tutorsTable.querySelectorAll('tbody tr');

    // determina si el botón de envío debe estar activo, para lo cual, se debe
    // tener seleccionado un nivel educativo, un grupo, un género y la cantidad
    // de tutores debe ser mayor que cero
    let enableSubmit = educationLevelSelect.value !== 'none' &&
        groupSelect.value !== 'none' &&
        genderSelect.value !== 'none' &&
        tutorRows.length > 0;

    // para cada fila en la tabla tutores, determina si el botón debe estar
    // activo basándose en que si el parentesco para el tutor está establecido
    for (let i = 0; i < tutorRows.length; i++) {
        const row = tutorRows[i];
        const relationshipSelect = row.querySelector('td select');

        enableSubmit &= (relationshipSelect.value !== 'none');
    }
    
    // habilita/deshabilita el botón
    if (enableSubmit) {
        submitButton.removeAttribute('disabled');
    } else {
        submitButton.setAttribute('disabled', true);
    }
}

/* manejo de eventos de formularios */

// es llamado cuando se envía el formulario de prevalidación de CURP
function onPrevalidationFormSubmitted(event) {
    // previene el comportamiento predeterminado
    event.preventDefault();

    // obtiene la curp
    let curp = document.getElementById('prevalidation-curp').value;

    // crea un cliente para realizar peticiones HTTP
    let xhr = new XMLHttpRequest();
    // adjunta un manejador de respuesta recibida
    xhr.onreadystatechange = function() {
        // verifica si el estado del cliente es completa y si el código de
        // respuesta es válido (200)
        if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
            // obtiene la respuesta en formato json
            let response = JSON.parse(this.responseText);

            // verifica si el resultado de la operación en la base de
            // datos fue existosa
            if (response['status'] === 'ok') {
                let isRegistered = response['is_registered'];

                if (isRegistered === true) {
                    // muestra la alerta de prevalidación fallida
                    showAlert('prevalidation-failed');
                    const link = document.getElementById('student_url');
                    const id = response['student']['student_id'];

                    link.setAttribute('href', 'student_panel.php?student_id=' + id);
                } else {
                    // oculta la alerta de prevalidación fallida
                    hideAlert('prevalidation-failed');
                    showAlert('prevalidation-success');
                    // muestra el formulario de registro
                    showElement('registration-form');
                    hideElement('prevalidation-form');
            
                    document.getElementById('student-curp').value = curp;
                }
            } else {
                // imprime el mensaje de error
                console.warn('Error:', response['message']);
            }
        }
    };

    // establece el tipo de método y la URL de la petición adjuntando la curp
    // ingresada
    xhr.open('GET', 'queries/prevalidate.php?curp=' + curp);
    // envia la petición
    xhr.send();
}

// es llamado cuando se envía el formulario de registro de un nuevo tutor
function onRegisterTutorFormSubmitted(event) {
    // obtiene el formulario
    const form = event.target;
    
    // TODO: agregar validación de datos
    // previene el comportamiento predeterminado
    //event.preventDefault();

    let name = form['name'].value;
    let first_surname = form['first_surname'].value;
    let second_surname = form['second_surname'].value;
    let full_name = `${name} ${first_surname} ${second_surname}`.trim();
    let rfc = form['rfc'].value;
    let email = form['email'].value;
    let phone_number = form['phone_number'].value;

    /* A partir de este punto la información ya está validada */
    
    // toma los datos ingreados
    let tutorData = {
        name: name,
        first_surname: first_surname,
        second_surname: second_surname,
        full_name: full_name,
        rfc: rfc,
        email: email,
        phone_number: phone_number
    };

    // obtiene el tipo de evento por el cual se abrió el formulario
    const eventType = form['event_type'].value;
    
    // procesa el tipo de evento que originó la apertura del formulario
    switch (eventType) {
        case 'registration':
            // agrega el tutor a la lista de tutores registrados
            addTutorToList(tutorData, true);
            break;
        case 'update':
            // actualiza el tutor en la lista
            updateTutorInList(tutorData, form['row_id'].value);
            break;
        default:
            break;
    }
}

// es llamado cuando se envía el formulario de registro de alumno
function onRegistrationFormSubmitted(event) {
    // obtiene el formulario
    const form = event.target;

    // previene el comportamiento predeterminado
    //event.preventDefault();
    
    // TODO: agregar validación de datos
    
    let name = form['name'].value;
    let first_surname = form['first_surname'].value;
    let second_surname = form['second_surname'].value;
    let birth_date = form['birth_date'].value;
    let gender_id = form['gender_id'].value;
    let curp = form['curp'].value;
    let ssn = form['ssn'].value;
    let address_street = form['address_street'].value;
    let address_number = form['address_number'].value;
    let address_district = form['address_district'].value;
    let address_zip = form['address_zip'].value;
    let registeredTutors = [];
    let unregisteredTutors = [];
    let education_level_id = form['education_level_id'].value;
    let grade = parseInt(form['grade'].value);
    let group_id = parseInt(form['group_id'].value);

    // obtiene todas las filas de los tutores agregados
    let tutorRows = document.querySelectorAll('#student-tutors-table tbody tr');
    tutorRows.forEach(row => {
        // obtiene los datos adjuntos
        const attachment = row.getAttribute('data-attachment');
        const relationshipSelect = row.querySelector('td select');
        let relationship = parseInt(relationshipSelect.value);

        // convierte los datos a un objeto
        let tutorData = JSON.parse(attachment);
        let tutorId = tutorData['id'];
        
        if (tutorId === undefined) {
            tutorData['relationship_id'] = relationship;
            unregisteredTutors.push(tutorData);
        } else {
            registeredTutors.push({
                id: parseInt(tutorId),
                relationship_id: relationship
            });
        }
    });

    /* A partir de este punto la información ya está validada */

    // toma los datos ingreados
    let studentData = {
        name: name,
        first_surname: first_surname,
        second_surname: second_surname,
        birth_date: birth_date,
        gender_id: gender_id,
        curp: curp,
        ssn: ssn,
        address: {
            street: address_street,
            number: address_number,
            district: address_district,
            zip: address_zip
        },
        tutors: {
            registered: registeredTutors,
            unregistered: unregisteredTutors
        },
        education_level_id: education_level_id,
        grade: grade,
        group_id: group_id
    };

    form['new_student_info'].value = JSON.stringify(studentData);
}

// es llamado cuando se restablece el formulario de registro de alumno
function onRegistrationFormReset(event) {
    console.log('hello');
}
