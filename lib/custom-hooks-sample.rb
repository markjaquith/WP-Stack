# custom-hooks-sample.rb
# 
# This is a sample file for custom task hooks. Copy this file to 
# custom-hooks.rb and add your own hooks or use the following ones.
# custom-hooks.rb is not required, and if it exists, it will be loaded
# automatically from /config/config.rb.

# Backup remote database before making symbolic link to current release.
before "deploy:create_symlink", "db:backup"

# Clone Git submodules before making symbolic link to current release.
before "deploy:create_symlink", "git:submodule_tags"
