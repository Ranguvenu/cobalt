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
 * Strings for component 'url', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    mod_url
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['clicktoopen'] = 'Click {$a} link to open resource.';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configframesize'] = 'When a web page or an uploaded file is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['configrolesinparams'] = 'Enable if you want to include localized role names in list of available parameter variables.';
$string['configsecretphrase'] = 'This secret phrase is used to produce encrypted code value that can be sent to some servers as a parameter.  The encrypted code is produced by an md5 value of the current user IP address concatenated with your secret phrase. ie code = md5(IP.secretphrase). Please note that this is not reliable because IP address may change and is often shared by different computers.';
$string['contentheader'] = 'Content';
$string['createurl'] = 'Create a URL';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the URL file type and whether the browser allows embedding, determines how the URL is displayed. Options may include:

* Automatic - The best display option for the URL is selected automatically
* Embed - The URL is displayed within the page below the navigation bar together with the URL description and any blocks
* Open - Only the URL is displayed in the browser window
* In pop-up - The URL is displayed in a new browser window without menus or an address bar
* In frame - The URL is displayed within a frame below the navigation bar and URL description
* New window - The URL is displayed in a new browser window with menus and an address bar';
$string['displayselectexplain'] = 'Choose display type, unfortunately not all types are suitable for all URLs.';
$string['externalurl'] = 'Browse Video';
$string['framesize'] = 'Frame height';
$string['invalidstoredurl'] = 'Cannot display this resource, URL is invalid.';
$string['chooseavariable'] = 'Choose a variable...';
$string['invalidurl'] = 'Entered URL is invalid';
$string['modulename'] = 'stream';
$string['modulename_help'] = 'This Stream module streams your media content to your Moodle users. The media includes video and audio types. 
The tool streams video files in HLS format. So, you don’t feel like video-buffering, but you enjoy yourself with uninterrupted streaming with bit by bit loading. Just like your YouTube.  Keep the video files in different view formats like – Thumbnail or Directory.  
The same holds with the audio type. 
So create content specific to an organization, role, and user. 
With the APIs from the tool and a generated token from your Moodle LMS, you can sync the streaming application and the LMS, two ways. That way, you sync your Moodle LMS with the tool and the tool with your LMS. 
Use the tool on-premises or on the cloud. 
One of the top advantages of this tool is to reduce the load on your browser while fetching the videos. It has a repository from where you can upload files for streaming. And the contents are super safe as there is no scope for data sharing.';
$string['modulename_link'] = 'mod/stream/view';
$string['modulenameplural'] = 'stream';
$string['page-mod-url-x'] = 'Any URL module page';
$string['parameterinfo'] = '&amp;parameter=variable';
$string['parametersheader'] = 'URL variables';
$string['parametersheader_help'] = 'Some internal Moodle variables may be automatically appended to the URL. Type your name for the parameter into each text box(es) and then select the required matching variable.';
$string['pluginadministration'] = 'URL module administration';
$string['pluginname'] = 'stream';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printintro'] = 'Display URL description';
$string['printintroexplain'] = 'Display URL description below content? Some display types may not display description even if enabled.';
$string['rolesinparams'] = 'Include role names in parameters';
$string['search:activity'] = 'stream';
$string['serverurl'] = 'Server URL';
$string['url:addinstance'] = 'Add a new URL resource';
$string['url:view'] = 'View URL';
$string['width']='Width';
$string['height']='Height';
$string['streamanalyticsuser']='stream Analytics User';
$string['streamanalyticsemail']='stream Analytics user Email';
$string['appearence']= 'Appearence';

$string['specificstar'] = '{$a} Star';
$string['postcomment'] = 'Post Review';
$string['reviews'] = 'Reviews';
$string['reviews_for'] = 'Reviews for "{$a}"';
$string['writereview'] = 'Write a Review!';
$string['enable_reviews'] = 'Enable Reviews';
$string['configlocal_review_help'] = 'Enable reviews on the modules';

$string['report'] = 'Report';
$string['reports'] = 'Reports';
$string['streamreports'] = 'Stream Reports';

$string['toplikes'] = 'Most liked';
$string['topratings'] = 'Top rated';
$string['topviews'] = 'Most viewed';

$string['fivemins'] = 'Most liked ( > 5 minutes)';
$string['fivetotenmins'] = 'Most liked ( > 5 minutes AND < 10 minutes)';
$string['abovetenmins'] = 'Most liked ( > 10 minutes)';
$string['activevideos'] = 'Active/Total Videos';

$string['streamedvideos'] = 'Streamed Minutes';
$string['totalviews'] = 'Total Views';
$string['likesdislikes'] = 'Likes/Dislikes';
$string['ratings'] = 'Ratings';
$string['uploadedvideos'] = 'Videos';
$string['uploadvideo'] = 'Upload Video';
$string['organization'] = 'Organization';
$string['title'] = 'Title';
$string['tags'] = 'Tags';
$string['videodescription'] = 'Description';
$string['titlerequired'] = 'Required';
$string['filepath'] = 'Video';
$string['filepathrequired'] = 'Video Required';
$string['thumbnail'] = 'Thumbnail';
$string['advancedfields'] = 'Advanced Fields';

$string['standard'] = 'standard';
$string['url'] = 'url';
$string['likes'] = 'Likes';
$string['rating'] = 'Rating';
$string['views'] = 'Views';

$string['picture'] = 'picture of ';
$string['user'] = 'User ';
$string['email'] = 'Email ';
$string['rated'] = 'Rated ';
$string['lastviewedon'] = ' Last Viewed on ';
$string['likedon'] = 'Liked on ';
$string['view'] = 'View ';
$string['lastviewed'] = 'Last Viewed ';
$string['video'] = 'Video ';
$string['date'] = 'Date ';
$string['table'] = 'table';

$string['likesbelowfive'] = 'Likes based on duration(below 5min)';
$string['likesbetweenfiveten'] = 'Likes based on duration(between 5 - 10min)';
$string['likesaboveten'] = 'Likes based on duration(above 10min)';
$string['videoname'] = 'Video name';

$string['like'] = 'Like';
$string['dislike'] = 'Dislike';

$string['browsevideo'] = 'Browse video';
$string['required'] = 'Required';
$string['selectvideo'] = 'Select video';

$string['week'] = 'Week';
$string['month'] = 'Month';
$string['year'] = 'Year';
$string['custom'] = 'Custom';
$string['all'] = 'All';
$string['startdateenddate'] = 'Start Date - End Date';
$string['filter'] = 'Filter';
$string['activities'] = 'Activities : ';

// summaryreport
$string['videossummary'] = 'Videos summary';
$string['course'] = 'Course';
$string['averagetime'] = 'Average Time';
$string['uploadedon'] = 'Uploaded On';
$string['uploadedby'] = 'Uploaded By';

// topReports
$string['graph'] = 'Graph';
$string['reporttable'] = 'Report table';
$string['activitystatus'] = 'Activity status';
$string['status'] = 'Status';
$string['startedon'] = 'Started on';
$string['completedon'] = 'Completed on';
$string['timeperiod'] = 'Time Period';
$string['day'] = 'Day';
$string['completedvideos'] = 'Completed videos';

// trendcharts
$string['studentparticipation'] = 'Student participation';
$string['coursestats'] = 'Course stats';
$string['totalvideos'] = 'Total videos';
$string['activevideos'] = 'Active videos';

// videotrends
$string['videotrends'] = 'Video trends';

//viewsreport
$string['#ofattempts'] = '# of Attempts';

// lib/ratinglib
$string['bar'] = 'bar-';
$string['liked'] = 'Liked';
$string['disliked'] = 'Disliked';
$string['na'] = 'N/A';

// local/filters
$string['byactivity'] = 'By activity';
$string['selected'] = 'selected';
$string['manager'] = 'manager';
$string['bycourse'] = 'By course';
$string['querywrong'] = 'Sql Query Wrong!';

// local/stream
$string['disabled'] = 'disabled="disabled"';
$string['enrolforrating'] = 'You need to enroll to the stream to give a rating';

// renderer
$string['completed'] = 'Completed';
$string['notyetstarted'] = 'Not yet started';
$string['inprogress'] = 'In progress';
$string['notsynced'] = 'Not Synced';
$string['syncedat'] = 'Synced at ';

$string['noreport'] = 'Report doesnt exist';

$string['likesbelowfivereport'] = 'Likes based on duration (<5min)';
$string['likesbetweenfivetenreport'] = 'Likes based on duration (5-10min)';
$string['likesabovetenreport'] = 'Likes based on duration (Above 10min)';
$string['noofviewsbyuser'] ='No. of views by user';

#js strings
$string['dailyhitsviews'] = 'Daily Hits/Views';
$string['hitsviews'] = 'Hits/Views';
$string['noofusers'] = 'Number of users';
$string['tablesearch'] = 'Search...';
$string['eventstreamactivityviewed'] = 'Stream Activity viewed';
$string['eventdisliked'] = "Video Disliked";
$string['eventliked'] = "Video Liked";
$string['eventvideoplayed'] = 'Video Played';
$string['eventvideorated'] = 'Rating given';
$string['eventvideocompleted'] = 'Video Completed';
$string['eventvideopaused'] = 'Video Paused';
$string['streamingapp'] = 'Stream app';
$string['recordsession'] = 'Record session';
$string['nostreamrepository'] = 'Please enable stream repository to <u><a href="{$a}">continue</a></u>';
$string['completionvideo'] = 'The user must complete the video.';
