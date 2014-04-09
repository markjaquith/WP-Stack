# Stage WP configuration file.
#
# This is a sample config file. It manages settings for your remote repository,
# your default domains and your database values. Copy it to config.rb and
# customize it to your own needs.


# Application settings.

# // The name of your application. It can be any string.
set :application, "%%APPLICATION%%"

# // A shortname to identify your application.
set :application_id, "%%APPLICATION_ID%%"

# Where is your wp-config.php file located within #{release_path}?
# // Your release path points to the newer copy of your repository, so you must
# // specify where your wp-config.php file is located within your remore repo.
# // WordPress Bareboner puts the file in the "app" subfolder, so it needs to
# // be "#{release_path}/app", but if you are using WordPress-Skeleton, just
# // "#{release_path}" should do the trick and this value should be left empty.
set :application_path, "/app"

# Repository settings

# // Location of your remote repository.
set :repository,  "%%REPOSITORY%%"

# // Your preferred method for source control. Supports :accurev, :bzr, :cvs,
# // :darcs, :git, :mercurial, :perforce, :subversion or :none.
set :scm, :git

# // Using Git submodules?
set :git_enable_submodules, 1

# Default deploy directories.

# // :deploy_to in production.rb will use this value as default
set :production_deploy_to, "%%PRODUCTION_DEPLOY_TO%%"

# // :deploy_to in staging.rb will use this value as default
set :staging_deploy_to, "%%STAGING_DEPLOY_TO%%"

# Default domains.

# // :deploy_to in production.rb will use this value as default
set :production_domain, "%%PRODUCTION_DOMAIN%%"

# // :deploy_to in staging.rb will use this value as default
set :staging_domain, "%%STAGING_DOMAIN%%"

# Local stage default settings.

# // Path to your local shared folder.
set :local_shared_folder, "%%LOCAL_SHARED_FOLDER%%"

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
			:host        => "%%PRODUCTION_HOST%%",
			:user        => "%%PRODUCTION_USER%%",
			:password    => "%%PRODUCTION_PASSWORD%%",
			:name        => "%%PRODUCTION_DB%%",
			:dump_suffix => "%%PRODUCTION_DUMP_SUFFIX%%", # A string to differentiate mysqldumps
		},
		:staging => {
			:host        => "%%STAGING_HOST%%",
			:user        => "%%STAGING_USER%%",
			:password    => "%%STAGING_PASSWORD%%",
			:name        => "%%STAGING_DB%%",
			:dump_suffix => "%%STAGING_DUMP_SUFFIX%%", # A string to differentiate mysqldumps
		},
		:local => {
			:host        => "%%LOCAL_HOST%%",
			:user        => "%%LOCAL_USER%%",
			:password    => "%%LOCAL_PASSWORD%%",
			:name        => "%%LOCAL_DB%%",
			:dump_suffix => "%%LOCAL_DUMP_SUFFIX%%", # A string to differentiate mysqldumps
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
