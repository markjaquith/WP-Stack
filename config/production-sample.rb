# production-sample.rb
#
# This file is only loaded for the production stage. It contains values that
# will be present when you run tasks related to production.
#
# Customize this file to your own needs and copy it as production.rb.

# Where should the site deploy to?
set :deploy_to, production_deploy_to

# Backup settings
set :application_backup_path, production_backup_path
set :application_max_backups, production_max_backups

# Now configure the servers for this environment

# OPTION 1
# Your web servers IP addresses or hostnamen go here

role :web, production_domain
# role :web, "second web server here"
# role :web, "third web server here, etc"

# role :memcached, "your memcached server IP address or hostname here"
# role :memcached, "second memcached server here, etc"

# OPTION 2

# If your web servers are the same as your memcached servers,
# comment out all the "role" lines and use "server" lines:

# server "your web/memcached server IP address or hostname here", :web, :memcached
# server "second web/memcached server here", :web, :memcached
