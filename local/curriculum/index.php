<?php
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

/**
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage curriculum
 * @copyright  2022 Eabyas Info Solutions <www.eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

require_login();

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 5, PARAM_INT);

$systemcontext = context_system::instance();
$PAGE->set_pagelayout("standard");
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/curriculum/index.php');
$PAGE->set_heading(get_string('manage_curriculum', 'local_curriculum'));
$PAGE->set_title(get_string('pluginname', 'local_curriculum'));
$PAGE->requires->css('/local/curriculum/css/jquery.dataTables.min.css');
$PAGE->requires->js_call_amd('local_curriculum/ajaxforms', 'load', array());
$PAGE->requires->js_call_amd('local_costcenter/newcostcenter', 'load', array());
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('manage_curriculum', 'local_curriculum'));

$renderer = $PAGE->get_renderer('local_curriculum');

require_once($CFG->dirroot . '/local/courses/filters_form.php');
require_once('lib.php');

echo $OUTPUT->header();
$renderer->top_action_buttons();
    $filterparams = $renderer->get_catelog_curriculums(true);
    if(is_siteadmin() || has_capability('local/costcenter:manage_multiorganizations', $systemcontext)){
        $mform = new filters_form(null, array('filterlist' => array('dependent_fields','curriculum'), 'filterparams' => $filterparams));
    } else if (has_capability('local/costcenter:manage_ownorganization', $systemcontext)) {
        $mform = new filters_form(null, array('filterlist' => array('dependent_fields', 'curriculum'), 'filterparams' => $filterparams));
    } else if (has_capability('local/costcenter:manage_owndepartments', $systemcontext)) {
        $mform = new filters_form(null, array('filterlist' => array('dependent_fields', 'curriculum'), 'filterparams' => $filterparams));
    } else if (has_capability('local/costcenter:manage_ownsubdepartments', $systemcontext)) {
        $mform = new filters_form(null, array('filterlist' => array('curriculum'), 'filterparams' => $filterparams));
    }
    
    echo '<a class="btn-link btn-sm d-flex align-items-center filter_btn" href="javascript:void(0);" data-toggle="collapse" data-target="#local_curriculum-filter_collapse" aria-expanded="false" aria-controls="local_curriculum-filter_collapse">
               <span class="filter mr-2">Filters</span>
            <i class="m-0 fa fa-sliders fa-2x" aria-hidden="true"></i>

          </a>';
    echo  '<div class="collapse '.$show.'" id="local_curriculum-filter_collapse">
                <div id="filters_form" class="card card-body p-2">';
                    $mform->display();
    echo        '</div>
            </div>';

// $filterparams['submitid'] = 'form#filteringform';
// echo $OUTPUT->render_from_template('local_costcenter/global_filter', $filterparams);

echo $renderer->curriculum_view($filterparams);
echo $OUTPUT->footer();
