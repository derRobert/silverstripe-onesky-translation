<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 26.04.2015
 * Time: 18:49
 */
class MemberDateFormat extends Extension {

    public function MemberFormat() {
        require_once 'Zend/Date.php';
        if(!$this->owner->value || !Zend_Date::isDate($this->owner->value, 'YYYY-mm-dd')) {
            user_error('This is not a valid date value.', E_USER_ERROR);
        }
        $date = new Zend_Date($this->owner->value);
        if(Member::currentUser() && Member::currentUser()->DateFormat) {
            return $date->toString(Member::currentUser()->DateFormat);
        }
        return $date->toString('dd.mm.YYYY');
    }

    public function MemberDateTimeFormat() {
        require_once 'Zend/Date.php';
        if(!$this->owner->value || !Zend_Date::isDate($this->owner->value, 'YYYY-mm-dd')) {
            user_error('This is not a valid date value.', E_USER_ERROR);
        }
        $date = new Zend_Date($this->owner->value);
        if(Member::currentUser() && Member::currentUser()->TimeFormat) {
            return $date->toString(Member::currentUser()->TimeFormat);
        }
        return $date->toString('dd.mm.YYYY H:i:s');
    }

}