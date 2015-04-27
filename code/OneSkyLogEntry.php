<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 27.04.2015
 * Time: 11:37
 */
class OneSkyLogEntry extends DataObject{

    private static $db = array(
        'Action' => 'Varchar',
        'Locale' => 'Varchar(5)',
        'ResponseCode' => 'Int'
    );

    private static $has_one = array(
        'Member' => 'Member'
    );

    private static $default_sort = 'ID DESC';

    private static $summary_fields = array(
        'Created' => 'Created',
        'Action' => 'Action',
        'Locale' => 'Locale',
        'ResponseNice' => 'Status',
        'Member.Email' => 'Email'
    );


    public function onBeforeWrite() {
        if( !$this->MemberID ) {
            $this->MemberID = Member::currentUserID();
        }
        parent::onBeforeWrite();
    }

    public static function log($action, $locale, $response_code) {
        $Entry = new OneSkyLogEntry();
        $Entry->Action = $action;
        $Entry->Locale = $locale;
        $Entry->ResponseCode = $response_code;
        $Entry->write();
    }

    public function ResponseNice() {
        if( $this->ResponseCode && strpos((string)$this->ResponseCode, "2") == 0 ) {
            return 'Sucess';
        } else {
            return 'Error';
        }
    }


}