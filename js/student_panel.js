// abre el cuadro de diálogo para editar una dirección
function openEditAddressDialog() {
    showDialog('edit-address-dialog');
    document.getElementById('edit-address-error').hidden = true;
}

// abre el cuadro de diálogo para establece el NSS
function openSetSsnDialog() {
    showDialog('set-ssn-dialog');
    document.getElementById('set-ssn-error').hidden = true;
}

// abre el cuadro de diálogo para ver la información de un tutor
function openViewTutorInfo(tutorId) {
    // obtiene el índice del objeto con la información del tutor
    const tutorIndex = tutors.findIndex(tutor => tutor['number'] == tutorId);
    if (tutorIndex === -1) {
        return;
    }

    // obtiene el objeto con la información del tutor
    const tutor = tutors[tutorIndex];

    // obtiene los elementos para mostrar los datos del tutor
    const tutorInfoName = document.getElementById('tutor-info-name');
    const tutorInfoRelationship = document.getElementById('tutor-info-relationship');
    const tutorInfoRfc = document.getElementById('tutor-info-rfc');
    const tutorInfoPhoneNumber = document.getElementById('tutor-info-phone-number');
    const tutorInfoEmail = document.getElementById('tutor-info-email');
    
    // establece los datos en los elementos
    tutorInfoName.textContent = `${tutor['name']} ${tutor['first_surname']} ${tutor['second_surname']}`;
    tutorInfoRelationship.textContent = tutor['relationship']['description'];
    tutorInfoRfc.textContent = tutor['rfc'];
    tutorInfoPhoneNumber.textContent = tutor['phone_number'];
    tutorInfoEmail.textContent = tutor['email'];

    showDialog('view-tutor-info-dialog');
}

// abre el cuadro de diálogo para edicar el contacto de un tutor
function openEditTutorContact(tutorId) {
    // obtiene el formulario
    const form = document.getElementById('edit-tutor-contact-form');
    document.getElementById('edit-tutor-contact-error').hidden = true;

    // obtiene el índice del objeto con la información del tutor
    const tutorIndex = tutors.findIndex(tutor => tutor['number'] == tutorId);
    if (tutorIndex === -1) {
        return;
    }

    // obtiene el objeto con la información del tutor
    const tutor = tutors[tutorIndex];

    // establece los datos en sus respectivos campos
    form['email'].value = tutor['email'];
    form['phone_number'].value = tutor['phone_number'];
    form['tutor_id'].value = tutor['number'];

    showDialog('edit-tutor-contact-dialog');
}

// realiza la búsqueda de alumnos
function searchStudents(query) {
    // realiza la petición para buscar alumnos
    fetch('queries/search.php?type=student&q=' + query, { 
        method: 'GET' 
    // procesa la respuesta una vez recibida
    }).then((response) => {
        // verifica que la respuesta sea exitosa
        if (response.ok) {
            // obtiene la respuesta en formato json
            response.json().then((data) => {
                // verifica si el resultado de la operación en la base de datos
                // fue exitoso
                if (data['status'] === 'ok') {
                    // despliega los resultados
                    let results = data['data'];
                    console.log('Found student count:', results.length);
                    showFoundStudents(results);
                } else {
                    // muestra un mensaje de alerta
                    console.warn('Error:', data['message']);
                }
            });
        // de lo contrario imprime el código de estado
        } else {
            console.log('Error processing request: ', response.status);
        }
    // procesa el mensaje de error en caso de producirse
    }).catch(error => {
        console.error('Error making request: ', error);
    })
}

// muestra los resultados de búsqueda
function showFoundStudents(results) {
    const resultsTable = document.getElementById('search-results');
    const baseUrl = window.location.origin + 
        window.location.pathname + 
        '?student_id=';
    
    while (resultsTable.childElementCount > 0) {
        resultsTable.firstElementChild.remove();
    }

    for (let i = 0; i < results.length; i++) {
        const item = results[i];
        const element = document.createElement('p');
        const link = document.createElement('a');

        link.innerText = item['full_name'];
        link.setAttribute('href', baseUrl + item['student_id']);
        element.append(link);
        resultsTable.append(element);
    }
}

function printInvoice(paymentId) {
    window.open(
        'actions/generate_invoice.php?payment_id=' + paymentId,
        'popup_window',
        'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=1200,height=800,left=100,top=100'
    );
}

function viewPayment(paymentId) {
    window.location.href = 'payment_panel.php?payment_id=' + paymentId;
}

/* manejo de eventos */

// maneja la actualización del número de seguro social
function onSetSsnFormSubmitted(event) {
    // previene el comportamiento predeterminado
    event.preventDefault();
    // obtiene el formulario
    const form = document.getElementById('set-ssn-form');
    
    // TODO: agregar validación de datos antes de enviar los datos
        
    /* A partir de este punto la información ya está validada */
    
    // crea un nuevo objeto FormData
    const formData = new FormData(form);
    // realiza la petición para actualizar la dirección
    fetch('actions/set_student_ssn.php', {
        method: 'POST',
        body: formData
    // procesa la respuesta una vez recibida
    }).then((response) => {
        // verifica que la respuesta sea exitosa
        if (response.ok) {
            // obtiene la respuesta en formato json
            response.json().then((data) => {
                // verifica si el resultado de la operación en la base de datos
                // fue exitoso
                if (data['status'] === 'ok') {
                    // recarga la página para mostrar los cambios hechos
                    window.location.reload();
                } else {
                    // muestra un mensaje de alerta
                    console.warn('Error:', data['message']);
                }
            });
        // de lo contrario imprime el código de estado
        } else {
            
            console.log('Error processing request: ', response.status);
        }
    // procesa el mensaje de error en caso de producirse
    }).catch(error => {
        console.error('Error making request: ', error);
    })
}

// maneja la actualización de la dirección
function onEditAddressFormSubmitted(event) {
    // previene el comportamiento predeterminado
    event.preventDefault();
    // obtiene el formulario
    const form = document.getElementById('edit-address-form');
    
    // TODO: agregar validación de datos antes de enviarlos
    
    /* A partir de este punto la información ya está validada */
    
    // crea un nuevo objeto FormData
    const formData = new FormData(form);
    // realiza una petición POST para actualizar la dirección
    fetch('actions/update_student_address.php', {
        method: 'POST',
        body: formData
    }).then((response) => { // procesa la respuesta una vez recibida
        // verifica que la respuesta sea exitosa
        if (response.ok) {
            // obtiene la respuesta en formato json
            response.json().then((data) => {
                // verifica si el resultado de la operación en la base de datos
                // fue exitoso
                if (data['status'] === 'ok') {
                    // recarga la página para mostrar los cambios hechos
                    window.location.reload();
                } else {
                    // imprime el mensaje de error
                    console.warn('Error:', data['message']);
                }
            });
        } else {
            // de lo contrario imprime el código de estado
            console.log('Error processing request: ', response.status);
        }
        // procesa el mensaje de error en caso de producirs
    }).catch(error => console.error('Error making request: ', error));
};

// maneja la actualización del contacto de un tutor
function onEditTutorContactFormSubmitted(event) {
    // previene el comportamiento predeterminado
    event.preventDefault();
    // obtiene el formulario
    const form = document.getElementById('edit-tutor-contact-form');
    
    // TODO: agregar validación de datos antes de enviarlos
    
    /* A partir de este punto la información ya está validada */
    
    // crea un nuevo objeto FormData
    const formData = new FormData(form);

    // crea un cliente para realizar peticiones HTTP
    let xhr = new XMLHttpRequest();
    // establece un manejador cuando se recibe una respuesta del servidor
    xhr.onreadystatechange = function () {
        // verifica si el estado del cliente es completa y si el código de
        // respuesta es válido (200)
        if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
            // obtiene la respuesta en formato json
            let response = JSON.parse(this.responseText);
            
            // verifica si el resultado de la operación en la base de
            // datos fue existos
            if (response['status'] === 'ok') {
                // recarga la página para mostrar los cambios hechos
                window.location.reload();
            } else {
                // imprime el mensaje de error
                console.warn('Error:', data['message']);
            }
        }
    };

    // establece el tipo de método y la URL de la petición
    xhr.open('POST', 'actions/update_tutor_contact.php');
    // envia la petición junto con los datos del formulario
    xhr.send(formData);
}
