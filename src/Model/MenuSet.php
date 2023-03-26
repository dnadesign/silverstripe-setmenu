<?php

namespace DNADesign\SetMenu\Model;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\TextField;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Subsites\Model\Subsite;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class MenuSet extends DataObject implements PermissionProvider
{
    private static $table_name = 'MenuSets';

    private static $db = [
        'Key' => 'Varchar(255)',
        'Name' => 'Varchar(255)',
        'Type' => 'Varchar(255)'
    ];

    private static $has_many = [
        'MenuItems' => MenuItem::class,
    ];

    private static $cascade_deletes = [
        'MenuItems'
    ];

    private static $summary_fields = [
        'Name',
        'Type'
    ];

    /**
     * Create the MenuSets as defined in configuration
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();

        $defaultSets = $this->getMenuSetsFromConfig();

        if (class_exists('SilverStripe\Subsites\Model\Subsite')) {
            $subsites = Subsite::all_sites();

            $subsites->each(function ($subsite) use ($defaultSets) {
                Subsite::changeSubsite($subsite->ID);

                $this->createMenuSetsFromConfig($defaultSets, $subsite->ID);
            });
        } else {
            $this->createMenuSetsFromConfig($defaultSets);
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
    public function getMenuSetsFromConfig()
    {
        return $this->config()->get('default_sets') ?: [];
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Key',
            'MenuItems'
        ]);

        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create('Type')
                    ->setDescription('<em>Determined by configuration</em>')
                    ->performDisabledTransformation(),
                new GridField(
                    'MenuItems',
                    'Menu Items',
                    $this->MenuItems(),
                    $config = GridFieldConfig_RecordEditor::create()
                )
            ]
        );

        $config->addComponent(new GridFieldOrderableRows('Sort'));

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
        return array_key_exists($this->Key, $this->getMenuSetsFromConfig());
    }
}
