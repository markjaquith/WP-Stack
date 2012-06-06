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

"Must-use" plugins aka `mu-plugins` are WordPress plugins that are dropped into the `{WordPress content dir}/mu-plugins/` directory. They are autoloaded — no need to activate them. WP Stack comes with a number of these plugins for your use:

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

1. Create a `deploy` user on your system.
2. Create an SSH key for `deploy`, make sure it can SSH to all of your web servers, and make sure it can pull down your site repo code.
3. [Install RubyGems][rubygems]
4. `sudo gem install capistrano capistrano-ext railsless-deploy`
5. Switch to the deploy user: `su deploy`
6. Check out WP Stack somewhere on your server
7. Customize and rename `config/SAMPLE.{config|production|staging}.rb`
9. Make sure your deploy path exists and is owned by the deploy user.
9. Run `cap deploy:setup` to setup the initial `shared` and `releases` directories.

[rubygems]: http://rubygems.org/pages/download

### Deploying

1. Switch to the deploy user: `su deploy`
2. Run `cap production deploy` (to deploy to staging, use `cap staging:deploy`)

### Rolling Back

1. Switch to the deploy user: `su deploy`
2. Run `cap deploy:rollback`

### About Stages

There are two "stages": production and staging. These can be completely different servers, or different paths on the same set of servers.