// Aquí van las funciones específicas para la página del Panel de Control

// Función para actualizar los meses según el año seleccionado
// Utilizamos sufijos para diferencias entre las diviones que hagan uso de este
// mismo selector de fechas.
function updateMonths(suffix) {
    var year = document.getElementById("date-year" + suffix).value;
    var monthSelect = document.getElementById("date-month" + suffix);

    // Limpiar las opciones de mes y día
    monthSelect.innerHTML = "<option value=''>Mes</option>";
    document.getElementById("date-day" + suffix).innerHTML = "<option value=''>Día</option>";

    if (year) {
        var months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", 
                      "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        
        // Agregar los meses al selector
        months.forEach(function(month, index) {
            var option = document.createElement("option");
            option.value = index + 1;
            option.textContent = month;
            monthSelect.appendChild(option);
        });
    }
}

// Función para actualizar los días según el mes y año seleccionados
function updateDays(suffix) {
    var year = document.getElementById("date-year" + suffix).value;
    var month = document.getElementById("date-month" + suffix).value;
    var daySelect = document.getElementById("date-day" + suffix);

    // Limpiar las opciones de día
    daySelect.innerHTML = "<option value=''>Día</option>";

    if (year && month) {
        var daysInMonth;
        
        // Determinar la cantidad de días según el mes y año (considerando años bisiestos)
        if (month == 2) {
            daysInMonth = (isLeapYear(year)) ? 29 : 28; // Febrero tiene 29 días si es bisiesto
        } else if ([4, 6, 9, 11].includes(parseInt(month))) {
            daysInMonth = 30; // Meses con 30 días
        } else {
            daysInMonth = 31; // Meses con 31 días
        }

        // Agregar los días al selector
        for (var i = 1; i <= daysInMonth; i++) {
            var option = document.createElement("option");
            option.value = i;
            option.textContent = i;
            daySelect.appendChild(option);
        }
    }
}

// Función para verificar si un año es bisiesto
function isLeapYear(year) {
    return (year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0));
}