<!DOCTYPE html>
<html lang="es">
    <?php
        // inicia una sesión
        session_start();
        $user = null;

        // verifica si el token de autentificación está fijado
        if (isset($_SESSION['token'])) {
            // valida el token para obtener el usuario asociado
            require_once __DIR__ . '/models/access/user.php';
            $user = User::validate_token($_SESSION['token']);
        }

        // verifica si no se localizó a un usuario con inicio de sesión
        if ($user === null) {
            session_destroy();
            header('Location: login.php');
        }
    ?>
	<head>
		<!--title-->
        <title>Panel de Control</title>
        <link rel="icon" type="image/png" href="favicon.png">
        <!--javascript-->
        <script src="js/fontawesome/solid.js"></script>
        <script src="js/control_panel.js"></script>
        <script src="js/common.js"></script>
        <!--stylesheets-->
        <link href="css/style.css" rel="stylesheet" />
        <link href="css/controls.css" rel="stylesheet" />
        <link href="css/alerts.css" rel="stylesheet" />
        <link href="css/dialogs.css" rel="stylesheet" />
        <link href="css/theme.css" rel="stylesheet" />
        <link href="css/fontawesome/fontawesome.css" rel="stylesheet" />
        <link href="css/fontawesome/solid.css" rel="stylesheet" />
		<!--metadata-->
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta charset="utf-8"/>
	</head>
	<body>
        <header>
            <div class="header-left">
                <div class="header-menu">
                    <i id="toggle-menu" class="fas fa-bars"></i>
                </div>
                <a class="header-logo" href="index.php">
                    <img src="images/logo.png">
                </a>
            </div>
            <div class="header-right">
                <div class="user-photo">
                    <img>
                </div>
                <div class="user-icons">
                    <a href="user_panel.php">
                        <i class="fas fa-cog"></i>
                    </a>
                    <a href="actions/sign_out.php">
                        <i class="fas fa-sign-out-alt" ></i>
                    </a>
                </div>
            </div>
        </header>
        <div id="menu">
            <a class="menu-item" href="index.php">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <label>Página principal</label>
                </div>
            </a>
            <a class="menu-item" href="registration_panel.php">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <label>Registrar alumno</label>
                </div>
            </a>
            <a class="menu-item" href="student_panel.php">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <label>Consultar alumno</label>
                </div>
            </a>
            <a class="menu-item" href="group_query_panel.php">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <label>Consultar grupos</label>
                </div>
            </a>
            <a class="menu-item" href="fee_query_panel.php">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-search-dollar"></i>
                    </div>
                    <label>Consultar cuotas</label>
                </div>
            </a>
            <a class="menu-item" href="control_panel.php" style="display: none;">
                <div class="menu-elements">
                    <div class="menu-icon">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <label>Panel de control</label>
                </div>
            </a>
        </div>
        <div id="content">
            <h1>Panel de control</h1>
            <form action="#">
                <div id="image-right">
                    <img src="images/fee-icon.png">
                </div>
                <div id="new-school-cycle" class="card">
                    <div class="card-header">
                        <h2>Nuevo ciclo escolar</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <div class="control control-col width-6">
                                <label>Fecha de inicio</label>
                                <input type="date" id="starting-date">
                            </div>
                            <div class="control control-col width-6">
                                <label>Fecha de fin</label>
                                <input type="date" id="ending-date">
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control width-4">
                                <button type="submit">Generar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="new-inscription-quote" class="card">
                    <div class="card-header">
                        <h2 class="headerStyle2">Nueva cuota de inscripción</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <table style="margin-left: 20px">
                                <tr>
                                    <th>Nivel</th>
                                    <th>Costo</th>
                                </tr>
                                <tr>
                                    <td>Preescolar</td>
                                    <td><input name="prees-price" type="text" ></td>
                                </tr>
                                <tr>
                                    <td>Primaria</td>
                                    <td><input name="prima-price" type="text" ></td>
                                </tr>
                                <tr>
                                    <td>Secundaria</td>
                                    <td><input name="secun-price" type="text" ></td>
                                </tr>
                            </table>
                        </div>
                        <div class="control-row">
                            <div class="control width-4">
                                <button type="submit">Generar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="new-maintenance-quote" class="card">
                    <div class="card-header"></div>
                    <div class="card-body"></div>
                    <h1 class="headerStyle2">Nueva cuota de mantenimiento</h1>
                    <div class="control-row">
                        <div class="control horizontal width-12">
                            <label class="width-3" style="width: calc(21% - 13px)">Ciclo escolar: </label>
                            <select id="school-cycle" name="maintenance-school-cycle" class="width-4">
                                <option value="" selected disabled hidden>Seleccione...</option>
                                <option value="2324C">2324C</option>
                                <option value="2425C">2425C</option>
                                <option value="2526C">2526C</option>
                            </select>
                        </div>
                        <div class="control horizontal width-12">
                            <label class="width-3">Concepto: </label>
                            <input name="maintenance-concept" type="text" class="width-12">
                        </div>
                    </div>
                    <div class="control-row">
                        <div class="control width-4">
                            <button type="submit" style="margin-left: 1px">Generar</button>
                        </div>
                    </div>
                </div>
                <div id="new-monthly-payment" class="card">
                    <div class="card-header">
                        <h1 class="headerStyle2">Nueva mensualidad</h1>
                    </div>
                    <div class="card-body">
    
                        <div class="control-row">
                            <div class="control horizontal width-6">
                                <label class="width-4" style="margin-right: 0px">Ciclo escolar: </label>
                                <select id="school-cycle" name="monthly-payment-cycle" class="width-4">
                                    <option value="" selected disabled hidden>Seleccione...</option>
                                    <option value="2324C">2324C</option>
                                    <option value="2425C">2425C</option>
                                    <option value="2526C">2526C</option>
                                </select>
                            </div>
                            <div class="control horizontal width-6">
                                <label class="width-4">Nivel educativo: </label>
                                <select id="school-level" name="monthly-payment-level" class="width-4">
                                    <option value="" selected disabled hidden>Seleccione...</option>
                                    <option value="prees">Preescolar</option>
                                    <option value="prima">Primaria</option>
                                    <option value="secun">Secundaria</option>
                                </select>
                            </div>
                        </div>
                    </div>
                        <div class="control-row">
                            <div class="control horizontal width-6">
                                <label class="width-4" style="margin-right: 0px">Precio: </label>
                                <input type="text" name="monthly-payment-price">
                            </div>
                            <div class="control horizontal width-6">
                                <label class="width-4">Día de vencimiento: </label>
                                <input type="text" name="monthly-payment-exp-day">
                            </div>
                        </div>
                        <table style="margin-left: 20px">
                            <tr>
                                <th>Mes</th>
                                <th>Vacacional</th>
                            </tr>
                            <tr>
                                <td>Enero</td>
                                <td><input name="january" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Febrero</td>
                                <td><input name="february" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Marzo</td>
                                <td><input name="march" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Abril</td>
                                <td><input name="april" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Mayo</td>
                                <td><input name="may" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Junio</td>
                                <td><input name="june" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Julio</td>
                                <td><input name="july" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Agosto</td>
                                <td><input name="august" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Septiembre</td>
                                <td><input name="september" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Octubre</td>
                                <td><input name="october" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Noviembre</td>
                                <td><input name="november" type="checkbox" ></td>
                            </tr>
                            <tr>
                                <td>Diciembre</td>
                                <td><input name="december" type="checkbox" ></td>
                            </tr>
                        </table>
                        <div class="control-row">
                            <div class="control width-4">
                                <button type="submit" style="margin-left: 1px">Generar</button>
                            </div>
                        </div>
                </div>
                <div id="new-stationer-quote" class="card">
                    <h1 class="headerStyle2">Nueva cuota de papelería</h1>
                    <div class="control-row" style="margin-bottom: 25px">
                        <div class="control horizontal width-6">
                            <label class="width-4" style="margin-right: 0px">Ciclo escolar: </label>
                            <select id="school-cycle" name="stationer-cycle" class="width-4">
                                <option value="" selected disabled hidden>Seleccione...</option>
                                <option value="2324C">2324C</option>
                                <option value="2425C">2425C</option>
                                <option value="2526C">2526C</option>
                            </select>
                        </div>
                        <div class="control horizontal width-6">
                            <label class="width-4">Nivel educativo: </label>
                            <select id="school-level" name="stationer-school-level" class="width-4">
                                <option value="" selected disabled hidden>Seleccione...</option>
                                <option value="prees">Preescolar</option>
                                <option value="prima">Primaria</option>
                                <option value="secun">Secundaria</option>
                            </select>
                        </div>
                    </div>
                    <table>
                        <tr>
                            <th>Grado</th>
                            <th>Concepto</th>
                            <th>Precio</th>
                        </tr>
                        <tr>
                            <td>A</td>
                            <td><input name="stationer-concept1" type="text" ></td>
                            <td><input name="stationer-price1" type="text" ></td>
                        </tr>
                        <tr>
                            <td>B</td>
                            <td><input name="stationer-concept2" type="text" ></td>
                            <td><input name="stationer-price2" type="text" ></td>
                        </tr>
                        <tr>
                            <td>C</td>
                            <td><input name="stationer-concept3" type="text" ></td>
                            <td><input name="stationer-price3" type="text" ></td>
                        </tr>
                        <tr>
                            <td>D</td>
                            <td><input name="stationer-concept4" type="text" ></td>
                            <td><input name="stationer-price4" type="text" ></td>
                        </tr>
                    </table>
                    <div class="control-row">
                        <div class="control width-4">
                            <button type="submit" style="margin-left: 1px">Generar</button>
                        </div>
                    </div>    
                </div>
                <div id="new-uniform-quote" class="card">
                    <h1 class="headerStyle2">Nueva cuota de uniforme</h1>
                    <div class="control-row" style="margin-bottom: 25px">
                        <div class="control horizontal width-4">
                            <label class="width-4" style="margin-right: 0px">Ciclo escolar: </label>
                            <select id="school-cycle" name="uniform-cycle" class="width-4">
                                <option value="" selected disabled hidden>Seleccione...</option>
                                <option value="2324C">2324C</option>
                                <option value="2425C">2425C</option>
                                <option value="2526C">2526C</option>
                            </select>
                        </div>
                        <div class="control horizontal width-4">
                            <label class="width-5">Nivel educativo: </label>
                            <select id="school-level" name="uniform-school-level" class="width-4">
                                <option value="" selected disabled hidden>Seleccione...</option>
                                <option value="prees">Preescolar</option>
                                <option value="prima">Primaria</option>
                                <option value="secun">Secundaria</option>
                            </select>
                        </div>
                        <div class="control horizontal width-4">
                            <label class="width-5">Tipo de uniforme: </label>
                            <select id="uniform-type" name="uniform-type" class="width-4">
                                <option value="" selected disabled hidden>Seleccione...</option>
                                <option value="1">Normal</option>
                                <option value="2">Deportivo</option>
                            </select>
                        </div>
                        <div class="control horizontal width-12">
                            <label class="width-1" style="margin-right: 5px">Concepto: </label>
                            <input name="uniform-concept" type="text" class="width-12">
                        </div>
                        <div class="control horizontal width-6">
                            <label class="width-2" style="margin-right: 0px">Talla: </label>
                            <input name="uniform-size" type="text" class="width-4">
                        </div>
                        <div class="control horizontal width-6">
                            <label class="width-2">Costo: </label>
                            <input name="uniform-price" type="text" class="width-4">
                        </div>
                        <div class="control width-4">
                            <button type="add-to-table" style="margin-left: 1px">Agregar a la tabla</button>
                        </div>
                    </div>
                    <table>
                        <tr>
                            <th>Concepto</th>
                            <th>Talla</th>
                            <th>Precio</th>
                            <th>Tipo</th>
                            <th>Nivel escolar</th>
                        </tr>
                        <tr>
                            <td>Paq. uniforme ordinario niña CH secundaria-2022</td>
                            <td>CH</td>
                            <td>$1000</td>
                            <td>Normal</td>
                            <td>Secundaria</td>
                        </tr>
                        <tr>
                            <td>Paq. uniforme ordinario niño CH secundaria-2022</td>
                            <td>CH</td>
                            <td>$1000</td>
                            <td>Normal</td>
                            <td>Secundaria</td>
                        </tr>
                        <tr>
                            <td>Paq. uniforme deportivo unisex CH secundaria-2022</td>
                            <td>CH</td>
                            <td>1300</td>
                            <td>Deportivo</td>
                            <td>Secundaria</td>
                        </tr>
                        <tr>
                            <td>Paq. uniforme ordinario niña M secundaria-2022</td>
                            <td>M</td>
                            <td>1000</td>
                            <td>Normal</td>
                            <td>Secundaria</td>
                        </tr>
                    </table>
                    <div class="control-row">
                        <div class="control width-4">
                            <button type="submit" style="margin-left: 1px">Registrar</button>
                        </div>
                    </div>
                    </div>
            </form>
            <form id="new-special-event-form" action="#">
                <div class="card">
                    <div class="card-header">
                        <h2 class="headerStyle2">Nuevo evento especial</h2>
                    </div>
                    <div class="card-body">
                        <div class="control-row">
                            <div class="control control-col width-6">
                                <label>Fecha programada</label>
                                <input type="date" name="scheduled_date">
                            </div>
                            <div class="control control-col width-6">
                                <label>Concepto</label>
                                <input type="text" name="concept">
                            </div>
                            <div class="control control-col width-6">
                                <label>Costo</label>
                                <input type="number" name="cost">
                            </div>
                        </div>
                        <div class="control-row">
                            <div class="control control-col width-4">
                                <button type="submit">Registrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>
