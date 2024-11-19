# CheesePay v2.0

Más bonito y mejor.

# Páginas

## Inicio

### Objetivo
Ser el punto de partida para acceder a los diferentes apartados del sistema.

### Acciones
- Buscar a un alumno utilizando algún dato conocido.

## Panel de información de alumno

### Objetivo

Mostrar información acerca de un alumno así como proporcionar acceso a las acciones administrativas que pueden realizarse sobre el mismo.

### Acciones

- Registrar un nuevo pago
- Dar de baja
- Reinscribir (en caso de que el alumno no este inscrito en ningun grupo en el ciclo escolar actual)

### Consultas involucradas

- Consultar datos del alumno
- Consultar tutores registrados
- Consultar pagos hechos
- Consultar cuotas pagadas
- Cambiar estado de inscripción de alumno

## Panel de pagos

### Objetivo

Permitir el registro de nuevos pagos de mensualidades, uniformes, papelería y eventos especiales.

### Notas

Este panel solo será accesible desde el panel de alumno. No se podrá acceder a él directamente.

### Acciones

- Registrar pago
- Imprimir recibo

### Consultas involucradas

- Consultar nombre, nivel educativo y grado del alumno.
- Consultar tutores del alumno.
- Consultar cuotas de mensualidad, uniforme, eventos especiales y papeleria por ciclo escolar, nivel educativo y grado del alumno.
- Colsultar uniformes por tipo.
- Registrar pago con sus cuotas en una misma transacción.

## Panel de registro

### Objetivo

Permitir el registro de nuevos alumnos con toda su información en la base de 
datos de la institución.

### Acciones

- Consultar niveles educativos y grupos
- Consultar cuotas de inscripción y mantenimiento
- Consultar cuota de mensualidad del mes en curso, papeleria y uniforme.
- Consultar tutor por medio de RFC
- Realizar en una misma transacción:
  - Registro de alumno
  - Registro de tutores
  - Registro de pago con sus cuotas
  - Registro de alumno en grupo
- Impresión de recibo de pago

## Panel de consulta de cuotas

### Objetivo

### Acciones

- Consultar cuotas utilizando un filtro de ciclo escolar, nivel educativo.

## Panel de consulta de grupos

## 

---

# Algoritmos

## Proceso de inscripción de un alumno

Todo esto formará parte de una misma transacción.

1. Inicio
2. Realizar prevalidación de la información de un alumno
3. Recolección de información, si hace falta
4. Asociación con tutores, si hace falta
5. Selección de nivel educativo, grado y grupo.
6. Realización de pago de inscripción
7. Registro de alumno en grupo
8. Impresión de comprobante de pago
9. Fin
