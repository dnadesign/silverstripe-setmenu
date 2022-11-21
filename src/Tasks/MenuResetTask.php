<?php

namespace DNADesign\SetMenu\Tasks;

use DNADesign\SetMenu\Model\MenuSet;
use SilverStripe\ORM\DB;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Security\Permission;

class MenuResetTask extends BuildTask
{
    protected $title = '[Menu] Delete all menu sets';

    protected $description = 'Primarily for testing purposes, truncate the MenuSet and MenuItem tables.';

    private static $segment = 'menu-truncate-task';

    public function run($request)
    {
        if (!Permission::check('ADMIN')) {
            echo '<p>This task can only be run by an administrator</p>';
        }

        // Don't allow this task to be run on prod unless explicitly allowed
        if (Director::isLive() && !Config::inst()->get(MenuSet::class, 'prod_truncate_permission')) {
            echo 'This task is intended for UAT/DEV only';
            return;
        }

        // Give the user a confirmation prompt
        if (!$request->getVar('confirm')) {
            echo '<p>This task will delete all <strong>MenuSet</strong> and <strong>MenuItem</strong> objects.</p>';
            echo '<p>Run a /dev/build/ to repopulate the <strong>MenuSet</strong> table from config.</p>';
            echo '<p>Are you sure?</p>';
            echo '<a href="/dev/tasks/menu-truncate-task?confirm=1">Yes, I\'m sure</a>';
            exit;
        }

        DB::query('TRUNCATE Table MenuSets');
        DB::query('TRUNCATE Table MenuItems');

        exit('DONE');
    }
}
