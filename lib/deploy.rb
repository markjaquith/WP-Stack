# deploy.rb
# 
# This file includes default settings and hooks for the deployment process.

# Set some default values
set :user, "deploy"
set :use_sudo, false
set :deploy_via, :remote_cache
set :copy_exclude, [".git", ".gitmodules", ".DS_Store", ".gitignore"]
set :keep_releases, 5

# Put some hooks before deploy tasks
after "deploy:update", "deploy:cleanup"
after "deploy:update_code", "shared:make_shared_dir"
after "deploy:update_code", "shared:make_symlinks"
after "deploy:update_code", "db:make_config"
after "deploy:update_code", "util:make_config"
after "util:make_config", "util:make_commands"
after "deploy", "memcached:update"

# Put some hooks after deploy tasks
before "deploy:setup", "db:init"

# Pull in the config file
loadFile 'config/config.rb'
