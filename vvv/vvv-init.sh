#!/bin/bash
#
# vvv-init.sh
#
# This file will install Capistrano in VVV virtual machines.

# Capture a basic ping result to Google's primary DNS server to determine if
# outside access is available to us. If this does not reply after 2 attempts,
# we try one of Level3's DNS servers as well. If neither IP replies to a ping,
# then we'll skip a few things further in provisioning rather than creating a
# bunch of errors.
echo -e "\nStarting Capistrano installation process..."
ping_result="$(ping -c 2 8.8.4.4 2>&1)"
if [[ $ping_result != *bytes?from* ]]; then
	ping_result="$(ping -c 2 4.2.2.2 2>&1)"
fi

if [[ $ping_result == *bytes?from* ]]; then

	# rubygems install, required by Capistrano
	if [[ ! -d /opt/vagrant_ruby/lib/ruby/gems ]]; then
		sudo apt-get install rubygems
		# Clean up apt caches
		apt-get clean
	else
		echo " * rubygems is already installed."
	fi

	# Capistrano install
	if [[ -d /opt/vagrant_ruby/lib/ruby/gems ]]; then
		if [[ ! -d /var/lib/gems/1.8/gems/capistrano-2.15.5/lib/capistrano ]]; then
			# We're specifying an older Capistrano version, because recent versions
			# may cause errors running some cap commands.
			sudo gem install capistrano -v 2.15.5
			# Install Capistrano friends
			sudo gem install capistrano-ext railsless-deploy
		else
			echo " * Capistrano is already installed."
		fi
	else
		echo -e " * Capistrano requires rubygems to work."
	fi

else
	echo -e "\nNo network connection available, skipping package installation"
fi
