#
# Capistrano configuration; see cap_deploy.rb
#

require 'rubygems'
#require 'railsless-deploy'

load 'deploy' if respond_to?(:namespace) # cap2 differentiator
Dir['vendor/plugins/*/recipes/*.rb'].each { |plugin| load(plugin) }

load 'cap_deploy' # remove this line to skip loading any of the default tasks