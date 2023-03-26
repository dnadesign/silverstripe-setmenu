<?php

namespace DNADesign\SetMenu\Model;

use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\TreeDropdownField;

class MenuItem extends DataObject
{
    private static $table_name = 'MenuItems';

    private static $db = [
        'Title' => 'Varchar(255)',
        'Link' => 'Text',
        'Sort' => 'Int',
        'NewWindow' => 'Boolean'
    ];

    private static $has_one = [
        'Page' => SiteTree::class,
        'MenuSet' => MenuSet::class
    ];

    private static $summary_fields = [
        'Title' => 'Name',
        'Page.Title' => 'Page Title',
        'Link' => 'Link',
        'NewWindow.Nice' => 'Opens in a new tab?',
    ];

    private static $default_sort = 'Sort ASC';

    public function getCMSFields()
    {
        $fields = FieldList::create(TabSet::create('Root'));

        $fields->addFieldsToTab(
            'Root.main',
            [
                TextField::create('Title', 'Title')->setDescription('If left blank, will default to the selected page\'s name.'),
                TreeDropdownField::create('PageID', 'Page on this site', SiteTree::class)
                    ->setDescription('Leave blank if you wish to manually specify the URL below.'),
                TextField::create('Link', 'URL')
                    ->setDescription('Enter a full URL to link to another website.'),
                CheckboxField::create('NewWindow', 'Open in a new window?'),
            ]
        );

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }

    /**
     * Checks to see if a page has been chosen and if so sets Link to null
     * This means that used in conjunction with the __get method, calling $menuItem->Link won't
     * return the Link field of this MenuItem but rather call the Link method on the associated Page
     *
     * @return void
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if ($this->PageID != 0) {
            $this->Link = null;
        }
    }

    /**
     * Attempts to return the $field from this MenuItem
     * If $field is not found or it is not set then attempts to return a similar field on the associated Page (if there is one)
     *
     * @param string $field
     * @return mixed
     */
    public function __get($field)
    {
        $default = parent::__get($field);

        if ($default || $field === 'ID') {
            return $default;
        } else {
            $page = $this->Page();

            if ($page instanceof DataObject) {
                if ($page->hasMethod($field)) {
                    return $page->$field();
                } else {
                    return $page->$field;
                }
            }
        }
    }

    // Permissions: This object reuses the permission code provided by MenuSet

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
        if (Permission::check('MANAGE_MENUS')) {
            return true;
        }

        return parent::canDelete($member = null);
    }
}
