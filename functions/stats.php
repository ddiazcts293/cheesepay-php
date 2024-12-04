<?php

final class Statistics {
    private static $select = 
        'SELECT 
        (
            SELECT COUNT(alumno) 
            FROM vw_estado_alumnos
        ) AS student_count,
        (
            SELECT COUNT(alumno) 
            FROM vw_estado_alumnos 
            WHERE esta_inscrito = TRUE
        ) AS active_student_count,
        (
            SELECT COUNT(alumno) 
            FROM vw_estado_alumnos 
            WHERE esta_inscrito = TRUE
            AND esta_al_corriente = FALSE
        ) AS students_with_payments_due_count,
        (
            SELECT COUNT(*)
            FROM grupos
            WHERE ciclo = fn_obtener_ciclo_escolar_actual()
        ) AS group_count,
        (
            SELECT COUNT(*)
            FROM niveles_educativos
        ) as education_level_count,
        (
            SELECT COUNT(*)
            FROM eventos_especiales
            WHERE fecha_programada > CURDATE()
        ) AS special_event_count,
        (
            SELECT COUNT(*)
            FROM pagos
            WHERE WEEK(fecha) = WEEK(CURDATE())
            AND YEAR(fecha) = YEAR(CURDATE())
        ) AS current_week_payment_count,
        (
            SELECT COUNT(*)
            FROM pagos
            WHERE WEEK(fecha) = WEEK(CURDATE()) - 1
            AND YEAR(fecha) = YEAR(CURDATE())
        ) AS previous_week_payment_count';

    private int $student_count = 0;
    private int $active_student_count = 0;
    private int $students_with_payments_due_count = 0;
    private int $group_count = 0;
    private int $education_level_count = 0;
    private int $special_event_count = 0;
    private int $current_week_payment_count = 0;
    private int $previous_week_payment_count = 0;

    public function get_student_count() : int {
        return $this->student_count;
    }

    public function get_active_student_count() : int {
        return $this->active_student_count;
    }

    public function get_students_with_payments_due_count() : int {
        return $this->students_with_payments_due_count;
    }

    public function get_group_count() : int {
        return $this->group_count;
    }

    public function get_education_level_count() : int {
        return $this->education_level_count;
    }

    public function get_special_event_count() : int {
        return $this->special_event_count;
    }

    public function get_current_week_payment_count() : int {
        return $this->current_week_payment_count;
    }

    public function get_previous_week_payment_count() : int {
        return $this->previous_week_payment_count;
    }

    public function __construct(
        int $student_count,
        int $active_student_count,
        int $students_with_payments_due_count,
        int $group_count,
        int $education_level_count,
        int $special_event_count,
        int $current_week_payment_count,
        int $previous_week_payment_count,
    ) {
        $this->student_count = $student_count;
        $this->active_student_count = $active_student_count;
        $this->students_with_payments_due_count = $students_with_payments_due_count;
        $this->group_count = $group_count;
        $this->education_level_count = $education_level_count;
        $this->special_event_count = $special_event_count;
        $this->current_week_payment_count = $current_week_payment_count;
        $this->previous_week_payment_count = $previous_week_payment_count;
    }

    public static function get(MySqlConnection $conn = null) : self {
        if ($conn == null) {
            $conn = new MySqlConnection();
        }

        $row = $conn->query(self::$select)[0];
        return new self(
            $row['student_count'],
            $row['active_student_count'],
            $row['students_with_payments_due_count'],
            $row['group_count'],
            $row['education_level_count'],
            $row['special_event_count'],
            $row['current_week_payment_count'],
            $row['previous_week_payment_count']
        );
    }
}
