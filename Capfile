# Capfile
#
# This is the main file of the package and the first to be executed.
#
# Capistrano reads its instructions from here. In ordinary cases, the Capfile is
# where you will tell Capistrano about the servers you want to connect to and
# the tasks you want to perform on those servers. Here we do somethig different:
# we"re just gonna use this file to set up the most basic values and load our
# required libraries, and will configure the rest of our deployment application
# via config/config.rb and some hook files that we"re gonna call here and there.

# Load required modules.
require "rubygems"
require "railsless-deploy"

# Set up multistage
set :stages, ["production", "staging", "local"]
set :default_stage, "production"

# Avoid tty fatal error
default_run_options[:pty] = true

# Load Stage WP libraries
load "lib/misc"
load "lib/tasks"
load "lib/deploy" # Loads config/config.rb after
load "lib/deploy-after"

# Load a Customfile if we have one
loadFile "Customfile"

# Delay loading of multistage module, so we can override :stages if needed
require "capistrano/ext/multistage"
