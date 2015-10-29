# Define tasks under symfony namespace
namespace :symfony do

  desc "Set symfony_env environment variable"
  task :set_symfony_env do
    fetch(:default_env).merge!(symfony_env: fetch(:symfony_env) || 'prod')
  end

end

Capistrano::DSL.stages.each do |stage|
  after stage, 'symfony:set_symfony_env'
end
