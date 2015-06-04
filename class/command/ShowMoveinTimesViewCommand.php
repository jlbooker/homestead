<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'MoveinTimesView.php');
  /*
   * ShowMoveinTimesViewCommand
   *
   *   Creates and show's the interface for creating and removing movein times.
   */

class ShowMoveinTimesViewCommand extends Command {

    public function getRequestVars(){
        return array('action' => 'ShowMoveinTimesView');
    }

    public function execute(CommandContext $context){
        $view = new MoveinTimesView();
        $context->setContent($view->show());
    }
}

