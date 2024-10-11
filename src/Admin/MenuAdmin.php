<?php

namespace DNADesign\SetMenu\Admin;

use SilverStripe\Admin\ModelAdmin;
use DNADesign\SetMenu\Model\MenuSet;
use SilverStripe\Subsites\State\SubsiteState;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class MenuAdmin extends ModelAdmin
{
    private static $menu_title = 'Menus';

    private static $url_segment = 'menus';

    private static $menu_icon_class = 'font-icon-menu';

    private static $managed_models = [
        MenuSet::class,
    ];

    public function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();

        // Remove import, export, and print buttons
        $config->removeComponentsByType([
            GridFieldImportButton::class,
            GridFieldExportButton::class,
            GridFieldPrintButton::class,
            GridFieldAddNewButton::class
        ]);

        return $config;
    }

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $gridFieldName = $this->sanitiseClassName($this->modelClass);
        $gridField = $form->Fields()->fieldByName($gridFieldName);
        $gridField->getConfig()->addComponent(new GridFieldOrderableRows());

        return $form;
    }

    /**
     * Filter by Subsite ID if the module is present
     *
     * @return DataList|void
     */
    public function getList()
    {
        $list = parent::getList();

        if (class_exists('SilverStripe\Subsites\State\SubsiteState') && $this->modelClass === MenuSet::class) {
            $list = $list->filter(
                [
                    'SubsiteID' => SubsiteState::singleton()->getSubsiteId()
                ]
            );
        }

        return $list;
    }

    /**
     * Functional equivalent of applying the SilverStripe\Subsites\Extensions\SubsiteMenuExtension extension.
     *
     * @return Boolean
     */
    public function subsiteCMSShowInMenu()
    {
        if (class_exists('SilverStripe\Subsites\Model\Subsite')) {
            return true;
        }

        return false;
    }
}
