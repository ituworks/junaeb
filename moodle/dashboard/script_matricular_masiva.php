<?php
/*
 *   Luis Cordero - luis.cordero@uworks.cl
 *   Marcular Masivamente a todos los suarios en los siguiete cursos excluyendo a los usuarios @junaeb
 *   - Bienvenida al Programa para Encargados PAE.
 *   - Inducción al entorno virtual Junaeb Capacita.
 *   - Conociendo Junaeb y su Plan Contrapeso, inició el 08 de Octubre
 *   - Comprendiendo nuestro ámbito de actuación como Encargado PAE, inició el 15 de Octubre
 *   - Aplicando las Competencias del mi Rol como Encargado PAE, iniciará el 29 de Octubre
 *   - Implementando acciones educativas como Encargado PAE, iniciará el 19 de Noviembre
 *   2018-10-30
 */
require_once(dirname(__FILE__) . '/../config.php');

//Matricula a los alumnos a un Curso
function enrol($courseid, $userid){
    global $DB;
    $plugin_instance = $DB->get_record("enrol", array('courseid' => $courseid, 'enrol'=>'manual'));
    $plugin = enrol_get_plugin('manual');
    $roleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
    $plugin->enrol_user($plugin_instance, $userid, $roleid);
    return $plugin;
}
// require_once($CFG->dirroot.'/cohort/lib.php');
/*
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
*/
$sql = '
        SELECT
            *
        FROM
            {course}
        WHERE
            {course}.id IN (3, 4, 5, 6, 7, 27)
    ';
$courses = $DB->get_records_sql($sql, $params=null, $limitfrom=0, $limitnum=0);
echo $OUTPUT->header();
?>
<h2>Script Matriculado Masivo</h2>
<table class="table">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Curso</th>
            <th scope="col">Usuarios</th>
            <th scope="col">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $i = 0;
            foreach ($courses as $course):
                $i++;
                $sql = '
                    SELECT
                    *
                    FROM
                    mdl_user
                    WHERE
                    mdl_user.id NOT IN (
                        SELECT
                        mdl_user.id
                        FROM
                        mdl_role
                        JOIN mdl_role_assignments ON mdl_role_assignments.roleid = mdl_role.id
                        JOIN mdl_user ON mdl_user.id = mdl_role_assignments.userid
                        JOIN mdl_context ON mdl_context.id = mdl_role_assignments.contextid
                        JOIN mdl_course ON mdl_course.id = mdl_context.instanceid
                        WHERE
                        mdl_role.shortname = "student"
                        AND mdl_course.id = '.$course->id.'
                    )
                    AND mdl_user.email NOT LIKE "%@junaeb.cl%"
                    AND mdl_user.email LIKE "%@%"
                    ';
                $rows = $DB->get_records_sql($sql, $params=null, $limitfrom=0, $limitnum=0);
        ?>
        <tr>
            <th>
                <?php echo $i; ?>
            </th>
            <td>
                <?php echo $course->fullname; ?>
            </td>
            <td>
                <?php
                $count_user = 0;
                foreach ($rows as $row) {
                    $count_user++;
                    echo $row->email.'<br />';
                    echo $row->id.'<br />';
                    enrol($course->id, $row->id);
                }
                ?>
            </td>
            <td>
                <?php echo $count_user; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php echo $OUTPUT->footer(); ?>
