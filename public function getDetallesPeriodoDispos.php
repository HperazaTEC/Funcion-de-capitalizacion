<?php

    /**
     * Calcula las estructuras de capitalización a partir de los arreglos
     * recibidos. Devuelve los saldos generados para el periodo actual y el
     * periodo inmediato anterior.
     */
    private function calcularCapitalizacion(array $nuevoCalculoAnterior, array $nuevoCalculo)
    {
        $saldoLineaCalculo = array(
            'saldoLineaCorte'    => 0,
            'saldoInsoluto'      => 0,
            'interesesDevengados'=> 0,
            'iva'                => 0,
            'interesOrdinario'   => 0,
            'interesMoratorios'  => 0,
            'ivaInteres'         => 0,
            'ivaMoratorio'       => 0,
            'capitalizado'       => 0,
            'interes_pagado'     => 0,
            'interes_pagado_capitalizable' => 0,
        );

        $saldoLineaCalculoAnterior = array(
            'saldoLineaCorte'    => 0,
            'saldoInsoluto'      => 0,
            'interesesDevengados'=> 0,
            'iva'                => 0,
            'interesOrdinario'   => 0,
            'interesMoratorios'  => 0,
            'ivaInteres'         => 0,
            'ivaMoratorio'       => 0,
            'capitalizado'       => 0,
            'interes_pagado'     => 0,
            'interes_pagado_capitalizable' => 0,
        );

        foreach ($nuevoCalculoAnterior as $item) {
            foreach ($item as $subItem) {
                $pagoMoratorio  = 0;
                $ivaMoratorio   = 0;
                $interesGenerado= 0;
                $ivaInteres     = 0;

                foreach ($subItem as $key => $value) {
                    if($key=="pago_total") {
                        $saldoLineaCalculoAnterior['saldoLineaCorte'] += $value;
                    }
                    if($key=="saldo_capital") {
                        $saldoLineaCalculoAnterior['saldoInsoluto'] += $value;
                    }
                    if($key=="pago_moratorios") {
                        $pagoMoratorio = $value;
                    }
                    if($key=="iva_moratorios") {
                        $ivaMoratorio = $value;
                    }
                    if($key=="interes_generado") {
                        $interesGenerado = $value;
                    }
                    if($key=="iva_moratorios") {
                        $saldoLineaCalculoAnterior['iva'] += $value;
                    }
                    if($key=="iva_interes_generado") {
                        $saldoLineaCalculoAnterior['iva'] += $value;
                        $ivaInteres = $value;
                    }
                    if($key=="capitalizado") {
                        $saldoLineaCalculoAnterior['capitalizado'] += $value;
                    }
                    if($key=="interes_pagado") {
                        $saldoLineaCalculoAnterior['interes_pagado'] += $value;
                    }
                    if($key=="interes_pagado_capitalizable") {
                        $saldoLineaCalculoAnterior['interes_pagado_capitalizable'] += $value;
                    }
                }

                $resta = $pagoMoratorio - $ivaMoratorio;

                $saldoLineaCalculoAnterior['interesesDevengados'] += $resta + $interesGenerado;
                $saldoLineaCalculoAnterior['interesOrdinario']    += $interesGenerado;
                $saldoLineaCalculoAnterior['interesMoratorios']    += $resta;
                $saldoLineaCalculoAnterior['ivaMoratorio']         += $ivaMoratorio;
                $saldoLineaCalculoAnterior['ivaInteres']           += $ivaInteres;
            }
        }

        foreach ($nuevoCalculo as $item) {
            foreach ($item as $subItem) {
                $pagoMoratorio  = 0;
                $ivaMoratorio   = 0;
                $interesGenerado= 0;
                $ivaInteres     = 0;

                foreach ($subItem as $key => $value) {
                    if($key=="pago_total") {
                        $saldoLineaCalculo['saldoLineaCorte'] += $value;
                    }
                    if($key=="saldo_capital") {
                        $saldoLineaCalculo['saldoInsoluto'] += $value;
                    }
                    if($key=="pago_moratorios") {
                        $pagoMoratorio = $value;
                    }
                    if($key=="iva_moratorios") {
                        $ivaMoratorio = $value;
                    }
                    if($key=="interes_generado") {
                        $interesGenerado = $value;
                    }
                    if($key=="iva_moratorios") {
                        $saldoLineaCalculo['iva'] += $value;
                    }
                    if($key=="iva_interes_generado") {
                        $saldoLineaCalculo['iva'] += $value;
                        $ivaInteres = $value;
                    }
                    if($key=="capitalizado") {
                        $saldoLineaCalculo['capitalizado'] += $value;
                    }
                    if($key=="interes_pagado") {
                        $saldoLineaCalculo['interes_pagado'] += $value;
                    }
                    if($key=="interes_pagado_capitalizable") {
                        $saldoLineaCalculo['interes_pagado_capitalizable'] += $value;
                    }
                }

                $resta = $pagoMoratorio - $ivaMoratorio;

                $saldoLineaCalculo['interesesDevengados'] += $resta + $interesGenerado;
                $saldoLineaCalculo['interesOrdinario']    += $interesGenerado;
                $saldoLineaCalculo['interesMoratorios']    += $resta;
                $saldoLineaCalculo['ivaMoratorio']         += $ivaMoratorio;
                $saldoLineaCalculo['ivaInteres']           += $ivaInteres;
            }
        }

        return array(
            'saldoLineaCalculo'         => $saldoLineaCalculo,
            'saldoLineaCalculoAnterior' => $saldoLineaCalculoAnterior,
        );
    }

    public function getDetallesPeriodoDisposicion($idSolicitud,$fecha1,$fecha2,$cliente=null, $incluirDisposicionesPagadas = 0)
	 {
        $model=$this;
        $data=array();

        $cobroInteres=$model->id_producto0->cobro_interes;

        $empresa=Empresa::model()->find();
        $decimales=2;
        if(isset($empresa->decimales)){
            $decimales=$empresa->decimales;
        }
        if(is_null($model->tabla_disposiciones)){
            $model->tabla_disposiciones=$model->id_producto0->tabla_disposiciones;
        }

        if(isset($model->id_grupo_cliente) && $model->id_grupo_cliente!=0){
            //Grupal
            if(!is_null($cliente)){
                $sqlComisiones="
					SELECT
						*
					FROM
						comisiones_amortizaciones c LEFT JOIN
						amortizaciones a ON(c.id_amortizacion=a.id) LEFT JOIN
						grupos_solidarios g ON(a.id_grupo_solidario=g.id)
					WHERE
						g.id_solicitud={$model->id} AND
						c.financiado<>1
				";
                $dataComisiones=Yii::app()->db->createCommand($sqlComisiones)->queryAll();
                $infoComisiones=array();
                foreach ($dataComisiones as $comision) {
                    $infoComisiones[$comision["id_amortizacion"]][]=array(
                        'comision'=>$comision["nombre"],
                        'monto'=>$comision["monto_comision"]-$comision["monto_impuesto"],
                        'iva'=>$comision["monto_impuesto"],
                    );
                }
                $amortizaciones=Amortizaciones::model()->with("id_grupo_solidario0")->findAll("id_grupo_solidario0.id_solicitud=:solicitud AND id_grupo_solidario0.id_cliente=:cliente",array(':solicitud'=>$model->id,':cliente'=>$cliente));

                $amortizacionesAll[0]=$amortizaciones;
            }
        }else{
            //Principal--------------------------------------
            $sqlComisiones="
				SELECT
				    c.id as id_comision,
					c.*,
				    a.*
				FROM
					comisiones_amortizaciones c LEFT JOIN
					amortizaciones a ON(c.id_amortizacion=a.id)
				WHERE
					a.id_solicitud={$model->id} AND
					c.financiado<>1
			";
            $dataComisiones=Yii::app()->db->createCommand($sqlComisiones)->queryAll();
            $infoComisiones=array();

            foreach ($dataComisiones as $comision) {
                $infoComisiones[$comision["id_amortizacion"]][]=array(
                    'id'=>$comision["id_comision"],
                    'descontar'=>$comision["descontar_dispocision"],
                    'comision'=>$comision["nombre"],
                    'monto'=>$comision["monto_comision"]-$comision["monto_impuesto"],
                    'iva'=>$comision["monto_impuesto"],
                );
            }
            $amortizaciones=Amortizaciones::model()->findAll("id_solicitud=:solicitud",array(':solicitud'=>$model->id));
            $disposiciones=Disposiciones::model()->findAll("id_solicitud=:solicitud",array(':solicitud'=>$model->id));

			//aqui todavia no teraer las amortaziones
            $amortizacionesAll[0]=$amortizaciones;
            $nuevoCalculo=null;
            $nuevoCalculoAnterior=null;

            //Disposiciones-----------------------------------
			$solicitud=Solicitudes::model()->findByPK($idSolicitud);
            if($solicitud->id_producto0->tabla_disposiciones=="unica" || $solicitud->intereses_visibles==1){
                $sqlComisiones="
                    SELECT
                        *
                    FROM
                        comisiones_amortizaciones c LEFT JOIN
                        amortizaciones a ON(c.id_amortizacion=a.id) LEFT JOIN
                        disposiciones d ON(a.id_disposicion=d.id)
                    WHERE
                        d.id_solicitud={$model->id} AND
                        c.financiado<>1
                ";
                $dataComisiones=Yii::app()->db->createCommand($sqlComisiones)->queryAll();
                foreach ($dataComisiones as $comision) {
                    $infoComisiones[$comision["id_amortizacion"]][]=array(
                        'id'=>$comision["id_comision"],
                        'descontar'=>$comision["descontar_dispocision"],
                        'comision'=>$comision["nombre"],
                        'monto'=>$comision["monto_comision"]-$comision["monto_impuesto"],
                        'iva'=>$comision["monto_impuesto"],
                    );
                }
            }

            $keyAll=1;
            foreach ($disposiciones as $disposicion) {
				Yii::app()->session['estadoCuentaDispo']="EstadoCuenta";

				if (
					mb_strtolower($disposicion->estatus) == "pagado" 
					&& $incluirDisposicionesPagadas == 0
				) {
					continue;
				}

                //trae las id de amortizaciones
                $amortizaciones=Amortizaciones::model()->findAll("id_disposicion=:disposicion",array(':disposicion'=>$disposicion->id));
                $amortizacionesAll[$keyAll]=$amortizaciones;

                //trae el mes que escojio el usuario
                $disposicion= Amortizaciones::model()->getDisposicion($amortizaciones[1]->id,$idSolicitud,$fecha2);
                $nuevoCalculo[$keyAll]=$disposicion;

                //trae el mes anterior (antes de la fecha que el usuario escojio)
                $d = new DateTime( $fecha2);
                $d->modify( 'last day of previous month' );
                $d= $d->format("Y-m-d H:i:s");

                $disposicionAnterior= Amortizaciones::model()->getDisposicion($amortizaciones[1]->id,$idSolicitud,$d);
                $nuevoCalculoAnterior[$keyAll]=$disposicionAnterior;

                $keyAll++;
            }

        }
        $saldoLineaCalculo=array(
            'saldoLineaCorte'=>0,
            'saldoInsoluto'=>0,
            'interesesDevengados'=>0,
            'iva'=>0,
            'interesOrdinario'=>0,
            'interesMoratorios'=>0,
            'ivaInteres'=>0,
            'ivaMoratorio'=>0,
        );

        $saldoLineaCalculoAnterior=array(
            'saldoLineaCorte'=>0,
            'saldoInsoluto'=>0,
            'interesesDevengados'=>0,
            'iva'=>0,
            'interesOrdinario'=>0,
            'interesMoratorios'=>0,
            'ivaInteres'=>0,
            'ivaMoratorio'=>0,
        );

        foreach ($nuevoCalculoAnterior as $item) {
            foreach ($item as $subItem) {
                $pagoMoratorio=0;
                $ivaMoratorio=0;
                $interesGenerado=0;
                $ivaInteres=0;

                foreach ($subItem as $key=>$value) {
                    if($key=="pago_total")
                    {

                        $saldoLineaCalculoAnterior['saldoLineaCorte']+=$value;

                    }

                    if($key=="saldo_capital")
                    {

                        $saldoLineaCalculoAnterior['saldoInsoluto']+=$value;

                    }

                    if($key=="pago_moratorios"){
                        $pagoMoratorio=$value;
                    }

                    if($key=="iva_moratorios"){
                        $ivaMoratorio=$value;
                    }

                    if($key=="interes_generado"){
                        $interesGenerado=$value;
                    }

                    if($key=="iva_moratorios"){
                        $saldoLineaCalculoAnterior['iva']+=$value;
                    }

                    if($key=="iva_interes_generado"){
                        $saldoLineaCalculoAnterior['iva']+=$value;
                        $ivaInteres=$value;
                    }
                }


                $resta=$pagoMoratorio-$ivaMoratorio;


                $saldoLineaCalculoAnterior['interesesDevengados']+=$resta+$interesGenerado;

                $saldoLineaCalculoAnterior['interesOrdinario']+=$interesGenerado;
                $saldoLineaCalculoAnterior['interesMoratorios']+=$resta;


                $saldoLineaCalculoAnterior['ivaMoratorio']+=$ivaMoratorio;
                $saldoLineaCalculoAnterior['ivaInteres']+=$ivaInteres;

            }
        }



        foreach ($nuevoCalculo as $item) {
            foreach ($item as $subItem) {
                $pagoMoratorio=0;
                $ivaMoratorio=0;
                $interesGenerado=0;
                $ivaInteres=0;

                foreach ($subItem as $key=>$value) {
                    if($key=="pago_total")
                    {

                        $saldoLineaCalculo['saldoLineaCorte']+=$value;

                    }

                    if($key=="saldo_capital")
                    {

                        $saldoLineaCalculo['saldoInsoluto']+=$value;

                    }

                    if($key=="pago_moratorios"){
                        $pagoMoratorio=$value;
                    }

                    if($key=="iva_moratorios"){
                        $ivaMoratorio=$value;
                    }

                    if($key=="interes_generado"){
                        $interesGenerado=$value;
                    }

                    if($key=="iva_moratorios"){
                        $saldoLineaCalculo['iva']+=$value;
                    }

                    if($key=="iva_interes_generado"){
                        $saldoLineaCalculo['iva']+=$value;
                        $ivaInteres=$value;
                    }
                }


                $resta=$pagoMoratorio-$ivaMoratorio;


                $saldoLineaCalculo['interesesDevengados']+=$resta+$interesGenerado;

                $saldoLineaCalculo['interesOrdinario']+=$interesGenerado;
                $saldoLineaCalculo['interesMoratorios']+=$resta;


                $saldoLineaCalculo['ivaMoratorio']+=$ivaMoratorio;
                $saldoLineaCalculo['ivaInteres']+=$ivaInteres;
            }
        }

        $capitalizacion = $this->calcularCapitalizacion($nuevoCalculoAnterior, $nuevoCalculo);
        $saldoLineaCalculo['capitalizado'] = $capitalizacion['saldoLineaCalculo']['capitalizado'];
        $saldoLineaCalculo['interes_pagado'] = $capitalizacion['saldoLineaCalculo']['interes_pagado'];
        $saldoLineaCalculo['interes_pagado_capitalizable'] = $capitalizacion['saldoLineaCalculo']['interes_pagado_capitalizable'];
        $saldoLineaCalculoAnterior['capitalizado'] = $capitalizacion['saldoLineaCalculoAnterior']['capitalizado'];
        $saldoLineaCalculoAnterior['interes_pagado'] = $capitalizacion['saldoLineaCalculoAnterior']['interes_pagado'];
        $saldoLineaCalculoAnterior['interes_pagado_capitalizable'] = $capitalizacion['saldoLineaCalculoAnterior']['interes_pagado_capitalizable'];

        $periodoCapitalizado  = $saldoLineaCalculo['capitalizado'] - $saldoLineaCalculoAnterior['capitalizado'];
        $periodoInteresPagado = $saldoLineaCalculo['interes_pagado'] - $saldoLineaCalculoAnterior['interes_pagado'];
        $periodoInteresPagadoCap = $saldoLineaCalculo['interes_pagado_capitalizable'] - $saldoLineaCalculoAnterior['interes_pagado_capitalizable'];

        //NOTA: El saldo de linea y el saldo a pagar son casi iguales, la diferencia es que saldo de linea incluye algunos datos mas.
        //Originalmente el saldo de linea y el saldo a pagar eran diferentes en que el saldo de linea incluia las disposiciones de capital y el saldo a pagar solo incluia los vencimientos de capital.
        $saldoLinea=array(
            'capital'=>0,
            'intereses'=>0,
            'comisiones'=>0,
            'mora'=>0,
        );
        $saldoPagar=array(
            'capital'=>0,
            'intereses'=>0,
            'comisiones'=>0,
            'mora'=>0,
        );
        $resumenPeriodo=array(
            'capital'=>array(
                'cargos'=>0,
                'abonos'=>0,
                'saldo_anterior'=>0,
            ),
            'interes'=>array(
                'cargos'=>0,
                'abonos'=>0,
                'saldo_anterior'=>0,
                'capitalizado'=>0,
            ),
            'mora'=>array(
                'cargos'=>0,
                'abonos'=>0,
                'saldo_anterior'=>0,
            ),
            'iva_interes'=>array(
                'cargos'=>0,
                'abonos'=>0,
                'saldo_anterior'=>0,
            ),
            'iva_mora'=>array(
                'cargos'=>0,
                'abonos'=>0,
                'saldo_anterior'=>0,
            )
        );

        $resumenPeriodo['interes']['capitalizado'] = $periodoCapitalizado;
        $saldoLinea['interes_pagado'] = $periodoInteresPagado;
        $saldoLinea['interes_pagado_capitalizable'] = $periodoInteresPagadoCap;
        $comisionesPeriodo=array(
            //array(),
        );
        $detallesPeriodo=array(
            //array(),
        );
        $keyDetallePeriodo=0;

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if($ip=="187.189.90.2"){
            $ip=0;
        }

        //DEVENGADOS
        $fechaSinCambiar = $fecha1;
        foreach ($amortizacionesAll as $keyP=>$amortizaciones) {
            //Capital
            if($keyP==0){
                //Si es cero es la principal
                if(isset($amortizaciones[0])){
                    //Si es un grupo solidario
                    if(isset($amortizaciones[0]->id_grupo_solidario) && $amortizaciones[0]->id_grupo_solidario!=0){
                        //Si se dispuso antes del periodo o en el periodo se agrega al saldo
                        if(strtotime($amortizaciones[0]->id_grupo_solidario0->id_solicitud0->fecha_disposicion)<=strtotime($fecha2)){
                            $saldoLinea['capital']+=$amortizaciones[0]->id_grupo_solidario0->monto;
                            $saldoPagar['capital']+=$amortizaciones[0]->id_grupo_solidario0->monto;

							//Hay un tema con el capital ya que al tomar en cuenta la disposición y no el vencimiento, no es posible por ahora considerar los descuentos de capital.
                            if(strtotime($amortizaciones[0]->id_grupo_solidario0->id_solicitud0->fecha_disposicion)<strtotime($fecha1)){
                                $resumenPeriodo['capital']['saldo_anterior']+=$amortizaciones[0]->id_grupo_solidario0->monto;
                            }else{
                                $resumenPeriodo['capital']['cargos']+=$amortizaciones[0]->id_grupo_solidario0->monto;

                                $detallesPeriodo[$keyDetallePeriodo]['operacion']='disposicion';
                                $detallesPeriodo[$keyDetallePeriodo]['solicitud']=$model->id;
                                $detallesPeriodo[$keyDetallePeriodo]['disposicion']=$amortizacion->id_disposicion;
                                $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion']=($keyP==0?"S_".$model->clave:"D_".$amortizacion->id_disposicion0->clave);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion']=($keyP==0?$model->fecha_ultimo_vencimiento:$amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                                $detallesPeriodo[$keyDetallePeriodo]['plazo']=($keyP==0?$model->plazo_autorizado:$amortizacion->id_disposicion0->plazo);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha']=$amortizaciones[0]->id_grupo_solidario0->id_solicitud0->fecha_disposicion;
                                $detallesPeriodo[$keyDetallePeriodo]['concepto']="Disposición";
                                $detallesPeriodo[$keyDetallePeriodo]['cargo']+=$amortizaciones[0]->id_grupo_solidario0->monto;
                                $detallesPeriodo[$keyDetallePeriodo]['abono']+=0;
                                $detallesPeriodo[$keyDetallePeriodo]['capital']+=$amortizaciones[0]->id_grupo_solidario0->monto;
                                $detallesPeriodo[$keyDetallePeriodo]['interes']+=0;
                                $detallesPeriodo[$keyDetallePeriodo]['mora']+=0;
                                $detallesPeriodo[$keyDetallePeriodo]['iva']+=0;
                            }
                        }
                    }else{
                        //Si es un individual simple o primera disposicion revolvente
                        if(strtotime($amortizaciones[0]->id_solicitud0->fecha_disposicion)<=strtotime($fecha2)){
                            //Si se dispuso antes del periodo o en el periodo se agrega al saldo
                            $saldoLinea['capital']+=$amortizaciones[0]->id_solicitud0->entregable;
                            if($amortizaciones[0]->id_solicitud0->id_producto0->id_tipo_producto==9){
                                $saldoLinea['capital']+=$amortizaciones[0]->id_solicitud0->enganche;
                                //$saldoLinea['capital_pagado']-=$amortizaciones[0]->id_solicitud0->enganche;
                                if($solicitud->fecha_disposicion>=$fecha1 && $solicitud->fecha_disposicion<=$fecha2){
                                    $resumenPeriodo['capital']['cargos']+=$amortizaciones[0]->id_solicitud0->entregable;
                                }else{
                                    $resumenPeriodo['capital']['saldo_anterior']+=$amortizaciones[0]->id_solicitud0->enganche;
                                }
                            }
                            $saldoPagar['capital']+=$amortizaciones[0]->id_solicitud0->entregable;
                            //Hay un tema con el capital ya que al tomar en cuenta la disposición y no el vencimiento, no es posible por ahora considerar los descuentos de capital.
                            if(strtotime($amortizaciones[0]->id_solicitud0->fecha_disposicion)<strtotime($fecha1)){
                                $resumenPeriodo['capital']['saldo_anterior']+=$amortizaciones[0]->id_solicitud0->entregable;
                                if($amortizaciones[0]->id_solicitud0->intereses_visibles==1){
                                    $detallesPeriodo[$keyDetallePeriodo]['operacion']='disposicion';
                                    $detallesPeriodo[$keyDetallePeriodo]['solicitud']=$model->id;
                                    $detallesPeriodo[$keyDetallePeriodo]['disposicion']=$amortizacion->id_disposicion;
                                    $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion']=($keyP==0?"S_".$model->clave:"D_".$amortizacion->id_disposicion0->clave);
                                    $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                    $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                    $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion']=($keyP==0?$model->fecha_ultimo_vencimiento:$amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                                    $detallesPeriodo[$keyDetallePeriodo]['plazo']=($keyP==0?$model->plazo_autorizado:$amortizacion->id_disposicion0->plazo);
                                    $detallesPeriodo[$keyDetallePeriodo]['fecha']=$amortizaciones[0]->id_solicitud0->fecha_disposicion;
                                    $detallesPeriodo[$keyDetallePeriodo]['concepto']="Disposición";
                                    $detallesPeriodo[$keyDetallePeriodo]['cargo']+=0;
                                    $detallesPeriodo[$keyDetallePeriodo]['abono']+=0;
                                    $detallesPeriodo[$keyDetallePeriodo]['capital']+=0;
                                    $detallesPeriodo[$keyDetallePeriodo]['interes']+=0;
                                    $detallesPeriodo[$keyDetallePeriodo]['mora']+=0;
                                    $detallesPeriodo[$keyDetallePeriodo]['iva']+=0;
                                }
                            }else{
                                $resumenPeriodo['capital']['cargos']+=$amortizaciones[0]->id_solicitud0->entregable;

                                $detallesPeriodo[$keyDetallePeriodo]['operacion']='disposicion';
                                $detallesPeriodo[$keyDetallePeriodo]['solicitud']=$model->id;
                                $detallesPeriodo[$keyDetallePeriodo]['disposicion']=$amortizacion->id_disposicion;
                                $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion']=($keyP==0?"S_".$model->clave:"D_".$amortizacion->id_disposicion0->clave);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion']=($keyP==0?$model->fecha_ultimo_vencimiento:$amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                                $detallesPeriodo[$keyDetallePeriodo]['plazo']=($keyP==0?$model->plazo_autorizado:$amortizacion->id_disposicion0->plazo);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha']=$amortizaciones[0]->id_solicitud0->fecha_disposicion;
                                $detallesPeriodo[$keyDetallePeriodo]['concepto']="Disposición";
                                $detallesPeriodo[$keyDetallePeriodo]['cargo']+=$amortizaciones[0]->id_solicitud0->entregable;
                                $detallesPeriodo[$keyDetallePeriodo]['abono']+=0;
                                $detallesPeriodo[$keyDetallePeriodo]['capital']+=$amortizaciones[0]->id_solicitud0->entregable;
                                $detallesPeriodo[$keyDetallePeriodo]['interes']+=0;
                                $detallesPeriodo[$keyDetallePeriodo]['mora']+=0;
                                $detallesPeriodo[$keyDetallePeriodo]['iva']+=0;
                            }
                        }
                    }
                }
                $modelAmortizacion=Amortizaciones::model()->findByPK($amortizaciones[0]->id);
                if(is_null($modelAmortizacion->id_solicitud)){
                    if(!is_null($modelAmortizacion->id_disposicion)){
                        $disposicion=Disposiciones::model()->findByPK($modelAmortizacion->id_disposicion);
                        $idSolicitud=$disposicion->id_solicitud;
                    }
                }else{
                    $idSolicitud=$modelAmortizacion->id_solicitud;
                }
                $solicitud=Solicitudes::model()->findByPK($idSolicitud);
                $disposiciones_amortizaciones=Disposiciones::model()->findAll("id_solicitud='".$idSolicitud."'");//correccion amortizaciones sin id de solicitud.
                $c=0;
                foreach($disposiciones_amortizaciones as $disposicion_periodo){
                    $c++;
                    if(strtotime($disposicion_periodo->fecha)<=strtotime($fecha2)){
                        $saldoLinea['capital']+=$disposicion_periodo->importe;
                        $saldoPagar['capital']+=$disposicion_periodo->importe;
                        if(strtotime($disposicion_periodo->fecha)<strtotime($fecha1)){
                            $resumenPeriodo['capital']['saldo_anterior']+=$disposicion_periodo->importe;
                            if($solicitud->intereses_visibles==1){
                                $detallesPeriodo[$keyDetallePeriodo.$c]['operacion']='disposicion';
                                $detallesPeriodo[$keyDetallePeriodo.$c]['solicitud']=$model->id;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['disposicion']=$disposicion_periodo->id;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['grupo']=$disposicion_periodo->id_grupo;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['clave_disposicion']="D_".$disposicion_periodo->clave;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['fecha_disposicion']=$disposicion_periodo->fecha;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['fecha_vencimiento']=$disposicion_periodo->fecha;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['fecha_ultima_amortizacion']=$disposicion_periodo->fecha_ultimo_vencimiento;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['plazo']=$disposicion_periodo->plazo;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['fecha']=$disposicion_periodo->fecha;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['concepto']="Disposición";
                                $detallesPeriodo[$keyDetallePeriodo.$c]['cargo']+=0;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['abono']+=0;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['capital']+=0;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['interes']+=0;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['mora']+=0;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['iva']+=0;
                            }
                        }else{
                            $resumenPeriodo['capital']['cargos']+=$disposicion_periodo->importe;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['operacion']='disposicion';
                            $detallesPeriodo[$keyDetallePeriodo.$c]['solicitud']=$model->id;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['disposicion']=$disposicion_periodo->id;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['grupo']=$disposicion_periodo->id_grupo;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['clave_disposicion']="D_".$disposicion_periodo->clave;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['fecha_disposicion']=$disposicion_periodo->fecha;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['fecha_vencimiento']=$disposicion_periodo->fecha;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['fecha_ultima_amortizacion']=$disposicion_periodo->fecha_ultimo_vencimiento;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['plazo']=$disposicion_periodo->plazo;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['fecha']=$disposicion_periodo->fecha;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['concepto']="Disposición";
                            $detallesPeriodo[$keyDetallePeriodo.$c]['cargo']+=$disposicion_periodo->importe;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['abono']+=0;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['capital']+=$disposicion_periodo->importe;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['interes']+=0;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['mora']+=0;
                            $detallesPeriodo[$keyDetallePeriodo.$c]['iva']+=0;
                        }
                    }
                }
            }else{
                //Si es una disposición revolvente
            }
            if(isset($detallesPeriodo[$keyDetallePeriodo])){
                //Si se agrego la amortización se suma 1 al key
                $keyDetallePeriodo++;
            }

            //Interes, Mora y Comisiones
            foreach ($amortizaciones as $keyA => $amortizacion) {
                if($solicitud->id_producto0->tabla_disposiciones=="unica" && $keyP>0){
                    break;
                }
                $idsDisp[]=$amortizacion->id;
                if(isset($amortizacion->fecha_pago) && $amortizacion->fecha_pago!=0){
                    $fecha_pago=date("Y-m-d",strtotime($amortizacion->fecha_pago));
                }else{
                    $fecha_pago=0;
                }

                $agregar=false;
                $provisiones=false;
                if(strtotime($amortizacion->fecha_amortizacion)<=strtotime($fecha2)){
                    //Si se vence antes del periodo o en el periodo se agrega
                    $agregar=true;
                }else{
                    if(isset($fecha_pago) && $fecha_pago!=0 && strtotime($fecha_pago)<=strtotime($fecha2)){
                        //Si se paga antes del periodo o en el periodo se agrega
                        $agregar=true;
                    }else{
                        if($solicitud->intereses_visibles==1){
                            $agregar=true;
                            $provisiones=true;
                        }
                    }
                }
                if($agregar) {
                    $idsDispA[] = $amortizacion->id;
                    //Capital
                    //El capital de la amortización no se suma al saldo de linea|saldo pagar|resumen del periodo ya que se agrega desde la disposición
                    $saldoLinea['capital'] += 0;
                    $saldoPagar['capital'] += 0;
                    //Si la amortización se paga antes de la fecha1 es parte del saldo anterior
                    if ((isset($fecha_pago) && $fecha_pago != 0 && strtotime($fecha_pago) < strtotime($fecha1)) && ($amortizacion->fecha_amortizacion<$fecha1)) {
                        //Si ya esta pagado se suma cero al saldo
                        $resumenPeriodo['capital']['saldo_anterior'] += 0;
                    } else {
                        //Si se vence o se paga despues de la fecha1 es parte del periodo ya que ya se sabe que es menor a la fecha2
                        //Se suma cero al cargo porque ya se agregi desde la disposicion
                        if ($amortizacion->fecha_amortizacion >= $fecha1) {
                            if ($solicitud->intereses_visibles != 1) {
                                if (!isset($amortizacion->id_disposicion) || $amortizacion->id_disposicion == 0) {
                                    //$resumenPeriodo['capital']['cargos']+=$amortizacion->pago_capital;
                                }
                            }
                            $parcialidadesCapital = ParcialidadesAmortizaciones::model()->findAll("id_amortizacion='" . $amortizacion->id . "'");

                            if ($solicitud->intereses_visibles != 1) {
                                foreach ($parcialidadesCapital as $parcialidad) {
                                    if ($parcialidad->fecha_pago < $fecha1) {
                                        //$resumenPeriodo['capital']['cargos'] -= $parcialidad->pago_capital;
                                    }
                                }
                            }
                            $detallesPeriodo[$keyDetallePeriodo]['operacion'] = 'vencimiento';
                            $detallesPeriodo[$keyDetallePeriodo]['solicitud'] = $model->id;
                            $detallesPeriodo[$keyDetallePeriodo]['disposicion'] = $amortizacion->id_disposicion;
                            $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion'] = ($keyP == 0 ? "S_" . $model->clave : "D_" . $amortizacion->id_disposicion0->clave);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion'] = ($keyP == 0 ? $model->fecha_disposicion : $amortizacion->id_disposicion0->fecha);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento'] = ($keyP == 0 ? $model->fecha_disposicion : $amortizacion->id_disposicion0->fecha);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion'] = ($keyP == 0 ? $model->fecha_ultimo_vencimiento : $amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                            $detallesPeriodo[$keyDetallePeriodo]['plazo'] = ($keyP == 0 ? $model->plazo_autorizado : $amortizacion->id_disposicion0->plazo);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha'] = $amortizacion->fecha_amortizacion;
                            $detallesPeriodo[$keyDetallePeriodo]['concepto'] = "Vencimiento {$amortizacion->numero_amortizacion}";
                            $detallesPeriodo[$keyDetallePeriodo]['cargo'] += $amortizacion->pago_capital + $amortizacion->pago_seguro+$amortizacion->iva_capital;
                            $detallesPeriodo[$keyDetallePeriodo]['abono'] += 0;
                            $detallesPeriodo[$keyDetallePeriodo]['capital'] += $amortizacion->pago_capital;
                            $detallesPeriodo[$keyDetallePeriodo]['interes'] += 0;
                            $detallesPeriodo[$keyDetallePeriodo]['mora'] += 0;
                            $detallesPeriodo[$keyDetallePeriodo]['seguro'] += $amortizacion->pago_seguro;
                            $detallesPeriodo[$keyDetallePeriodo]['iva'] += 0;
                        }else{
                            //Se agregan vencimientos anteriores con lo restante
                            if($amortizacion->status=="Calculado" || $amortizacion->fecha_pago>$fecha1){
                                $detallesPeriodo[$keyDetallePeriodo]['operacion'] = 'vencimiento';
                                $detallesPeriodo[$keyDetallePeriodo]['solicitud'] = $model->id;
                                $detallesPeriodo[$keyDetallePeriodo]['disposicion'] = $amortizacion->id_disposicion;
                                $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion'] = ($keyP == 0 ? "S_" . $model->clave : "D_" . $amortizacion->id_disposicion0->clave);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion'] = ($keyP == 0 ? $model->fecha_disposicion : $amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento'] = ($keyP == 0 ? $model->fecha_disposicion : $amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion'] = ($keyP == 0 ? $model->fecha_ultimo_vencimiento : $amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                                $detallesPeriodo[$keyDetallePeriodo]['plazo'] = ($keyP == 0 ? $model->plazo_autorizado : $amortizacion->id_disposicion0->plazo);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha'] = $amortizacion->fecha_amortizacion;
                                $detallesPeriodo[$keyDetallePeriodo]['concepto'] = "Vencimiento {$amortizacion->numero_amortizacion}";
                                $detallesPeriodo[$keyDetallePeriodo]['cargo'] += $amortizacion->pago_capital + $amortizacion->pago_seguro+$amortizacion->iva_capital;
                                $detallesPeriodo[$keyDetallePeriodo]['abono'] += 0;
                                $detallesPeriodo[$keyDetallePeriodo]['capital'] += $amortizacion->pago_capital;
                                $detallesPeriodo[$keyDetallePeriodo]['interes'] += 0;
                                $detallesPeriodo[$keyDetallePeriodo]['mora'] += 0;
                                $detallesPeriodo[$keyDetallePeriodo]['seguro'] += $amortizacion->pago_seguro;
                                $detallesPeriodo[$keyDetallePeriodo]['iva'] += 0;
                                //Se resta lo pagado
                                $parcialidadesAnteriores = ParcialidadesAmortizaciones::model()->findAll("id_amortizacion='" . $amortizacion->id . "'");
                                foreach ($parcialidadesAnteriores as $parcialidad) {
                                    if ($parcialidad->fecha_pago < $fecha1) {
                                        $detallesPeriodo[$keyDetallePeriodo]['capital'] -= $parcialidad->pago_capital;
                                        $detallesPeriodo[$keyDetallePeriodo]['cargo'] -= $parcialidad->pago_capital;
                                    }
                                }
                            }
                        }
                    }

                    //Interes
                    $interesAcumulado = 0;
                    if ($provisiones) {
                        if (strtotime($fecha2) > strtotime($fechaUltimaProvision)) {
                            $provisionesAmortizacion = ProvisionesAmortizaciones::model()->findAll('id_amortizacion="' . $amortizacion->id . '" AND fecha <="' . $fecha2 . '"');
                            $fechaUltimaProvision = date("Y-m-d", strtotime($amortizaciones[$key - $i]["fecha_amortizacion"]));
                            foreach ($provisionesAmortizacion as $provisionAmortizacion) {
                                $fechaUltimaProvision = $provisionAmortizacion->fecha;
                            }
                            $diferenciaDiasProvision = round((strtotime($fecha2) - strtotime($fechaUltimaProvision)) / 60 / 60 / 24);
                            $parcialidadesPosteriores = ParcialidadesAmortizaciones::model()->findAll('id_amortizacion="' . $amortizacion->id . '" AND fecha_pago >"' . $fechaUltimaProvision . '"');
                            $parcialidadesAnteriores = ParcialidadesAmortizaciones::model()->findAll('id_amortizacion="' . $amortizacion->id . '" AND fecha_pago <= "' . $fechaUltimaProvision . '"');
                            $DisposicionesPosteriores = Disposiciones::model()->findAll('id_solicitud="' . $amortizacion->id_solicitud . '" AND fecha >"' . $fechaUltimaProvision . '"');
                            $saldoProvisiones = $amortizacion->saldo_capital;
                            foreach ($DisposicionesPosteriores as $disp) {
                                $saldoProvisiones -= $disp->importe;
                            }
                            foreach ($parcialidadesAnteriores as $parc) {
                                $saldoProvisiones -= $parc->pago_capital;
                            }
                            $interesAcumulado = 0;
                            $fechaCalculo = $fechaUltimaProvision;
                            $fechaPagoAnterior = date("Y-m-d", strtotime($amortizaciones[$key - $i]["fecha_amortizacion"] . " +1 DAYS"));
                            for ($dia = 1; $dia <= $diferenciaDiasProvision; $dia++) {
                                $fechaCalculo = date('Y-m-d', strtotime($fechaCalculo . "+1 DAY"));
                                $parcialCapital = 0;
                                $veces_año = $solicitud->id_producto0->calculo_base;
                                $tasa_interes = $solicitud->sobretasa + (TasasReferencia::model()->find("serie='" . $solicitud->serie_tasa_referencia . "'")->valor / 100);
                                if ($solicitud->id_producto0->calculo_tasa_variable == "Inicio del periodo") {
                                    $tasa_interes = $solicitud->sobretasa + ($amortizacion->getValorTasaReferencia($solicitud->nombre_tasa_referencia, $fechaPagoAnterior, $solicitud->id_producto0->calculo_tasa_variable) / 100);
                                }
                                $interesAcumulado += ($saldoProvisiones * $tasa_interes * 1 / $veces_año);
                                foreach ($parcialidadesPosteriores as $parc) {
                                    if (strtotime($fechaCalculo) == strtotime($parc->fecha_pago)) {
                                        $parcialCapital += $parc->pago_capital;
                                        $fechaPagoAnterior = $parc->fecha_pago;
                                    } elseif (strtotime($fechaCalculo) > strtotime($parc->fecha_pago)) {
                                        $fechaPagoAnterior = $parc->fecha_pago;
                                    }
                                }
                                foreach ($DisposicionesPosteriores as $disp) {
                                    if (strtotime($fechaCalculo) == strtotime($disp->fecha)) {
                                        $saldoProvisiones += $disp->importe;
                                    }
                                }
                                $saldoProvisiones -= $parcialCapital;
                            }
                        }
                    }
                    //Solo se agregan al saldo de linea|saldo pagar las amortizaciones no pagadas
                    if (!isset($fecha_pago) || $fecha_pago == 0 || strtotime($fecha_pago) > strtotime($fecha2)) {
                        if ($provisiones) {
                            $provisionesAmortizacion = ProvisionesAmortizaciones::model()->findAll('id_amortizacion="' . $amortizacion->id . '" AND fecha <="' . $fecha2 . '"');
                            $interes_provisionado = 0;
                            foreach ($provisionesAmortizacion as $provisionAmortizacion) {
                                $interes_provisionado += $provisionAmortizacion->interes_provisionado;
                                $fechaUltimaProvision = $provisionAmortizacion->fecha;
                            }
                            $saldoLinea['interes'] += ($interes_provisionado + $interesAcumulado);
                            $saldoLinea['iva_interes'] += ($interes_provisionado + $interesAcumulado) * $solicitud->porcentaje_impuesto;
                            $saldoPagar['interes'] += ($interes_provisionado + $interesAcumulado);
                            $saldoPagar['iva_interes'] += ($interes_provisionado + $interesAcumulado) * $solicitud->porcentaje_impuesto;
                        } else {
                            $saldoLinea['interes'] += $amortizacion->pago_interes - $amortizacion->iva_interes_generado;
                            $saldoLinea['iva_interes'] += $amortizacion->iva_interes_generado;
                            $saldoPagar['interes'] += $amortizacion->pago_interes - $amortizacion->iva_interes_generado;
                            $saldoPagar['iva_interes'] += $amortizacion->iva_interes_generado;
                            $saldoLinea['renta'] += $amortizacion->pago_interes+$amortizacion->pago_capital-$amortizacion->iva_interes_generado;
                            $saldoLinea['iva_renta'] += $amortizacion->iva_interes_generado+$amortizacion->iva_capital;
                        }
                    }
                    //Si se paga antes de la fecha1 es parte del saldo anterior
                    if ((isset($fecha_pago) && $fecha_pago != 0 && strtotime($fecha_pago) < strtotime($fecha1)) && ($amortizacion->fecha_amortizacion<$fecha1)) {
                        //Se suma cero al saldo anterior cuando ya esta pagado
                        $resumenPeriodo['interes']['saldo_anterior'] += 0;
                        $resumenPeriodo['iva_interes']['saldo_anterior'] += 0;
                    } else {
                        //Si se vence o se paga despues de la fecha1 es parte del periodo ya que ya se sabe que es menor a la fecha2
                        //Se suman los importes vencidos al cargo
						$iva_comisiones=0;
                        if ($amortizacion->fecha_amortizacion >= $fecha1) {
                            if ($provisiones) {
                                $provisionesAmortizacion = ProvisionesAmortizaciones::model()->findAll('id_amortizacion="' . $amortizacion->id . '" AND fecha <="' . $fecha2 . '"');
                                $interes_provisionado = 0;
                                foreach ($provisionesAmortizacion as $provisionAmortizacion) {
                                    $interes_provisionado += $provisionAmortizacion->interes_provisionado;
                                }
                                $resumenPeriodo['interes']['cargos'] += ($interes_provisionado + $interesAcumulado) - (($interes_provisionado + $interesAcumulado) * $solicitud->porcentaje_impuesto);
                                $resumenPeriodo['iva_interes']['cargos'] += ($interes_provisionado + $interesAcumulado) * $solicitud->porcentaje_impuesto;
                            } else {
                                $resumenPeriodo['interes']['cargos'] += $amortizacion->pago_interes - $amortizacion->iva_interes_generado;
                                $resumenPeriodo['iva_interes']['cargos'] += $amortizacion->iva_interes_generado;
                                $ivaComisiones=0;
                                $comisionModel=ComisionesAmortizaciones::model()->find("id_amortizacion=$amortizacion->id");
                                if(!is_null($comisionModel)){
                                    $ivaComisiones=$amortizacion->pago_comisiones-($parcialidad->pago_comisiones/(1+$comisionModel->id_impuesto0->porcentaje));
                                }
                                $resumenPeriodo['comision']['cargos'] += $amortizacion->pago_comisiones - $ivaComisiones;
                                $resumenPeriodo['iva_comision']['cargos'] += $ivaComisiones;
                                if($solicitud->id_producto0->id_tipo_producto==8){
                                    $resumenPeriodo['interes']['cargos'] += $amortizacion->pago_capital;
                                    $resumenPeriodo['iva_interes']['cargos'] += $amortizacion->iva_capital;
                                }
                            }
                            $parcialidadesInteres = ParcialidadesAmortizaciones::model()->findAll("id_amortizacion='" . $amortizacion->id . "'");

                            foreach ($parcialidadesInteres as $parcialidad) {
                                if ($parcialidad->fecha_pago < $fecha1) {
                                    $ivaInteresP=$parcialidad->pago_interes-($parcialidad->pago_interes/(1+$solicitud->id_impuesto0->porcentaje));
                                    $resumenPeriodo['interes']['cargos'] -= $parcialidad->pago_interes-($ivaInteresP);
                                    $resumenPeriodo['iva_interes']['cargos'] -= $ivaInteresP;
                                }
                            }
                            $detallesPeriodo[$keyDetallePeriodo]['operacion'] = 'vencimiento';
                            $detallesPeriodo[$keyDetallePeriodo]['solicitud'] = $model->id;
                            $detallesPeriodo[$keyDetallePeriodo]['disposicion'] = $amortizacion->id_disposicion;
                            $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion'] = ($keyP == 0 ? "S_" . $model->clave : "D_" . $amortizacion->id_disposicion0->clave);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion'] = ($keyP == 0 ? $model->fecha_disposicion : $amortizacion->id_disposicion0->fecha);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento'] = ($keyP == 0 ? $model->fecha_disposicion : $amortizacion->id_disposicion0->fecha);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion'] = ($keyP == 0 ? $model->fecha_ultimo_vencimiento : $amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                            $detallesPeriodo[$keyDetallePeriodo]['plazo'] = ($keyP == 0 ? $model->plazo_autorizado : $amortizacion->id_disposicion0->plazo);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha'] = $amortizacion->fecha_amortizacion;
                            $detallesPeriodo[$keyDetallePeriodo]['concepto'] = "Vencimiento {$amortizacion->numero_amortizacion}";
                            if ($provisiones) {
                                $detallesPeriodo[$keyDetallePeriodo]['cargo'] += $interes_provisionado + $interesAcumulado;//Ya incluye el IVA
                            } else {
                                $detallesPeriodo[$keyDetallePeriodo]['cargo'] += $amortizacion->pago_interes;//Ya incluye el IVA
                                $detallesPeriodo[$keyDetallePeriodo]['cargo'] += $amortizacion->pago_comisiones;
                            }
                            $detallesPeriodo[$keyDetallePeriodo]['abono'] += 0;
                            $detallesPeriodo[$keyDetallePeriodo]['capital'] += 0;
                            if ($provisiones) {
                                $detallesPeriodo[$keyDetallePeriodo]['interes'] += ($interes_provisionado + $interesAcumulado) - (($interes_provisionado + $interesAcumulado) * $solicitud->porcentaje_impuesto);
                            } else {
                                $detallesPeriodo[$keyDetallePeriodo]['interes'] +=$amortizacion->interes_generado;
                            }

							foreach ($infoComisiones[$amortizacion->id] as $keyC => $comision) {
								$iva_comisiones += $comision["iva"];
							}
                            $detallesPeriodo[$keyDetallePeriodo]['comision']+=$amortizacion->pago_comisiones-$iva_comisiones;
							$detallesPeriodo[$keyDetallePeriodo]['comision_iva']+=$iva_comisiones;
							if($detallesPeriodo[$keyDetallePeriodo]['comision']<=0){
                                $detallesPeriodo[$keyDetallePeriodo]['comision']=0;
                            }
                            if($detallesPeriodo[$keyDetallePeriodo]['comision_iva']<=0){
                                $detallesPeriodo[$keyDetallePeriodo]['comision_iva']=0;
                            }
							//$detallesPeriodo[$keyDetallePeriodo]['mora'] += $amortizacion->pago_moratorios-$amortizacion->iva_moratorios;
                            //$detallesPeriodo[$keyDetallePeriodo]['iva'] += $amortizacion->iva_interes_generado+$iva_comisiones;
							//validar
							$productoZ=Productos::model()->findByPk($model->id_producto);
                			if($productoZ->id_tipo_producto!=8){
                                $detallesPeriodo[$keyDetallePeriodo]['iva'] += $amortizacion->iva_interes_generado;
                            $detallesPeriodo[$keyDetallePeriodo]['iva'] += $amortizacion->pago_comisiones - ($amortizacion->pago_comisiones / (1 + $solicitud->id_impuesto_moratorios0->porcentaje));
							}elseif($productoZ->id_tipo_producto==8){
                                $detallesPeriodo[$keyDetallePeriodo]['iva'] += $amortizacion->iva_interes_generado;
								$detallesPeriodo[$keyDetallePeriodo]['iva'] += $amortizacion->iva_capital;
							}
						} else {
                            $resumenPeriodo['interes']['saldo_anterior'] += $amortizacion->pago_interes - $amortizacion->iva_interes_generado;
                            $resumenPeriodo['iva_interes']['saldo_anterior'] += $amortizacion->iva_interes_generado;
                            $ivaComisiones=0;
                            $comisionModel=ComisionesAmortizaciones::model()->find("id_amortizacion=$amortizacion->id");
                            if(!is_null($comisionModel)){
                                $ivaComisiones=$amortizacion->pago_comisiones-($amortizacion->pago_comisiones/(1+$comisionModel->id_impuesto0->porcentaje));
                            }
                            $resumenPeriodo['comision']['saldo_anterior'] += $amortizacion->pago_comisiones - $ivaComisiones;
                            $resumenPeriodo['iva_comision']['saldo_anterior'] +=$ivaComisiones;
                            //Se agregan vencimientos anteriores con lo restante
                            if($amortizacion->status=="Calculado" || $amortizacion->fecha_pago>$fecha2){
                                $detallesPeriodo[$keyDetallePeriodo]['operacion'] = 'vencimiento';
                                $detallesPeriodo[$keyDetallePeriodo]['solicitud'] = $model->id;
                                $detallesPeriodo[$keyDetallePeriodo]['disposicion'] = $amortizacion->id_disposicion;
                                $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion'] = ($keyP == 0 ? "S_" . $model->clave : "D_" . $amortizacion->id_disposicion0->clave);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion'] = ($keyP == 0 ? $model->fecha_disposicion : $amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento'] = ($keyP == 0 ? $model->fecha_disposicion : $amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion'] = ($keyP == 0 ? $model->fecha_ultimo_vencimiento : $amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                                $detallesPeriodo[$keyDetallePeriodo]['plazo'] = ($keyP == 0 ? $model->plazo_autorizado : $amortizacion->id_disposicion0->plazo);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha'] = $amortizacion->fecha_amortizacion;
                                $detallesPeriodo[$keyDetallePeriodo]['concepto'] = "Vencimiento {$amortizacion->numero_amortizacion}";
                                if ($provisiones) {
                                    $detallesPeriodo[$keyDetallePeriodo]['cargo'] += $interes_provisionado + $interesAcumulado;//Ya incluye el IVA
                                } else {
                                    $detallesPeriodo[$keyDetallePeriodo]['cargo'] += $amortizacion->pago_interes;//Ya incluye el IVA
                                    $detallesPeriodo[$keyDetallePeriodo]['cargo'] += $amortizacion->pago_comisiones;
                                }
                                $detallesPeriodo[$keyDetallePeriodo]['abono'] += 0;
                                $detallesPeriodo[$keyDetallePeriodo]['capital'] += 0;
                                if ($provisiones) {
                                    $detallesPeriodo[$keyDetallePeriodo]['interes'] += ($interes_provisionado + $interesAcumulado) - (($interes_provisionado + $interesAcumulado) * $solicitud->porcentaje_impuesto);
                                } else {
                                    $detallesPeriodo[$keyDetallePeriodo]['interes'] +=$amortizacion->interes_generado;
                                }

                                foreach ($infoComisiones[$amortizacion->id] as $keyC => $comision) {
                                    $iva_comisiones += $comision["iva"];
                                }
                                $detallesPeriodo[$keyDetallePeriodo]['comision']+=($amortizacion->pago_comisiones-$iva_comisiones);
                                $detallesPeriodo[$keyDetallePeriodo]['comision_iva']+=$iva_comisiones;
                                if($detallesPeriodo[$keyDetallePeriodo]['comision']<=0){
                                    $detallesPeriodo[$keyDetallePeriodo]['comision']=0;
                                }
                                if($detallesPeriodo[$keyDetallePeriodo]['comision_iva']<=0){
                                    $detallesPeriodo[$keyDetallePeriodo]['comision_iva']=0;
                                }
                                //validar
                                $productoZ=Productos::model()->findByPk($model->id_producto);
                                if($productoZ->id_tipo_producto!=8){
                                    $detallesPeriodo[$keyDetallePeriodo]['iva'] += $amortizacion->iva_interes_generado;
                                    $ivaComisiones=0;
                                    $comisionModel=ComisionesAmortizaciones::model()->find("id_amortizacion=$amortizacion->id");
                                    if(!is_null($comisionModel)){
                                        $ivaComisiones=$amortizacion->pago_comisiones-($amortizacion->pago_comisiones/(1+$comisionModel->id_impuesto0->porcentaje));
                                    }
                                    $detallesPeriodo[$keyDetallePeriodo]['iva'] += $ivaComisiones;
                                }elseif($productoZ->id_tipo_producto==8){
                                    $detallesPeriodo[$keyDetallePeriodo]['iva'] += $amortizacion->iva_interes_generado;
                                    $detallesPeriodo[$keyDetallePeriodo]['iva'] += $amortizacion->iva_capital;
                                }
                                //Se resta lo pagado
                                $parcialidadesAnteriores = ParcialidadesAmortizaciones::model()->findAll("id_amortizacion='" . $amortizacion->id . "'");
                                foreach ($parcialidadesAnteriores as $parcialidad) {
                                    if ($parcialidad->fecha_pago < $fecha1) {
                                        $ivaInteresP=$parcialidad->pago_interes-($parcialidad->pago_interes/(1+$solicitud->id_impuesto0->porcentaje));
                                        $detallesPeriodo[$keyDetallePeriodo]['interes'] -= $parcialidad->pago_interes-($ivaInteresP);
                                        $detallesPeriodo[$keyDetallePeriodo]['iva'] -= $ivaInteresP;
                                        $detallesPeriodo[$keyDetallePeriodo]['cargo'] -= $parcialidad->pago_interes;
                                        $comisionModel=ComisionesAmortizaciones::model()->find("id_amortizacion=$amortizacion->id");
                                        if(!is_null($comisionModel)){
                                            $ivaComisionesP=$parcialidad->pago_comisiones-($parcialidad->pago_comisiones/(1+$comisionModel->id_impuesto0->porcentaje));
                                        }else{
                                            $ivaComisionesP=0;
                                        }
                                        $detallesPeriodo[$keyDetallePeriodo]['comision']-=$parcialidad->pago_comisiones-$ivaComisionesP;
                                        $detallesPeriodo[$keyDetallePeriodo]['iva'] -= $ivaComisionesP;
                                        $detallesPeriodo[$keyDetallePeriodo]['comision_iva'] -= $ivaComisionesP;
                                        $detallesPeriodo[$keyDetallePeriodo]['cargo'] -= $parcialidad->pago_comisiones;
                                    }
                                }
                            }
                        }

                    }
                    //Comisiones
                    //Solo se agregan al saldo de linea|saldo pagar las amortizaciones no pagadas
                    if (!isset($fecha_pago) || $fecha_pago == 0 || strtotime($fecha_pago) > strtotime($fecha2)) {
                        foreach ($infoComisiones[$amortizacion->id] as $keyC => $comision) {
                            $saldoLinea['comisiones'] += $comision["monto"];
                            $saldoLinea['iva_comisiones'] += $comision["iva"];
                            $saldoPagar['comisiones'] += $comision["monto"];
                            $saldoPagar['iva_comisiones'] += $comision["iva"];
                        }
                    }

                    //Moratorios
                    $dataMora=$amortizacion->getMontosMora($fecha2,$fecha1);
                    $pagoMora=$dataMora["mora"];
                    $ivaMora=$dataMora["iva"];
                    $saldoMora=$dataMora["saldo_periodo"];
                    $saldoIvaMora=$dataMora["saldo_periodo_iva"];
                    $saldoAnteriorMora=$dataMora["saldo_anterior"];
                    $saldoAnteriorIvaMora=$dataMora["saldo_anterior_iva"];
                    //Solo se agregan al saldo de linea|saldo pagar las amortizaciones no pagadas
                    if (!isset($fecha_pago) || $fecha_pago == 0 || strtotime($fecha_pago) > strtotime($fecha2)) {
						$saldoLinea['mora'] += $pagoMora;
                        $saldoLinea['iva_mora'] += $ivaMora;
                        $saldoPagar['mora'] += $pagoMora;
                        $saldoPagar['iva_mora'] += $ivaMora;
                    }
                    //Si se paga antes de la fecha1 es parte del saldo anterior
                    if ((isset($fecha_pago) && $fecha_pago != 0 && strtotime($fecha_pago) < strtotime($fecha1)) && ($amortizacion->fecha_amortizacion<$fecha1)) {
                        //Se suma cero al saldo anterior cuando ya esta pagado
                        //$resumenPeriodo['mora']['saldo_anterior'] += $saldoAnteriorMora;
                        //$resumenPeriodo['iva_mora']['saldo_anterior'] += $saldoAnteriorIvaMora;
                        $resumenPeriodo['mora']['saldo_anterior'] += 0;
                        $resumenPeriodo['iva_mora']['saldo_anterior'] += 0;
                    } else {
                        if($pagoMora>0 && ($amortizacion->fecha_amortizacion>=$fecha1 || ($amortizacion->status=="Calculado" || $amortizacion->fecha_pago>$fecha1))){
                            //montos en el periodo del estado de cuenta
                            $resumenPeriodo['mora']['cargos'] += $saldoMora;
                            $resumenPeriodo['iva_mora']['cargos'] += $saldoIvaMora;
                            $detallesPeriodo[$keyDetallePeriodo]['operacion'] = 'vencimiento';
                            $detallesPeriodo[$keyDetallePeriodo]['solicitud'] = $model->id;
                            $detallesPeriodo[$keyDetallePeriodo]['disposicion'] = $amortizacion->id_disposicion;
                            $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion'] = ($keyP == 0 ? "S_" . $model->clave : "D_" . $amortizacion->id_disposicion0->clave);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion'] = ($keyP == 0 ? $model->fecha_disposicion : $amortizacion->id_disposicion0->fecha);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento'] = ($keyP == 0 ? $model->fecha_disposicion : $amortizacion->id_disposicion0->fecha);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion'] = ($keyP == 0 ? $model->fecha_ultimo_vencimiento : $amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                            $detallesPeriodo[$keyDetallePeriodo]['plazo'] = ($keyP == 0 ? $model->plazo_autorizado : $amortizacion->id_disposicion0->plazo);
                            $detallesPeriodo[$keyDetallePeriodo]['fecha'] = $amortizacion->fecha_amortizacion;
                            $detallesPeriodo[$keyDetallePeriodo]['concepto'] = "Vencimiento {$amortizacion->numero_amortizacion}";
                            $detallesPeriodo[$keyDetallePeriodo]['cargo'] += $saldoMora+$saldoIvaMora+$saldoAnteriorMora+$saldoAnteriorIvaMora;
                            $detallesPeriodo[$keyDetallePeriodo]['abono'] += 0;
                            $detallesPeriodo[$keyDetallePeriodo]['capital'] += 0;
                            $detallesPeriodo[$keyDetallePeriodo]['interes'] += 0;
                            $detallesPeriodo[$keyDetallePeriodo]['mora'] += $saldoMora;
                            $detallesPeriodo[$keyDetallePeriodo]['iva'] += $saldoIvaMora;
                            //se agrega lo de periodos anteriores al vencimiento por petición de AMN
                            $detallesPeriodo[$keyDetallePeriodo]['mora'] += $saldoAnteriorMora;
                            $detallesPeriodo[$keyDetallePeriodo]['iva'] += $saldoAnteriorIvaMora;
                            //montos anteriores al periodo
                            $resumenPeriodo['mora']['saldo_anterior'] += $saldoAnteriorMora;
                            $resumenPeriodo['iva_mora']['saldo_anterior'] += $saldoAnteriorIvaMora;
							//echo"<pre>"; var_dump($saldoAnteriorIvaMora);
							//echo"<pre>"; var_dump($saldoIvaMora);
							//echo"<pre>"; var_dump($amortizacion->fecha_amortizacion);
                        }
                    }
                    $parcialidadesPagadas = ParcialidadesAmortizaciones::model()->findAll(array("condition" => " status!='Condonado' AND id_amortizacion=:amortizacion AND CAST(fecha_pago AS DATE)<=:fecha", "order" => "fecha_pago ASC", "params" => array(':amortizacion' => $amortizacion->id, ':fecha' => $fecha2)));
                }else{
                    $idsDisp1[]=$amortizacion->id;
                    //Si no se agrega la amortización revisar si la fecha anterior estaba en el periodo para calcular los días de interes de la fecha de vencimiento anterior a la fecha de corte del periodo
                    //Se usa CAST en la fecha de pago para eliminar la hora de la fecha
                    $parcialidades=ParcialidadesAmortizaciones::model()->findAll(array("condition"=>"id_amortizacion=:amortizacion AND CAST(fecha_pago AS DATE)<=:fecha","order"=>"fecha_pago ASC","params"=>array(':amortizacion'=>$amortizacion->id,':fecha'=>$fecha2)));

                    //Interes
                    if(
						isset($amortizaciones[$keyA - 1])
						&& strtotime(date("Y-m-d", strtotime($amortizaciones[$keyA - 1]->fecha_amortizacion))) < strtotime(date("Y-m-d", strtotime($fecha2)))
					){
                        if (
							Controller::normalizarTexto($cobroInteres) == "ajustable"
							|| Controller::normalizarTexto($cobroInteres) == "variable"
						) {
                            $limiteDias=false;
                            if(
								in_array(
									$model->id_tipo_amortizacion0->nombre,
									array(
										'Mensual',
										'Trimestral',
										'Semestral',
										'Anual',
										'Fin de Mes',
										'Pago Único (Bullet) con Interes Mensual',
										'Pago Único (Bullet) con Interes Capitalizable a Fin de Mes en días',
										'15 y Fin de Mes',
										'Pago Único (Bullet) con Interes Semestral',
										'Quincenal Personalizado'
									)
								)
							){
                                $limiteDias=true;
                            }

                            $diasAño=$model->id_producto0->calculo_base;
                            $veces_anual=$amortizacion->getVeces_anual($model);
                            if(isset($amortización->id_grupo_solidario) && $amortización->id_grupo_solidario!=0){
                                $tasa_interes=$model->sobretasa+($amortizacion->getValorTasaReferencia($model->nombre_tasa_referencia)/100);
                            }elseif(isset($amortización->id_disposicion) && $amortización->id_disposicion!=0){
                                $tasa_interes=$amortizacion->id_disposicion0->tasa;
                            }else{
                                $tasa_interes=$model->sobretasa+($amortizacion->getValorTasaReferencia($model->nombre_tasa_referencia)/100);
                            }
                            $fechaP0=$amortizaciones[$keyA-1]->fecha_amortizacion;
                            $saldoCapital0=$amortizaciones[$keyA-1]->saldo_final;


                            $interes0=0;
                            $diasMaximo=round($diasAño/$veces_anual);
                            $diasCalculados=0;
                            $fecha_vencimiento=$amortizacion->fecha_amortizacion;
                            if (
								Controller::normalizarTexto($cobroInteres) == "ajustable"
								|| Controller::normalizarTexto($cobroInteres) == "variable"
							) {
                                $fecha_vencimiento=$fecha2;
                            }

                            $disposiciones=Disposiciones::model()->findAll("id_solicitud='".$amortizacion->id_solicitud."'");
                            $interes_disposicion=0;
                            $interes_disposiciones=0;
                            if($solicitud->id_producto0->tabla_disposiciones=="unica"){
                                foreach($disposiciones as $disposicion){
                                    if((strtotime($disposicion->fecha)<=strtotime($fecha2))
                                        && (strtotime($disposicion->fecha)<strtotime($amortizacion->fecha_amortizacion)) && strtotime($disposicion->fecha)>=strtotime($amortizaciones[$keyA-1]->fecha_amortizacion)){
                                        $capital_disposicion=$disposicion->importe;
                                        $fecha_disposicion=$disposicion->fecha;
                                        $interes_disposicion=$disposicion->importe*$tasa_interes*round((strtotime($fecha2)-strtotime($disposicion->fecha))/60/60/24,0)/$diasAño;
                                        $interes_disposicion=round($interes_disposicion,$decimales);
                                        $interes_disposiciones+=$interes_disposicion;
                                    }
                                }
                            }

                            foreach($parcialidades as $parcialidad){
                                $idsParcialidad[]=$parcialidad->id;
                                $fechaPagoParcial=date("Y-m-d",strtotime($parcialidad->fecha_pago));

                                if($parcialidad->pago_capital>0){
                                    if(strtotime($fechaPagoParcial)<strtotime($fecha_vencimiento)){
                                        if(strtotime($fechaPagoParcial)>strtotime($fechaP0)){
                                            //$interes0-=$parcialidad->pago_interes;
                                            $diferenciaDias=round((strtotime($fechaPagoParcial)-strtotime($fechaP0))/60/60/24,0);
                                            if($diasCalculados+$diferenciaDias>$diasMaximo && $limiteDias==true){
                                                $diferenciaDias=$diasMaximo-$diasCalculados;
                                            }
                                            $diasCalculados+=$diferenciaDias;
                                            $interes0+=round((1+$model->porcentaje_impuesto)*$saldoCapital0*$tasa_interes*$diferenciaDias/$diasAño,$decimales);
                                            $fechaP0=$parcialidad->fecha_pago;

                                            $saldoCapital0-=$parcialidad->pago_capital;
                                        }
                                    }
                                }
                            }
                            if(strtotime($fecha_vencimiento)>strtotime($fechaP0)){
                                $diferenciaDias=round((strtotime($fecha_vencimiento)-strtotime($fechaP0))/60/60/24,0);
                                if($diasCalculados+$diferenciaDias>$diasMaximo && $limiteDias==true){
									$diferenciaDias=$diasMaximo-$diasCalculados;
                                }
                                $interes0+=round((1+$model->porcentaje_impuesto)*$saldoCapital0*$tasa_interes*($diferenciaDias)/$diasAño,$decimales);
                            }
                            $interes_generado=$interes0/(1+$model->porcentaje_impuesto);
                            $interes_generado=round($interes_generado,$decimales);
                            $interes_generado=$interes_generado;
                            $iva_interes_generado=$interes_generado*$model->porcentaje_impuesto;
                        }
                    }
                    $fecha1Ante = $fechaSinCambiar;
                    $between = false;
                    if (strtotime($amortizaciones[$keyA-1]->fecha_amortizacion)>=strtotime($fecha1Ante) && strtotime($amortizaciones[$keyA-1]->fecha_amortizacion)<=strtotime($fecha2)) {
                        $between = true;
                    }
                    if ($between==false) {
                        $fechaMenos = date("Y-m-d H:i:s",strtotime('-1 day',strtotime($fecha1Ante)));
                        $fecha1Ante= $fechaMenos;
                    }
                    $diasAmortizacion=round((strtotime($amortizacion->fecha_amortizacion)-strtotime($amortizaciones[$keyA-1]->fecha_amortizacion))/60/60/24,0);
                    $diasFecha2=round((strtotime($fecha2)-strtotime($amortizaciones[$keyA-1]->fecha_amortizacion))/60/60/24,0);
                    $diasFecha1=round((strtotime($fecha1)-strtotime($amortizaciones[$keyA-1]->fecha_amortizacion))/60/60/24,0);

                    if ($between==false) {
                        $diasFecha1=round((strtotime($fecha1Ante)-strtotime($amortizaciones[$keyA-1]->fecha_amortizacion))/60/60/24,0);
                    }

                    if($diasFecha1<=0){
                        $diasFecha1=0;
                    }
                    if($cobroInteres=="variable"){
                        //Cuando el cobro de interes es variable ya se calcula a los dias reales transcurridos hasta la fecha2
                        $interes_periodo=$interes_generado*(($diasFecha2-$diasFecha1)/$diasFecha2);
                        $iva_interes_periodo=$iva_interes_generado*(($diasFecha2-$diasFecha1)/$diasFecha2);
                        $interes_periodo_anterior=$interes_generado*(($diasFecha1)/$diasFecha2);
                        $iva_interes_periodo_anterior=$iva_interes_generado*(($diasFecha1)/$diasFecha2);
                    }else{
                        $interes_periodo=$interes_generado;
                        $iva_interes_periodo=$iva_interes_generado;
                    }

                    //Mora
                    $mora_generado=0;//La amortización no esta vencida por lo que es cero
                    $iva_mora_generado=0;
                    $mora_periodo=0;
                    $iva_mora_periodo=0;
                    $mora_periodo_anterior=0;
                    $iva_mora_periodo_anterior=0;

                    //Comisiones
                    $importeComisiones=0;
                    $importeComisionesIVA=0;
                    foreach ($infoComisiones[$amortizacion->id] as $keyC => $comision) {
                        $importeComisiones+=$comision["monto"];
                        $importeComisionesIVA+=$comision["iva"];
                    }
                    $comisiones_periodo=0;//Las comisiones no vencen por dia como los intereses solo vencen a la fecha de vencimiento por el importe completo
                    $iva_comisiones_periodo=0;//Las comisiones no vencen por dia como los intereses solo vencen a la fecha de vencimiento por el importe completo
                    $comisiones_periodo_anterior=0;//Las comisiones no vencen por dia como los intereses solo vencen a la fecha de vencimiento por el importe completo
                    $iva_comisiones_periodo_anterior=0;//Las comisiones no vencen por dia como los intereses solo vencen a la fecha de vencimiento por el importe completo


                    //Se calcula lo pagado en parcialidades
                    $interesPagado=0;
                    $ivaInteresPagado=0;
                    $moraPagado=0;
                    $ivaMoraPagado=0;
                    $comisionesPagado=0;
                    $ivaComisionesPagado=0;

                    $interesPagadoAnterior=0;
                    $ivaInteresPagadoAnterior=0;
                    $moraPagadoAnterior=0;
                    $ivaMoraPagadoAnterior=0;
                    $comisionesPagadoAnterior=0;
                    $ivaComisionesPagadoAnterior=0;


                    foreach ($parcialidades as $parcialidad) {
                        if(strtotime($parcialidad->fecha_pago)>strtotime($fecha1)){
                            //Si la fecha de pago es mayor a la fecha1 la parcialidad es del periodo
                            $interesPagado+=$parcialidad->pago_interes*(($amortizacion->pago_interes-$amortizacion->iva_interes_generado)/$amortizacion->pago_interes);
                            $ivaInteresPagado+=$parcialidad->pago_interes*(($amortizacion->iva_interes_generado)/$amortizacion->pago_interes);
                            if($amortizacion->pago_moratorios>0){
                                $moraPagado+=$amortizacion->pago_moratorios*(($amortizacion->pago_moratorios-$amortizacion->iva_moratorios)/$amortizacion->pago_moratorios);
                                $ivaMoraPagado+=$amortizacion->pago_moratorios*(($amortizacion->iva_moratorios)/$amortizacion->pago_moratorios);
                            }
                            if($parcialidad->pago_comisiones>0){
                                $comisionesPagado+=$parcialidad->pago_comisiones*($importeComisiones/($importeComisiones+$importeComisionesIVA));
                                $ivaComisionesPagado+=$parcialidad->pago_comisiones*($importeComisionesIVA/($importeComisiones+$importeComisionesIVA));
                            }
                        }else{
                            //Si la fecha de pago es menor a la fecha1 la parcialidad es anterior al periodo
                            $interesPagadoAnterior+=$parcialidad->pago_interes*(($amortizacion->pago_interes-$amortizacion->iva_interes_generado)/$amortizacion->pago_interes);
                            $ivaInteresPagadoAnterior+=$parcialidad->pago_interes*(($amortizacion->iva_interes_generado)/$amortizacion->pago_interes);
                            if($amortizacion->pago_moratorios>0){
                                $moraPagadoAnterior+=$amortizacion->pago_moratorios*(($amortizacion->pago_moratorios-$amortizacion->iva_moratorios)/$amortizacion->pago_moratorios);
                                $ivaMoraPagadoAnterior+=$amortizacion->pago_moratorios*(($amortizacion->iva_moratorios)/$amortizacion->pago_moratorios);
                            }
                            if($parcialidad->pago_comisiones>0){
                                $comisionesPagadoAnterior+=$parcialidad->pago_comisiones*($importeComisiones/($importeComisiones+$importeComisionesIVA));
                                $ivaComisionesPagadoAnterior+=$parcialidad->pago_comisiones*($importeComisionesIVA/($importeComisiones+$importeComisionesIVA));
                            }
                        }
                    }

                    if ($interesPagadoAnterior<=0) {
                        $saldo_interes_anterior = $interes_periodo_anterior;
                    }else{
                        $saldo_interes_anterior=0;
                    }
                    //INTERES
                    //Si lo pagado en parcialidades es mayor a lo generado se agrega lo pagado en parcialidades
                    if($interes_periodo+$saldo_interes_anterior<$interesPagado){
                        //Se suma el importe pagado en el periodo al saldo linea|saldo pagar
                        //Se suma cero al saldo linea|saldo pagar cuando es pagado anterior porque ya esta pagado.
                        $saldoLinea['interes']+=$interesPagado+0;
                        $saldoLinea['iva_interes']+=$ivaInteresPagado+0;
                        $saldoPagar['interes']+=$interesPagado+0;
                        $saldoPagar['iva_interes']+=$ivaInteresPagado+0;
                        //Se suman los importes pagados en el periodo al cargo
                        $resumenPeriodo['interes']['cargos']+=$interesPagado;
                        $resumenPeriodo['iva_interes']['cargos']+=$ivaInteresPagado;
                        //Se suman cero al cargo cuando es del periodo anterior
                        $resumenPeriodo['interes']['saldo_anterior']+=0;
                        $resumenPeriodo['iva_interes']['saldo_anterior']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['operacion']='vencimiento';
                        $detallesPeriodo[$keyDetallePeriodo]['solicitud']=$model->id;
                        $detallesPeriodo[$keyDetallePeriodo]['disposicion']=$amortizacion->id_disposicion;
                        $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion']=($keyP==0?"S_".$model->clave:"D_".$amortizacion->id_disposicion0->clave);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion']=($keyP==0?$model->fecha_ultimo_vencimiento:$amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                        $detallesPeriodo[$keyDetallePeriodo]['plazo']=($keyP==0?$model->plazo_autorizado:$amortizacion->id_disposicion0->plazo);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha']=$amortizacion->fecha_amortizacion;
                        $detallesPeriodo[$keyDetallePeriodo]['concepto']="Vencimiento {$amortizacion->numero_amortizacion}";
                        $detallesPeriodo[$keyDetallePeriodo]['cargo']+=$interesPagado+$ivaInteresPagado;
                        $detallesPeriodo[$keyDetallePeriodo]['abono']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['capital']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['interes']+=$interesPagado;
                        $detallesPeriodo[$keyDetallePeriodo]['mora']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['iva']+=$ivaInteresPagado;
                    }elseif($interes_periodo>0){
                        $idsParcialidad2[]=$interes_periodo_anterior;
                        //Si lo pagado en parcialidades es menor al devengado se toma lo devengado
                        //Se suma el importe devengado en el periodo al saldo linea|saldo pagar
                        //Se suma el importe devegado en el periodo anterior al saldo linea|saldo pagar
                        $saldoLinea['interes']+=$interes_periodo+$interes_periodo_anterior;
                        $saldoLinea['iva_interes']+=$iva_interes_periodo+$iva_interes_periodo_anterior;
                        $saldoPagar['interes']+=$interes_periodo+$interes_periodo_anterior;
                        $saldoPagar['iva_interes']+=$iva_interes_periodo+$iva_interes_periodo_anterior;

                        //Se suma el importe devengado al cargo
                        $resumenPeriodo['interes']['cargos']+=$interes_periodo;
                        $resumenPeriodo['iva_interes']['cargos']+=$iva_interes_periodo;
                        //Se suma el importe devengado del periodo anterior al saldo anterior
                        $resumenPeriodo['interes']['saldo_anterior']+=$interes_periodo_anterior;
                        $resumenPeriodo['iva_interes']['saldo_anterior']+=$iva_interes_periodo_anterior;

                        $detallesPeriodo[$keyDetallePeriodo]['operacion']='vencimiento';
                        $detallesPeriodo[$keyDetallePeriodo]['solicitud']=$model->id;
                        $detallesPeriodo[$keyDetallePeriodo]['disposicion']=$amortizacion->id_disposicion;
                        $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion']=($keyP==0?"S_".$model->clave:"D_".$amortizacion->id_disposicion0->clave);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion']=($keyP==0?$model->fecha_ultimo_vencimiento:$amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                        $detallesPeriodo[$keyDetallePeriodo]['plazo']=($keyP==0?$model->plazo_autorizado:$amortizacion->id_disposicion0->plazo);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha']=$amortizacion->fecha_amortizacion;
                        $detallesPeriodo[$keyDetallePeriodo]['concepto']="Vencimiento {$amortizacion->numero_amortizacion}";
                        $detallesPeriodo[$keyDetallePeriodo]['cargo']+=$interes_periodo+$iva_interes_periodo;
                        $detallesPeriodo[$keyDetallePeriodo]['abono']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['capital']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['interes']+=$interes_periodo;
                        $detallesPeriodo[$keyDetallePeriodo]['mora']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['iva']+=$iva_interes_periodo;
                    }

                    //MORA
                    //Si lo pagado en parcialidades es mayor a lo generado se agrega lo pagado en parcialidades
                    if($mora_periodo<$moraPagado){
                        //Se suma el importe pagado en el periodo al saldo linea|saldo pagar
                        //Se suma cero al saldo linea|saldo pagar cuando es pagado anterior porque ya esta pagado.
                        $saldoLinea['mora']+=$moraPagado+0;
                        $saldoLinea['iva_mora']+=$ivaMoraPagado+0;
                        $saldoPagar['mora']+=$moraPagado+0;
                        $saldoPagar['iva_mora']+=$ivaMoraPagado+0;

                        //Se suman los importes pagados en el periodo al cargo
                        $resumenPeriodo['mora']['cargos']+=$moraPagado;
                        $resumenPeriodo['iva_mora']['cargos']+=$ivaMoraPagado;
                        //Se suman cero al cargo cuando es del periodo anterior
                        $resumenPeriodo['mora']['saldo_anterior']+=0;
                        $resumenPeriodo['iva_mora']['saldo_anterior']+=0;

                        $detallesPeriodo[$keyDetallePeriodo]['operacion']='vencimiento';
                        $detallesPeriodo[$keyDetallePeriodo]['solicitud']=$model->id;
                        $detallesPeriodo[$keyDetallePeriodo]['disposicion']=$amortizacion->id_disposicion;
                        $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion']=($keyP==0?"S_".$model->clave:"D_".$amortizacion->id_disposicion0->clave);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion']=($keyP==0?$model->fecha_ultimo_vencimiento:$amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                        $detallesPeriodo[$keyDetallePeriodo]['plazo']=($keyP==0?$model->plazo_autorizado:$amortizacion->id_disposicion0->plazo);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha']=$amortizacion->fecha_amortizacion;
                        $detallesPeriodo[$keyDetallePeriodo]['concepto']="Vencimiento {$amortizacion->numero_amortizacion}";
                        $detallesPeriodo[$keyDetallePeriodo]['cargo']+=$moraPagado+$ivaMoraPagado;
                        $detallesPeriodo[$keyDetallePeriodo]['abono']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['capital']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['interes']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['mora']+=$moraPagado;
                        $detallesPeriodo[$keyDetallePeriodo]['iva']+=$ivaMoraPagado;
                    }elseif($mora_periodo>0){
                        //Si lo pagado en parcialidades es menor al devengado se toma lo devengado
                        //Se suma el importe devengado en el periodo al saldo linea|saldo pagar
                        //Se suma el importe devegado en el periodo anterior al saldo linea|saldo pagar
                        $saldoLinea['mora']+=$mora_periodo+$mora_periodo_anterior;
                        $saldoLinea['iva_mora']+=$iva_mora_periodo+$iva_mora_periodo_anterior;
                        $saldoPagar['mora']+=$mora_periodo+$mora_periodo_anterior;
                        $saldoPagar['iva_mora']+=$iva_mora_periodo+$iva_mora_periodo_anterior;

                        //Se suma el importe devengado al cargo
                        $resumenPeriodo['mora']['cargos']+=$mora_periodo;
                        $resumenPeriodo['iva_mora']['cargos']+=$iva_mora_periodo;
                        //Se suma el importe devengado del periodo anterior al saldo anterior
                        $resumenPeriodo['mora']['cargos']+=$mora_periodo_anterior;
                        $resumenPeriodo['iva_mora']['cargos']+=$iva_mora_periodo_anterior;

                        $detallesPeriodo[$keyDetallePeriodo]['operacion']='vencimiento';
                        $detallesPeriodo[$keyDetallePeriodo]['solicitud']=$model->id;
                        $detallesPeriodo[$keyDetallePeriodo]['disposicion']=$amortizacion->id_disposicion;
                        $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion']=($keyP==0?"S_".$model->clave:"D_".$amortizacion->id_disposicion0->clave);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion']=($keyP==0?$model->fecha_ultimo_vencimiento:$amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                        $detallesPeriodo[$keyDetallePeriodo]['plazo']=($keyP==0?$model->plazo_autorizado:$amortizacion->id_disposicion0->plazo);
                        $detallesPeriodo[$keyDetallePeriodo]['fecha']=$amortizacion->fecha_amortizacion;
                        $detallesPeriodo[$keyDetallePeriodo]['concepto']="Vencimiento {$amortizacion->numero_amortizacion}";
                        $detallesPeriodo[$keyDetallePeriodo]['cargo']+=$mora_periodo+$iva_mora_periodo;
                        $detallesPeriodo[$keyDetallePeriodo]['abono']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['capital']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['interes']+=0;
                        $detallesPeriodo[$keyDetallePeriodo]['mora']+=$mora_periodo;
                        $detallesPeriodo[$keyDetallePeriodo]['iva']+=$iva_mora_periodo;
                    }

                    //COMISIONES
                    //Si lo pagado en parcialidades es mayor a lo generado se agrega lo pagado en parcialidades
                    if($comisiones_periodo<$comisionesPagado){
                        //Se suma el importe pagado en el periodo al saldo linea|saldo pagar
                        //Se suma cero al saldo linea|saldo pagar cuando es pagado anterior porque ya esta pagado.
                        //Nota: Las comisiones no se devengan por parcialidades por lo que siempre sera 0 hasta que la amortización vence
                        /*
                        $saldoLinea['comisiones']+=$comisionesPagado+0;
                        $saldoLinea['iva_comisiones']+=$ivaComisionesPagado+0;
                        $saldoPagar['comisiones']+=$comisionesPagado+0;
                        $saldoPagar['iva_comisiones']+=$ivaComisionesPagado+0;
                        */
						$detallesPeriodo[$keyDetallePeriodo]['comision']+=$amortizacion->pago_comisiones-$importeComisionesIVA;
						$detallesPeriodo[$keyDetallePeriodo]['comision_iva']+=$importeComisionesIVA;
                        $detallesPeriodo[$keyDetallePeriodo]['iva']+=$ivaComisionesPagado;
                    }elseif($comisiones_periodo>0){
                        //Si lo pagado en parcialidades es menor al devengado se toma lo devengado
                        //Se suma el importe devengado en el periodo al saldo linea|saldo pagar
                        //Se suma el importe devegado en el periodo anterior al saldo linea|saldo pagar
                        //Nota: Las comisiones no se devengan por parcialidades por lo que siempre sera 0 hasta que la amortización vence
                        /*
                        $saldoLinea['comisiones']+=$comisiones_periodo+$comisiones_periodo_anterior;
                        $saldoLinea['iva_comisiones']+=$iva_comisiones_periodo+$iva_comisiones_periodo_anterior;
                        $saldoPagar['comisiones']+=$comisiones_periodo+$comisiones_periodo_anterior;
                        $saldoPagar['iva_comisiones']+=$iva_comisiones_periodo+$iva_comisiones_periodo_anterior;
                        */
						$detallesPeriodo[$keyDetallePeriodo]['iva']+=$importeComisionesIVA;
                    }
					$interes_generado=0;
					$iva_interes_generado=0;
                }
                if(isset($detallesPeriodo[$keyDetallePeriodo])){
                    //Si se agrego la amortización se suma 1 al key
                    $keyDetallePeriodo++;
                }
            }
        }
        //PAGOS
        foreach ($amortizacionesAll as $keyP=>$amortizaciones) {
            if($keyP==0 || $solicitud->id_producto0->tabla_disposiciones!="unica"){
                foreach ($amortizaciones as $keyA => $amortizacion) {
					if ($amortizacion->status == "Quebrantado"  && count($amortizacion->rel_parcialidades) == 0) {
						continue;
					}

                    if(isset($amortizacion->fecha_pago) && $amortizacion->fecha_pago!=0){
                        $fecha_pago=date("Y-m-d",strtotime($amortizacion->fecha_pago));
                    }else{
                        $fecha_pago=0;
                    }

                    $importeComisiones=0;
                    $importeComisionesIVA=0;
                    foreach ($infoComisiones[$amortizacion->id] as $keyC => $comision) {
                        $importeComisiones+=$comision["monto"];
                        $importeComisionesIVA+=$comision["iva"];
                    }

                    $parcialidades=ParcialidadesAmortizaciones::model()->findAll(array("condition"=>" (status='Registrado' OR status='Reestructurado') AND id_amortizacion=:amortizacion AND CAST(fecha_pago AS DATE)<=:fecha","order"=>"fecha_pago ASC","params"=>array(':amortizacion'=>$amortizacion->id,':fecha'=>$fecha2)));



                    //Si tiene parcialidades se toma en cuenta la parcialidad
                    if(count($parcialidades)>0){
                        $capitalPagado=0;
                        $ivaCapitalPagado=0;
                        $interesPagado=0;
                        $ivaInteresPagado=0;
                        $moraPagado=0;
                        $ivaMoraPagado=0;
                        $comisionesPagado=0;
                        $ivaComisionesPagado=0;
                        $seguroPagado=0;
                        $comisionesPagadoT=0;
                        $ivaComisionesPagadoT=0;

                        $capitalPagadoAnterior=0;
                        $ivaCapitalPagadoAnterior=0;
                        $interesPagadoAnterior=0;
                        $ivaInteresPagadoAnterior=0;
                        $moraPagadoAnterior=0;
                        $ivaMoraPagadoAnterior=0;
                        $comisionesPagadoAnterior=0;
                        $ivaComisionesPagadoAnterior=0;

                        $interes_pagado_nuevo=0;
                        $pago_moratorio_nuevo=0;
                        $iva_moratorio_nuevo=0;
                        $c=0;
                        foreach ($parcialidades as $parcialidad) {
                            $c++;
                            // $parciales=Amortizaciones::model()->findAll("id=:id",array(':id'=>$parcialidad->id_amortizacion));
                            $comisionesPagado=0;
                            $parcialidadAnterior=false;

                            foreach ($parcialidad as $key=>$value) {

                                if($key=="pago_interes" && $parcialidad->status!="Condonado")
                                {
                                    $interes_pagado_nuevo+=$value;
                                }
                                if($key=="pago_moratorios" && $parcialidad->status!="Condonado")
                                {
                                    $pago_moratorio_nuevo+=$value;
                                }

                                if($key=="iva_capital" || $key=="iva_interes" && $parcialidad->status!="Condonado")
                                {
                                    $iva_moratorio_nuevo+=$value;
                                }
                            }



                            $fecha_pago_parcialidad=date("Y-m-d",strtotime($parcialidad->fecha_pago));
                            if(strtotime($fecha_pago_parcialidad)>strtotime($fecha2)){
                                //Si la fecha de pago es despues de la fecha2 no se toma en cuenta
                                continue;
                            }
                            //Si la fecha de pago es mayor o igual a la fecha1 la parcialidad es del periodo
                            if(strtotime($fecha_pago_parcialidad)>=strtotime($fecha1)){
                                $capitalPagado+=$parcialidad->pago_capital;
                                $ivaCapitalPagado+=$parcialidad->iva_capital;



                                if($amortizacion->pago_interes!=0){
                                    $interesPagado+=$parcialidad->pago_interes*(($amortizacion->pago_interes-$amortizacion->iva_interes_generado)/$amortizacion->pago_interes);
                                    $ivaInteresPagado+=$parcialidad->pago_interes*(($amortizacion->iva_interes_generado)/$amortizacion->pago_interes);
                                    $ivaInteresPagadoP=$parcialidad->pago_interes*(($amortizacion->iva_interes_generado)/$amortizacion->pago_interes);;
                                }
                                if($amortizacion->pago_moratorios>0){
                                    $moraPagado+=$parcialidad->pago_moratorios*(($amortizacion->pago_moratorios-$amortizacion->iva_moratorios)/$amortizacion->pago_moratorios);
                                    $ivaMoraPagado+=$parcialidad->pago_moratorios*(($amortizacion->iva_moratorios)/$amortizacion->pago_moratorios);
                                    $ivaInteresPagadoP=$parcialidad->pago_moratorios*(($amortizacion->iva_moratorios)/$amortizacion->pago_moratorios);
                                }
                                if($parcialidad->pago_comisiones>0){
                                    $importeComisionesT=$importeComisiones;
                                    $importeComisionesIVAT=$importeComisionesIVA;
                                    foreach($infoComisiones[$amortizacion->id] as $keyC => $comision){
                                        if($comision["descontar"]==1){
                                            $idComision=$comision["id"];
                                            if(!is_null($idComision) AND $idComision !=""){
												$parcialidadT=ParcialidadesAmortizaciones::model()->find("id_comision_descontada=$idComision");
											   if(isset($parcialidadT)){
												   $importeComisionesT-=$comision["monto"];
												   $importeComisionesIVAT-=$comision["iva"];
												   $comisionesPagadoT=$parcialidad->pago_comisiones*($importeComisiones/($importeComisiones+$importeComisionesIVA));
												   $ivaComisionesPagadoT=$parcialidad->pago_comisiones*($importeComisionesIVA/($importeComisiones+$importeComisionesIVA));
											   }
										   }  
                                        }
                                    }
                                    $comisionesPagado=$parcialidad->pago_comisiones*($importeComisionesT/($importeComisionesT+$importeComisionesIVAT));
                                    $ivaComisionesPagado=$parcialidad->pago_comisiones*($importeComisionesIVAT/($importeComisionesT+$importeComisionesIVAT));
                                    $comisionesPagadoT=$parcialidad->pago_comisiones*($importeComisiones/($importeComisiones+$importeComisionesIVA));
                                    $ivaComisionesPagadoT=$parcialidad->pago_comisiones*($importeComisionesIVA/($importeComisiones+$importeComisionesIVA));
                                }
                                if($parcialidad->pago_seguro>0){
                                    $seguroPagado=$parcialidad->pago_seguro;
                                }
                            }else{
                                //Si la fecha de pago es menor a la fecha1 la parcialidad es anterior al periodo
                                $capitalPagadoAnterior+=$parcialidad->pago_capital;
                                $ivaCapitalPagadoAnterior+=$parcialidad->iva_capital;
                                if($amortizacion->pago_interes!=0){
                                    $interesPagadoAnterior+=$parcialidad->pago_interes*(($amortizacion->pago_interes-$amortizacion->iva_interes_generado)/$amortizacion->pago_interes);

                                    $ivaInteresPagadoAnterior+=$parcialidad->pago_interes*(($amortizacion->iva_interes_generado)/$amortizacion->pago_interes);
                                }
                                if($amortizacion->pago_moratorios>0){
									$moraPagadoAnterior+=$parcialidad->pago_moratorios*(($amortizacion->pago_moratorios-$amortizacion->iva_moratorios)/$amortizacion->pago_moratorios);

                                    $ivaMoraPagadoAnterior+=$parcialidad->pago_moratorios*(($amortizacion->iva_moratorios)/$amortizacion->pago_moratorios);
                                }
                                if($parcialidad->pago_comisiones>0){
                                    $comisionesPagadoAnterior+=$parcialidad->pago_comisiones*($importeComisiones/($importeComisiones+$importeComisionesIVA));
                                    $ivaComisionesPagadoAnterior+=$parcialidad->pago_comisiones*($importeComisionesIVA/($importeComisiones+$importeComisionesIVA));
                                }
                                if(strtotime($amortizacion->fecha_amortizacion)>=strtotime($fecha1)){
                                    $parcialidadAnterior=true;
                                }
                            }
                            if($solicitud->intereses_visibles==1){
                                $detallesPeriodo[$keyDetallePeriodo.$c]['operacion']='pago';
                                $detallesPeriodo[$keyDetallePeriodo.$c]['solicitud']=$model->id;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['disposicion']=$amortizacion->id_disposicion;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['clave_disposicion']=($keyP==0?"S_".$model->clave:"D_".$amortizacion->id_disposicion0->clave);
                                $detallesPeriodo[$keyDetallePeriodo.$c]['fecha_disposicion']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo.$c]['fecha_vencimiento']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo.$c]['fecha_ultima_amortizacion']=($keyP==0?$model->fecha_ultimo_vencimiento:$amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                                $detallesPeriodo[$keyDetallePeriodo.$c]['plazo']=($keyP==0?$model->plazo_autorizado:$amortizacion->id_disposicion0->plazo);
                                $detallesPeriodo[$keyDetallePeriodo.$c]['fecha']=$parcialidad->fecha_pago;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['concepto']="Parcialidad {$amortizacion->numero_amortizacion}";
                                $detallesPeriodo[$keyDetallePeriodo.$c]['cargo']+=0;
                                //$detallesPeriodo[$keyDetallePeriodo.$c]['abono']+=$parcialidad->pago_capital+$parcialidad->pago_interes+$comisionesPagado+$parcialidad->pago_moratorios+$ivaInteresPagadoP+$ivaMoraPagadoP;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['abono']+=$parcialidad->pago_capital+$parcialidad->pago_interes+$comisionesPagado+$parcialidad->pago_moratorios;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['capital']+=$parcialidad->pago_capital;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['interes']+=$parcialidad->pago_interes-$ivaInteresPagadoP;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['mora']+=$parcialidad->pago_moratorios-$ivaMoraPagadoP;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['mora']+=0;
                                $detallesPeriodo[$keyDetallePeriodo.$c]['iva']+=$ivaInteresPagadoP+$ivaMoraPagadoP;
                            }
                            $importeComisionesT=$importeComisiones;
                            foreach($infoComisiones[$amortizacion->id] as $keyC => $comision){
                                if($comision["descontar"]==1){
                                    $idComision=$comision["id"];
                                    if(!is_null($idComision) AND $idComision !=""){
										$parcialidadT=ParcialidadesAmortizaciones::model()->find("id_comision_descontada=$idComision");
										if(isset($parcialidadT)){
											$importeComisionesT-=$comision["monto"];
										}
									}
                                }
                            }
                            foreach ($infoComisiones[$amortizacion->id] as $keyC => $comision) {
                                if(isset($parcialidad->id_comision_descontada)){
                                    if($parcialidad->id_comision_descontada==$comision['id']){
                                        if($comisionesPagado>0){
                                            $comisionesPeriodo[]=array(
                                                'fecha'=>$parcialidad->fecha_pago,
                                                'concepto'=>"Parcialidad ".$comision['comision'],
                                                'monto'=>$comision['monto'],
                                                'iva'=>$comision['iva'],
                                                'moneda'=>'MXN',
                                            );
                                        }
                                    }
                                }else{
                                    $idComision=$comision["id"];
                                    if(!is_null($idComision) AND $idComision !=""){
										$parcialidadT=ParcialidadesAmortizaciones::model()->find("id_comision_descontada=$idComision");
										if($comision["descontar"]==0 || !isset($parcialidadT)){
											if($comisionesPagado>0){
												$comisionesPeriodo[]=array(
													'fecha'=>$parcialidad->fecha_pago,
													'concepto'=>"Parcialidad ".$comision['comision'],
													'monto'=>$comision['monto']*(($comisionesPagado)/($importeComisionesT)),
													'iva'=>$comision['iva']*(($comisionesPagado)/($importeComisionesT)),
													'moneda'=>'MXN',
												);
											}
										}
									}   
                                }
                            }

                            if($capitalPagado+$interesPagado+$comisionesPagado+$moraPagado+$ivaInteresPagado+$ivaMoraPagado+$seguroPagado>0.01 || $parcialidadAnterior){
                                if($solicitud->intereses_visibles!=1){
                                    $ivaInteresP=$parcialidad->pago_interes-($parcialidad->pago_interes/(1+$solicitud->id_impuesto0->porcentaje));
                                    $comisionModel=ComisionesAmortizaciones::model()->find("id_amortizacion=$amortizacion->id");
                                    if(!is_null($comisionModel)){
                                        $ivaComisionesP=$parcialidad->pago_comisiones-($parcialidad->pago_comisiones/(1+$comisionModel->id_impuesto0->porcentaje));
                                    }else{
                                        $ivaComisionesP=0;
                                    }
                                    $ivaMoraP=$parcialidad->pago_moratorios-($parcialidad->pago_moratorios/(1+$solicitud->id_impuesto_moratorios0->porcentaje));
                                    $detallesPeriodo[$keyDetallePeriodo]['operacion']='pago';
                                    $detallesPeriodo[$keyDetallePeriodo]['solicitud']=$model->id;
                                    $detallesPeriodo[$keyDetallePeriodo]['disposicion']=($keyP==0?null:$amortizacion->id_disposicion);
                                    $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion']=($keyP==0?"S_".$model->clave:"D_".$amortizacion->id_disposicion0->clave);
                                    $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                    $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                    $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion']=($keyP==0?$model->fecha_ultimo_vencimiento:$amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                                    $detallesPeriodo[$keyDetallePeriodo]['plazo']=($keyP==0?$model->plazo_autorizado:$amortizacion->id_disposicion0->plazo);
                                    $detallesPeriodo[$keyDetallePeriodo]['fecha']=$parcialidad->fecha_pago;
                                    $detallesPeriodo[$keyDetallePeriodo]['concepto']="Parcialidad {$amortizacion->numero_amortizacion}";
                                    $detallesPeriodo[$keyDetallePeriodo]['cargo']+=0;
                                    /*$detallesPeriodo[$keyDetallePeriodo]['abono']+=$capitalPagado+$interesPagado+$comisionesPagado+$moraPagado+$ivaInteresPagado+$ivaMoraPagado+$ivaComisionesPagado;
                                    $detallesPeriodo[$keyDetallePeriodo]['capital']+=$capitalPagado;
                                    $detallesPeriodo[$keyDetallePeriodo]['interes']+=$interesPagado;
                                    $detallesPeriodo[$keyDetallePeriodo]['mora']+=$moraPagado;
                                    $detallesPeriodo[$keyDetallePeriodo]['mora']+=$seguroPagado;
                                    $detallesPeriodo[$keyDetallePeriodo]['iva']+=$ivaInteresPagado+$ivaMoraPagado+$ivaComisionesPagado;*/
                                    $detallesPeriodo[$keyDetallePeriodo]['abono']+=$parcialidad->pago_capital+$parcialidad->pago_interes+$parcialidad->pago_moratorios+$parcialidad->pago_comisiones;//$parcialidad->pago_otros_gastos
                                    $detallesPeriodo[$keyDetallePeriodo]['capital']+=$parcialidad->pago_capital;
                                    $detallesPeriodo[$keyDetallePeriodo]['comision']+=$parcialidad->pago_comisiones-$ivaComisionesP;
                                    $detallesPeriodo[$keyDetallePeriodo]['mora']+=$parcialidad->pago_moratorios-$ivaMoraP;
                                    $detallesPeriodo[$keyDetallePeriodo]['iva']+=$ivaInteresP+$ivaComisionesP+$ivaMoraP+$parcialidad->iva_capital;
                                }

                                $keyDetallePeriodo++;
                            }
                        }
                        //Nota: El capital pagado es de toda la historia y los demas conceptos pagados es del periodo
                        //Es un tema que surgio por FORTRADE.



                        if( $interes_pagado_nuevo!=0)
                        {
                            //$saldoLinea['interes_pagado']+=$interes_pagado_nuevo;
                            $interesP=$interes_pagado_nuevo/(1+($solicitud->id_impuesto0->porcentaje));
                            $ivaP=$interes_pagado_nuevo-$interesP;
                            $saldoLinea['interes_pagado']+=$interesP;
							$fecha_pago_cap = date('Y-m-d 00:00:00', strtotime($amortizacion->fecha_pago));//quitamos horas por cuestiones de el strtotime ya que aunque se pague
							//el mismo dia si las horas son distintas arroja un adeudo
                        }

                        if( $pago_moratorio_nuevo!=0)
                        {
                            $saldoLinea['mora_pagado']+=$pago_moratorio_nuevo;
                        }
                        else{
                            $saldoLinea['mora_pagado']+=$moraPagado;
                        }
                        if( $iva_moratorio_nuevo!=0)
                        {
                            $saldoLinea['iva_pagado']+=$iva_moratorio_nuevo;
                        }
                        else{
                            $saldoLinea['iva_pagado']+=$ivaMoraPagado;
                        }


                        $saldoLinea['capital_pagado']+=$capitalPagado+$capitalPagadoAnterior;
                        $saldoLinea['iva_interes_pagado']+=$ivaInteresPagado;
                        $saldoLinea['comisiones_pagado']+=$comisionesPagado;
                        $saldoLinea['iva_comisiones_pagado']+=$ivaComisionesPagado;
                        // $saldoLinea['mora_pagado']+=$moraPagado;
                        $saldoLinea['iva_mora_pagado']+=$ivaMoraPagado;


                        //Se resta el capital pagado del saldo de linea|saldo pagar, esto siempre esa asi ya que el capital se toma lo dispuesto
                        //y no lo vencido
                        $saldoLinea['capital']-=$capitalPagado+$capitalPagadoAnterior;
                        $saldoPagar['capital']-=$capitalPagado+$capitalPagadoAnterior;
                        //Solo se restan del saldo de linea|saldo pagar las amortizaciones no pagadas
                        if(
							!isset($fecha_pago) 
							|| $fecha_pago==0 
							|| strtotime($fecha_pago)>strtotime($fecha2)
						){
                            $saldoLinea['interes']-=$interesPagado+$interesPagadoAnterior;
                            $saldoLinea['iva_interes']-=$ivaInteresPagado+$ivaInteresPagadoAnterior;
                            $saldoLinea['renta'] -= $interesPagado+$interesPagadoAnterior;
                            $saldoLinea['iva_renta'] -= $ivaInteresPagado+$ivaInteresPagadoAnterior;
                            $saldoLinea['renta'] -= $capitalPagado+$capitalPagadoAnterior;
                            $saldoLinea['iva_renta'] -= $ivaCapitalPagado+$ivaCapitalPagadoAnterior;
                            $saldoLinea['mora']-=$moraPagado+$moraPagadoAnterior;
                            $saldoLinea['iva_mora']-=$ivaMoraPagado+$ivaMoraPagadoAnterior;

                            $saldoPagar['interes']-=$interesPagado+$interesPagadoAnterior;
                            $saldoPagar['iva_interes']-=$ivaInteresPagado+$ivaInteresPagadoAnterior;
                            $saldoPagar['mora']-=$moraPagado+$moraPagadoAnterior;
                            $saldoPagar['iva_mora']-=$ivaMoraPagado+$ivaMoraPagadoAnterior;

                            //Para el caso de las comisiones es necesario que la fecha de vencimiento se encuentre en el periodo para que no de negativos al pagar antes.
                            /*
                                 Vencimiento ------------------------------|-----|--------
                                                                           VA    PA
                                    Comision -------|----------------------|--------------
                                                    PC                     VC
                            Estado de Cuenta -------------|-----------|-------------------
                                                          I           F
                            */
                            if(strtotime($amortizacion->fecha_amortizacion)<=strtotime($fecha2)){
                                $saldoLinea['comisiones']-=$comisionesPagado+$comisionesPagadoAnterior;
                                $saldoLinea['iva_comisiones']-=$ivaComisionesPagado+$ivaComisionesPagadoAnterior;

                                $saldoPagar['comisiones']-=$comisionesPagado+$comisionesPagadoAnterior;
                                $saldoPagar['iva_comisiones']-=$ivaComisionesPagado+$ivaComisionesPagadoAnterior;
                            }
                        }
                        //Si se paga antes de la fecha1 es parte del saldo anterior
                        if(
							isset($fecha_pago) 
							&& $fecha_pago!=0 
							&& strtotime($fecha_pago)<strtotime($fecha1)
						){
                            //Se suma el importe de capital al saldo anterior
                            $resumenPeriodo['capital']['saldo_anterior']-=$capitalPagadoAnterior;
                            //Se suma cero al saldo anterior porque ya esta pagado
                            $resumenPeriodo['interes']['saldo_anterior']-=0;//$interesPagadoAnterior;
                            $resumenPeriodo['mora']['saldo_anterior']-=0;//$moraPagadoAnterior;
                            $resumenPeriodo['iva_interes']['saldo_anterior']-=0;//$ivaInteresPagadoAnterior;
                            $resumenPeriodo['iva_mora']['saldo_anterior']-=0;//$ivaMoraPagadoAnterior;
                        } elseif (
							isset($fecha_pago) 
							&& $fecha_pago!=0 
							&& strtotime($fecha_pago)>=strtotime($fecha1) 
							&& strtotime($fecha_pago)<=strtotime($fecha2)
						){
                            //Si se paga despues de la fecha1 y hasta la fecha2 el pago es parte del periodo
							$resumenPeriodo['capital']['saldo_anterior']-=$capitalPagadoAnterior;
                            $resumenPeriodo['capital']['abonos']+=$capitalPagado;
							}
                            $resumenPeriodo['mora']['abonos']+=$moraPagado;
                            $resumenPeriodo['iva_interes']['abonos']+=$ivaInteresPagado;
                            $resumenPeriodo['iva_mora']['abonos']+=$ivaMoraPagado;
                            $ivaComisiones=0;
                            $comisionModel=ComisionesAmortizaciones::model()->find("id_amortizacion=$amortizacion->id");
                            if(!is_null($comisionModel)){
                                $ivaComisiones=$amortizacion->pago_comisiones-($parcialidad->pago_comisiones/(1+$comisionModel->id_impuesto0->porcentaje));
                            }
                            $resumenPeriodo['comision']['abonos'] += $comisionesPagadoT;
                            $resumenPeriodo['iva_comision']['abonos'] += $ivaComisionesT;
                            if($solicitud->id_producto0->id_tipo_producto==8){
                                $resumenPeriodo['interes']['abonos'] += $capitalPagado;
                                $resumenPeriodo['iva_interes']['abonos'] += $ivaCapitalPagado;
                            }
                        }else{
                            //Si no se ha pagado es parte del periodo
                            $resumenPeriodo['capital']['abonos']+=$capitalPagado;
                            $resumenPeriodo['mora']['abonos']+=$moraPagado;
                            $resumenPeriodo['iva_interes']['abonos']+=$ivaInteresPagado;
                            $resumenPeriodo['iva_mora']['abonos']+=$ivaMoraPagado;

                            //Se suman los importes al saldo anterior ya que aun no se ha pagado la amortizacion
                            $resumenPeriodo['capital']['saldo_anterior']-=$capitalPagadoAnterior;
                            $resumenPeriodo['interes']['saldo_anterior']-=$interesPagadoAnterior;
                            if($resumenPeriodo['interes']['saldo_anterior']<0){
                                $resumenPeriodo['interes']['saldo_anterior']=0;
                            }
                            $resumenPeriodo['mora']['saldo_anterior']-=$moraPagadoAnterior;
                            $resumenPeriodo['iva_interes']['saldo_anterior']-=$ivaInteresPagadoAnterior;
                            $resumenPeriodo['iva_mora']['saldo_anterior']-=$ivaMoraPagadoAnterior;
                        }


                    }else{
                        //Si la amortizacion no tiene parcialidades
                        //Solo se toman en cuenta los pagos hasta la fecha2
                        if(isset($fecha_pago) && $fecha_pago!=0 && strtotime($fecha_pago)<=strtotime($fecha2)){
                            //Se resta el capital al saldo de linea de capital
                            $saldoLinea['capital']-=$amortizacion->pago_capital;
                            //Lo pagado no se agrega al saldo de linea
                            if(isset($fecha_pago) && $fecha_pago!=0 && strtotime($fecha_pago)<=strtotime($fecha2)){
                                $saldoLinea['interes']-=0;
                                $saldoLinea['iva_interes']-=0;
                                $saldoLinea['comisiones']-=0;
                                $saldoLinea['iva_comisiones']-=0;
                                $saldoLinea['mora']-=0;
                                $saldoLinea['iva_mora']-=0;
                            }

                            //Lo pagado se agrega dentro de los pagados del saldo de linea
                            $saldoLinea['capital_pagado']+=$amortizacion->pago_capital;
                            $saldoLinea['iva_interes_pagado']+=$amortizacion->iva_interes_generado;
                            $saldoLinea['comisiones_pagado']+=$amortizacion->pago_comisiones*(1-$amortizacion->factor_iva_comisiones);
                            $saldoLinea['iva_comisiones_pagado']+=$amortizacion->pago_comisiones*($amortizacion->factor_iva_comisiones);
                            $saldoLinea['mora_pagado']+=$amortizacion->pago_moratorios-$amortizacion->iva_moratorios;
                            $saldoLinea['iva_mora_pagado']+=$amortizacion->iva_moratorios;
                            //    $saldoLinea['iva_pagado']+=$amortizacion->iva_interes_generado+$amortizacion->iva_moratorios+$amortizacion->pago_comisiones*($amortizacion->factor_iva_comisiones);

                            $saldoLinea['iva_pagado']+=$amortizacion->iva_moratorios+$amortizacion->iva_interes_generado;


                            //Se resta el capital al saldo a pagar de capital
                            $saldoPagar['capital']-=$amortizacion->pago_capital;
                            //Lo pagado no se agrega al saldo a pagar
                            if(isset($fecha_pago) && $fecha_pago!=0 && strtotime($fecha_pago)<=strtotime($fecha2)){
                                $saldoPagar['interes']-=0;
                                $saldoPagar['iva_interes']-=0;
                                $saldoPagar['comisiones']-=0;
                                $saldoPagar['iva_comisiones']-=0;
                                $saldoPagar['mora']-=0;
                                $saldoPagar['iva_mora']-=0;
                            }

                            if($amortizacion->pago_comisiones>0){
                                $comisionesPagado=$amortizacion->pago_comisiones*($importeComisiones/($importeComisiones+$importeComisionesIVA));
                                $ivaComisionesPagado=$amortizacion->pago_comisiones*($importeComisionesIVA/($importeComisiones+$importeComisionesIVA));
                            }

                            //Solo se agregan los pagos del periodo a los abonos del resumen del periodo
                            if(
								isset($fecha_pago) 
								&& $fecha_pago!=0 
								&& strtotime($fecha_pago)>=strtotime($fecha1)
								|| ($amortizacion->fecha_amortizacion>=$fecha1 && $amortizacion->fecha_amortizacion<=$fecha2)
							) {
                                $resumenPeriodo['capital']['abonos']+=$amortizacion->pago_capital;
                                if($solicitud->id_producto0->id_tipo_producto==8){
                                    $resumenPeriodo['interes']['abonos'] += $amortizacion->pago_capital;
                                    $resumenPeriodo['iva_interes']['abonos'] += $amortizacion->iva_capital;
                                }
                                $resumenPeriodo['mora']['abonos']+=$amortizacion->pago_moratorios-$amortizacion->iva_moratorios;
                                $resumenPeriodo['iva_interes']['abonos']+=$amortizacion->iva_interes_generado;
                                $resumenPeriodo['iva_mora']['abonos']+=$amortizacion->iva_moratorios;
								$importeComisionesIVA=0;
                                //Si la amortizacion se paga en el periodo tambien se agregan las comisiones
                                foreach ($infoComisiones[$amortizacion->id] as $keyC => $comision) {
                                    if($comisionesPagado>0){
                                        $comisionesPeriodo[]=array(
                                            'fecha'=>$amortizacion->fecha_pago,
                                            'concepto'=>"Pago ".$comision['comision'],
                                            'monto'=>$comision['monto'],
                                            'iva'=>$comision['iva'],
                                            'moneda'=>'MXN',
                                        );
                                    }
									$importeComisionesIVA+=$comision['iva'];
                                }

                                $detallesPeriodo[$keyDetallePeriodo]['operacion']='pago';
                                $detallesPeriodo[$keyDetallePeriodo]['solicitud']=$model->id;
                                $detallesPeriodo[$keyDetallePeriodo]['disposicion']=($keyP==0?null:$amortizacion->id_disposicion);
                                $detallesPeriodo[$keyDetallePeriodo]['clave_disposicion']=($keyP==0?"S_".$model->clave:"D_".$amortizacion->id_disposicion0->clave);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_disposicion']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_vencimiento']=($keyP==0?$model->fecha_disposicion:$amortizacion->id_disposicion0->fecha);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha_ultima_amortizacion']=($keyP==0?$model->fecha_ultimo_vencimiento:$amortizacion->id_disposicion0->fecha_ultimo_vencimiento);
                                $detallesPeriodo[$keyDetallePeriodo]['plazo']=($keyP==0?$model->plazo_autorizado:$amortizacion->id_disposicion0->plazo);
                                $detallesPeriodo[$keyDetallePeriodo]['fecha']=$amortizacion->fecha_pago;
                                $detallesPeriodo[$keyDetallePeriodo]['concepto']="Pago {$amortizacion->numero_amortizacion}";
                                $detallesPeriodo[$keyDetallePeriodo]['cargo']+=0;
                                $detallesPeriodo[$keyDetallePeriodo]['abono']+=$amortizacion->pago_capital+$amortizacion->iva_capital+$amortizacion->pago_interes+$amortizacion->pago_comisiones+$amortizacion->pago_moratorios+$amortizacion->pago_seguro+$amortizacion->pago_otros_gastos;
                                $detallesPeriodo[$keyDetallePeriodo]['capital']+=$amortizacion->pago_capital;
                                
                                $detallesPeriodo[$keyDetallePeriodo]['comision']+=$amortizacion->pago_comisiones-$importeComisionesIVA;
                                $detallesPeriodo[$keyDetallePeriodo]['mora']+=$amortizacion->pago_moratorios-$amortizacion->iva_moratorios;
                                $detallesPeriodo[$keyDetallePeriodo]['seguro']+=$amortizacion->pago_seguro;
                                $detallesPeriodo[$keyDetallePeriodo]['iva']+=$amortizacion->iva_interes_generado+$amortizacion->iva_moratorios+$importeComisionesIVA+$amortizacion->iva_capital;
                                $keyDetallePeriodo++;
                            }else{
                                //Se resta el importe de capital cuando el pago es del periodo anterior
                                $resumenPeriodo['capital']['saldo_anterior']-=$amortizacion->pago_capital;
                                //Se resta cero cuando el pago es del periodo anterior
                                $resumenPeriodo['interes']['saldo_anterior']-=0;
                                $resumenPeriodo['mora']['saldo_anterior']-=0;
                                $resumenPeriodo['iva_interes']['saldo_anterior']-=0;
                                $resumenPeriodo['iva_mora']['saldo_anterior']-=0;
                            }
                        }
                    }
					// ——— Condonaciones sobre cargos (unificado) ———

					// 0) Reiniciar variables auxiliares por cada amortización:
					$capitalPagado = $ivaCapitalPagado = 0;
					$interesPagado = $ivaInteresPagado = 0;
					$moraPagado    = $ivaMoraPagado    = 0;
					$comisionesPagado    = $ivaComisionesPagado    = 0;
					$capitalPagadoAnterior = $ivaCapitalPagadoAnterior = 0;
					$interesPagadoAnterior = $ivaInteresPagadoAnterior = 0;
					$moraPagadoAnterior    = $ivaMoraPagadoAnterior    = 0;
					$comisionesPagadoAnterior = $ivaComisionesPagadoAnterior = 0;

					// 1) Recuperar todas las condonaciones hasta la fecha de corte
					$parcialidadesCondonaciones = ParcialidadesAmortizaciones::model()->findAll([
						"condition" => "
							status='Condonado'
							AND id_amortizacion=:amortizacion
							AND CAST(fecha_pago AS DATE)<=:fecha2
						",
						"order"  => "fecha_pago ASC",
						"params" => [
							':amortizacion' => $amortizacion->id,
							':fecha2'       => $fecha2,
						],
					]);

					$moraCondonado = 0;
					// conservar índice inicial de detalles
					$startDetalle = $keyDetallePeriodo;
					$c = 0;

					foreach ($parcialidadesCondonaciones as $parcialidad) {
						// fecha de pago como YYYY-MM-DD
						$fechaPagoParcial = date("Y-m-d", strtotime($parcialidad->fecha_pago));
						// 2) Saltar antes de inicio o después de corte
						if (strtotime($fechaPagoParcial) < strtotime($fecha1) || strtotime($fechaPagoParcial) > strtotime($fecha2)) {
							continue;
						}
						$c++;

						// 3) Restar capital condonado de saldos
						$saldoLinea['capital']               -= $parcialidad->pago_capital;
						$saldoPagar['capital']               -= $parcialidad->pago_capital;
						$resumenPeriodo['capital']['cargos'] -= $parcialidad->pago_capital;

						// 4) Acumular mora condonada
						$moraCondonado += $parcialidad->pago_moratorios;

						// 5) Calcular netos de interés e IVA condonados
						$impMora    = $solicitud->id_impuesto_moratorios0->porcentaje;
						$impInteres = $solicitud->id_impuesto0->porcentaje;
						$moraTotal    = round($parcialidad->pago_moratorios / (1 + $impMora), 2);
						$ivaMoraC     = $parcialidad->pago_moratorios - $moraTotal;
						$interesTotal = round($parcialidad->pago_interes    / (1 + $impInteres), 2);
						$ivaInteresC  = $parcialidad->pago_interes - $interesTotal;

						// 6) Ajustar saldoPagar y resumenPeriodo de intereses
						$saldoPagar['interes']     -= $interesTotal;
						$saldoPagar['iva_interes'] -= $ivaInteresC;
						$resumenPeriodo['interes']['cargos']    -= $interesTotal;
						$resumenPeriodo['iva_interes']['cargos'] -= $ivaInteresC;

						// 7) Ajustar en detallesPeriodo al índice actual
						$idx = $startDetalle + ($c - 1);
						//   (mora)
						if ($moraTotal > $detallesPeriodo[$idx]['mora']) {
							$mAnt   = $detallesPeriodo[$idx]['mora'];
							$ivaAnt = $mAnt * $impMora;
							$detallesPeriodo[$idx]['cargo'] -= ($mAnt + $ivaAnt);
							$detallesPeriodo[$idx]['mora']  = 0;
							$detallesPeriodo[$idx]['iva']   -= $ivaAnt;
						} else {
							$detallesPeriodo[$idx]['cargo'] -= ($moraTotal + $ivaMoraC);
							$detallesPeriodo[$idx]['mora']  -= $moraTotal;
							$detallesPeriodo[$idx]['iva']   -= $ivaMoraC;
						}
						//   (interés)
						if ($interesTotal >= $detallesPeriodo[$idx]['interes']) {
							$iAnt    = $detallesPeriodo[$idx]['interes'];
							$ivaAntI = $iAnt * $impInteres;
							$detallesPeriodo[$idx]['cargo']   -= ($iAnt + $ivaAntI);
							$detallesPeriodo[$idx]['interes'] = 0;
							$detallesPeriodo[$idx]['iva']     -= $ivaAntI;
						} else {
							$detallesPeriodo[$idx]['cargo']   -= ($interesTotal + $ivaInteresC);
							$detallesPeriodo[$idx]['interes'] -= $interesTotal;
							$detallesPeriodo[$idx]['iva']     -= $ivaInteresC;
						}
						//   evitar negativos
						foreach (['cargo','mora','interes','iva'] as $campo) {
							if ($detallesPeriodo[$idx][$campo] < 0) {
								$detallesPeriodo[$idx][$campo] = 0;
							}
						}

						// 8) Distribuir condonación en pagos dentro/fuera del periodo
						if (strtotime($fechaPagoParcial) >= strtotime($fecha1)) {
							// dentro del periodo
							$capitalPagado += $parcialidad->pago_capital;
							if ($amortizacion->pago_interes > 0) {
								$interesPagado    += $interesTotal;
								$ivaInteresPagado += $ivaInteresC;
							}
							if ($amortizacion->pago_moratorios > 0) {
								$moraPagado    += $moraTotal;
								$ivaMoraPagado += $ivaMoraC;
							}
							if ($parcialidad->pago_comisiones > 0) {
								$comisionesPagado    += $parcialidad->pago_comisiones * ($importeComisiones / ($importeComisiones + $importeComisionesIVA));
								$ivaComisionesPagado += $parcialidad->pago_comisiones - ($parcialidad->pago_comisiones * ($importeComisiones / ($importeComisiones + $importeComisionesIVA)));
							}
							if ($parcialidad->pago_seguro > 0) {
								$seguroPagado = $parcialidad->pago_seguro;
							}
						} else {
							// antes del periodo
							$capitalPagadoAnterior += $parcialidad->pago_capital;
							if ($amortizacion->pago_interes > 0) {
								$interesPagadoAnterior    += $interesTotal;
								$ivaInteresPagadoAnterior += $ivaInteresC;
							}
							if ($amortizacion->pago_moratorios > 0) {
								$moraPagadoAnterior    += $moraTotal;
								$ivaMoraPagadoAnterior += $ivaMoraC;
							}
							if ($parcialidad->pago_comisiones > 0) {
								$comisionesPagadoAnterior    += $parcialidad->pago_comisiones * ($importeComisiones / ($importeComisiones + $importeComisionesIVA));
								$ivaComisionesPagadoAnterior += $parcialidad->pago_comisiones - ($parcialidad->pago_comisiones * ($importeComisiones / ($importeComisiones + $importeComisionesIVA)));
							}
						}

						// 9) Detalle de condonación en el periodo
						if (strtotime($fechaPagoParcial) >= strtotime($fecha1)) {
							$ivaMoraC = $parcialidad->pago_moratorios - ($parcialidad->pago_moratorios / (1 + $impMora));
							$comModel = ComisionesAmortizaciones::model()->find("id_amortizacion={$amortizacion->id}");
							$ivaComC  = $comModel
								? $parcialidad->pago_comisiones - ($parcialidad->pago_comisiones / (1 + $comModel->id_impuesto0->porcentaje))
								: 0;

							$detallesPeriodo[$startDetalle + $c] = [
								'operacion'        => 'condonacion',
								'solicitud'        => $model->id,
								'disposicion'      => $amortizacion->id_disposicion,
								'clave_disposicion'=> $keyP==0
													? "S_{$model->clave}"
													: "D_{$amortizacion->id_disposicion0->clave}",
								'fecha'            => $parcialidad->fecha_pago,
								'concepto'         => "Condonación {$amortizacion->numero_amortizacion}",
								'cargo'            => 0,
								'abono'            => $parcialidad->pago_capital
													+ $parcialidad->pago_interes
													+ $parcialidad->pago_comisiones
													+ $parcialidad->pago_moratorios,
								'capital'          => $parcialidad->pago_capital,
								'interes'          => $interesTotal,
								'comision'         => $parcialidad->pago_comisiones - $ivaComC,
								'mora'             => $moraTotal - $ivaMoraC,
								'iva'              => $ivaInteresC + $ivaMoraC + $ivaComC,
							];
						}
					}

					// avanzar el índice global
					$keyDetallePeriodo = $startDetalle + $c;

					// 10) Ajuste final del resumen de mora
					if (($resumenPeriodo['mora']['saldo_anterior']
						+ $resumenPeriodo['iva_mora']['saldo_anterior']) > 0) {
						if (($resumenPeriodo['mora']['saldo_anterior']
							+ $resumenPeriodo['iva_mora']['saldo_anterior']) >= $moraCondonado) {
							$resumenPeriodo['mora']['saldo_anterior']    -= $moraCondonado / (1 + $solicitud->id_impuesto0->porcentaje);
							$resumenPeriodo['iva_mora']['saldo_anterior'] -= $moraCondonado
								- ($moraCondonado / (1 + $solicitud->id_impuesto0->porcentaje));
						} else {
							$diffM = $moraCondonado
								- ($resumenPeriodo['mora']['saldo_anterior']
								+ $resumenPeriodo['iva_mora']['saldo_anterior']);
							$resumenPeriodo['mora']['cargos']     -= $diffM / (1 + $solicitud->id_impuesto0->porcentaje);
							$resumenPeriodo['iva_mora']['cargos'] -= $diffM
								- ($diffM / (1 + $solicitud->id_impuesto0->porcentaje));
							$resumenPeriodo['mora']['saldo_anterior']    = 0;
							$resumenPeriodo['iva_mora']['saldo_anterior'] = 0;
						}
					} else {
						$resumenPeriodo['mora']['cargos']     -= $moraCondonado / (1 + $solicitud->id_impuesto0->porcentaje);
						$resumenPeriodo['iva_mora']['cargos'] -= $moraCondonado
							- ($moraCondonado / (1 + $solicitud->id_impuesto0->porcentaje));
					}


                }
            }
        }
		//die;
        $data=array(
            'saldo_linea_nuevo'=>$saldoLineaCalculo,
            'saldo_linea_nuevo_anterior'=>$saldoLineaCalculoAnterior,
            'saldo_linea'=>$saldoLinea,
            'saldo_pagar'=>$saldoPagar,
            'resumen_periodo'=>$resumenPeriodo,
            'comisiones_periodo'=>$comisionesPeriodo,
            'detalles_periodo'=>$detallesPeriodo,
        );

		

        return $data;
    }