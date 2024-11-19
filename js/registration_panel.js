// realiza la verificación de la CURP antes de comenzar con el registro
function prevalidate() {
    // obtiene la curp
    let curp = document.forms['prevalidation-form']['curp'].value;
    // verifica que la CURP esté definida y su longitud sea mayor de 18 
    // caracteres
    if (curp !== undefined && curp.length == 18) {
        // crea un cliente para realizar peticiones HTTP
        let xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
                let response = JSON.parse(this.responseText);
                let isRegistered = response['is_registered'];

                if (isRegistered === true) {
                    showAlert('prevalidation-failed');
                } else {
                    hideAlert('prevalidation-failed');
                    showAlert('prevalidation-success');
                    showElement('registration-form');
                    hideElement('prevalidation-form');
                }
            }
        };
        
        xhr.open('GET', 'queries/prevalidate.php?curp=' + curp);
        xhr.send();
    }

    return false;
}

function changeEducationLevel(levelCode) {
    // obtiene el elemento del selector de grado
    let gradeInput = document.getElementById('prevalidation-grade');
    
    // verifica si el código corresponde al elemento predeterminado
    if (levelCode === 'none') {
        // deshabilita el selector de grado
        gradeInput.setAttribute('disabled', true)
    } else {
        // crea un cliente para realizar peticiones HTTP
        let xhr = new XMLHttpRequest();
        // adjunta un manejador de respuesta recibida
        xhr.onreadystatechange = function() {
            // verifica si el estado del cliente es completa y si el código de 
            // respuesta es válido (200)
            if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
                let level = JSON.parse(this.responseText);
                
                gradeInput.value = 1;
                gradeInput.max = level.grade_count;
                gradeInput.removeAttribute('disabled');
            }
        };

        // establece el tipo de método y la URL de la petición adjuntando el
        // código del nivel educativo seleccionado
        xhr.open('GET', 'queries/get_education_level.php?code=' + levelCode);
        // envia la petición
        xhr.send();
    }
}

//window.addEventListener('load', init);
