<?php
/**
 * Calcula el interés capitalizado para un crédito.
 *
 * @param float $principal Monto inicial sobre el que se capitaliza.
 * @param float $rate Tasa de interés anual expresada en porcentaje.
 * @param int   $periods Número de periodos totales a capitalizar.
 * @param int   $frequency Frecuencia de capitalización por año. Por defecto 1.
 *
 * @return float Importe del interés capitalizado.
 */
function calcularCapitalizacion($principal, $rate, $periods, $frequency = 1)
{
    $factor = pow(1 + ($rate / 100) / $frequency, $periods * $frequency);
    return $principal * ($factor - 1);
}
?>
