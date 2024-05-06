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

namespace theme_bloom\output;

use moodle_url;
use html_writer;
use get_string;
use context_system;
use core_component;
use user_picture;
use component_action;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_bloom
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \core_renderer {
    
    /**
     * Return the site's logo URL, if any.
     *
     * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
     * @param int $maxheight The maximum height, or null when the maximum height does not matter.
     * @return moodle_url|false
     */
    public function get_logo_url($maxwidth = null, $maxheight = 200) {
        global $CFG;
        $logo = get_config('core_admin', 'logo');
        if (empty($logo)) {
            $url = new moodle_url('/theme/bloom/pix/CobaltLogo.png');
            return $url;
        }

        // 200px high is the default image size which should be displayed at 100px in the page to account for retina displays.
        // It's not worth the overhead of detecting and serving 2 different images based on the device.

        // Hide the requested size in the file path.
        $filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';

        // Use $CFG->themerev to prevent browser caching when the file changes.
        return moodle_url::make_pluginfile_url(context_system::instance()->id, 'core_admin', 'logo', $filepath,
            theme_get_revision(), $logo);
    }

public function get_compact_logo_url($maxwidth = 300, $maxheight = 300) {
    global $USER, $DB;
    if($USER->id == 0){
        $logo = get_config('core_admin', 'logocompact');
        if (empty($logo)) {
            $url = new moodle_url('/theme/bloom/pix/CobaltLogo.png');
            return $url;
        }

        // Hide the requested size in the file path.
        $filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';

        // Use $CFG->themerev to prevent browser caching when the file changes.
        return moodle_url::make_pluginfile_url(context_system::instance()->id, 'core_admin', 'logocompact', $filepath,
            theme_get_revision(), $logo);
    }
    if(!empty($USER->open_costcenterid)){
        $costcenterid = $DB->get_field('local_costcenter', 'costcenter_logo', array('id'=> $USER->open_costcenterid));
    }
        $logopath = $this->page->theme->setting_file_url('logo', 'logo');
        // $logopath = get_config('core_admin', 'logocompact');
        if(!is_siteadmin()){
            if(!empty($costcenterid)){
                // $costcenterlogopath = costcenter_logo($costcenterid);
                 $costcenterlogopath = false;
                $sql = "SELECT * FROM {files} WHERE itemid = :logo  AND filename != '.' ORDER BY id DESC";
                $costcenterlogorecord = $DB->get_record_sql($sql, array('logo' => $costcenterid), 1);

                if (!empty($costcenterlogorecord)) {
                    if ($costcenterlogorecord->filearea == "costcenter_logo") {
                        $costcenterlogopath = moodle_url::make_pluginfile_url(
                                                                $costcenterlogorecord->contextid,
                                                                $costcenterlogorecord->component,
                                                                $costcenterlogorecord->filearea,
                                                                $costcenterlogorecord->itemid,
                                                                $costcenterlogorecord->filepath,
                                                                $costcenterlogorecord->filename
                                                            );
                    }
                }
                if(!empty($costcenterlogopath)) {
                    $logopath = $costcenterlogopath;
                } else {
                    // $logo = get_config('core_admin', 'logocompact');
                    // if (!empty($logo)) {
                    //     $url = new moodle_url('/theme/bloom/pix/CobaltLogo.png');
                    //     $logopath = $url;
                    // }
                    $logo = get_config('core_admin', 'logocompact');
                    if (empty($logo)) {
                        $url = new moodle_url('/theme/bloom/pix/CobaltLogo.png');
                        return $url;
                    }

                    // Hide the requested size in the file path.
                    $filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';

                    // Use $CFG->themerev to prevent browser caching when the file changes.
                    return moodle_url::make_pluginfile_url(context_system::instance()->id, 'core_admin', 'logocompact', $filepath,
                        theme_get_revision(), $logo);


                }
            }
        } else {
            $logo = get_config('core_admin', 'logocompact');
            if (empty($logo)) {
                $url = new moodle_url('/theme/bloom/pix/CobaltLogo.png');
                return $url;
            }

            // Hide the requested size in the file path.
            $filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';

            // Use $CFG->themerev to prevent browser caching when the file changes.
            return moodle_url::make_pluginfile_url(context_system::instance()->id, 'core_admin', 'logocompact', $filepath,
                theme_get_revision(), $logo);
        }

    return $logopath;
}

    /**
     * Return the site's compact logo URL, if any.
     *
     * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
     * @param int $maxheight The maximum height, or null when the maximum height does not matter.
     * @return moodle_url|false
     */
    // public function get_compact_logo_url($maxwidth = 300, $maxheight = 300) {
    //     global $CFG;
    //     $logo = get_config('core_admin', 'logocompact');
    //     if (empty($logo)) {
    //         $url = new moodle_url('/theme/bloom/pix/CobaltLogo.png');
    //         return $url;
    //     }

    //     // Hide the requested size in the file path.
    //     $filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';

    //     // Use $CFG->themerev to prevent browser caching when the file changes.
    //     return moodle_url::make_pluginfile_url(context_system::instance()->id, 'core_admin', 'logocompact', $filepath,
    //         theme_get_revision(), $logo);
    // }
    
    public function edit_button(moodle_url $url) {
        if ($this->page->theme->haseditswitch) {
            return;
        }
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }
        $button = new \single_button($url, $editstring, 'post', ['class' => 'btn btn-primary']);
        return $this->render_single_button($button);
    }

    /**
     * Renders the "breadcrumb" for all pages in bloom.
     *
     * @return string the HTML for the navbar.
     */
    public function navbar(): string {
        $newnav = new \theme_bloom\bloomnavbar($this->page);
        return $this->render_from_template('core/navbar', $newnav);
    }

    /**
     * Renders the context header for the page.
     *
     * @param array $headerinfo Heading information.
     * @param int $headinglevel What 'h' level to make the heading.
     * @return string A rendered context header.
     */
    public function context_header($headerinfo = null, $headinglevel = 1): string {
        global $DB, $USER, $CFG, $SITE;
        require_once($CFG->dirroot . '/user/lib.php');
        $context = $this->page->context;
        $heading = null;
        $imagedata = null;
        $subheader = null;
        $userbuttons = null;

        // Make sure to use the heading if it has been set.
        if (isset($headerinfo['heading'])) {
            $heading = $headerinfo['heading'];
        } else {
            $heading = $this->page->heading;
        }

        // The user context currently has images and buttons. Other contexts may follow.
        if ((isset($headerinfo['user']) || $context->contextlevel == CONTEXT_USER) && $this->page->pagetype !== 'my-index') {
            if (isset($headerinfo['user'])) {
                $user = $headerinfo['user'];
            } else {
                // Look up the user information if it is not supplied.
                $user = $DB->get_record('user', array('id' => $context->instanceid));
            }

            // If the user context is set, then use that for capability checks.
            if (isset($headerinfo['usercontext'])) {
                $context = $headerinfo['usercontext'];
            }

            // Only provide user information if the user is the current user, or a user which the current user can view.
            // When checking user_can_view_profile(), either:
            // If the page context is course, check the course context (from the page object) or;
            // If page context is NOT course, then check across all courses.
            $course = ($this->page->context->contextlevel == CONTEXT_COURSE) ? $this->page->course : null;

            if (user_can_view_profile($user, $course)) {
                // Use the user's full name if the heading isn't set.
                if (empty($heading)) {
                    $heading = fullname($user);
                }

                $imagedata = $this->user_picture($user, array('size' => 100));

                // Check to see if we should be displaying a message button.
                if (!empty($CFG->messaging) && has_capability('moodle/site:sendmessage', $context)) {
                    $userbuttons = array(
                        'messages' => array(
                            'buttontype' => 'message',
                            'title' => get_string('message', 'message'),
                            'url' => new moodle_url('/message/index.php', array('id' => $user->id)),
                            'image' => 'message',
                            'linkattributes' => \core_message\helper::messageuser_link_params($user->id),
                            'page' => $this->page
                        )
                    );

                    if ($USER->id != $user->id) {
                        $iscontact = \core_message\api::is_contact($USER->id, $user->id);
                        $contacttitle = $iscontact ? 'removefromyourcontacts' : 'addtoyourcontacts';
                        $contacturlaction = $iscontact ? 'removecontact' : 'addcontact';
                        $contactimage = $iscontact ? 'removecontact' : 'addcontact';
                        $userbuttons['togglecontact'] = array(
                                'buttontype' => 'togglecontact',
                                'title' => get_string($contacttitle, 'message'),
                                'url' => new moodle_url('/message/index.php', array(
                                        'user1' => $USER->id,
                                        'user2' => $user->id,
                                        $contacturlaction => $user->id,
                                        'sesskey' => sesskey())
                                ),
                                'image' => $contactimage,
                                'linkattributes' => \core_message\helper::togglecontact_link_params($user, $iscontact),
                                'page' => $this->page
                            );
                    }

                    $this->page->requires->string_for_js('changesmadereallygoaway', 'moodle');
                }
            } else {
                $heading = null;
            }
        }

        $prefix = null;
        if ($context->contextlevel == CONTEXT_MODULE) {
            if ($this->page->course->format === 'singleactivity') {
                $heading = $this->page->course->fullname;
            } else {
                $heading = $this->page->cm->get_formatted_name();
                $imagedata = $this->pix_icon('monologo', '', $this->page->activityname, ['class' => 'activityicon']);
                $purposeclass = plugin_supports('mod', $this->page->activityname, FEATURE_MOD_PURPOSE);
                $purposeclass .= ' activityiconcontainer';
                $purposeclass .= ' modicon_' . $this->page->activityname;
                $imagedata = html_writer::tag('div', $imagedata, ['class' => $purposeclass]);
                $prefix = get_string('modulename', $this->page->activityname);
            }
        }


        $contextheader = new \context_header($heading, $headinglevel, $imagedata, $userbuttons, $prefix);
        return $this->render_context_header($contextheader);
    }

     /**
      * Renders the header bar.
      *
      * @param context_header $contextheader Header bar object.
      * @return string HTML for the header bar.
      */
    protected function render_context_header(\context_header $contextheader) {

        // Generate the heading first and before everything else as we might have to do an early return.
        if (!isset($contextheader->heading)) {
            $heading = $this->heading($this->page->heading, $contextheader->headinglevel, 'h2');
        } else {
            $heading = $this->heading($contextheader->heading, $contextheader->headinglevel, 'h2');
        }

        // All the html stuff goes here.
        $html = html_writer::start_div('page-context-header');

        // Image data.
        if (isset($contextheader->imagedata)) {
            // Header specific image.
            $html .= html_writer::div($contextheader->imagedata, 'page-header-image mr-2');
        }

        // Headings.
        if (isset($contextheader->prefix)) {
            $prefix = html_writer::div($contextheader->prefix, 'text-muted text-uppercase small line-height-3');
            $heading = $prefix . get_string("sitenews", 'theme_bloom', $contextheader->heading);
        }
        $html .= html_writer::tag('div', $heading, array('class' => 'page-header-headings'));

        // Buttons.
        if (isset($contextheader->additionalbuttons)) {
            $html .= html_writer::start_div('btn-group header-button-group');
            foreach ($contextheader->additionalbuttons as $button) {
                if (!isset($button->page)) {
                    // Include js for messaging.
                    if ($button['buttontype'] === 'togglecontact') {
                        \core_message\helper::togglecontact_requirejs();
                    }
                    if ($button['buttontype'] === 'message') {
                        \core_message\helper::messageuser_requirejs();
                    }
                    $image = $this->pix_icon($button['formattedimage'], $button['title'], 'moodle', array(
                        'class' => 'iconsmall',
                        'role' => 'presentation'
                    ));
                    $image .= html_writer::span($button['title'], 'header-button-title');
                } else {
                    $image = html_writer::empty_tag('img', array(
                        'src' => $button['formattedimage'],
                        'role' => 'presentation'
                    ));
                }
                $html .= html_writer::link($button['url'], html_writer::tag('span', $image), $button['linkattributes']);
            }
            $html .= html_writer::end_div();
        }
        $html .= html_writer::end_div();

        return $html;
    }

    /**
     * See if this is the first view of the current cm in the session if it has fake blocks.
     *
     * (We track up to 100 cms so as not to overflow the session.)
     * This is done for drawer regions containing fake blocks so we can show blocks automatically.
     *
     * @return boolean true if the page has fakeblocks and this is the first visit.
     */
    public function firstview_fakeblocks(): bool {
        global $SESSION;

        $firstview = false;
        if ($this->page->cm) {
            if (!$this->page->blocks->region_has_fakeblocks('side-pre')) {
                return false;
            }
            if (!property_exists($SESSION, 'firstview_fakeblocks')) {
                $SESSION->firstview_fakeblocks = [];
            }
            if (array_key_exists($this->page->cm->id, $SESSION->firstview_fakeblocks)) {
                $firstview = false;
            } else {
                $SESSION->firstview_fakeblocks[$this->page->cm->id] = true;
                $firstview = true;
                if (count($SESSION->firstview_fakeblocks) > 100) {
                    array_shift($SESSION->firstview_fakeblocks);
                }
            }
        }
        return $firstview;
    }

    public function quickaccess_links() {
        global $DB, $CFG, $USER, $PAGE;
        $systemcontext = context_system::instance();
        $core_component = new core_component();
        $block_content = '';
        $local_pluginlist = $core_component::get_plugin_list('local');
        $block_pluginlist = $core_component::get_plugin_list('block');

        $block_content .= html_writer::start_tag('ul', array('class'=>'quickpop_over_ul m-0'));
            //======= Dasboard link ========//  
            // $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_dashboard', 'class'=>'pull-left user_nav_div dashboard'));
            //     $button1 = html_writer::link($CFG->wwwroot, '<i class="fa fa-home" aria-hidden="true"></i><span class="user_navigation_link_text">'.get_string('leftmenu_dashboard', 'theme_bloom').'</span>', array('class'=>'user_navigation_link'));
            //     $block_content .= $button1;
            // $block_content .= html_writer::end_tag('li');

            //=======Leader Dasboard link ========// 
//             $gamificationb_plugin_exist = $core_component::get_plugin_directory('block', 'gamification');
//             $gamificationl_plugin_exist = $core_component::get_plugin_directory('local', 'gamification');
//             if($gamificationl_plugin_exist && $gamificationb_plugin_exist && (has_capability('local/gamification:view
// ',$systemcontext) || is_siteadmin() )){
//                 $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_gamification_leaderboard', 'class'=>'pull-left user_nav_div notifications'));
//                 $gamification_url = new moodle_url('/blocks/gamification/dashboard.php');
//                 $gamification = html_writer::link($gamification_url, '<i class="fa fa-trophy"></i><span class="user_navigation_link_text">'.get_string('leftmenu_gmleaderboard','theme_bloom').'</span>',array('class'=>'user_navigation_link'));
//                 $block_content .= $gamification;
//                 $block_content .= html_writer::end_tag('li');
//             }
            $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_home', 'class'=>'pull-left user_nav_div adminstration'));
                    $admin_url = new moodle_url('/my');
                    $admin = html_writer::link($admin_url, '<span class="home_structure_icon dypatil_cmn_icon icon"></span><span class="user_navigation_link_text">'.get_string('home','theme_bloom').'</span>',array('class'=>'user_navigation_link'));
                    $block_content .= $admin;
                $block_content .= html_writer::end_tag('li');
            $pluginnavs = array();
            foreach($local_pluginlist as $key => $local_pluginname){
                if(file_exists($CFG->dirroot.'/local/'.$key.'/lib.php')){
                    require_once($CFG->dirroot.'/local/'.$key.'/lib.php');
                    $functionname = 'local_'.$key.'_leftmenunode';
                    if(function_exists($functionname)){
                        $data =  (array)$functionname();
                         foreach($data as  $key => $val){
                            $pluginnavs[$key][] = $val;
                          }
                        
                    }
                }
            }
            // ksort($pluginnavs);
            // foreach($pluginnavs as $pluginnav){
            //     foreach($pluginnav  as $key => $value){
            //             $data = $value;
            //             $block_content .= $data;
            //     }
            // }

            foreach($block_pluginlist as $key => $local_pluginname){
                 if(file_exists($CFG->dirroot.'/blocks/'.$key.'/lib.php')){
                    require_once($CFG->dirroot.'/blocks/'.$key.'/lib.php');
                    $functionname = 'block_'.$key.'_leftmenunode';
                    if(function_exists($functionname)){
                    // $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_dashboard', 'class'=>'pull-left user_nav_div dashboard row-fluid '));
                        $data = (array)$functionname();
                        foreach($data as  $key => $val){
                            $pluginnavs[$key][] = $val;
                        }
                    // $block_content .= html_writer::end_tag('li');
                    }
                }
            }
            ksort($pluginnavs);   
            foreach($pluginnavs as $pluginnav){
                foreach($pluginnav  as $key => $value){
                        $data = $value;
                        $block_content .= $data;
                }
            }         
            /*Site Administration Link*/
            if(is_siteadmin()){
                // $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_adminstration', 'class'=>'pull-left user_nav_div adminstration'));
                //     $admin_url = new moodle_url('/repository/customfiles/file.php');
                //     $admin = html_writer::link($admin_url, '<span class="image_repository_icon dypatil_cmn_icon icon"></span><span class="user_navigation_link_text">'.get_string('repositoryfiles','theme_bloom').'</span>',array('class'=>'user_navigation_link'));
                //     $block_content .= $admin;
                // $block_content .= html_writer::end_tag('li');
                $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_adminstration', 'class'=>'pull-left user_nav_div adminstration'));
                    $admin_url = new moodle_url('/admin/search.php');
                    $admin = html_writer::link($admin_url, '<span class="site_admn_wht_structure_icon dypatil_cmn_icon icon"></span><span class="user_navigation_link_text">'.get_string('leftmenu_adminstration','theme_bloom').'</span>',array('class'=>'user_navigation_link'));
                    $block_content .= $admin;
                $block_content .= html_writer::end_tag('li');

            }
            // if(has_capability('block/proposals:dashboardview',$systemcontext) && !is_siteadmin()) {
            //     $block_content .= html_writer::start_tag('li', array('id'=> 'id_leftmenu_home', 'class'=>'pull-left user_nav_div adminstration'));
            //         $admin_url = new moodle_url('/blocks/proposals/view.php');
            //         $admin = html_writer::link($admin_url, '<span class="course_wht_structure_icon dypatil_cmn_icon icon"></span><span class="user_navigation_link_text">'.get_string('proposals','theme_bloom').'</span>',array('class'=>'user_navigation_link'));
            //         $block_content .= $admin;
            //     $block_content .= html_writer::end_tag('li');
            // }
        $block_content .= html_writer::end_tag('ul');
        
        return $block_content;
    }
    /**
     * Overides the core user_picture function in output_renderers.php
     * overideen to show wavatar instead of user image
     *
     * @return string 
     */
    public function user_picture(\stdClass $user, array $options = null) {
        global $CFG, $DB;
        $userpicture = new user_picture($user);
        $core_component = new core_component();
        foreach ((array)$options as $key=>$value) {
            if (property_exists($userpicture,$key)) {
                $userpicture->$key = $value;
            }
        }
        $wavatar_plugin_exist = $core_component::get_plugin_directory('local', 'wavatar');
        if(!empty($wavatar_plugin_exist)){
            if(!$user->picture && $myavatar = $DB->get_field('local_wavatar_info', 'path', array('userid' => $user->id))){
                $defaulturl = $CFG->wwwroot . '/local/wavatar/svgavatars/ready-avatars/'.$myavatar.''; // default image
                $defaultpic = '<img src='.$defaulturl.' alt="Picture of '.$user->firstname.' '.$user->lastname.'" title="Picture of '.$user->firstname.' '.$user->lastname.'" class="'.$userpicture->class.'" width = "'.$userpicture->size.'" height = "'.$userpicture->size.'" />';
                return $defaultpic;
            }
        }
        // var_dump($userpicture);
        // exit;
        if ($user->picture == 0 || $user->picture > 0){
            return $this->render($userpicture);
        }
    }

     /**
     * Internal implementation of user image rendering.
     *
     * @param user_picture $userpicture
     * @return string
     */
    protected function render_user_picture(user_picture $userpicture) {
        global $CFG;

        $user = $userpicture->user;
        $canviewfullnames = has_capability('moodle/site:viewfullnames', $this->page->context);

        $alt = '';
        if ($userpicture->alttext) {
            if (!empty($user->imagealt)) {
                $alt = $user->imagealt;
            }
        }

        if (empty($userpicture->size)) {
            $size = 35;
        } else if ($userpicture->size === true or $userpicture->size == 1) {
            $size = 100;
        } else {
            $size = $userpicture->size;
        }

        $class = $userpicture->class;

        if ($user->picture == 0) {
            $class .= ' defaultuserpic';
        }

        $src = $userpicture->get_url($this->page, $this);

        $attributes = array('src' => $src, 'class' => $class, 'width' => $size, 'height' => $size);
        if (!$userpicture->visibletoscreenreaders) {
            $alt = '';
        }
        $attributes['alt'] = $alt;

        if (!empty($alt)) {
            $attributes['title'] = $alt;
        }

        // Get the image html output first, auto generated based on initials if one isn't already set.
        // if ($user->picture == 0 && empty($CFG->enablegravatar) && !defined('BEHAT_SITE_RUNNING')) {
        //     $output = html_writer::tag('span', mb_substr($user->firstname, 0, 1) . mb_substr($user->lastname, 0, 1),
        //         ['class' => 'userinitials size-' . $size]);
        // } else {
            $output = html_writer::empty_tag('img', $attributes);
        // }

        // Show fullname together with the picture when desired.
        if ($userpicture->includefullname) {
            $output .= fullname($userpicture->user, $canviewfullnames);
        }

        if (empty($userpicture->courseid)) {
            $courseid = $this->page->course->id;
        } else {
            $courseid = $userpicture->courseid;
        }
        if ($courseid == SITEID) {
            $url = new moodle_url('/user/profile.php', array('id' => $user->id));
        } else {
            $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
        }

        // Then wrap it in link if needed. Also we don't wrap it in link if the link redirects to itself.
        if (!$userpicture->link ||
                ($this->page->has_set_url() && $this->page->url == $url)) { // Protect against unset page->url.
            return $output;
        }

        $attributes = array('href' => $url, 'class' => 'd-inline-block aabtn');
        if (!$userpicture->visibletoscreenreaders) {
            $attributes['tabindex'] = '-1';
            $attributes['aria-hidden'] = 'true';
        }

        if ($userpicture->popup) {
            $id = html_writer::random_id('userpicture');
            $attributes['id'] = $id;
            $this->add_action_handler(new component_action('click', $url), $id);
           
        }

        return html_writer::tag('a', $output, $attributes);
    }

}
