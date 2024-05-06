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
 * @package    block_proposals
 * @copyright  moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");


class nonfunded extends moodleform {
    public function definition() {
        global $CFG,$DB;
        
        $mform = $this->_form;
        
        $fid = $_GET['formid'];
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_NOTAGS);
        $strrequired = get_string('required','block_proposals');

        $mform->addElement('html','<div>');
        //Funded Elements
        $mform->addElement('html','<div id="nonfunded">'); 
        $res=$DB->get_records('applicationtable',['type' =>NonFunded]);
        $applicationtype = array();
        $applicationtype[null]='Select';
        foreach ($res as $key => $value) {
          $applicationtype[$key] = $value->applicationtype;
        }
        $mform->addElement('select' , 'applicationtype' ,get_string('applicationtype','block_proposals'),$applicationtype);
        $mform->addRule('applicationtype', get_string('applicationtype', 'block_proposals'), 'required', null, 'server');
        
        $title=array('placeholder' => 'Title of the Proposed Research Project 250 characters');
        $mform->addElement('text' , 'title', get_string('titleproject','block_proposals'),$title);
        $mform->addHelpButton('title', 'title' , 'block_proposals');
        $mform->setType('title', PARAM_NOTAGS);
        $mform->addRule('title' , get_string('twofivcharacters','block_proposals') , 'required' , 250 , 'server');
        $mform->addRule('title' , get_string('twofivcharacters','block_proposals') , 'maxlength' , 250 , 'server');

        // Scientific title of the study
        $scientifictitleofthestudy=array('placeholder' => '250 characters');
        $mform->addElement('text','scientifictitleofthestudy',get_string('Scientifictitleofthestudy','block_proposals'),$scientifictitleofthestudy);
        $mform->addRule('scientifictitleofthestudy' , get_string('twofivcharacters','block_proposals') , 'required' , 250 , 'server');
        $mform->addRule('scientifictitleofthestudy' , get_string('twofivcharacters','block_proposals') , 'maxlength' , 250 , 'server');
        // Principle investigator
        $principleinvestigator=array('placeholder' => '100 characters');
        $mform->addElement('text','principleinvestigator',get_string('Principleinvestigator','block_proposals'),$principleinvestigator);
        $mform->addRule('principleinvestigator' , get_string('huncharacters','block_proposals') , 'required' , 100 , 'server');
        $mform->addRule('principleinvestigator' , get_string('huncharacters','block_proposals') , 'maxlength' , 100 , 'server');
          
        $coinvestigator1=array('placeholder' => '30 characters');
        $mform->addElement('text','coinvestigator1',get_string('co-investigator1','block_proposals'),$coinvestigator1);
        $mform->addRule('coinvestigator1' , get_string('thrcharacters','block_proposals') , 'required' , 30 , 'server');
        $mform->addRule('coinvestigator1' , get_string('thrcharacters','block_proposals') , 'maxlength' , 30 , 'server');

        $coinvestigator2=array('placeholder' => '30 characters');
        $mform->addElement('text','coinvestigator2',get_string('co-investigator2','block_proposals'),$coinvestigator2);
        $mform->addRule('coinvestigator2' , get_string('thrcharacters','block_proposals') , 'required' , 30 , 'server');
        $mform->addRule('coinvestigator2' , get_string('thrcharacters','block_proposals') , 'maxlength' , 30 , 'server');

        $coinvestigator3=array('placeholder' => '30 characters');
        $mform->addElement('text','coinvestigator3',get_string('co-investigator3','block_proposals'),$coinvestigator3);
        $mform->addRule('coinvestigator3' , get_string('thrcharacters','block_proposals') , 'required' , 30 , 'server');
        $mform->addRule('coinvestigator3' , get_string('thrcharacters','block_proposals') , 'maxlength' , 30 , 'server');

        $coinvestigator4=array('placeholder' => '30 characters');
        $mform->addElement('text','coinvestigator4',get_string('co-investigator4','block_proposals'),$coinvestigator4);
        $mform->addRule('coinvestigator4' , get_string('thrcharacters','block_proposals') , 'required' , 30 , 'server');
        $mform->addRule('coinvestigator4' , get_string('thrcharacters','block_proposals') , 'maxlength' , 30 , 'server');

        $coinvestigator5=array('placeholder' => '30 characters');
        $mform->addElement('text','coinvestigator5',get_string('co-investigator5','block_proposals'),$coinvestigator5);
        $mform->addRule('coinvestigator5' , get_string('thrcharacters','block_proposals') , 'required' , 30 , 'server');
        $mform->addRule('coinvestigator5' , get_string('thrcharacters','block_proposals') , 'maxlength' , 30 , 'server');
 
        // $res=$DB->get_records('department');
        // $departmentname = array();
        // $departmentname[null]='Select';
        // foreach ($res as $key => $value) {
        //   $departmentname[$key] = $value->departmentname;
        // }

        // $mform->addElement('select' , 'departmentname',get_string('Departmentname','block_proposals'), $departmentname);
        // $mform->addRule('departmentname', get_string('mustbeselect','block_proposals'),'required', null, 'server');


        $sql= $DB->get_records_menu('department');
        $mform->addElement('searchableselector', 'departmentname', get_string('Departmentname','block_proposals'), $sql);
        $mform->addRule('departmentname', get_string('mustbeselect','block_proposals'),'required', null, 'server');
        // Name of the Guide

        $nameoftheguide= array();
        $nameoftheguide[null]='Select';
        $nameoftheguide['Aashay Shah'] = 'Aashay Shah';
        $nameoftheguide['Abdul Malik Abdul Moeen Nagori
'] = 'Abdul Malik Abdul Moeen Nagori
';
        $nameoftheguide['Abhay Gangadhar Uppe'] = 'Abhay Gangadhar Uppe';
        $nameoftheguide['Abhay Sadashiv Chowdhary'] = 'Abhay Sadashiv Chowdhary';
        $nameoftheguide['Abhijit Guruprasad Bagul'] = 'Abhijit Guruprasad Bagul';
        $nameoftheguide['Abhijit Ramdas Budhkar'] = 'Abhijit Ramdas Budhkar';
        $nameoftheguide['Abhishek G Mahadik'] = 'Abhishek G Mahadik';
        $nameoftheguide['Adeel Inamulhaque Ansari'] = 'Adeel Inamulhaque Ansari';
        $nameoftheguide['Aditi Kaundinya'] = 'Aditi Kaundinya';
        $nameoftheguide['Aditya Chandrashekhar Oak'] = 'Aditya Chandrashekhar Oak';
        $nameoftheguide['Aditya Rajendra Gunjotikar'] = 'Aditya Rajendra Gunjotikar';
        $nameoftheguide['Ajinkya Deepak Achalare'] = 'Ajinkya Deepak Achalare';
        $nameoftheguide['Ajit Subhash Baviskar'] = 'Ajit Subhash Baviskar';
        $nameoftheguide['Akanksha Saberwal'] = 'Akanksha Saberwal';
        $nameoftheguide['Akshada Vitthal Dhumal'] = 'Akshada Vitthal Dhumal';
        $nameoftheguide['Alok Ashok Gangurde'] = 'Alok Ashok Gangurde';
        $nameoftheguide['Amit Baloo Garud'] = 'Amit Baloo Garud';
        $nameoftheguide['Amit Chandrashekhar Kulkarni'] = 'Amit Chandrashekhar Kulkarni';
        $nameoftheguide['Amit Prakash Nagarik'] = 'Amit Prakash Nagarik';
        $nameoftheguide['Amit Shyammohan Saxena'] = 'Amit Shyammohan Saxena';
        $nameoftheguide['Amita Verma'] = 'Amita Verma';
        $nameoftheguide['Amol Sunil Chaudhari'] = 'Amol Sunil Chaudhari';
        $nameoftheguide['Anand Anil Ambekar'] = 'Anand Anil Ambekar';
        $nameoftheguide['Anand Prabhakar Sude'] = 'Anand Prabhakar Sude';
        $nameoftheguide['Anant Dhondapant Patil'] = 'Anant Dhondapant Patil';
        $nameoftheguide['Aniket PAndharinath Patil'] = 'Aniket PAndharinath Patil';
        $nameoftheguide['Aniket Pandurang Patil'] = 'Aniket Pandurang Patil';
        $nameoftheguide['Anilkumar Kanaiyalal Gvalani'] = 'Anilkumar Kanaiyalal Gvalani';
        $nameoftheguide['Aniruddh Shringare'] = 'Aniruddh Shringare';
        $nameoftheguide['Anisha Ameena Bashir'] = 'Anisha Ameena Bashir';
        $nameoftheguide['Anita Sharan'] = 'Anita Sharan';
        $nameoftheguide['Anupriya Premraj Mohokar'] = 'Anupriya Premraj Mohokar';
        $nameoftheguide['Anuradha Harshad Panchal'] = 'Anuradha Harshad Panchal';
        $nameoftheguide['Anuradha Malliwal'] = 'Anuradha Malliwal';
        $nameoftheguide['Anurag Asim'] = 'Anurag Asim';
        $nameoftheguide['Anuragdeep Syal'] = 'Anuragdeep Syal';
        $nameoftheguide['Anuya Anil Pawde'] = 'Anuya Anil Pawde';
        $nameoftheguide['Aparna Sagare'] = 'Aparna Sagare';
        $nameoftheguide['Apurva Diliprao Shinde'] = 'Apurva Diliprao Shinde';
        $nameoftheguide['Archana Akshay Kadam'] = 'Archana Akshay Kadam';
        $nameoftheguide['Archana Hrishikesh Tadwalkar'] = 'Archana Hrishikesh Tadwalkar';
        $nameoftheguide['Archana Rahul Bhate'] = 'Archana Rahul Bhate';
        $nameoftheguide['Arpit Rajendra Rajpurohit'] = 'Arpit Rajendra Rajpurohit';
        $nameoftheguide['Arundhati Barua'] = 'Arundhati Barua';
        $nameoftheguide['Asawari Sujendra Arwikar'] = 'Asawari Sujendra Arwikar';
        $nameoftheguide['Ashish Vaman Wagh'] = 'Ashish Vaman Wagh';
        $nameoftheguide['Ashish Vasudeo Dhande'] = 'Ashish Vasudeo Dhande';
        $nameoftheguide['Ashvini Vijaysinh Deshmukh'] = 'Ashvini Vijaysinh Deshmukh';
        $nameoftheguide['Aswathy Radhakrishnan'] = 'Aswathy Radhakrishnan';
        $nameoftheguide['Audumbar Dashrath Borgaonkar'] = 'Audumbar Dashrath Borgaonkar';
        $nameoftheguide['Avinash Eknath Chaudhari'] = 'Avinash Eknath Chaudhari';
        $nameoftheguide['Ayesha Haroon Sayed'] = 'Ayesha Haroon Sayed';
        $nameoftheguide['Balasaheb Tukaram Govardhane'] = 'Balasaheb Tukaram Govardhane';
        $nameoftheguide['Bharat Pandit Shinde'] = 'Bharat Pandit Shinde';
        $nameoftheguide['Bharati Vivek Joshi Nalgirkar'] = 'Bharati Vivek Joshi Nalgirkar';
        $nameoftheguide['Bhasker Semitha Bhasker'] = 'Bhasker Semitha Bhasker';
        $nameoftheguide['Bhavika S Verma'] = 'Bhavika S Verma';
        $nameoftheguide['Bhavishya Sundar'] = 'Bhavishya Sundar';
        $nameoftheguide['Bhushan Jaywantrao Patil'] = 'Bhushan Jaywantrao Patil';
        $nameoftheguide['Bhushan Kashinath Chavan'] = 'Bhushan Kashinath Chavan';
        $nameoftheguide['Bibekananda Mahapatra'] = 'Bibekananda Mahapatra';
        $nameoftheguide['Cassandra Anna Carvalho'] = 'Cassandra Anna Carvalho';
        $nameoftheguide['Chalak Ajit Rajshekhar'] = 'Chalak Ajit Rajshekhar';
        $nameoftheguide['Chandan Singh'] = 'Chandan Singh';
        $nameoftheguide['Cherian Philemon Kurian'] = 'Cherian Philemon Kurian';
        $nameoftheguide['Chetan Dilip Vispute'] = 'Chetan Dilip Vispute';
        $nameoftheguide['Chinmayi Pramod Bokey'] = 'Chinmayi Pramod Bokey';
        $nameoftheguide['Dasari Anurag'] = 'Dasari Anurag';
        $nameoftheguide['Dattatray Dnyandeo Musmade'] = 'Dattatray Dnyandeo Musmade';
        $nameoftheguide['Deepa Haritosh Velankar'] = 'Deepa Haritosh Velankar';
        $nameoftheguide['Deepak Kumar Govindrao Langade'] = 'Deepak Kumar Govindrao Langade';
        $nameoftheguide['Deepak Ramdas Kamble'] = 'Deepak Ramdas Kamble';
        $nameoftheguide['Deepali Amarsinh Vidhate'] = 'Deepali Amarsinh Vidhate';
        $nameoftheguide['Deepali Rishi Rajpal'] = 'Deepali Rishi Rajpal';
        $nameoftheguide['Deepika Gulati'] = 'Deepika Gulati';
        $nameoftheguide['Deepika Murali Iyangar'] = 'Deepika Murali Iyangar';
        $nameoftheguide['Dharmik Dilip Bhuva'] = 'Dharmik Dilip Bhuva';
        $nameoftheguide['Dheeman Sarkar'] = 'Dheeman Sarkar';
        $nameoftheguide['Dinesh Chandra Gupta'] = 'Dinesh Chandra Gupta';
        $nameoftheguide['Dinesh Jagannath Patil'] = 'Dinesh Jagannath Patil';
        $nameoftheguide['Dipak Sudam Ahire'] = 'Dipak Sudam Ahire';
        $nameoftheguide['Dipali Chandrakant Puri'] = 'Dipali Chandrakant Puri';
        $nameoftheguide['Dipika Mohan Koli'] = 'Dipika Mohan Koli';
        $nameoftheguide['Dipin Kumar Yadav'] = 'Dipin Kumar Yadav';
        $nameoftheguide['Divya Daga'] = 'Divya Daga';
        $nameoftheguide['Divya Dayanand Patil'] = 'Divya Dayanand Patil';
        $nameoftheguide['Divya Ramadoss'] = 'Divya Ramadoss';
        $nameoftheguide['Divya Shekhar Shetty'] = 'Divya Shekhar Shetty';
        $nameoftheguide['Fehmida Najmuddin'] = 'Fehmida Najmuddin';
        $nameoftheguide['Felin Ann Francis'] = 'Felin Ann Francis';
        $nameoftheguide['Fiona Patra'] = 'Fiona Patra';
        $nameoftheguide['Gandi Sudha Rani'] = 'Gandi Sudha Rani';
        $nameoftheguide['Ganesh Arjun Avhad'] = 'Ganesh Arjun Avhad';
        $nameoftheguide['Gehdoo Raghuveer Singh Pritam Singh'] = 'Gehdoo Raghuveer Singh Pritam Singh';
        $nameoftheguide['Ghanshyam Ramnath Kane'] = 'Ghanshyam Ramnath Kane';
        $nameoftheguide['Girija Prakash Nair'] = 'Girija Prakash Nair';
        $nameoftheguide['Gupta Vijaykumar Kanhaiyalal'] = 'Gupta Vijaykumar Kanhaiyalal';
        $nameoftheguide['Haritosh Kamalakar Velankar'] = 'Haritosh Kamalakar Velankar';
        $nameoftheguide['Harsha Sameer Pagad'] = 'Harsha Sameer Pagad';
        $nameoftheguide['Harshit Pawankumar Thole'] = 'Harshit Pawankumar Thole';
        $nameoftheguide['Hemant Nandkishor Lahoti'] = 'Hemant Nandkishor Lahoti';
        $nameoftheguide['Hetal Kirit Shah'] = 'Hetal Kirit Shah';
        $nameoftheguide['Hritika Sharma'] = 'Hritika Sharma';
        $nameoftheguide['Huda Ali Sayed'] = 'Huda Ali Sayed';
        $nameoftheguide['Jawahar Athmaram Vontivillu'] = 'Jawahar Athmaram Vontivillu';
        $nameoftheguide['Jayant Laxman Pednekar'] = 'Jayant Laxman Pednekar';
        $nameoftheguide['Jayendra Giridhar Yadav'] = 'Jayendra Giridhar Yadav';
        $nameoftheguide['Jayshree Prakash Vaswani'] = 'Jayshree Prakash Vaswani';
        $nameoftheguide['Jeetendra Dinkar Chavan'] = 'Jeetendra Dinkar Chavan';
        $nameoftheguide['Jesheen Kaur Joshbir Mann'] = 'Jesheen Kaur Joshbir Mann';
        $nameoftheguide['Joseph Sengol Gounder'] = 'Joseph Sengol Gounder';
        $nameoftheguide['Jyoti Amol Pawar'] = 'Jyoti Amol Pawar';
        $nameoftheguide['Kailash Khandeshwarrao Jawade'] = 'Kailash Khandeshwarrao Jawade';
        $nameoftheguide['Kalpesh Uttam Chaudhari'] = 'Kalpesh Uttam Chaudhari';
        $nameoftheguide['Kammara Vinod Achari'] = 'Kammara Vinod Achari';
        $nameoftheguide['Kanade Gaurav Gajanan'] = 'Kanade Gaurav Gajanan';
        $nameoftheguide['Kanika Rai'] = 'Kanika Rai';
        $nameoftheguide['Kapil Shivsing Bainade'] = 'Kapil Shivsing Bainade';
        $nameoftheguide['Kavitha Vivek Dongerkery'] = 'Kavitha Vivek Dongerkery';
        $nameoftheguide['Keertana Sadanand Shetty'] = 'Keertana Sadanand Shetty';
        $nameoftheguide['Keshav Dada Kale'] = 'Keshav Dada Kale';
        $nameoftheguide['Ketan Laxman Pakhale'] = 'Ketan Laxman Pakhale';
        $nameoftheguide['Ketan Ramesh Vagholkar'] = 'Ketan Ramesh Vagholkar';
        $nameoftheguide['Keya Rani Lahiri'] = 'Keya Rani Lahiri';
        $nameoftheguide['Khilchand Dilip Bhangale'] = 'Khilchand Dilip Bhangale';
        $nameoftheguide['Kiran Vasant Godse'] = 'Kiran Vasant Godse';
        $nameoftheguide['Kirtibala Ashish Dhande'] = 'Kirtibala Ashish Dhande';
        $nameoftheguide['Kisan Rajaram Khade'] = 'Kisan Rajaram Khade';
        $nameoftheguide['Krishna Kirit Bhadiadra'] = 'Krishna Kirit Bhadiadra';
        $nameoftheguide['Krishnarao Narayan Bhosle'] = 'Krishnarao Narayan Bhosle';
        $nameoftheguide['Kritika Singh K Yadav'] = 'Kritika Singh K Yadav';
        $nameoftheguide['Krushna Rambhau Borkar'] = 'Krushna Rambhau Borkar';
        $nameoftheguide['Kulkarni Amar Vilas'] = 'Kulkarni Amar Vilas';
        $nameoftheguide['Kumar Abhineet Chand'] = 'Kumar Abhineet Chand';
        $nameoftheguide['Kunapuli Sri Satya Ramdev'] = 'Kunapuli Sri Satya Ramdev';
        $nameoftheguide['Madhurima Suresh Nair'] = 'Madhurima Suresh Nair';
        $nameoftheguide['Mahesh Virayya Padsalge'] = 'Mahesh Virayya Padsalge';
        $nameoftheguide['Maitreya Jagdish Patil'] = 'Maitreya Jagdish Patil';
        $nameoftheguide['Maitri Kishor Mehta'] = 'Maitri Kishor Mehta';
        $nameoftheguide['Manasi Satish More'] = 'Manasi Satish More';
        $nameoftheguide['Manish Shriram Pendse'] = 'Manish Shriram Pendse';
        $nameoftheguide['Manisha Mishra'] = 'Manisha Mishra';
        $nameoftheguide['Manisha Sandeep Nakhate'] = 'Manisha Sandeep Nakhate';
        $nameoftheguide['Manju Bhashini Sundru'] = 'Manju Bhashini Sundru';
        $nameoftheguide['Manjula Sudeep Sarkar'] = 'Manjula Sudeep Sarkar';
        $nameoftheguide['Manjyot Manish Gautam'] = 'Manjyot Manish Gautam';
        $nameoftheguide['Manmohan Prithviraj Madan'] = 'Manmohan Prithviraj Madan';
        $nameoftheguide['Manohar Rajendra Joshi'] = 'Manohar Rajendra Joshi';
        $nameoftheguide['Manpreet Kaur Harbans Singh Juneja'] = 'Manpreet Kaur Harbans Singh Juneja';

        $nameoftheguide['Mayank Chourasia'] = 'Mayank Chourasia';
        $nameoftheguide['Mayuri Vijay Ghorpade'] = 'Mayuri Vijay Ghorpade';
        $nameoftheguide['Mayuri Vinayak More'] = 'Mayuri Vinayak More';
        $nameoftheguide['Meena Kumar'] = 'Meena Kumar';
        $nameoftheguide['Meena Naresh Satia'] = 'Meena Naresh Satia';
        $nameoftheguide['Meghana Vijay Choudhary'] = 'Meghana Vijay Choudhary';
        $nameoftheguide['Mehreen Shahid'] = 'Mehreen Shahid';
        $nameoftheguide['Mohammed Hishaam M A'] = 'Mohammed Hishaam M A';
        $nameoftheguide['Mona Sanjeev Kumar Jadhav'] = 'Mona Sanjeev Kumar Jadhav';
        $nameoftheguide['Mradula Nilesh Kulapkar'] = 'Mradula Nilesh Kulapkar';
        $nameoftheguide['Mrunalini Rajendra Kanvinde'] = 'Mrunalini Rajendra Kanvinde';
        $nameoftheguide['Mukta Jain'] = 'Mukta Jain';
        $nameoftheguide['Mumtaz Sharif'] = 'Mumtaz Sharif';
        $nameoftheguide['Nandan Shrikant Purandare'] = 'Nandan Shrikant Purandare';
        $nameoftheguide['Nandita Amit Saxena'] = 'Nandita Amit Saxena';
        $nameoftheguide['Nayan Shrivastava'] = 'Nayan Shrivastava';
        $nameoftheguide['Neelam Chhillar'] = 'Neelam Chhillar';
        $nameoftheguide['Neelu Elon'] = 'Neelu Elon';
        $nameoftheguide['Neeti Mathur'] = 'Neeti Mathur';
        $nameoftheguide['Neha Anil Momale'] = 'Neha Anil Momale';
        $nameoftheguide['Nida Abdulrahim Khan'] = 'Nida Abdulrahim Khan';
        $nameoftheguide['Niharika Ranjan'] = 'Niharika Ranjan';
        $nameoftheguide['Niket Ashok Attarde'] = 'Niket Ashok Attarde';
        $nameoftheguide['Nikhil Chandrashekhar Sarangdhar'] = 'Nikhil Chandrashekhar Sarangdhar';
        $nameoftheguide['Nikhil Mahesh Gurjar'] = 'Nikhil Mahesh Gurjar';
        $nameoftheguide['Nilesh Suryakant Ingale'] = 'Nilesh Suryakant Ingale';
        $nameoftheguide['Nilofar Imamhusen Yelurkar'] = 'Nilofar Imamhusen Yelurkar';
        $nameoftheguide['Nitin Jayanand Nadkarni'] = 'Nitin Jayanand Nadkarni';
        $nameoftheguide['Nitin Nandkumar Jagdhane'] = 'Nitin Nandkumar Jagdhane';
        $nameoftheguide['Nitin Shantiel Bharos'] = 'Nitin Shantiel Bharos';
        $nameoftheguide['Nivedita Devabrata Moulick'] = 'Nivedita Devabrata Moulick';
        $nameoftheguide['Pallavi Gahlowt'] = 'Pallavi Gahlowt';
        $nameoftheguide['Pallavi Prashant Basapure'] = 'Pallavi Prashant Basapure';
        $nameoftheguide['Pallavi Sachin Chitnis'] = 'Pallavi Sachin Chitnis';
        $nameoftheguide['Pankaj Baban Tule'] = 'Pankaj Baban Tule';
        $nameoftheguide['Parag Ravindra Chaudhari'] = 'Parag Ravindra Chaudhari';
        $nameoftheguide['Patel Abhirajsinh'] = 'Patel Abhirajsinh';
        $nameoftheguide['Patel Rajas Balkrishna'] = 'Patel Rajas Balkrishna';
        $nameoftheguide['Pooja Girdharilal Binnani'] = 'Pooja Girdharilal Binnani';
        $nameoftheguide['Pooja Kochar'] = 'Pooja Kochar';
        $nameoftheguide['Pooja Mahesh Ghogare'] = 'Pooja Mahesh Ghogare';
        $nameoftheguide['Prachi Jaysingh Sankhe'] = 'Prachi Jaysingh Sankhe';
        $nameoftheguide['Pradeep Bijaynath Tiwari'] = 'Pradeep Bijaynath Tiwari';
        $nameoftheguide['Pradnya Sandesh Deolekar'] = 'Pradnya Sandesh Deolekar';
        $nameoftheguide['Pragati Salil Upasham'] = 'Pragati Salil Upasham';
        $nameoftheguide['Prajakta Ananta Kulkarni'] = 'Prajakta Ananta Kulkarni';
        $nameoftheguide['Prajakta M. Radke'] = 'Prajakta M. Radke';
        $nameoftheguide['Prakash Dattatraya Samant'] = 'Prakash Dattatraya Samant';
        $nameoftheguide['Prakash Madhukar Dive'] = 'Prakash Madhukar Dive';
        $nameoftheguide['Pramila Shriram Yadav'] = 'Pramila Shriram Yadav';
        $nameoftheguide['Prasad Liladhar Chaudhari'] = 'Prasad Liladhar Chaudhari';
        $nameoftheguide['Prasad Prabhakar Kulkarni'] = 'Prasad Prabhakar Kulkarni';
        $nameoftheguide['Prashant Dasharath Purkar'] = 'Prashant Dasharath Purkar';
        $nameoftheguide['Prashant Pandey'] = 'Prashant Pandey';
        $nameoftheguide['Preeti Samir Pachpute'] = 'Preeti Samir Pachpute';
        $nameoftheguide['Premkumar Ramkishan Maurya'] = 'Premkumar Ramkishan Maurya';
        $nameoftheguide['Prithi Rajendra Inamdar'] = 'Prithi Rajendra Inamdar';
        $nameoftheguide['Priya Narasinhrao Deshpande'] = 'Priya Narasinhrao Deshpande';
        $nameoftheguide['Priyadarshini R Cholera'] = 'Priyadarshini R Cholera';
        $nameoftheguide['Priyanka'] = 'Priyanka';
        $nameoftheguide['Priyanka Anil Choudhari'] = 'Priyanka Anil Choudhari';
        $nameoftheguide['Priyanka Balaram Jadhav'] = 'Priyanka Balaram Jadhav';
        $nameoftheguide['Priyanka Balasaheb Dhobale'] = 'Priyanka Balasaheb Dhobale';
        $nameoftheguide['Rahul Prakash Zalse'] = 'Rahul Prakash Zalse';
        $nameoftheguide['Rahul Shrikrishna Siraskar'] = 'Rahul Shrikrishna Siraskar';
        $nameoftheguide['Raj Panchdeo Gautam'] = 'Raj Panchdeo Gautam';
        $nameoftheguide['Rajdeep Salil Pal'] = 'Rajdeep Salil Pal';
        $nameoftheguide['Rajendraprasad Ramesh Butala'] = 'Rajendraprasad Ramesh Butala';
        $nameoftheguide['Rajesh Kumar Rai'] = 'Rajesh Kumar Rai';
        $nameoftheguide['Rajiv Jairaj Rao'] = 'Rajiv Jairaj Rao';
        $nameoftheguide['Rajshekhar Keshavrao Yadav'] = 'Rajshekhar Keshavrao Yadav';
        $nameoftheguide['Raju Laxmanrao Patil'] = 'Raju Laxmanrao Patil';
        $nameoftheguide['Rakhi Milind MOre'] = 'Rakhi Milind MOre';
        $nameoftheguide['Ravi Prakash Naulakha'] = 'Ravi Prakash Naulakha';
        $nameoftheguide['Ravindra Mahadeo Kattimani'] = 'Ravindra Mahadeo Kattimani';
        $nameoftheguide['Reetu Singhal'] = 'Reetu Singhal';
        $nameoftheguide['Rewa Amit Garud'] = 'Rewa Amit Garud';
        $nameoftheguide['Richa Singh'] = 'Richa Singh';
        $nameoftheguide['Rita Swaminathan'] = 'Rita Swaminathan';
        $nameoftheguide['Ritika Khurana'] = 'Ritika Khurana';
        $nameoftheguide['Rochana Girish Bakhshi'] = 'Rochana Girish Bakhshi';
        $nameoftheguide['Rohan Bharat Gala'] = 'Rohan Bharat Gala';
        $nameoftheguide['Rohan Dattu Patil'] = 'Rohan Dattu Patil';
        $nameoftheguide['Rohan Pradeep Palshetkar'] = 'Rohan Pradeep Palshetkar';
        $nameoftheguide['Rohan Sawant'] = 'Rohan Sawant';
        $nameoftheguide['Rohit Mahesh Sane'] = 'Rohit Mahesh Sane';
        $nameoftheguide['Roopashri Omprakash Jamadar'] = 'Roopashri Omprakash Jamadar';
        $nameoftheguide['Rubina Manzoorhussain Patankar'] = 'Rubina Manzoorhussain Patankar';
        $nameoftheguide['Ruma Nooreen'] = 'Ruma Nooreen';
        $nameoftheguide['Ruta Vinayak Bapat'] = 'Ruta Vinayak Bapat';
        $nameoftheguide['Sachin Yashwant  Kale'] = 'Sachin Yashwant  Kale';
        $nameoftheguide['Sadhana Subodh Mendhurwar'] = 'Sadhana Subodh Mendhurwar';
        $nameoftheguide['Safeer Fayyaz Kapdi'] = 'Safeer Fayyaz Kapdi';
        $nameoftheguide['Sagar Manakchand Soni'] = 'Sagar Manakchand Soni';
        $nameoftheguide['Sameer Vilas Vyahalkar'] = 'Sameer Vilas Vyahalkar';
        $nameoftheguide['Sameera Rane'] = 'Sameera Rane';
        $nameoftheguide['Samhita Shreerang Purandare'] = 'Samhita Shreerang Purandare';
        $nameoftheguide['Sandeep Narayan Deore'] = 'Sandeep Narayan Deore';
        $nameoftheguide['Sandesh Ratnu Deolekar'] = 'Sandesh Ratnu Deolekar';
        $nameoftheguide['Sandhya Shripad Jathar'] = 'Sandhya Shripad Jathar';
        $nameoftheguide['Sandip Haribhau Tayade'] = 'Sandip Haribhau Tayade';
        $nameoftheguide['Sanika Abhay Jain'] = 'Sanika Abhay Jain';
        $nameoftheguide['Sanjana Sanjay Malokar'] = 'Sanjana Sanjay Malokar';
        $nameoftheguide['Sanjay Dhar'] = 'Sanjay Dhar';
        $nameoftheguide['Sanjay Dineshkumar Pasoria'] = 'Sanjay Dineshkumar Pasoria';
        $nameoftheguide['Sanjay Kumar Agarwal'] = 'Sanjay Kumar Agarwal';
        $nameoftheguide['Sanjiv Shantaram Kale'] = 'Sanjiv Shantaram Kale';
        $nameoftheguide['Santosh Kashiram Narayankar'] = 'Santosh Kashiram Narayankar';
        $nameoftheguide['Santwana Chandrakar'] = 'Santwana Chandrakar';
        $nameoftheguide['Sarang Paramhans Bajpai'] = 'Sarang Paramhans Bajpai';
        $nameoftheguide['Sarfaraz Yusuf Shaikh'] = 'Sarfaraz Yusuf Shaikh';
        $nameoftheguide['Saroj Indersain Sahdev'] = 'Saroj Indersain Sahdev';
        $nameoftheguide['Satvik Rajnikant Patel'] = 'Satvik Rajnikant Patel';
        $nameoftheguide['Saurabh Vijay Kothari'] = 'Saurabh Vijay Kothari';
        $nameoftheguide['Sayali Suhas Damle'] = 'Sayali Suhas Damle';
        $nameoftheguide['Seema Alok Gupta'] = 'Seema Alok Gupta';
        $nameoftheguide['Shahid Mushtaq Patel'] = 'Shahid Mushtaq Patel';
        $nameoftheguide['Shailesh Gunwantrai Sangani'] = 'Shailesh Gunwantrai Sangani';
        $nameoftheguide['Shantanu Subhashrao Deshpande'] = 'Shantanu Subhashrao Deshpande';
        $nameoftheguide['Sharmila Pandharinath Patil'] = 'Sharmila Pandharinath Patil';
        $nameoftheguide['Shashmira Bhaskar Tonse'] = 'Shashmira Bhaskar Tonse';
        $nameoftheguide['Sheena Ann Mammen'] = 'Sheena Ann Mammen';
        $nameoftheguide['Shikhar Dalbir Singh'] = 'Shikhar Dalbir Singh';
        $nameoftheguide['Shilpi  Yadav'] = 'Shilpi  Yadav';
        $nameoftheguide['Shirish Bapusaheb Patil'] = 'Shirish Bapusaheb Patil';
        $nameoftheguide['Shirish Prabhakar Khatu'] = 'Shirish Prabhakar Khatu';
        $nameoftheguide['Shishir Ananda Kamble'] = 'Shishir Ananda Kamble';
        $nameoftheguide['Shital Sandeep Deore'] = 'Shital Sandeep Deore';
        $nameoftheguide['Shivaji Ramhari Londhe'] = 'Shivaji Ramhari Londhe';
        $nameoftheguide['Shravan Shrikant Patil'] = 'Shravan Shrikant Patil';
        $nameoftheguide['Shreya Nilesh Bhate'] = 'Shreya Nilesh Bhate';
        $nameoftheguide['Shrikrishna A Joshi'] = 'Shrikrishna A Joshi';
        $nameoftheguide['Shruti Mallappa Ugran'] = 'Shruti Mallappa Ugran';
        $nameoftheguide['Shruti Nagare'] = 'Shruti Nagare';
        $nameoftheguide['Shruti Rajendra Shinde'] = 'Shruti Rajendra Shinde';
        $nameoftheguide['Shshank Padamchand Jain'] = 'Shshank Padamchand Jain';
        $nameoftheguide['Shubhangi Dattatray Kulat'] = 'Shubhangi Dattatray Kulat';
        $nameoftheguide['Shwet Vinayak Sabnis'] = 'Shwet Vinayak Sabnis';
        $nameoftheguide['Shyam Kashinath Sobti'] = 'Shyam Kashinath Sobti';
        $nameoftheguide['Shyamrao Chidanandrao More'] = 'Shyamrao Chidanandrao More';
        $nameoftheguide['Sidharth Verma'] = 'Sidharth Verma';
        $nameoftheguide['Smita Pradeep Patil'] = 'Smita Pradeep Patil';
        $nameoftheguide['Sneha P John'] = 'Sneha P John';
        $nameoftheguide['Sneha Padmakar Chavarkar'] = 'Sneha Padmakar Chavarkar';
        $nameoftheguide['Snigdha Mukharji'] = 'Snigdha Mukharji';
        $nameoftheguide['Soham Shankar Chatterjee'] = 'Soham Shankar Chatterjee';
        $nameoftheguide['Somnath Madhukar Mallakmir'] = 'Somnath Madhukar Mallakmir';
        $nameoftheguide['Sonal Kishore Signapurkar'] = 'Sonal Kishore Signapurkar';
        $nameoftheguide['Sonali Nilesh Sarvaiya'] = 'Sonali Nilesh Sarvaiya';
        $nameoftheguide['Sonali Rajaram Shivane'] = 'Sonali Rajaram Shivane';
        $nameoftheguide['Sonalika Rajesh Dughar'] = 'Sonalika Rajesh Dughar';
        $nameoftheguide['Soumyaa Agrawal'] = 'Soumyaa Agrawal';
        $nameoftheguide['Soumyan Dey'] = 'Soumyan Dey';
        $nameoftheguide['Souparna Mandal'] = 'Souparna Mandal';
        $nameoftheguide['Sriram Gopal'] = 'Sriram Gopal';
        $nameoftheguide['Srividya Sreenivasan'] = 'Srividya Sreenivasan';
        $nameoftheguide['Srushti Omprakash Agrawal'] = 'Srushti Omprakash Agrawal';
        $nameoftheguide['Sudhamani Sheshagiri Rao'] = 'Sudhamani Sheshagiri Rao';
        $nameoftheguide['Suhas Chandrakant Bendre'] = 'Suhas Chandrakant Bendre';
        $nameoftheguide['Suhas Vidyadhar Abhyankar'] = 'Suhas Vidyadhar Abhyankar';
        $nameoftheguide['Sumedha Milind Joshi'] = 'Sumedha Milind Joshi';
        $nameoftheguide['Sumedha Prakash Shinde'] = 'Sumedha Prakash Shinde';
        $nameoftheguide['Sumita Karandikar'] = 'Sumita Karandikar';
        $nameoftheguide['Sunanda Panigrahi'] = 'Sunanda Panigrahi';
        $nameoftheguide['Sunil Dube'] = 'Sunil Dube';
        $nameoftheguide['Sunil Hiriyanna Shetty'] = 'Sunil Hiriyanna Shetty';
        $nameoftheguide['Sunita Prabhakar Bharti'] = 'Sunita Prabhakar Bharti';
        $nameoftheguide['Surekha Hemant Bhalekar'] = 'Surekha Hemant Bhalekar';
        $nameoftheguide['Surekha Shirish Patil'] = 'Surekha Shirish Patil';
        $nameoftheguide['Suresh  Fakira Ade'] = 'Suresh  Fakira Ade';
        $nameoftheguide['Swagat Subhash Waghmare'] = 'Swagat Subhash Waghmare';
        $nameoftheguide['Swati Suresh Borade'] = 'Swati Suresh Borade';
        $nameoftheguide['Sweety Purushotham N'] = 'Sweety Purushotham N';
        $nameoftheguide['Swetabh Suresh Roy'] = 'Swetabh Suresh Roy';
        $nameoftheguide['Tanusri Tetarbe'] = 'Tanusri Tetarbe';
        $nameoftheguide['Tejas Uttamrao Bhosale'] = 'Tejas Uttamrao Bhosale';
        $nameoftheguide['Tulsi Pitamberdas Manek'] = 'Tulsi Pitamberdas Manek';
        $nameoftheguide['Umesh Tulsidas Varyani'] = 'Umesh Tulsidas Varyani';
        $nameoftheguide['Vaibhav Jagannath Koli'] = 'Vaibhav Jagannath Koli';
        $nameoftheguide['Vaishali Shirish Thakare'] = 'Vaishali Shirish Thakare';
        $nameoftheguide['Vaja Chirag'] = 'Vaja Chirag';
        $nameoftheguide['Vangal Krishnaswamy Sashindran'] = 'Vangal Krishnaswamy Sashindran';
        $nameoftheguide['Varsha Himanshu Vyas'] = 'Varsha Himanshu Vyas';
        $nameoftheguide['Varsha Pravin Bande'] = 'Varsha Pravin Bande';
        $nameoftheguide['Varun Vittal Shetty'] = 'Varun Vittal Shetty';
        $nameoftheguide['Vedant Madhav Ghuse'] = 'Vedant Madhav Ghuse';
        $nameoftheguide['Vedashree Dhananjay Deshpande'] = 'Vedashree Dhananjay Deshpande';
        $nameoftheguide['Veeranna Kotrashetti'] = 'Veeranna Kotrashetti';
        $nameoftheguide['Vijay Baburao Sonawane'] = 'Vijay Baburao Sonawane';
        $nameoftheguide['Violet Nilesh Pinto'] = 'Violet Nilesh Pinto';
        $nameoftheguide['Viral Jayshinh Nanda'] = 'Viral Jayshinh Nanda';
        $nameoftheguide['Vishnu Vijayan Pillai'] = 'Vishnu Vijayan Pillai';
        $nameoftheguide['Vivek Venkatrao Joshi Nalgirkar'] = 'Vivek Venkatrao Joshi Nalgirkar';
        $nameoftheguide['Vrushali Anupkumar Rawool'] = 'Vrushali Anupkumar Rawool';
        $nameoftheguide['Yashraj Shreetej Shah'] = 'Yashraj Shreetej Shah';
        $nameoftheguide['Yogesh Gurudas Dabholkar'] = 'Yogesh Gurudas Dabholkar';
        $nameoftheguide['Yogeshwar Sadashivrao Nandanwar'] = 'Yogeshwar Sadashivrao Nandanwar';
        $nameoftheguide['Zainabkhanam Airani'] = 'Zainabkhanam Airani';

 $mform->addElement('select' , 'guidename',get_string('guide','block_proposals'), $nameoftheguide);
        $mform->addRule('guidename', get_string('mustbeselect','block_proposals'),'required', null, 'server');


// Name of the Co-guide

$nameofthecoguide= array();
        $nameofthecoguide[null]='Select';
        $nameofthecoguide['Aashay Shah'] = 'Aashay Shah';
        $nameofthecoguide['Abdul Malik Abdul Moeen Nagori
'] = 'Abdul Malik Abdul Moeen Nagori
';
        $nameofthecoguide['Abhay Gangadhar Uppe'] = 'Abhay Gangadhar Uppe';
        $nameofthecoguide['Abhay Sadashiv Chowdhary'] = 'Abhay Sadashiv Chowdhary';
        $nameofthecoguide['Abhijit Guruprasad Bagul'] = 'Abhijit Guruprasad Bagul';
        $nameofthecoguide['Abhijit Ramdas Budhkar'] = 'Abhijit Ramdas Budhkar';
        $nameofthecoguide['Abhishek G Mahadik'] = 'Abhishek G Mahadik';
        $nameofthecoguide['Adeel Inamulhaque Ansari'] = 'Adeel Inamulhaque Ansari';
        $nameofthecoguide['Aditi Kaundinya'] = 'Aditi Kaundinya';
        $nameofthecoguide['Aditya Chandrashekhar Oak'] = 'Aditya Chandrashekhar Oak';
        $nameofthecoguide['Aditya Rajendra Gunjotikar'] = 'Aditya Rajendra Gunjotikar';
        $nameofthecoguide['Ajinkya Deepak Achalare'] = 'Ajinkya Deepak Achalare';
        $nameofthecoguide['Ajit Subhash Baviskar'] = 'Ajit Subhash Baviskar';
        $nameofthecoguide['Akanksha Saberwal'] = 'Akanksha Saberwal';
        $nameofthecoguide['Akshada Vitthal Dhumal'] = 'Akshada Vitthal Dhumal';
        $nameofthecoguide['Alok Ashok Gangurde'] = 'Alok Ashok Gangurde';
        $nameofthecoguide['Amit Baloo Garud'] = 'Amit Baloo Garud';
        $nameofthecoguide['Amit Chandrashekhar Kulkarni'] = 'Amit Chandrashekhar Kulkarni';
        $nameofthecoguide['Amit Prakash Nagarik'] = 'Amit Prakash Nagarik';
        $nameofthecoguide['Amit Shyammohan Saxena'] = 'Amit Shyammohan Saxena';
        $nameofthecoguide['Amita Verma'] = 'Amita Verma';
        $nameofthecoguide['Amol Sunil Chaudhari'] = 'Amol Sunil Chaudhari';
        $nameofthecoguide['Anand Anil Ambekar'] = 'Anand Anil Ambekar';
        $nameofthecoguide['Anand Prabhakar Sude'] = 'Anand Prabhakar Sude';
        $nameofthecoguide['Anant Dhondapant Patil'] = 'Anant Dhondapant Patil';
        $nameofthecoguide['Aniket PAndharinath Patil'] = 'Aniket PAndharinath Patil';
        $nameofthecoguide['Aniket Pandurang Patil'] = 'Aniket Pandurang Patil';
        $nameofthecoguide['Anilkumar Kanaiyalal Gvalani'] = 'Anilkumar Kanaiyalal Gvalani';
        $nameofthecoguide['Aniruddh Shringare'] = 'Aniruddh Shringare';
        $nameofthecoguide['Anisha Ameena Bashir'] = 'Anisha Ameena Bashir';
        $nameofthecoguide['Anita Sharan'] = 'Anita Sharan';
        $nameofthecoguide['Anupriya Premraj Mohokar'] = 'Anupriya Premraj Mohokar';
        $nameofthecoguide['Anuradha Harshad Panchal'] = 'Anuradha Harshad Panchal';
        $nameofthecoguide['Anuradha Malliwal'] = 'Anuradha Malliwal';
        $nameofthecoguide['Anurag Asim'] = 'Anurag Asim';
        $nameofthecoguide['Anuragdeep Syal'] = 'Anuragdeep Syal';
        $nameofthecoguide['Anuya Anil Pawde'] = 'Anuya Anil Pawde';
        $nameofthecoguide['Aparna Sagare'] = 'Aparna Sagare';
        $nameofthecoguide['Apurva Diliprao Shinde'] = 'Apurva Diliprao Shinde';
        $nameofthecoguide['Archana Akshay Kadam'] = 'Archana Akshay Kadam';
        $nameofthecoguide['Archana Hrishikesh Tadwalkar'] = 'Archana Hrishikesh Tadwalkar';
        $nameofthecoguide['Archana Rahul Bhate'] = 'Archana Rahul Bhate';
        $nameofthecoguide['Arpit Rajendra Rajpurohit'] = 'Arpit Rajendra Rajpurohit';
        $nameofthecoguide['Arundhati Barua'] = 'Arundhati Barua';
        $nameofthecoguide['Asawari Sujendra Arwikar'] = 'Asawari Sujendra Arwikar';
        $nameofthecoguide['Ashish Vaman Wagh'] = 'Ashish Vaman Wagh';
        $nameofthecoguide['Ashish Vasudeo Dhande'] = 'Ashish Vasudeo Dhande';
        $nameofthecoguide['Ashvini Vijaysinh Deshmukh'] = 'Ashvini Vijaysinh Deshmukh';
        $nameofthecoguide['Aswathy Radhakrishnan'] = 'Aswathy Radhakrishnan';
        $nameofthecoguide['Audumbar Dashrath Borgaonkar'] = 'Audumbar Dashrath Borgaonkar';
        $nameofthecoguide['Avinash Eknath Chaudhari'] = 'Avinash Eknath Chaudhari';
        $nameofthecoguide['Ayesha Haroon Sayed'] = 'Ayesha Haroon Sayed';
        $nameofthecoguide['Balasaheb Tukaram Govardhane'] = 'Balasaheb Tukaram Govardhane';
        $nameofthecoguide['Bharat Pandit Shinde'] = 'Bharat Pandit Shinde';
        $nameofthecoguide['Bharati Vivek Joshi Nalgirkar'] = 'Bharati Vivek Joshi Nalgirkar';
        $nameofthecoguide['Bhasker Semitha Bhasker'] = 'Bhasker Semitha Bhasker';
        $nameofthecoguide['Bhavika S Verma'] = 'Bhavika S Verma';
        $nameofthecoguide['Bhavishya Sundar'] = 'Bhavishya Sundar';
        $nameofthecoguide['Bhushan Jaywantrao Patil'] = 'Bhushan Jaywantrao Patil';
        $nameofthecoguide['Bhushan Kashinath Chavan'] = 'Bhushan Kashinath Chavan';
        $nameofthecoguide['Bibekananda Mahapatra'] = 'Dr.Anita Sharan';
        $nameofthecoguide['Cassandra Anna Carvalho'] = 'Cassandra Anna Carvalho';
        $nameofthecoguide['Chalak Ajit Rajshekhar'] = 'Chalak Ajit Rajshekhar';
        $nameofthecoguide['Chandan Singh'] = 'Chandan Singh';
        $nameofthecoguide['Cherian Philemon Kurian'] = 'Cherian Philemon Kurian';
        $nameofthecoguide['Chetan Dilip Vispute'] = 'Chetan Dilip Vispute';
        $nameofthecoguide['Chinmayi Pramod Bokey'] = 'Chinmayi Pramod Bokey';
        $nameofthecoguide['Dasari Anurag'] = 'Dasari Anurag';
        $nameofthecoguide['Dattatray Dnyandeo Musmade'] = 'Dattatray Dnyandeo Musmade';
        $nameofthecoguide['Deepa Haritosh Velankar'] = 'Deepa Haritosh Velankar';
        $nameofthecoguide['Deepak Kumar Govindrao Langade'] = 'Deepak Kumar Govindrao Langade';
        $nameofthecoguide['Deepak Ramdas Kamble'] = 'Deepak Ramdas Kamble';
        $nameofthecoguide['Deepali Amarsinh Vidhate'] = 'Deepali Amarsinh Vidhate';
        $nameofthecoguide['Deepali Rishi Rajpal'] = 'Deepali Rishi Rajpal';
        $nameofthecoguide['Deepika Gulati'] = 'Deepika Gulati';
        $nameofthecoguide['Deepika Murali Iyangar'] = 'Deepika Murali Iyangar';
        $nameofthecoguide['Dharmik Dilip Bhuva'] = 'Dharmik Dilip Bhuva';
        $nameofthecoguide['Dheeman Sarkar'] = 'Dheeman Sarkar';
        $nameofthecoguide['Dinesh Chandra Gupta'] = 'Dinesh Chandra Gupta';
        $nameofthecoguide['Dinesh Jagannath Patil'] = 'Dinesh Jagannath Patil';
        $nameofthecoguide['Dipak Sudam Ahire'] = 'Dipak Sudam Ahire';
        $nameofthecoguide['Dipali Chandrakant Puri'] = 'Dipali Chandrakant Puri';
        $nameofthecoguide['Dipika Mohan Koli'] = 'Dipika Mohan Koli';
        $nameofthecoguide['Dipin Kumar Yadav'] = 'Dipin Kumar Yadav';
        $nameofthecoguide['Divya Daga'] = 'Divya Daga';
        $nameofthecoguide['Divya Dayanand Patil'] = 'Divya Dayanand Patil';
        $nameofthecoguide['Divya Ramadoss'] = 'Divya Ramadoss';
        $nameofthecoguide['Divya Shekhar Shetty'] = 'Divya Shekhar Shetty';
        $nameofthecoguide['Fehmida Najmuddin'] = 'Fehmida Najmuddin';
        $nameofthecoguide['Felin Ann Francis'] = 'Felin Ann Francis';
        $nameofthecoguide['Fiona Patra'] = 'Fiona Patra';
        $nameofthecoguide['Gandi Sudha Rani'] = 'Gandi Sudha Rani';
        $nameofthecoguide['Ganesh Arjun Avhad'] = 'Ganesh Arjun Avhad';
        $nameofthecoguide['Gehdoo Raghuveer Singh Pritam Singh'] = 'Gehdoo Raghuveer Singh Pritam Singh';
        $nameofthecoguide['Ghanshyam Ramnath Kane'] = 'Ghanshyam Ramnath Kane';
        $nameofthecoguide['Girija Prakash Nair'] = 'Girija Prakash Nair';
        $nameofthecoguide['Gupta Vijaykumar Kanhaiyalal'] = 'Gupta Vijaykumar Kanhaiyalal';
        $nameofthecoguide['Haritosh Kamalakar Velankar'] = 'Haritosh Kamalakar Velankar';
        $nameofthecoguide['Harsha Sameer Pagad'] = 'Harsha Sameer Pagad';
        $nameofthecoguide['Harshit Pawankumar Thole'] = 'Harshit Pawankumar Thole';
        $nameofthecoguide['Hemant Nandkishor Lahoti'] = 'Hemant Nandkishor Lahoti';
        $nameofthecoguide['Hetal Kirit Shah'] = 'Hetal Kirit Shah';
        $nameofthecoguide['Hritika Sharma'] = 'Hritika Sharma';
        $nameofthecoguide['Huda Ali Sayed'] = 'Huda Ali Sayed';
        $nameofthecoguide['Jawahar Athmaram Vontivillu'] = 'Jawahar Athmaram Vontivillu';
        $nameofthecoguide['Jayant Laxman Pednekar'] = 'Jayant Laxman Pednekar';
        $nameofthecoguide['Jayendra Giridhar Yadav'] = 'Jayendra Giridhar Yadav';
        $nameofthecoguide['Jayshree Prakash Vaswani'] = 'Jayshree Prakash Vaswani';
        $nameofthecoguide['Jeetendra Dinkar Chavan'] = 'Jeetendra Dinkar Chavan';
        $nameofthecoguide['Jesheen Kaur Joshbir Mann'] = 'Jesheen Kaur Joshbir Mann';
        $nameofthecoguide['Joseph Sengol Gounder'] = 'Joseph Sengol Gounder';
        $nameofthecoguide['Jyoti Amol Pawar'] = 'Jyoti Amol Pawar';
        $nameofthecoguide['Kailash Khandeshwarrao Jawade'] = 'Kailash Khandeshwarrao Jawade';
        $nameofthecoguide['Kalpesh Uttam Chaudhari'] = 'Kalpesh Uttam Chaudhari';
        $nameofthecoguide['Kammara Vinod Achari'] = 'Kammara Vinod Achari';
        $nameofthecoguide['Kanade Gaurav Gajanan'] = 'Kanade Gaurav Gajanan';
        $nameofthecoguide['Kanika Rai'] = 'Kanika Rai';
        $nameofthecoguide['Kapil Shivsing Bainade'] = 'Kapil Shivsing Bainade';
        $nameofthecoguide['Kavitha Vivek Dongerkery'] = 'Kavitha Vivek Dongerkery';
        $nameofthecoguide['Keertana Sadanand Shetty'] = 'Keertana Sadanand Shetty';
        $nameofthecoguide['Keshav Dada Kale'] = 'Keshav Dada Kale';
        $nameofthecoguide['Ketan Laxman Pakhale'] = 'Ketan Laxman Pakhale';
        $nameofthecoguide['Ketan Ramesh Vagholkar'] = 'Ketan Ramesh Vagholkar';
        $nameofthecoguide['Keya Rani Lahiri'] = 'Keya Rani Lahiri';
        $nameofthecoguide['Khilchand Dilip Bhangale'] = 'Khilchand Dilip Bhangale';
        $nameofthecoguide['Kiran Vasant Godse'] = 'Kiran Vasant Godse';
        $nameofthecoguide['Kirtibala Ashish Dhande'] = 'Kirtibala Ashish Dhande';
        $nameofthecoguide['Kisan Rajaram Khade'] = 'Kisan Rajaram Khade';
        $nameofthecoguide['Krishna Kirit Bhadiadra'] = 'Krishna Kirit Bhadiadra';
        $nameofthecoguide['Krishnarao Narayan Bhosle'] = 'Krishnarao Narayan Bhosle';
        $nameofthecoguide['Kritika Singh K Yadav'] = 'Kritika Singh K Yadav';
        $nameofthecoguide['Krushna Rambhau Borkar'] = 'Krushna Rambhau Borkar';
        $nameofthecoguide['Kulkarni Amar Vilas'] = 'Kulkarni Amar Vilas';
        $nameofthecoguide['Kumar Abhineet Chand'] = 'Kumar Abhineet Chand';
        $nameofthecoguide['Kunapuli Sri Satya Ramdev'] = 'Kunapuli Sri Satya Ramdev';
        $nameofthecoguide['Madhurima Suresh Nair'] = 'Madhurima Suresh Nair';
        $nameofthecoguide['Mahesh Virayya Padsalge'] = 'Mahesh Virayya Padsalge';
        $nameofthecoguide['Maitreya Jagdish Patil'] = 'Maitreya Jagdish Patil';
        $nameofthecoguide['Maitri Kishor Mehta'] = 'Maitri Kishor Mehta';
        $nameofthecoguide['Manasi Satish More'] = 'Manasi Satish More';
        $nameofthecoguide['Manish Shriram Pendse'] = 'Manish Shriram Pendse';
        $nameofthecoguide['Manisha Mishra'] = 'Manisha Mishra';
        $nameofthecoguide['Manisha Sandeep Nakhate'] = 'Manisha Sandeep Nakhate';
        $nameofthecoguide['Manju Bhashini Sundru'] = 'Manju Bhashini Sundru';
        $nameofthecoguide['Manjula Sudeep Sarkar'] = 'Manjula Sudeep Sarkar';
        $nameofthecoguide['Manjyot Manish Gautam'] = 'Manjyot Manish Gautam';
        $nameofthecoguide['Manmohan Prithviraj Madan'] = 'Manmohan Prithviraj Madan';
        $nameofthecoguide['Manohar Rajendra Joshi'] = 'Manohar Rajendra Joshi';
        $nameofthecoguide['Manpreet Kaur Harbans Singh Juneja'] = 'Manpreet Kaur Harbans Singh Juneja';

        $nameofthecoguide['Mayank Chourasia'] = 'Mayank Chourasia';
        $nameofthecoguide['Mayuri Vijay Ghorpade'] = 'Mayuri Vijay Ghorpade';
        $nameofthecoguide['Mayuri Vinayak More'] = 'Mayuri Vinayak More';
        $nameofthecoguide['Meena Kumar'] = 'Meena Kumar';
        $nameofthecoguide['Meena Naresh Satia'] = 'Meena Naresh Satia';
        $nameofthecoguide['Meghana Vijay Choudhary'] = 'Meghana Vijay Choudhary';
        $nameofthecoguide['Mehreen Shahid'] = 'Mehreen Shahid';
        $nameofthecoguide['Mohammed Hishaam M A'] = 'Mohammed Hishaam M A';
        $nameofthecoguide['Mona Sanjeev Kumar Jadhav'] = 'Mona Sanjeev Kumar Jadhav';
        $nameofthecoguide['Mradula Nilesh Kulapkar'] = 'Mradula Nilesh Kulapkar';
        $nameofthecoguide['Mrunalini Rajendra Kanvinde'] = 'Mrunalini Rajendra Kanvinde';
        $nameofthecoguide['Mukta Jain'] = 'Mukta Jain';
        $nameofthecoguide['Mumtaz Sharif'] = 'Mumtaz Sharif';
        $nameofthecoguide['Nandan Shrikant Purandare'] = 'Nandan Shrikant Purandare';
        $nameofthecoguide['Nandita Amit Saxena'] = 'Nandita Amit Saxena';
        $nameofthecoguide['Nayan Shrivastava'] = 'Nayan Shrivastava';
        $nameofthecoguide['Neelam Chhillar'] = 'Neelam Chhillar';
        $nameofthecoguide['Neelu Elon'] = 'Neelu Elon';
        $nameofthecoguide['Neeti Mathur'] = 'Neeti Mathur';
        $nameofthecoguide['Neha Anil Momale'] = 'Neha Anil Momale';
        $nameofthecoguide['Nida Abdulrahim Khan'] = 'Nida Abdulrahim Khan';
        $nameofthecoguide['Niharika Ranjan'] = 'Niharika Ranjan';
        $nameofthecoguide['Niket Ashok Attarde'] = 'Niket Ashok Attarde';
        $nameofthecoguide['Nikhil Chandrashekhar Sarangdhar'] = 'Nikhil Chandrashekhar Sarangdhar';
        $nameofthecoguide['Nikhil Mahesh Gurjar'] = 'Nikhil Mahesh Gurjar';
        $nameofthecoguide['Nilesh Suryakant Ingale'] = 'Nilesh Suryakant Ingale';
        $nameofthecoguide['Nilofar Imamhusen Yelurkar'] = 'Nilofar Imamhusen Yelurkar';
        $nameofthecoguide['Nitin Jayanand Nadkarni'] = 'Nitin Jayanand Nadkarni';
        $nameofthecoguide['Nitin Nandkumar Jagdhane'] = 'Nitin Nandkumar Jagdhane';
        $nameofthecoguide['Nitin Shantiel Bharos'] = 'Nitin Shantiel Bharos';
        $nameofthecoguide['Nivedita Devabrata Moulick'] = 'Nivedita Devabrata Moulick';
        $nameofthecoguide['Pallavi Gahlowt'] = 'Pallavi Gahlowt';
        $nameofthecoguide['Pallavi Prashant Basapure'] = 'Pallavi Prashant Basapure';
        $nameofthecoguide['Pallavi Sachin Chitnis'] = 'Pallavi Sachin Chitnis';
        $nameofthecoguide['Pankaj Baban Tule'] = 'Pankaj Baban Tule';
        $nameofthecoguide['Parag Ravindra Chaudhari'] = 'Parag Ravindra Chaudhari';
        $nameofthecoguide['Patel Abhirajsinh'] = 'Patel Abhirajsinh';
        $nameofthecoguide['Patel Rajas Balkrishna'] = 'Patel Rajas Balkrishna';
        $nameofthecoguide['Pooja Girdharilal Binnani'] = 'Pooja Girdharilal Binnani';
        $nameofthecoguide['Pooja Kochar'] = 'Pooja Kochar';
        $nameofthecoguide['Pooja Mahesh Ghogare'] = 'Pooja Mahesh Ghogare';
        $nameofthecoguide['Prachi Jaysingh Sankhe'] = 'Prachi Jaysingh Sankhe';
        $nameofthecoguide['Pradeep Bijaynath Tiwari'] = 'Pradeep Bijaynath Tiwari';
        $nameofthecoguide['Pradnya Sandesh Deolekar'] = 'Pradnya Sandesh Deolekar';
        $nameofthecoguide['Pragati Salil Upasham'] = 'Pragati Salil Upasham';
        $nameofthecoguide['Prajakta Ananta Kulkarni'] = 'Prajakta Ananta Kulkarni';
        $nameofthecoguide['Prajakta M. Radke'] = 'Prajakta M. Radke';
        $nameofthecoguide['Prakash Dattatraya Samant'] = 'Prakash Dattatraya Samant';
        $nameofthecoguide['Prakash Madhukar Dive'] = 'Prakash Madhukar Dive';
        $nameofthecoguide['Pramila Shriram Yadav'] = 'Pramila Shriram Yadav';
        $nameofthecoguide['Prasad Liladhar Chaudhari'] = 'Prasad Liladhar Chaudhari';
        $nameofthecoguide['Prasad Prabhakar Kulkarni'] = 'Prasad Prabhakar Kulkarni';
        $nameofthecoguide['Prashant Dasharath Purkar'] = 'Prashant Dasharath Purkar';
        $nameofthecoguide['Prashant Pandey'] = 'Prashant Pandey';
        $nameofthecoguide['Preeti Samir Pachpute'] = 'Preeti Samir Pachpute';
        $nameofthecoguide['Premkumar Ramkishan Maurya'] = 'Premkumar Ramkishan Maurya';
        $nameofthecoguide['Prithi Rajendra Inamdar'] = 'Prithi Rajendra Inamdar';
        $nameofthecoguide['Priya Narasinhrao Deshpande'] = 'Priya Narasinhrao Deshpande';
        $nameofthecoguide['Priyadarshini R Cholera'] = 'Priyadarshini R Cholera';
        $nameofthecoguide['Priyanka'] = 'Priyanka';
        $nameofthecoguide['Priyanka Anil Choudhari'] = 'Priyanka Anil Choudhari';
        $nameofthecoguide['Priyanka Balaram Jadhav'] = 'Priyanka Balaram Jadhav';
        $nameofthecoguide['Priyanka Balasaheb Dhobale'] = 'Priyanka Balasaheb Dhobale';
        $nameofthecoguide['Rahul Prakash Zalse'] = 'Rahul Prakash Zalse';
        $nameofthecoguide['Rahul Shrikrishna Siraskar'] = 'Rahul Shrikrishna Siraskar';
        $nameofthecoguide['Raj Panchdeo Gautam'] = 'Raj Panchdeo Gautam';
        $nameofthecoguide['Rajdeep Salil Pal'] = 'Rajdeep Salil Pal';
        $nameofthecoguide['Rajendraprasad Ramesh Butala'] = 'Rajendraprasad Ramesh Butala';
        $nameofthecoguide['Rajesh Kumar Rai'] = 'Rajesh Kumar Rai';
        $nameofthecoguide['Rajiv Jairaj Rao'] = 'Rajiv Jairaj Rao';
        $nameofthecoguide['Rajshekhar Keshavrao Yadav'] = 'Rajshekhar Keshavrao Yadav';
        $nameofthecoguide['Raju Laxmanrao Patil'] = 'Raju Laxmanrao Patil';
        $nameofthecoguide['Rakhi Milind MOre'] = 'Rakhi Milind MOre';
        $nameofthecoguide['Ravi Prakash Naulakha'] = 'Ravi Prakash Naulakha';
        $nameofthecoguide['Ravindra Mahadeo Kattimani'] = 'Ravindra Mahadeo Kattimani';
        $nameofthecoguide['Reetu Singhal'] = 'Reetu Singhal';
        $nameofthecoguide['Rewa Amit Garud'] = 'Rewa Amit Garud';
        $nameofthecoguide['Richa Singh'] = 'Richa Singh';
        $nameofthecoguide['Rita Swaminathan'] = 'Rita Swaminathan';
        $nameofthecoguide['Ritika Khurana'] = 'Ritika Khurana';
        $nameofthecoguide['Rochana Girish Bakhshi'] = 'Rochana Girish Bakhshi';
        $nameofthecoguide['Rohan Bharat Gala'] = 'Rohan Bharat Gala';
        $nameofthecoguide['Rohan Dattu Patil'] = 'Rohan Dattu Patil';
        $nameofthecoguide['Rohan Pradeep Palshetkar'] = 'Rohan Pradeep Palshetkar';
        $nameofthecoguide['Rohan Sawant'] = 'Rohan Sawant';
        $nameofthecoguide['Rohit Mahesh Sane'] = 'Rohit Mahesh Sane';
        $nameofthecoguide['Roopashri Omprakash Jamadar'] = 'Roopashri Omprakash Jamadar';
        $nameofthecoguide['Rubina Manzoorhussain Patankar'] = 'Rubina Manzoorhussain Patankar';
        $nameofthecoguide['Ruma Nooreen'] = 'Ruma Nooreen';
        $nameofthecoguide['Ruta Vinayak Bapat'] = 'Ruta Vinayak Bapat';
        $nameofthecoguide['Sachin Yashwant  Kale'] = 'Sachin Yashwant  Kale';
        $nameofthecoguide['Sadhana Subodh Mendhurwar'] = 'Sadhana Subodh Mendhurwar';
        $nameofthecoguide['Safeer Fayyaz Kapdi'] = 'Safeer Fayyaz Kapdi';
        $nameofthecoguide['Sagar Manakchand Soni'] = 'Sagar Manakchand Soni';
        $nameofthecoguide['Sameer Vilas Vyahalkar'] = 'Sameer Vilas Vyahalkar';
        $nameofthecoguide['Sameera Rane'] = 'Sameera Rane';
        $nameofthecoguide['Samhita Shreerang Purandare'] = 'Samhita Shreerang Purandare';
        $nameofthecoguide['Sandeep Narayan Deore'] = 'Sandeep Narayan Deore';
        $nameofthecoguide['Sandesh Ratnu Deolekar'] = 'Sandesh Ratnu Deolekar';
        $nameofthecoguide['Sandhya Shripad Jathar'] = 'Sandhya Shripad Jathar';
        $nameofthecoguide['Sandip Haribhau Tayade'] = 'Sandip Haribhau Tayade';
        $nameofthecoguide['Sanika Abhay Jain'] = 'Sanika Abhay Jain';
        $nameofthecoguide['Sanjana Sanjay Malokar'] = 'Sanjana Sanjay Malokar';
        $nameofthecoguide['Sanjay Dhar'] = 'Sanjay Dhar';
        $nameofthecoguide['Sanjay Dineshkumar Pasoria'] = 'Sanjay Dineshkumar Pasoria';
        $nameofthecoguide['Sanjay Kumar Agarwal'] = 'Sanjay Kumar Agarwal';
        $nameofthecoguide['Sanjiv Shantaram Kale'] = 'Sanjiv Shantaram Kale';
        $nameofthecoguide['Santosh Kashiram Narayankar'] = 'Santosh Kashiram Narayankar';
        $nameofthecoguide['Santwana Chandrakar'] = 'Santwana Chandrakar';
        $nameofthecoguide['Sarang Paramhans Bajpai'] = 'Sarang Paramhans Bajpai';
        $nameofthecoguide['Sarfaraz Yusuf Shaikh'] = 'Sarfaraz Yusuf Shaikh';
        $nameofthecoguide['Saroj Indersain Sahdev'] = 'Saroj Indersain Sahdev';
        $nameofthecoguide['Satvik Rajnikant Patel'] = 'Satvik Rajnikant Patel';
        $nameofthecoguide['Saurabh Vijay Kothari'] = 'Saurabh Vijay Kothari';
        $nameofthecoguide['Sayali Suhas Damle'] = 'Sayali Suhas Damle';
        $nameofthecoguide['Seema Alok Gupta'] = 'Seema Alok Gupta';
        $nameofthecoguide['Shahid Mushtaq Patel'] = 'Shahid Mushtaq Patel';
        $nameofthecoguide['Shailesh Gunwantrai Sangani'] = 'Shailesh Gunwantrai Sangani';
        $nameofthecoguide['Shantanu Subhashrao Deshpande'] = 'Shantanu Subhashrao Deshpande';
        $nameofthecoguide['Sharmila Pandharinath Patil'] = 'Sharmila Pandharinath Patil';
        $nameofthecoguide['Shashmira Bhaskar Tonse'] = 'Shashmira Bhaskar Tonse';
        $nameofthecoguide['Sheena Ann Mammen'] = 'Sheena Ann Mammen';
        $nameofthecoguide['Shikhar Dalbir Singh'] = 'Shikhar Dalbir Singh';
        $nameofthecoguide['Shilpi  Yadav'] = 'Shilpi  Yadav';
        $nameofthecoguide['Shirish Bapusaheb Patil'] = 'Shirish Bapusaheb Patil';
        $nameofthecoguide['Shirish Prabhakar Khatu'] = 'Shirish Prabhakar Khatu';
        $nameofthecoguide['Shishir Ananda Kamble'] = 'Shishir Ananda Kamble';
        $nameofthecoguide['Shital Sandeep Deore'] = 'Shital Sandeep Deore';
        $nameofthecoguide['Shivaji Ramhari Londhe'] = 'Shivaji Ramhari Londhe';
        $nameofthecoguide['Shravan Shrikant Patil'] = 'Shravan Shrikant Patil';
        $nameofthecoguide['Shreya Nilesh Bhate'] = 'Shreya Nilesh Bhate';
        $nameofthecoguide['Shrikrishna A Joshi'] = 'Shrikrishna A Joshi';
        $nameofthecoguide['Shruti Mallappa Ugran'] = 'Shruti Mallappa Ugran';
        $nameofthecoguide['Shruti Nagare'] = 'Shruti Nagare';
        $nameofthecoguide['Shruti Rajendra Shinde'] = 'Shruti Rajendra Shinde';
        $nameofthecoguide['Shshank Padamchand Jain'] = 'Shshank Padamchand Jain';
        $nameofthecoguide['Shubhangi Dattatray Kulat'] = 'Shubhangi Dattatray Kulat';
        $nameofthecoguide['Shwet Vinayak Sabnis'] = 'Shwet Vinayak Sabnis';
        $nameofthecoguide['Shyam Kashinath Sobti'] = 'Shyam Kashinath Sobti';
        $nameofthecoguide['Shyamrao Chidanandrao More'] = 'Shyamrao Chidanandrao More';
        $nameofthecoguide['Sidharth Verma'] = 'Sidharth Verma';
        $nameofthecoguide['Smita Pradeep Patil'] = 'Smita Pradeep Patil';
        $nameofthecoguide['Sneha P John'] = 'Sneha P John';
        $nameofthecoguide['Sneha Padmakar Chavarkar'] = 'Sneha Padmakar Chavarkar';
        $nameofthecoguide['Snigdha Mukharji'] = 'Snigdha Mukharji';
        $nameofthecoguide['Soham Shankar Chatterjee'] = 'Soham Shankar Chatterjee';
        $nameofthecoguide['Somnath Madhukar Mallakmir'] = 'Somnath Madhukar Mallakmir';
        $nameofthecoguide['Sonal Kishore Signapurkar'] = 'Sonal Kishore Signapurkar';
        $nameofthecoguide['Sonali Nilesh Sarvaiya'] = 'Sonali Nilesh Sarvaiya';
        $nameofthecoguide['Sonali Rajaram Shivane'] = 'Sonali Rajaram Shivane';
        $nameofthecoguide['Sonalika Rajesh Dughar'] = 'Sonalika Rajesh Dughar';
        $nameofthecoguide['Soumyaa Agrawal'] = 'Soumyaa Agrawal';
        $nameofthecoguide['Soumyan Dey'] = 'Soumyan Dey';
        $nameofthecoguide['Souparna Mandal'] = 'Souparna Mandal';
        $nameofthecoguide['Sriram Gopal'] = 'Sriram Gopal';
        $nameofthecoguide['Srividya Sreenivasan'] = 'Srividya Sreenivasan';
        $nameofthecoguide['Srushti Omprakash Agrawal'] = 'Srushti Omprakash Agrawal';
        $nameofthecoguide['Sudhamani Sheshagiri Rao'] = 'Sudhamani Sheshagiri Rao';
        $nameofthecoguide['Suhas Chandrakant Bendre'] = 'Suhas Chandrakant Bendre';
        $nameofthecoguide['Suhas Vidyadhar Abhyankar'] = 'Suhas Vidyadhar Abhyankar';
        $nameofthecoguide['Sumedha Milind Joshi'] = 'Sumedha Milind Joshi';
        $nameofthecoguide['Sumedha Prakash Shinde'] = 'Sumedha Prakash Shinde';
        $nameofthecoguide['Sumita Karandikar'] = 'Sumita Karandikar';
        $nameofthecoguide['Sunanda Panigrahi'] = 'Sunanda Panigrahi';
        $nameofthecoguide['Sunil Dube'] = 'Sunil Dube';
        $nameofthecoguide['Sunil Hiriyanna Shetty'] = 'Sunil Hiriyanna Shetty';
        $nameofthecoguide['Sunita Prabhakar Bharti'] = 'Sunita Prabhakar Bharti';
        $nameofthecoguide['Surekha Hemant Bhalekar'] = 'Surekha Hemant Bhalekar';
        $nameofthecoguide['Surekha Shirish Patil'] = 'Surekha Shirish Patil';
        $nameofthecoguide['Suresh  Fakira Ade'] = 'Suresh  Fakira Ade';
        $nameofthecoguide['Swagat Subhash Waghmare'] = 'Swagat Subhash Waghmare';
        $nameofthecoguide['Swati Suresh Borade'] = 'Swati Suresh Borade';
        $nameofthecoguide['Sweety Purushotham N'] = 'Sweety Purushotham N';
        $nameofthecoguide['Swetabh Suresh Roy'] = 'Swetabh Suresh Roy';
        $nameofthecoguide['Tanusri Tetarbe'] = 'Tanusri Tetarbe';
        $nameofthecoguide['Tejas Uttamrao Bhosale'] = 'Tejas Uttamrao Bhosale';
        $nameofthecoguide['Tulsi Pitamberdas Manek'] = 'Tulsi Pitamberdas Manek';
        $nameofthecoguide['Umesh Tulsidas Varyani'] = 'Umesh Tulsidas Varyani';
        $nameofthecoguide['Vaibhav Jagannath Koli'] = 'Vaibhav Jagannath Koli';
        $nameofthecoguide['Vaishali Shirish Thakare'] = 'Vaishali Shirish Thakare';
        $nameofthecoguide['Vaja Chirag'] = 'Vaja Chirag';
        $nameofthecoguide['Vangal Krishnaswamy Sashindran'] = 'Vangal Krishnaswamy Sashindran';
        $nameofthecoguide['Varsha Himanshu Vyas'] = 'Varsha Himanshu Vyas';
        $nameofthecoguide['Varsha Pravin Bande'] = 'Varsha Pravin Bande';
        $nameofthecoguide['Varun Vittal Shetty'] = 'Varun Vittal Shetty';
        $nameofthecoguide['Vedant Madhav Ghuse'] = 'Vedant Madhav Ghuse';
        $nameofthecoguide['Vedashree Dhananjay Deshpande'] = 'Vedashree Dhananjay Deshpande';
        $nameofthecoguide['Veeranna Kotrashetti'] = 'Veeranna Kotrashetti';
        $nameofthecoguide['Vijay Baburao Sonawane'] = 'Vijay Baburao Sonawane';
        $nameofthecoguide['Violet Nilesh Pinto'] = 'Violet Nilesh Pinto';
        $nameofthecoguide['Viral Jayshinh Nanda'] = 'Viral Jayshinh Nanda';
        $nameofthecoguide['Vishnu Vijayan Pillai'] = 'Vishnu Vijayan Pillai';
        $nameofthecoguide['Vivek Venkatrao Joshi Nalgirkar'] = 'Vivek Venkatrao Joshi Nalgirkar';
        $nameofthecoguide['Vrushali Anupkumar Rawool'] = 'Vrushali Anupkumar Rawool';
        $nameofthecoguide['Yashraj Shreetej Shah'] = 'Yashraj Shreetej Shah';
        $nameofthecoguide['Yogesh Gurudas Dabholkar'] = 'Yogesh Gurudas Dabholkar';
        $nameofthecoguide['Yogeshwar Sadashivrao Nandanwar'] = 'Yogeshwar Sadashivrao Nandanwar';
        $nameofthecoguide['Zainabkhanam Airani'] = 'Zainabkhanam Airani';

         $mform->addElement('select' , 'nameofthecoguide',get_string('coguide','block_proposals'), $nameofthecoguide);
        
        // Contact Person
        $contactperson=array('placeholder' => '30 characters');
        $mform->addElement('text','contactperson',get_string('ContactPerson','block_proposals'),$contactperson);
        $mform->addRule('contactperson' , get_string('thrcharacters','block_proposals') , 'required' , 30 , 'server');
        $mform->addRule('contactperson' , get_string('thrcharacters','block_proposals') , 'maxlength' , 30 , 'server');

        // Funding
        $funding=array();
        $funding[] = $mform->createElement('radio', 'funding', '', get_string('Self','block_proposals'), 0);
        $funding[] = $mform->createElement('radio', 'funding', '', get_string('Organization','block_proposals'), 1);
        $mform->addGroup($funding, 'funding',get_string('Funding','block_proposals'), array(' '), false);


        // Sponsor

        $Sponsor=array();
        $Sponsor[] = $mform->createElement('radio', 'sponsorss', '', get_string('yes'), 1);
        $Sponsor[] = $mform->createElement('radio', 'sponsorss', '', get_string('no'), 0);
        $mform->addGroup($Sponsor, 'sponsors', get_string('Sponsor','block_proposals'), array(' '), false);

        $mform->disabledIf('sponsor', 'sponsorss', 'eq', 0);

        $mform->addElement('text','sponsor',get_string('If-yes','block_proposals'));
        // Study site
        $studysite=array('placeholder' => '30 characters');
        $mform->addElement('text','studysite',get_string('Studysite','block_proposals'),$studysite);
        $mform->addRule('studysite' , get_string('thrcharacters','block_proposals') , 'required' , 30 , 'server');
        $mform->addRule('studysite' ,get_string('thrcharacters','block_proposals') , 'maxlength' , 30 , 'server');

        $briefsummary=array('placeholder' => '10000 characters');
        $mform->addElement('textarea','briefsummary',get_string('background/introduction/rationale','block_proposals'),$briefsummary);
        $mform->addRule('briefsummary' , get_string('providethoucharacters','block_proposals') , 'required' , 10000 , 'server');
        $mform->addRule('briefsummary' ,get_string('providethoucharacters','block_proposals') , 'maxlength' , 10000 , 'server');

        $mform->addHelpButton('briefsummary','briefsummary','block_proposals');
        // Health condition /Problem studied
        $healthcondition=array('placeholder' => '30 characters');
        $mform->addElement('text','healthcondition',get_string('Healthcondition','block_proposals'),$healthcondition);
        $mform->addRule('healthcondition' , get_string('thrcharacters','block_proposals') , 'required' , 30 , 'server');

        $mform->addRule('healthcondition' ,get_string('thrcharacters','block_proposals') , 'maxlength' , 30 , 'server');
        // Study population

        $population= array();
        $population[] = $mform->createElement('radio','population','',get_string('Healthyvolunteers','block_proposals'),1);
        $population[] = $mform->createElement('radio', 'population','',get_string('Diseased','block_proposals'), 0);
        $mform->addGroup($population, 'population', get_string('Studypopulation','block_proposals'), array(' '), false);


        $typeofstudy = array();
        $typeofstudy[null]='Select';
        $typeofstudy ['Interventional'] = 'Interventional';
        $typeofstudy ['Observational – Prospective'] = 'Observational – Prospective';
        $typeofstudy ['Observational – Retrospective'] = 'Observational – Retrospective';
        $typeofstudy ['Cross-sectional'] = 'Cross-sectional';
        $mform->addElement('select' , 'typeofstudy' , get_string('studytype','block_proposals') , $typeofstudy);
        $mform->addRule('typeofstudy' , get_string('mustbeselect','block_proposals') , 'required' ,null , 'server');
        $study= array();
        $study[null]='Select';
        $study['Single arm'] = 'Single arm';
        $study['Double arm'] = 'Double arm';
        $study['Multiple arm'] = 'Multiple arm';
        $mform->addElement('select' , 'studyarm' ,get_string('Studyarm','block_proposals'), $study);
        $mform->addRule('studyarm', get_string('mustbeselect','block_proposals'),'required', null, 'server');

        $mform->disabledIf('control', 'studyarm', 'eq', $study['Single arm']);
        $mform->disabledIf('groupradio', 'studyarm', 'eq', $study['Single arm']);
        $mform->disabledIf('blinding', 'studyarm', 'eq', $study['Single arm']);
         
        $group=array();
        $group[] = $mform->createElement('radio', 'groupradio', '', get_string('parallel','block_proposals'), 1);
        $group[] = $mform->createElement('radio', 'groupradio', '', get_string('cross','block_proposals'), 0);
        $mform->addGroup($group, 'group', 'Type of group', array(' '), false);
        
  
        $control[] = $mform->createElement('radio', 'control', '', get_string('yes'), 1);
        $control[] = $mform->createElement('radio', 'control', '', get_string('no'), 0);
        $mform->addGroup($control, 'radioar', get_string('control','block_proposals'), array(' '), false);

        $mform->disabledIf('comparator', 'control', 'eq', 0);

        $comparator= array();
        $comparator[null]='Select';
        $comparator['Placebo'] = 'Placebo';
        $comparator['Active drug'] = 'Active drug';
        $mform->addElement('select' , 'comparator' ,get_string('If-yes','block_proposals'), $comparator);

        $Intervention = array();
        $Intervention[null]='Select';
        $Intervention ['Drug'] = 'Drug';
        $Intervention ['Operative Procedure'] = 'Operative Procedure';
        $Intervention ['Lifestyle Modification'] = 'Lifestyle Modification';
        $Intervention ['Exercise'] = 'Exercise';
        $Intervention ['Diet'] = 'Diet';
        $Intervention ['Other'] = 'Other';

        $mform->addElement('select' , 'intervention' , 'Intervention' , $Intervention);
        $mform->addRule('intervention',get_string('mustbeselect','block_proposals'),'required', null, 'server');

        $mform->addElement('text', 'interventionother',get_string('interventionother','block_proposals'));

        $mform->disabledIf('interventionother', 'intervention', 'neq', $Intervention['Other']);

        // $Blinding=array();

        $Blinding[] = $mform->createElement('radio', 'blinding', '', get_string('yes'), 1);
        $Blinding[] = $mform->createElement('radio', 'blinding', '', get_string('no'), 0);
        $mform->addGroup($Blinding, 'radioar', 'Blinding', array(' '), false);

        $mform->disabledIf('blindingyes', 'blinding', 'eq', 0);

        $Blindingyes= array();
        $Blindingyes[null]='Select';
        $Blindingyes['Single'] = 'Single';
        $Blindingyes['Double'] = 'Double';
        $Blindingyes['Triple'] = 'Triple';


         $mform->addElement('select' , 'blindingyes' ,get_string('If-yes','block_proposals'), $Blindingyes);


        $completestudydesign=array('placeholder' => '10,000 characters');
        $mform->addElement('textarea','completestudydesign',get_string('Completestudydesign','block_proposals'),$completestudydesign);
        $mform->addRule('completestudydesign' , get_string('tenthocharacters','block_proposals') , 'required' , 10000 , 'server');
        $mform->addRule('completestudydesign' ,get_string('tenthocharacters','block_proposals') , 'maxlength' , 10000 , 'server');

        $inclusioncriteria=array('placeholder' => '5000 characters');
        $mform->addElement('textarea','inclusioncriteria',get_string('Inclusioncriteria','block_proposals'),$inclusioncriteria);
        $mform->addRule('inclusioncriteria' , get_string('fivthocharacters','block_proposals') , 'required' , 5000 , 'server');
        $mform->addRule('inclusioncriteria' ,get_string('fivthocharacters','block_proposals') , 'maxlength' , 5000 , 'server');

        $exclusioncriteria=array('placeholder' => '5000 characters');
        $mform->addElement('textarea','exclusioncriteria','Exclusion criteria Add more option',$exclusioncriteria);
        $mform->addRule('exclusioncriteria' , get_string('fivthocharacters','block_proposals') , 'required' , 5000 , 'server');
        $mform->addRule('exclusioncriteria' ,get_string('fivthocharacters','block_proposals') , 'maxlength' , 5000 , 'server');

        $aimobjectives=array('placeholder' => '500 characters');
        $mform->addElement('text','aimobjectives',get_string('Aimobjectives','block_proposals'),$aimobjectives);

        $mform->addHelpButton('aimobjectives', 'Aimobjectives' , 'block_proposals');

 
        $primaryobjective=array('placeholder' => '5000 characters');
        $mform->addElement('textarea','primaryobjective',get_string('Primaryobjective','block_proposals'),$primaryobjective);
        $mform->addRule('primaryobjective' , get_string('fivthocharacters','block_proposals') , 'required' , 5000 , 'server');
        $mform->addRule('primaryobjective' ,get_string('fivthocharacters','block_proposals') , 'maxlength' , 5000 , 'server');

        $secondaryobjective=array('placeholder' => '5000 characters');
        $mform->addElement('textarea','secondaryobjective',get_string('SecondaryObjective','block_proposals'),$secondaryobjective);
        $mform->addRule('secondaryobjective' , get_string('fivthocharacters','block_proposals') , 'required' , 5000 , 'server');
        $mform->addRule('secondaryobjective' ,get_string('fivthocharacters','block_proposals') , 'maxlength' , 5000 , 'server');

        $primaryoutcome=array('placeholder' => '5000 characters');
        $mform->addElement('textarea','primaryoutcome',get_string('Primaryoutcome','block_proposals'),$primaryoutcome);
        $mform->addRule('primaryoutcome' , get_string('fivthocharacters','block_proposals') , 'required' , 5000 , 'server');
        $mform->addRule('primaryoutcome' ,get_string('fivthocharacters','block_proposals') , 'maxlength' , 5000 , 'server');
        $secondaryoutcome=array('placeholder' => '5000 characters');
        $mform->addElement('textarea','secondaryoutcome',get_string('Secondaryoutcome','block_proposals'),$secondaryoutcome);
        $mform->addRule('secondaryoutcome' , get_string('fivthocharacters','block_proposals') , 'required' , 5000 , 'server');
        $mform->addRule('secondaryoutcome' ,get_string('fivthocharacters','block_proposals') , 'maxlength' , 5000 , 'server');

        $studygroups=array('placeholder' => '5000 characters');
        $mform->addElement('textarea','studygroups',get_string('StudyGroups','block_proposals'),$studygroups);
        $mform->addRule('studygroups' , 'detail description of study groups' , 'required' , 5000 , 'server');
        $mform->addRule('studygroups' ,'detail description of study groups' , 'maxlength' , 5000 , 'server');
        $mform->addHelpButton('studygroups','StudyGroups','block_proposals');


        $enrollmentprocess=array('placeholder' => '5000 characters');
        $mform->addElement('textarea','enrollmentprocess',get_string('Enrollmentprocess','block_proposals'),$enrollmentprocess);
        $mform->addRule('enrollmentprocess' , get_string('fivthocharacters','block_proposals') , 'required' , 5000 , 'server');
        $mform->addRule('enrollmentprocess' ,get_string('fivthocharacters','block_proposals') , 'maxlength' , 5000 , 'server');


        $studyprocedure=array('placeholder' => '10,000 characters');
        $mform->addElement('textarea','studyprocedure',get_string('StudyProcedure','block_proposals'),$studyprocedure);
        $mform->addRule('studyprocedure' , get_string('tenthocharacters','block_proposals') , 'required' , 10000 , 'server');
        $mform->addRule('studyprocedure' ,get_string('tenthocharacters','block_proposals') , 'maxlength' , 10000 , 'server');
        $mform->addHelpButton('studyprocedure','StudyProcedure','block_proposals');


        //Studyprocedureimage
        $mform->addElement('filemanager', 'studyprocedurefile', get_string('StudyProcedurefile', 'block_proposals'), null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('*')));
        $mform->addRule('studyprocedurefile', get_string('file','block_proposals'), 'required', null, 'server');

        $mform->addElement('html','</br>');
        $definitionoptions = array('subdirs'=>false, 'maxfiles'=>1, 'maxbytes'=>$maxbytes, 'trusttext'=>true,
        'context'=>$context);

        $samplesizejustification=array('placeholder' => 'Sample size justification 5000 characters');
        $mform->addElement('textarea', 'samplesizejustrification',get_string('samplesizejustification','block_proposals'), $samplesizejustification);
        $mform->addRule('samplesizejustrification' , get_string('fivthocharacters','block_proposals') , 'required' , 5000 , 'server');
        $mform->addRule('samplesizejustrification' ,get_string('fivthocharacters','block_proposals') , 'maxlength' , 5000 , 'server');

        $definitionoptions = array('subdirs'=>false, 'maxfiles'=>1, 'maxbytes'=>$maxbytes, 'trusttext'=>true,
        'context'=>$context);
        //samplesizejustification_file 
        $mform->addElement('filemanager', 'samplesizejustrificationfile', get_string('samplesizejustrificationfile', 'block_proposals'), null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('*')));

        $mform->addRule('samplesizejustrificationfile', get_string('file','block_proposals'), 'required', null, 'server');

        $methodofsampling = array();
        $methodofsampling[null]='Select';
        $methodofsampling ['Simple random'] = get_string('Simplerandom','block_proposals');
        $methodofsampling ['Systematic sampling'] = get_string('Systematicsampling','block_proposals');
        $methodofsampling ['Stratified sampling'] = get_string('Stratifiedsampling','block_proposals');
        $methodofsampling ['Clustered sampling'] = get_string('Clusteredsampling','block_proposals');
        $methodofsampling ['Convenience sampling'] = get_string('Conveniencesampling','block_proposals');


        $methodofsampling ['Quota sampling'] = get_string('Quotasampling','block_proposals');
        $methodofsampling ['Purposive sampling'] = get_string('Purposivesampling','block_proposals');
        $methodofsampling ['Snowball sampling'] = get_string('Snowballsampling','block_proposals');
        $methodofsampling ['Others'] = get_string('Others','block_proposals');

        $methodofsampling ['Not applicable'] = get_string('Notapplicable','block_proposals');



        // $methodofsampling=array('placeholder' => 'Method of sampling');
        $mform->addElement('select', 'methodofsampling',get_string('Methodofsampling','block_proposals'), $methodofsampling);
        $mform->addRule('methodofsampling', get_string('mustbeselect','block_proposals'),'required', null, 'server');


        $mform->disabledIf('methodofsamplingother', 'methodofsampling', 'neq', $methodofsampling['Others']);
        $mform->addElement('text','methodofsamplingother',get_string('Others','block_proposals'));


        // $biologicalmaterialhandling=array();

        $biologicalmaterialhandling[] = $mform->createElement('radio', 'biologicalhandling', '', get_string('yes'), 1);
        $biologicalmaterialhandling[] = $mform->createElement('radio', 'biologicalhandling', '', get_string('no'), 0);
        $mform->addGroup($biologicalmaterialhandling, 'radioar', 'Biological material handling', array(' '), false);

        $mform->disabledIf('biologicalmaterialhandling', 'biologicalhandling', 'eq', 0);

        $Biologicalmaterialhandling=array('placeholder' => '(If Yes – Provide Detail)');
        $mform->addElement('text','biologicalmaterialhandling',get_string('If-yes','block_proposals'),$Biologicalmaterialhandling);
        $mform->addHelpButton('biologicalmaterialhandling','ifyeshand','block_proposals');


        $phaseoftrial = array();
        $phaseoftrial[null]='Select';
        $phaseoftrial ['Phase-I'] = get_string('Phase-I','block_proposals');
        $phaseoftrial ['Phase-II'] = get_string('Phase-II','block_proposals');
        $phaseoftrial ['Phase-III'] = get_string('Phase-III','block_proposals');
        $phaseoftrial ['Phase-IV'] = get_string('Phase-IV','block_proposals');
        $phaseoftrial ['PMS (Post-Marketing Study)'] = get_string('Post-MarketingStudy','block_proposals');
        $phaseoftrial ['N/A'] = get_string('N/A','block_proposals');

        $mform->addElement('select','phaseoftrial',get_string('Phaseoftrial','block_proposals'),$phaseoftrial);
        $mform->addRule('phaseoftrial', get_string('mustbeselect','block_proposals'),'required', null, 'server');


        $studyperiodandduration=array('placeholder' => '256 characters');
        $mform->addElement('date_selector','startdate',get_string('startdate','block_proposals'));
        $mform->addElement('date_selector','enddate',get_string('enddate','block_proposals'));

        // $mform->closeHeaderBefore('briefsummary');

        $vancouverstyleonly=array('placeholder' => '10,000 characters');
        $mform->addElement('textarea','vancouverstyleonly',get_string('Vancouverstyleonly','block_proposals'),$vancouverstyleonly);
        $mform->addRule('vancouverstyleonly' , get_string('tenthocharacters','block_proposals') , 'required' , 10000 , 'server');
        $mform->addRule('vancouverstyleonly' ,get_string('tenthocharacters','block_proposals') , 'maxlength' , 10000 , 'server');


        //files to be included27
        $mform->addElement('html','<button><a href="'.$CFG->wwwroot.'/blocks/proposals/pdf/templateicf.docx" style="color:white" download>Template</a></button>');


        $mform->addElement('filemanager', 'informedconsentform', get_string('Informedconsentform', 'block_proposals'), null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('.doc','.pdf')));
        $mform->addHelpButton('informedconsentform','Informedconsentform','block_proposals'); 

        //files to included28
        $mform->addElement('html','<button><a href="'.$CFG->wwwroot.'/blocks/proposals/pdf/template.pdf" style="color:white" download>Template</a></button>');


        $mform->addElement('html','<button style="margin-left:20px"><a href="'.$CFG->wwwroot.'/blocks/proposals/pdf/sample.pdf" style="color:white" download>Sample</a></button>');


        $mform->addElement('filemanager','patientinformationsheet',get_string('participantinformationsheet','block_proposals'),null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('.doc','.pdf')));


        $mform->addElement('filemanager','casereportform',get_string('casereportform','block_proposals'),null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('.doc','.pdf')));
        $mform->addRule('casereportform', get_string('file','block_proposals'), 'required', null, 'server');
        $mform->addHelpButton('casereportform', 'casereportform' , 'block_proposals');


        $mform->addElement('filemanager','waiverofconsentform',get_string('Waiverofconsentform','block_proposals'),null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('.doc','.pdf')));


        $mform->addElement('filemanager','otherquestionnaires',get_string('OtherQuestionnaires','block_proposals'),null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('.doc','.pdf','.jpg')));

        $mform->addElement('filemanager','otherquestionnairesone',get_string('oneAdditionalDocument','block_proposals'),null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('.doc','.pdf','.jpg')));
        $mform->addElement('filemanager','otherquestionnairestwo',get_string('twoAdditionalDocument','block_proposals'),null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('.doc','.pdf','.jpg')));
        $mform->addElement('filemanager','otherquestionnairesthr',get_string('thrAdditionalDocument','block_proposals'),null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('.doc','.pdf','.jpg')));
        $mform->addElement('filemanager','otherquestionnairesfou',get_string('fouAdditionalDocument','block_proposals'),null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('.doc','.pdf','.jpg')));
        $mform->addElement('filemanager','otherquestionnairesfiv',get_string('fivAdditionalDocument','block_proposals'),null,
        array('subdirs' => 0,'maxfiles' => 1,
        'accepted_types' => array('.doc','.pdf','.jpg')));


        $mform->addElement('html','</br>');
        $mform->addElement('html','</br>');
        $mform->addElement('html','</br>');

        $mform->addElement('html','<p style="margin-left:80px">Please download the uploaded protocol. Submit one hard copy with the signature of guide & Head of the
        department. Along with application form</p>');

        $mform->addElement('html','</br>');

        $mform->addElement('html','<label style="margin-left:200px">Signature & stamp of HOD</label>');


        $mform->addElement('html','<label style="margin-left:400px">Signature & stamp of Guide</label>');

        $mform->addElement('html','</br>');
        $mform->addElement('html','</br>');

        $mform->addElement('html','<hr style="border:1px solid">');
         
        $mform->addElement('html','</div>');
        $mform->addElement('html','</div>');

        $data = $DB->get_record('submissions',array('id'=>$fid));
        // if($data->status == 0){
       $this->add_action_buttons($cancel = false, $submitlabel=get_string('savedraft','block_proposals')); 
        // }
        $this->add_action_buttons($cancel = true,$submitlabel=get_string('submit','block_proposals'));
       
        // if ($data->draft == 'n1') {
        //    $this->add_action_buttons($cancel = false, $submitlabel=get_string('preview','block_proposals'));
        // }
            
    } 
   function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if (!empty($data['startdate']) && !empty($data['enddate']) &&
            ($data['enddate'] < $data['startdate'])) {
        
                $errors['enddate'] = get_string('enddatebeforestartdate','block_proposals');
        }
             return $errors;
    }


}
