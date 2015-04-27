<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 24.04.2015
 * Time: 10:45
 */

class T extends Controller {

    private static $allowed_actions = array(
        'index',
        'uploadOriginal',
        'languages',
        'downloadTranslations'
    );

    /**
     * @var null|OneSkyClient
     */
    protected $api = null;

    public function init() {
        parent::init();
        $this->api = new OneSkyClient();
    }


    public function index( SS_HTTPRequest $request ){

        $response = $this->api->projects('show', array(
            'project_id' => Config::inst()->get('OneSkyClient', 'project_id')
        ));
        Debug::dump( json_decode($response, true) );


        return array();
    }
    public function uploadOriginal( SS_HTTPRequest $request ) {
        $this->api->uploadBaseLanguageFile();
        return array();
    }

    public function downloadTranslations( SS_HTTPRequest $request ) {
        $cfg = Config::inst()->forClass('OneSkyClient');
        $dest_dir = Director::baseFolder().'/'.$cfg->files_dir;
        if( !is_writable($dest_dir) ) {
            user_error('Cannot write to dir: '.$dest_dir);
        }
        if( $languages = $this->api->getLanguages()) foreach( $languages as $lang ) {
            if( !$lang->is_base_language ) { // skip default language
                $filename_append = ($lang->region ? '_' . $lang->region : '');
                $file_path = $dest_dir . '/' . $lang->locale . $filename_append . '.yml';
                $response = $this->api->translations('export', array(
                    'project_id' => $cfg->project_id,
                    'locale'     => $lang->locale,
                    'source_file_name' => $cfg->base_language['file']
                ));
                file_put_contents($file_path, $response);
            }
        }
        return array();
    }

    public function languages( SS_HTTPRequest $request ) {
        $response = $this->api->projects('languages', array(
            'project_id' => Config::inst()->get('OneSkyClient', 'project_id')
        ));
        Debug::dump( json_decode($response, true) );

        return array();
    }



}