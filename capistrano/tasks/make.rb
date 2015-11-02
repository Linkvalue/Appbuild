# Define tasks under make namespace
namespace :make do

  desc "Execute make prod-deploy"
  task :prod_deploy do
    on roles(:web) do |host|
      within release_path do
        execute :make, 'prod-deploy'
      end
    end
  end

end

after 'deploy:updated', 'make:prod_deploy'
