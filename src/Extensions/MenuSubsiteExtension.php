<?php

namespace DNADesign\SetMenu\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\Subsites\State\SubsiteState;

/**
 * Add the Subsite relation, and include the ID on the record
 */
class MenuSubsiteExtension extends DataExtension
{
    private static $has_one = [
        'Subsite' => Subsite::class
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->push(HiddenField::create('SubsiteID', 'SubsiteID', SubsiteState::singleton()->getSubsiteId()));
    }
}
