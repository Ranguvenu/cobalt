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
 * This file contains the news item block class, based upon block_base.
 *
 * @package    block_news_items
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class block_news_items
 *
 * @package    block_news_items
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_news_items extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_news_items');
    }

    function get_content() {
        global $CFG, $USER, $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }


        if ($this->page->course->newsitems) {   // Create a nice listing of recent postings

            require_once($CFG->dirroot.'/mod/forum/lib.php');   // We'll need this

            $text = '';

            if (!$forum = forum_get_course_forum($this->page->course->id, 'news')) {
                return '';
            }

            $modinfo = get_fast_modinfo($this->page->course);
            if (empty($modinfo->instances['forum'][$forum->id])) {
                return '';
            }
            $cm = $modinfo->instances['forum'][$forum->id];

            if (!$cm->uservisible) {
                return '';
            }

            $context = context_module::instance($cm->id);

        /// User must have perms to view discussions in that forum
            if (!has_capability('mod/forum:viewdiscussion', $context)) {
                return '';
            }

        /// First work out whether we can post to this group and if so, include a link
            $groupmode    = groups_get_activity_groupmode($cm);
            $currentgroup = groups_get_activity_group($cm, true);

            if (forum_user_can_post_discussion($forum, $currentgroup, $groupmode, $cm, $context)) {
                $text .= '<div class="newlink"><a href="'.$CFG->wwwroot.'/mod/forum/post.php?forum='.$forum->id.'">'.
                          get_string('addanewtopic', 'forum').'</a>...</div>';
            }

        /// Get all the recent discussions we're allowed to see

            // This block displays the most recent posts in a forum in
            // descending order. The call to default sort order here will use
            // that unless the discussion that post is in has a timestart set
            // in the future.
            // This sort will ignore pinned posts as we want the most recent.

          $sort = forum_get_default_sort_order(true, 'p.modified', 'd', false);
          $discussions='';
            if (! $discussions = forum_get_discussions($cm, $sort, false,
                                                        -1,$currentgroup, $this->page->course->newsitems,
                                                        false, -1, 0, FORUM_POSTS_ALL_USER_GROUPS) ) {
                $text .= '('.get_string('nonews', 'forum').')';
                $this->content->text = $text;
                return $this->content;
            }

        /// Actually create the listing now

            $strftimerecent = get_string('strftimerecent');
            $strmore = get_string('more', 'forum');

        /// Accessibility: markup as a list.
            $text .= "\n<ul class='unlist'>\n";
             $i=0;$more = '';
 

        foreach ($discussions as $discussion) {
            $discussion->subject = $discussion->name;

            $messages=$DB->get_record_sql("SELECT fp.message FROM {forum_posts} as fp 
                JOIN {forum_discussions} as fd ON fp.discussion=fd.id 
                WHERE fp.discussion=$discussion->id "); 

            $discussion->subject = format_string($discussion->subject, true, $forum->course);

            $posttime = $discussion->modified;
                if (!empty($CFG->forum_enabletimedposts) && ($discussion->timestart > $posttime)) {
                    $posttime = $discussion->timestart;
                }
                $text .= '<li class="post">'.
                '<div class="row test_announcments">'.
                    '<div class="col-lg-3">
                        <div class="d-flex text-left">
                            <div class="w-100 d-flex flex-column justify-content-between date_label">
                                <div class="info d-none"><a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussion->discussion.'">'.$discussion->subject.'</a></div>
                                <div class="date">
                                '.userdate($posttime, $strftimerecent).'</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9  text-right">
                        <div class="text-right">
                            <div class="description_text">'.$messages->message.'</div>
                            <div class="details text-left"><a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussion->discussion.'">See details</a></div>
                        </div>
                    </div>'.

                 
                '</div>'.
                         "</li>";
                    $i++;
                if($USER->open_type == 1){
                    if($i == 3){
                        break;
                    }
                }else{
                    if($i == 3){
                        break;
                    }
                }

            }

            $text .= "</ul>";
            if(count($discussions) > 2){
                
            $more = '<a href="'.$CFG->wwwroot.'/mod/forum/view.php?f='.$forum->id.'">'.get_string('seeall', 'block_news_items').'</a> ...';
            }
            $this->content->text = $text;
           $this->content->footer = $more;

        /// If RSS is activated at site and forum level and this forum has rss defined, show link
            if (isset($CFG->enablerssfeeds) && isset($CFG->forum_enablerssfeeds) &&
                $CFG->enablerssfeeds && $CFG->forum_enablerssfeeds && $forum->rsstype && $forum->rssarticles) {
                require_once($CFG->dirroot.'/lib/rsslib.php');   // We'll need this
                if ($forum->rsstype == 1) {
                    $tooltiptext = get_string('rsssubscriberssdiscussions','forum');
                } else {
                    $tooltiptext = get_string('rsssubscriberssposts','forum');
                }
                if (!isloggedin()) {
                    $userid = $CFG->siteguest;
                } else {
                    $userid = $USER->id;
                }

                $this->content->footer .= '<br />'.rss_get_link($context->id, $userid, 'mod_forum', $forum->id, $tooltiptext);
            }

        }

        return $this->content;
    }
}


