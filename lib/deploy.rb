# deploy.rb
# 
# This file includes default settings and hooks for the deployment process.

set :user, "deploy"
set :use_sudo, false
set :deploy_via, :remote_cache
set :copy_exclude, [".git", ".gitmodules", ".DS_Store", ".gitignore"]
set :keep_releases, 5

after "deploy:update", "deploy:cleanup"
after "deploy:update_code", "shared:make_shared_dir"
after "deploy:update_code", "shared:make_symlinks"
after "deploy:update_code", "db:make_config"
after "deploy", "memcached:update"

before "deploy:setup", "db:init"

# Pull in the config file
loadFile 'config/config.rb'
