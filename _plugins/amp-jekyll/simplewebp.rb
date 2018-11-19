require 'nokogiri'

module Jekyll
  module WebpFilter
    def webp_images(input)
      doc = Nokogiri::HTML.fragment(input);
      # Find <img>
      # Create new element <noscript data-webp></noscript>
      # Copy <img> inside <noscript>
      # Replace <img> in DOM with <noscript data-webp><img/></noscript>
      doc.css('img').each do |img|
        noscript = Nokogiri::XML::Node.new "noscript", doc
        noscript['data-webp'] =""
        noscript_img = img.dup
        noscript.add_child(noscript_img)
        img.replace noscript
      end

      # Return the html as plaintext string
      doc.to_s
    end
  end
end

Liquid::Template.register_filter(Jekyll::WebpFilter)
