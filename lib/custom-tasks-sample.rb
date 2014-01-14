# custom-tasks-sample.rb
# 
# This is a sample file for custom tasks. Copy this file to custom-tasks.rb and
# add your own tasks or use the following ones. custom-tasks.rb is not required,
# and if it exists, it will be loaded automatically from /config/config.rb.

namespace :static do
	desc "Upload non-repository files"
	task :upload, :roles => :web do
		if stage == :local then
			puts "[ERROR] You must run static:upload from staging with cap staging static:upload or from production with cap production static:upload"
		else
			uploads = [
				[ "/example/source", "/example/destination" ],
			]
			uploads.each do |upload|
				if File.exists? upload[0] then 
					current_host = capture("echo $CAPISTRANO:HOST$").strip
					command = "scp -r #{upload[0]} #{user}@#{current_host}:#{upload[1]}"
					system command
				else
					puts "The path #{upload[0]} does not exists"
				end
			end
		end
	end
end