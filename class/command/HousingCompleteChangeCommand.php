<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'CommandFactory.php');

class HousingCompleteChangeCommand extends Command {
    public $username;

    public function getRequestVars(){
        $vars = array('action'=>'HousingCompleteChange');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        return $vars;
    }

    public function execute(CommandContext $context){
        // Command to redirect to when done (success or error)
        $cmd = CommandFactory::getCommand('HousingRoomChange');
        $cmd->tab = 'complete';

        if(!is_null($context->get('username'))){
            $rc = new RoomChangeRequest;
            $rc = $rc->search($context->get('username'));
        } else {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Cannot complete room change for non-existant user!');
            $cmd->redirect();
        }

        if($rc->change(new CompletedChangeRequest) && $rc->save()){
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Room Change Completed');
        }else{
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not complere room change.');
        }

        $cmd->redirect();
    }
}
?>