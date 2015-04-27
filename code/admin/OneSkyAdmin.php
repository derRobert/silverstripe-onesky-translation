<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 24.04.2015
 * Time: 19:46
 */

class OneSkyAdmin extends LeftAndMain implements PermissionProvider {

    private static $menu_title      = "OneSky Übersetzung";
    private static $url_segment     = "onesky";
    //private static $menu_icon       = "mandrill/images/icon.png";
    //private static $url_rule        = '/$Action/$ID';
    private static $allowed_actions = array(
        "view",
    );

    /**
     * @var null|OneSkyClient
     */
    protected $api = null;

    /**
     * @var null|false|ArrayData
     */
    protected $project_info=null;


    public function init() {
        parent::init();
        $this->api = new OneSkyClient();
        $this->project_info = $this->api->getProjectInfo();
    }


    /**
     * Provides custom permissions to the Security section
     *
     * @return array
     */
    public function providePermissions()
    {
        $title = _t("OneSky.MENUTITLE",
            LeftAndMain::menu_title_for_class('OneSky'));
        return array(
            "CMS_ACCESS_OneSky" => array(
                'name' => _t('OneSky.ACCESS', "Access to '{title}' section",
                    array('title' => $title)),
                'category' => _t('Permission.CMS_ACCESS_CATEGORY', 'CMS Access'),
                'help' => _t(
                    'OneSky.ACCESS_HELP',
                    'Allow use of OneSky admin section'
                )
            ),
        );
    }

    /**
     * Check the permission to make sure the current user has permission
     *
     * @return bool
     */
    public function canView($member = null)
    {
        return Permission::check("CMS_ACCESS_OneSky");
    }

    public function getList() {

        return ArrayList::create();
    }

    public function getEditForm($id = null, $fields = null) {

        $fields = FieldList::create();

        if( $this->project_info ){
            $fields->push(
                HeaderField::create($this->project_info->name)
            );
            if( $this->project_info->description ) {
                $fields->push(
                    LiteralField::create( 'onesky_project_description', sprintf("<p><i>%s</i></p>", $this->project_info->description) )
                );
            }
            $fields->push(LiteralField::create('onesky_string_count', "<p>Amount strings: {$this->project_info->string_count}</p>"));
            $fields->push(LiteralField::create('onesky_word_count', "<p>Amount words: {$this->project_info->word_count}</p>"));
        }

        $gridFieldConfig = GridFieldConfig::create()->addComponents(
            new GridFieldToolbarHeader(),
            new GridFieldSortableHeader(),
            new GridFieldDataColumns(),
            new GridFieldFooter(),
            new OneSkyGridfieldActions()
        );
        $gridField = new GridField('OneSkyStats',false, $this->api->getLanguages() , $gridFieldConfig);

        $columns = $gridField->getConfig()->getComponentByType('GridFieldDataColumns');
        $columns->setDisplayFields(array(
            'english_name' => _t('OneSkyAdmin.EnglishNameTitle', 'Title'),
            'code' => _t('OneSkyAdmin.CodeTitle', 'Code'),
            'region' => _t('OneSkyAdmin.RegionTitle', 'Region'),
            'is_base_language' => _t('OneSkyAdmin.IsBaseLanguageTitle', 'Is base language'),
            'translation_progress' => _t('OneSkyAdmin.TranlsationProgressTitle', 'Progress'),
            'local_filetime' => _t('OneSkyAdmin.LocalFiletimeTitle', 'Last Changed')
        ));


        $columns->setFieldFormatting(array(
            'code' => function($value, &$item) {
                return $value;
            },
            'is_base_language' => function($value, &$item) {
                return ($value?'✓':'');
            },
            'local_filetime' => function($value, &$item) {
                if( $value ) {
                    $date = new Date();
                    $date->setValue($value);
                    return $date->MemberDateTimeFormat();
                }
                return "--";
            }
        ));

        //$gridField->addExtraClass('all-reports-gridfield');
        $fields->push($gridField);


        $gridFieldConfig = GridFieldConfig::create()->addComponents(
            new GridFieldToolbarHeader(),
            new GridFieldSortableHeader(),
            new GridFieldDataColumns(),
            new GridFieldFooter()
        );

        $grid = new GridField('Logs', 'Logs', OneSkyLogEntry::get(), $gridFieldConfig);
        $fields->push($grid);

        $actions = new FieldList(

        );
        $form = Form::create(
            $this, "EditForm", $fields, $actions
        )->setHTMLID('Form_EditForm');
        //$form->setResponseNegotiator($this->getResponseNegotiator());
        //$form->addExtraClass('cms-edit-form cms-panel-padded center ' . $this->BaseCSSClasses());
        $form->loadDataFrom($this->request->getVars());

        $this->extend('updateEditForm', $form);

        return $form;
    }

    public function Content()
    {
        return $this->renderWith($this->getTemplatesWithSuffix('_Content'));
    }

}