# staging-sample.rb
#
# This file is only loaded for the staging stage. It contains values that
# will be present when you run tasks related to staging.
#
# Customize this file to your own needs and copy it as staging.rb.

# Where should the site deploy to?
set :deploy_to, staging_deploy_to

# Backup settings
set :application_backup_path, staging_backup_path
set :application_max_backups, staging_max_backups

# Now configure the servers for this environment

# OPTION 1
# Your web servers IP addresses or hostnamen go here

role :web, staging_domain
# role :web, "second web server here"
# role :web, "third web server here, etc"

# role :memcached, "your memcached server IP address or hostname here"
# role :memcached, "second memcached server here, etc"

# OPTION 2

# If your web servers are the same as your memcached servers,
# comment out all the "role" lines and use "server" lines:

# server "your web/memcached server IP address or hostname here", :web, :memcached
# server "second web/memcached server here", :web, :memcached
