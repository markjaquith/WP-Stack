# Stage WP configuration file
#
# This is a sample config file. It manages settings for your remote repository,
# your default domains and your database values. Copy it to config.rb and
# customize it to your own needs.

# Repository settings
set :application, "Local WordPress Installation"
set :repository,  "git://github.com/username/website.git"
set :scm, :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `git`, `mercurial`, `perforce`,
# `subversion` or `none`

# Using Git submodules?
set :git_enable_submodules, 1

# This should be the same as :deploy_to in production.rb
set :production_deploy_to, "/srv/www/website/application"

# The domain name used for your production environment
set :production_domain, ""

# The domain name used for your staging environment
set :staging_domain, ""

# Database
# Set the values for host, user, pass, and name for production, staging and 
# local stages. You can also specify a backup directory where your mysqldumps
# should be saved.
set :wpdb do
	{
		:production => {
			:host     	 => "localhost",
			:user     	 => "root",
			:password	 => "root",
			:name     	 => "production_db",
			:backups_dir => "/srv/www/website/backups/dumps",
			:dump_suffix => "production", # A string to differentiate mysqldumps 
		},
		:staging => {
			:host     	 => "localhost",
			:user     	 => "root",
			:password	 => "root",
			:name     	 => "staging_db",
			:backups_dir => "/srv/www/website/backups/dumps",
			:dump_suffix => "staging", # A string to differentiate mysqldumps 
		},
		:local => {
			:host     	 => "localhost",
			:user     	 => "root",
			:password	 => "root",
			:name     	 => "local_db",
			:backups_dir => "/srv/www/website/backups/dumps",
			:dump_suffix => "local", # A string to differentiate mysqldumps 
		}
	}
end

# Path to your local uploads folder.
set :local_shared_folder, "/srv/www/website/application/application/shared"

# Load additional files
loadFile "lib/custom-hooks.rb"
loadFile "lib/custom-tasks.rb"

# You"re not done! You must also configure production.rb and staging.rb
