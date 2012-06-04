# Customize this file, and then rename it to config.rb

set :application, "WP Stack Site"
set :repository,  "set your git repository location here"
set :scm, :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `git`, `mercurial`, `perforce`, `subversion` or `none`

# Using Git Submodules?
set :git_enable_submodules, 1

role :web, "your web-server here"
