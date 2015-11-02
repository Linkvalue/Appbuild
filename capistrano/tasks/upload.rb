# Define tasks under upload namespace
namespace :upload do

  desc "Upload linked_files, either if linked file doesn't exist yet or force_upload_linked_files is true."
  task :upload_linked_files do
    on roles(:web) do |host|
      fetch(:linked_files).each do |file|
        target = shared_path.join(file)
        if !test("[ -f #{target} ]") || fetch(:force_upload_linked_files, false)
          file_ext = File.extname(file)
          source = File.join(File.dirname(file), "#{File.basename(file, file_ext)}_#{fetch(:stage)}#{file_ext}")
          invoke 'upload:upload_source_as_target', source, target
        else
          info "#{target} linked file already exists. Set :force_upload_linked_files to true if you want to update it."
        end
      end
    end
  end

  desc "Upload local source as remote target"
  task :upload_source_as_target, :source, :target do |t, args|
    source = args[:source]
    target = args[:target]
    on(:local) do
      info "Looking for #{source} to replace #{target}..."
      if test("[ -f #{source} ]")
          on roles(:all) do |host|
            info "Uploading file #{source} as #{target}..."
            upload! source, target
          end
      else
        warn "#{source} file does not exist. Nothing to upload. Skipped."
      end
    end
  end

end

before 'deploy:check:linked_files', 'upload:upload_linked_files'
