require 'nokogiri'

module Jekyll
    module LinkFilter
        def autonofollow(input)
            content = Nokogiri::HTML.fragment(input)
            site_url = @context.registers[:site].config['url']
            content.css("a").each do |a|
                next unless a.get_attribute('href') =~ /\Ahttp/i
                next if a.get_attribute("href").start_with?(site_url)
                a['rel'] = "nofollow"
                a['target'] = "_blank"
            end
            content.to_s
        end
    end
end

Liquid::Template.register_filter(Jekyll::LinkFilter)
