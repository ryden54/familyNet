familyNet
=========

Family social site, for tchat, pictures & coordinates database.

Setup
=====

Create a config/site/ folder to store all the config relative to your website (at least the sql config).

In the config/site/env.conf.php, you can define/override the environment level (DEV, PROD...) based on your conditions (REOTE_ADDRESS...)

You must create at least one family member in your database ("personnes" table) and set it's id in the config/site/global.conf.php file in the $config['ROOT_FAMILY_MEMBER_ID'] variable
