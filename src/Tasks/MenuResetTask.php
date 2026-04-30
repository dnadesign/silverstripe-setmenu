<?php

namespace DNADesign\SetMenu\Tasks;

use SilverStripe\ORM\DB;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Control\Director;
use DNADesign\SetMenu\Model\MenuSet;
use SilverStripe\Core\Config\Config;
use SilverStripe\Security\Permission;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use SilverStripe\PolyExecution\PolyOutput;

class MenuResetTask extends BuildTask
{
    protected string $title = '[Menu] Delete all menu sets';

    protected static string $description = 'Primarily for testing purposes, truncate the MenuSet and MenuItem tables.';

    private static $segment = 'menu-truncate-task';

    public function getOptions(): array
    {
        return [
            new InputOption(
                'confirm',
                null,
                InputOption::VALUE_REQUIRED,
                'Confirm truncating all MenuSet and MenuItem records'
            ),
        ];
    }

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        if (!Director::is_cli() && !Permission::check('ADMIN')) {
            $output->writeln('This task can only be run by an administrator');
            return Command::FAILURE;
        }

        // Don't allow this task to be run on prod unless explicitly allowed
        if (Director::isLive() && !Config::inst()->get(MenuSet::class, 'prod_truncate_permission')) {
            $output->writeln('This task is intended for UAT/DEV only');
            return Command::FAILURE;
        }

        // Give the user a confirmation prompt
        $confirm = $input->getOption('confirm');
        $isConfirmed = in_array(strtolower((string) $confirm), ['1', 'true', 'yes'], true);

        if (!$isConfirmed) {
            $output->writeln('This task will delete all MenuSet and MenuItem objects.');
            $output->writeln('Run /dev/build/ afterwards to repopulate MenuSet from config.');
            $output->writeln('Re-run with --confirm (CLI) or ?confirm=1 (browser) to proceed.');
            $output->writeln('Example: vendor/bin/sake dev/tasks/menu-truncate-task --confirm=1');
            return Command::SUCCESS;
        }

        DB::query('TRUNCATE Table MenuSets');
        DB::query('TRUNCATE Table MenuItems');

        return Command::SUCCESS;
    }
}
