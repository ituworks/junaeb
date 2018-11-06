<?php
/*
 *   Luis Cordero - luis.cordero@uworks.
 *   Dashboard - Monstrar el boton del dashboard solo para los usuarios que se encuentra en la cohorte id = 18
 *   2018-10-30
 */
 require_once(dirname(__FILE__) . '/../config.php');
 global $DB;
 global $USER;
 $user_id = $USER->id;
 $sql = '
         SELECT
             {user}.*,
             {cohort_members}.userid
         FROM
             {user}
             LEFT JOIN {cohort_members} ON {cohort_members}.userid = {user}.id
         WHERE
             {user}.id = '.$user_id.'
             AND {cohort_members}.cohortid = 18

     ';
$user = $DB->get_record_sql($sql);
$user_id = $user->userid;
if ($user_id != 0):
     // usuario se encuentra registrado en la cohorte id = 18
    if ( (!empty($_GET["course_id"])) && (!empty($_GET["region"]))):
        // Error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        // Path to PHPExcel classes
        require_once($CFG->dirroot . '/lib/phpexcel/PHPExcel/IOFactory.php');
        require_once($CFG->dirroot . '/lib/phpexcel/PHPExcel.php');
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        // Create CVS
        // setting column heading
        $objPHPExcel->getActiveSheet()->setCellValue('A1',"ID");
        $objPHPExcel->getActiveSheet()->setCellValue('B1',"Phone");
        $aggregationstatus = $_GET["aggregationstatus"];
        switch ($aggregationstatus) {
            case 'used':
                $aggregationstatus_filter = 'Finalizados';
                $aggregationstatus = '"used"';
                $aggregationstatus_url = 'used';
                break;

            case 'novalue':
                $aggregationstatus_filter = 'En Curso';
                $aggregationstatus = '"novalue"';
                $aggregationstatus_url = 'novalue';
                break;

            default:
                $aggregationstatus_filter = 'Todos';
                $aggregationstatus = '"used", "novalue"';
                $aggregationstatus_url = '';
                break;
        }
        $group = $_GET["group"];
        switch ($group) {
            case 'JUNAEB':
                $group_filter = 'JUNAEB';
                $group_url = 'JUNAEB';
                $not_like = '';
                $email = '@junaeb.cl';
                break;

            case 'PAE':
                $group_filter = 'PAE';
                $group_url = 'PAE';
                $not_like = 'NOT';
                $email = '@junaeb.cl';
                break;

            default:
                $group_filter = 'Todos';
                $group_url = '';
                $not_like = '';
                $email = '';
                break;
        }
        $course_id = $_GET["course_id"];
        $region = $_GET["region"];
        if ($region == 'Sin definir') {
            $region = '';
            $region_title = 'Sin definir';
        }else{
            $region_title =  $region;
        }
        $sql = 'SELECT * FROM {course} WHERE {course}.id = ' . $course_id;
        $course = $DB->get_record_sql($sql);
        $sql = '
                SELECT
                    {user}.*,
                    {grade_grades}.finalgrade
                FROM
                    {grade_grades}
                    INNER JOIN {user} ON {user}.id = {grade_grades}.userid
                    INNER JOIN {grade_items} ON {grade_items}.id = {grade_grades}.itemid
                WHERE
                    {grade_items}.courseid = ' . $course_id . '
                    AND {user}.region = "' . $region . '"
                    AND {user}.deleted = 0
                    AND {grade_items}.itemmodule = "quiz"
                    AND {grade_grades}.aggregationstatus IN ('.$aggregationstatus.')
                    AND {user}.email '.$not_like.' LIKE "%'.$email.'%"
                ORDER BY {user}.username ASC
                ';
        $rows = $DB->get_records_sql($sql, $params=null, $limitfrom=0, $limitnum=0);


        // setting column heading
        $i=1;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'ID');
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,'Apellidos');
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,'Nombres');
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,'Email');
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,'Región');
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,'Provincia');
        $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,'Perfil');
        $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,'RDB');
        $objPHPExcel->getActiveSheet()->setCellValue('J'.$i,'NOTA');
        // setting column body
        $i=2;
        foreach ($rows as $row):
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$row->username);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$row->firstname);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$row->lastname);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$row->email);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$row->region);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$row->provincia);
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$row->perfil);
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$row->rbd);
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$i,number_format($row->finalgrade, 2));
            $i++;
        endforeach;
        // Redirect output to a client’s web browser (Excel5)
        $name_file = date('Ymd-his');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="export-'.$name_file.'.csv"');
        header('Cache-Control: max-age=0');
        // Create
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
        $objWriter->save('php://output');
        exit;
    else:
        $url = new moodle_url('/dashboard/index.php');
        redirect($url);
    endif;
else:
    echo $OUTPUT->header();
    echo "<h1>Usted no poseea acceso</h1>";
    echo $OUTPUT->footer();
endif;
?>
