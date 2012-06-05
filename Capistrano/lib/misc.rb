def loadFile file
  begin
    raise "Could not find #{file}" unless FileTest.readable? file
    load file
  rescue Exception => e
    puts '[Error] ' + e.message
    exit 1
  end
end

def media_master server, path
  set :master, Capistrano::ServerDefinition.new( server, :path => path )
end
