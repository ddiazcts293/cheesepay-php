<!DOCTYPE html>
<html lang="es">
    <?php 
        require __DIR__ . '/../models/payment.php';
        $payment = null;
        $payment_fees = [];
        $student = null;
        $tutor = null;
        $current_group = null;

        // verifica si el método de la solicitud es GET y si se recibió el folio
        // de un pago
        if ($_SERVER['REQUEST_METHOD'] == 'GET' AND isset($_GET['payment_id'])) {
            // limpia el parámetro recibido
            $payment_id = sanitize($_GET['payment_id']);
            
            try {
                // crea una nueva conexión
                $conn = new MySqlConnection();
                // realiza la consulta del pago
                $payment = Payment::get($payment_id, $conn);
                if ($payment !== null ) {
                    $payment_fees = $payment->get_fees($conn);
                    $student = $payment->get_student();
                    $tutor = $payment->get_tutor();
                    $current_group = $student->get_current_group();
                }
            } catch (mysqli_sql_exception $ex) {
                die('Error al intentar recuperar los datos del pago.');
            }
        } else {
            die('Invalid request');
        }
    ?>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            <?php if ($payment !== null) { ?>
                Pago #<?php echo $payment->get_payment_id(); ?>
            <?php } else { ?>
                Generador de facturas
            <?php } ?>
        </title>
        <link href="../css/alerts.css" rel="stylesheet" />
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
            }
            .factura {
                width: 800px;
                height: 100%;
                margin: 0 auto;
                border: 1px solid #000;
                padding: 20px;
            }
            .header {
                text-align: center;
            }
            .detalle {
                margin-top: 20px;
            }
            table {
                width: 100%;
                height: 100%;
                border-collapse: collapse;
            }
            th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        </style>
    </head>
    <body>
        <?php if ($payment !== null) { ?>
            <script>
                window.addEventListener('load', function () {
                    window.print();
                });
            </script>
            <div class="factura">
                <div class="header">
                    <h1>Nombre del Colegio</h1>
                    <p>Dirección del Colegio</p>
                    <p>RFC del Colegio</p>
                </div>
                <div class="detalle">
                    <table>
                        <tbody>
                            <tr>
                                <th>Folio</th>
                                <td><?php echo $payment->get_payment_id(); ?></td>
                                <th>Fecha</th>
                                <td><?php echo $payment->get_date(); ?></td>
                            </tr>
                            <tr>
                                <th>Matrícula</th>
                                <td><?php echo $student->get_student_id(); ?></td>
                                <th>Tutor</th>
                                <td><?php echo $tutor->get_full_name(); ?></td>
                            </tr>
                            <tr>
                                <th>Alumno</th>
                                <td><?php echo $student->get_full_name(); ?></td>
                                <th>Grupo</th>
                                <td>
                                    <?php if (isset($current_group)) { 
                                        echo $current_group;
                                    } else { ?>
                                        Ninguno
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php if ($current_group !== null) { ?>
                                <tr>
                                    <th>Nivel educativo</th>
                                    <td><?php echo $current_group->get_education_level()->get_description(); ?></td>
                                    <th>Ciclo escolar</th>
                                    <td><?php echo $current_group->get_school_year(); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <br>
                    <br>
                    <table>
                        <thead>
                            <tr>
                                <th>Id. de cuota</th>
                                <th>Concepto</th>
                                <th>Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_fees as $fee) { ?>
                                <tr>
                                    <td><?php echo $fee->get_number(); ?></td>
                                    <td><?php echo $fee->get_concept(); ?></td>
                                    <td><?php echo format_as_currency($fee->get_cost()); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <p>
                        <strong>Total: </strong>
                        <?php 
                            // calcula el total inicial
                            $total = 0.0;
                            // recorre todas las cuotas agregadas para sumar los costos
                            foreach ($payment_fees as $fee) {
                                $total += $fee->get_cost();
                            }
                            
                            echo format_as_currency($total);
                        ?>
                    </p>
                </div>
            </div>
        <?php } else { ?>
            <div class="alert alert-danger">
                <span><strong>Error:</strong> No se pudo localizar el pago especificado.</span>
            </div>
        <?php } ?>
    </body>
</html>
