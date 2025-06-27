                    if($key=="interes_pagado_capitalizable" || $key=="interes_pagado_cap") {
                    if($key=="interes_pagado_capitalizable" || $key=="interes_pagado_cap") {
                                                if($verificar_cap->id_instrumento_monetario0->descripcion=="CAPITALIZACION"){
                                                        $saldoLinea['interes_pagado_capitalizable'] += $interesPagado;
                                                        $interesPagado = 0;
                                                        $interesPagadoAnterior = 0;
                                                }
                                                $verificar_cap= Operaciones::model()->find("referencia_operacion='".$amortizacion->referencia_operacion."'");
                                                if($verificar_cap->id_instrumento_monetario0->descripcion=="CAPITALIZACION"){
                                                        $saldoLinea['interes_pagado_capitalizable'] += $amortizacion->pago_interes-$amortizacion->iva_interes_generado;
                                                        $saldoLinea['interes_pagado']+= 0;
                                                        $saldoLinea['iva_interes_pagado']+= 0;
                                                }else{
