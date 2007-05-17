<?php

/**
 * Uninstall file for hms
 */

function hms_uninstall(&$content)
{
    $result[] = PHPWS_DB::dropTable('hms_pricing_tiers');
    $result[] = PHPWS_DB::dropTable('hms_questionnaire');
    $result[] = PHPWS_DB::dropTable('hms_residence_hall');
    $result[] = PHPWS_DB::dropTable('hms_beds');
    $result[] = PHPWS_DB::dropTable('hms_bedrooms');
    $result[] = PHPWS_DB::dropTable('hms_room');
    $result[] = PHPWS_DB::dropTable('hms_roommates');
    $result[] = PHPWS_DB::dropTable('hms_roommate_hashes');
    $result[] = PHPWS_DB::dropTable('hms_student');
    $result[] = PHPWS_DB::dropTable('hms_suite');
    $result[] = PHPWS_DB::dropTable('hms_assignment');
    $result[] = PHPWS_DB::dropTable('hms_deadlines');
    $result[] = PHPWS_DB::dropTable('hms_learning_community_floors');
    $result[] = PHPWS_DB::dropTable('hms_floor');
    $result[] = PHPWS_DB::dropTable('hms_hall_communities');
    $result[] = PHPWS_DB::dropTable('hms_learning_community_applications');
    $result[] = PHPWS_DB::dropTable('hms_learning_community_questions');
    $result[] = PHPWS_DB::dropTable('hms_learning_community_assignment');
    $result[] = PHPWS_DB::dropTable('hms_learning_communities'); # must drop this after learning_community_applications and learning_community_questions becase of foreign keys
    $result[] = PHPWS_DB::dropTable('hms_application');
    $result[] = PHPWS_DB::dropTable('hms_student_profiles');
    $result[] = PHPWS_DB::dropTable('hms_roommate_approval');

    foreach($result as $r) {
        if(PEAR::isError($r)) {
            return $r;
        }
    }
    
    $content[] = _('HMS tables removed.');
    return TRUE;
}

?>
