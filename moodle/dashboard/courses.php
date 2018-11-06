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
    if (!empty($_GET["category_id"])):
        $category_id = $_GET["category_id"];
        $sql = 'SELECT * FROM {course_categories} WHERE {course_categories}.id = ' . $category_id;
        $category = $DB->get_record_sql($sql);
        $sql = '
                SELECT
                    *
                FROM
                    {course}
                WHERE
                    {course}.visible = 1 AND {course}.category = '.$category_id.'
            ';
        $rows = $DB->get_records_sql($sql, $params=null, $limitfrom=0, $limitnum=0);

        echo $OUTPUT->header();

?>
<link rel="stylesheet" href="<?php echo $CFG->wwwroot.'/dashboard/assets/node_modules/bootstrap/dist/css/bootstrap.min.css'; ?>">
<link rel="stylesheet" href="<?php echo $CFG->wwwroot.'/dashboard/assets/style.css'; ?>">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <?php
                $url_breadcrumb = new moodle_url('/dashboard/index.php');
            ?>
            <li class="breadcrumb-item"><a href="<?php echo $url_breadcrumb; ?>">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $category->name ?></li>
        </ol>
    </nav>
    <p>Por favor seleccione el curso para conocer las calificaciones</p>
    <h2>Lista de cursos</h2>
    <div class="table-crud">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Curso</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $i = 0;
                    foreach ($rows as $row):
                        $i++;
                        $row->url = new moodle_url('/dashboard/course.php', array('category_id' => $category->id, 'course_id' => $row->id));
                ?>
                <tr>
                    <th scope="row"><?php echo $i; ?></th>
                    <td>
                        <?php echo $row->fullname; ?>
                    </td>
                    <td>
                        <a href="<?php echo $row->url; ?>" class="btn btn-primary">
                            Ver más
                        </a>
                    </td>
                </tr>
                <?php
                    endforeach;
                ?>
          </tbody>
        </table>
    </div>
<?php
    echo $OUTPUT->footer();
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
