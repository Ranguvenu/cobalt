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
 * user signup page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot.'/employees/lib.php');
require_once('lib.php');

    if (!$authplugin = signup_is_enabled()) {
    print_error('notlocalisederrormessage', 'error', '', 'Sorry, you may not use this page.');
    }
    $PAGE->set_url('/local/employees/signup.php');
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('login');
    $PAGE->requires->jquery();
    // If wantsurl is empty or /login/signup.php, override wanted URL.
    // We do not want to end up here again if user clicks "Login".
    if (empty($SESSION->wantsurl)) {
    $SESSION->wantsurl = $CFG->wwwroot . '/';
    } else {
    $wantsurl = new moodle_url($SESSION->wantsurl);
        if ($PAGE->url->compare($wantsurl, URL_MATCH_BASE)) {
        $SESSION->wantsurl = $CFG->wwwroot . '/';
        }
    }
    if (isloggedin() and !isguestuser()) {
    // Prevent signing up when already logged in.
        echo $OUTPUT->header();
        echo $OUTPUT->box_start();
    $logout = new single_button(new moodle_url('/login/logout.php',
        array('sesskey' => sesskey(), 'loginpage' => 1)), get_string('logout'), 'post');
    $continue = new single_button(new moodle_url('/'), get_string('cancel'), 'get');
        echo $OUTPUT->confirm(get_string('cannotsignup', 'error', fullname($USER)), $logout, $continue);
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
    exit;
    }
    // If verification of age and location (digital minor check) is enabled.
    if (\core_auth\digital_consent::is_age_digital_consent_verification_enabled()) {
    $cache = cache::make('core', 'presignup');
    $isminor = $cache->get('isminor');
        if ($isminor === false) {
        // The verification of age and location (minor) has not been done.
        redirect(new moodle_url('/login/verify_age_location.php'));
        } else if ($isminor === 'yes') {
        // The user that attempts to sign up is a digital minor.
        redirect(new moodle_url('/login/digital_minor.php'));
        }
    }
    // Plugins can create pre sign up requests.
    // Can be used to force additional actions before sign up such as acceptance of policies, validations, etc.

        $mform_signup = new local_employees\forms\extsignup_form();
    if ($mform_signup->is_cancelled()) {
    redirect(get_login_url());
    } else if ($user = $mform_signup->get_data()) {
    // Add missing required fields.
    $user->confirmed   = 0;
    $user->username   = $user->email;
    $user->lang        = current_language();
    $user->firstaccess = 0;
    $user->timecreated = time();
    $user->mnethostid  = $CFG->mnet_localhost_id;
    $user->secret      = random_string(15);
    $user->auth        = $CFG->registerauth;
    $domain = substr(strrchr($user->email, "@"), 1);

    $deptsql = "SELECT id,shortname,open_external FROM {local_costcenter} WHERE open_domains = :domain";
    $deptresult = $DB->get_record_sql($deptsql ,array('domain'=>$domain));
    $empid = $DB->get_record_sql('SELECT id from {user} ORDER BY id DESC LIMIT 1');
    $empid->id++;
    $user->open_employeeid =$user->firstname.$user->lastname.$empid->id;
    $user->open_external   = $deptresult->open_external;
    $user->open_costcenterid = $deptresult->id;
    $user->open_departmentid = $DB->get_field('local_costcenter','id',array('shortname'=>'all_'.$deptresult->shortname));
    echo html_writer::start_tag('div', array('class' => 'd-flex  signup_success'));
        $authplugin->user_signup($user, true);
    echo html_writer::end_tag('div');
    echo $OUTPUT->notification(get_string('registraionsuccess', 'local_employees'),'success');
    echo $OUTPUT->single_button($CFG->wwwroot , get_string('backtohome', 'local_employees'));
    echo $OUTPUT->footer();
    exit; //never reached
}
$newaccount = get_string('newaccount');
$login      = get_string('login');

$PAGE->navbar->add($login);
$PAGE->navbar->add($newaccount);

$PAGE->set_title($newaccount);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

if ($mform_signup instanceof renderable) {
    // Try and use the renderer from the auth plugin if it exists.
    try {
        $renderer = $PAGE->get_renderer('auth_' . $authplugin->authtype);
    } catch (coding_exception $ce) {
        // Fall back on the general renderer.
        $renderer = $OUTPUT;
    }
    echo $renderer->render($mform_signup);
} else {
    // Fall back for auth plugins not using renderables.
        $sitename = format_string($SITE->fullname, true, ['context' => context_course::instance(SITEID)]);
        $logopath = $OUTPUT->loginlogo();
        if($logopath) {
          $return =  '<img class = "login_logo" src="'.$logopath.'" title="'.$sitename.'" alt="'.$sitename.'"/>';
        } else {
            $return = '<h2 class="card-header">'.$sitename.'</h2>';
        }
    echo '
<div class="container-fluid ">
    <div class="row justify-content-md-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <div class="card-title text-xs-center mt-5">
                            '.$return.'
                    </div>
                    <div class="signup-form">';
                        $mform_signup->display();
                    echo   '<div class="have_account">
                            Have an account?<span><a href="'.$CFG->wwwroot.'/login/index.php"> Login here</a></span>
                            <div class="text-muted mt-3">
                                <div class="followus">Follow us on :</div>
                                <div class="media_icons">
                                    '.$OUTPUT->footer_social_icons().'
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7 p-0">
        </div>
    </div>
</div>
<script>
$(".password-visible").click(function(){

    if($(this).parent().find("input").attr("type")=="text"){
        $(this).html("<i class=\'fa fa-eye\'></i>");
        $(this).parent().find("input").attr("type","password");
    }else{
        $(this).html("<i class=\'fa fa-eye-slash\'></i>");
        $(this).parent().find("input").attr("type","text");
    }

});
</script>
';
}
echo $OUTPUT->footer();
