﻿# Adp Tools

A wordpress plugin with a lot of functions to make your life easier as a developer. This repo contains two folders, **AdpCore** the plugin, once installed you need to copy the other folder that is **AdpApp** to your active theme. This plugin adds laravel blade templating engine support so that all the examples below will be in blade format. I will refer to the AdpApp folder which you copied to the theme as app folder from now on. And there will be a menu Adp Tools from where you will be able to access all the tools. Below are the functions it provides.


# Conditionals

There is a folder named **conditional** in the app folder. You can place the blade files here and an admin interface will be available for managing different conditions against which you will be able to show or hide that template. You can also set the default visibility to Show or Hide. Accompanying each template is a config file with the same name in json format, it contains the configuration object for that template, there are two key elements in this config that is Name and Contents. Name is the label that will be displayed in the admin view and the Contents which is optional contains different key value pairs of content variables, If you have any content variable then these will be displayed in the admin view against which you will be able to change the content in different conditions and then can directly use those variables in template. Here is the screenshot of what the admin ui will be like.
![enter image description here](http://demos.appsdevpk.com/wp-content/uploads/2024/04/conditionals-scaled.jpg)

## Shortcodes

Then there is a folder named **shortcodes** in the app folder. When you put a blade file here, it will automatically create a shortcode with the file name, you just need to code your logic in this file. All shortcode arguments will be available as variables.

## Custom Post Types

If you want to create a custom post type just place a json configuration file in **posttypes** folder inside the app folder. You can register the post type with minimum configuration of singular and plural label, the name of the file will be used as post type name. You can pass extra arguments against extraArgs key in configuration object in json file (like supports parameter).

## Custom Taxonomies

You can register a custom taxonomy by placing a json configuration file in **taxonomies** folder inside the app folder in your theme. You should provide atleast three key value pairs in the configuration to register the taxonomy, and these are posttype,singular and plural.

## Custom Meta Boxes

This plugin uses cmb2 plugin (included in the plugin folder, you don't need to install it separately). To register a custom meta box, just place a configuration file in **metaboxes** folder inside app folder. For help about the configuration just visit cmb2 documentation. A sample configuration file is provided in the repo.

## Admin Pages

You can register custom admin pages too. Just place a json configuration file in **adminpages** folder inside the app folder. Here again the cmb2 plugin is being used, so for help just refer to the cmb2 docs.


# Libraries

There is a sub admin page in Adp Tools named **Libraries**, here you can search for different js or css libraries from cdnjs to use in your site. You have two option for search one Search Similar, it will search for the libraries matching the search keyword you entered. Then there is Search Exact option, this will search the exact library against the keyword entered, from here you can copy the cdn paths to clipboard or save those for later by clicking Save To My Library button.
![enter image description here](http://demos.appsdevpk.com/wp-content/uploads/2024/04/screenshot-woocommercetest.local-2024.04.28-23_21_57.png)