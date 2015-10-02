set :application, "Imobiparser"
set :domain,      "ftp.imobiliarlist.ro"
set :deploy_to,   "public_html"
set :app_path,    "app"
set :web_path,    "web"


set :scm,         :git
set :repository,  "file:///Users/Cristi/asdasd"

set :dump_assetic_assets, true
set :use_composer, true

set :shared_files,    ["app/config/parameters.yml"]
set :shared_children, [app_path + "/logs", web_path + "/uploads", "vendor", app_path + "/sessions"]

session:
    save_path: "%kernel.root_dir%/sessions/"

set :use_sudo,      false
set :user, "r40525imob"

set :writable_dirs,       ["app/cache", "app/logs", "app/sessions"]
set :webserver_user,      "www-data"
set :permission_method,   :acl
set :use_set_permissions, true
