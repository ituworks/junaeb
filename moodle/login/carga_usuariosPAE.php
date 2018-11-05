<?php
header("Content-Type: text/html;charset=utf-8");
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
// Developed : Juan Espinoza jg.espinoza.castro@gmail.com
/**
 * Main login page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require_once('lib.php');
require_once($CFG->dirroot.'/cohort/lib.php');

redirect_if_major_upgrade_required();

$testsession = optional_param('testsession', 0, PARAM_INT); // test session works properly
$anchor      = optional_param('anchor', '', PARAM_RAW);      // Used to restore hash anchor to wantsurl.

$context = context_system::instance();
$PAGE->set_url("$CFG->wwwroot/login/index.php");
$PAGE->set_context($context);
$PAGE->set_pagelayout('login');

/// Initialize variables
$errormsg = '';
$errorcode = 0;
$xerrorcode = 0;
$code = 0;


//Crea formato correo con HTML
function bodymail($nuser,$user,$pass)
{

    $html = "
    <p></p>
    <table class='container-for-gmail-android' width='100%' cellspacing='0' cellpadding='0' align='center'>
    <tbody>
    <tr>
    <td style='background-color: #ffffff;' width='100%' valign='top' align='left'><center>
    <table style='border-bottom: 1px solid #cccccc;' width='100%' cellspacing='0' cellpadding='0' bgcolor='#ffffff'>
    <tbody>
    <tr>
    <td style='text-align: center; vertical-align: middle;' width='100%' valign='top' height='80'><center>
    <table class='w320' width='600' cellspacing='0' cellpadding='0'>
    <tbody>
    <tr>
    <td style='vertical-align: middle;'><a href='http://capacita.junaeb.cl/soportecapacita' target='_blank' rel='noopener'><img src='https://www.junaeb.cl/agenda/logo_junaeb_web.png' alt='logo' height='80'></a></td>
    </tr>
    </tbody>
    </table>
    </center><!-- [if gte mso 9]>
                                </v:textbox>
                                </v:rect>
                                <![endif]--></td>
    </tr>
    </tbody>
    </table>
    </center></td>
    </tr>
    <tr>
    <td style='background-color: #f7f7f7;' class='content-padding' width='100%' valign='top' align='center'><center>
    <table class='w320' style='width: 599px;' width='600' cellspacing='0' cellpadding='0'>
    <tbody>
    <tr>
    <td class='header-lg' style='width: 599px;'>Soporte Capacita - Junaeb</td>
    </tr>
    <tr>
    <td class='free-text' style='width: 599px;'></td>
    </tr>
    <tr>
    <td style='padding-bottom: 30px; width: 599px;'>
    <p class='western' style='text-align: left;' lang='es-CL'>Hola ".$nuser." </p><p class='western' style='text-align: left;' lang='es-CL'><span id='selectionBoundary_1538672788604_18110600561031442' style='line-height: 0; display: none;' class='rangySelectionBoundary'>﻿</span><span id='selectionBoundary_1538672792495_2943912868969367' style='line-height: 0; display: none;' class='rangySelectionBoundary'>﻿</span>


    Te damos la bienvenida al entorno virtual de capacitación Junaeb.<br>
    Sus datos fueron validados y podrá ingresar con la siguiente información:<br>
    <br><br>
    <a href='https://capacita.junaeb.cl/app/login/index.php'> -> Entrar a capacita Junaeb</a>

    <br><br>
    usuario    : ".$user."<br>
    Contraseña : ".$pass."<br>
    <br>
    Saludos cordiales




    <br></p>
    </td>
    </tr>

    </tbody>
    </table>
    </center></td>
    </tr>
    <tr>
    <td style='background-color: #ffffff; border-top: 1px solid #e5e5e5; border-bottom: 1px solid #e5e5e5; height: 100px;' width='100%' valign='top' align='center'><center>
    <table class='w320' width='600' cellspacing='0' cellpadding='0'>
    <tbody>
    <tr>
    <td style='padding: 10px 0 10px;'>

    <h2 style='text-align: left;'><strong><span style='color: #3366ff; text-align: left;'>— —</span> <span style='color: #ff0000;'>— — —</span></strong></h2>
    <h5>
    <p style='text-align: left;'><strong>Soporte Junaeb<br></strong></p>
    <p style='text-align: left;'>Departamento de Informática</p>
    <p style='text-align: left;'><strong>Junaeb | Ministerio de Educación | Gobierno de Chile</strong></p>
    <p style='text-align: left;'><a href='https://maps.google.com/?q=Monjitas+565,+Santiago-Centro,+Santiago&amp;entry=gmail&amp;source=g'>Monjitas 565, Santiago-Centro, Santiago</a></p>
    <p style='text-align: left;'><span style='color: #008000;'>Ayuda a proteger el Medio Ambiente, NO imprimas este correo</span></p>
    </h5>
    </td>
    </tr>
    </tbody>
    </table>
    </center></td>
    </tr>
    </tbody>
    </table>
    <p></p>";
    return $html;
}


//Matricula a los alumnos a un Curso
function enrol($courseid,$userid)
{
        global $DB;
        $plugin_instance = $DB->get_record("enrol", array('courseid' => $courseid, 'enrol'=>'manual'));
        $plugin = enrol_get_plugin('manual');
        $roleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $plugin->enrol_user($plugin_instance, $userid, $roleid);
        return $plugin;

}

$sendmail = 0;
$rcount = 0;
$acount = 0;
$count = 0;
$linea = "";
$realcount = 0;



/*
$query = "SELECT * FROM mdl_user WHERE auth = 'manual'";
echo $query;
$records = $DB->get_records_sql($query);
foreach ($records as $var)
{
    $userid = $var->id;
    $rut = $var->rut;
    $rut = str_replace('.', '', $rut);
    $rut = str_replace('.', '', $rut);
    echo "<br>".$rut;
    echo "<br>Actualizando...";
    $usuario = new stdClass();
    $usuario->id = $userid;
    $usuario->rut = $rut;
    $DB->update_record("user", $usuario);
}
die();
*/
//Carga CSV Con datos
$archivo = "cargas_manuales/cargaPAE_2018-11-05.csv";  //Ruta del archivo CSV
//$archivo = "cargas_manuales/test.csv";  //Ruta del archivo CSV
//$archivo = "";


$file = fopen($archivo, "r") or exit("Error abriendo fichero!");

while($linea = fgets($file))
{
  //recoje Linea a Linea y los sepra por ,
  $data = explode(",", $linea);
  $rut = $data[2];
  $correo = $data[7];
  $nombres = $data[3];
  $apellidos = $data[4];
  $rbd = $data[8];
  $telefono = $data[9];
  $region = $data[10];
  $perfil = $data[5].' - '.$data[6];

//valida los datos
$code = 0;
if (empty($rut)) $code = 3;
if (empty($correo)) $code = 3;
if (empty($rbd)) $code = 3;
if (empty($nombres)) $code = 3;
if (empty($apellidos)) $code = 3;

//$responseData = reCapchaKey(0); //Capcha
//if(!$responseData->success) $code = 4;

if ($code == 0)
{

        $fxusername = $rut;
        $xusername = strtoupper($fxusername);
        $xusername = str_replace('.', '', $xusername);
        $xusername = str_replace(',', '', $xusername);
        $xusername = str_replace(' ', '', $xusername);
        $nombre = trim($nombres);
        $apellido = trim($apellidos);
        $mail = trim($correo);
        $rut = trim($rut);
        $rut = str_replace('.', '', $rut);
        $xpassword = "";
        $rxpassword = "";
        $rdb = $rbd;

        //if ($mail == "paula.sotonu@gmail.com") die("paula.sotonu@gmail.com");
        //Genera contraseña con Rut y Nombres
        $xusername = trim($xusername);
        $mf = strpos($xusername,'-');
        if (!empty($mf))
        {
           $xusername = substr($xusername,count($xusername)-1,-2);
           if (ctype_digit($xusername))
           {

                $tmp1 = substr($nombre, 0,1);
                $tmp2 = substr($apellido, 0,1);
                $tmp3 = substr($xusername, 0,4);
                $fulltmp = $tmp1. $tmp2 . $tmp3;
                $xpassword =  strtoupper($fulltmp);
                $rxpassword =  strtoupper($fulltmp);
           }

        }
        else
        {
            $xusername = $fxusername;
            $xpassword = $fpassword;
            $rxpassword =  strtoupper($fulltmp);
        }


        $password = md5($xpassword);
        $samaccountname = $xusername;
        $userid = 0;
        global $USER;
        $query = "select * from {$CFG->prefix}user where username = '$samaccountname'";
        //$query = "select * from {$CFG->prefix}user where username = '$samaccountname' and rut = '$rut'";
        //echo "<br".$query;
        $records = $DB->get_records_sql($query);
        foreach ($records as $var)
        {
            $userid = $var->id;
        }


        echo "<bR>Contador :".$count;
        if (count($records) == 0)
        {
                //Agrega Registro
                //echo "<br>".$query;
                echo "<br>Agregando...";
                $usuario = new stdClass();
                $usuario->username = $samaccountname;
                $usuario->auth = 'manual';
                $usuario->password = $password;
                $usuario->firstname = $nombre;
                $usuario->lastname = $apellido;
                $usuario->email = $mail;
                $usuario->city = "";
                $usuario->confirmed = 1;
                $usuario->policyagreed = 0;
                $usuario->lang = "es";
                $usuario->maildisplay = 0;
                $usuario->autosuscribe = 0;
                $usuario->mnethostid = 1;
                $usuario->rut = $rut;
                $usuario->rbd = $rdb;
                $usuario->phone1 = $telefono;
                $usuario->perfil = $perfil;
                $usuario->city = $region;
                $usuario->type_user = "77";
                $usuario->country = "BR";
                $sendmail = 0;
                $userid = $DB->insert_record("user", $usuario);
                $rcount++;
        }
        else
        {
                //Modifica Registro
                echo "<br>Actualizando...";
                $usuario = new stdClass();
                $usuario->id = $userid;
                $usuario->password = $password;
                $usuario->firstname = $nombre;
                $usuario->lastname = $apellido;
                $usuario->email = $mail;
                $usuario->rbd = $rdb;
                $usuario->confirmed = 1;
                $usuario->phone1 = $telefono;
                $usuario->city = $region;
                $usuario->type_user = "78";
                $usuario->country = "AR";
                $usuario->perfil = $perfil;
                $usuario->rut = $rut;
                $sendmail = 0;
                $DB->update_record("user", $usuario);
                $code = 2;
                $acount++;
        }



        if (!empty($userid))
        {
            // Matricula en todos los cursos
            echo "<br>Matriculando...";
            $rst = enrol(3,$userid);
            // Inducción al entorno virtual Junaeb Capacita
            $rst = enrol(4,$userid);
            // Conociendo Junaeb y su Plan Contrapeso
            $rst = enrol(5,$userid);
            // Comprendiendo nuestro ámbito de actuación como Encargado PAE
            $rst = enrol(6,$userid);
            // Aplicando las Competencias del mi Rol como Encargado PAE
            $rst = enrol(7,$userid);
            // Implementando acciones educativas como Encargado PAE
            $rst = enrol(27,$userid);
            // Bienvenida al Programa para Encargados PAE

            $rst = cohort_add_member(19,$userid);
            // Listado Nacional PAE 05/11/2018


        }

        $code = 1;


        //Enviamos Correo
        $subject = "Registro en plataforma Junaeb Capacita";


        $query = "select * from {$CFG->prefix}user where id = '$userid'";
        $toUser = $DB->get_record_sql($query);
        $toUser->email = $mail;


        $query = "select * from {$CFG->prefix}user where email = 'soportecapacita@junaeb.cl'";
        $fromUser = $DB->get_record_sql($query);

        //echo $nombre;
        //echo $samaccountname;
        //echo $rxpassword;

        $messageHtml = bodymail($nombre,$samaccountname,$rxpassword);
        if ($sendmail == 1)
        {
            echo "<br>Notificanto al correo";
            $rst = email_to_user($toUser, $fromUser, $subject, $messageText, $messageHtml, '', '', true);
        }
        //echo $rst;bodymail
        //echo $messageHtml;

        echo "<br>RUT :".$rut;
        echo "<br>CORREO :".$correo;
        echo "<br>NOMBRES :".$nombres. " ".$apellidos;
        echo "<br>APELLIDOS :".$apellidos;
        echo "<br>RBD :".$rbd;
        echo "<br>TELEFONO :".$telefono;
        echo "<br>REGSION :".$region;
        echo "<br>NUSER :".$samaccountname;
        echo "<br>PASS :".$xpassword;
        if (empty($samaccountname)) echo "ERROR";
        if (empty($xpassword)) echo "ERROR";
        echo "<br>---------------------------------------------";

        $realcount++;

}
$count++;
}
fclose($archivo);
echo "<br>><b>Total de Procesados".$count;
echo "<br>><b>Total de Registrado".$rcount;
echo "<br>><b>Total de Actualizados".$acount;
echo "<br>><b>Total de Actualizados". $realcount;
?>
