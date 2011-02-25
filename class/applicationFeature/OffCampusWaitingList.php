<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class OffCampusWaitingListRegistration extends ApplicationFeatureRegistration {

    function __construct()
    {
        $this->name = 'OffCampusWaitingList';
        $this->description = 'Open Waiting List';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 3;
    }

    public function showForStudent(Student $student, $term)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');

        if($student->getApplicationTerm() > Term::getCurrentTerm()){
            return false;
        }

        $app = HousingApplication::getApplicationByUser($student->getUsername(), $term, 'lottery');

        // Must be a returning student and either have not re-applied or have re-applied to the waiting list already
        if($student->getApplicationTerm() <= Term::getCurrentTerm() && (is_null($app) || (!is_null($app) && $app->application_type == 'lottery' && $app->waiting_list_hide == 1) || (!is_null($app) && $app->application_type == 'offcampus_waiting_list'))){
            return true;
        }

        return false;
    }
}

class OffCampusWaitingList extends ApplicationFeature {

    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'OffCampusWaitingListMenuBlockView.php');

        $application = HousingApplication::getApplicationByUser($student->getUsername(), $this->term, 'offcampus_waiting_list');

        return new OffCampusWaitingListMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $application);
    }
}

?>