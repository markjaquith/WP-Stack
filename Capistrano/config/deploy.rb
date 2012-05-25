# Pull in the config file
loadFile 'config/config.rb'

after "deploy:restart", "deploy:cleanup"
