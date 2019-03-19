require "nokogiri"
require "open-uri"
require "yaml"

class Attendance < Jekyll::Command
    @@twinks = {}
    @@twinks['Андрей'] = ['Оракин', 'Имрелихон', 'Минаскира', 'Яжматьепт', 'Ватгернн', 'Стикстень']
    @@twinks['Адептус'] = ['Адептуська', 'Адептукусь']
    @@twinks['Саша'] = ['Назири', 'Коррос']
    @@twinks['Макс'] = ['Ыгхан', 'Капуцын']
    @@twinks['Витя'] = ['Сумваера', 'Эсксель']
    @@twinks['Барсик'] = ['Бейбарсиков', 'Рансенваль']
    @@twinks['Эмиль'] = ['Вглазури', 'Хрер', 'Фуфия']
    @@twinks['Дима-Вездепых'] = ['Вездепых', 'Вритм']
    @@twinks['Димасик'] = ['Вайзище', 'Вайзё']
    class << self
        def init_with_program(prog)
            prog.command(:attendance) do |c|
                c.syntax "attendance [options]"
                c.description "Guild RT attendance"
                c.option "guild", "--guild ID", "Warcraft Logs guild id, eg: 374677"
                c.option "team", "--team ID", "Warcraft Logs team id, eg: 15620 (main), 15829 (static)"
                c.option "name", "--name NAME", "Report name, eg: main, static"
                c.action do |args, options|
                    # Grab data
                    url = 'https://www.warcraftlogs.com/guild/attendance-table/' + options['guild'] + '/' + options['team'] + '/0?page=1'
                    puts 'Grabbing data... for guild #' + options['guild'] + ' (team #' + options['team'] + ' - ' + options['name'] + ')'
                    data = {}
                    doc = Nokogiri::HTML(open(url), nil, Encoding::UTF_8.to_s).css("#attendance-table")
                    person = nil
                    doc.search('tr').each do |row|
                        i = 1
                        row.search('td').each do |cell|
                            if i == 1
                                @@twinks.each do |owner, names|
                                    person = cell.text.strip
                                    if names.include? cell.text.strip
                                        person = owner
                                        break
                                    end
                                end
                                if (data.key? person) === false
                                    data[person] = {}
                                end
                                data[person]['name'] = person
                                if (data[person].key? 'items') === false
                                    data[person]['items'] = {}
                                end
                            elsif i == 2
                                data[person]["att"] = cell.text.strip.gsub('%','')
                            else
                                if data[person]["items"][i] != 1
                                    data[person]["items"][i] = (cell.attr('class') == "present" ? 1 : 0)
                                end
                            end
                            i+=1
                        end
                    end
                    data = calcAttendance(data)
                    File.write('./_data/attendance_' + options['name'] + '.json', [data].to_json)
                end
            end
        end

        def calcAttendance(data)
            data.each do |person, info|
                absent = 0
                present = 0
                data[person]['items'].each do |index, value|
                    value == 1 ? present+=1 : absent+=1
                end
                if absent > 0 or present > 0
                    data[person]['att'] = format('%.1f', present.to_f / ((absent + present).to_f/100).to_f)
                else
                    data[person]['att'] = 0
                end
            end
            data
        end
    end
end
