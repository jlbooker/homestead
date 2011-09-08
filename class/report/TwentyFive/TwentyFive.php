<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class TwentyFive extends Report {
    const friendlyName = 'Students 25 and Older';
    const shortName = 'TwentyFive';

    private $term;
    private $all_rows;

    public function __construct($id = 0)
    {
        parent::__construct($id);
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        if (!isset($this->term) || is_null($this->term)) {
            throw new InvalidArgumentException('Missing term.');
        }

        $term = Term::getTermSem($this->term);

        $db = new PHPWS_DB('hms_new_application');

        $db->addColumn('banner_id');
        $db->addColumn('username');
        $db->addWhere('term', $this->term);

        //to debug
        //$db->setLimit(10);

        $results = $db->select();
        if (empty($results)) {
            return;
        } elseif (PEAR::isError($results)) {
            throw new DatabaseException($results->toString());
        }

        $tfyearsagomk = mktime(0, 0, 0, date('n'), date('j'), date('Y') - 25);
        $twenty_five_years_ago = date('Y-m-d', $tfyearsagomk);

        foreach ($results as $student) {
            $sf = StudentFactory::getStudentByBannerId($student['banner_id'], $term);
            $dob = $sf->getDOB();
            if ($dob > $twenty_five_years_ago) {
                continue;
            }
            $student['dob'] = $dob;
            $student['full_name'] = $sf->getFullName();
            $this->all_rows[] = $student;
        }
    }

    public function getRows()
    {
        return $this->all_rows;
    }

    public function getCsvColumnsArray()
    {
        return array('Banner ID', 'Username', 'Date of Birth', 'Name');
    }

    public function getCsvRowsArray()
    {
        return $this->all_rows;
    }

}

?>
