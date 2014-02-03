# deploy-after.rb
# 
# This file is loaded after deploy.rb, and includes hooks to be executed when
# all the config settings are loaded.


# Clone Git submodules before making symbolic link to current release.
before "deploy:create_symlink", "git:submodule_tags" if git_enable_submodules
