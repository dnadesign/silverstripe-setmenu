<?php

namespace DNADesign\SetMenu\Model;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\Security\Permission;
use SilverStripe\LinkField\Form\MultiLinkField;
use SilverStripe\LinkField\Models\ExternalLink;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\LinkField\Models\SiteTreeLink;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\Versioned\Versioned;

class MenuSet extends DataObject implements PermissionProvider
{
    private static array $extensions = [
        Versioned::class,
    ];

    private static $table_name = 'MenuSets';

    private static $db = [
        'Key' => 'Varchar(255)',
        'Name' => 'Varchar(255)',
        'Type' => 'Varchar(255)',
        'Sort' => 'Int',
    ];

    private static $has_many = [
        'MenuItems' => Link::class . '.Owner',
    ];

    private static array $owns = [
        'MenuItems'
    ];

    private static $cascade_deletes = [
        'MenuItems'
    ];

    private static $summary_fields = [
        'Name',
        'Type'
    ];

    private static $default_sort = 'Sort';

    /**
     * Create the MenuSets as defined in configuration
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        $defaultSets = $this->getMenuSetsFromConfig('default_sets');

        // Create menu sets for the main site (SubsiteID = 0)
        $this->createMenuSetsFromConfig($defaultSets);

        // Check for subsites
        if (class_exists('SilverStripe\Subsites\Model\Subsite')) {
            $subsites = Subsite::all_sites(false);

            $subsites->each(function ($subsite) {
                Subsite::changeSubsite($subsite->ID);

                // Retrieve menu sets based on subsite's theme name
                if (!$subsite->Theme) {
                    return;
                }

                $subsiteSets = $this->getMenuSetsFromConfig($subsite->Theme);
                if ($subsiteSets) {
                    $this->createMenuSetsFromConfig($subsiteSets, $subsite->ID);
                }
            });
        }
    }

    /**
     * Create the MenuSet objects from configuration (for the given subsite, if a $subsiteID is supplied)
     *
     * @param String[] $defaultSets
     * @param Int|null $subsiteID
     * @return void
     */
    public function createMenuSetsFromConfig($defaultSets, $subsiteID = null)
    {
        $filter = [];
        if ($subsiteID) {
            $filter['SubsiteID'] =  $subsiteID;
        }

        foreach ($defaultSets as $name => $type) {
            $filter['Key'] = $name;

            $existingRecord = MenuSet::get()
                ->filter($filter)
                ->first();

            if (!$existingRecord) {
                $set = new MenuSet();
                $set->Key = $name;
                $set->Name = $name;
                $set->Type = $type;

                if ($subsiteID) {
                    $set->SubsiteID = $subsiteID;
                }

                $set->write();

                $addendum = $subsiteID ? ', for Subsite #$subsiteID"' : '';
                DB::alteration_message("MenuSet '$name' created, of type '$type'$addendum", 'created');
            }
        }
    }

    /**
     * Get the MenuSet names and types, configured in .yml
     *
     * @return String[]
     */
    public function getMenuSetsFromConfig($setType)
    {
        return $this->config()->get($setType) ?: [];
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Key',
            'MenuItems',
            'Sort'
        ]);

        $menuItemsField = MultiLinkField::create('MenuItems')
            ->setAllowedTypes([SiteTreeLink::class, ExternalLink::class]);

        if (!Permission::check('MANAGE_MENUS')) {
            $menuItemsField->performReadonlyTransformation(true);
        }

        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create('Type')
                    ->setDescription('<em>Determined by configuration</em>')
                    ->performDisabledTransformation(),
                $menuItemsField
            ]
        );

        return $fields;
    }

    // Permissions: MenuItem reuses the permission code defined here

    public function providePermissions()
    {
        return [
            'MANAGE_MENUS' => 'Manage Menus'
        ];
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canCreate($member = null, $context = [])
    {
        if (Permission::check('MANAGE_MENUS')) {
            return true;
        }

        return parent::canCreate($member = null, $context = []);
    }

    public function canEdit($member = null)
    {
        if (Permission::check('MANAGE_MENUS')) {
            return true;
        }

        return parent::canEdit($member = null);
    }

    public function canDelete($member = null)
    {
        // MenuSets are defined in configuration, so cannot be deleted until removed from there
        if ($this->isDefaultSet()) {
            return false;
        }

        if (Permission::check('MANAGE_MENUS')) {
            return true;
        }

        return parent::canDelete($member = null);
    }

    /**
     * Check if this menu set appears in the default sets config
     * @return bool
     */
    public function isDefaultSet()
    {
        return array_key_exists($this->Key, $this->getMenuSetsFromConfig('default_sets'));
    }
}
