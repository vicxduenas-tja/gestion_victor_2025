<?php

class Clima extends Controller
{
    public function __construct()
    {
        parent::__construct();
        session_start();
    }

    /**
     * Obtiene el clima actual de Tarija, Bolivia
     * Retorna datos en formato JSON
     */
    public function obtenerClima()
    {
        // Coordenadas de Tarija, Bolivia
        $lat = -21.5355;
        $lon = -64.7295;

        $data = $this->model->getClima($lat, $lon);

        if ($data && isset($data['main'])) {
            $res = array(
                'tipo' => 'success',
                'data' => array(
                    'temperatura' => round($data['main']['temp']),
                    'sensacion' => round($data['main']['feels_like']),
                    'temp_min' => round($data['main']['temp_min']),
                    'temp_max' => round($data['main']['temp_max']),
                    'humedad' => $data['main']['humidity'],
                    'presion' => $data['main']['pressure'],
                    'descripcion' => $data['weather'][0]['description'],
                    'icono' => $data['weather'][0]['icon'],
                    'viento' => round($data['wind']['speed'] * 3.6, 1), // Convertir m/s a km/h
                    'ciudad' => $data['name'],
                    'pais' => $data['sys']['country']
                )
            );
        } else {
            $res = array(
                'tipo' => 'error',
                'mensaje' => 'No se pudo obtener la información del clima. Por favor, verifica tu API key.'
            );
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    /**
     * Vista del widget de clima (si se necesita una vista separada)
     */
    public function index()
    {
        if (empty($_SESSION['id'])) {
            header('Location: ' . BASE_URL);
        }
        $data['title'] = 'Clima Tarija';
        $this->views->getView('clima', 'index', $data);
    }
}
?>
