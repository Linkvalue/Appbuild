set :deploy_config_path, File.expand_path('capistrano/deploy.rb')
set :stage_config_path, File.expand_path('capistrano/environments')

# Load DSL and set up stages
require 'capistrano/setup'

# Include default deployment tasks
require 'capistrano/deploy'

# Load custom tasks
Dir.glob('capistrano/tasks/*.rb').each { |r| import r }
