# Silverstripe Set Menu
A Silverstripe module to define menu sets for different purposes.

## Credit
This module was inspired by the following modules, but modified for our tastes:
- [heyday/silverstripe-menumanager](https://github.com/heyday/silverstripe-menumanager)
-  [guttmann/silverstripe-menumanager-subsites](https://github.com/guttmann/silverstripe-menumanager-subsites)

## Requirements
This module is baked for Silverstripe 4 and Php 8

## Installation

This module is not yet published on packagist, so install the module by including it in your **composer.json** with a repository definition.

```
    "require": {
        "dnadesign/silverstripe-setmenu": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/dnadesign/silverstripe-setmenu.git"
        }
    ],
```
## Configuration
Define the **MenuSet**s and the group they belong to in .yml configuration by setting `default_sets`. 

- _Key_: Initial name of the **MenuSet**.
- _Value_: Group the **MenuSet** belongs to.

```
DNADesign\SetMenu\Model\MenuSet:
  default_sets:
    Main nav: Main
    Main footer: Footer
    Secondary footer: Footer
    Auxilliary Footer: Footer
```

On `/dev/build` they will be created automatically.
## Usage

Use the `$MenuType('Name')` template function to retrieve the MenuSet DataList:
```
<% loop $MenuType('Footer') %>
    <p>$Name</p>
    <% loop $MenuItems %>
        <a href="{$Link}" class="{$LinkingMode}"
            <% if $NewWindow %>
                target="_blank"
                rel="noopener"
            <% end_if %>
        >
            {$Title}
        </a>
    <% end_loop %>
<% end_loop %>
```
## Permissions
In `/admin/security/`, add the _Access to 'Menus' section_ and _Manage Menus_ permissions to a group to view and edit the menu objects.
## Subsites
This module is configured to operate with or without the Subsites module.

## Tasks
The module comes with a task to truncate the **MenuSet**s, which may be helpful for development purposes. This can be enabled on PROD if required.

```
DNADesign\SetMenu\Model\MenuSet:
  prod_truncate_permission: true
```

## TODO
- Currently, when using Subsites, the same **MenuSet**s will be created for each subsite.
  - Update the configuration documentation and `requireDefaultRecords()` functionality in `MenuSetSubsiteExtension` to allow different configuration per Subsite.
