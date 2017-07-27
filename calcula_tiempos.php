<?php

    define('CLI_SCRIPT', true);
    require_once(dirname(__FILE__) . '/../../config.php');
    require_once('lib.php');

    $tbl_name = "block_calam_usertime";

    echo "Se inicia con el proceso de cálculo de tiempos por usuario\nEl proceso puede tardar unos minutos...\n";

    /*cálculo de los parametros con los que inicia el script*/

    //Variable offset que contiene la diferencia del GMT para ser aplicada en las variables tipo date
    $offset = (int)date("Z");

    //verificar que existan datos en la tabla 'unam_stats_usertime'.
    $exist_data = check_table_data($tbl_name);

    if (!$exist_data){      
        $start_day = (int)$CFG->start_day;
        $today_format = get_today_dmy();
        $yesterday = get_yesterday_timestamp($today_format, $offset);
    }
    else {
        //Se verifica cuál es el último día calculado.
        $last_day = (int)calculated_last_day($tbl_name);
        // Se adecua al GMT
        $last_day -= $offset;
      
        //Se genera el String en formato d-m-Y
        $last_day_format = date('d-m-Y', $last_day);
      
        //Se obtiene el día siguiente al último calculado, llamado "next_day"      
        //Se toma como referencia el último día y se le suman los segundos para general el "siguiente día"
        $start_day = $last_day + 86400; 
      
        //Se genera el String en formato d-m-Y
        $next_day_format = date('d-m-Y', $start_day);        

        //Se obtiene el día actual en formato 'd-m-Y'
        $today_format = get_today_dmy();

        //Se obtiene el día anterior al presente
        $yesterday = get_yesterday_timestamp($today_format, $offset);

        if($next_day_format == $today_format)
        {
            error("No se calcula el día actual");
            die();
        }
        //Se adecua la hora con GMT
        $start_day += $offset;      
    }

var_dump($start_day);
var_dump($yesterday);

//die();

    //Se obtiene un arreglo con el id, firstname y lastname de los usuarios definidos por el administrador para ser calculados {$CFG->role_calculate}
    $arr_users = get_users_id();

    //Se obtiene el valor de la ventana de tiempo seleccionada por el administrador {$CFG->time_window}
    $window = (int)$CFG->time_window;    

    //Se realiza un arreglo con los días del período de cálculo, en este caso el día de inicio dado por el administrador y el día de fin es el día de ayer
    $arr_days = get_days_in_a_period($start_day, $yesterday);

//die();    
      
    //El contador de días obtenidos
    $n_days = count($arr_days);

    //El procedimiento de calculo e inserción en la base de datos
    foreach($arr_users as $this_user){
        $elements = get_count_user_hits($start_day, $yesterday + 86399, $this_user->id);
        if($elements->count > 0){
            for ($i = 0; $i < $n_days; $i++){
                $user_logs = consulta_logs_usuario($this_user->id, $arr_days[$i], $arr_days[$i] + 86399 );
                $tiempos = computo_tiempo_curso($user_logs, $window);

                foreach( $tiempos as $id_curso=>$tiempo_curso){
                    if($tiempo_curso["tiempo"] > 0 || $tiempo_curso["hits"] > 0)
                        insert_unam_stats_usertime_course($this_user->id,  $arr_days[$i],  $arr_days[$i] + 86399, $tiempo_curso, $id_curso);
                }
            }              
        }
    }
    echo "El proceso de cálculo de tiempos por usuario ha concluido satisfactoriamente";


/*    
    $time_fin=_microtime();
    $memo_fin=memory_get_peak_usage();    
    $memoria_final = memory_get_usage();
    echo '
    Memoria final: ' . $memoria_final . '
    
    memo ini: '. $memo_fin .'

    ';


    

echo "
Usados un máximo de: ".round(($memo_fin - $memo_ini)/(1024*1024),2). "Mb

";
*/
/*    
    $memoria_inicial = memory_get_usage();
    $memo_ini=memory_get_peak_usage();    
    $time_ini=_microtime();
    echo 'Memoria inicial: ' . $memoria_inicial . '
    memo ini: '. $memo_ini .'
    ';
    
*/    

?>
