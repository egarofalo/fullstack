# Project

Wordpress stack developed with Symfony packages and integrated with Composer.

## Description

Fullstack is a Wordpress stack with a greater security and a better structure folder. The Wordpress core files are located in a subfolder, called <code>public/wp</code>. The old and well-known <code>wp-content</code> folder is replaced by <code>public/content</code>. On the other hand, Composer is used to install PHP dependencies, Wordpress themes and plugins.
The configuration files are located outside the public folder for security reasons and contains the environment information like database credentials, proxy server settings, etc.

## Requirements

<ul>
    <li>php ^7.2</li>
    <li>Composer</li>
</ul>

## Installation with Github

Download the project from github or using the <code>git clone</code> command, and then run the <code>composer install</code> command inside the project root (location of the <code>composer.json</code> file).

## Installation with Composer

Install [Composer](https://getcomposer.org/download/) on your computer and once installed run in the cli <code>composer create-project codevelopers/fullstack</code>. If you want to install the project in a different folder, specify the name of the destination folder <code>composer create-project codevelopers/fullstack dest</code>.

## Install plugins

Search Wordpress plugins in the repository [WordPress Packagist](https://wpackagist.org/) and then run (in the cli) into the project root <code>composer require wpackagist-plugin/plugin-name</code> to install the plugin wich you choosed. You can also install the plugins from the WordPress dashboard.

## Install themes

Find Wordpress themes in the repository [WordPress Packagist](https://wpackagist.org/) and then run (in the same location as the <code>composer.json</code>, ie project root) <code>composer require wpackagist-theme/theme-name</code> to install themes. You can also install themes from the dashboard.

## Install PHP dependencies

Search PHP packages in [Packagist](https://packagist.org/) and then run (in the project root) <code>composer require vendor/package-name</code> to install PHP dependencies.

## WordPress configuration file

You have three configuration files:

-   <code>env.dev.php</code> for development and testing environment.
-   <code>env.local.php</code> for local development environment.
-   <code>env.dist.php</code> for production environment.

To set up the correct environment, you must set the constant <code>ENV</code> to <code>local</code>, <code>dev</code> or <code>dist</code> in the <code>env.php</code> file, as appropriate. Alternatively, you can set the environment variables in the webserver, since Fullstack get first the values from the <code>$\_ENV</code> array super global, and if not exists, use the <code>env.\*.php</code> file to get the settings.
Additionally, you must set the [Wordpress Authentication unique keys and salts](https://api.wordpress.org/secret-key/1.1/salt/) in the <code>config/salts.php</code> file.

## Console commands

Fullstack comes with a command line console (cli), with three commands:

-   <code>php cli database:create</code> to create the database in mysql server.
-   <code>php cli database:export</code> to dump the database structure and data in a self contained sql file.
-   <code>php cli database:import</code> to import the database structure and data in the last generated sql file.
-   <code>php cli theme:update</code> to change themes name and folder.

Remember that the cli command must be used only in the local or development environment, as it uses dev packages installed with composer.

## Deploy to production

The deploy to production server process don't require upload the <code>vendor</code> folder, as Fullstack only uses php dependencies installed with Composer in the local or development environment.

## Help us to skip working in this project

[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.paypal.com/donate?hosted_button_id=8PBTL2V25MMVW)
