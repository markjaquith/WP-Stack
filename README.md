# Stage WP

A toolkit for creating professional [WordPress][wp] deployments, forked from Mark Jaquith's [WP-Stack][wpstack]. It uses Capistrano as a code deployment system, and offers a complete set of tasks for you to scale your WordPress project to different servers, both from local and remote environments.

**Current stable version**: [1.1.1](https://github.com/andrezrv/stage-wp/tree/1.1.1)

## Why do you need this?

Because WordPress runs professional sites, and you should have a professional deployment tool to go along with it. Stage WP is a toolkit that helps you manage that.

## Getting Started

### Assumptions about your workflow

* You mantain your code under a version control system (like [Git](http://git-scm.com/)). Stage WP works specially well with [WordPress Barebones][wpbarebones].
* You develop locally before deploying your code.
* Ideally, you have a staging environment to test changes before they go to production.
* Ideally, you use a CDN for static assets.
* You deploy your code from a local environment.

Additionally, you should be able to easily scale out to multiple web servers, if needed.

### Capistrano

Capistrano is a code deployment tool. When you have code that is ready to go "live", this is what we you're gonna use to do it.

### Setting Up Your Local System

1. Create a `deploy` user on each of your remote web servers (staging and production). This will be the user that is going to perform all the deployment actions on your remote systems.
	* `addgroup deploy`
	* `adduser --system --shell /bin/bash --ingroup deploy --disabled-password --home /home/deploy deploy`
	If you want a user with another name, it must be the same as the one you set for `:user` within `/config/config.rb`.
2. Make sure that `deploy` can pull down your site repo code with `git clone $your_repo` from all your servers.
3. Create (if you don't have one) and upload an SSH key to the list of authorized keys for the `deploy` user in each of your web servers.
	* `ssh-keygen`
	* `cat ~/.ssh/id_rsa.pub | ssh deploy@$your_server "cat - >> ~/.ssh/authorized_keys"`
3. [Install Ruby][ruby].
	* `sudo apt-get install ruby`
4. Install Capistrano and extras.
	* `sudo gem install capistrano -v 2.15.5`
	* `sudo gem install capistrano-ext railsless-deploy`

If you're using [VVV][vvv], steps 3 and 4 will run automatically upon `vagrant up --provision`.

### Setting Up Stage WP

1. Check out Stage WP somewhere on your local system.
	* `git clone git@github.com:andrezrv/Stage-WP.git /srv/www/website/stage-wp`
2. Customize and rename `/config/{config|local|production|staging}-sample.rb`
3. Make sure your remote `:production_deploy_to` and `:staging_deploy_to` paths exist and are owned by the deploy user.
	* `chown -R deploy:deploy /path/to/your/deployment`
4. Go to your Stage WP directory and run `cap deploy:setup` to setup the initial `shared` and `releases` directories.

### Deploying

1. `cd` to your Stage WP directory.
2. Run `cap production deploy` (to deploy to staging, use `cap staging deploy`).

### Rolling Back

If something went wrong with your deployment, you can always go back to the previous version:

1. `cd` to your Stage WP directory.
2. Run `cap deploy:rollback`

### About Stages

There are three "stages": local, production and staging. The first one is your development environment. The other two should be remote systems, and can be completely different servers, or different paths on the same set of servers.

Stage WP, unlike WP-Stack, is meant to be used from a development environment, performing actions on remote servers from a local system.

You can still use it in a remote system, like your production or staging servers, but this comes with a trick: if Stage WP is running on staging or production, your local stage will be the same as your remote one.

### Database Tasks

With Stage WP, you can create your database, backup, restore and move mysqldumps from the local stage to production and staging with the help of the following commands:

* `cap {staging|production} db:init`: Initializes the WordPress database in a remote stage.
* `cap {local|staging} db:sync`: Syncs the local or staging database and uploads from production.
* `cap {local|staging|production} db:backup`: Performs a mysqldump of your WordPress database and saves it within the selected stage.
* `cap {local|staging|production} db:restore`: Restores the WordPress database from a backup stored in the selected stage.
* `cap {staging|production} db:pull`: Downloads a database backup from the selected stage and stores it locally.
* `cap {staging|production} db:push`: Uploads a local database backup to the selected stage.

### Managing Shared Files

You can synchronize your shared files both from local to remote stages and from remote stages to local with the help of the following commands:

* `cap {staging|production} shared:pull`: Synchronizes your local shared files from the selected stage.
* `cap {staging|production} shared:push`: Synchronizes your selected stage shared files from local.

### Full Synchronization

You can synchronize both database and shared files from production to staging or local using `cap {local|staging} db:sync`.

### Some Other Useful Tasks

If you're using [WordPress Barebones][wpbarebones], you can perform remote backup and maintenance tasks with the following commands:

* `cap {staging|production} util:backup_application`: Zips and saves all application files to `:application_backup_path`.
* `cap {staging|production} util:backup_db`: Performs a mysqldump of the WordPress database and saves it to `:wpdb[stage][:backups_dir]`.
* `cap {staging|production} util:switch`: Switches the state of your site from active to maintenance and vice versa.
* `cap {staging|production} util:full_backup`: Performs a full backup (files and database).

Yet more useful tasks:

* `cap {local|staging|production} nginx:restart`: Restarts NGINX.
* `cap {local|staging|production} phpfpm:restart`: Restarts PHP-FPM.
* `cap {local|staging|production} memcached:restart`: Restarts Memcached.
* `cap {local|staging|production} memcached:upload`: Updates the pool of Memcached servers.

## WordPress Must Use Plugins

Must Use plugins are WordPress plugins that usually live into the `mu-plugins` directory. They are autoloaded — no need to activate them. Stage WP comes with a number of these plugins for your use:

### Stage WP CDN

This is a very simple CDN plugin. Simply configure the constant `WP_STACK_CDN_DOMAIN` in your `wp-config.php` or hook in and override the `wp_stack_cdn_domain` option. Provide a domain name only, like `static.example.com`. The plugin will look for static file URLs on your domain and repoint them to the CDN domain. Original credits go to [Mark Jaquith][mj].

### Stage WP Multisite Uploads

The way WordPress Multisite serves uploads is not ideal. It streams them through a PHP file. Professional sites should not do this. This plugin allows one NGINX rewrite rule to handle all uploads, eliminating the need for PHP streaming. It uses the following URL scheme for uploads: `{scheme}://{domain}/wp-files/{blog_id}/`. By inserting the `$blog_id`, one rewrite rule can make sure file requests go to the correct blog. Original credits go to [Mark Jaquith][mj].

**Note:** You will need to implement this NGINX rewrite rule for this to work:

`rewrite ^/wp-files/([0-9]+)/(.*)$ /wp-content/blogs.dir/$1/files/$2;`

### Stage WP Manual DB Upgrades

Normally, WordPress redirects `/wp-admin/` requests to the WordPress database upgrade screen. On large sites, or sites with a lot of active authors, this may not be desired. This drop-in prevents the automatic redirect and instead lets you manually go to `/wp-admin/upgrade.php` to upgrade a site.

## Assumptions Made About WordPress

If you're not using [WordPress Barebones][wpbarebones], you should be aware of these assumptions:

1. Your application path is defined in `:application_path`, inside `/config/config.rb`
2. Your `wp-config.php` file must exist in the root of your application path.
3. Stage WP replaces the following "stubs":
	* `%%DB_NAME%%` — Database name.
	* `%%DB_HOST%%` — Database host.
	* `%%DB_USER%%` — Database username.
	* `%%DB_PASSWORD%%` — Database password.
	* `%%WP_STAGE%%` – will be `production` or `staging` after deploy.
4. Stage WP uses the constants `WP_STAGE` (which should be set to `'%%WP_STAGE%%'`) and `STAGING_DOMAIN`, which should be set to the domain you want to use for staging (something like `staging.example.com`).

## Extending

You have the following options to add your own functionlity without altering the flow of the repository:

* Add your custom configurations to `/config/config.rb`, `/config/local-config.rb`, `/config/production-config.rb` and `/config/staging-config.rb`, since they won't be tracked by Git.
* Rename `/Customfile-sample` to `/Customfile`, if you want to hook `/Capfile`.
* Rename `/lib/custom-tasks-sample.rb` to `/lib/custom-tasks.rb`, if you want to add extra tasks.
* Rename `/lib/custom-hooks-sample.rb` to `/lib/custom-hooks.rb`, if you want to add extra hooks.
* Put anything you want into `/custom`. Git won't track those files.

### Contributing
If you feel like you want to help this project by adding something you think useful, you can make your pull request against the master branch :)

[wp]: http://wordpress.org/
[wpstack]: http://github.com/markjaquith/WP-Stack
[wpbarebones]: http://github.com/andrezrv/wordpress-barebones/
[mj]: http://github.com/markjaquith
[cap]: https://github.com/capistrano/capistrano
[ruby]: https://www.ruby-lang.org/en/installation/#ruby-install
[vvv]: https://github.com/Varying-Vagrant-Vagrants/VVV
