# config valid only for current version of Capistrano
lock '3.4.0'

# Project
set :application, 'AppBuildServer'
set :keep_releases, 3

# VCS
set :scm, :git
set :repo_url, 'git@github.com:LinkValue/AppBuildServer.git'

# Shared dirs/files
set :linked_dirs, fetch(:linked_dirs, []).push('wallet', 'web/uploads', 'build')
set :linked_files, fetch(:linked_files, []).push('app/config/parameters.yml')

# Remote environment
set :default_env, {}

# Logs
set :log_level, :debug

# Force upload linked_files even if they already exists
set :force_upload_linked_files, true
