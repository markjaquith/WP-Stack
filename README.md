# WP Stack
A toolkit for creating professional [WordPress][wp] deployments.

Commissioned by [Knewton](http://www.knewton.com/).

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

**Note:** You will need to implement this Nginx rewrite rule for this to work:

`rewrite ^/wp-files/([0-9]+)/(.*)$ /wp-content/blogs.dir/$1/files/$2;`

### Manual DB Upgrades

Normally, WordPress redirects `/wp-admin/` requests to the WordPress database upgrade screen. On large sites, or sites with a lot of active authors, this may not be desired. This drop-in prevents the automatic redirect and instead lets you manually go to `/wp-admin/upgrade.php` to upgrade a site.

## Capistrano

Capistrano is a code deployment tool. When you have code that is ready to go "live", this is what does it.

### Setup

1. Create a `deploy` user on your system (Ubuntu: `addgroup deploy; adduser --system --shell /bin/bash --ingroup deploy --disabled-password --home /home/deploy deploy
`).
2. Create an SSH key for `deploy`, make sure it can SSH to all of your web servers, and make sure it can pull down your site repo code.
	* Switch to the deploy user (`su deploy`).
	* `ssh-keygen`
	* `cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys`
	* Add the contents of `~/.ssh/id_rsa.pub` to `~/.ssh/authorized_keys` on every server you're deploying to.
3. [Install RubyGems][rubygems].
4. Install Capistrano and friends: `sudo gem install capistrano capistrano-ext railsless-deploy`
5. Switch to the deploy user (`su deploy`) and check out WP Stack somewhere on your server: `git clone git@github.com:markjaquith/WP-Stack.git ~/deploy`
6. Customize and rename `config/SAMPLE.{config|production|staging}.rb`
7. Make sure your `:deploy_to` path exists and is owned by the deploy user: `chown -R deploy:deploy /path/to/your/deployment`
8. Run `cap deploy:setup` (from your WP Stack directory) to setup the initial `shared` and `releases` directories.

[rubygems]: http://rubygems.org/pages/download

### Deploying

1. Switch to the deploy user: `su deploy`
2. `cd` to the WP Stack directory.
3. Run `cap production deploy` (to deploy to staging, use `cap staging deploy`)

### Rolling Back

1. Switch to the deploy user: `su deploy`
2. `cd` to the WP Stack directory.
3. Run `cap deploy:rollback`

### About Stages

There are two "stages": production and staging. These can be completely different servers, or different paths on the same set of servers.

To sync from production to staging (DB and files), run `cap staging db:sync`.

## Assumptions made about WordPress

If you're not using [WordPress Skeleton](https://github.com/markjaquith/WordPress-Skeleton), you should be aware of these assumptions:

1. Your `wp-config.php` file exists in your web root. So put it there.
2. WP Stack replaces the following "stubs":
	* `%%DB_NAME%%` — Database name.
	* `%%DB_HOST%%` — Database host.
	* `%%DB_USER%%` — Database username.
	* `%%DB_PASSWORD%%` — Database password.
	* `%%WP_STAGE%%` – will be `production` or `staging` after deploy.
3. WP Stack uses the constants `WP_STAGE` (which should be set to `'%%WP_STAGE%%'`) and `STAGING_DOMAIN`, which should be set to the domain you want to use for staging (something like `staging.example.com`).