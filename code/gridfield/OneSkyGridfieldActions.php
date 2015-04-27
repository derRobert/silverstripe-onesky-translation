<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 26.04.2015
 * Time: 12:44
 */
class OneSkyGridfieldActions implements GridField_ColumnProvider, GridField_ActionProvider {

    public function augmentColumns($gridField, &$columns) {
        if(!in_array('Actions', $columns)) {
            $columns[] = 'Actions';
        }
    }

    public function getColumnAttributes($gridField, $record, $columnName) {
        return array('class' => 'col-buttons');
    }

    public function getColumnMetadata($gridField, $columnName) {
        if($columnName == 'Actions') {
            return array('title' => '');
        }
    }

    public function getColumnsHandled($gridField) {
        return array('Actions');
    }

    public function getColumnContent($gridField, $record, $columnName) {
        if( $record->is_base_language ) {
            $field = GridField_FormAction::create(
                $gridField,
                'CustomAction' . $record->ID,
                'Upload',
                "uploadbaselanguagefile",
                array('code' => $record->code)
            );
        } else {
            $field = GridField_FormAction::create(
                $gridField,
                'CustomAction' . $record->ID,
                'Download',
                "downloadlanguagefile",
                array('code' => $record->code)
            );
        }

        return $field->Field();
    }

    public function getActions($gridField) {
        return array('uploadbaselanguagefile', 'downloadlanguagefile');
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
        if($actionName == 'uploadbaselanguagefile') {
            $client = new OneSkyClient();
            $response = $client->uploadBaseLanguageFile();
            if( $response && strpos((string)$response->meta->status, "2") == 0 ) { // responses of 2xx are succesful
                Controller::curr()->getResponse()->setStatusCode(
                    $rsp_code=200,
                    'Upload successful'
                );
            } else {
                Controller::curr()->getResponse()->setStatusCode(
                    $rsp_code = 500,
                    'Oopps, could not upload file'
                );

            }
            $cfg = Config::inst()->forClass('OneSkyClient');
            OneSkyLogEntry::log('upload', $cfg->base_language['code'], $rsp_code);
        } elseif( $actionName=="downloadlanguagefile" ) {
            $client = new OneSkyClient();
            $result = $client->downloadTranslationFile($arguments['code']);
            if( $result ) {
                Controller::curr()->getResponse()->setStatusCode(
                    $rsp_code = 200,
                    'Download successful'
                );
            } else {
                Controller::curr()->getResponse()->setStatusCode(
                    $rsp_code = 500,
                    'Oopps, could not download file'
                );
            }
            OneSkyLogEntry::log('download', $arguments['code'], $rsp_code);
        }
    }
}