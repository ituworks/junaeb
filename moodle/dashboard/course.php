<?php
/*
 *   Luis Cordero - luis.cordero@uworks.
 *   Dashboard - Monstrar el boton del dashboard solo para los usuarios que se encuentra en la cohorte id = 18
 *   2018-10-30
 */
require_once(dirname(__FILE__) . '/../config.php');
global $DB;
/*
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
    // usuario se encuentra registrado en la cohorte id = 18 */
    if ( (!empty($_GET["course_id"]))  && (!empty($_GET["category_id"])) ):
        global $DB;
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
        $category_id = $_GET["category_id"];
        $sql = 'SELECT * FROM {course_categories} WHERE {course_categories}.id = ' . $category_id;
        $category = $DB->get_record_sql($sql);
        $course_id = $_GET["course_id"];
        $sql = 'SELECT * FROM {course} WHERE {course}.id = ' . $course_id;
        $course = $DB->get_record_sql($sql);
        $sql = '
                SELECT
                    {user}.id,
                    {user}.region,
                    count(*) as n,
                    sum({grade_grades}.finalgrade) as finalgrade
                FROM
                    {grade_grades}
                    INNER JOIN {user} ON {user}.id = {grade_grades}.userid
                    INNER JOIN {grade_items} ON {grade_items}.id = {grade_grades}.itemid
                WHERE
                    {grade_items}.courseid = ' . $course_id . '
                    AND {user}.deleted = 0
                    AND {grade_items}.itemmodule = "quiz"
                    AND {grade_grades}.aggregationstatus IN ('.$aggregationstatus.')
                    AND {user}.email '.$not_like.' LIKE "%'.$email.'%"
                GROUP BY {user}.region
                ORDER BY {user}.region ASC
                ';
        $rows = $DB->get_records_sql($sql, $params=null, $limitfrom=0, $limitnum=0);
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
                <li class="breadcrumb-item active" aria-current="page"><?php echo $course->fullname; ?></li>
            </ol>
        </nav>
        <p>A continuación observa el reporte de las calificaciones de este curso. <button href="#" data-toggle="modal" data-target="#glosaModal" class="btn btn-info btn-sm">Ver detalles</button></p>
        <!-- Modal -->
        <div class="modal fade" id="glosaModal" tabindex="-1" role="dialog" aria-labelledby="glosaModal" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-body">
                  <p>
                      Usted puede hacer el análisis de los datos, con las siguientes características.
                  </p>
                  <p>
                      En sección "estados" se muestras 3 opciones, las cuales se detallas a continuación:
                  </p>
                  <ul>
                      <li>
                          <b>Todos:</b> Aquí te mostramos todos los usuarios que han ingresado a realizar el examen de este curso, aquí se agrupan usuarios que están en estado de realización "Finalizados" y "en curso".
                      </li>
                      <li>
                          <b>Finalizados:</b> Aquí se muestran los usuarios que han realizado exitosamente su examen y cuentan con una calificación final.
                      </li>
                      <li>
                          <b>En curso:</b> Aquí se muestran los usuarios que están realizando su examen final o aun no terminan por completo su examen.
                      </li>
                  </ul>
                  <p>
                      En todos los casos, te mostramos la cantidad total de estudiantes y el promedio total de sus calificaciones, según región.
                      Si quieres ver el detalle por región, ve a “ver más”, además puedes descargar el informe en formato Excel.
                  </p>
                  <p>
                      Además, puede filtrar por tipo de usuario en “grupos” los cuales corresponden a:
                  </p>
                  <ul>
                      <li>
                          <b>JUNAEB:</b> son todos aquellos participantes que han realizado o están realizando el examen de este curso, y son funcionarios Junaeb
                      </li>
                      <li>
                          <b>PAE:</b> son todos aquellos participantes que han realizado o están realizando el examen de este curso, y no son funcionarios Junaeb pero si son parte del programa PAE.
                      </li>
                  </ul>


              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
        <h2>
            <?php echo $course->fullname; ?>
        </h2>
        <div class="row">
            <div class="col">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#">Estados:</a>
                    </li>
                    <li class="nav-item">
                        <?php
                            $url_nav = new moodle_url('/dashboard/course.php', array('category_id' => $category->id, 'course_id' => $course->id, 'aggregationstatus' => '', 'group' => $group_url ));
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
                            $url_nav = new moodle_url('/dashboard/course.php', array('category_id' => $category->id, 'course_id' => $course->id, 'aggregationstatus' => 'used', 'group' => $group_url ));
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
                            $url_nav = new moodle_url('/dashboard/course.php', array('category_id' => $category->id, 'course_id' => $course->id, 'aggregationstatus' => 'novalue', 'group' => $group_url ));
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
                            $url_nav = new moodle_url('/dashboard/course.php', array('category_id' => $category->id, 'course_id' => $course->id, 'group' => '', 'aggregationstatus' => $aggregationstatus_url ));
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
                            $url_nav = new moodle_url('/dashboard/course.php', array('category_id' => $category->id, 'course_id' => $course->id, 'group' => 'JUNAEB', 'aggregationstatus' => $aggregationstatus_url ));
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
                            $url_nav = new moodle_url('/dashboard/course.php', array('category_id' => $category->id, 'course_id' => $course->id, 'group' => 'PAE', 'aggregationstatus' => $aggregationstatus_url ));
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
        <div class="table-crud">
            <div class="row">
                <div class="col">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Region</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">Promedio</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

                                $i = $sum_cantidad = $sum_promedio = 0;
                                foreach ($rows as $row):
                                    $i++;
                                    $sum_cantidad = $sum_cantidad + $row->n;
                                    $sum_promedio = $sum_promedio + $row->finalgrade;
                                    if (!$row->region) {
                                        $row->region = 'Sin definir';
                                    }
                                    $row->url = new moodle_url('/dashboard/user.php', array('category_id' => $category->id, 'course_id' => $course->id, 'region' => $row->region, 'aggregationstatus' => $aggregationstatus_url, 'group' => $group_url ));
                            ?>
                            <tr>
                                <th scope="row"><?php echo $i; ?></th>
                                <td>
                                    <?php
                                        echo $row->region;
                                    ?>
                                </td>
                                <td>
                                    <?php echo $row->n; ?>
                                </td>
                                <td>
                                    <?php echo number_format($row->finalgrade/$row->n, 2); ?>
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
                      <tfoot>
                          <tr>
                              <th scope="col" colspan="2">Totales</th>
                              <th scope="col">
                                  <?php echo $sum_cantidad; ?>
                              </th>
                              <th scope="col">
                                  <?php echo number_format($sum_promedio/$sum_cantidad, 2); ?>
                              </th>
                              <th scope="col"></th>
                          </tr>
                      </tfoot>
                    </table>
                </div>
                <div class="col">
                    <canvas id="myChart" width="400" height="400"></canvas>
                    <script>
                    var ctx = document.getElementById("myChart");
                    var myChart = new Chart(ctx, {
                        type: 'horizontalBar',
                        data: {
                            labels: [
                                <?php foreach ($rows as $row): ?>
                                "<?php if($row->region){ echo $row->region; }else{ echo 'Sin definir'; } ?>",
                                <?php endforeach; ?>
                            ],
                            datasets: [{
                                label: 'Cantidad',
                                data: [
                                    <?php foreach ($rows as $row): ?>
                                        <?php echo $row->n; ?>,
                                    <?php endforeach; ?>
                                ],
                                backgroundColor: [
                                    <?php foreach ($rows as $row): ?>
                                        'rgba(255, 99, 132, 0.2)',
                                    <?php endforeach; ?>

                                ],
                                borderColor: [
                                    <?php foreach ($rows as $row): ?>
                                        'rgba(255,99,132,1)',
                                    <?php endforeach; ?>
                                ],
                                borderWidth: 1
                            },
                            {
                                label: 'Promedio',
                                data: [
                                    <?php foreach ($rows as $row): ?>
                                        <?php echo number_format($row->finalgrade/$row->n, 2); ?>,
                                    <?php endforeach; ?>
                                ],
                                backgroundColor: [
                                    <?php foreach ($rows as $row): ?>
                                        'rgba(54, 162, 235, 0.2)',
                                    <?php endforeach; ?>
                                ],
                                borderColor: [
                                    <?php foreach ($rows as $row): ?>
                                        'rgba(54, 162, 235, 1)',
                                    <?php endforeach; ?>
                                ],
                                borderWidth: 1
                            }
                        ]
                        },
                        options: {
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero:true
                                    }
                                }]
                            }
                        }
                    });
                    </script>
                </div>
            </div>
        </div>
    <?php echo $OUTPUT->footer();  ?>
<?php
    else:
        $url = new moodle_url('/dashboard/index.php');
        redirect($url);
    endif;
/*
else:
    echo $OUTPUT->header();
    echo "<h1>Usted no poseea acceso</h1>";
    echo $OUTPUT->footer();
endif;
*/
?>
