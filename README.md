# WP Stack
A toolkit for creating professional [WordPress][wp] deployments.

[wp]: http://wordpress.org/

## Why
WordPress runs professional sites. You should have a professional deployment to go along with it. You should be using:

* Version control (like Git)
* A code deployment system (like Capistrano)
* A staging environment to test changes before they go live
* CDN for static assets

Additionally, you should be able to easily scale out to multiple web servers, if needed.

WP Stack is a toolkit that helps you do all that.

## WordPress Must-use Plugins

"Must-use" plugins aka `mu-plugins` are WordPress plugins that are dropped into the `{wp-content}/mu-plugins/` directory. They are autoloaded — no need to activate them. WP Stack comes with a number of these plugins for your use:

### CDN

`wp-stack-cdn.php`

This is a very simple CDN plugin. Simply configure the constant `WP_STACK_CDN_DOMAIN` in your `wp-config.php` or hook in and override the `wp_stack_cdn_domain` option. Provide a domain name only, like `static.example.com`. The plugin will look for static file URLs on your domain and repoint them to the CDN domain.

### Multisite Uploads

`wp-stack-ms-uploads.php`

The way WordPress Multisite serves uploads is not ideal. It streams them through a PHP file. Professional sites should not do this. This plugin allows one nginx rewrite rule to handle all uploads, eliminating the need for PHP streaming. It uses the following URL scheme for uploads: `{scheme}://{domain}/wp-files/{blog_id}/`. By inserting the `$blog_id`, one rewrite rule can make sure file requests go to the correct blog.

**Note:** You will need to implement the nginx rewrite rule for this to work.

## Capistrano

Capistrano is a deployment tool.

### Setup

1. [Install RubyGems][rubygems]
2. `sudo gem install capistrano capistrano-ext railsless-deploy`

[rubygems]: http://rubygems.org/pages/download
