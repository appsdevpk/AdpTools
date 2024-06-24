# Adp Tools

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

This plugin uses cmb2 plugin (included in the plugin folder, you don't need to install it separately). To register a custom meta box, just place a configuration file in **metaboxes** folder inside app folder. For help about the configuration just visit cmb2 documentation. A sample configuration file is provided in the repo. There is an extra parameter in field options named showinadmin, if you want to show a specific meta field in admin posts list, just set this parameter to true. You can conditionally show the meta box by configuring showon in meta configuration, currently only two conditions are supported, you can either configure ids parameter to show this meta box on posts with selected ids or you can configure templates list to show this meta box on specific template. For further help take a look at the examples.
You can show custom meta boxes at front also, you just need a short code for that and that is, [adpMetaForm metaboxid="pageMeta" objectid="21"], here metaboxid is the id of metabox you want to show at front and the objectid is the post, page or custom post type id where you want to store that meta.

## Admin Pages

You can register custom admin pages too. Just place a json configuration file in **adminpages** folder inside the app folder. Here again the cmb2 plugin is being used, so for help just refer to the cmb2 docs.


# Libraries

There is a sub admin page in Adp Tools named **Libraries**, here you can search for different js or css libraries from cdnjs to use in your site. You have two option for search one Search Similar, it will search for the libraries matching the search keyword you entered. Then there is Search Exact option, this will search the exact library against the keyword entered, from here you can copy the cdn paths to clipboard or save those for later by clicking Save To My Library button.
![enter image description here](http://demos.appsdevpk.com/wp-content/uploads/2024/04/screenshot-woocommercetest.local-2024.04.28-23_21_57.png)


## Server Components

Server components or custom html components can be used as html tags anywhere in your wordpress site, realm library is being used to render these components, to create a component, create a blade template file in servercomponents folder inside the app folder. You should follow the naming conventions for the components (should be atleast two words separated by -). Each component should have at least one template file, then there is the config file for the component in json format, this file contains the list of component attributes and states (samples are provided in the app folder), and if you want to handle or listen to the events then you can define those inside a directory with the same name as the component and the suffix -flows. For further details, look at the realm framework documentation and the attached samples.

## Conditional Css And JS

There are a lot of ways to improve your wordpress site performance, one is to conditionally load css and js where needed, now you can do it easily with the help of this plugin. Just place your css and js files in their repsective folders inside app folder. The rule is to have your file name in two or three parts separated with -. First part will always be either header or footer, this will tell the plugin where you want to place this css or js. Second part can have any one option from template, single and page, this will tell the plugin where to include this script, in a custom template, in a post or in any page. If you have the third part in the name which is optional, then this will decide in which specific template, post or page, you want to include this css or js. Samples are included in app folder of the repo

## Live Components

Same as server components, but keeps updating (without page reload) by use of SSE (Server Sent Events), you dont need to write any SSE login, just define a component in the livecomponents folder inside the app folder, all the server components rule apply. But the name should be different from server components, if you define a component hello-world in server components then you cannot define a live component with same name.

## Admin Dashboard Widgets

Registering custom dashboard widgets cannot be more easy than placing just a template and config file inside admindashwidgets inside the app folder, just place a blade template file and a json config file with the same name inside admindashwidgets folder inside app folder and the widget will appear on your dashboard.

## Custom Help Tabs

To show a custom help tab in wordpress help menu, just place a blade template and a json config file inside helptabs folder in app folder and the help tab will show in wordpress help menu. Example is provided in app folder.

## Custom Widgets and Widget Areas

You need to place two files to register a widget inside widgets folder in app folder. One is the blade template to render the output of the widget and the other is the json config file, the keys in config files are self explanatory (take a look at the provided example). You can register a custom widget area just by placing a json config file in widgetareas folder inside the app folder. To include that area in your theme or some blade template just use the function adpEmbedWidgetArea('widget-area-id')

## Custom pages

If you want to register a custom url and execute your code when wordpress opens that url, you just need to place repsective template files in the pages folder inside the app folder. Like if you want to execute some code when someone opens yoursite.com/testpage/123, then you will create a folder inside pages folder named testpage and then will create a template file in that folder named 123.blade.php, and the code of that file will execute when someone will hit that url. You can use all wordpress functionality their. If you want to change the title of the page, just define a variable in your file with the name $pageTitle = "Test 1234 Page"; and assign it the value you want to show in the title.

## Annotations

One thing is very important when you are developing a wordpress site, that is the client's feedback, Now it will become a breeze to get the client feed back on any part of the site with the help of annotations. Only logged in users can annotate any part of the web page in two ways, to annotate a textual area, you just need to select some text and the annotation icon will appear, clicking that icon will open a popup comment area where you or your client can add comments, if you want to annoate images, just give the image annotate class and any logged in user can add comments on any part of that image by just creating a selection with the mouse and then adding comments in the popup which appears after.

## Tools

When you click on Adp Tools in wp admin after activating the plugin, you will see a list of tools which can help you in a lot small tasks, just explore those and find your favorite ones, there are two buttons with each tool (Open Here and Open Separate). Some tools will open inside your wordpress admin by clicking Open Here (using IFrame), but if some tool does not open this way, then click on Open Separate and it will open in new tab.


## Wizards

Here you can visually create configuration and template files for all the tools available in the plugin. No need to copy app folder inside your theme, the wizards will take care of it.

![enter image description here](http://demos.appsdevpk.com/wp-content/uploads/2024/06/screenshot-woocommercetest.local-2024.06.23-22_55_09.jpg)