require 'rubygems'
require 'railsless-deploy'
load 'lib/misc'

# Multistage
set :stages, ['production', 'staging']
set :default_stage, 'production'
require 'capistrano/ext/multistage'

load 'lib/tasks'
load 'lib/deploy' # Loads config/config.rb after
load 'lib/deploy-after'
