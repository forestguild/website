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

        def tocAbsoluteUrls(input, page_url)
            content = Nokogiri::HTML.fragment(input)
            content.css("a").each do |a|
                next unless a.get_attribute('href').start_with?('#')
                a['href'] = canonical(page_url) + a['href']
            end
            content.to_s
        end

        # Special for Yandex.Turbo, because their parsers raise warnings
        def removeToc(input)
            input.gsub(/(<!-- vim-markdown-toc.*<!-- vim-markdown-toc -->\n)/m, '')
        end

        def canonical(input, prefix = '')
            unless input.nil?
            url = input.gsub('index.html','').gsub('amp/','/').gsub('.html','')
            @context.registers[:site].config['url'] + (@context.registers[:site].config['baseurl'] + (prefix ? '/' + prefix : '') + url).gsub('//','/')
            end
        end
    end
end

Liquid::Template.register_filter(Jekyll::LinkFilter)
