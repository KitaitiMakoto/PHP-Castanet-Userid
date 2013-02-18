require 'rake/clean'

SPEC_FILE = File.expand_path('../composer.json', __FILE__)

task :default => :test

desc 'Run tests'
task :test do
  sh 'vendor/bin/phpunit tests/*Test.php'
end

desc 'Generate documentation'
task :doc do
  sh 'phpdoc --directory src --target doc'
  sh 'kramdown README.markdown > doc/README.html'
end

desc 'Release package'
task :release do
  require 'json'
  spec = JSON.load(open(SPEC_FILE))
  version = spec['version']
  sh "git tag #{version}"
  sh "git push --tags && git push --tags github"
end
