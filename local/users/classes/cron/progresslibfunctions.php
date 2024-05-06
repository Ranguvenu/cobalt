<?php 
namespace local_users\cron;
/**
 * Validation callback function - verified the column line of csv file.
 * Converts standard column names to lowercase.
 * @param csv_import_reader $cir
 * @param array $stdfields standard user fields
 * @param array $profilefields custom profile fields
 * @param moodle_url $returnurl return url in case of any error
 * @return array list of fields
 */
use csv_import_reader;
use moodle_url;
use core_text;
class progresslibfunctions{
    /**
     * [uu_validate_user_upload_columns description]
     * @param  csv_import_reader $cir           [description]
     * @param  array             $stdfields     [standarad fields in user table]
     * @param  array             $profilefields [profile fields in user table]
     * @param  moodle_url        $returnurl     [moodle return page url]
     * @return array                            [validated fields in csv uploaded]
     */
    public function uu_validate_user_upload_columns(csv_import_reader $cir, $stdfields, $profilefields, moodle_url $returnurl) {

        $columns = $cir->get_columns();
        $labelstring = get_config('local_costcenter');
        $firstlevel = strtolower($labelstring->firstlevel);
        $secondlevel = strtolower($labelstring->secondlevel);
        $thirdlevel = strtolower($labelstring->thirdlevel);

        // $requiredcolumn = ['organization', 'first_name', 'last_name', 'password', 'email', 'student_status','phone','city','address', 'department', 'subdepartment', 'rollno'];
        $requiredcolumn = [$firstlevel, 'first_name', 'last_name', 'password', 'email', 'student_status','phone','city','address',$secondlevel, $thirdlevel, 'rollno'];
        $notexistscolumn = array_diff($columns, $requiredcolumn);
        foreach ($notexistscolumn as $key => $unused) {
            switch ($key) {
                case '0':
                    $message=get_string( 'miss_org','local_users', $labelstring);
                    $type = (\core\output\notification::NOTIFY_ERROR);
                    echo $msg =\core\notification::add($message, $type);
                    break;
                case '1':
                    $message=get_string( 'miss_firstname','local_users');
                    $type = (\core\output\notification::NOTIFY_ERROR);
                    echo $msg =\core\notification::add($message, $type);
                    break;
                
                case '2':
                    $message=get_string( 'miss_lastname','local_users');
                    $type = (\core\output\notification::NOTIFY_ERROR);
                    echo $msg =\core\notification::add($message, $type);
                    break;
                
                case '3':
                    $message=get_string( 'miss_password','local_users');
                    $type = (\core\output\notification::NOTIFY_ERROR);
                    echo $msg =\core\notification::add($message, $type);
                    break;
                
                case '4':
                   $message=get_string( 'miss_email','local_users');
                    $type = (\core\output\notification::NOTIFY_ERROR);
                    echo $msg =\core\notification::add($message, $type);
                    break;
                
                case '5':
                    $message=get_string( 'miss_userstatus','local_users');
                    $type = (\core\output\notification::NOTIFY_ERROR);
                    echo $msg =\core\notification::add($message, $type);
                    break;
                
                case '6':
                    $message=get_string( 'miss_phone','local_users');
                    $type = (\core\output\notification::NOTIFY_ERROR);
                    echo $msg =\core\notification::add($message, $type);
                    break;
                case '7':
                    $message=get_string( 'miss_city','local_users');
                    $type = (\core\output\notification::NOTIFY_ERROR);
                    echo $msg =\core\notification::add($message, $type);
                    break;

                case '8':
                    $message=get_string( 'miss_address','local_users');
                    $type = (\core\output\notification::NOTIFY_ERROR);
                    echo $msg =\core\notification::add($message, $type);
                    break;

                // case '9':
                //     $message=get_string( 'miss_department','local_users');
                //     $type = (\core\output\notification::NOTIFY_ERROR);
                //     echo $msg =\core\notification::add($message, $type);
                //     break;

                // case '10':
                //     $message=get_string( 'miss_subdepartment','local_users');
                //     $type = (\core\output\notification::NOTIFY_ERROR);
                //     echo $msg =\core\notification::add($message, $type);
                //     break;

                case '11':
                    $message=get_string( 'miss_rollno','local_users');
                    $type = (\core\output\notification::NOTIFY_ERROR);
                    echo $msg =\core\notification::add($message, $type);
                    break;
            }
        }

        if (empty($columns)) {
            $cir->close();
            $cir->cleanup();
            print_error('cannotreadtmpfile', 'error', $returnurl);
        }

        if (empty($columns)) {
            $cir->close();
            $cir->cleanup();
            print_error('cannotreadtmpfile', 'error', $returnurl);
        }
        if (count($columns) < 2) {
            $cir->close();
            $cir->cleanup();
            print_error('csvfewcolumns', 'error', $returnurl);
        }
        // test columns
        $processed = array();
        foreach ($columns as $key => $unused) {
            $field = $columns[$key];
            $lcfield = core_text::strtolower($field);
            if (in_array($field, $stdfields) or in_array($lcfield, $stdfields)) {
                // standard fields are only lowercase
                $newfield = $lcfield;
            } else if (in_array($field, $profilefields)) {
                // exact profile field name match - these are case sensitive
                $newfield = $field;
            } else if (in_array($lcfield, $profilefields)) {
                // hack: somebody wrote uppercase in csv file, but the system knows only lowercase profile field
                $newfield = $lcfield;
            } else if (preg_match('/^(cohort|user|group|type|role|enrolperiod)\d+$/', $lcfield)) {
                // special fields for enrolments
                $newfield = $lcfield;
            } else {
                $cir->close();
                $cir->cleanup();
                echo "<a href = '$returnurl' class ='btn btn-primary required_filed'>continue</a>";
                die;
            }
            if (in_array($newfield, $processed)) {
                $cir->close();
                $cir->cleanup();
            }
            $processed[$key] = $newfield;    
        }
        return $processed;
    }
}
