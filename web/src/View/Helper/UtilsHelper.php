<?php

namespace App\View\Helper;

use Cake\View\Helper;

class UtilsHelper extends Helper
{

    /**
     * @param $valor
     * @return string
     */
    public function moeda($valor)
    {
        return number_format($valor, 2, ',', '.');
    }

    /**
     * Aplica uma mascara no valor
     * Ex: ###.###.###-## para CPF
     * @param $formato
     * @param $valor
     * @return mixed
     */
    public function mascara($formato, $valor)
    {
        $search = [' ', '.', '+', '-', '/', '(', ')'];
        $replace = ['', '', '', '', '', '', ''];
        $valor = str_replace($search, $replace, $valor);
        $valor_count = strlen($valor);
        $formato_array = explode("#", $formato);

        $formato_alt = "";
        for ($a = 0; $a < $valor_count; $a++) {
            $formato_alt .= (string)$formato_array[$a];
            $formato_alt .= '#';
        }

        $mascarado = vsprintf(str_replace("#", "%s", $formato_alt), str_split($valor));
        if ($mascarado) {
            $output = $mascarado;
        } else {
            $output = $valor;
        }
        return $output;
    }
}