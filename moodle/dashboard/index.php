<?php
/*
 *   Luis Cordero - luis.cordero@uworks.
 *   Dashboard
 *   2018-10-29
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
    $sql = '
            SELECT
                *
            FROM
                {course_categories}
            WHERE
                {course_categories}.visible = 1
        ';
    $rows = $DB->get_records_sql($sql, $params=null, $limitfrom=0, $limitnum=0);

    echo $OUTPUT->header();

?>
<link rel="stylesheet" href="<?php echo $CFG->wwwroot.'/dashboard/assets/node_modules/bootstrap/dist/css/bootstrap.min.css'; ?>">
<link rel="stylesheet" href="<?php echo $CFG->wwwroot.'/dashboard/assets/style.css'; ?>">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>
    <p>Por favor seleccione la categoría de curso que desea analizar</p>
    <h2>Lista de categorias</h2>
    <div class="table-crud">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Categoría</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $i = 0;
                    foreach ($rows as $row):
                        $i++;
                        $row->url = new moodle_url('/dashboard/courses.php', array('category_id' => $row->id));
                ?>
                <tr>
                    <th scope="row"><?php echo $i; ?></th>
                    <td>
                        <?php echo $row->name; ?>
                    </td>
                    <td>
                        <a href="<?php echo $row->url; ?>" class="btn btn-primary">
                            Ver cursos
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
    echo $OUTPUT->header();
    echo "<h1>Usted no poseea acceso</h1>";
    echo $OUTPUT->footer();
endif;
?>
