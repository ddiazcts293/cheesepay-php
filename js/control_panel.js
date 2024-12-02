function addNewUniformFee() {
    const template = document.getElementById('uniform-fee-row-template');
    const tbody = document.querySelector('#uniform-fees-table tbody');

    tbody.appendChild(template.content.cloneNode(true));
    const addedRow = tbody.lastElementChild;
    addedRow.querySelector('[data-field-action=\'remove\']').onclick = (e) => {
        e.target.parentElement.parentElement.remove();
    };
}

function addNewAddionalGruop() {
    const template = document.getElementById('additional-groups-row-template');
    const tbody = document.querySelector('#additional-groups-table tbody');

    tbody.appendChild(template.content.cloneNode(true));
    const addedRow = tbody.lastElementChild;
    addedRow.querySelector('[data-field-action=\'remove\']').onclick = (e) => {
        e.target.parentElement.parentElement.remove();
    };
}

function validateSelect(select) {
    if (select.value === 'none') {
        select.value = undefined;
    }
    return select.reportValidity();
}

// registra un nuevo evento especial
function onNewSpecialEventFormSubmitted(event) {
    // realizar validación de campos

    // obtiene el formulario
    const form = document.getElementById('new-special-event-form');
    // obtiene el costo establecido
    const cost = parseFloat(form['cost'].value);

    if (cost <= 0) {
        console.warn('Cost cannot be equal or less than 0.');
        event.preventDefault();
    }
}

function onNewSchoolYearFormSubmitted(event) {
    const newSchoolYearForm = document.getElementById('new-school-year-form');
    const newSchoolYearInfoForm = document.getElementById('new-school-year-info-form');

    // obtiene las fechas ingresadas
    const startingDate = new Date(newSchoolYearForm['starting_date'].value);
    const endingDate = new Date(newSchoolYearForm['ending_date'].value);

    // calcula la diferencia de meses entre la fechas obtenidas
    const startYear = startingDate.getFullYear();
    const startMonth = startingDate.getMonth();
    const endYear = endingDate.getFullYear();
    const endMonth = endingDate.getMonth();
    const yearsDiff = endYear - startYear;
    const monthDiff = (yearsDiff * 12) + endMonth - startMonth;

    // determina si deberá habilitar el formulario para registrar cuotas
    let enableNewSchoolYear = monthDiff >= 10;    
    newSchoolYearInfoForm.hidden = !enableNewSchoolYear;
    newSchoolYearForm['starting_date'].readOnly = enableNewSchoolYear;
    newSchoolYearForm['ending_date'].readOnly = enableNewSchoolYear;

    // previene el comportamiento predeterminado
    event.preventDefault();
}

function onNewSchoolYearInfoFormSubmitted(event) {
    // previente el comportamiento predeterminado
    event.preventDefault();

    // obtiene los formularios
    const schoolYearForm = document.getElementById('new-school-year-form')
    const form = document.getElementById('new-school-year-info-form');
    // obtiene los elementos que contienen datos
    const enrollmentMonthlyRows = document.querySelectorAll(
        '#enrollment-monthly-fees-table tbody tr'
    );    
    const stationeryRows = document.querySelectorAll(
        '#stationery-fees-table tbody tr'
    );
    const uniformRows = document.querySelectorAll(
        '#uniform-fees-table tbody tr'
    );
    const addionalGroupRows = document.querySelectorAll(
        '#additional-groups-table tbody tr'
    );

    let enrollmentFees = [];
    let monthlyFees = [];
    let stationeryFees = [];
    let uniformFees = []; 
    let additionalGroups = [];   

    // procesa cada fila para extraer los datos de inscripciones y mensualidades
    enrollmentMonthlyRows.forEach(row => {
        const levelId = row.getAttribute('data-row-id');
        const enrollmentCost = parseFloat(
            row.querySelector('[data-field-name=\'enrollment_cost\'] input').value
        );
        const monthlyCost = parseFloat(
            row.querySelector('[data-field-name=\'monthly_cost\'] input').value
        );

        enrollmentFees.push({
            education_level: levelId,
            cost: enrollmentCost
        });

        monthlyFees.push({
            education_level: levelId,
            cost: monthlyCost
        });
    });
    // procesa cada fila para extraer los datos de cuotas de papelería
    stationeryRows.forEach(row => {
        const levelId = row.querySelector('[data-field-name=\'education_level\']')
            .getAttribute('data-field-value');
        const grade = row.querySelector('[data-field-name=\'grade\']')
            .getAttribute('data-field-value');
        const concept = row.querySelector('[data-field-name=\'concept\'] input').value;
        const cost = row.querySelector('[data-field-name=\'cost\'] input').value;

        stationeryFees.push({
            education_level: levelId,
            grade: parseInt(grade),
            concept: concept,
            cost: parseFloat(cost)
        });
    });

    for (let i = 0; i < uniformRows.length; i++) {
        const row = uniformRows[i];
        const levelSelect = row.querySelector('[data-field-name=\'education_level\'] select');
        const typeSelect = row.querySelector('[data-field-name=\'type\'] select');

        if (!(validateSelect(levelSelect) && validateSelect(typeSelect))) {
            return;
        }

        const levelId = levelSelect.value;
        const type = typeSelect.value;
        const concept = row.querySelector('[data-field-name=\'concept\'] input').value;
        const size = row.querySelector('[data-field-name=\'size\'] input').value;
        const cost = row.querySelector('[data-field-name=\'cost\'] input').value;

        uniformFees.push({
            education_level: levelId,
            concept: concept,
            size: size,
            type: type,
            cost: parseFloat(cost)
        });
    }

    for (let i = 0; i < addionalGroupRows.length; i++) {
        const row = addionalGroupRows[i];
        const levelSelect = row.querySelector('[data-field-name=\'education_level\'] select');

        if (!validateSelect(levelSelect)) {
            return;
        }

        const levelId = levelSelect.value;
        const grade = row.querySelector('[data-field-name=\'grade\'] input').value;
        const quantity = row.querySelector('[data-field-name=\'quantity\'] input').value;

        additionalGroups.push({
            education_level: levelId,
            grade: parseInt(grade),
            quantity: parseInt(quantity)
        });
    };

    // crea un objeto con la información del ciclo escolar
    let json = {
        starting_date: schoolYearForm['starting_date'].value,
        ending_date: schoolYearForm['ending_date'].value,
        fees: {
            maintenance: {
                concept: form['maintenance_concept'].value,
                cost: parseFloat(form['maintenance_cost'].value)
            },
            enrollment: enrollmentFees,
            monthly: monthlyFees,
            uniform: uniformFees,
            stationery: stationeryFees
        },
        additional_groups: additionalGroups
    };

    // crea un objeto FormData para almacenar la información
    let formData = new FormData();
    formData.append('new_school_year_info', JSON.stringify(json));
    // crea un cliente http
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(e) {
        
    };

    xhr.open('POST', 'actions/register_school_year.php')
    xhr.send(formData);
}
