require "nokogiri"
require "open-uri"
require "yaml"

class Wowdaily < Jekyll::Command
    class << self
        def init_with_program(prog)
            prog.command(:wowdaily) do |c|
                c.syntax "wowdaily [options]"
                c.description "Grab WoW Daily info from wowhead"
                c.action do |args, options|
                    # Init empty
                    worldboss = {}
                    wowtoken = 0
                    holidays = []
                    islandexpeditions = []
                    emissares = []

                    # Grab data
                    puts "Grabbing data..."
                    doc = Nokogiri::HTML(open("https://ru.wowhead.com/")).css(".tiw-region-EU")

                    # Parse it
                    puts "Parsing..."
                    doc.css(".tiw-group-epiceliteworldbfa").css(".icon-both").css("a").each do |a|
                        worldboss = {"name": a.text,"url": "https://ru.wowhead.com" + a.get_attribute("href")}
                    end
                    doc.css(".tiw-group-wowtoken").css(".moneygold").each do |gold|
                        wowtoken = gold.text.gsub(",","").to_i
                    end
                    doc.css(".tiw-group-holiday").css("td.icon-both").css("a").each do |a|
                        holiday = {"name" => a.text, "url" => "https://ru.wowhead.com" + a.get_attribute("href")}
                        holidays.push(holiday)
                    end
                    doc.css(".tiw-group-islandexpeditions").css("td.icon-both").css("a").each do |a|
                        expedition = {"name" => a.text, "url" => "https://ru.wowhead.com" + a.get_attribute("href")}
                        islandexpeditions.push(expedition)
                    end
                    doc.css(".tiw-group-emissary7").css("td.icon-horde").css("a").each do |a|
                        emissar = {"name" => a.text, "url" => "https://ru.wowhead.com" + a.get_attribute("href")}
                        emissares.push(emissar)
                    end
                    doc.css(".tiw-group-emissary7").css("td.icon-both").css("a").each do |a|
                        emissar = {"name" => a.text, "url" => "https://ru.wowhead.com" + a.get_attribute("href")}
                        emissares.push(emissar)
                    end
                    data = {"worldboss": worldboss, "wowtoken": wowtoken,"holidays": holidays,"islandexpeditions":islandexpeditions,"emissares":emissares}

                    File.write("./_data/wowdaily.json", data.to_json)
                end
            end
        end
    end
end
