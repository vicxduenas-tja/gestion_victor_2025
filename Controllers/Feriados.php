<?php

class Feriados extends Controller
{
    public function __construct()
    {
        parent::__construct();
        session_start();
    }

    /**
     * Obtiene los días feriados de Bolivia para el año actual
     * Retorna datos en formato JSON
     */
    public function obtenerFeriados()
    {
        $year = date('Y'); // Año actual
        $data = $this->model->getFeriados($year);

        if ($data && is_array($data)) {
            // Filtrar solo los feriados próximos (actuales y futuros)
            $hoy = date('Y-m-d');
            $feriadosProximos = array_filter($data, function($feriado) use ($hoy) {
                return $feriado['date'] >= $hoy;
            });

            // Limitar a los próximos 5 feriados
            $feriadosProximos = array_slice($feriadosProximos, 0, 5);

            $res = array(
                'tipo' => 'success',
                'data' => array_map(function($feriado) {
                    return array(
                        'fecha' => $feriado['date'],
                        'nombre' => $feriado['localName'],
                        'nombre_en' => $feriado['name'],
                        'es_fijo' => $feriado['fixed'],
                        'es_global' => $feriado['global'],
                        'tipo' => $feriado['types'],
                        'dias_restantes' => $this->diasRestantes($feriado['date'])
                    );
                }, $feriadosProximos),
                'total' => count($data)
            );
        } else {
            $res = array(
                'tipo' => 'error',
                'mensaje' => 'No se pudo obtener la información de días feriados.'
            );
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    /**
     * Obtiene todos los feriados del año
     */
    public function listarTodos()
    {
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        $data = $this->model->getFeriados($year);

        if ($data && is_array($data)) {
            $res = array(
                'tipo' => 'success',
                'data' => $data,
                'year' => $year
            );
        } else {
            $res = array(
                'tipo' => 'error',
                'mensaje' => 'No se pudo obtener la información de días feriados.'
            );
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    /**
     * Calcula los días restantes hasta una fecha
     */
    private function diasRestantes($fecha)
    {
        $hoy = new DateTime();
        $fechaFeriado = new DateTime($fecha);
        $diferencia = $hoy->diff($fechaFeriado);

        return $diferencia->days;
    }

    /**
     * Vista del widget de feriados (si se necesita una vista separada)
     */
    public function index()
    {
        if (empty($_SESSION['id'])) {
            header('Location: ' . BASE_URL);
        }
        $data['title'] = 'Días Feriados Bolivia';
        $this->views->getView('feriados', 'index', $data);
    }
}
?>
