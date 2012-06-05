namespace :shared do
	task :make_shared_dir do
		run "if [ ! -d #{shared_path}/files ]; then mkdir #{shared_path}/files; fi"
	end
	task :make_symlinks do
		run "if [ ! -h #{release_path}/shared ]; then ln -s #{shared_path}/files/ #{release_path}/shared; fi"
		run "for p in `find -L #{release_path} -type l`; do t=`readlink $p | grep -o 'shared/.*$'`; sudo mkdir -p #{release_path}/$t; sudo chown www-data:www-data #{release_path}/$t; done"
	end
end

namespace :nginx do
	desc "Restarts nginx"
	task :restart do
		run "sudo /etc/init.d/nginx restart"
	end
end

namespace :phpfpm do
  desc" Restarts PHP-FPM"
  task :restart do
    run "sudo /etc/init.d/php-fpm restart"
  end
end

namespace :git do
	desc "Updates git submodule tags"
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

