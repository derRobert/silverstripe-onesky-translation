<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 24.04.2015
 * Time: 10:46
 */
class OneSkyClient extends \Onesky\Api\Client {


    public function __construct(){
        $api_key        = Config::inst()->get('OneSkyClient', 'api_key');
        $secret_key     = Config::inst()->get('OneSkyClient', 'secret_key');
        $this->setApiKey($api_key)->setSecret($secret_key);
    }

    public function uploadBaseLanguageFile() {
        $cfg = Config::inst()->forClass('OneSkyClient');
        $response = $this->files('upload', array(
            'project_id'  => $cfg->project_id,
            'file'        => Director::baseFolder().'/'.$cfg->files_dir.'/'.$cfg->base_language['file'],
            'file_format' => $cfg->file_format,
            'locale'      => $cfg->base_language['code']
        ));
        return  json_decode($response);
    }

    public function downloadTranslationFile($locale_string) {
        $cfg = Config::inst()->forClass('OneSkyClient');
        $dest_dir = Director::baseFolder().'/'.$cfg->files_dir;
        if( !is_writable($dest_dir) ) {
            user_error('Cannot write to dir: '.$dest_dir);
        }
        $file_path = $dest_dir . '/' . $locale_string. '.yml';
        $response = $this->translations('export', array(
            'project_id' => $cfg->project_id,
            'locale'     => $locale_string,
            'source_file_name' => $cfg->base_language['file']
        ));
        try {
            file_put_contents($file_path, $response);
        } catch (Exception $ex) {
            return false;
        }
        return true;

    }

    public function getLanguages() {
        $ret = ArrayList::create();
        $response = $this->projects('languages', array(
            'project_id' => Config::inst()->get('OneSkyClient', 'project_id')
        ));
        $result =  json_decode($response);
        if( $result && $result->meta->status == 200 ) {
            foreach( $result->data as $lang ) {
                $ret->push( ArrayData::create(array(
                    "code" => $lang->code,
                    "english_name" => $lang->english_name,
                    "local_name" => $lang->local_name,
                    "custom_locale" => $lang->custom_locale,
                    "locale" => $lang->locale,
                    "region" => $lang->region,
                    "is_base_language" => $lang->is_base_language,
                    "is_ready_to_publish" => $lang->is_ready_to_publish,
                    "translation_progress" => $lang->translation_progress,
                    "local_filetime" => @filemtime( Director::baseFolder().'/'.Config::inst()->get('OneSkyClient', 'files_dir')."/{$lang->locale}.yml" )
                )) );
            }
        }
        return $ret;
    }

    /**
     * @return false|ArrayData
     */
    public function getProjectInfo() {
        $response = $this->projects('show', array(
            'project_id' => Config::inst()->get('OneSkyClient', 'project_id')
        ));
        $result =  json_decode($response);
        if( $result && $result->meta->status == 200 ) {
            return ArrayData::create(array(
                'name' => $result->data->name,
                'description' => $result->data->description,
                'string_count' => $result->data->string_count,
                'word_count' => $result->data->word_count,
            ));
        }
        return false;
    }






}