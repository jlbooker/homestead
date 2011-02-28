<?php

/**
 * The HMS_RLC_Application class
 * Implements the RLC_Application object and methods to load/save
 * learning community applications from the database.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

define('RLC_RESPONSE_LIMIT', 4096); // max number of characters allowed in the text areas on the RLC application

// RLC application types
define('RLC_APP_FRESHMEN', 'freshmen');
define('RLC_APP_RETURNING', 'returning');

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Item.php');

class HMS_RLC_Application extends HMS_Item {

    public $id;

    public $username;
    public $date_submitted;

    public $rlc_first_choice_id;
    public $rlc_second_choice_id;
    public $rlc_third_choice_id;

    public $why_specific_communities;
    public $strengths_weaknesses;

    public $rlc_question_0;
    public $rlc_question_1;
    public $rlc_question_2;

    public $term = NULL;

    public $denied = 0;

    public $application_type;

    /**
     * Constructor
     * Set $username equal to the ASU email of the student you want
     * to create/load a application for. Otherwise, the student currently
     * logged in (session) is used.
     */
    public function HMS_RLC_Application($id = 0)
    {
        $this->construct($id);
    }

    public function getDb(){
        return new PHPWS_DB('hms_learning_community_applications');
    }

    public function getAdminPagerTags()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'Term.php');

        $student = StudentFactory::getStudentByUsername($this->username, Term::getCurrentTerm());

        $rlc_list = HMS_Learning_Community::getRLCList();

        $tags = array();

        $tags['NAME']           = $student->getFullNameProfileLink();

        $rlcCmd = CommandFactory::getCommand('ShowRlcApplicationReView');
        $rlcCmd->setAppId($this->getId());

        $tags['1ST_CHOICE']     = $rlcCmd->getLink($rlc_list[$this->getFirstChoice()],'_blank');
        if(isset($rlc_list[$this->getSecondChoice()]))
        $tags['2ND_CHOICE'] = $rlc_list[$this->getSecondChoice()];
        if(isset($rlc_list[$this->getThirdChoice()]))
        $tags['3RD_CHOICE'] = $rlc_list[$this->getThirdChoice()];
        $tags['FINAL_RLC']      = HMS_RLC_Application::generateRLCDropDown($rlc_list,$this->getID());
        $tags['CLASS']          = $student->getClass();
        //        $tags['SPECIAL_POP']    = ;
        //        $tags['MAJOR']          = ;
        //        $tags['HS_GPA']         = ;
        $tags['GENDER']         = $student->getPrintableGender();
        $tags['DATE_SUBMITTED'] = date('d-M-y',$this->getDateSubmitted());

        $denyCmd = CommandFactory::getCommand('DenyRlcApplication');
        $denyCmd->setApplicationId($this->getID());

        $tags['DENY']           = $denyCmd->getLink('Deny');

        return $tags;
    }

    public function applicantsReport()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $term = Term::getSelectedTerm();

        $student = StudentFactory::getStudentByUsername($this->username, $this->term);

        $application_date = isset($this->date_submitted) ? HMS_Util::get_long_date($this->date_submitted) : 'Error with the submission date';

        $roomie = NULL;
        if(HMS_Roommate::has_confirmed_roommate($this->username, $term)){
            $roomie = HMS_Roommate::get_Confirmed_roommate($this->username, $term);
        }
        elseif(HMS_Roommate::has_roommate_request($this->username, $term)){
            $roomie = HMS_Roommate::get_unconfirmed_roommate($this->username, $term) . ' *pending* ';
        }

        $row['last_name']           = $student->getLastName();
        $row['first_name']          = $student->getFirstName();
        $row['middle_name']         = $student->getMiddleName();
        $row['gender']              = $student->getGender();
        $row['roommate']            = $roomie;
        $row['email']               = $student->getUsername() . '@appstate.edu';
        $row['second_choice']       = $this->getSecondChoice();
        $row['third_choice']        = $this->getThirdChoice();
        $row['application_date']    = $application_date;
        $row['denied']              = (isset($this->denied) && $this->denied == 0) ? 'yes' : 'no';

        return $row;
    }

    public function getDeniedPagerTags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        $student = StudentFactory::getStudentByUsername($this->username, $this->term);

        $tags = array();
        $rlc_list = HMS_Learning_Community::getRLCList();

        $tags['NAME']           = $student->getProfileLink();

        $rlcCmd = CommandFactory::getCommand('ShowRlcApplicationReView');
        $rlcCmd->setAppId($this->getId());

        $tags['1ST_CHOICE']     = $rlcCmd->getLink($rlc_list[$this->getFirstChoice()],'_blank');

        if(isset($rlc_list[$this->getSecondChoice()]))
        $tags['2ND_CHOICE'] = $rlc_list[$this->getSecondChoice()];
        if(isset($rlc_list[$this->getThirdChoice()]))
        $tags['3RD_CHOICE'] = $rlc_list[$this->getThirdChoice()];
        $tags['CLASS']          = $student->getClass();
        $tags['GENDER']         = $student->getGender();
        $tags['DATE_SUBMITTED'] = date('d-M-y',$this->getDateSubmitted());

        $unDenyCmd = CommandFactory::getCommand('UnDenyRlcApplication');
        $unDenyCmd->setApplicationId($this->id);

        $tags['ACTION']         = $unDenyCmd->getLink('Un-Deny');

        return $tags;
    }

    public function viewByRLCPagerTags()
    {
        $student = StudentFactory::getStudentByUsername($this->username, Term::getSelectedTerm());

        $tags['NAME']       = $student->getFulLNameProfileLink();
        $tags['GENDER']     = $student->getPrintableGender();
        $tags['USERNAME']   = $this->username;

        $viewCmd = CommandFactory::getCommand('ShowRlcApplicationReView');
        $viewCmd->setAppId($this->getId());

        $actions[] = $viewCmd->getLink('View Application');

        $assign = HMS_RLC_Assignment::getAssignmentByUsername($this->username, $this->term);

        $rmCmd = CommandFactory::getCommand('RemoveRlcAssignment');
        $rmCmd->setAssignmentId($assign->id);

        $actions[] = $rmCmd->getLink('Remove');

        // Remove and Deny macro command
        $rmDenyCmd = CommandFactory::getCommand('RemoveDenyRlcAssignment');
        $rmDenyCmd->setAppId($this->getId());
        $rmDenyCmd->setAssignmentId($assign->id);
        
        $actions[] = $rmDenyCmd->getLink('Remove & Deny');

        $tags['ACTION'] = implode(' | ', $actions);

        // Show all possible roommates for this application
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $allRoommates = HMS_Roommate::get_all_roommates($this->username, $this->term);
        $tags['ROOMMATES'] = 'N/A'; // Default text

        if(sizeof($allRoommates) > 1){
            // Don't show all the roommates
            $tags['ROOMMATES'] = "Multiple Requests";
        } 
        elseif(sizeof($allRoommates) == 1) {
            // Get other roommate
            $otherGuy = StudentFactory::getStudentByUsername($allRoommates[0]->get_other_guy($this->username), $this->term);
            $tags['ROOMMATES'] = $otherGuy->getFullNameProfileLink();
            // If roommate is pending then show little status message
            if(!$allRoommates[0]->confirmed){
                $tags['ROOMMATES'] .= " (Pending)";
            }
        }

        return $tags;
    }

    public function report_by_rlc_pager_tags()
    {
        $student = StudentFactory::getStudentByUsername($this->username, $this->term);

        $row['name']        = $student->getFullName();
        $row['gender']      = $student->getPrintableGender();
        $row['username']    = $student->getUsername();

        return $row;
    }

    /*****************
     * Static Methods *
     *****************/

    /**
     * Check to see if an application already exists for the specified user. Returns FALSE if no application exists.
     * If an application does exist, an associative array containing that row is returned. In the case of a db error, a PEAR
     * error object is returned.
     * @param include_denied Controls whether or not denied applications are returned
     */
    public static function checkForApplication($username, $term, $include_denied = TRUE)
    {
        $db = new PHPWS_DB('hms_learning_community_applications');

        $db->addWhere('username',$username,'ILIKE');
        $db->addWhere('term', $term);

        if(!$include_denied){
            $db->addWhere('denied', 0);
        }

        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        if(sizeof($result) > 1){
            return $result;
        }else{
            return FALSE;
        }
    }

    public static function getApplicationByUsername($username, $term)
    {
        $app = new HMS_RLC_Application();

        $db = new PHPWS_DB('hms_learning_community_applications');

        $db->addWhere('username', $username, 'ILIKE');
        $db->addWhere('term', $term);

        $result = $db->loadObject($app);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        if($app->id == 0){
            return null;
        }

        return $app;
    }

    public static function getApplicationById($id){

        $app = new HMS_RLC_Application();

        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('id', $id);
        $result = $db->loadObject($app);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return $app;
    }

    /**
     * Get denied RLC applicants by term 
     * @return Array of Student objects
     */
    public static function getDeniedApplicantsByTerm($term)
    {
        // query DB
        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('denied',1);
        $db->addWhere('term', $term);
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        // create student objects from the denied applications
        $students = array();
        foreach($result as $app){
            $students[] = StudentFactory::getStudentByUsername($app['username'], $term);
        }

        return $students;
    }

    //TODO move this!!
    public function denied_pager()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = new DBPager('hms_learning_community_applications', 'HMS_RLC_Application');

        $pager->db->addWhere('term', Term::getSelectedTerm());
        $pager->db->addWhere('denied', 1); // show only denied applications

        $pager->db->addColumn('hms_learning_community_applications.*');
        $pager->db->addColumn('hms_learning_communities.abbreviation');
        $pager->db->addWhere('hms_learning_community_applications.rlc_first_choice_id',
                             'hms_learning_communities.id','=');

        $pager->setModule('hms');
        $pager->setTemplate('admin/denied_rlc_app_pager.tpl');
        $pager->setEmptyMessage("No denied RLC applications exist.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle1"');
        $pager->addRowTags('getDeniedPagerTags');

        return $pager->get();
    }

    /**
     * Generates a drop down menu using the RLC abbreviations
     */
    public static function generateRLCDropDown($rlc_list,$application_id){

        $output = "<select name=\"final_rlc[$application_id]\">";

        $output .= '<option value="-1">None</option>';

        foreach ($rlc_list as $id => $rlc_name){
            $output .= "<option value=\"$id\">$rlc_name</option>";
        }

        $output .= '</select>';

        return $output;
    }

    /****************************
     * Accessor & Mutator Methods
     ****************************/

    public function setID($id){
        $this->id = $id;
    }

    public function getID(){
        return $this->id;
    }

    public function setUsername($username){
        $this->username = $username;
    }

    public function getUsername(){
        return $this->username;
    }

    public function setDateSubmitted($date = NULL){
        if(!isset($date)){
            $this->date_submitted = mktime();
        }else{
            $this->date_submitted = $date;
        }
    }

    public function getDateSubmitted(){
        return $this->date_submitted;
    }

    public function setFirstChoice($choice){
        $this->rlc_first_choice_id = $choice;
    }

    public function getFirstChoice(){
        return $this->rlc_first_choice_id;
    }

    public function setSecondChoice($choice){
        $this->rlc_second_choice_id = $choice;
    }

    public function getSecondChoice(){
        return $this->rlc_second_choice_id;
    }

    public function setThirdChoice($choice){
        $this->rlc_third_choice_id = $choice;
    }

    public function getThirdChoice(){
        return $this->rlc_third_choice_id;
    }

    public function setWhySpecificCommunities($why){
        $this->why_specific_communities = $why;
    }

    public function getWhySpecificCommunities(){
        return $this->why_specific_communities;
    }

    public function setStrengthsWeaknesses($strenghts){
        $this->strengths_weaknesses = $strenghts;
    }

    public function getStrengthsWeaknesses(){
        return $this->strengths_weaknesses;
    }

    public function setRLCQuestion0($question){
        $this->rlc_question_0 = $question;
    }

    public function getRLCQuestion0(){
        return $this->rlc_question_0;
    }

    public function setRLCQuestion1($question){
        $this->rlc_question_1 = $question;
    }

    public function getRLCQuestion1(){
        return $this->rlc_question_1;
    }

    public function setRLCQuestion2($question){
        $this->rlc_question_2 = $question;
    }

    public function getRLCQuestion2(){
        return $this->rlc_question_2;
    }

    public function setAssignmentID($id){
        $this->hms_assignment_id = $id;
    }

    public function getAssignmentID(){
        return $this->hms_assignment_id;
    }

    /**
     * @depreciated
     * Use 'getTerm' instead.
     */
    public function getEntryTerm(){
        return $this->term;
    }

    /**
     * @depreciated
     * Use 'setTerm' instead.
     */
    public function setEntryTerm($term){
        $this->term = $term;
    }

    public function getTerm(){
        return $this->term;
    }

    public function setTerm($term){
        $this->term = $term;
    }

    public function getApplicationType(){
        return $this->application_type;
    }

    public function setApplicationType($type){
        $this->application_type = $type;
    }
}

?>
