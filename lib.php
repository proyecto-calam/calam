<?php

defined('MOODLE_INTERNAL') || die();


/**
Esta función Obtiene todos los registros del logstore_standard_log en un determinado lapso de tiempo y para un usuario determinado

    $idusuario	bigint
    $f_inicio	bigint    fecha en formato timestamp
    $f_fin	bigint    fecha en formato timestamp
*/
function consulta_logs_usuario($idusuario, $f_inicio, $f_fin){
    global $DB;
    global $CFG;
    $datos = '';

    $consulta= "
        SELECT id, userid, courseid, timecreated
        FROM {$CFG->prefix}logstore_standard_log
        WHERE userid = ?
        AND timecreated  BETWEEN ? AND ?
        ORDER BY id ASC";
    $datos = $DB->get_records_sql($consulta, array($idusuario, $f_inicio, $f_fin));
    return $datos;
}


/**
Esta función obtiene todos los registros de mensajes en el foro en un determinado tiempo para todos los usuarios

$f_inicio	bigint    fecha en formato timestamp
$f_fin    	bigint    fecha en formato timestamp
*/
function consulta_mensajes_foro($f_inicio, $f_fin)
{
    global $DB;
    global $CFG;
    $data = '';

    $query = "
        SELECT C.*, u.username,
        (SELECT su.username
            FROM {$CFG->prefix}user su WHERE su.id =
            (SELECT sc.userid FROM {$CFG->prefix}forum_posts sc
            WHERE C.parent = sc.id )) As Enrespuesta , CHARACTER_LENGTH(C.message)
            As Largo, fd.course, fd.forum, fd.name FROM {$CFG->prefix}forum_discussions fd, {$CFG->prefix}forum_posts C, {$CFG->prefix}user u
            WHERE u.idnumber LIKE 'LicenciaturaJunio2016'
            LIMIT 0,1000";
    $data = $DB->get_records_sql($query);
    return $data;
}


/**
Obtiene todos los registros de {$CFG->prefix}teacher_time en un determinado lapso de tiempo y para un usuario determinado

$idusuario	bigint
$f_inicio   bigint    fecha en formato timestamp
$f_fin    	bigint    fecha en formato timestamp
*/
function get_records_unam_stats_usertime($idusuario, $f_inicio, $f_fin){
    global $DB;
    global $CFG;

    $query= "
        SELECT *
        FROM {$CFG->prefix}unam_stats_usertime
        WHERE userid = ?
        AND time_start >= ?
        AND time_start <= ?
        ORDER BY id ASC";
    $param = array($idusuario, $f_inicio, $f_fin);
    $data = $DB->get_records_sql($query,$param);

    return $data;
}


/**
Obtiene todos los registros de {$CFG->prefix}unam_stats_forum en un determinado lapso de tiempo y para un usuario determinado

$idusuario	bigint
$f_inicio   bigint    fecha en formato timestamp
$f_fin    	bigint    fecha en formato timestamp
*/
function get_records_unam_stats_forum($idusuario, $f_inicio, $f_fin){
    global $DB;
    global $CFG;

    $query="
	SELECT
		fs.time_start,
		SUM(fs.total_posts) as total_posts,
		SUM(fs.total_words) as total_words,
		SUM(fs.total_characters) as total_characters,
		group_concat( '{\"',fs.responsed_userid, '\":\"',fs.total_posts,'\"}' separator ',') as relaciones

	FROM {$CFG->prefix}unam_stats_forum as fs

	WHERE fs.userid IN (?)
	AND fs.time_start BETWEEN ? AND ?

	GROUP BY fs.time_start
	";

    $param = array($idusuario, $f_inicio, $f_fin);
    $data = $DB->get_records_sql($query,$param);

    return $data;
}

/*/
Se obtiene el respondiente de la tabla unam_stats_forum, nombre, apellidos y picture de la tabla user. El id de grupo de la tabla unam_stats_forum. El nombre del grupo, el nombre del curso y el nombre corto, así como la suma de las palabras, mensajes y el total de caracteres

$idusuario  bigint
$f_inicio   bigint    fecha en formato timestamp
$f_fin      bigint    fecha en formato timestamp
*/
function get_records_unam_stats_forum2($idusuario, $f_inicio, $f_fin){
    global $DB;
    global $CFG;

    $query="
	SELECT
		fs.responsed_userid,
		u.firstname as firstname,
		u.lastname as lastname,
		u.picture,
		fs.groupid,
		g.name as groupname,
		f.course as courseid,
		c.shortname as course_name,

		SUM(fs.total_words) as total_words,
		SUM(fs.total_posts) as total_posts,
		SUM(fs.total_characters) as total_characters


		FROM {$CFG->prefix}unam_stats_forum fs
		INNER JOIN {$CFG->prefix}user u  on (u.id = fs.responsed_userid)
		INNER JOIN {$CFG->prefix}forum f on (fs.foroid = f.id)
		INNER JOIN {$CFG->prefix}course c on (f.course = c.id)
		LEFT  JOIN {$CFG->prefix}groups g on (g.id = fs.groupid)

		WHERE fs.userid IN (?)
		AND fs.time_start BETWEEN ? AND ?

		GROUP BY fs.time_start
	";


    $param = array($idusuario, $f_inicio, $f_fin);
    $data = $DB->get_records_sql($query,$param);

    return $data;
}


/**
Obtiene un listado de las fechas que tienen registro en el periodo especificado

$f_inicio	bigint    fecha en formato timestamp
$f_fin    	bigint    fecha en formato timestamp
*/
function get_days_unam_stats_usertime($f_inicio, $f_fin){
    global $DB;
    global $CFG;
    $query= "
        SELECT DISTINCT time_start
        FROM {$CFG->prefix}unam_stats_usertime
        WHERE time_start BETWEEN ? AND ?
    ";
    $param = array($f_inicio, $f_fin);
    $data = $DB->get_records_sql($query,$param);
    return $data;
}


/**
Calcula el total de tiempo en segundos de una serie de registros

$registros	arr	Estos registros son obtenidos de la tabla mdl_logstore_standard_log
$ventana	int	Cantidad en segundos del máximo tiempo permitido entre un evento y otro del log.
*/
function computo_tiempo_curso($registros, $ventana){

    $sumas = array();
//    $numero = count($registros, 0);
    $contador = 0;
    $anterior = 0;
    $curso_anterior = 0;

    foreach($registros as $registro){
	if($contador == 0){
		$curso_anterior = $registro->courseid;
		$sumas["$curso_anterior"]= array("tiempo"=>0, "hits"=>1); 
		$anterior = $registro->timecreated;
	}else{
		if($curso_anterior != $registro->courseid){
			$curso_anterior = $registro->courseid;
		}

		if (!array_key_exists($registro->courseid, $sumas)){
			$sumas["$curso_anterior"] = array("tiempo"=>0, "hits"=>1); 
		}

		$diferencia = $registro->timecreated - $anterior;
		$sumas["$curso_anterior"]["hits"]++;

		if($diferencia <= $ventana){
			$sumas["$curso_anterior"]["tiempo"] += $diferencia;
		}
		$anterior = $registro->timecreated;		
	}
        $contador++;
    }
    return $sumas;
}




/**
Calcula el total de tiempo en segundos de una serie de registros

$registros	arr	Estos registros son obtenidos de la tabla mdl_logstore_standard_log
$ventana	int	Cantidad en segundos del máximo tiempo permitido entre un evento y otro del log.
*/
function computo_tiempo($registros, $ventana){

    $suma = 0;
    $numero = count($registros, 0);
    $contador = 0;
    $anterior = 0;

    foreach($registros as $registro){
        if($contador == 0){
            $anterior = $registro->timecreated;
        }else{
            $diferencia = $registro->timecreated - $anterior;
            if($diferencia <= $ventana){
                $suma += $diferencia;
            }
            $anterior = $registro->timecreated;
        }
        $contador++;
    }
	$result=array("tiempo"=>$suma,"hits"=>$contador);
    return $result;
}


/**
Realiza una inserción a la tabla teacher_time

$userid		int
$time_start	int	fecha en formato timestamp
$time_end	int	fecha en formato timestamp
$total_time	int
*/

function insert_teacher_time($userid, $time_start, $time_end, $total_time){
    global $DB;

    $registro = new stdClass();
    $registro->userid = $userid;
    $registro->time_start = $time_start;
    $registro->time_end = $time_end;
    $registro->total_time = $total_time;

    $tmp = $DB->insert_record('teacher_time', $registro, false);

}


/**
Realiza una inserción a la tabla unam_stats_usertime

$userid		bigint
$time_start   	bigint    fecha en formato timestamp
$time_end      	bigint    fecha en formato timestamp
$total_time    	bigint
*/

function insert_unam_stats_usertime($userid, $time_start, $time_end, $total_time){
    global $DB;

    $registro = new stdClass();
    $registro->userid = $userid;
    $registro->time_start = $time_start;
    $registro->time_end = $time_end;
    $registro->total_time = $total_time["tiempo"];
    $registro->hits = $total_time["hits"];

    $tmp = $DB->insert_record('unam_stats_usertime', $registro, false);
}


/**
Realiza una inserción a la tabla unam_stats_usertime_course

$userid		bigint
$time_start   	bigint    fecha en formato timestamp
$time_end      	bigint    fecha en formato timestamp
$total_time    	bigint
*/

function insert_unam_stats_usertime_course($userid, $time_start, $time_end, $total_time, $courseid){
    global $DB;

    $registro = new stdClass();
    $registro->userid = $userid;
    $registro->time_start = $time_start;
    $registro->time_end = $time_end;
    $registro->total_time = $total_time["tiempo"];
    $registro->courseid = $courseid;
    $registro->hits = $total_time["hits"];

    $tmp = $DB->insert_record('unam_stats_usertime_course', $registro, false);
}


/**
Realiza una inserción a la tabla unam_stats_forum

$register    obj
$time_start  bigint    fecha en formato timestamp
$time_end    bigint    fecha en formato timestamp
*/
function insert_unam_stats_forum($register, $time_start, $time_end){
    global $DB;
    $registro = new stdClass();
    $registro->foroid = $register->foroid;
    $registro->userid = $register->userid;
    $registro->groupid = $register->groupid;
    $registro->responsed_userid = $register->responsed_userid;
    $registro->total_words = $register->total_words;
    $registro->total_posts = $register->total_mensajes;
    $registro->total_characters = $register->total_caracteres;
    $registro->time_start = $time_start;
    $registro->time_end = $time_end;

    $tmp = $DB->insert_record('unam_stats_forum', $registro, false);
}



/**
Esta función genera un array con días dado una fecha de inicio y fin como parámetros, haciendo que cada elemento sea un día de diferencia (68400 segundos del anterior)

$fInicio	bigint	fecha en formato timestamp
$fFin        	bigint	fecha en formato timestamp
*/
function get_days_in_a_period($fInicio, $fFin){
    $auxFechaInicio = $fInicio;
    $contador = 0;
    $arregloDias[$contador++] = $fInicio;
    while($auxFechaInicio < $fFin)
    {
        $auxFechaInicio += 86400;
        $arregloDias[$contador] = ($auxFechaInicio < $fFin)?$auxFechaInicio:$fFin;
        $contador++;
    }
    return $arregloDias;
}


/**
Esta función convierte el parámetro de tipo entero (en segundos) en un formato de hora:  hh:mm:ss

$tiempo_en_segundos	int
*/
function conversorSegundosHoras($tiempo_en_segundos){
    $horas = floor($tiempo_en_segundos / 3600);
    $minutos = floor(($tiempo_en_segundos - ($horas * 3600)) / 60);
    $segundos = $tiempo_en_segundos - ($horas * 3600) - ($minutos * 60);
    return $horas . ':' . $minutos . ":" . $segundos;
}


/**
Esta función obtiene los datos de usuario: id, firstanme, lastname roleid y shortname de la base de datos dado un rol o grupo de roles

$p_role     array
*/
function get_users_role($p_role){
    global $DB;
    global $CFG;
    $result = "";
    $query="
        SELECT u.id, u.firstname, u.lastname, ra.roleid, r.shortname

        FROM {$CFG->prefix}user AS u
        INNER JOIN {$CFG->prefix}role_assignments AS ra ON u.id = ra.userid
        INNER JOIN {$CFG->prefix}context AS context ON context.id = ra.contextid AND context.contextlevel = 50
        INNER JOIN {$CFG->prefix}role AS r ON ra.roleid = r.id

        WHERE ra.roleid IN ($p_role)";

    $data = $DB->get_records_sql($query);

    return $data;
}


/**
Esta función obtiene el un arreglo de usuarios con los datos id, firstame, lastname, roleid y shortname de la tabla user dado un rol de usuario.

*/
function get_users_id(){
    global $DB;
    global $CFG;
    $result = "";
    $query="
        SELECT DISTINCT  u.id, u.firstname, u.lastname
        FROM {$CFG->prefix}user u
        INNER JOIN  {$CFG->prefix}logstore_standard_log l ON l.userid = u.id
        INNER JOIN {$CFG->prefix}role_assignments AS ra ON u.id = ra.userid
        INNER JOIN {$CFG->prefix}context AS context ON context.id = ra.contextid AND context.contextlevel = 50
        INNER JOIN {$CFG->prefix}role AS r ON ra.roleid = r.id
        WHERE ra.roleid IN ({$CFG->role_calculate})
	ORDER BY u.id asc
    ";
    $data = $DB->get_records_sql($query);
    return $data;
}

/**
Esta función realiza el conteo de los post en los foros dado un id de usuario

$date_start     bigint  fecha en formato timestamp
$date_end       bigint  fecha en formato timestamp
$var_userid     bigint
*/
function get_count_forum_post($date_start, $date_end, $var_userid){
    global $DB;
    global $CFG;

    $query="
	SELECT count(*) AS count
	FROM {$CFG->prefix}forum_posts AS fp
	WHERE  fp.userid in ($var_userid)
	AND fp.created BETWEEN ".$date_start." AND ".$date_end.";
	";
    $data = $DB->get_record_sql($query);
    return $data;
}

/**
Esta función realiza el conteo de los clics en la plataforma moodle dado un id de usuario

$date_start     bigint  fecha en formato timestamp
$date_end       bigint  fecha en formato timestamp
$var_userid     bigint
*/
function get_count_user_hits($date_start, $date_end, $var_userid){
    global $DB;
    global $CFG;

    $query="
    SELECT count(*) AS count
    FROM {$CFG->prefix}logstore_standard_log AS l
    WHERE  l.userid in ($var_userid)
    AND l.timecreated BETWEEN ".$date_start." AND ".$date_end.";
    ";
    $data = $DB->get_record_sql($query);
    return $data;
}



/**
Esta  función que extrae los datos: id, foroid, discussionid, userid, groupid, courseid, total_character y total_discussion con respecto a los foros de discusión dado un periodo y un id de usuario.

$date_start	bigint	fecha en formato timestamp
$date_end	bigint	fecha en formato timestamp
$var_userid	bigint
*/

function get_masseges_forum_user($date_start, $date_end, $var_userid){
    global $DB;
    global $CFG;

    $query="
        SELECT
	fp.id,

	f.id as foroid,
	fp.userid,
	fd.groupid,
	fp_parent.userid as responsed_userid,
	count(*) as total_mensajes,
	group_concat(fp.message SEPARATOR ' ') as mensajes, 
	SUM(CHARACTER_LENGTH(fp.message)) As total_caracteres


        FROM {$CFG->prefix}forum f
	INNER JOIN {$CFG->prefix}forum_discussions AS fd ON (fd.forum = f.id)
	INNER JOIN {$CFG->prefix}forum_posts AS fp ON (fp.discussion = fd.id)
	INNER JOIN {$CFG->prefix}forum_posts AS fp_parent ON (fp_parent.id = fp.parent)

	WHERE fp.created BETWEEN ".$date_start." AND ".$date_end."
	AND fp.userid in ($var_userid)
	group by f.id,fp_parent.userid
	order by f.id,fp.userid, fp_parent.userid asc
	"; 
    $data = $DB->get_records_sql($query);
    return $data;
}


/**
Esta función extrae los datos: id, foroid y forum_name, de la tabla forum_post; discusionid, discussion_name, groupid y course de la tabla forum_discussions;  userid, username, firstname y lastname de la tabla user;  group_name de la tabla group; course_name de la tabla course, total_caracteres y total_mensajes dado un período y un rol o grupo de roles.

$date_start	bigint	fecha en formato timestamp
$date_end     	bigint	fecha en formato timestamp
$users    	array
*/
function get_masseges_forum_role($date_start, $date_end, $users){
    global $DB;
    global $CFG;
    $date_end += 86399;
    $query="
       SELECT
         fs.id,
         f.course,
         course.shortname as course_shortname,
       	 course.fullname as course_fullname,

         fs.foroid as foroid,
       	 f.name as forum_name,

         fs.userid,

         uf.username,
       	 uf.firstname,
         uf.lastname,

         fs.groupid,
         groups.name as group_name,
         fs.responsed_userid,

         uf_responsed.username as responsed_username,
         uf_responsed.firstname as responsed_firstname,
         uf_responsed.lastname as responsed_lastname,

         SUM(fs.total_posts) as total_posts,
         SUM(fs.total_words) as total_words,
         SUM(fs.total_characters) as total_characters

	   FROM {$CFG->prefix}unam_stats_forum as fs
	   INNER JOIN {$CFG->prefix}forum AS f ON (f.id = fs.foroid)
	   INNER JOIN {$CFG->prefix}user AS uf ON (fs.userid = uf.id)
	   INNER JOIN {$CFG->prefix}user AS uf_responsed ON (fs.responsed_userid = uf_responsed.id)
	   INNER JOIN {$CFG->prefix}course AS course ON (f.course = course.id)
	   LEFT  JOIN {$CFG->prefix}groups AS groups ON (groups.id = fs.groupid)

	   WHERE fs.userid in (". $users .")
	   AND   fs.time_start BETWEEN ".$date_start." AND ".$date_end."
	GROUP BY fs.foroid, fs.responsed_userid
	ORDER BY fs.foroid, fs.userid, fs.responsed_userid

	";

    $data = $DB->get_records_sql($query);
    return $data;
}



/**
Esta función genera un arreglo de ids de usuario dado un arreglo de roles

$string_roles	array
*/
function get_user_id_role($string_roles){
    global $DB;
    global $CFG;
    $query="
	SELECT u.id
	FROM {$CFG->prefix}user AS u

	INNER JOIN {$CFG->prefix}role_assignments AS ra ON u.id = ra.userid
	INNER JOIN {$CFG->prefix}context AS context ON context.id = ra.contextid AND context.contextlevel = 50
	INNER JOIN {$CFG->prefix}role AS r ON ra.roleid = r.id
	WHERE ra.roleid IN ($string_roles)
	ORDER BY u.id ASC";
    $data = $DB->get_records_sql($query);
    return $data;
}

/**
Esta función genera un listado de los datos a importar en un .csv, preparando el encabezado y formando los datos del arreglo .csv

$arr            array
$restrictions   int
*/
function get_data($arr, $restrictions){
	$i = 0; //contador de registros
	$output = "";
	$header = "";
	$flag = (count($restrictions) > 0) ? TRUE : FALSE; //SE INICIALIZA UNA BANDERA, DE ACUERDO AL VALOR

	foreach ($arr as $this_row) {// comienzo el recorrido de cada registro
		$row = "";

		foreach ($this_row as $key =>$value) {  //ahora reviso cada campo del registro

			$toprint = true; //si el valor es verdadero se imprimirá esta colunna

			if ($flag){ //cuando hay restricciones entra a este segmento
				$toprint = !in_array($key, $restrictions);
			}

			//inicializo el encabezado con los indices asociativos del array
			if (($i == 0) && $toprint){ $header .= $key . ','; } //si es el primer registro y la columna se imprime

			if (!is_numeric($value)) {//si el valor no es un número, reemplazo " por ' para evitar conflictos en el csv
				$value = str_replace('"', "'",  $value);
			}

			if($toprint) $row .= '"' . $value . '",';
		}
		//Se ha terminado de revisar los campos

		if ($i == 0){ //si es el primer registro, sustituyo la última coma por un salto de línea
			$header = substr($header, 0, -1);
			$output .= $header . "\n";
		}

		//En la cadena del registro actual, sustituyo la última coma por un salto de línea
		$row = substr($row, 0, -1);
		$output .= $row . "\n";

		$i++; //incremento el número de registros procesados
	}

	return $output;
}

/**
Esta función genera un listado de los datos a importar en un .csv, preparando el encabezado y formando los datos del arreglo .csv

$arr_msg_forum_user    array
**/
function get_data_forum($arr_msg_forum_user){
    $output = "";
    $output = "foroid, forum_name, responsed_userid, responsed_username, responsed_firstname, responsed_lastname, userid,username,firstname,lastname,course,course_name,total_mensajes,total_mensajes\n";
    foreach ($arr_msg_forum_user as $msg_forum_user) {

	$msg_forum_user->forum_name = str_replace('"', "'",  $msg_forum_user->forum_name);
	$msg_forum_user->discussion_name = str_replace('"', "'",  $msg_forum_user->discussion_name);

        $output .= "\"" .$msg_forum_user->foroid."\",";
        $output .= "\"" .$msg_forum_user->forum_name."\",";
        $output .= "\"" .$msg_forum_user->responsed_userid."\",";
        $output .= "\"" .$msg_forum_user->responsed_username."\",";
        $output .= "\"" .$msg_forum_user->responsed_firstname."\",";
        $output .= "\"" .$msg_forum_user->responsed_lastname."\",";
        $output .= "\"" .$msg_forum_user->userid."\",";
        $output .= "\"" .$msg_forum_user->username."\",";
        $output .= "\"" .$msg_forum_user->firstname."\",";
        $output .= "\"" .$msg_forum_user->lastname."\",";
        $output .= "\"" .$msg_forum_user->course."\",";
        $output .= "\"" .$msg_forum_user->shortname."\",";
        $output .= "\"" .$msg_forum_user->total_mensajes."\",";
        $output .= "\"" .$msg_forum_user->total_caracteres."\"\n";
    }
    return $output;
}


/**
Función que genera un listado de datos a importar en un .csv (mensajes en el mensajero) preparando el encabezado y formando los datos del arreglo .csv

$arr_msg_messenger    array
*/
function get_data_messenger($arr_msg_messenger){
    $output = "";
    $output = "idmessage,iduserfrom,usernameuserfrom,firstnameuserfrom,lastnameuserfrom,iduserto,usernameuserto,firstnameuserto,lastnameuserto,extenso,fullmessageformat,timecreated,timecreatedformat\n";
    $output = "iduserfrom,username,firstname,lastname,total_caracteres,total_msg\n";

    foreach ($arr_msg_messenger as $key_arr_msg_messenger) {
        $output .= $key_arr_msg_messenger->iduserfrom.",";
        $output .= $key_arr_msg_messenger->username.",";
        $output .= $key_arr_msg_messenger->firstname.",";
        $output .= $key_arr_msg_messenger->lastname.",";
        $output .= $key_arr_msg_messenger->total_caracteres.",";
        $output .= $key_arr_msg_messenger->total_mensajes."\n";

    }
    return $output;
}


/**
Esta función genera un arreglo de datos con los campos: id, name, shortname, sortorder y archetype de la tabla role, según los roles ingresados por el administrador en la instalación.
*/
function get_data_role(){
    global $DB;
    global $CFG;
    $query = "
        SELECT r.id, r.name, r.shortname, r.sortorder, r.archetype
        FROM {$CFG->prefix}role AS r
        WHERE r.id IN ({$CFG->role_calculate})
        ORDER BY r.sortorder";
    $data = $DB->get_records_sql($query);
    return $data;
}

/**
Esta función genera un arreglo de datos con los campos: id, name, shortname, sortorder y archetype de la tabla role,
*/
function get_data_role_all(){
    global $DB;
    global $CFG;
    $query = "
        SELECT r.id, r.name, r.shortname, r.sortorder, r.archetype
        FROM {$CFG->prefix}role AS r        
        ORDER BY r.sortorder";
    $data = $DB->get_records_sql($query);
    return $data;
}


/**
Esta función genera una cadena en formato csv con el detalle diario de los segundos en plataforma  por cada usuario que tenga asignado el rol o roles enviados en el contexto curso

$f_inicio     bigint	fecha en formato timestamp
$f_inicio     bigint	fecha en formato timestamp
$roles Es una cadena con el rol o roles que eligió el usuario
*/
function get_time_platform($f_inicio,$f_fin, $roles){
    $output = "";
    $days =get_days_in_a_period ($f_inicio, $f_fin);
    $arr_usr = get_users_role($roles);

    $init_row = array (
        "userid" => "",
        "lastname" => "",
        "firstname" => "",
        "rol" => ""
    );

    foreach ($days as $day)
        $init_row[$day] = 0;

    $row = $init_row;

    foreach ($row as $index=> $value){
        if(is_numeric($index)) {
            $row[$index] = gmdate('d/m/Y', $index);
        }else
            $row[$index] = $index;
    }

    $output .= row_to_string($row);
    foreach($arr_usr as $this_user) {
        $row = $init_row;
        $row["userid"]= $this_user->id;
        $row["lastname"]= $this_user->lastname;
        $row["firstname"]= $this_user->firstname;
        $row["rol"]= $this_user->shortname;
        $records_user = get_records_unam_stats_usertime($this_user->id, $f_inicio, $f_fin);
        foreach($records_user as $ru){
            $row[$ru->time_start] = $ru->total_time;
        }
        $output .= row_to_string($row);
    }
    return $output;
}


/**
Esta función genera una cadena en formato csv con el detalle diario de el número de mensajes en foros contestados por cada usuario que tenga asignado el rol o roles enviados en el contexto curso

$arr    array
*/
function row_to_string($arr){
    $result = "";
    foreach($arr as $r){
        $result .= '"'.$r . '",';
    }
    $result[strlen($result) - 1] = "\n";
    return $result;
}

/*
Esta función recibe un array, del cuál se genera una cadena con cada elemento del arrego separada por coma(,)

$array      array
*/
function array_to_string($array){
    $salida = "";
    $tamanio = count($array);
    if($tamanio > 0)
    {
	foreach($array as $elemento){
	    $salida .= $elemento->id.",";
        }
	$salida = substr($salida,0,strlen($salida) - 1);
    }
    else
    {
	$salida .= ",";
    }
    return $salida;
}


/**
Esta función genera una cadena en formato csv con el detalle diario de el número de mensajes en foros contestados por cada usuario que tenga asignado el rol o roles enviados en el contexto curso

$f_inicio	bigint	fecha en formato timestamp
$f_fin     	bigint	fecha en formato timestamp
$roles        	Es una cadena con el rol o roles que eligió el usuario     array
*/
function get_unam_stats_forum_details($f_inicio,$f_fin, $roles){
    $output = "";
    $init_row = array (
        "userid" => "",
        "lastname" => "",
        "firstname" => "",
        "role" => ""
    );
    $days =get_days_in_a_period ($f_inicio, $f_fin); //Se obtiene un arreglo cuya dimensión es la cantidad de días que comprende el periodo
    $arr_usr = get_users_role($roles); //Se obtiene una lista de id de los usuarios que coinciden con el rol

    foreach ($days as $day)
        $init_row[$day] = 0;
    $row = $init_row;

    //inicializa los encabezados y lo asigno a la cadena inicial
    foreach ($row as $index=> $value){
        if(is_numeric($index)) {
            $row[$index] = gmdate('d/m/Y', $index);
        }else
            $row[$index] = $index;
    }
    $output .= row_to_string($row);

    //comienzo el recorrido para cada usuario que coincida con el rol
    foreach($arr_usr as $this_user) {
        $row = $init_row;
        $row["userid"]= $this_user->id;
        $row["lastname"]= $this_user->lastname;
        $row["firstname"]= $this_user->firstname;
        $row["role"]= $this_user->shortname;
        $records_user = get_records_unam_stats_forum($this_user->id, $f_inicio, $f_fin); //obtengo los registros que coinciden con el periodo

        foreach($records_user as $ru){
            $row[$ru->time_start] = $ru->total_posts;
        }
        $output .= row_to_string($row);
    }
    return $output;
}


/**
Esta función obtiene un arreglo de con los datos iduserfrom, username, firstname, lastname, total_caracteres y total_mensajes de la tabla message dado un periodo y un arreglo de roles en moodle

$f_inicio   bigint  fecha en formato timestamp
$f_fin      bigint  fecha en formato timestamp
$roles      array
*/
function get_messages_messenger_role($f_inicio, $f_fin, $users){
    global $DB;
    global $CFG;
    $output = [];

    $init_row = array (
        "userid" => "",
        "firstname" => "",
        "lastname" => "",
        "mensajes_leidos" => 0,
        "caracteres_leidos" => 0,
        "mensajes_no_leidos" => 0,
        "caracteres_no_leidos" => 0
    );

    $row = $init_row;

    //inicializa los encabezados y lo asigno a la cadena inicial
    foreach ($row as $index=> $value){
        if(is_numeric($index)) {
            $row[$index] = gmdate('d/m/Y', $index);
        }else
            $row[$index] = $index;
    }

    //array_push($output,$row);
    $a_user = explode(",",$users);
    foreach ($a_user as $index=> $value){
        $output[$value] = $init_row;
        $output[$value]["userid"] = $value;
    }

    $query="
        SELECT 
            uf.id as userid, 
            uf.username, 
            uf.firstname, 
            uf.lastname, 
            SUM(CHARACTER_LENGTH(m.fullmessage)) AS total_caracteres, 
            count(*) AS total_mensajes 

        FROM  {$CFG->prefix}user AS uf 
        LEFT JOIN {$CFG->prefix}message as m ON (m.useridfrom = uf.id) 

        WHERE m.timecreated >= {$f_inicio}
        AND m.timecreated < {$f_fin} 
        AND uf.id in (".$users.") 
        GROUP BY uf.id 
        ORDER BY uf.id asc;
    ";
    $data = $DB->get_records_sql($query);

    foreach ($data as $index=> $value){
        $output[$index]["firstname"] = $value->firstname;
        $output[$index]["lastname"] = $value->lastname;
        $output[$index]["mensajes_no_leidos"] = $value->total_mensajes;
        $output[$index]['caracteres_no_leidos'] = $value->total_caracteres;
    }

    $query="
        SELECT 
            uf.id as userid, 
            uf.username, 
            uf.firstname, 
            uf.lastname, 
            SUM(CHARACTER_LENGTH(m.fullmessage)) AS total_caracteres, 
            count(*) AS total_mensajes 

        FROM  {$CFG->prefix}user AS uf 
        LEFT JOIN {$CFG->prefix}message_read as m ON (m.useridfrom = uf.id) 

        WHERE m.timecreated >= {$f_inicio}
        AND m.timecreated < {$f_fin} 
        AND uf.id in (".$users.") 
        GROUP BY uf.id 
        ORDER BY uf.id asc;
    ";

    $data = $DB->get_records_sql($query);


    foreach ($data as $index=> $value){
        $output[$index]["mensajes_leidos"] = $value->total_mensajes;
        $output[$index]['caracteres_leidos'] = $value->total_caracteres;
    }

    return $output;
}


/**
Esta función se utiliza para imprimir el contenido de una variable

$variable puede ser de cualquier tipo
*/
function imprime_pre($variable){
    echo "<pre>";
    print_r($variable);
    echo "</pre>";
}


/**
Esta función imprime un arreglo de logs

$logs   string
*/
function imprime_logs($logs) {
    foreach ($logs as $k => $registro) {
        imprime_pre($registro);
    }
}


/**
Esta función obtiene todos los registros del logstore
*/
function consulta_logs(){
    global $DB;
    global $CFG;
    $datos = '';

    $consulta= "SELECT id, userid, courseid, timecreated FROM {$CFG->prefix}logstore_standard_log WHERE userid = 5482";
    $datos = $DB->get_records_sql($consulta);
    return $datos;
}

/**
Esta función obtiene el tiempo total de un usuario, dado un periodo de tiempo

$finicio	bigint	formato timestamp
$ffin		bigint	formato timestamp
*/
function get_unam_stats_usertime_count($finicio, $ffin){
    global $DB;
    global $CFG;
    $datos = '';
    $consulta= "
	SELECT COUNT(*) as count
	FROM {$CFG->prefix}unam_stats_usertime
	WHERE time_start between $finicio AND $ffin;";
    $datos = $DB->get_record_sql($consulta);
    return $datos;
}

/**
Esta función obtiene el numero de mensajes en el foro dado un periodo

$finicio	bigint	formato timestamp
$ffin		bigint	formato timestamp
*/
function get_unam_stats_forum_count($finicio, $ffin){
    global $DB;
    global $CFG;
    $datos = '';
    $consulta= "
	SELECT COUNT(*) as count
	FROM {$CFG->prefix}unam_stats_forum
	WHERE time_start between $finicio AND $ffin;";
    $datos = $DB->get_record_sql($consulta);
    return $datos;
}


/**
Esta función devuelve verdadero si el usuario que se manda por parametro, tiene asignado
un rol de la lista enviada en algún curso de la plataforma.

$userid             bigint
$roles_aprobados    $array
*/
function stats_authorized_user($userid, $roles_aprobados){

	//obtengo los cursos
	$courses = get_courses();
	$flag = FALSE;
	$roles_aprobados = split(",", $roles_aprobados);
	//recorro la lista de cursos
	foreach ($courses as $course){
		if($course->id != 1){ //entra en todos los cursos excepto en el curso 1 que es la pantalla inicial
			$context = get_context_instance ( CONTEXT_COURSE, $course->id );
			$roles = get_user_roles($context, $userid, false);
			if (count($roles) > 0){
				foreach ($roles as $role){
					if (in_array($role->roleid, $roles_aprobados)) {
						$flag = TRUE;
					}
				}
			}
		}

	}
	return $flag;
}

/**
Esta función verifica la existencia de una tabla en moodle

$table_name     string
**/
function check_table_moodle($table_name){
  global $DB;
  global $CFG;
  $determination =false;
  $datos = '';
  $consulta= "SHOW TABLES LIKE '{$CFG->prefix}$table_name'";
  $datos = $DB->get_record_sql($consulta);
  if($datos != '')
    $determination = true;
  return $determination;
}

/**
Esta función verifica si la tabla con el nombre que se pasa como parámetro está vacía
$table_name     string

**/
function check_table_data($table_name){
global $DB;
global $CFG;
$determination =false;
$query = "
SELECT COUNT(t.id) as total
FROM {$CFG->prefix}$table_name AS t";
$data = $DB->get_record_sql($query);
if($data->total > 0)
  $determination = true;
return $determination;
}

/**
Esta función trunca una tabla de la base de datos
$table_name     string
**/
function truncate_table_database($table_name){
global $DB;
global $CFG;
$query = "TRUNCATE TABLE {$CFG->prefix}$table_name";
$data = $DB->execute($query);
$determinacion = "La tabla está vacía";
return $determinacion;
}

/**
Esta función crea la tabla unam_stats_forum en la base de datos
$table_name     string
**/
function create_table_unam_stats_forum($table_name){
global $DB;
global $CFG;
$query = "
CREATE TABLE {$CFG->prefix}$table_name (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  foroid bigint(20) DEFAULT NULL,
  userid bigint(20) DEFAULT NULL,
  groupid bigint(20) DEFAULT NULL,
  responsed_userid bigint(20) DEFAULT NULL,
  total_words bigint(20) DEFAULT NULL,
  total_posts bigint(20) DEFAULT NULL,
  total_characters bigint(20) DEFAULT NULL,
  time_start bigint(20) DEFAULT NULL,
  time_end bigint(20) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY foroid (foroid),
  KEY userid (userid),
  KEY groupid (groupid),
  KEY responsed_userid (responsed_userid),
  KEY time_start (time_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$data = $DB->execute($query);
$determinacion = "Se ha creado la tabla";
return $determinacion;
}

/**
Esta función crea la tabla unam_stats_usertime en la base de datos
$table_name     string
**/
function create_table_unam_stats_usertime($table_name){
global $DB;
global $CFG;
$query = "
CREATE TABLE {$CFG->prefix}$table_name (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  userid bigint(20) NOT NULL,
  time_start bigint(20) NOT NULL,
  time_end bigint(20) NOT NULL,
  total_time bigint(20) NOT NULL,
  PRIMARY KEY (id),
  KEY time_start (time_start), 
  KEY time_end (time_end), 
  KEY userid (userid)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$data = $DB->execute($query);
$determinacion = "Se ha creado la tabla";
return $determinacion;
}

/**
Esta función crea la tabla unam_stats_usertime_course en la base de datos
$table_name     string
**/
function create_table_unam_stats_usertime_course($table_name){
global $DB;
global $CFG;
$query = "
CREATE TABLE {$CFG->prefix}$table_name (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  userid bigint(20) NOT NULL,
  time_start bigint(20) NOT NULL,
  time_end bigint(20) NOT NULL,
  total_time bigint(20) NOT NULL,
  courseid bigint(20) NOT NULL,
  PRIMARY KEY (id),
  KEY time_start (time_start), 
  KEY time_end (time_end), 
  KEY userid (userid),
  KEY courseid (courseid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$data = $DB->execute($query);
$determinacion = "Se ha creado la tabla";
return $determinacion;
}

/**
Esta función obtiene el último día calculado en la tabla que se pasa como parámetro (nombre)

$table_name     string
**/
function calculated_last_day($table_name){
global $DB;
global $CFG;
$query = "
SELECT tbl.time_start
FROM {$CFG->prefix}$table_name AS tbl
WHERE tbl.time_start IS NOT NULL
ORDER BY tbl.time_start DESC
LIMIT 1";
$data = $DB->get_records_sql($query);
foreach ($data as $id => $record)
{
  $date = $record->time_start;
}
$date_f = $date;
return $date_f;
}

/**
Esta función realiza la conversión de timestamp (bigint) a fomrato 'd-m-Y H:i:s'

$date   bigint (timestamp)
*/
function transformation_of_date($date){
$offset = date("Z");
	$offset *= 1;
$date -= $offset;
$date_start = date('d-m-Y H:i:s', $date);
return $date_start;
}


/**
Esta función genera el cálculo que agrega registros a la tabla unam_stats_forum
*/
function calculate_unam_stats_forum_for_instalation()
{

    echo "Se inicia con el proceso de cálculo de tiempos por usuario\nEl proceso puede tardar unos minutos\n";

    //Definición de la variable CFG para uso de recursos moodle.
    global $CFG;
  
    echo "Se inicia con el proceso de cálculo de estadísticas de foros\nEl proceso puede tardar unos minutos\n";
  
    //verificar que existan datos en la tabla 'unam_stats_forum'.
    $exist_data = check_table_data("unam_stats_forum");
  
    //Variable offset que contiene la diferencia del GMT para ser aplicada en las variables tipo date
    $offset = date("Z");
  
    //Se multiplica por 1 para transformar la cadena a un entero
    $offset *= 1;

    //En caso de no existir datos en la tabla
    if (!$exist_data)
    {
        //Se realiza el cálculo, pero determinando el usuario cuál es la fecha inicial
        $start_day = $CFG->start_day;
  
        //Se obtiene el día actual en formato 'd-m-Y'
        $today_format = get_today_dmy();

        $yesterday = get_yesterday_timestamp($today_format, $offset);
    
    
    
        $flag = get_unam_stats_forum_count($start_day, $yesterday);
        if ($flag->count)
        {
            print_error("Existen registros dentro del limite");
        }

        $arr_days = get_days_in_a_period($start_day, $yesterday);
        $n_days = count($arr_days);
    
        $arr_users = get_users_id();
        foreach($arr_users as $this_user)
        {
            $elements = get_count_forum_post($start_day, $yesterday, $this_user->id);
            if($elements->count > 0)
            {
                for ($i = 0; $i < $n_days; $i++)
                {
                    $tmp = $arr_days[$i] + 86399;
                    $result = get_masseges_forum_user($arr_days[$i], $tmp, $this_user->id);
                    if ($result != null)
                    {
                        foreach($result as $register)
                        {
                            $register->total_words = get_message_words($register->mensajes);
                            insert_unam_stats_forum($register, $arr_days[$i], $tmp);
                        }
                    }
                }               
            }
        }
        echo "El proceso de cálculo de estadísticas de foros ha concluido satisfactoriamente";
    }
    else//En caso de existir datos
    {
        //Se verifica cuál es el último día calculado.
        $last_day = calculated_last_day("unam_stats_forum");
    
        //Se multiplica por 1 para transformar la cadena a un entero
        $last_day *= 1; 
    
        // Se adecua al GMT
        $last_day -= $offset; 
    
        //Se genera el String en formato d-m-Y        
        $last_day_format = date('d-m-Y', $last_day);

        //Se obtiene el día siguiente al último calculado
        //Se toma como referencia el último día y se le suman los segundos para general el "siguiente día"
        $next_day = $last_day + 86400; 
    
        //Se genera el String en formato d-m-Y        
        $next_day_format = date('d-m-Y', $next_day);

        //Se obtiene el día actual en formato 'd-m-Y'
        $today_format = get_today_dmy();

        //Se obtiene el día anterior llamado "yesterday"
        $yesterday = get_yesterday_timestamp($today_format, $offset);

        if($next_day_format == $today_format)
        {
            error("No se calcula el día actual");
            die();
        }
    
        $next_day += $offset;


        $flag = get_unam_stats_forum_count($next_day, $yesterday);
        if ($flag->count)
        {
            print_error("Existen registros dentro del limite");
        }
        $arr_days = get_days_in_a_period($next_day, $yesterday);
        $n_days = count($arr_days);
        $arr_users = get_users_id();
        foreach($arr_users as $this_user)
        {
            $elements = get_count_forum_post($next_day, $yesterday, $this_user->id);
            if($elements->count > 0)
            {
                for ($i = 0; $i < $n_days; $i++)
                {
                    $tmp = $arr_days[$i] + 86399;
                    $result = get_masseges_forum_user($arr_days[$i], $tmp, $this_user->id);
                    if ($result != null)
                    {
                        foreach($result as $register)
                        {
                             $register->total_words = get_message_words($register->mensajes);
                            insert_unam_stats_forum($register, $arr_days[$i], $tmp);
                        }
                    }
                }               
            }//fin si hay registros
        }
        echo "El proceso de cálculo de estadísticas de foros ha concluido satisfactoriamente";
    }
}

/**
Esta función genera el cálculo que agrega registros a la tabla unam_stats_usertime
*/
function calculate_unam_stats_usertime_for_instalation(){
    echo "Se inicia con el proceso de cálculo de tiempos por usuario\nEl proceso puede tardar unos minutos...\n";
    
    //Definición de la variable CFG para uso de recursos moodle.
    global $CFG;

    //verificar que existan datos en la tabla 'unam_stats_usertime'.
    $exist_data = check_table_data("unam_stats_usertime");
    
    //Variable offset que contiene la diferencia del GMT para ser aplicada en las variables tipo date
    $offset = date("Z");      
    
    //Se multiplica por 1 para transformar la cadena a un entero
    $offset *= 1;

    //En caso de no existir datos en la tabla
    if (!$exist_data)
    {      
        //Se realiza el cálculo, pero determinando el usuario cuál es la fecha inicial
        $start_day = $CFG->start_day;
      
        //Se obtiene el día actual en formato 'd-m-Y'
        $today_format = get_today_dmy();

        //Se obtiene el dato yesterday que funge como fecha_fin
        $yesterday = get_yesterday_timestamp($today_format, $offset);      
      
        //Se obtiene un arreglo con el id, firstname y lastname de los usuarios definidos por el administrador para ser calculados {$CFG->role_calculate}
        $arr_users = get_users_id();

        //Se obtiene el valor de la ventana de tiempo seleccionada por el administrador {$CFG->time_window}
        $window = $CFG->time_window;
      
        //Se multiplica por 1 para hacerlo entero
        $window *= 1;
      
        //Se realiza un arreglo con los días del período de cálculo, en este caso el día de inicio dado por el administrador y el día de fin es el día de ayer
        $arr_days = get_days_in_a_period($start_day, $yesterday);
      
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
    }
    else//En caso de existir datos en la tabla
    {
        //Se verifica cuál es el último día calculado.
        $last_day = calculated_last_day("unam_stats_usertime");
      
        //Se multiplica por 1 para transformar la cadena a un entero
        $last_day *= 1;
      
        // Se adecua al GMT
        $last_day -= $offset;
      
        //Se genera el String en formato d-m-Y
        $last_day_format = date('d-m-Y', $last_day);
      
        //Se obtiene el día siguiente al último calculado, llamado "next_day"      
        //Se toma como referencia el último día y se le suman los segundos para general el "siguiente día"
        $next_day = $last_day + 86400; 
      
        //Se genera el String en formato d-m-Y
        $next_day_format = date('d-m-Y', $next_day);        

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
        $next_day += $offset;      
      
        //Se obtiene un arreglo con el id, firstname y lastname de los usuarios definidos por el administrador para ser calculados {$CFG->role_calculate}
        $arr_users = get_users_id();

        //Se obtiene el valor de la ventana de tiempo seleccionada por el administrador {$CFG->time_window}
        $window = $CFG->time_window;
         
        //Se multiplica por 1 para hacerlo entero
        $window *= 1;

        //Se realiza un arreglo con los días del período de cálculo, en este caso el día de inicio dado por el administrador y el día de fin es el día de ayer
        $arr_days = get_days_in_a_period($next_day, $yesterday);
          
        //El contador de días obtenidos
        $n_days = count($arr_days);

        //El procedimiento de calculo e inserción en la base de datos
        foreach($arr_users as $this_user){
            $elements = get_count_user_hits($next_day, $yesterday + 86399, $this_user->id);
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
    }
}

/**
Esta función obtiene el día actual y lo regresa en formato 'd-m-Y'
*/
function get_today_dmy(){
$today = getdate();//Se obtiene el día presente
$today_tmpstmp = $today[0];//Se obtiene del objeto en posición 0 el valor en formato timestamp (bigint)
$today_format = date('d-m-Y', $today_tmpstmp);//Se obtiene de la variable $today_tmpstmp (en formato timestamp - long) el formato en date 'd-m-Y'        
return $today_format;
}

/**
Esta función obtiene el día anterior y lo regresa en formato timestamp - bigint

$today_format   string
$offset         int
*/
function get_yesterday_timestamp($today_format, $offset){
//Se obtiene el día anterior a las 23:59:59

$yesterday = strtotime($today_format);//La variable $yesterday posee en formato timestamp - bigint el valor de la variable $today en formato date
$yesterday -= 1;//Al hacer la transformación existe una diferencia de valores, que es equilibrada con -1, además de que posee ya el valor restado de 86399 segundos
$yesterday += $offset;//Se agrega el valor de $offset para eliminar la diferencia de GMT    
return $yesterday;
}

/**
Esta función ingresa un registro en la tabla mdl_config

$name       string
$value      puede ser de diversos tipos
*/
function insert_config($name, $value){
  global $DB;

  $registro = new stdClass();
  $registro->name = $name;
  $registro->value = $value;

  $tmp = $DB->insert_record('config', $registro, false);
}

/**
Esta función genera un arreglo de datos con los campos: id, name, shortname, sortorder y archetype de la tabla role, según los roles ingresados por el administrador en la instalación aceptados para ver el contenido de unam_stats.
*/
function get_data_role_allowed(){
    global $DB;
    global $CFG;
    $query = "
        SELECT r.id, r.name, r.shortname, r.sortorder, r.archetype
        FROM {$CFG->prefix}role AS r
        WHERE r.id IN ({$CFG->role_allowed})
        ORDER BY r.sortorder";
    $data = $DB->get_records_sql($query);
    return $data;
}

/**
Esta función genera un arreglo de datos con los campos: id, name, shortname, sortorder y archetype de la tabla role, según los roles ingresados como parámetros.

$roles      array
*/
function get_data_role_per_ids($roles){
    global $DB;
    global $CFG;
    $query = "
        SELECT r.id, r.name, r.shortname, r.sortorder, r.archetype
        FROM {$CFG->prefix}role AS r
        WHERE r.id IN ($roles)
        ORDER BY r.sortorder";
    $data = $DB->get_records_sql($query);
    return $data;
}

/**
Esta función recibe como parametro un conjunto de mensajes concatenados y cuenta el número de palabras sin etiquetas html

$p_string   string
*/
function get_message_words($p_string){
	$p_string = strip_tags($p_string);
	return str_word_count($p_string);
}


/*
Esta función extrae los datos: id, foroid y forum_name, de la tabla forum_post; discusionid, discussion_name, groupid y course de la tabla forum_discussions;  userid, username, firstname y lastname de la tabla user;  group_name de la tabla group; course_name de la tabla course, total_caracteres y total_mensajes dado un período y un rol o grupo de roles.

$date_start bigint  fecha en formato timestamp
$date_end       bigint  fecha en formato timestamp
$var_role       array
*/
function get_messages_forum_user($date_start, $date_end, $userid){
    global $DB;
    global $CFG;
    $date_end += 86399;
    $query="
        SELECT fp.id, 
        f.id as foroid,
        f.name as forum_name, 

        fd.id as discusionid, 
        fd.name as discussion_name,

        fp.userid, 
        uf.username,
        uf.firstname,
         uf.lastname,

        fd.groupid, 
        g.name as group_name,

        fd.course, 
        course.shortname as course_name,

        SUM(CHARACTER_LENGTH(fp.message)) As total_caracteres, 
        count(*) as total_mensajes

        FROM {$CFG->prefix}forum f 
        INNER JOIN {$CFG->prefix}forum_discussions AS fd ON (fd.forum = f.id) 
        INNER JOIN {$CFG->prefix}forum_posts AS fp ON (fp.discussion = fd.id) 
        INNER JOIN {$CFG->prefix}groups AS g ON (g.id = fd.groupid)
        INNER JOIN {$CFG->prefix}user AS uf ON (fp.userid = uf.id)
        INNER JOIN {$CFG->prefix}course AS course ON (fd.course = course.id)

        WHERE fp.created BETWEEN ".$date_start. " AND ".$date_end."
        AND fp.parent <> 0        
        AND fp.userid in (
        SELECT u.id

        FROM {$CFG->prefix}user AS u

        INNER JOIN {$CFG->prefix}role_assignments AS ra ON u.id = ra.userid
        INNER JOIN {$CFG->prefix}context AS context ON context.id = ra.contextid AND context.contextlevel = 50
        INNER JOIN {$CFG->prefix}role AS r ON ra.roleid = r.id
        WHERE u.id IN (".$userid.")
        )
        GROUP BY fp.userid
        ORDER BY fp.userid, fd.id";

    $data = $DB->get_records_sql($query);
    return $data;    
}



/**
Obtiene todos los promedios de tiempo por día {$CFG->prefix}unam_stats_usertime en un determinado lapso de tiempo 
$f_inicio       bigint    fecha en formato timestamp
$f_fin      bigint    fecha en formato timestamp
*/
function get_records_unam_stats_usertime_prom($f_inicio, $f_fin){
    global $DB;
    global $CFG;
    
    $query= "
        SELECT time_start, AVG(total_time) as prom
        FROM {$CFG->prefix}unam_stats_usertime 
        WHERE time_start >= ?
        AND time_start <= ?
        GROUP BY time_start";
    $param = array($f_inicio, $f_fin);
    $data = $DB->get_records_sql($query,$param);

    return $data;
}


/**
Esta función genera una cadena en formato csv con el detalle diario de los segundos en plataforma  por cada usuario que tenga asignado el rol o roles enviados en el contexto curso

$f_inicio     bigint    fecha en formato timestamp
$f_inicio     bigint    fecha en formato timestamp
$userid Es una cadena con el rol o roles que eligió el usuario
*/
function get_time_platform_user($f_inicio,$f_fin, $userid){
    $days =get_days_in_a_period ($f_inicio, $f_fin);
    $init_row = array (
        "userid" => "",
        "lastname" => "",
        "firstname" => "",
    );
    
    foreach ($days as $day)
        $init_row[$day] = 0;

        $row = $init_row;

    foreach ($row as $index=> $value){
        if(is_numeric($index)) {
            $row[$index] = gmdate('d/m/Y', $index);
        }else
            $row[$index] = $index;
    }

    $output["header"] = $row;

    $row = $init_row;
    $this_user = core_user::get_user($userid);
    $row["userid"]= $this_user->id;
    $row["lastname"]= $this_user->lastname;
    $row["firstname"]= $this_user->firstname;

    $records_user = get_records_unam_stats_usertime($this_user->id, $f_inicio, $f_fin);
    foreach($records_user as $ru){
        if($ru->total_time > 0){
            $row[$ru->time_start] = $ru->total_time;
        }
    }
    $output["user"] = $row;


    $row = $init_row;
    $user_prom = get_records_unam_stats_usertime_prom($f_inicio, $f_fin);
    foreach($user_prom as $up){
        $row[$up->time_start] = $up->prom;
    }

    $output["prom"] = $row;
    return $output;
}

/**
Esta función genera un arreglo de ids, nombres y roles de usuario dado un arreglo de roles

$string_roles   array
*/
function get_user_id_name_role($string_roles){
    global $DB;
    global $CFG;
    $query="
    SELECT DISTINCT u.id, u.firstname, u.lastname, r.shortname
    FROM {$CFG->prefix}user AS u
    INNER JOIN {$CFG->prefix}role_assignments AS ra ON u.id = ra.userid
    INNER JOIN {$CFG->prefix}context AS context ON context.id = ra.contextid AND context.contextlevel = 50
    INNER JOIN {$CFG->prefix}role AS r ON ra.roleid = r.id
    WHERE ra.roleid IN ($string_roles)
    ORDER BY u.lastname, u.firstname, r.shortname ASC";
    $data = $DB->get_records_sql($query);
    return $data;
}

function _microtime(){
	// 0.41494500 1291000531 -> 1291000531.41494500
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
?>
