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
 * Language strings
 *
 * @package    local
 * @subpackage costcenter
 * @copyright  2022 eAbyas Info Solutions Pvt Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['activecoursecount'] = 'Active Courses';
$string['activeprogramcount'] = 'Active Programs';
$string['activeusers'] = 'Active Students';
$string['addcostcentertabdes'] = 'This page allows you to create/define a new costcenter.<br>
Fill in the following details and click on  create college to create a new college.';
$string['addnewcourse'] = 'Create New Course';
$string['adnewcostcenter'] = '<span class="create_collegeiconwt popupstringicon" aria-hidden="true"></span> Create New {$a} <div class= "popupstring"></div>';
$string['editcostcen'] = '<span class="create_collegeiconwt popupstringicon" aria-hidden="true"></span> Update {$a} <div class= "popupstring"></div>';
// $string['editcostcen'] = '<span class="create_collegeiconwt popupstringicon" aria-hidden="true"></span> Update {$a} <div class= "popupstring"></div>';
$string['adnewdept'] = '<i class="icon fa fa-sitemap" aria-hidden="true"></i> Create New {$a} <div class= "popupstring"></div>';
$string['adnewsubdept'] = '<span class="create_depticonwt popupstringicon" aria-hidden="true"></span> Create New {$a} </div>';
// $string['update_subdept'] = '<i class="icon fa fa-plus" aria-hidden="true"></i> Update {$a} <div class= "popupstring"></div>';
$string['update_subdept'] = '<span class="create_depticonwt popupstringicon" aria-hidden="true"></span> Update {$a} </div>';
$string['addnewdept'] = 'Create New {$a->secondlevel}';
$string['addnewdept/subdept'] = 'Create New Department/Sub Department';
$string['addnewsubdept'] = 'Create New Sub Department';

$string['allowdeletes'] = 'Allow deletes';
$string['allowframembedding'] = 'This page allows you to manage (delete/edit) the costcenters that are defined under this institution.';
$string['allprogramcount'] = 'All Programcount';
$string['alreadyassigned'] = 'Already user is assigned to selected department "{$a->costcenter}"';
$string['anycostcenter'] = 'Any Department';
$string['applydesc'] = 'Thank you for your interest!<br>
To be a part of this costcenter, please fill in the following details and complete the admission process.<br>
You are applying to-<br>
costcenter Name :<b style="margin-left:5px;font-size:15px;margin-top:5px;">{$a->costcenter}</b><br>
Program Name :<b style="margin-left:5px;font-size:15px;">{$a->pgm}</b><br/>
Date of Application :<b style="margin-left:5px;font-size:15px;">{$a->today}</b>';
$string['asignmanagertabdes'] = 'This page allows you to assign manager(s) to the respective costcenter(s). ';
$string['assign_costcenter'] = 'Assigned Department ';
$string['assigncostcenter'] = 'Assign Departments';
$string['assigncostcenter_help'] = 'Assign this user to a Department.';
$string['assignedcostcenter'] = 'Assigned Departments';
$string['assignedfailed'] = 'Error in assigning a user';
$string['assignedsuccess'] = 'Successfully assigned manager to Department.';
$string['assignedtocostcenters'] = 'Assigned to Department';
$string['assignemployee'] = 'Assign student';
$string['assignmanager'] = 'Assign Managers';
$string['assignmanager_title'] = 'Department : Assign Managers';
$string['assignmanagertxt'] = "Assign the manager to a Department by selecting the respective manager, next selecting the respective colleges and then clicking on 'Assign Manager' ";
$string['assignrole_help'] = 'Assign a role to the user in the selected Department.';
$string['assignroles'] = 'Assign Roles';
$string['assignusers'] = 'Assign Managers';

$string['batch'] = 'Batch';
$string['campus'] = 'Campus';
$string['cannotcreatedept'] = 'You cannot create Department until atleast one organization creation';
// $string['cannotdeletecostcenter'] = 'As the department "{$a->scname}" has sub department, you cannot delete it. Please delete the assigned Departments or programs first and come back here. ';
$string['cannotdeletecostcenter'] = 'You cannot delete <b>{$a}</b> organization as it has either active users/courses/curriculum/open courses/programs under it.';
$string['cannotdeletedep'] = 'You cannot delete <b>{$a->fullname}</b> {$a->secondlevel} as it has either active {$a->thirdlevel}/users/courses/curriculum/open courses/programs under it.';
$string['cannotdeletecollege'] = 'You cannot delete <b>{$a}</b> college as it has either active users/courses/curriculum/open courses/programs under it.';
$string['cannotdeletesubdep'] = 'You cannot delete <b>{$a->fullname}</b> {$a->thirdlevel} as it has either active users/courses/curriculum/open courses/programs under it.';
// $string['cannotdeleteclgdept'] = 'You cannot delete <b>{$a}</b> department as it has either active users/courses/curriculum/open courses/programs under it.';
$string['certifications'] = 'Certifications';
$string['chilepermissions'] = 'Do we need to allow the manager to see child courses of this costcenter.';
$string['classrooms'] = 'Classrooms';
$string['cobaltLMSentitysettings'] = 'Entity Settings';
$string['completed'] = 'Completed';
$string['confirm'] = 'Confirm';
$string['confirmationmsgfordel'] = 'Do you really want to delete <b>{$a}</b> ?';
$string['costcenter'] = 'Departments';
$string['costcenter:assign_multiple_departments_manage'] = 'Assign multiple departments';
$string['costcenter:assignmanager'] = 'costcenter:Assign Manager to costcenter';
$string['costcenter:assignusers'] = 'costcenter:assignusers';
$string['costcenter:create'] = 'costcenter:Create';
$string['costcenter:delete'] = 'costcenter:delete';
$string['costcenter:manage'] = 'costcenter:manage';
$string['costcenter:manage_multidepartments'] = 'costcenter:manage_multidepartments';
$string['costcenter:manage_multiorganizations'] = 'Manage multiple organizations';
$string['costcenter:manage_owndepartments'] = 'costcenter:manage_owndepartments';
$string['costcenter:manage_ownorganization'] = 'Manage own organization';
$string['costcenter:manage_ownorganizations'] = 'costcenter:manage_own organizations';
$string['costcenter:manage_subdepartments_manage'] = 'Manage_subdepartments';
$string['costcenter:update'] = 'costcenter:Update';
$string['costcenter:view'] = 'costcenter:view';
$string['costcenter:visible'] = 'costcenter:Visible';
$string['costcenter_logo'] = 'Preferred logo';
$string['costcenter_name'] = 'Costcenter';
$string['costcenteraccountupdated'] = 'Departments updated';
$string['costcenteraccountuptodate'] = 'Departments up-to-date';
$string['costcenterdeleted'] = 'Department deleted';
$string['costcenterid'] = 'Departments';
$string['costcenterlevel'] = 'Department Level';
$string['costcentermodified'] = 'costcenter modified';
$string['costcentername'] = 'Name';
$string['costcenternotaddedregistered'] = 'Departments not added, Already manager';
$string['costcenterrequired'] = 'Department field is mandatory';
$string['costcenters'] = 'Departments';
$string['costcenterscolleges'] = 'Departments';
$string['costcenterscreated'] = 'Department created';
$string['costcentersdeleted'] = 'Department deleted';
$string['costcentersettings'] = 'Department Settings';
$string['costcentersskipped'] = 'Department skipped';
$string['costcentersupdated'] = 'Department updated';
$string['costcenternotfound_admin'] = 'Sorry! costcenter not created yet, Please click continue button to create.';
$string['costcenternotfound_otherrole'] = 'Sorry! costcenter not created yet, Please inform authorized user(Admin or Manager) to Crete costcenter';
$string['costcenternotcreated'] = 'Sorry! costcenter not created yet, Please click continue button to create or go to create costcenter/college tab.';
$string['courses'] = 'Courses';
$string['confirmation_to_disable_0'] = 'Do you really want to activate <b>{$a->fullname}</b> {$a->firstlevel}?';
$string['confirmation_to_disable_1'] = 'Do you really want to inactivate <b>{$a->fullname}</b> ?';
$string['confirmation_to_disable_department_0'] = 'Do you really want to activate <b>{$a->fullname}</b> ?';
$string['confirmation_to_disable_department_1'] = 'Do you really want to inactivate <b>{$a->fullname}</b> ?';
$string['confirmation_to_disable_subdepartment_0'] = 'Do you really want to activate <b>{$a->fullname}</b> ?';
$string['confirmation_to_disable_subdepartment_1'] = 'Do you really want to inactivate <b>{$a->fullname}</b> {$a->thirdlevel}?';
$string['cannot_disable_subdepartment_0'] = 'You cannot inactivate <b>{$a}</b> {$a->thirdlevel} as it has either active students/courses/programs under it.';
$string['cannot_disable_subdepartment_1'] = 'You cannot inactivate <b>{$a->fullname}</b> {$a->thirdlevel} as it has either active students/courses/programs under it.';

$string['cannot_disable_department'] = 'You cannot inactivate <b>{$a->fullname}</b> {$a->secondlevel} as it has either active {$a->thirdlevel}/students/courses/programs under it.';
// $string['cannot_disable_college'] = 'You cannot inactivate <b>{$a}</b> college as it has either active students/courses/programs under it.';
$string['create_organization'] = 'Create New {$a->firstlevel}';
$string['createcostcenter'] = 'Create New +';
$string['createdepartment'] = 'Create New {$a->secondlevel}';
$string['createnewcourse'] = 'Create New +';
$string['createsubdepartment'] = 'Create New {$a->thirdlevel}';
$string['createsuccess'] = 'Department with name "{$a->costcenter}" created successfully';
$string['csvdelimiter'] = 'CSV delimiter';
$string['cuplan'] = 'cuplan';
$string['college'] = '{$a}';

$string['defaultvalues'] = 'Default values';
$string['delconfirm'] = 'Do you really want to delete this Course?';
$string['deletecostcenter'] = 'Delete Department';
$string['deleteerrors'] = 'Delete errors';
$string['deletesuccess'] = 'Deleted Successfully';
$string['deletesuccesscostcenter'] = 'Department "<b>{$a}</b>" deleted Successfully';
$string['department'] = '{$a}';
$string['dept'] = 'Department';
$string['department_or_region'] = 'Department/Region/Location/Division';
$string['department_structure'] = '{$a->secondlevel} Structure';
$string['deptconfig'] = 'Department Configuration';
$string['description'] = 'Description';
$string['description'] = 'Description';
$string['duration'] = 'Duration';

$string['editcostcenter'] = 'Edit Department';
$string['editcostcentertabdes'] = 'This page allows you to edit costcenter.<br>
Fill in the following details and click on  Update costcenter.';
$string['editdep'] = 'Edit Department';
$string['encoding'] = 'Encoding';
$string['errormessage'] = 'Error Message';
$string['errors'] = 'Errors';
$string['eventlevel_help'] = '<b style="color:red;">Note: </b>Global level is a default event level <br />We have four levels of events
<ul><li><b>Global:</b> Site level events</li><li><b>costcenter:</b> Events for particular costcenter<li><b>program:</b>Events for particular program</li><li><b>Semester:</b> Events for particular semester</li></ul>';
$string['failure'] = 'You can not inactivate Department.';
$string['fieldlabel'] = 'Licence Key';
$string['fullnamecannotbeempty'] = 'Name cannot be empty';
$string['fullnametakenlp'] = 'Name <b>"{$a}"</b> already taken ';
$string['graduatelist'] = '
<p style="text-align:justify;">Online applications will be accepted from <i>{$a->sd}</i> under the costcenter <i>{$a->sfn}</i>.
Last date for online submissions is <i>{$a->ed} </i>.
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Apply Now</i> button to submit the online application.</p>';
$string['graduatelists'] = '
<p style="text-align:justify;">Online applications will be accepted from <i>{$a->sd}</i> under the costcenter <i>{$a->sfn}</i>. Click
<a href="program.php?id={$a->pid}">here </a>for more details.Click on <i>Apply Now</i> button to submit the online application.</p>';
$string['globalcourse'] = 'Is this Global Course?';
$string['GPA/CGPAsettings'] = 'GPA/CGPA Settings';
$string['help_des'] = '<h1>View Departments</h1>
<p>This page allows you to manage (delete/edit) the Departments that are defined under this institution.</b></p>

<h1>Add New</h1>
<p>This page allows you to create/define a new costcenter. </b></p>
<p>Fill in the following details and click on save changes to create a new costcenter.</p>
<ul>
<li style="display:block"><h4>Parent</h4>
<p>Parent denotes the main institution that can be categorized into different Departments, campus, universities etc. It can have one or multiple (child) sub-institutions.</b></p>
<p>Select the top level or the parent costcenter under which the new costcenter has to be created. </p>
<p><b>Note*:</b> Select \'Top Level\', if the new costcenter will be the parent costcenter or the highest level under this institution.</p></li>
<li style="display:block"><h4>Type</h4>
<p>Defines the type of institution or the naming convention you would like to apply for the above mentioned institution.</b></p>
<p><b>Campus -</b> A designation given to an educational institution that covers a large area including library, lecture halls, residence halls, student centers, parking etc.</p>
<p><b>Institution -</b> A designation given to an educational institution that grants graduation degrees, doctoral degrees or research certifications along with the undergraduate degrees. <Need to check/confirm></p>
<p><b>costcenter -</b> An educational institution or a part of collegiate university offering higher or vocational education. It may be interchangeable with Institution. It may also refer to a secondary or high costcenter or a constituent part of university.</p></li></ul>
<h1>Assign Manager</h1>
<p>This page allows you to assign manager(s) to the respective costcenter(s). </b></p>
<p>To assign manager(s), select the manager(s) by clicking on the checkbox, then select the costcenter from the given list and finally click on \'Assign Manager\'.</p>
';

$string['iconstyle'] = 'Icon style';
$string['inactivecoursecount'] = 'In-Active Courses';
$string['inactiveprogramcount'] = 'In-Active Programs';
$string['inactiveusers'] = 'In-Active Users';
$string['info'] = 'Help';
$string['information'] = 'A costcenter in Cobalt Learning Management System is defined as college/institution that offers program(s). The costcenter(s) is instructed/disciplined by Instructor(s). A costcenter has its own programs and departments. ';
$string['learning_plan'] = 'Learning Plan';
$string['learningplan'] = 'Learning Plan';
$string['list'] = '
<p style="text-align:justify;">We are accepting online application for the program <i>{$a->pfn}</i>
under the costcenter <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Last date for online submission is <i>{$a->ed}</i>. Please click on below <i>Apply Now </i> button to submit online application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['lists'] = '
<p style="text-align:justify;">We are accepting online application for the program <i>{$a->pfn}</i>
under the costcenter <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Please click on below <i>Apply Now </i> button to submit online application. Click <a href="program.php?id={$a->pid}">here</a> for more details.</p>';
$string['location'] = 'Location';
$string['managecostcenters'] = 'Manage Departments';
$string['missingcostcenter'] = 'Please select the Department';
$string['missingcostcentername'] = 'Name cannot be empty';
$string['missingtheme'] = 'Select Theme';
$string['msg_add_reg_schl'] = 'Hi {$a->username}<br> You are assigned to Department {$a->costcentername}.';
$string['msg_del_reg_schl'] = 'Hi {$a->username}<br> You are un assigned from costcenter {$a->costcentername}.';
$string['navigation_info'] = 'Presently no data is available, Click here to ';
$string['new'] = 'New';
$string['newcostcenter'] = 'New program created';
$string['no_categories_data'] = 'No Categories Available';
$string['no_courses_data'] = 'No Courses Available';
$string['no_data_available'] = 'No data available';
$string['no_feedbacks_data'] = 'No Feedbacks Available';
$string['no_groups_data'] = 'No Batches Available';
$string['no_onlineexams_data'] = 'No Online exams Available';
$string['no_skills_data'] = 'No Skills Available';
$string['no_user'] = "No user is assigned till now";
$string['no_users_data'] = 'No Students Available';
$string['no_employees_data'] = 'Faculty/Staff not yet created';
$string['nochanges'] = 'No changes';
$string['nocostcenter'] = 'No Department is assigned';
$string['noorganizationsavailable'] = 'No {$a->firstlevel} available';
$string['nopermissions'] = 'Sorry, You dont have Permissions ';
$string['noprogram'] = 'No program is assigned';
$string['not_available'] = '0';
$string['notassignedcostcenter'] = 'Sorry you are not assigned to any costcenter.';
$string['notemptymsg'] = 'Licence Key should not be Empty';
$string['nousersyet'] = 'No User is having Manager Role';
$string['notassignedcostcenter_ra'] = 'Sorry! You are not assigned to any costcenter/organization, Please click continue button to Assign.';
$string['notassignedcostcenter_otherrole'] = 'Sorry! You are not assigned to any costcenter/organization, Please inform authorized user(Admin or Manager) to Assign.';
$string['offlist'] = '
<p style="text-align:justify;">We are accepting applications for the program <i>{$a->pfn}</i>
under the costcenter <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Last date for online submission is <i>{$a->ed}</i>. Please click on below <i>Download </i> button to download application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['offlists'] = '
<p style="text-align:justify;">We are accepting applications for the program <i>{$a->pfn}</i>
under the costcenter <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Please click on below <i>Download </i> button to download application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['offgraduatelist'] = '
<p style="text-align:justify;">Applications will be accepted from <i>{$a->sd}</i> under the costcenter <i>{$a->sfn}</i>.
Last date for application submissions is <i>{$a->ed} </i>.
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Download </i> button to download the application.</p>';
$string['offgraduatelists'] = '
<p style="text-align:justify;">Applications will be accepted from <i>{$a->sd}</i> under the costcenter <i>{$a->sfn}</i>.
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Download</i> button to download the application.</p>';
$string['online_exams'] = 'Online Exams';
// $string['organisation'] = 'Institution';
$string['orgtion'] = 'Institution';
$string['colg'] = 'College';
$string['organisations'] = 'Organization';
$string['organization'] = '{$a}';
$string['organization/department'] = 'Institution / Department';
$string['orgconfig'] = 'Organization Configuration';
$string['orgmanage'] = 'Manage {$a->firstlevel}';
$string['orgStructure'] = '{$a->firstlevel} Structure';
$string['clgStructure'] = '{$a->secondlevel} Structure';

$string['parent'] = 'Parent';
$string['parent_help'] = "To create a New Department at Parent Level, please select 'Parent' ";
$string['parentcannotbeempty'] = 'Parent cannot be empty';
$string['parentid'] = 'Parentid';
$string['permissions'] = 'Permissions';
$string['permissions_error'] = 'Sorry! You dont have permission to access';
$string['permissions_help'] = 'Do we need to allow the manager to see child courses of this department.';
$string['pgmheading'] = 'costcenter & Program Details';
// $string['pluginname'] = 'Departments';
$string['pluginname'] = 'Institution Structure';
$string['settingsname'] = 'University/Institution Structure';
$string['positions'] = 'Position';
$string['preferredscheme'] = ' Select preferred scheme';
$string['PrefixandSuffix'] = 'Prefix and Suffix';
$string['problemunassignedsuccess'] = 'There is a problem in Unassigning manager from department';
$string['programname'] = 'Program name';
$string['programs'] = 'Programs';
$string['programsandcostcenters'] = "<h3>Programs and Department Assigned to this costcenter</h3>";
$string['reportdes'] = 'The list of accepted applicants is given below along with the registered costcenter name, program name, admission type, student type, and the status of the application.
<br>Apply filters to customize the view of applicants based on the application type, program type, costcenter, program, student type, and status.';
$string['reports'] = 'Reports';
$string['rounded'] = 'Rounded';
$string['rounded-square'] = 'Rounded-square';
$string['rowpreviewnum'] = 'Preview rows';

$string['saction'] = 'Action';
$string['scheme_1'] = 'Greenish';
$string['scheme_2'] = 'Red fox';
$string['scheme_3'] = 'Orange';
$string['scheme_4'] = 'Green';
$string['scheme_5'] = 'Midnight blue';
$string['scheme_6'] = 'Lite blue';
$string['search'] = 'Search';
$string['select'] = 'Select Department';
$string['selectcostcenter'] = 'TOP Level';
$string['selectsubcostcenter'] = 'Sub Department';
$string['shortname'] = 'Code';
$string['shortnamecannotbeempty'] = 'Code cannot be empty';
$string['shortnametakenlp'] = 'Code <b>"{$a}"</b> already taken ';
$string['skillset'] = 'Skill set';
$string['square'] = 'Square';
$string['students'] = 'Students';
$string['sub_departments'] = '{$a}';
$string['subdepartment'] = '{$a}';
$string['subskillset'] = 'Sub skill set';
$string['success'] = 'Department "{$a->costcenter}" successfully {$a->visible}.';
$string['theme'] = 'Theme Name';
$string['timecreated'] = 'Time Created';
$string['timemodified'] = 'Time modofied';
$string['toomanyoptionstoshow'] = 'Too many options ({$a}) to show';
$string['top'] = 'Top';
$string['totalusers'] = 'Total Users';
$string['totalcoursecount'] = 'Total Courses';
$string['type'] = 'Type';
$string['type_help'] = 'Please select your Department Type. If it is "University" please select University as Type. If it is "Campus"  select Campus as Type.';
$string['unassign'] = 'Un assign';
$string['unassignedsuccess'] = 'Successfully Unassigned Manager from department';
$string['unassignmanager'] = "Are you sure, you want to unassign Manager?";
$string['unassingheading'] = 'Unassign Manager';
$string['university'] = 'University';
$string['universitysettings'] = 'University Settings';
$string['update_costcenter'] = '<i class="icon fa fa-sitemap" aria-hidden="true"></i> Update {$a} <div class= "popupstring"></div>';
$string['updatesuccess'] = 'Department with name "{$a->costcenter}" updated successfully';
$string['upload_users'] = 'Manage Users';
$string['uploadcostcenter'] = 'Upload Departments';
$string['uploadcostcenters'] = 'Upload Departments';
$string['uploadcostcenters'] = 'Upload Departments';
$string['uploadcostcenters'] = 'Upload Departments';
$string['uploadcostcenterspreview'] = 'Upload Departments preview';
$string['uploadcostcenterspreview'] = 'Uploaded Departments preview';
$string['uploadcostcenterspreview'] = 'Uploaded Departments Preview';
$string['uploadcostcentersresult'] = 'Upload Departments results';
$string['uploadusers'] = 'Upload Users';
$string['uploaduserspreview'] = 'Upload Users Preview';
$string['uploadusersresult'] = 'Uploaded Users Result';
$string['username'] = 'Managers';
$string['uubulk'] = 'Select for bulk costcenter actions';
$string['uubulkall'] = 'All Departments';
$string['uubulknew'] = 'New Departments';
$string['uubulkupdated'] = 'Updated Departments';
$string['uucsvline'] = 'CSV line';
$string['uploadcostcenter_help'] = ' The format of the file should be as follows:
* Please download sample excelsheet through button provided .
* Enter the values based upon the information provided in Information/help tab';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing Departments';
$string['uuoptype_addupdate'] = 'Add new and update existing Departments';
$string['uuoptype_update'] = 'Update existing Departments only';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uuupdatetype'] = 'Existing costcenter details';
$string['view'] = 'View Departments';
$string['viewcostcenter'] = 'View {$a->firstlevel}';
$string['viewsubdepartments'] = 'View {$a->secondlevel}';
$string['viewusers'] = 'View Users';
$string['visible'] = 'Visible';
$string['viewapplicantsdes'] = 'The list of registered applicants is given below so as to view their applications and confirm their admission. Applicants whose details furnished do not meet the requirement can be rejected based on the rules and regulations.
<br>Using the filters, customize the view of applicants based on the admission type, program type, costcenter, program and curriculum.
';
$string['cannotdeletelinkedbatchcostcenter'] = 'You cannot delete <b>{$a}</b> organization as it has batches under it.';
$string['cannotinactivelinkedbatchcostcenter'] = 'You cannot inactivate <b>{$a}</b> organization as it has batches under it.';
$string['cannotdeletelinkedbatchdepartment'] = 'You cannot delete <b>{$a}</b> department as it has batches under it.';
$string['cannotinactivelinkedbatchdepartment'] = 'You cannot inactivate <b>{$a}</b> department as it has batches under it.';
$string['cannotdeletelinkedbatchsubdepartment'] = 'You cannot delete <b>{$a}</b> sub-department as it has batches under it.';
$string['cannotinactivelinkedbatchsubdepartment'] = 'You cannot inactivate <b>{$a}</b> sub-department as it has batches under it.';
$string['univ_depart'] = 'University';
$string['non_univ_depart'] = 'Institute';
$string['customreq_selectdeptcoll'] = 'Select any one';
$string['collegelabel'] = 'University Campus College/Affiliated College';
// $string['college'] = 'College';
$string['createcollege'] = 'Create Affiliated Colleges';
// $string['adnewcollege'] = '<i class="icon fa fa-plus-square" aria-hidden="true"></i> Create New College <div class= "popupstring"></div>';

$string['firstlevel'] = 'First level Institution hierarchy';
$string['secondlevel'] = 'Second level Institution hierarchy';
$string['thirdlevel'] = 'Third level Institution hierarchy';
$string['settingdescription'] = 'Description';
$string['settingsdesc'] = 'In the below fields you can define your Institution\'s academic hierarchy levels and the same nomenclature to be used for those levels. The same nomenclature will be used for all the respective labels inside the custom modules of application . ';

$string['firstlevelstring'] = 'First level represents your institution type either as a University/ Education Institution/ Training Institute etc';
$string['secondlevelstring'] = 'Second level represents either an Affiliated College of a University/ Branch of a Training Institute etc';
$string['thirdlevelstring'] = 'Third level represents either Department of a college/ Department of a Training Institute etc';
$string['settingstitle'] = 'Settings';
$string['spacesarenotallowed'] = 'Space are not allowed in the Code';
// $string['university'] = 'University';
$string['institution'] = 'Institution';
// $string['errordept'] = 'Please select department';
$string['selectopen_costcenterid'] = '--Select {$a->firstlevel}--';
$string['select_role'] = '--Select Role--';
$string['role'] = 'Role';
$string['errorrole'] = 'Please select a Role';
$string['select_secondlevel'] = 'Select_{$a}';
$string['select_thirdlevel'] = 'Select_{$a}';

