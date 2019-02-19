require 'date'
require 'chronic'

module Jekyll
    module MythicHelper
        def mythicCurrentWeekNumber(input)
            initWeek = @context.registers[:site].data['affixes']['time']['init'] #First week when mythic+ epoch started
            weekOffset = 0
            nowString = Time.now
            now = nowString.to_i
            startWeek = ((Chronic.parse("this Tuesday", :now => nowString).to_date - 6).to_time + (9 * 60 * 60)).to_i #M+ week start (eg: 2018-11-21 09:00:00)
            endWeek = (Chronic.parse("this Tuesday", :now => nowString).to_time + 20 * 60 * 60 + 59 * 60 + 59).to_i #M+ week end (eg: 2018-11-28 08:59:59)
            unless (now >= startWeek && now <= endWeek)
                weekOffset -= 1
            end
            initWeek = Time.at(initWeek).to_datetime
            thisWeek = Time.at(startWeek).to_datetime

            ((thisWeek - initWeek).to_i / 7 + weekOffset).floor
        end

        # Get array of m+ affixes
        def mythicAffixes(input)
            # Set correct week number, based on input
            if input == 'next'
                weekNumber = mythicCurrentWeekNumber(0).to_i + 1
            elsif input == 'previous'
                weekNumber = mythicCurrentWeekNumber(0).to_i - 1
            else
                weekNumber = mythicCurrentWeekNumber(0).to_i
            end
            # Get Week number with world delay
            weekNumber = weekNumber + @context.registers[:site].data['affixes']['time']['delay'].to_i
            # Get whole m+ affixes size
            cycleSize = @context.registers[:site].data['affixes']['cycle'].length
            # Calculate current turn
            turn = weekNumber % cycleSize

            @context.registers[:site].data['affixes']['cycle'][turn]
        end
    end
end

Liquid::Template.register_filter(Jekyll::MythicHelper)
