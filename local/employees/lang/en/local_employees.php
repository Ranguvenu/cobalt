<?php
$string['manage_employees'] = 'Manage Faculty & Staff';
$string['pluginname'] = 'Manage Faculty & Staff';
$string['adnewemployees'] = '<i class="fa fa-user-plus popupstringicon mr-2" aria-hidden="true"></i>Create Faculty/Staff';
$string['employeerole'] = 'Role';
$string['instructor'] = 'Instructor';
$string['organization'] = '{$a}';
$string['employeesearch'] = 'Filter';
$string['subsubdepartment'] = 'subsubdepartment';
$string['msg_pwd_change'] = 'Hi {$a->username}<br>Your password changed successfully!';
$string['manage_br_employees'] = 'Manage Faculty & Staff';

$string['addemployee'] = 'Add Teacher';
$string['employees'] = 'Sample';
$string['selectrole'] = 'Select Role';
$string['assignrole'] = 'Assign role';
$string['joiningdate'] = 'DATE OF JOINING';
$string['generaldetails'] = 'General Details';
$string['personaldetails'] = 'Personal Details';
$string['contactdetails'] = 'Contact Details';
$string['not_assigned'] = 'Not Assigned.';
$string['address'] = 'Address';
$string['user_id'] = 'Userid';
$string['syncstatics'] = 'Syncstatics';
$string['usersinfo'] = '{$a->username} User Information';
$string['search'] = 'Search';
$string['enrolldate'] = 'Enroll Date';
$string['name'] = 'Name';
$string['code'] = 'Code';
$string['userpicture'] = 'Teacher Picture';
$string['newuser'] = 'New Teacher';
$string['createemployee'] = 'Create Faculty/Staff';
$string['downloaduser'] ='Downloademployees';
$string['editemployees'] = '<i class="fa fa-user-plus popupstringicon mr-2" aria-hidden="true"></i> Update Faculty/Staff <div class= "popupstring"></div>';
$string['updateuser'] = 'Update Faculty/Staff';
$string['role'] = 'Role Assigned';
$string['browseusers'] = 'Browse Users';
$string['browseuserspage'] = 'This page allows the user to view the list of users with their profile details which also includes the login summary.';
$string['trash'] = 'Delete User';
$string['delconfirm'] = 'Are you sure? you really want  to delete "{$a->name}" ?';
$string['trashconfirm'] = 'User "{$a->name}" deleted successfully.';
$string['usercreatesuccess'] = 'User "{$a->name}" created Successfully.';
$string['userupdatesuccess'] = 'User "{$a->name}" updated Successfully.';
$string['addnewuser'] = 'Add New User +';
$string['assignedcostcenteris'] = '{$a->label} is "{$a->value}"';
$string['emailexists'] = 'Email exists already.';
$string['givevaliddob'] = 'Give a valid Date of Birth';
$string['dateofbirth'] = 'Date of Birth';
$string['dateofbirth_help'] = 'User should have minimum 20 years age for today.';
$string['assignrole_help'] = 'Assign a role to the user in the selected Organization.';
$string['siteadmincannotbedeleted'] = 'Site Administrator can not be deleted.';
$string['youcannotdeleteyourself'] = 'You can not delete yourself.';
$string['siteadmincannotbesuspended'] = 'Site Administrator can not be suspended.';
$string['youcannotsuspendyourself'] = 'You can not suspend yourself.';
$string['employees:manage'] = 'Manage Teachers';
$string['manage_employees'] = 'Manage Faculty & Staff';
$string['employees:view'] = 'View Employees';
$string['employees:create'] = 'employees:create';
$string['employees:trash'] ='employees:trash';
$string['employees:edit'] = 'employees:edit';
$string['infohelp'] = 'Info/Help';
$string['report'] = 'Report';
$string['viewprofile'] = 'View Profile';
$string['myprofile'] = 'My Profile';
$string['adduserstabdes'] = 'This page allows you to add a new user. This can be one by filling up all the required fields and clicking on "submit" button.';
$string['edituserstabdes'] = 'This page allows you to modify details of the existing user.';
$string['helpinfodes'] = 'Browse user will show all the list of users with their details including their first and last access summary. Browse users also allows the user to add new users.';
$string['youcannoteditsiteadmin'] = 'You can not edit Site Admin.';
$string['suspendsuccess'] = 'User "{$a->name}" suspended Successfully.';
$string['unsuspendsuccess'] = 'User "{$a->name}" Unsuspended Successfully.';
$string['p_details'] = 'PERSONAL/ACADEMIC DETAILS';
$string['acdetails'] = 'Academic Details';
$string['manageusers'] = 'Manage Users';
$string['username'] = 'User name';
$string['unameexists'] = 'Username Already exists';
$string['open_employeeidexist'] = 'Staff Id Already exists';
$string['open_employeeiderror'] = 'Staff Id can contain only alplabets or numericals special charecters not allowed';
$string['total_courses'] = 'Total number of Courses';
$string['enrolled'] = 'Number of Courses Enrolled';
$string['completed'] = 'Number of Courses Completed';
$string['signature'] = "Registrar's Signature";
$string['status'] = "Status";
$string['courses'] = "Courses";
$string['date'] = "Date";
$string['doj'] = 'Date of Joining';
$string['hcostcenter'] = 'Organization';
$string['paddress'] = 'PERMANENT ADDRESS';
$string['caddress'] = 'PRESENT ADDRESS';
$string['invalidpassword'] = 'Invalid password';
$string['dol'] = 'Date of leave';
$string['dor'] = 'Date of resignation';
$string['serviceid'] = 'Staff ID';
$string['help_1'] = '<div class="helpmanual_table"><table class="generaltable" border="1">
<tr class="field_type_head"><td class="empty_column"></td><td class="field_type font-weight-bold" style="text-align:left;border-left:1px solid white;padding-left:50px;">Mandatory Fields</td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>{$a->firstlevel}</td><td>Provide the {$a->firstlevel} code</td></tr>
<tr><td>Firstname</td><td>Enter the first name, avoid additional spaces.</td></tr>
<tr><td>Lastname</td><td>Enter the last name, avoid additional spaces.</td></tr>
<tr><td>Email</td><td>Enter valid email.</td></tr>
<tr><td>Password</td><td>Provide the password,Password must be at least 8 characters long,Password must have at least 1 digit(s),Password must have at least 1 upper case letter(s),
Password must have at least 1 non-alphanumeric character(s) such as as *, -, or #..</td></tr>
<tr><td>Staff_status</td><td>Enter Staff Status as either \'Active\' or \'Inactive\', avoid additional spaces.</td></tr>
<tr><td>{$a->secondlevel}</td><td>Provide {$a->secondlevel} code. {$a->secondlevel} must already exist in system as part of {$a->firstlevel} hierarchy.</td></tr>
<tr><td>Role</td><td>Enter Role (editingteacher, orgadmin, collegeadmin, departmentadmin), avoid additional spaces.</td></tr>
<tr><td>Phone Number</td><td>Enter Phone Number.It should be in Numerics and contains 10 digits only.</td></tr>
';

$string['help_2'] = '</td></tr>
<tr class="field_type_head"><td class="empty_column"></td><td class="field_type font-weight-bold" style="text-align:left;border-left:1px solid white;"><b  class="pad-md-l-50 hlep2-oh">Non-Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>City</td><td>Enter city name for staff.</td></tr>
<tr><td>Address</td><td>Enter address for staff.</td></tr>  
<tr><td>{$a->thirdlevel}</td><td>Enter {$a->thirdlevel} code for staff.</td></tr> 
</table>';

$string['help_2_orghead'] = '</td></tr>
<tr class="field_type_head"><td class="empty_column"></td><td class="field_type font-weight-bold" style="text-align:left;border-left:1px solid white;"><b  class="pad-md-l-50 hlep2-oh">Non-Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>City</td><td>Enter city name for staff.</td></tr>
<tr><td>Address</td><td>Enter address for staff.</td></tr>  
<tr><td>{$a->thirdlevel}</td><td>Enter {$a->thirdlevel} code for staff.</td></tr> 
</table>';


$string['help_2_dephead'] = '</td></tr>
<tr class="field_type_head"><td class="empty_column"></td><td class="field_type font-weight-bold" style="text-align:left;border-left:1px solid white;"><b  class="pad-md-l-50 hlep2-oh">Non-Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>City</td><td>Enter city name for staff.</td></tr>
<tr><td>Address</td><td>Enter address for staff.</td></tr>  
<tr><td>{$a->thirdlevel}</td><td>Enter {$a->thirdlevel} code for staff.</td></tr> 
</table>';

$string['help_2_subdephead'] = '</td></tr>
<tr class="field_type_head"><td class="empty_column"></td><td class="field_type font-weight-bold" style="text-align:left;border-left:1px solid white;"><b  class="pad-md-l-50 hlep2-oh">Non-Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>City</td><td>Enter city name for staff.</td></tr>
<tr><td>Address</td><td>Enter address for staff.</td></tr>   
</table>';

$string['help_1_orghead'] = '<table class="generaltable" border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;"><b class="pad-md-l-50 hlep1-oh">Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>Firstname</td><td>Enter the first name, avoid additional spaces.</td></tr>
<tr><td>Lastname</td><td>Enter the last name, avoid additional spaces.</td></tr>
<tr><td>Email</td><td>Enter valid email.</td></tr>
<tr><td>Password</td><td>Provide the password,Password must be at least 8 characters long,Password must have at least 1 digit(s),Password must have at least 1 upper case letter(s),
Password must have at least 1 non-alphanumeric character(s) such as as *, -, or #..</td></tr>
<tr><td>Staff_status</td><td>Enter Staff Status as either \'Active\' or \'Inactive\', avoid additional spaces.</td></tr>
<tr><td>{$a->secondlevel}</td><td>Provide {$a->secondlevel} code. {$a->secondlevel} must already exist in system as part of {$a->firstlevel} hierarchy.</td></tr>
<tr><td>Phone Number</td><td>Enter Phone Number.It should be in Numerics and contains 10 digits only.</td></tr>
<tr><td>Role</td><td>Enter the Role (editingteacher, collegeadmin, departmentadmin), avoid additional spaces.</td></tr>
';

$string['help_1_dephead'] = '<table class="generaltable" border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;"><b class="pad-md-l-50 hlep1-dh">Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>Firstname</td><td>Enter the first name, avoid additional spaces.</td></tr>
<tr><td>Lastname</td><td>Enter the last name, avoid additional spaces.</td></tr>
<tr><td>Email</td><td>Enter valid email.</td></tr>
<tr><td>Password</td><td>Provide the password,Password must be at least 8 characters long,Password must have at least 1 digit(s),Password must have at least 1 upper case letter(s),
Password must have at least 1 non-alphanumeric character(s) such as as *, -, or #..</td></tr>
<tr><td>Staff_status</td><td>Enter Staff Status as either \'Active\' or \'Inactive\', avoid additional spaces.</td></tr>
<tr><td>Phone Number</td><td>Enter Phone Number.It should be in Numerics and contains 10 digits only.</td></tr>
<tr><td>Role</td><td>Enter the Role (editingteacher, collegeadmin, departmentadmin), avoid additional spaces.</td></tr>
';

$string['help_1_subdephead'] = '<table class="generaltable" border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;"><b class="pad-md-l-50 hlep1-dh">Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>Firstname</td><td>Enter the first name, avoid additional spaces.</td></tr>
<tr><td>Lastname</td><td>Enter the last name, avoid additional spaces.</td></tr>
<tr><td>Email</td><td>Enter valid email.</td></tr>
<tr><td>Password</td><td>Provide the password,Password must be at least 8 characters long,Password must have at least 1 digit(s),Password must have at least 1 upper case letter(s),
Password must have at least 1 non-alphanumeric character(s) such as as *, -, or #..</td></tr>
<tr><td>Staff_status</td><td>Enter Staff Status as either \'Active\' or \'Inactive\', avoid additional spaces.</td></tr>
<tr><td>Phone Number</td><td>Enter Phone Number.It should be in Numerics and contains 10 digits only.</td></tr>
<tr><td>Role</td><td>Enter the Role (editingteacher), avoid additional spaces.</td></tr>
';

$string['already_assignedstocostcenter']='{$a} already assigned to costcenter. Please unassign from costcenter to proceed further';
$string['already_instructor']='{$a} already assigned as instructor. Please unassign this user as instructor to proceed further';
$string['already_mentor']='{$a} already assigned as mentor. Please unassign this user as mentor to proceed further';
// ***********************Strings for bulk users**********************
$string['download'] = 'Download';
$string['downloadempdetails'] = 'Download Faculty/Staff detials';
$string['csvdelimiter'] = 'CSV delimiter';
$string['encoding'] = 'Encoding';
$string['errors'] = 'Errors';
$string['nochanges'] = 'No changes';
$string['uploadusers'] = 'Upload Faculty/Staff';
$string['rowpreviewnum'] = 'Preview rows';
$string['uploaduser'] = 'Upload Faculty/Staff';
$string['back_upload'] = 'Back';
$string['bulkupload'] = 'Bulk Upload Faculty/Staff';
$string['uploaduser_help'] = ' The format of the file should be as follows:

* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
* The first record contains a list of fieldnames defining the format of the rest of the file';

$string['uploaduserspreview'] = 'Upload Users Preview';
$string['userscreated'] = 'Users created';
$string['usersskipped'] = 'Users skipped';
$string['usersupdated'] = 'Users updated';
$string['uuupdatetype'] = 'Existing users details';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing users';
$string['uuoptype_addupdate'] = 'Add new and update existing users';
$string['uuoptype_update'] = 'Update existing users only';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uploadusersresult'] = 'Uploaded Users Result';
$string['helpmanual'] = 'Download sample Excel sheet and fill the field values in the format specified below.';
$string['manual'] = 'Help Manual';
$string['info'] = 'Help';
$string['helpinfo'] = 'Browse user will show all the list of users with their details including their first and last access summary. Browse users also allows the user to add new users.';
$string['changepassdes'] = 'This page allows the user to view the list of users with their profile details which also includes the login summary. Here you can also manage (edit/trash/inactivate) the users.';
$string['changepassinstdes'] = 'This page allows you to update or modify the password at any point of time; provided the instructor must furnish the current password correctly.';
$string['changepassregdes'] = 'This page allows you to update or modify the password at any point of time; provided the registrar must furnish the current password correctly.';
$string['info_help'] = '<h1>Browse Users</h1>
This page allows the user to view the list of users with their profile details which also includes the login summary. Here you can also manage (edit/trash/inactivate) the users.
<h1>Add New/Create User</h1>
This page allows you to add a new user. This can be one by filling up all the required fields and clicking on ‘submit’ button.';
$string['enter_grades'] = 'Enter Grades';
$string['firstname'] = 'First name';
$string['middlename'] = 'Middle name';
$string['lastname'] = 'Last name';
$string['female']='Female';
$string['male']='Male';
$string['userdob']='Date of Birth';
$string['phone']='Mobile';
$string['email']='Email';
$string['emailerror']='Enter valid Email ID';
$string['phoneminimum']='Please Enter Minimum 10 Digits';
$string['phonemaximum']='You Can\' t Enter More Than 15 digits';
$string['country_error']='Please select a country';
$string['numeric'] = 'Only numeric values';
$string['pcountry']='Country';
$string['genderheading']='Generate Heading';
$string['primaryyear']='Primary Year';
$string['score']='Score';
$string['contactname']='Contact Name';
$string['hno']='House Number';
$string['phno']='Phone Number';
$string['pob']='Place of Birth';
$string['contactname']='Contact Name';
$string['bulkassign'] = 'Bulk assignment to the costcenter';
$string['im:costcenter_unknown'] = 'Unknown costcenter';
$string['im:user_unknown'] = 'Unkown user';
$string['im:user_notcostcenter'] = 'Loggedin manager not assigned to this costcenter "{$a->csname}"';
$string['im:already_in'] = 'User already assigned to the costcenter';
$string['im:assigned_ok'] = '{$a} User assigned successfully';
$string['upload_employees'] = 'Upload Faculty/Staff';
$string['assignuser_costcenter'] = 'Assign users to Organization';
$string['button'] = 'CONTINUE';
$string['idnumber'] = 'Id Number';
$string['username'] = 'Username';
$string['firstcolumn'] = 'User column contains';
$string['enroll_batch'] ='Batch Enroll';
$string['mass_enroll'] = 'Bulk enrolments';
$string['mass_enroll_help'] = <<<EOS
<h1>Bulk enrolments</h1>

<p>
With this option you are going to enrol a list of known users from a file with one account per line
</p>
<p>
<b> The firstline </b> the empty lines or unknown accounts will be skipped. </p>

<p>
The file may contains one or two columns, separated by a comma, a semi-column or a tabulation.

You should prepare it from your usual spreadsheet program from official lists of students, for example,
and add if needed a column with groups to which you want these users to be added. Finally export it as CSV. (*)</p>

<p>
<b> The first one must contains a unique account identifier </b>: idnumber (by default) login or email  of the target user. (**). </p>

<p>
The second <b>if present,</b> contains the group's name in wich you want that user to be added. </p>

<p>
If the group name does not exist, it will be created in your course, together with a grouping of the same name to which the group will be added.
.<br/>
This is due to the fact that in Moodle, activities can be restricted to groupings (group of groups), not groups,
 so it will make your life easier. (this requires that groupings are enabled by your site administrator).

<p>
You may have in the same file different target groups or no groups for some accounts
</p>

<p>
You may unselect options to create groups and groupings if you are sure that they already exist in the course.
</p>

<p>
By default the users will be enroled as students but you may select other roles that you are allowed to manage (teacher, non editing teacher
or any custom roles)
</p>

<p>
You may repeat this operation at will without dammages, for example if you forgot or mispelled the target group.
</p>


<h2> Sample files </h2>

Id numbers and a group name to be created in needed in the course (*)
<pre>
"idnumber";"group"
" 2513110";" 4GEN"
" 2512334";" 4GEN"
" 2314149";" 4GEN"
" 2514854";" 4GEN"
" 2734431";" 4GEN"
" 2514934";" 4GEN"
" 2631955";" 4GEN"
" 2512459";" 4GEN"
" 2510841";" 4GEN"
</pre>

only idnumbers (**)
<pre>
idnumber
2513110
2512334
2314149
2514854
2734431
2514934
2631955
</pre>

only emails (**)
<pre>
email
toto@insa-lyon.fr
titi@]insa-lyon.fr
tutu@insa-lyon.fr
</pre>

usernames and groups, separated by a tab :

<pre>
username	 group
ppollet      groupe_de_test              will be in that group
codet        groupe_de_test              also him
astorck      autre_groupe                will be in another group
yjayet                                   no group for this one
                                         empty line skipped
unknown                                  unknown account skipped
</pre>

<p>
<span <font color='red'>(*) </font></span>: double quotes and spaces, added by some spreadsheet programs will be removed.
</p>

<p>
<span <font color='red'>(**) </font></span>: target account must exit in Moodle ; this is normally the case if Moodle is synchronized with
some external directory (LDAP...)
</p>


EOS;


$string['reportingto'] = 'Reports To';
$string['functionalreportingto'] = 'Functional Reporting To';
$string['ou_name'] = 'OU Name';
$string['department'] = '{$a}';
$string['subdept'] = '{$a}';
$string['costcenter_custom'] = 'Costcenter';
$string['subdepartment'] = 'Sub Department';
$string['designation'] = 'Designation';
$string['designations_help'] = 'Search and select a designation from the available pool. Designation made available here are the designation that are mapped to users on the system. Selecting a designation means that any user in the system who has the selected designation mapped to them will be eligible for enrollment.';
$string['client'] = 'Client';
$string['grade'] = 'Grade';
$string['team'] = 'Team';
$string['hrmrole'] = 'Role';
$string['role_help'] = "Search and select a role from the available pool. Roles made available here are the roles that are mapped to users on the system. Selecting a 'role (s)' means that any user in the system who has the selected role mapped to them will be eligible for enrollment.";
$string['zone'] = 'Zone';
$string['region'] = 'Region';
$string['branch'] = 'Branch';
$string['group'] = 'Group';
$string['preferredlanguage'] = 'Language';
$string['open_group'] = 'Level';
$string['open_band'] = 'Band';
$string['open_role'] = 'Role';
$string['open_zone'] = 'Zone';
$string['open_region'] = 'Region';
$string['open_grade'] = 'Grade';
$string['open_branch'] = 'Branch';
$string['position'] = 'Role';
$string['emp_status'] = 'Faculty/Staff Status';
$string['resign_status'] = 'Resignation Status';
$string['emp_type'] = 'Faculty/Staff Type';
$string['dob'] = 'Date of Birth';
$string['career_track_tag'] = 'Career Track';
$string['campus_batch_tag'] = 'Campus Batch';
$string['calendar'] = 'Calendar Name';
$string['otherdetails'] = 'Other Details';
$string['location'] = 'Location';
$string['city'] = 'City';
$string['gender'] = 'Gender';
$string['usersupdated'] = 'Users updated';
$string['supervisor'] = 'Reporting To';
$string['selectasupervisor'] = 'Select Reporting To';
$string['reportingmanagerid'] = 'Functional Reporting To';
$string['selectreportingmanager'] = 'Select Functional Reporting';
$string['salutation'] = 'Salutation';
$string['employment_status'] = 'Employment Status';
$string['confirmation_date'] = 'Confirmation Date';
$string['confirmation_due_date'] = 'Confirmation Due Date';
$string['age'] = 'Age';
$string['paygroup'] = 'Paygroup';
$string['physically_challenge'] = 'Physically Challenge';
$string['disability'] = 'Disability';
$string['employment_type'] = 'Employment Type';
$string['employment_status'] = 'Employment Status';
$string['employee_status'] = 'Faculty/Staff Status';
$string['enrol_user'] = 'Enrol Users';
$string['level'] = 'Level';
$string['select_career'] = 'Select Career Track';
$string['select_grade'] = 'Select Grade';
/*-----------Ended Here-------------*/

$string['userinfo'] = 'User info';
$string['addtional_info'] = 'Addtional info';
$string['user_transcript'] = 'User transcript';
$string['type'] = 'Type';
$string['transcript_history'] = 'Transcript History (2015-2016)';
$string['sub_sub_department']='Sub Sub Depatement';
$string['zone_region']='Zone Region';
$string['area']='Area';
$string['dob']='DOB';
$string['matrail_status']='Martial Status';
$string['state']='State';
$string['course_header']='CURRENT LEARNING';
$string['courses_header_emp']='CURRENT LEARNING FOR ';
$string['courses_data']='No Courses to display.';
$string['page_header']='Profile Details';
$string['adnewuser']='<i class="fa fa-user-plus popupstringicon mr-2" aria-hidden="true"></i> Create User <div class= "popupstring"></div>';
$string['empnumber']='Faculty/Staff ID';
$string['departments']='Departments';
$string['sub_departments']='Sub Department';
$string['department_help']='This setting determines the category in which the Department will appear.';
$string['subdepartment_help']='This setting determines the category in which the Sub Department  will appear in the list of Departments.';
$string['subsubdepartment_help']='This setting determines the category in which the sub sub department will appear in the list of Sub Department.';
// $string['errordept']='Please select department';
$string['errorsubdept']='Please select Sub Department';
$string['errorsubsubdept']='Please select Sub Sub Department';
$string['errorfirstname']='Please enter a valid first name';
$string['errorlastname']='Please enter a valid last name';
$string['erroremail']='Please enter an Email address';
$string['filemail']='Email Address';
$string['idexits']='Faculty/Staff ID Already exists';
//-------for sync lang files-------
$string['options']='Option';
$string['enrollmethods']='Enroll method';
$string['authenticationmethods'] = 'Authentication method';

$string['assigned_courses'] = 'Assigned Courses';
$string['completed_courses'] = 'Completed Courses';
$string['not_started_courses'] = 'Not Started';
$string['inprogress_courses'] = 'In Progress';
$string['employee_id'] = 'Faculty/Staff ID';
$string['certificates'] = 'Certificates';
$string['already_assignedlp']='User assigned to Learning plan';
$string['coursehistory']='History';
$string['employees']="Faculty/Staff";
$string['learningplans']="Learning Paths";
$string['lowercaseunamerequired'] = 'Username should be in lowercase only';
$string['sync_users'] = 'Sync users';
$string['sync_errors'] = 'Sync errors';
$string['sync_stats'] = 'Sync statistics';
$string['view_employees'] = 'view staff';
$string['nodepartmenterror'] = 'Department cannot be empty';
$string['syncstatistics'] = 'Sync Statistics';
$string['phonenumvalidate']='Please enter a 10 digit valid number';

$string['cannotcreateuseremployeeidadderror'] = 'Faculty/Staff with staff {$a->employee_id} already exist so cannot create user in adduser mode at line {$a->linenumber}';
$string['cannotfinduseremployeeidupdateerror'] = 'Faculty/Staff with staff {$a->employee_id} doesn\'t exist';
$string['cannotcreateuseremailadderror'] = 'Faculty/Staff with mailid {$a->email} already exist so cannot create user in adduser mode at line {$a->linenumber}';
$string['cannotedituseremailupdateerror'] = 'Faculty/Staff with mailid {$a->email} doesn\'t exist so cannot update in update mode at line {$a->linenumber}';
$string['multipleuseremployeeidupdateerror'] = 'Multiple Faculty/Staff with staff {$a->employee_id} exist';
$string['multipleedituseremailupdateerror'] = 'Multiple Faculty/Staff with email {$a} exist';
$string['multipleedituserusernameediterror'] = 'Multiple Faculty/Staff with username {$a} exist';
$string['cannotedituserusernameediterror'] = 'Faculty/Staff with username {$a} doesn\'t exist in update mode';
$string['cannotcreateuserusernameadderror'] = 'Faculty/Staff with username {$a->username} already exist cannot create user in add mode at line {$a->linenumber}';
$string['trashconfirm'] = 'Do you really want to delete <b>{$a->fullname}</b>?';
$string['local_users_table_footer_content'] = 'Showing {$a->start_count} to {$a->end_count} of {$a->total_count} entries';
$string['suspendconfirm'] = 'Are you sure you want to change status of {$a->fullname} ?';
$string['suspendconfirmenable'] = 'Do you really want to inactivate <b>{$a->fullname}</b>?';
$string['suspendconfirmdisable'] = 'Do you really want to activate <b>{$a->fullname}</b>?';
$string['firstname_surname'] = 'First Name / Surname';
$string['employeeid'] = 'Faculty/Staff id';
$string['batch'] = 'Batch id';
$string['emailaddress'] = 'Email';
$string['supervisorname'] = 'Reporting To';
$string['lastaccess'] = 'Last Access';
$string['actions'] = 'Actions';
$string['classrooms'] = 'Classrooms';
$string['onlineexams'] = 'Online exams';
$string['programs'] = 'Programs';
$string['contactno'] = 'Contact no';
$string['nosupervisormailfound'] = 'No Reporting managers found with email {$a->email} at line {$a->line}.';
$string['nosupervisorempidfound'] = 'No Reporting managers found with Faculty/Staff {$a->empid} at line {$a->line}.';
$string['valusernamerequired'] = 'Please enter a valid user name';
$string['valfirstnamerequired'] = 'Please enter a valid First name';
$string['vallastnamerequired'] = 'Please enter a valid Last name';
$string['errororganization'] = 'Please select a organization';
$string['usernamerequired'] = 'Please enter a username';
$string['passwordrequired'] = 'Please enter a Password';
$string['departmentrequired'] = 'Please select department';
$string['employeeidrequired'] = 'Please enter faculty/staffid';
$string['noclassroomdesc'] = 'No description provided';
$string['noprogramdesc'] = 'No description provided';

$string['team_dashboard'] = 'Team Dashboard';
$string['myteam'] = 'My Team';
$string['idnumber'] = 'Faculty/Staff ID';
//==============For target audience=========
$string['target_audience'] = 'Target audience';
$string['open_group'] = 'Group';
$string['groups_help'] = 'Search and select an available or existing custom group as target audience';
$string['open_band'] = 'Band';
$string['open_hrmsrole'] = 'Role';
$string['role_help'] = "Search and select a role from the available pool. Roles made available here are the roles that are mapped to users on the system. Selecting a 'role (s)' means that any user in the system who has the selected role mapped to them will be eligible for enrollment.";
$string['open_branch'] = 'Branch';
$string['open_designation'] = 'Designation';
$string['designation_help'] = 'Search and select a designation from the available pool. Designation made available here are the designation that are mapped to users on the system. Selecting a designation means that any user in the system who has the selected designation mapped to them will be eligible for enrollment.';
$string['open_location'] = 'Location';
$string['location_help'] = "Users belonging to these location can enrol/request to this modulSearch and select an available or existing Faculty/Staff location's. The location available here are the locations that are mapped to users on the system. Selecting a location(s) means that any user in the system who has the selected location mapped to them will be eligible for enrollment.";
$string['team_allocation'] = 'Team allocation';
$string['myteam'] = 'My team';
$string['allocate'] = 'Allocate';
$string['learning_type'] = 'Learning Type';

$string['team_confirm_selected_allocation'] = 'Confirm allocation?';
$string['team_select_user'] = 'Please select a user.';
$string['team_select_course_s'] = 'Please select valid course/s.';
$string['team_approvals'] = 'Team approvals';
$string['approve'] = 'Approve';
$string['no_team_requests'] = 'No requests from team';
$string['team_no_learningtype'] = 'Please select any learning type.';
$string['select_requests'] = 'Select any requests.';
$string['select_learningtype'] = 'Select any learning type.';
$string['allocate_search_users'] = 'Search Users...';
$string['allocate_search_learnings'] = 'Search Learning Types...';
$string['select_user_toproceed'] = 'Select a user to proceed.';
$string['no_coursesfound'] = 'No courses found';
$string['no_classroomsfound'] = 'No classrooms found';
$string['no_programsfound'] = 'No programs found';
$string['team_requests_search'] = 'Search Team Requests by Users...';
$string['team_nodata'] = 'No records found';
$string['allocate_confirm_allocate'] = 'Are you sure you want to Approve selected requests?';
$string['team_request_confirm'] = 'Are you sure you want to Approve selected requests?';
$string['members'] = 'Members';
$string['permissiondenied'] = 'You dont have permissions to view this page.';
$string['onlinetests'] = 'Online Tests';
$string['manage_br_users'] = 'Manage Users';
$string['profile'] = 'Profile';
$string['badges'] = 'Badges';
$string['completed'] = 'Completed';
$string['notcompleted'] = 'Not Completed';
$string['nopermission'] = 'You dont have permissions to view ths page';
$string['selectdepartment'] = 'Select Department';
$string['selectsupervisor'] = 'Select Reporting To';
$string['total'] = 'Total';
$string['active'] = 'Active';
$string['inactive'] = 'Inactive';
$string['trashconfirmsynch'] = 'Are you sure you want to delete the selected values ?';
$string['classroom'] = 'Classrooms';
$string['learningplan'] = 'Learningplan';
$string['program'] = 'Program';
$string['open_level'] = 'Level';
$string['certification'] = 'Certification';
$string['certifications'] = 'Certifications';
$string['groups'] = 'groups';
$string['notbrandedmobileapp'] = 'You are not using BizLMS branded mobile App';
$string['makeactive'] = 'Make Active';
$string['makeinactive'] = 'Make Inactive';
$string['positionreq'] = 'Select role';
$string['domain'] = 'Domain';
$string['domainreq'] = 'Select Domain';
$string['skillname'] = 'Skill Name';
$string['level'] = 'Level';
$string['categorypopup'] = 'Competency {$a}';
$string['competency'] = 'Competency';
$string['skill_profile'] = 'Skill Profile';
$string['competency'] = 'Competency';
$string['skills'] = 'Skills';
$string['open_level'] = 'Level';
$string['competencyprogress'] = 'Competency Progress';

$string['login'] = 'Login';
$string['users'] = 'Users';
$string['selectonecheckbox_msg'] = 'Please Select atleast one checkbox';
$string['save_continue'] = 'Save and continue';
$string['skip'] = 'Skip';
$string['previous'] = 'Previous';
$string['cancel'] = 'Cancel';
$string['emailaleadyexists'] = 'User with email {$a->email} already exist at line {$a->excel_line_number}.';

$string['usernamealeadyexists'] = 'User with email {$a->email} already exist at line {$a->excel_line_number}.';

$string['employeeid_alreadyexists'] = 'User with faculty/staff {$a->employee_id) already exist at line {$a->excel_line_number}.';

$string['empiddoesnotexists'] = 'User with faculty/staff  {$a->employee_id) does not exist at line  {$a->excel_line_number}.';
$string['empfile_syncstatus'] = 'faculty/staff upload result';

$string['addedusers_msg'] = 'Total {$a} new users added to the system.';
$string['updatedusers_msg'] = 'Total {$a} users details updated.';
$string['errorscount_msg'] = 'Total {$a} errors occured in the sync update.';
$string['warningscount_msg'] = 'Total {$a} warnings occured in the sync update.';
$string['superwarnings_msg'] = 'Total {$a} Warnings occured while updating faculty/staff.';

$string['filenotavailable'] = 'No data found.';

$string['orgmissing_msg'] = 'Provide the {$a->firstlevel} info for faculty/staff \'{$a->employee_id}\' of uploaded sheet at line {$a->excel_line_number} .';

$string['invalidorg_msg'] = '{$a->firstlevel} "{$a->org_shortname}" for faculty/staff \'{$a->employee_id}\' in uploaded excelsheet does not exist in system at line {$a->excel_line_number}';
$string['otherorg_msg'] = '{$a->firstlevel} "{$a->org_shortname}" entered at line \'{$a->employee_id}\' for faculty/staff {$a->excel_line_number} in uploaded excelsheet does not belongs to you.';


$string['invalidempid_msg'] = 'Provide valid faculty/staff value \'{$a->employee_id}\' inserted in the excelsheet at line {$a->excel_line_number} .';

$string['empidempty_msg'] = 'Provide faculty/staff for username \'{$a->username}\' of uploaded sheet at line {$a->excel_line_number}. ';
$string['error_employeeidcolumn_heading'] = 'Error in faculty/staff column heading in uploaded excelsheet ';

$string['firstname_emptymsg'] = 'Provide firstname for  faculty/staff \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}.';
$string['error_firstnamecolumn_heading'] = 'Error in first name column heading in uploaded excelsheet ';

$string['latname_emptymsg'] = 'Provide last name for  faculty/staff \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';
$string['error_lastnamecolumn_heading'] = 'Error in last name column heading in uploaded excelsheet';

$string['email_emptymsg'] = 'Provide email id for  faculty/staff \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';
$string['invalidemail_msg'] = 'Invalid email id entered for  staffid \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}.';

$string['columnsarragement_error'] = 'Error in arrangement of columns in uploaded excelsheet at line {$a}';

$string['invalidusername_error'] = 'Provide valid username for faculty/staff \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';

$string['usernameempty_error'] = 'Provide username for faculty/staff \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';

$string['empstatusempty_error'] = 'Provide faculty/staff status for  faculty/staff \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';

$string['select_org'] = '--Select organization--';
$string['select_dept'] = '--Select department--';
$string['select_reportingto'] = '--Select Reporting To--';
$string['select_subdept'] = '--Select Sub Department--';
$string['select_opt'] = '-- Select --';
$string['only_add'] = 'Only add';
$string['only_update'] = 'Only update';
$string['add_update'] = 'Both add and update';
$string['disable'] = 'Disable';
$string['enable'] = 'Enable' ;
$string['employee'] = 'Faculty/Staff' ;
$string['error_in_creation'] = 'Error in creation' ;
$string['error_in_inactivating'] = 'Error in inactivating';
$string['error_in_deletion'] = 'Error in deletion';
$string['file_notfound_msg'] = 'file not found/ empty file error';
$string['back'] = 'Back';
$string['help_manual'] = 'Help manual';
$string['sync_errors'] = 'Sync Errors';
$string['welcome'] = 'Welcome';
$string['edit_profile'] = 'Edit Profile';
$string['messages'] = 'Messages';
$string['competencies'] = 'Competencies';
$string['error_with'] = 'Error with';
$string['uploaded_by'] = 'Uploaded by';
$string['uploaded_on'] = 'Uploaded On';
$string['new_employees_count'] = 'New Faculty/Staff Count';
$string['trashs'] = 'Delete';
$string['trash'] = 'Delete';
$string['sup_warningscount'] = 'Supervisor Warnings Count';
$string['warningscount'] = 'Warnings Count';
$string['errorscount'] = 'Errors Count';
$string['updated_employeescount'] = 'Updated Faculty/Staff Count';
$string['personalinfo'] = 'Personal Info :';
$string['professionalinfo'] = 'Professional Info :';
$string['otherinfo'] = 'Other Info :';
$string['trash'] = 'Delete';
$string['pictureof'] = 'Picture of';
$string['syncnow'] = 'Sync Now';
$string['authmethod'] = 'Auth Method';
$string['open_locationrequired'] = 'Please provide Location information';
$string['open_hrmsrolerequired'] = 'Please provide Role information';
$string['password_required'] = 'Provide Password information of the user at line {$a->linenumber}';
$string['hrmsrole_upload_error'] = 'Provide Role information of the user at line {$a->linenumber}';
$string['location_upload_error'] = 'Provide Location information of the user at line {$a->linenumber}';
$string['password_upload_error'] = '{$a->errormessage} at line {$a->linenumber}';
$string['client_upload_error'] = 'Provide Department information of the user at line {$a->linenumber}';
$string['position_upload_error'] = 'With out domain you cannot upload Position only';
$string['notifylogins'] = 'Notify Login Details';
$string['logininfo'] = 'Login Details';
$string['logininfobody'] = ' <p>Hi {$a->firstname},</p>
<p>Please find the below login detials for the site {$a->siteurl}.</p>
<p>Username: {$a->firstname}</p>
<p>Password: {$a->password}</p>
<p>Thanks,</p>
<p>Admin.</p>';

$string['trashsss'] = 'Delete';
$string['trashsyncStatistics'] = 'Are you sure? you really want  to delete ?';
$string['personalinfo'] = '
        <span class="personal_info">
            <i class="statuslist_icon fa fa-user"></i>
            <span class="personalinfo_rightbar"></span>
        </span>
        <span class="statuslist_title">Personal Info</span>';
$string['professionalinfo'] = '
        <span class="professional_info">
            <i class="statuslist_icon fa fa-users"></i>
        </span>
        <span class="statuslist_title">Professional Info</span>';
$string['addressinfo'] = '
        <span class="address_info">
            <span class="address_leftbar"></span>
            <i class="statuslist_icon fa fa-map-marker"></i>
            <span class="address_rightbar"></span>
        </span>
        <span class="statuslist_title">Address Info</span>';
$string['academicinfo'] = '
        <span class="academic_info">
            <span class="academic_leftbar"></span>
            <i class="statuslist_icon fa fa-briefcase"></i>
        </span>
        <span class="statuslist_title">Academic Info</span>';
$string['profileupdate'] = 'Profile Update.';
$string['aboutmyself'] = 'About myself';
$string['notificationpreferences'] = 'Notification Preferences.';
$string['notificationpreferenceserr'] = 'Please Select Notification Preferences.';
$string['topicsinterested'] = 'Topics of Interest';
$string['topicsinterestederr'] = 'Please Select Topics Interested.';
$string['topicsinterested_help'] = "Search and select a topics from the available pool. Topic made available here are the topics that are mapped to users on the system. Selecting a 'topic (s)' means that any user in the system who has the selected topic mapped to them will be eligible for enrollment.";
$string['myrole'] = 'My Role.';
$string['myroleerr'] = 'Please Select Role.';
$string['select_not_type'] = 'Select Notification type.';
$string['select_topics'] = 'Select Topic.';
$string['backtohome'] = 'Click here to login';
$string['profileupdate'] = 'Hey there ! Help us <br>to know a bit more about you.';
$string['open_external'] = 'External Users';
$string['select_hrmsrole'] = 'Select role';
$string['select_topicinterest'] = 'Select Topics Interest';
$string['registraionsuccess'] = 'Congratulations, your account has been successfully created.';
$string['createaccount'] = 'Create Account';
$string['continue'] = 'Continue';
$string['contactus'] = 'Help Desk';
$string['mailto'] = 'Mail to';
$string['contactus_desc'] = 'Contact Us Description';
$string['send'] = 'Send';
$string['successmsg'] = 'Thanks for writing us. We will get back as soon as possible';
$string['failedmsg'] = 'Your email has been failed. Please contact the site administrator';
$string['contactus_desc'] = 'If you have any issues or queries or suggestions, please send an email here';
$string['subject'] = 'Subject';
$string['body'] = 'Body';
$string['sme_support'] = 'SME Support';
$string['tech_support'] = 'Technical Support';
$string['missingfirstname'] = 'Missing first name';
$string['missinglastname'] = 'Missing last name';
$string['profile_picture'] = 'Profile picture';
$string['profile_picture_help'] = 'To add a new picture, browse and select an image (in JPG or PNG format) then click "Update profile". The image will be cropped to a square and resized to 100x100 pixels.';
$string['applicationtype'] = 'Application Type';
$string['departmentname'] = 'Department name';
$string['provideorganization'] = '{$a->firstlevel} is missing, provide {$a->firstlevel} info';
$string['providefirstname'] = 'First name is missing, provide first name';
$string['providerole'] = 'Role is missing, provide Role';
$string['providerollno'] = 'Roll no is missing, provide rollno';
$string['providelastname'] = 'Last name is missing, provide last_name';
$string['providecollege'] = '{$a->secondlevel} is missing, provide {$a->secondlevel}';
$string['providepassword'] = 'Password is missing, provide password';
$string['provideemail'] = 'Email is missing, provide email';
$string['provideemployeestatus'] = 'Faculty/Staff status is missing, provide faculty/staff status';
$string['provideemployeephonenumber'] = 'Faculty/Staff phone number is missing, provide faculty/staff phone number';
$string['pleaseselectrole'] = 'Please select Role';
$string['selectdept'] = 'Select department';
$string['pleaseselectdept'] = 'Please select department';
$string['emprole'] = 'Role';

/*profile page starts */
$string['coursesenrolled'] = '<i class="icon popupstringicon fa fa-book" aria-hidden="true"></i>Total Enrolled Courses';
$string['completedcount'] = '<i class="icon popupstringicon fa fa-diamond" aria-hidden="true"></i>Programs Associated';
$string['rating'] = '<i class="fa fa-star" aria-hidden="true"></i> Average Ratings';
$string['duration'] = 'Duration';
$string['batchname'] = 'Batchname';
$string['quizes'] = '<i class="fa fa-briefcase popupstringicon" aria-hidden="true"></i> Quizes';
$string['assignments'] = '<i class="fa fa-tasks popupstringicon" aria-hidden="true"></i> Assignments graded';
$string['questions'] = '<i class="fa fa-question-circle popupstringicon" aria-hidden="true"></i> Questions uploaded';
$string['files'] = '<i class="fa fa-book popupstringicon" aria-hidden="true"></i>
 Files';
$string['attendance'] = 'Attendance';
$string['startdate'] = 'Startdate';
$string['enddate'] = 'Enddate';
$string['allcourses'] = 'All courses';
$string['allstudents'] = 'All students details';
$string['courseprogress'] = 'Course Progress';
$string['latest_courses'] ='Latest Courses';
$string['programs_involved'] ='Programs Involved';

/*Phone Number validations for bulkupload. */
$string['phone_emptymsg'] ='Provide phone number for \'{$a->employee_id}\' of uploaded excelsheet at line {$a->excel_line_number}';
$string['phonenumber_limit'] ='Phone Number should be 10 Digits only';
$string['phonenumber_numeric'] ='Phone Number should be numerics';
$string['error_phonecolumn_heading'] = 'Error in phone number column heading in uploaded excelsheet';
$string['error_status_column_heading'] = 'Error in status column heading in uploaded excelsheet';
$string['pleaseselectorganization'] = 'Please select a organization';
$string['open_departmentidcourse'] = 'Department';
$string['open_departmentidcourse_help'] = 'Department for the course';
$string['open_costcenteriduser'] = 'Organization';
$string['open_costcenteriduser_help'] = 'Organization for the faculty/staff';
$string['open_departmentiduser'] = 'Department';
$string['open_departmentiduser_help'] = 'Department for the faculty/staff';
$string['open_subdepartmentuser'] = 'Sub Department';
$string['open_subdepartmentuser_help'] = 'Sub Department for the faculty/staff';
$string['errormobiledigits'] = 'Mobile No should be 10 digits only';
$string['acceptedtype'] = 'Should be numaric value';
$string['errorphone1']='Please enter valid Phone Number';
$string['miss_org'] = '{$a->firstlevel} column is missing';
$string['miss_firstname'] = 'fisrt_name cloumn is missing';
$string['miss_lastname'] = 'last_name column is missing';
$string['miss_password'] = 'password column is missing';
$string['miss_email'] = 'email column is missing';
$string['miss_userstatus'] = 'faculty/staff_status column is missing';
$string['miss_phone'] = 'phone column is missing';
$string['description'] = 'About myself';
$string['description_help'] = 'About for the faculty/staff';
$string['coursecompletion'] = 'Course Completion';
$string['tobecompleted'] = 'To be completed';
$string['tobecompletedbasedoncompletioncriteria'] = 'To be completed based on completion criteria';
$string['delete_teacher'] = 'You cannot delete <b>{$a->fullname}</b> as He/She is enrolled to a course';

$string['addressdesc'] = 'Address';
$string['addressdesc_help'] = 'Address maxlength is 255 characters only. ';

$string['citydesc'] = 'City';
$string['citydesc_help'] = 'City maxlength is 120 characters only. ';
$string['wrong_department'] = '{$a->secondlevel} "{$a->secondleveldata}" for faculty/staff id \'{$a->employee_id}\' in uploaded excelsheet does not exist in system at line {$a->excel_line_number}';
$string['wrong_subdepartment'] = '{$a->thirdlevel} "{$a->thirdleveldata}" for faculty/staff id \'{$a->employee_id}\' in uploaded excelsheet does not exist in system at line {$a->excel_line_number}';
$string['subdepartmentisnotexists'] = '{$a->thirdlevel} "{$a->thirdleveldata}" for faculty/staff id \'{$a->employee_id}\' in uploaded excelsheet does not exist in system at line {$a->excel_line_number}';
$string['departmentisnotexists'] = '{$a->secondlevel} "{$a->secondleveldata}" for faculty/staff id \'{$a->employee_id}\' in uploaded excelsheet does not exist in system at line {$a->excel_line_number}';
$string['cannotcreateusepasswordadderror'] = 'Faculty/Staff with empty password so cannot create user in add mode at line {$a->linenumber}';
$string['cannothide'] = 'You cannot inactive the <b>{$a}</b> user as it is being used in an active course';
$string['specialcharactersnotallwoed'] = 'You cannot use special charecters';
$string['roleidempty_msg'] = 'Provide Role for Faculty/Staff \'{$a->username}\' of uploaded sheet at line {$a->excel_line_number}. ';;

$string['teachercannotbeAlldept'] = 'Teacher should be assigned to particular {$a}';
$string['teachercannotbeAllsub'] = 'Teacher should be assigned to particular {$a}';
$string['orgadmincannotbedept'] = 'Organisation Admin should be assigned to All ';
$string['orgadmincannotbesub'] = 'Organisation Admin should be assigned to All ';
$string['collegeadmincannotbedept'] = 'College Admin should be assigned to particular {$a}';
$string['departmentadmincannotbedept'] = 'Department Admin should be assigned to particular {$a}';
$string['departmentadmincannotbesub'] = 'Department Admin should be assigned to particular {$a}';
$string['collegeadmincannotbesub'] = 'College Admin should be assigned to All ';


$string['secondlevelcannotbealltocollegeadmin'] = '{$a->secondlevel} cannot be All to College Admin';
$string['thirdlevelcannotbealltodepartmentadmin'] = '{$a->thirdlevel} cannot be All to Department Admin';
$string['thirdlevelcannotbeparticulardepttocollegeadmin'] = '{$a->thirdlevel} can be All/empty to College Admin';
$string['secondlevelroleidempty_tocollegeadmin'] = '{$a->secondlevel} cannot be empty to College Admin';
$string['thirdlevelroleidempty_todepartmentadmin'] = '{$a->thirdlevel} cannot be empty to Department Admin';
$string['secondlevelcannotbealltoteacher'] = '{$a->secondlevel} cannot be All to Teacher';
$string['thirdlevelcannotbealltoteacher'] = '{$a->thirdlevel} cannot be All to Teacher';
$string['thirdlevelroleidempty_toteacher'] = '{$a->thirdlevel} cannot be empty to Teacher';
$string['secondlevelroleidempty_toteacher'] = '{$a->secondlevel} cannot be empty to Teacher';
// $string['secondlevelcannotbealltocollegeadmin'] = '{$a->secondlevel} cannot be All to College Admin';
$string['secondlevelshouldbealltoorgadmin'] = '{$a->secondlevel} Should be All to Organization Admin';
$string['thirdlevelshouldbealltoorgadmin'] = '{$a->thirdlevel} Should be All to Organization Admin';
$string['thirdlevelshouldbealltocollegeadmin'] = '{$a->thirdlevel} Should be All to College Admin';
$string['deptnotexistsincostcenter'] = '{$a->secondlevel} not exists in the above {$a->firstlevel}';
$string['subdeptnotexistsincostcenter'] = '{$a->thirdlevel} not exists in the above {$a->firstlevel}';

$string['rolenotexists'] = '\'{$a->roleshortname}\' role is not exist at the line {$a->excel_line_number}';
$string['roleidnotexists'] = ' You don\'t have permission to create \'{$a->roleshortname}\' user at the line {$a->excel_line_number}';
