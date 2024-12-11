<?php

namespace DNADesign\SetMenu\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\Subsites\State\SubsiteState;

/**
 * Add the Subsite relation, and include the ID on the record
 *
 * @extends Extension<(Subsite & static)>
 */
class MenuSubsiteExtension extends Extension
{
    private static $has_one = [
        'Subsite' => Subsite::class
    ];

    protected function updateCMSFields(FieldList $fields): void
    {
        $fields->push(HiddenField::create('SubsiteID', 'SubsiteID', SubsiteState::singleton()->getSubsiteId()));
    }
}
