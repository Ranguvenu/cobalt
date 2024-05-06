<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->libdir/filelib.php");

/**
 * Files external functions
 *
 * @package    block_proposals
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
 // use \block_proposals\form;

 class block_proposal_external extends external_api {
 public static function deleteconfirm_parameters() {
   return new external_function_parameters(
     array(
       'id'  => new external_value(PARAM_INT, 'id',0),
     )
   );
 }
 public static function deleteconfirm($id) {
   global $DB,$USER;
   $params=self::validate_parameters (
     self::deleteconfirm_parameters(),array('id'=>$id)
   );
   $context=context_system::instance();
   self::validate_context($context);
   if ($id) {
    $DB->delete_records('submissions', array('id' => $id));
  } else {
     throw new moodle_exception('Error');
   }
 }
 public static function deleteconfirm_returns(){
   return null;
 }
 
}
