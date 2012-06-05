# Customize this file, and then rename it to config.rb

set :application, "WP Stack Site"
set :repository,  "set your git repository location here"
set :deploy_to, "/srv/www/example.com"
set :scm, :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `git`, `mercurial`, `perforce`, `subversion` or `none`

# Using Git Submodules?
set :git_enable_submodules, 1

# role :web, "your web server here"
# role :web, "second web server here"
# role :web, "third web server here, etc"

# role :memcached, "your memcached server here"
# role :memcached, "second memcached server here, etc"

# Alternatively, if your web servers are the same as your memcached servers,
# comment out all the "role" lines and use "server" lines:

# server "your web/memcached server here", :web, :memcached
# server "second web/memcached server here", :web, :memcached
