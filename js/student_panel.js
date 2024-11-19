function init() {
    
}

function openEditAddressDialog() {
    showModal('edit-address-dialog');
}

function openSetSsnDialog() {
    showModal('set-ssn-dialog');
}

function submitAddress(event) {
    // previene el comportamiento predeterminado
    event.preventDefault();
    // obtiene el formulario
    const form = document.getElementById('edit-address-form');
    
    // TODO: agregar validación de datos antes de enviar los datos
    
    /* A partir de este punto la información ya está validada */
    
    // crea un nuevo objeto FormData
    const formData = new FormData(form);
    // realiza la petición para actualizar la dirección
    fetch('queries/update_student_address.php', {
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
                    console.warn('DB Error:', data['message']);
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
};

function submitSsn(event) {
    // previene el comportamiento predeterminado
    event.preventDefault();
    // obtiene el formulario
    const form = document.getElementById('set-ssn-form');
    
    // TODO: agregar validación de datos antes de enviar los datos
        
    /* A partir de este punto la información ya está validada */
    
    // crea un nuevo objeto FormData
    const formData = new FormData(form);
    // realiza la petición para actualizar la dirección
    fetch('queries/set_student_ssn.php', {
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
                    console.warn('DB Error:', data['message']);
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

function search(query) {
    // realiza la petición para actualizar la dirección
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
                    showSearchResults(results);
                } else {
                    // muestra un mensaje de alerta
                    console.warn('DB Error:', data['message']);
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

function showSearchResults(results) {
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

window.addEventListener('load', () => init());
