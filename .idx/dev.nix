{pkgs}: {
  channel = "stable-23.11";
  packages = [
    pkgs.nodejs_20
    pkgs.php83Packages.composer
    pkgs.php83
    pkgs.mysql80
  ];
  idx.extensions = [
    "vue.volar"
  ];
  idx.previews = {
    previews = {
      web = {
        command = [
          "npm"
          "run"
          "dev"
        ];
        manager = "web";
      };
    };
  };
  idx.workspace.onCreate = {
    create-env = "cp .env.example .env";
    gen-app-key = "php artisan key:generate";
  };
  idx.workspace.onStart = {
    install-php-deps = "composer install";
    install-npm-deps = "npm install";
    ide-helpers = "composer ide-helpers";
  };
}