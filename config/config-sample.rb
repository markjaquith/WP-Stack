# Stage WP configuration file.
#
# This is a sample config file. It manages settings for your remote repository,
# your default domains and your database values. Copy it to config.rb and
# customize it to your own needs.


# Application settings.

# // The name of your application. It can be any string.
set :application, "Local WordPress Installation"

# // A shortname to identify your application.
set :application_id, "wordpress"

# Where is your wp-config.php file located within #{release_path}?
# // Your release path points to the newer copy of your repository, so you must
# // specify where your wp-config.php file is located within your remore repo.
# // WordPress Barebones puts the file in the "app" subfolder, so it needs to
# // be "#{release_path}/app", but if you are using WordPress-Skeleton, just
# // "#{release_path}" should do the trick and this value should be left empty.
set :application_path, "/app"

# Repository settings

# // Location of your remote repository.
set :repository,  "git://github.com/username/website.git"

# // Your preferred method for source control. Supports :accurev, :bzr, :cvs,
# // :darcs, :git, :mercurial, :perforce, :subversion or :none.
set :scm, :git

# // Using Git submodules?
set :git_enable_submodules, 1

# Default deploy directories.

# // :deploy_to in production.rb will use this value as default
set :production_deploy_to, "/srv/www/website/application"

# // :deploy_to in staging.rb will use this value as default
set :staging_deploy_to, "/srv/www/website/application"

# Default domains.

# // :deploy_to in production.rb will use this value as default
set :production_domain, "www.website.com"

# // :deploy_to in staging.rb will use this value as default
set :staging_domain, "staging.website.com"

# Local stage default settings.

# // Path to your local shared folder.
set :local_shared_folder, "/srv/www/website/application/shared"

# WordPress database settings.
#
# Set the values for host, user, pass, and name for production, staging and
# local stages. You can also specify a backup directory where your mysqldumps
# should be saved. Note that placeholders like %%DB_NAME%% and %%DB_PASSWORD%%
# in your wp-config.php file will be automatically replaced when `cap deploy` is
# executed, so you only have to configure these values here.
set :wpdb do
	{
		:production => {
			:host        => "localhost",
			:user        => "root",
			:password    => "root",
			:name        => "production_db",
			:backups_dir => "/srv/www/website/backups/dumps",
			:max_backups => "3",
			:dump_suffix => "production", # A string to differentiate mysqldumps
		},
		:staging => {
			:host        => "localhost",
			:user        => "root",
			:password    => "root",
			:name        => "staging_db",
			:backups_dir => "/srv/www/website/backups/dumps",
			:max_backups => "3",
			:dump_suffix => "staging", # A string to differentiate mysqldumps
		},
		:local => {
			:host        => "localhost",
			:user        => "root",
			:password    => "root",
			:name        => "local_db",
			:backups_dir => "/srv/www/website/backups/dumps",
			:max_backups => "3",
			:dump_suffix => "local", # A string to differentiate mysqldumps
		}
	}
end

# Additional hook files.

# // Load (if exists) a file meant to contain your custom hooks for tasks.
if File.exists?("lib/custom-hooks.rb") then
	loadFile "lib/custom-hooks.rb"
end
# // Load (if exists) a file meant to contain your custom tasks.
if File.exists?("lib/custom-hooks.rb") then
	loadFile "lib/custom-tasks.rb"
end

# You're not done! You must also configure production.rb, staging.rb and local.rb
