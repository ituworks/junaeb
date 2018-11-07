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
            $region_url = 'Sin definir';
        }else{
            $region_title =  $region;
            $region_url =  $region;
        }
        $category_id = $_GET["category_id"];
        $sql = 'SELECT * FROM {course_categories} WHERE {course_categories}.id = ' . $category_id;
        $category = $DB->get_record_sql($sql);
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
        $sql = '
                SELECT
                    *
                FROM
                    {grade_items}
                WHERE
                    {grade_items}.courseid = ' . $course_id . '
                    AND {grade_items}.itemmodule = "quiz"
                ';
        $grade_items = $DB->get_record_sql($sql);
        echo $OUTPUT->header();
    ?>
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot.'/dashboard/assets/node_modules/bootstrap/dist/css/bootstrap.min.css'; ?>">
    <link rel="stylesheet" href="<?php echo $CFG->wwwroot.'/dashboard/assets/style.css'; ?>">
    <script src="<?php echo $CFG->wwwroot.'/dashboard/assets/node_modules/chart.js/dist/Chart.js'; ?>"></script>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <?php
                    $url_breadcrumb = new moodle_url('/dashboard/index.php');
                ?>
                <li class="breadcrumb-item"><a href="<?php echo $url_breadcrumb; ?>">Dashboard</a></li>
                <?php
                    $url_breadcrumb = new moodle_url('/dashboard/courses.php', array('category_id' => $category->id));
                ?>
                <li class="breadcrumb-item"><a href="<?php echo $url_breadcrumb; ?>"><?php echo $category->name; ?></a></li>
                <?php
                    $url_breadcrumb = new moodle_url('/dashboard/course.php', array('course_id' => $course_id, 'category_id' => $category->id, 'group' => $group_url, 'aggregationstatus' => $aggregationstatus_url));
                ?>
                <li class="breadcrumb-item"><a href="<?php echo $url_breadcrumb; ?>"><?php echo $course->fullname; ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $region_title; ?></li>
            </ol>
        </nav>
        <h2>
            <?php echo $course->fullname; ?>
        </h2>
        <h3>
            Usuarios de la región <?php echo $region_title; ?>
        </h3>
        <p class="text-right"><b>Nota Máxima <?php echo number_format($grade_items->grademax, 2); ?></b></p>
        <div class="row">
            <div class="col">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#">Estados:</a>
                    </li>
                    <li class="nav-item">
                        <?php
                            $url_nav = new moodle_url('/dashboard/user.php', array('category_id' => $category->id, 'course_id' => $course->id, 'region' => $region_url, 'aggregationstatus' => '', 'group' => $group_url ));
                            if ($aggregationstatus_url == '') {
                                $class = ' active';
                            }else{
                                $class = '';
                            }
                        ?>
                        <a class="nav-link<?php echo $class; ?>" href="<?php echo $url_nav; ?>">Todos</a>
                    </li>
                    <li class="nav-item">
                        <?php
                            $url_nav = new moodle_url('/dashboard/user.php', array('category_id' => $category->id, 'course_id' => $course->id, 'region' => $region_url, 'aggregationstatus' => 'used', 'group' => $group_url ));
                            if ($aggregationstatus_url == 'used') {
                                $class = ' active';
                            }else{
                                $class = '';
                            }
                        ?>
                        <a class="nav-link<?php echo $class; ?>" href="<?php echo $url_nav; ?>">Finalizados</a>
                    </li>
                    <li class="nav-item">
                        <?php
                            $url_nav = new moodle_url('/dashboard/user.php', array('category_id' => $category->id, 'course_id' => $course->id, 'region' => $region_url, 'aggregationstatus' => 'novalue', 'group' => $group_url ));
                            if ($aggregationstatus_url == 'novalue') {
                                $class = ' active';
                            }else{
                                $class = '';
                            }
                        ?>
                        <a class="nav-link<?php echo $class; ?>" href="<?php echo $url_nav; ?>">En Curso</a>
                    </li>
                </ul>
            </div>
            <div class="col">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#">Grupos:</a>
                    </li>
                    <li class="nav-item">
                        <?php
                            $url_nav = new moodle_url('/dashboard/user.php', array('category_id' => $category->id, 'course_id' => $course->id, 'region' => $region_url, 'group' => '', 'aggregationstatus' => $aggregationstatus_url ));
                            if ($group_url == '') {
                                $class = ' active';
                            }else{
                                $class = '';
                            }
                        ?>
                        <a class="nav-link<?php echo $class; ?>" href="<?php echo $url_nav; ?>">Todos</a>
                    </li>
                    <li class="nav-item">
                        <?php
                            $url_nav = new moodle_url('/dashboard/user.php', array('category_id' => $category->id, 'course_id' => $course->id, 'region' => $region_url, 'group' => 'JUNAEB', 'aggregationstatus' => $aggregationstatus_url ));
                            if ($group_url == 'JUNAEB') {
                                $class = ' active';
                            }else{
                                $class = '';
                            }
                        ?>
                        <a class="nav-link<?php echo $class; ?>" href="<?php echo $url_nav; ?>">JUNAEB</a>
                    </li>
                    <li class="nav-item">
                        <?php
                            $url_nav = new moodle_url('/dashboard/user.php', array('category_id' => $category->id, 'course_id' => $course->id, 'region' => $region_url, 'group' => 'PAE', 'aggregationstatus' => $aggregationstatus_url ));
                            if ($group_url == 'PAE') {
                                $class = ' active';
                            }else{
                                $class = '';
                            }
                        ?>
                        <a class="nav-link<?php echo $class; ?>" href="<?php echo $url_nav; ?>">PAE</a>
                    </li>
                </ul>
            </div>
        </div>
        <?php if(count($rows)>0): ?>
        <div class="table-crud">
            <div class="row">
                <div class="col">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Datos del Usuario</th>
                                <th scope="col">Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $i = $sum_promedio = 0;
                                foreach ($rows as $row):
                                    $i++;
                                    $sum_promedio = $sum_promedio + $row->finalgrade;
                            ?>
                            <tr>
                                <th scope="row"><?php echo $i; ?></th>
                                <td>
                                    <div class="row">
                                        <div class="col">
                                            <dl>
                                                <dt>ID</dt>
                                                <dd><?php echo $row->username; ?></dd>
                                                <dt>Apellidos y Nombres</dt>
                                                <dd><?php echo $row->firstname.' '.$row->lastname; ?></dd>
                                                <dt>E-mail</dt>
                                                <dd><?php echo $row->email; ?></dd>
                                                <dt>Región / Provincia</dt>
                                                <dd><?php echo $row->region.' / '.$row->provincia; ?></dd>
                                            </dl>
                                        </div>
                                        <div class="col">
                                            <dl>
                                                <dt>Perfil</dt>
                                                <dd><?php echo $row->perfil; ?></dd>
                                                <dt>Escuela</dt>
                                                <dd><?php echo $row->establecimiento; ?></dd>
                                                <dt>RDB</dt>
                                                <dd><?php echo $row->rbd; ?></dd>
                                            </dl>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php echo number_format($row->finalgrade, 2); ?>
                                </td>
                            </tr>
                            <?php
                                endforeach;
                            ?>
                      </tbody>
                      <tfoot>
                          <tr>
                              <th scope="col" colspan="2">Promedio</th>
                              <th scope="col">
                                  <?php echo number_format($sum_promedio/$i, 2); ?>
                              </th>
                              <th scope="col"></th>
                          </tr>
                      </tfoot>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <?php
                        $url_export = new moodle_url('/dashboard/export.php', array('category_id' => $category->id, 'course_id' => $course_id, 'region' => $region_title, 'group' => $group_url, 'aggregationstatus' => $aggregationstatus_url ));
                    ?>
                    <a href="<?php echo $url_export; ?>" class="btn btn-primary btn-lg">Descargar XLS</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info m-t-1">
            Sin resultados
        </div>
    <?php endif; ?>
    <?php echo $OUTPUT->footer();  ?>
<?php
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
