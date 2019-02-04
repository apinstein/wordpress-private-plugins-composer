# Packagify

A script to conveniently package private Wordpress plugins into composer "artifact" repositories.

When using proper DevOps approaches with Wordpress, a common problem is how to manage private plugins.

Packagify makes it easy to maintain a folder with all of your private plugins and automatically creates proper composer packaging for each plugin.

## How it works

We leverage composer's `artifact` repository type, which allows you to add a single composer repository via local path that points to a directory of ZIP files.

The script identifies all plugins in the `src/` directory, creates a minimal composer.json, and packages it all up in the `dist/` folder. The original files are never touched.

## Usage

1. Copy any plugins you want into `src`/
1. Run `php packagify.php`
1. Copy/paste the outputted composer commands into your project.

## Example Output

```
$ php packagify.php
Found wordpress plugin: ark-core
Found wordpress plugin: fresh-framework
Creating /Users/apinstein/dev/wp-private-plugins-repo/src/ark-core/composer.json
Creating /Users/apinstein/dev/wp-private-plugins-repo/src/fresh-framework/composer.json

Composer Configuration:
composer config repositories.private-plugins artifact /Users/apinstein/dev/wp-private-plugins-repo/dist
composer require "ark-core/ark-core:1.0.0" "fresh-framework/fresh-framework:1.0.0"
```

## MISC

* Sometimes composer gets confused with the `artifact` if there is an error; try using `composer clearcache` and running things again.

## TODO

* I'm not satisfied with how paths work, or what's the best-practice for storing the artifacts from `dist/`.
    * MAYBE: `dist/` could be a private binary git repo? 
    * MAYBE: `dist/` could be customizable to dump it directly into the local php project? and then the zips could be checked in (or into a git submodule)
* I'm not satisfied with the trick of hard-coding all plugins to v1.0.0 -- this was they only way I could figure so far to make this automatable with no manual intervention.
* Convert this into a plugin command for wp-cli
