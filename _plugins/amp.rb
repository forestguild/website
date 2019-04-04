require 'nokogiri'
require 'fastimage'

module Jekyll
    module AmpFilter
        def amp_images(input, responsive = true, wi = nil, he = nil)
            doc = Nokogiri::HTML.fragment(input);
            doc.css('img:not([width])').each do |image|
                if wi && he
                    image['width']  = wi
                    image['height'] = he
                else
                    if image['src'].start_with?('http://', 'https://')
                        src = image['src']
                    else
                        src = File.join(Dir.pwd, image['src'])
                    end
                    begin
                        size = FastImage.size(src)
                        image['width']  = size[0]
                        image['height'] = size[1]
                    rescue Exception => e
                        puts 'Unable to get image dimensions for "' + src + '". For local files, build the site with \'--skip-initial-build\' for better results. [Error: ' + e.to_s + ']'
                    end
                end
            end
            doc.css('img').each do |image|
                image.name = "amp-img"
                image['layout'] = "responsive" if responsive
            end
            doc.css('picture').each do |picture|
                amp_img = picture.css('amp-img')
                picture.add_next_sibling(amp_img) unless amp_img.empty?
                picture.remove
            end
            doc.css('amp-img').each do |amp_img|
                noscript = Nokogiri::XML::Node.new "noscript", doc
                noscript_img = amp_img.dup
                noscript_img.remove_attribute('layout')
                noscript_img.name = 'img'
                noscript.add_child(noscript_img)
                amp_img.add_child(noscript)
            end
            doc.to_s
        end
    end
end

Liquid::Template.register_filter(Jekyll::AmpFilter)

module Jekyll
    class AmpPost < Jekyll::Page
        def initialize(site, base, dir, post)
            self.data = post.data.clone
            self.data['layout'] = 'amp'
            @site = site
            @base = base
            @dir = dir
            @url = dir
            @name = 'index.html'
            self.process(@name)
            self.content = post.content
            self.data['body'] = (Liquid::Template.parse post.content).render site.site_payload
            # Merge all data from post so that keys from self.data have higher priority
            #self.data = post.data.merge(self.data)
            #self.data.delete('excerpt')
            #self.data.delete('permalink')
            self.data['canonical_url'] = post.url
        end
    end
    class AmpGenerator < Generator
        priority :low
        def generate(site)
            #layout = File.dirname(site.layouts['amp'].path)
            pages = Array.new
            site.pages.each do |post|
                next if post['layout'] != 'wiki'
                pages << AmpPost.new(site, site.source, File.join('amp', post['url']), post)
            end
            site.pages += pages
        end
    end
end
