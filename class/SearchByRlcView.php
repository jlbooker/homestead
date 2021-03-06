<?php

PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

class SearchByRlcView extends hms\View {

    public function show(){
        PHPWS_Core::initCoreClass('Form.php');
        $form = new PHPWS_Form;
        $form->addDropBox('rlc', HMS_Learning_Community::getRlcList());
        $form->setClass('rlc', 'form-control');
        $form->addHidden('module', 'hms');
        $form->addHidden('action', 'ShowSearchByRlc');
        $form->addSubmit('submit', _('Search'));
        $form->setClass('submit', 'btn btn-primary pull-right');

        $tags = $form->getTemplate();
        $tags['TITLE'] = "RLC Search";

        Layout::addPageTitle("RLC Search");

        $final = PHPWS_Template::processTemplate($tags, 'hms', 'admin/search_by_rlc.tpl');
        return $final;
    }
}
