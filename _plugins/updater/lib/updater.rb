require 'net/http'
require 'uri'
require 'open-uri'
require 'json'

class Updater < Jekyll::Command
    class << self
        def init_with_program(prog)
            prog.command(:updateprogress) do |c|
                c.syntax "updateprogress [options]"
                c.description "Update guild progress"
                c.option "region", "--region REGION", "WoW Region, eg: eu"
                c.option "realm", "--realm REALM", "WoW translated Realm, eg: галакронд"
                c.option "realm-id", "--realm-id ID", "Raider.IO realm ID, eg: 607"
                c.option "guild", "--guild NAME", "Guild name, eg: Ясный Лес"
                c.action do |args, options|
                    #Raider.IO
                    rio = Net::HTTP.post(URI('https://raider.io/api/crawler/guilds'),
                                         {"realmId": options['realm-id'], "realm": options['realm'], "region": options['region'], "guild": options['guild'], "numMembers": "0"}.to_json,
                                         "Content-Type" => "application/json").body()
                    puts "Raider.IO: " + (JSON.parse(rio)['success'] == true ? "OK" : "FAIL")

                    #WoWProgress
                    wp_url = URI.parse(URI.encode('https://www.wowprogress.com/update_progress/guild/' + options['region'] + '/' + options['realm'] + '/' + options['guild']))
                    wp_url = get_final_url(wp_url, 'https://www.wowprogress.com')
                    wp_list = Nokogiri::HTML(open(URI.encode(wp_url)))
                    wp_chars = Array.new
                    wp_list.css('.char_chbx').each do |item|
                        wp_chars.push(item.get_attribute('id').gsub('check_',''))
                    end
                    wp = Net::HTTP.post_form(URI.parse(wp_url),{"submit": "1", "char_ids": wp_chars})
                    puts "WoWProgress: " + (JSON.parse(wp.body)['success'] == true ? "OK" : "FAIL")
                end
            end
        end
        def get_final_url(url, domain = '')
            r = Net::HTTP.get_response(url)
            location = r.header['location']
            if r.code == "301" or r.code == "302"
                location = r.header['location']
                unless location =~ /\Ahttp/i
                    location = domain + location
                end
                r = get_final_url(URI.parse(location), domain)
            else
                location = url.to_s
            end
            location
        end
    end
end
