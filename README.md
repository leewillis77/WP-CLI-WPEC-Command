WP-CLI WP e-Commerce commands
=============================

**This is a Work In Progress - Do not expect it to work or do anything interesting**

This is a package that implements the various commands for [WP-CLI](http://wp-cli.org) for working with, developing for, or testing the [WP e-Commerce](http://wordpress.org/extend/wp-e-commerce) plugin.

### Requirements

* PHP 5.4 or newer
* WordPress 3.9 or newer
* WP e-Commerce 3.8 or newer

### Installation

* Head to ~/.wp-cli/commands
* Clone this repo
* Edit (or create) ~/.wp-cli/config.yml and ensure that it requires the new commands

```yaml
require:
  - commands/WP-CLI-WPEC-Command/commands.php
```

### Usage

Some example usage:

```bash
# Output all categories as a table
$ wp wpec-category list

# Output all categories as a CSV file
$ wp wpec-category list --format=csv

# Get category with ID 7
$ wp wpec-category get 7

# Get category with ID 7, formatted as JSON
$ wp wpec-category get example-category --format=json
```
