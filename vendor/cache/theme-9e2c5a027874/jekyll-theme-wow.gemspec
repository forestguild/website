# -*- encoding: utf-8 -*-
# stub: jekyll-theme-wow 0.0.1 ruby lib

Gem::Specification.new do |s|
  s.name = "jekyll-theme-wow".freeze
  s.version = "0.0.1"

  s.required_rubygems_version = Gem::Requirement.new(">= 0".freeze) if s.respond_to? :required_rubygems_version=
  s.metadata = { "plugin_type" => "theme" } if s.respond_to? :metadata=
  s.require_paths = ["lib".freeze]
  s.authors = ["Nikita Chernyi".freeze]
  s.date = "2019-04-04"
  s.email = ["github@rakshazi.me".freeze]
  s.files = ["README.md".freeze, "_includes/assets/amp/boilerplate.css".freeze, "_includes/assets/amp/custom.css".freeze, "_includes/assets/loader.html".freeze, "_includes/assets/logo.svg".freeze, "_includes/assets/website.css".freeze, "_includes/assets/website.js".freeze, "_includes/elements/actualbanner.html".freeze, "_includes/elements/author.html".freeze, "_includes/elements/menu.html".freeze, "_includes/elements/related.html".freeze, "_includes/game/affixes.html".freeze, "_includes/game/attendance.html".freeze, "_layouts/amp.html".freeze, "_layouts/core/compress.html".freeze, "_layouts/core/default.html".freeze, "_layouts/core/page.html".freeze, "_layouts/core/redirect.html".freeze, "_layouts/wiki.html".freeze, "_sass/.gitkeep".freeze, "assets/css/bootstrap.min.css".freeze, "assets/css/bootstrap.min.css.map".freeze, "assets/css/tablesorter.min.css".freeze, "assets/img/affixes/1.jpg".freeze, "assets/img/affixes/1.webp".freeze, "assets/img/affixes/10.jpg".freeze, "assets/img/affixes/10.webp".freeze, "assets/img/affixes/11.jpg".freeze, "assets/img/affixes/11.webp".freeze, "assets/img/affixes/117.jpg".freeze, "assets/img/affixes/117.webp".freeze, "assets/img/affixes/12.jpg".freeze, "assets/img/affixes/12.webp".freeze, "assets/img/affixes/13.jpg".freeze, "assets/img/affixes/13.webp".freeze, "assets/img/affixes/14.jpg".freeze, "assets/img/affixes/14.webp".freeze, "assets/img/affixes/15.jpg".freeze, "assets/img/affixes/15.webp".freeze, "assets/img/affixes/16.jpg".freeze, "assets/img/affixes/16.webp".freeze, "assets/img/affixes/2.jpg".freeze, "assets/img/affixes/2.webp".freeze, "assets/img/affixes/3.jpg".freeze, "assets/img/affixes/3.webp".freeze, "assets/img/affixes/4.jpg".freeze, "assets/img/affixes/4.webp".freeze, "assets/img/affixes/5.jpg".freeze, "assets/img/affixes/5.webp".freeze, "assets/img/affixes/6.jpg".freeze, "assets/img/affixes/6.webp".freeze, "assets/img/affixes/7.jpg".freeze, "assets/img/affixes/7.webp".freeze, "assets/img/affixes/8.jpg".freeze, "assets/img/affixes/8.webp".freeze, "assets/img/affixes/9.jpg".freeze, "assets/img/affixes/9.webp".freeze, "assets/img/alliance.png".freeze, "assets/img/alliance.webp".freeze, "assets/img/design/bg.affixes.jpg".freeze, "assets/img/design/bg.affixes.webp".freeze, "assets/img/design/bg.connect.jpg".freeze, "assets/img/design/bg.connect.webp".freeze, "assets/img/design/bg.dark.jpg".freeze, "assets/img/design/bg.dark.webp".freeze, "assets/img/design/bg.red.jpg".freeze, "assets/img/design/bg.red.webp".freeze, "assets/img/design/bg.today.jpg".freeze, "assets/img/design/bg.today.webp".freeze, "assets/img/design/navbar.jpg".freeze, "assets/img/design/navbar.webp".freeze, "assets/img/dungeons/ataldazar.jpg".freeze, "assets/img/dungeons/ataldazar.rezan.jpg".freeze, "assets/img/dungeons/ataldazar.rezan.webp".freeze, "assets/img/dungeons/ataldazar.webp".freeze, "assets/img/dungeons/freehold.jpg".freeze, "assets/img/dungeons/freehold.last.jpg".freeze, "assets/img/dungeons/freehold.last.webp".freeze, "assets/img/dungeons/freehold.webp".freeze, "assets/img/gold.jpg".freeze, "assets/img/gold.webp".freeze, "assets/img/horde-alliance.png".freeze, "assets/img/horde-alliance.webp".freeze, "assets/img/horde.png".freeze, "assets/img/horde.webp".freeze, "assets/img/line.lg.png".freeze, "assets/img/line.lg.webp".freeze, "assets/img/line.sm.png".freeze, "assets/img/line.sm.webp".freeze, "assets/img/logo.png".freeze, "assets/img/logo.webp".freeze, "assets/img/roles/dd.jpg".freeze, "assets/img/roles/dd.webp".freeze, "assets/img/roles/heal.jpg".freeze, "assets/img/roles/heal.webp".freeze, "assets/img/roles/tank.jpg".freeze, "assets/img/roles/tank.webp".freeze, "assets/js/bootstrap.bundle.min.js".freeze, "assets/js/jquery.min.js".freeze, "assets/js/tablesorter.filter.min.js".freeze, "assets/js/tablesorter.min.js".freeze]
  s.homepage = "https://github.com/forestguild/theme".freeze
  s.licenses = ["MIT".freeze]
  s.rubygems_version = "3.0.3".freeze
  s.summary = "World of Warcraft theme for Jekyll".freeze

  s.installed_by_version = "3.0.3" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 4

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<jekyll>.freeze, ["~> 3.5"])
    else
      s.add_dependency(%q<jekyll>.freeze, ["~> 3.5"])
    end
  else
    s.add_dependency(%q<jekyll>.freeze, ["~> 3.5"])
  end
end
