<?php

/**
 * Contains administrative functionality
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Admin 
{
    var $error;

    /**
     * Constructor for HMS_Admin
     * Sets default values to NULL or empty string
     *
     * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
     */
    function HMS_Admin()
    {
        $this->error = "";
    }

    /**
     * Main method for all administrative tasks.
     * Farms tasks out to other classes.
     *
     * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
     */
    function main()
    {
        if(isset($_REQUEST['type'])) {
            $type = $_REQUEST['type'];
        } else {
            $type = 'main';
        }
       
        switch($type)
        {
            case 'term':
                PHPWS_Core::initModClass('hms', 'HMS_Term.php');
                $final = HMS_Term::main();
                break;
            case 'hall':
                PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
                $final = HMS_Residence_Hall::main();
                break;
            case 'floor':
                PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
                $final = HMS_Floor::main();
                break;
            case 'room':
                PHPWS_Core::initModClass('hms', 'HMS_Room.php');
                $final = HMS_Room::main();
                break;
            case 'suite':
                PHPWS_Core::initModClass('hms', 'HMS_Suite.php');
                $final = HMS_Suite::main();
                break;
            case 'bed':
                PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
                $final = HMS_Bed::main();
                break;
            case 'student':
                PHPWS_Core::initModClass('hms', 'HMS_Student.php');
                $final = HMS_Student::main();
                break;
            case 'maintenance':
                PHPWS_Core::initModClass('hms', 'HMS_Maintenance.php');
                $final = HMS_Maintenance::main();
                break;
            case 'display':
                PHPWS_Core::initModClass('hms', 'HMS_Display.php');
                $final = HMS_Display::main();
                break;
            case 'assignment':
                PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
                $final = HMS_Assignment::main();
                break;
            case 'rlc':
                PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
                $final = HMS_Learning_Community::main();
                break;
            case 'admin':
                switch($_REQUEST['op'])
                {
                    case 'show_primary_admin_panel':
                        $final = HMS_Admin::show_primary_admin_panel();
                        break;
                    case 'withdrawn_search_start':
                        $final = HMS_Admin::withdrawn_search_start();
                        break;
                    case 'withdrawn_search':
                        $final = HMS_Admin::withdrawn_search();
                        break;
                    case 'withdrawn_search_process':
                        $final = HMS_Admin::withdrawn_search_process();
                        break;
                    default:
                        PHPWS_Core::initModClass('hms', 'HMS_Display.php');
                        $final = HMS_Display::main();
                        break;
                }
                break;
            case 'xml':
                PHPWS_Core::initModClass('hms','HMS_XML.php');
                $final = HMS_XML::main();
                break;
            case 'roommate':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                $final = HMS_Roommate::main();
                break;
            case 'reports':
                PHPWS_Core::initModClass('hms', 'HMS_Reports.php');
                $final = HMS_Reports::main();
                break;
            case 'autoassign':
/*                PHPWS_Core::initModClass('hms', 'HMS_Pending_Assignment.php');
                $final = HMS_Pending_Assignment::main();*/
                PHPWS_Core::initModClass('hms', 'HMS_Autoassigner.php');
                // TRUE for test, FALSE for real
                $final = HMS_Autoassigner::auto_assign(FALSE);
                break;
            case 'letter':
                PHPWS_Core::initModClass('hms', 'HMS_Letter.php');
                $final = HMS_Letter::main();
                break;
            case 'queue':
                $result = HMS_Admin::handle_queues();
                if($result !== TRUE) {
                    $final = $result;
                    break;
                }
                PHPWS_Core::initModClass('hms', 'HMS_Maintenance.php');
                $_REQUEST['op'] = 'show_maintenance_options';
                $final = HMS_Maintenance::main();
                break;
            case 'movein':
                PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');
                $final = HMS_Movein_Time::main();
                break;
            case 'deadlines':
                PHPWS_Core::initModClass('hms', 'HMS_Deadlines.php');
                $final = HMS_Deadlines::main();
                break;
            case 'activity_log':
                PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
                $final = HMS_Activity_Log::main();
                break;
            default:
                PHPWS_Core::initModClass('hms', 'HMS_Maintenance.php');
                $_REQUEST['op'] = 'show_maintenance_options';
                $final = HMS_Maintenance::main();
                break;
        }

        //Layout::add($final);
        PHPWS_Core::initCoreClass('Cookie.php');

        $link        = "index.php?module=hms&type=maintenance&op=show_maintenance_options";
        $content     = $final;
        $tab         = (isset($_GET['tab']) ? $_GET['tab'] : null);

        //check to see if a user has a default tab set, otherwise just show them the main tab
        if( !isset($_SESSION['Panel_Current_Tab']['hmsMaintenance']) ){
            $tab = PHPWS_Cookie::read('default_tab');
        }

        if( $tab != null ){
            PHPWS_Core::initModClass('hms', 'HMS_Maintenance.php');

            switch( $tab ){
                case 'maintenance_main':
                    $content = $final;
                    break;
                case 'logs':
                    PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
                    $content = HMS_Activity_log::main();
                    break;
                case 'structure':
                    $content = HMS_Maintenance::show_options(MENU_TYPE_STRUCTURE);
                    break;
                case 'rlc':
                    $content = HMS_Maintenance::show_options(MENU_TYPE_RLC);
                    break;
                case 'assignment':
                    $content = HMS_Maintenance::show_options(MENU_TYPE_ASSIGNMENT);
                    break;
                case 'statistics':
                    PHPWS_Core::initModClass('hms', 'HMS_Display.php');
                    $content = HMS_Display::get_system_statistics();
                    break;
                case 'reports':
                    PHPWS_Core::initModClass('hms', 'HMS_Reports.php');
                    $content = HMS_Reports::display_reports();
                    break;
                default:
                    $content = $final;
                    break;
            }
        }

        $tabs['maintenance_main']   = array("title" => "Main Menu", "link" => $link,
                                        "link_title" => "View Maintenance Options");
        $tabs['structure']          = array("title" => "Hall Structure", "link" => $link,
                                        "link_title" => "Structure Options");
        $tabs['rlc']                = array("title" => "RLC Maintenance", "link" => $link,
                                        "link_title" => "View RLC Maintenance Options");
        $tabs['assignment']         = array("title" => "Assignment", "link" => $link,
                                        "link_title" => "Assignment Options");
        $tabs['statistics']         = array("title" => "Statistics", "link" => $link,
                                        "link_title" => "View Statistics");
        $tabs['logs']               = array("title" => "Activity Logs", "link" => $link,
                                        "link_title" => "Activity Logs");
        $tabs['reports']            = array('title' => 'Reports', 'link' => $link,
                                        'link_title'=>'Reports');
        
        //Allow a user to set their default tab
        if( $tab != PHPWS_Cookie::read('default_tab') && !isset($_REQUEST['make_default_tab']) ){
            $content = "<a href='index.php?module=hms&type=maintenance&op=" . $_REQUEST['op'] . 
                       ($tab != null ? "&tab=" . $tab : "&tab=maintenance_main")
                       . "&make_default_tab=true'>Make Default Tab</a>" . $content;
        }

        if( isset($_REQUEST['make_default_tab']) ){
            PHPWS_Core::initCoreClass('Cookie.php');
            PHPWS_Cookie::write('default_tab', $tab);
        }

        PHPWS_Core::initModClass("controlpanel", "Panel.php");
        $panel = &new PHPWS_Panel("hmsMaintenance");
        $panel->quickSetTabs($tabs);
        if( $tab != null ){
            $panel->setCurrentTab($tab);
        }

        Layout::add($panel->display($content));
        Layout::addStyle('controlpanel');

        if(Current_User::allow('hms', 'reports')) 
            $links[] = PHPWS_Text::secureLink(_('HMS Reports'), 'hms', array('type'=>'reports', 'op'=>'display_reports')); 

        if(Current_User::allow('hms', 'search'))
            $links[] = PHPWS_Text::secureLink(_('Search Students'), 'hms', array('type'=>'student', 'op'=>'enter_student_search_data'));
        
        $links[] = PHPWS_Text::secureLink(_('Comprehensive Maintenance'), 'hms', array('type'=>'maintenance', 'op'=>'show_maintenance_options'));

        if(Current_User::isUnrestricted('users') || Current_User::isRestricted('users'))
            $links[] = PHPWS_Text::secureLink(_('Control Panel'), 'controlpanel');

        if( Current_User::allow('hms', 'login_as_student') && (isset($_SESSION['login_as_student']) && $_SESSION['login_as_student'] == true) ) 
            $links[] = PHPWS_Text::secureLink(_('Logout of Student Session'), 'hms', array('op' => 'end_student_session'));
            
        $links[] = PHPWS_Text::secureLink(_('Logout'), 'users', array('action'=>'user', 'command'=>'logout'));

        MiniAdmin::add('hms', $links);
    }

    /**
     * Display the admin panel requested by Housing 
     */
    function show_primary_admin_panel()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::show_primary_admin_panel();
    }

    /**
     * Shows the page where the user can start the withdrawn student search
     */
    function withdrawn_search_start($success_msg = NULL, $error_msg = NULL)
    {
        if(!Current_User::allow('hms', 'search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $form = &new PHPWS_Form();
        $form->addSubmit('start', 'Begin Withdrawn Student Search');

        $form->addHidden('mod', 'hms');
        $form->addHidden('type', 'admin');
        $form->addHidden('op', 'withdrawn_search');

        $tpl = $form->getTemplate();

        $tpl['TERM'] = HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);

        if(isset($success_msg)){
            $tpl['SUCCESS_MSG'] = $success_msg;
        }

        if(isset($error_msg)){
            $tpl['ERROR_MSG'] = $error_msg;
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/withdrawn_search_start.tpl');
    }

    /**
     * Performs the withdrawn student search
     */
    function withdrawn_search()
    {
        if(!Current_User::allow('hms', 'search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Student.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        //$result = PHPWS_DB::select('col', 'select DISTINCT * FROM (select hms_student_id from hms_application UNION select asu_username from hms_assignment) as foo');
        //$result = PHPWS_DB::query('select DISTINCT * FROM (select hms_student_id from hms_application UNION select asu_username from hms_assignment) as foo');

        $db = &new PHPWS_DB('hms_application');
        $term = HMS_Term::get_selected_term();
       
        // This is ugly, but it does what we need it to do...
        // (necessary since not everyone who is assigned will have an application) 
        $db->setSQLQuery("select DISTINCT * FROM (select hms_student_id from hms_application WHERE term=$term UNION select asu_username from hms_assignment WHERE term=$term) as foo");
        $result = $db->select('col');

        //test($result);

        if(PEAR::isError($result)){
            PHPWS_Error::logIfError($result);
            return HMS_Admin::withdrawn_search_start(NULL, 'An error occured while working with the HMS database.');
        }

        $form = &new PHPWS_Form('withdrawn_form');
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'admin');
        $form->addHidden('op', 'withdrawn_search_process');
        $form->addSubmit('submit', 'Remove Withdrawn Students');

        # Lookup each student
        $i = 0;
        foreach($result as $asu_username){
            # Query SOAP, skiping students who are not withdrawn
            if(HMS_SOAP::get_student_type($asu_username, $term) != TYPE_WITHDRAWN){
                continue;
            }

            $assignment = HMS_Assignment::get_assignment($asu_username, $term);

            $tpl['withdrawn_students'][] = array(
                                    'NAME'              => HMS_Student::get_link($asu_username, TRUE),
                                    'BANNER_ID'         => HMS_SOAP::get_banner_id($asu_username),
                                    'REMOVE_CHECKBOX'   => '<input type="checkbox" name="remove_checkbox' . "[{$i}]" . '" value="' . $asu_username . '" checked>',
                                    'ASSIGNMENT'        => is_null($assignment)?'None':PHPWS_Text::secureLink($assignment->where_am_i(), 'hms', array('type'=>'room', 'op'=>'show_edit_room', 'room'=>$assignment->get_room_id()))
                                    );
            $i++;
        }

        $tpl['TITLE'] = 'Withdrawn Search - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        $tpl['COUNT'] = sizeof($tpl['withdrawn_students']);

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/withdrawn_search.tpl');
    }

    function withdrawn_search_process()
    {
        if(!Current_User::allow('hms', 'search')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        
        #test($_REQUEST['remove_checkbox']);

        $tpl['status']      = array();
        $tpl['warnings']    = array();
        $tpl['TITLE']       = 'Withdrawn Removal Results';

        $term = HMS_Term::get_selected_term();

        # Process each of the selected students
        foreach($_REQUEST['remove_checkbox'] as $asu_username){
            
            # Check for and mark as withdrawn any application
            if(HMS_Application::check_for_application($asu_username, $term, TRUE) != FALSE){
                $application = &new HMS_Application($asu_username, $term);
                $application->withdrarwn = 1;
                $app_result = $application->save();
                if(PEAR::isError($app_result)){
                    $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                               'MESSAGE'    => 'Saving application failed (database error).');
                }else{
                    $tpl['status'][] = array('USERNAME'    => $asu_username,
                                             'MESSAGE'     => "Application removed.");
                }

                # TODO: log application withdrawl
            }
            /*
            # Check for and delete any assignments
            if(HMS_Assignment::check_for_assignment($asu_username, $term)){
                $assignment = HMS_Assignment::get_assignment($asu_username, $term);
                if($assignment == NULL || $assignment == FALSE){
                    $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                               'MESSAGE'    => 'Error loading assignment.');
                }else{
                    if($assignment->delete() != TRUE){
                        $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                   'MESSAGE'    => 'Error deleting the assignment.');
                    }else{
                        $tpl['status'][] = array('USERNAME'    => $asu_username,
                                                 'MESSAGE'     => "Assignment removed.");
                        # TODO: log assignment removal
                    }
                }
            }
            */

            /*
            # check for and delete any roommate requests, perhaps let the other roommate know?
            $roommates = HMS_Roommate::get_all_roommates($asu_username);
            if($roommates == FALSE){
                $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                           'MESSAGE'    => 'Error deleting the assignment.');
            }else if(sizeof($roommates) > 0){
                # Delete each roommate request
                foreach($roommates as $rm){
                    if($rm->delete() == TRUE){
                        $tpl['status'][] = array('USERNAME'    => $asu_username,
                                                 'MESSAGE'     => "Roommate request removed. {$rm->requestor}->{$rm->$requestee}");
                        # TODO: log the roommate request removal
                        # TODO: notify the other roommate, perhaps?
                    }else{
                        $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                   'MESSAGE'    => "Error deleting roommate request. {$rm->requestor}->{$rm->requestee}");
                    }
                }
            }
            */

            /*
            # Check for and delete any learning community assignments
            $rlc_app = HMS_RLC_Application::check_for_application($asu_username, $term, FALSE); // look only in non-denied RLC apps for this term
            if(PEAR::isError($rlc_app)){
                $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                           'MESSAGE'    => 'Error looking for RLC application.');
            }else if($rlc_app != FALSE){
                # Get their rlc app
                $rlc_app = &new HMS_RLC_Application($asu_username, $term);
                if(PEAR::isError($rlc_app)){
                    $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                               'MESSAGE'    => 'Error loading RLC application.');
                }else{
                    # See if they're assigned anywhere
                    $assignment_id = $rlc_app->hms_assignment_id;
                    if($assignment_id != NULL){
                        # Delete the assignment id from the application
                        $rlc_app->hms_assignment_id = NULL;
                        $rlc_app->denied = 1; // Mark as denied so it won't bother anyone
                        if(PEAR::isError($rlc_app->save())){
                            $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                       'MESSAGE'    => 'Error saving rlc application.');
                        }else{
                            $rlc_assignment = &new HMS_RLC_Assignment($assignment_id);
                            if(PEAR::isError($rlc_assignment)){
                                $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                           'MESSAGE'    => 'Error loading RLC assignment.');
                            }else{
                                if($rlc_assignment->delete() != TRUE){
                                    $tpl['warnings'][] = array('USERNAME'   => $asu_username,
                                                               'MESSAGE'    => 'Error deleting RLC assignment.');
                                }else{
                                    $tpl['status'][] = array('USERNAME'    => $asu_username,
                                                             'MESSAGE'     => 'Deleted RLC assignment.');
                                    #TODO: log removal of RLC assignment
                                }
                            }
                        }
                    }
                }
                                
            }
            */
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/withdrawn_search_process.tpl');
    }
}

?>
