<?php
class FeriadosModel extends Query
{
    // API de Nager.Date - Totalmente gratuita, sin necesidad de API key
    private $apiUrl = 'https://date.nager.at/api/v3';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene los días feriados de Bolivia para un año específico
     * @param int $year Año
     * @return array|false Datos de los feriados o false en caso de error
     */
    public function getFeriados($year)
    {
        $url = $this->apiUrl . '/PublicHolidays/' . $year . '/BO';

        // Realizar petición con cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'User-Agent: Mozilla/5.0'
        ]);

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
     * Verifica si una fecha específica es un día feriado
     * @param string $date Fecha en formato YYYY-MM-DD
     * @return array|false Información del feriado o false si no es feriado
     */
    public function esFeriado($date)
    {
        $year = date('Y', strtotime($date));
        $feriados = $this->getFeriados($year);

        if ($feriados) {
            foreach ($feriados as $feriado) {
                if ($feriado['date'] == $date) {
                    return $feriado;
                }
            }
        }

        return false;
    }

    /**
     * Obtiene el próximo día feriado
     * @return array|false Información del próximo feriado o false
     */
    public function getProximoFeriado()
    {
        $year = date('Y');
        $hoy = date('Y-m-d');
        $feriados = $this->getFeriados($year);

        if ($feriados) {
            foreach ($feriados as $feriado) {
                if ($feriado['date'] >= $hoy) {
                    return $feriado;
                }
            }

            // Si no hay más feriados este año, buscar en el próximo año
            $feriadosProximoAnio = $this->getFeriados($year + 1);
            if ($feriadosProximoAnio && count($feriadosProximoAnio) > 0) {
                return $feriadosProximoAnio[0];
            }
        }

        return false;
    }

    /**
     * Obtiene los países disponibles en la API
     * @return array|false Lista de países o false en caso de error
     */
    public function getPaisesDisponibles()
    {
        $url = $this->apiUrl . '/AvailableCountries';

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
