<?php
class ClimaModel extends Query
{
    // API Key de OpenWeatherMap - NOTA: El usuario debe registrarse en https://openweathermap.org/api
    // y obtener su propia API key gratuita (permite 1000 llamadas/día)
    private $apiKey = 'TU_API_KEY_AQUI'; // Cambiar por tu API key
    private $apiUrl = 'https://api.openweathermap.org/data/2.5/weather';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene el clima actual de una ubicación mediante latitud y longitud
     * @param float $lat Latitud
     * @param float $lon Longitud
     * @return array|false Datos del clima o false en caso de error
     */
    public function getClima($lat, $lon)
    {
        // Construir URL con parámetros
        $url = $this->apiUrl . '?' . http_build_query([
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $this->apiKey,
            'units' => 'metric', // Celsius
            'lang' => 'es' // Español
        ]);

        // Realizar petición con cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Verificar respuesta
        if ($httpCode == 200 && $response) {
            return json_decode($response, true);
        }

        return false;
    }

    /**
     * Obtiene pronóstico del clima para 5 días
     * @param float $lat Latitud
     * @param float $lon Longitud
     * @return array|false Datos del pronóstico o false en caso de error
     */
    public function getPronostico($lat, $lon)
    {
        $url = 'https://api.openweathermap.org/data/2.5/forecast?' . http_build_query([
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $this->apiKey,
            'units' => 'metric',
            'lang' => 'es'
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && $response) {
            return json_decode($response, true);
        }

        return false;
    }
}
?>
