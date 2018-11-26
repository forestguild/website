# Сайт гильдии "Ясный Лес" [![Build Status](https://gitlab.com/rakshazi/forestguild/badges/master/build.svg)](https://forestguild.club)

Написан на [Jekyll](https://jekyllrb.com), [PHP](https://php.net) и [Gitlab CI](https://gitlab.com/rakshazi/forestguild).

Внутри:

* База знаний
* Новости
* Информация о событиях (РТ)
* Автоапдрейтер прогресса гильдии и персонажей на [Raider.IO](https://raider.io) и [WoWProgress](https://wowprogress.com)
* Зал славы (текущий рейдовый прогресс гильдии)

### Оптимизация изображений

**Требования**

imagemagick, jpegtran

1. `mogrify -format jpg *.*` - конвертировать все изображения в формат jpeg (исходный не-jpeg) можно удалять)
2. `find ./assets/img/ -name "*.jpg" -type f -exec jpegtran -copy none -optimize -progressive -outfile {} {} \;` - оптимизировать jpeg изображения
3. `mogrify -format webp *.*` - конвертировать все изображения в формат webp

> jpeg = для браузеров, без поддержки webp
