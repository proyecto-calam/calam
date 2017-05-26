<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');
//La variable acción posee el identificador para crear o trucar la tabla mediante AJAX
$group = addslashes(htmlspecialchars($_POST["groups"]));

//La table_name posee el nombre de la tabla para crearla o trucarla mediante AJAX
$course = addslashes(htmlspecialchars($_POST["course"]));

//La table_name posee el nombre de la tabla para crearla o trucarla mediante AJAX
//$startdate = addslashes(htmlspecialchars($_POST["startdate"]));
//$alumnos  = get_student_course($course, $group);
/*$act = get_course_activity($course);
$idactivity = get_cadena_id($act);
$calificaciones = get_user_selected_grades(50, $idactivity);*/
$resultado = array();
$resultado["datos"] = computa($course, $group);

$courseobj = $DB->get_record('course', array('id'=>$course));
//imprime_pre($courseobj);

$resultado["info"] = array("courseid"=>$course, "groupid"=>$group, "coursename"=>$courseobj->fullname);
echo json_encode ($resultado);





function computa($courseid, $groupid){
      $output = array();
      $init_row = array (
          "userid" => "",
          "firstname" => "",
          "lastname" => ""          
      );

      $activities = get_course_activity($courseid); //Se obtiene un arreglo cuya dimensión es la cantidad de actividades de un curso en especifico
//imprime_pre($activities);

      $idactivity = get_cadena_id($activities);
      $arr_usr = get_student_course($courseid, $groupid); //Se obtiene una lista de id de los usuarios enrolados en el curso

      //echo "<pre>";

      foreach ($activities as $activity){
          $init_row[$activity->id] = 0;
        }
      $row = $init_row;

      $i = 1;      
      //inicializa los encabezados y lo asigno a la cadena inicial
      foreach ($row as $index=> $value){
          if(is_numeric($index)) {
              $row[$index] = array("itemname" =>$activities[$index]->itemname, "orden" =>$i++);
          }else
              $row[$index] = $index;
      }      
      array_push($output, $row);
      //imprime_pre($output);

      //comienzo el recorrido para cada usuario que coincida con el rol
      foreach($arr_usr as $this_user) {        
          $row = $init_row;
          $row["userid"]= $this_user->userid;
          $row["lastname"]= $this_user->lastname;
          $row["firstname"]= $this_user->firstname;

//imprime_pre($this_user);          
          $user_grades = get_user_selected_grades($this_user->userid, $idactivity);
//imprime_pre($user_grades);

          foreach($user_grades as $grade){                                  
              $row[$grade->itemid] = $grade->finalgrade;
          }
          array_push($output, $row);

      }      
      return $output;
  }


  function get_course_activity($courseid){
      global $DB;
      global $CFG;

    $query="
    SELECT id, itemname, sortorder
    FROM {$CFG->prefix}grade_items
    WHERE courseid = ? AND itemtype LIKE 'mod'
    ORDER BY sortorder ASC";

    $datos = $DB->get_records_sql($query, array($courseid));

    return $datos;
  }

  function get_cadena_id($arr){
    $out = "";
    foreach($arr as $r) $out .= $r->id . ',';
    $out = substr($out, 0, -1);
    return $out;
  }

  function get_user_selected_grades($userid, $items){
      global $DB;
      global $CFG;
    $query="
    SELECT itemid, finalgrade
    FROM {$CFG->prefix}grade_grades
    WHERE userid = ? AND  itemid IN ($items) ";
//imprime_pre($query);
    $datos = $DB->get_records_sql($query, array($userid));

    return $datos;    
  }


function get_student_course($courseid, $groupid = 0){
  global $DB;
  global $CFG;

  if( $groupid > 0){
    $query="
    SELECT gm.id, gm.userid, u.firstname, u.lastname
    FROM {$CFG->prefix}groups_members AS gm
    INNER JOIN {$CFG->prefix}role_assignments AS ra ON gm.userid = ra.userid AND ra.roleid = 5 
    INNER JOIN {$CFG->prefix}context AS context ON (context.id = ra.contextid AND context.contextlevel = 50 AND context.instanceid =?)
    INNER JOIN {$CFG->prefix}user AS u ON (u.id = gm.userid)
    WHERE gm.groupid = ? ORDER BY id ASC";
    $datos = $DB->get_records_sql($query, array($courseid,$groupid));
  }
  else{
    $query="
    SELECT ra.id, ra.userid, u.firstname, u.lastname
    FROM {$CFG->prefix}role_assignments AS ra 
    INNER JOIN {$CFG->prefix}context AS context ON (context.id = ra.contextid AND context.contextlevel = 50 AND context.instanceid =?)
    INNER JOIN {$CFG->prefix}user AS u ON (u.id = ra.userid)
    WHERE ra.roleid = 5  ORDER BY id ASC";
    $datos = $DB->get_records_sql($query, array($courseid));
  }
  return $datos;
}


?>
