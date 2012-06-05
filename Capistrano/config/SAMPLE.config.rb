# Customize this file, and then rename it to config.rb

set :application, "WP Stack Site"
set :repository,  "set your git repository location here"
set :scm, :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `git`, `mercurial`, `perforce`, `subversion` or `none`

# Using Git Submodules?
set :git_enable_submodules, 1

# Designate a media master and path. This should be a server that always has the latest production user-uploaded files
# The path you designate here should be the same as your production :deploy_to value
media_master '127.0.0.1', '/srv/www/example.com'

# Database
# Set the values for host, user, pass, and name for both production and staging.
set :wpdb do
  {
    :production => {
      :host     => 'PRODUCTION DB HOST',
      :user     => 'PRODUCTION DB USER',
      :password => 'PRODUCTION DB PASS',
      :name     => 'PRODUCTION DB NAME',
    },
    :staging => {
      :host     => 'STAGING DB HOST',
      :user     => 'STAGING DB USER',
      :password => 'STAGING DB PASS',
      :name     => 'STAGING DB NAME',
    }
  }
end

# You're not done! You must also configure production.rb and staging.rb
