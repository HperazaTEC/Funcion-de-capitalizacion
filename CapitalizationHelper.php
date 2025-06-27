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

/**
 * Procesa la capitalizaci\xC3\xB3n de un cr\xC3\xA9dito con pagos opcionales.
 * Cada periodo se capitaliza el inter\xC3\xA9s generado y luego se descuenta
 * el pago de capital correspondiente.
 *
 * @param float $principal  Monto inicial del cr\xC3\xA9dito.
 * @param float $rate       Tasa de inter\xC3\xA9s anual en porcentaje.
 * @param int   $periods    Cantidad de periodos a calcular.
 * @param int   $frequency  Frecuencia de capitalizaci\xC3\xB3n por a\xC3\xB1o.
 * @param array $payments   Pagos de capital por periodo (indice base 1).
 *
 * @return array Detalle de cada periodo con saldo e inter\xC3\xA9s.
 */
function capitalizarCredito(
    $principal,
    $rate,
    $periods,
    $frequency = 1,
    array $payments = []
) {
    $saldo = $principal;
    $detalle = [];
    for ($i = 1; $i <= $periods; $i++) {
        $interes = $saldo * (($rate / 100) / $frequency);
        $saldo += $interes; // se capitaliza el interes
        $pago = isset($payments[$i]) ? $payments[$i] : 0;
        $saldo -= $pago;
        $detalle[] = [
            'periodo'  => $i,
            'interes'  => round($interes, 2),
            'pago'     => round($pago, 2),
            'saldo'    => round($saldo, 2),
        ];
    }
    return $detalle;
}
?>
