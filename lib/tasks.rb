# tasks.rb
# 
# This file contains the core tasks for Stage WP. You can override any of these
# tasks and add your own into custom-tasks.rb.

namespace :shared do
	desc "Create shared folder"
	task :make_shared_dir do
		run "if [ ! -d #{shared_path}/files ]; then mkdir #{shared_path}/files; fi"
	end
	desc "Create symlinks to shared folder"
	task :make_symlinks do
		run "if [ ! -h #{release_path}/shared ]; then ln -s #{shared_path}/files/ #{release_path}/shared; fi"
		run "for p in `find -L #{release_path} -type l`; do t=`readlink $p | grep -o 'shared/.*$'`; sudo mkdir -p #{release_path}/$t; sudo chown www-data:www-data #{release_path}/$t; done"
	end
	desc "Pulls shared files from remote location"
	task :pull do
		if stage == :local then
			puts "[Error] You must run shared:pull from staging or production with cap staging shared:pull or cap production shared:pull"
		else
			random = rand(10 ** 5).to_s.rjust(5, '0')
			current_host = capture("echo $CAPISTRANO:HOST$").strip
			run "cd #{shared_path}; sudo zip -r /tmp/#{ramdom}-shared .;"
			system "cd #{local_shared_folder}; sudo mkdir #{random}; sudo mkdir ../#{random}; sudo mv * ../#{random}/; sudo scp #{user}@#{current_host}:/tmp/#{ramdom}-shared.zip #{local_shared_folder}; sudo unzip #{ramdom}-shared.zip; sudo rm -rf shared.zip ../#{ramdom};"
		end
	end
	desc "Pushes shared files to remote location"
	task :push do
		if stage == :local then
			puts "[Error] You must run shared:pull from staging or production with cap staging shared:pull or cap production shared:pull"
		else
			random = rand(10 ** 5).to_s.rjust(5, '0')
			current_host = capture("echo $CAPISTRANO:HOST$").strip
			puts "Current user is #{user}"
			system "cd #{local_shared_folder}; sudo zip -r /tmp/#{random}-shared .;"
			system "sudo scp /tmp/shared.zip #{user}@#{current_host}:#{shared_path}"
			run "cd #{shared_path}; sudo mkdir ../#{random};"
			run "cd #{shared_path}/../#{random}; sudo unzip /tmp/#{random}-shared.zip;"
			run "mv #{shared_path} #{shared_path}-#{random}; mv #{shared_path}/../#{random} #{shared_path}; rm -rf #{shared_path}-#{random}"
			run "sudo chown -R #{user}:#{user} #{shared_path}"
			system "cd #{local_shared_folder}; sudo rm -f uploads.zip"
		end
	end
end

namespace :nginx do
	desc "Restart nginx"
	task :restart do
		run "sudo /etc/init.d/nginx reload"
	end
end

namespace :phpfpm do
	desc "Restart PHP-FPM"
	task :restart do
		begin # For non-Ubuntu systems
			run "sudo /etc/init.d/php-fpm restart"	
		rescue Exception => e # For Ubuntu systems
			run "sudo /etc/init.d/php5-fpm restart"	
		end
	end
end

namespace :git do
	desc "Update Git submodule tags"
	task :submodule_tags do
		run "if [ -d #{shared_path}/cached-copy/ ]; then cd #{shared_path}/cached-copy/ && git submodule foreach --recursive git fetch origin --tags; fi"
	end
end

namespace :memcached do
	desc "Restarts Memcached"
	task :restart do
		run "echo 'flush_all' | nc localhost 11211", :roles => [:memcached]
	end
	desc "Updates the pool of memcached servers"
	task :update do
		unless find_servers( :roles => :memcached ).empty? then
			mc_servers = '<?php return array( "' + find_servers( :roles => :memcached ).join( ':11211", "' ) + ':11211" ); ?>'
			run "echo '#{mc_servers}' > #{current_path}/memcached.php", :roles => :memcached
		end
	end
end

namespace :db do
	desc "Initialize the WordPress database in a remote stage" # This is a tricky one
	task :init do
		if stage == :local then
			puts "[Error] You must run db:init from staging or production with cap staging db:init or cap production db:init"
		else
			s = (stage == :production) ? wpdb[:production] : wpdb[:staging]
			# Obtain MySQL admin credentials
			admin_name = Capistrano::CLI.ui.ask "Please provide the name of your MySQL admin user [root] "
			admin_name = "root" if admin_name.empty?
			admin_password = Capistrano::CLI.password_prompt "Please provide the password of your MySQL admin user [root] "
			admin_password = "root" if admin_password.empty?
			# Store query strings
			create = "CREATE DATABASE IF NOT EXISTS #{s[:name]};"
			grant = "GRANT ALL PRIVILEGES ON #{s[:name]}.* TO '#{s[:user]}'@'#{s[:host]}' IDENTIFIED BY '#{s[:password]}';"
			show = "SHOW DATABASES;"
			# Initialize output
			output = ""
			# Check if database already exists
			run "mysql -u #{admin_name} -p -e \"#{show}\"" do |channel, stream, data| 
				if data =~ /^Enter password: /
				  	# Securely pass admin password
				    channel.send_data "#{admin_password}\n"
				end
				output += data
			end
			if output.include? s[:name] then # If database exists ...
				puts "Database already exists."
			else # In case database does not exist ...
				output = ""
				# Try to create database
				run "mysql -u #{admin_name} -p -e \"#{create} #{grant} #{show}\"" do |channel, stream, data| 
				  if data =~ /^Enter password: /
				  	# Securely pass admin password
				    channel.send_data "#{admin_password}\n"
				  end
				  output += data
				end
				if output.include? s[:name] then # If database was created ...
					puts "Remote database was initialized."
				else # If database wasn't created ...
					puts "Remote database could not be created."
				end
			end
		end
	end
	desc "Syncs the staging database (and uploads) from production"
	task :sync do
		if stage == :production then
			puts "[Error] You must run db:sync from staging with cap staging db:sync or from local with cap local db:sync"
		else
			if stage == :staging then
				puts "Hang on... this might take a while."
				random = rand(10 ** 5).to_s.rjust(5, '0')
				p = wpdb[:production]
				s = wpdb[:staging]
				puts "db:sync"
				puts stage
				run "ssh #{user}@#{production_domain} \"mysqldump -u #{p[:user]} --result-file=/tmp/wpstack-#{random}.sql -h #{p[:host]} -p#{p[:password]} #{p[:name]}\""
				run "scp #{user}@#{production_domain}:/tmp/wpstack-#{random}.sql /tmp/"
				run "mysql -u #{s[:user]} -h #{s[:host]} -p#{s[:password]} #{s[:name]} < /tmp/wpstack-#{random}.sql && rm /tmp/wpstack-#{random}.sql"
				puts "Database synced to staging"
				# Now to copy files
				find_servers( :roles => :web ).each do |server|
					run "rsync -avz --delete #{user}@#{production_domain}:#{production_deploy_to}/shared/files/ #{staging_deploy_to}/shared/files/"
				end
			end
			if stage == :local then
				puts "Hang on... this might take a while."
				random = rand(10 ** 5).to_s.rjust(5, '0')
				p = wpdb[:production]
				l = wpdb[:local]
				puts "db:sync"
				puts stage
				system "ssh #{user}@#{production_domain} \"mysqldump -u #{p[:user]} --result-file=/tmp/wpstack-#{random}.sql -h #{p[:host]} -p#{p[:password]} #{p[:name]}\""
				system "scp -#{user}@#{production_domain}:/tmp/wpstack-#{random}.sql /tmp/"
				system "mysql -u #{l[:user]} -h #{l[:host]} -p#{l[:password]} #{l[:name]} < /tmp/wpstack-#{random}.sql && rm /tmp/wpstack-#{random}.sql"
				puts "Database synced to local"
				# memcached.restart
				puts "Memcached flushed"
				# Now to copy files
				system "rsync -avz --delete #{user}@#{production_domain}:#{production_deploy_to}/shared/files/ #{local_shared_folder}/files/"
			end
		end
	end
	desc "Backup database"
	task :backup do
		if stage == :local then
			l = wpdb[:local]
			puts "Backing up MySQL local database..."
			filename = "#{l[:backups_dir]}/#{release_name}-#{l[:dump_suffix]}.sql"
			# Create folder for dumps, in case that it doesn't exist
			system "sudo mkdir -p #{l[:backups_dir]}"
			system "sudo mysqldump -u #{l[:user]} -p#{l[:password]} #{l[:name]} > #{filename}"
			if File.exists? filename then
				puts "MySQL local database saved to #{filename}"
			else
				puts "MySQL local database could not be saved."
			end
		end
		if stage == :production || stage == :staging then
			s = (stage == :production) ? wpdb[:production] : wpdb[:staging]
			puts "Backing up remote MySQL database..."
			filename = "#{s[:backups_dir]}/#{release_name}-#{s[:dump_suffix]}.sql"
			# Create folder for dumps, in case that it doesn't exist
			run "mkdir -p #{s[:backups_dir]}"
			begin
				run "mysqldump -u #{s[:user]} -p #{s[:name]} > #{filename}" do |channel, stream, data| 
				  if data =~ /^Enter password: /
				    channel.send_data "#{s[:password]}\n"
				  end
				end
				puts "Remote MySQL database saved to #{filename}"
			rescue Exception => Error
				puts "Remote MySQL database could not be saved."
			end
		end
	end
	desc "Restore database from backup"
	task :restore do
		if stage == :local then
				l = wpdb[:local]
			puts "Searching for available local backups..."
			# List contents from dumps folder
			backups = `ls -1 #{l[:backups_dir]}/`.split("\n")
			# Define default backup
			default_backup = backups.last
			puts "Available backups: "
			puts backups
			backup = Capistrano::CLI.ui.ask "Which backup would you like to restore? [#{default_backup}] "
			backup_file = backup
			backup_file = default_backup if backup_file.empty?
			if system "mysql -u #{l[:user]} -p#{l[:password]} #{l[:name]} < #{l[:backups_dir]}/#{backup_file}" then
				puts "Local database restored to backup saved in #{l[:backups_dir]}/#{backup_file}."
			else
				puts "Local database could not be restored from backup."
			end
		else
			env = (stage == :production) ? wpdb[:production] : wpdb[:staging]
			puts "Searching for available remote backups..."
			# List contents from dumps folder
			backups = capture("ls -1 #{env[:backups_dir]}").split("\n")
			# Define default backup
			default_backup = backups.last
			puts "Available backups: "
			puts backups
			backup = Capistrano::CLI.ui.ask "Which backup would you like to restore? [#{default_backup}] "
			backup_file = default_backup if backup.empty?
			backup_file = "#{env[:backups_dir]}/#{backup_file}"
			begin
				run "mysql -u #{env[:user]} -p#{env[:password]} #{env[:name]} < #{backup_file}" do |channel, stream, data| 
				  if data =~ /^Enter password: /
				    channel.send_data "#{p[:password]}\n"
				  end
				end
				puts "Remote database restored to backup saved in #{backup_file}."
			rescue Exception => Error
				puts "Remote database could not be restored from backup."
			end
		end
	end
	desc "Pull a remote database backup to local"
	task :pull do
		if stage == :local then
			puts "[Error] You must run db:pull from staging or production with cap staging db:pull or cap production db:pull"
		else
			env = (stage == :production) ? wpdb[:production] : wpdb[:staging]
			l = wpdb[:local]
			puts "Searching for available remote backups..."
			# List contents from dumps folder
			backups = capture("ls -1 #{env[:backups_dir]}").split("\n")
			# Define default backup
			default_backup = backups.last
			puts "Available backups: "
			puts backups
			backup = Capistrano::CLI.ui.ask "Which backup would you like to pull? [#{default_backup}] "
			backup_file = default_backup if backup.empty?
			current_host = capture("echo $CAPISTRANO:HOST$").strip
			if system "scp #{user}@#{current_host}:#{env[:backups_dir]}/#{backup_file} #{l[:backups_dir]}" then
				puts "Remote database saved to local host at #{l[:backups_dir]}/#{backup_file}."
			else
				puts "Remote database could not be pulled from backup."
			end
		end
	end
	desc "Push a remote database backup to remote from local"
	task :push do
		if stage == :local then
			puts "[Error] You must run db:push from staging or production with cap staging db:push or cap production db:push"
		else
			env = (stage == :production) ? wpdb[:production] : wpdb[:staging]
			l = wpdb[:local]
			puts "Searching for available local backups..."
			# List contents from dumps folder
			backups = `ls -1 #{l[:backups_dir]}/`.split("\n")
			# Define default backup
			default_backup = backups.last
			puts "Available backups: "
			puts backups
			backup = Capistrano::CLI.ui.ask "Which backup would you like to push? [#{default_backup}] "
			backup_file = default_backup if backup.empty?
			current_host = capture("echo $CAPISTRANO:HOST$").strip
			if system "scp #{l[:backups_dir]}/#{backup_file} #{user}@#{current_host}:#{env[:backups_dir]}" then
				puts "Local database uploaded to remote host at #{env[:backups_dir]}/#{backup_file}."
			else
				puts "Local database could not be pushed from backup."
			end
		end
	end
	desc "Set the database credentials (and other settings) in wp-config.php"
	task :make_config do
		set :staging_domain, '' unless defined? staging_domain
		{:'%%WP_STAGING_DOMAIN%%' => staging_domain,
		 :'%%WP_STAGE%%'          => stage,
		 :'%%DB_NAME%%'           => wpdb[stage][:name],
		 :'%%DB_USER%%'           => wpdb[stage][:user],
		 :'%%DB_PASSWORD%%'       => wpdb[stage][:password],
		 :'%%DB_HOST%%'           => wpdb[stage][:host]
		}.each do |k, v|
			run "sed -i 's/#{k}/#{v}/' #{release_path}#{application_path}/wp-config.php", :roles => :web
		end
	end
end

namespace :util do
	if !File.exists?("#{release_path}#{tasks_path}/config.sh") then
		abort("Backup tasks are not configured.")
	end
	desc "Set the database credentials (and other settings) in config.sh"
	task :make_config do
		set :staging_domain, '' unless defined? staging_domain
		run "sudo rm -f #{release_path}#{tasks_location}/config.sh"
		run "sudo cp #{release_path}#{tasks_location}/config-sample.sh #{release_path}#{tasks_location}/config.sh"
		{:'%%DB_NAME%%'                 => wpdb[stage][:name],
		 :'%%DB_USER%%'                 => wpdb[stage][:user],
		 :'%%DB_PASSWORD%%'             => wpdb[stage][:password],
		 :'%%DB_BACKUP_PATH%%'          => wpdb[stage][:backups_dir],
		 :'%%DB_MAX_BACKUPS%%'          => wpdb[stage][:max_backups],
		 :'%%PROJECT_PATH%%'            => release_path,
		 :'%%USR_BIN_PREFIX%%'          => application_id,
		 :'%%APPLICATION_PATH%%'        => application_path,
		 :'%%APPLICATION_BACKUP_PATH%%' => application_backup_path,
		 :'%%APPLICATION_MAX_BACKUPS%%' => application_max_backups,
		}.each do |k, v|
			run "sed -i 's/#{k}/#{v}/' #{release_path}#{tasks_path}/config.sh", :roles => :web
		end
	end
	desc "Add tasks commands to /usr/bin"
	task :make_commands do
		run "sudo bash #{release_path}#{tasks_location}/add-to-bin.sh"
	end
	desc "Zip and save all application files to #{application_backup_path}"
	task :backup_application do
		run "sudo bash #{release_path}#{tasks_location}/website-backup-application.sh"
	end
	desc "Perform a mysql dump of the WordPress database and save it to #{wpdb[stage][:backups_dir]}"
	task :backup_db do
		run "sudo bash #{release_path}#{tasks_location}/website-backup-database.sh"
	end
	desc "Switches the state of your site from live to maintenance and vice versa"
	task :switch do
		run "sudo bash #{release_path}#{tasks_location}/website-switch.sh"
	end
	desc "Perform a full backup (files and database)"
	task :full_backup do
		run "sudo bash #{release_path}#{tasks_location}/website-full-backup.sh"
	end
end
