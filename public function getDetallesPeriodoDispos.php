                $nuevoCalculo = array();
                $nuevoCalculoAnterior = array();
                if (isset($amortizaciones[0])) {
                    $index = isset($amortizaciones[1]) ? 1 : 0;
                    $nuevoCalculo[1] = Amortizaciones::model()->getDisposicion($amortizaciones[$index]->id, $idSolicitud, $fecha2);
                    $d = new DateTime($fecha2);
                    $d->modify('last day of previous month');
                    $d = $d->format("Y-m-d H:i:s");
                    $nuevoCalculoAnterior[1] = Amortizaciones::model()->getDisposicion($amortizaciones[$index]->id, $idSolicitud, $d);
                }
            $nuevoCalculo=array();
            $nuevoCalculoAnterior=array();
                // obtiene amortizacion base
                $index = isset($amortizaciones[1]) ? 1 : 0;
                if (isset($amortizaciones[$index])) {
                    //trae el mes que escojio el usuario
                    $disposicion = Amortizaciones::model()->getDisposicion($amortizaciones[$index]->id, $idSolicitud, $fecha2);
                    $nuevoCalculo[$keyAll] = $disposicion;
                    //trae el mes anterior (antes de la fecha que el usuario escojio)
                    $d = new DateTime($fecha2);
                    $d->modify('last day of previous month');
                    $d = $d->format("Y-m-d H:i:s");
                    $disposicionAnterior = Amortizaciones::model()->getDisposicion($amortizaciones[$index]->id, $idSolicitud, $d);
                    $nuevoCalculoAnterior[$keyAll] = $disposicionAnterior;
                }
