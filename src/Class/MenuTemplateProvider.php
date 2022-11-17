<?php

namespace DNADesign\SetMenu\Class;

use DNADesign\SetMenu\Model\MenuSet;
use SilverStripe\Subsites\State\SubsiteState;
use SilverStripe\View\TemplateGlobalProvider;

class MenuTemplateProvider implements TemplateGlobalProvider
{
    /**
     * Returns an array of strings of the method names of methods on the
     * call that should be exposed as global variables in the templates.
     *
     * @return array
     */
    public static function get_template_global_variables()
    {
        return [
            'MenuType' => 'MenuType'
        ];
    }

    /**
     * Return the MenuSets matching the given type
     *
     * @param String $name
     * @return DataList|null
     */
    public static function MenuType($type)
    {
        $filter = ['Type' => $type];

        if (class_exists('SilverStripe\Subsites\State\SubsiteState')) {
            $filter['SubsiteID'] = SubsiteState::singleton()->getSubsiteId();
        }

        $menuSets = MenuSet::get()->filter($filter);

        return $menuSets->count() > 0 ? $menuSets : null;
    }
}
