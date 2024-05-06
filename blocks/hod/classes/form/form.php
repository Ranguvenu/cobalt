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
 * @package    local_hod
 * @copyright  moodleone
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/formslib.php");
class form extends moodleform {
    public function definition() {
        global $CFG , $DB,$USER;
        $mform = $this->_form;
        $submission = $this->_customdata['submission'];
        $res = $DB->get_record('user',array('id'=>$USER->id));
        
        if($res->levelofapprove == 1){

        $radioarray[] = $mform->createElement('radio', 'approveronestatus', '',get_string('Approve','block_hod'), 1, $attributes);
        $radioarray[] = $mform->createElement('radio', 'approveronestatus', '',get_string('Reject','block_hod'), 2, $attributes);
      
        $mform->addGroup($radioarray, 'submission_statusclass', get_string('SelectStatus','block_hod'), array('class' => 'submission_statusclass'), false);
        $mform->addRule('submission_statusclass' , 'You can submit the information only after the radio button is checked ', 'required', 'client');
        }
        if($res->levelofapprove == 2){

        $radioarray[] = $mform->createElement('radio', 'approvertwostatus', '',get_string('Approve','block_hod'), 1, $attributes);
        $radioarray[] = $mform->createElement('radio', 'approvertwostatus', '',get_string('Reject','block_hod'), 2, $attributes);
    
        $mform->addGroup($radioarray, 'submission_statusclass', get_string('SelectStatus','block_hod'), array('class' => 'submission_statusclass'), false);
        $mform->addRule('submission_statusclass' , 'You can submit the information only after the radio button is checked ', 'required', 'client');
        }
        if($res->levelofapprove == 0){
        $radioarray[] = $mform->createElement('radio', 'status', '',get_string('Approve','block_hod'), 1, $attributes);
         $radioarray[] = $mform->createElement('radio', 'status', '', get_string('Reject','block_hod'), 2, $attributes);
         $radioarray[] = $mform->createElement('radio', 'status', '', get_string('Revision','block_hod'), 3, $attributes);
        $mform->addGroup($radioarray, 'submission_statusclass', get_string('SelectStatus','block_hod'), array('class' => 'submission_statusclass'), false);

         $mform->addRule('submission_statusclass' , 'You can submit the information only after the radio button is checked ', 'required', 'client');
$comment= array(); 
    $comment[null]='Select'; 

    $comment['Specify details of PI (Designation department email ID contact number etc.)'] = get_string('SpecifydetailsofPIDesignation','block_hod');
    $comment['Specify study team members(Co-Investigator Co-PI Research workers)'] = get_string('Specifystudyteammembers','block_hod');

    $comment['Specify details of other department(s) or organizations/institutes to be involved in the study Requires revision of study title'] = get_string('Specifydetailsofother','block_hod');

    $comment['Requires revision of study design'] = get_string('Requiresrevisionofstudydesign','block_hod');
    $comment['Revise primary objectives'] = get_string('Reviseprimaryobjectives','block_hod');
    $comment['Revise secondary objectives'] = get_string('Revisesecondaryobjectives','block_hod');
    $comment['Provide study rationale'] = get_string('Providestudyrationale','block_hod');
    $comment['Provide study hypothesis'] = get_string('Providestudyhypothesis','block_hod');
    $comment['Describe in detail the methods and study procedures'] = get_string('Describeindetailthemethods','block_hod');
    $comment['Describe the basis for sample size'] = get_string('Describethebasis','block_hod');
    $comment['Describe appropriate statistical methods and tests to be used'] = get_string('Describeappropriate','block_hod');
    $comment['Provide relevant references cited in text along with their copies'] =get_string('Providerelevantreferences','block_hod');
    $comment['Provide the possible research output/outcome from the study'] = get_string('Providethepossibleresearch','block_hod');

    $comment['Provide detailed case record form (CRF) for the study'] = get_string('Providedetailedcase','block_hod');
    $comment['Revise Informed Consent Document (ICD) to simplify language for lay persons understanding'] = get_string('ReviseInformedconsentDocument','block_hod');
    
    $comment['Revise Patient Information Sheet(PIS)to simplify language for lay persons understanding'] = get_string('RevisePatientInformationSheet','block_hod');
    $comment['Revise Informed Consent Form(ICF) to simplify language for lay persons understanding'] = get_string('ReviseInformedConsentForm','block_hod');
    $comment['Provide translations for ICD PIS and ICF in Hindi and Marathi'] = get_string('Providetranslationsfor','block_hod');

    $comment['PIS needs revision to include all necessary information about the study(include any potential adverse effects,compensation for participation,insurance cover for participants and other treatment options available for the participants)'] = get_string('PISneedsrevision','block_hod');

    $comment['PIS should mention that participants may receive a placebo (for RCTs)'] = get_string('PISshouldmention','block_hod');
    
    $comment['ICF to be revised as per the New Drugs and Clinical Trial Rules,2019 NewDrugs_CTRules_2019.pdf (cdsco.gov.in)'] = get_string('ICFtoberevisedas','block_hod');

    $comment['Provide assent form along with appropriate translations'] = get_string('Provideassentformalong','block_hod');
    $comment['Revise the ICD,PIS and ICF as per requirements for vulnerable participants'] = get_string('RevisetheICD','block_hod');

    $comment['Provide request for consent waiver(if applicable)'] = get_string('Providerequestfor','block_hod');
    $comment['Provide details of the validation of study questionnaire'] = get_string('Providedetailsofthe','block_hod');
    $comment['Provide Gant chart for the entire study'] = get_string('ProvideGantchart','block_hod');
    $comment['Write study period and duration'] = get_string('Writestudyperiodandduration','block_hod');
    $comment['Mention study population (Inclusion and exclusion criteria)'] = get_string('Mentionstudypopulation','block_hod');
    $comment['Mention study site / department / institute'] = get_string('Mentionstudysite','block_hod');
    $comment['Provide of details of funding/sponsor (if any)'] = get_string('Provideofdetailsoffunding','block_hod');
    $comment['Revise and submit the research proposal and other documents in standard template provided'] = get_string('Reviseandsubmitthe','block_hod');
    
    $comment['Revise the case report form(CRF) to include all necessary information'] = get_string('Revisethecasereport','block_hod');
    
    $comment['Provide all necessary approvals from the relevant authorities for conducting the study'] = get_string('Provideallnecessary','block_hod'); 
    $comment['Submit the revised proposal in print copy(signed by PI,Co-PI,HOD and other collaborating department HOD if needed) to the member secretary of IEC/IAEC'] = get_string('Submittherevisedproposal','block_hod');
    $comment['Others'] = get_string('Others','block_hod');

$options = array
             ( 
            'multiple' => true,
            ); 
             $array = array();
$mform->addElement('select' , 'comment',get_string('comment','block_hod'), $comment,$options);
$mform->disabledIf('comment','status','eq',1);
} 
        $mform->addElement('hidden', 'formid');
        $mform->setType('formid', PARAM_INT);
        $mform->setConstant('formid', $submission->id);
        $buttonarray = array();
        $classarray = array('class' => 'form-submit');
        $buttonarray[] = &$mform->createElement('submit', 'saveanddisplay', get_string('Update','block_hod'), $classarray);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
        $this->set_data($submission);
    }

    public function validation($data, $files) {

        global $DB;
        $submission = $this->_customdata['submission'];
        $errors = array();
 
        // if (empty($data['comment']) && $data['status'] != 1){
        //    $errors['comment'] = 'comment cannot be empty';
        // }
        // if (empty($data['status'])){
        //     $errors['comment'] = 'Missing Status';
        // }
        // print_object(count($data['comment']));exit;
         
        if (!empty($data['comment']) && $data['status'] == 3){
            $submissions = $DB->get_record('submissions' , [ id => $submission->id]);
                if ($submissions->status == 3 && $submissions->countrev == 3){
                    $errors['comment'] = get_string('revisethrice','block_hod');
                }
           
        } 
        if (count($data['comment']) > 4) {
                $errors['comment'] = get_string('Commentstatus','block_hod');
        }
        
        return $errors;
    }
}
