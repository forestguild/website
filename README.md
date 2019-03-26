# Сайт гильдии "Ясный Лес" [![Build Status](https://gitlab.com/rakshazi/forestguild/badges/master/build.svg)](https://gitlab.com/rakshazi/forestguild)

[forestguild.club](https://forestguild.club)

Написан на [Jekyll](https://jekyllrb.com), [Gitlab CI](https://gitlab.com/rakshazi/forestguild).

### Оптимизация изображений

**Требования**

imagemagick, jpegtran

1. `mogrify -format jpg *.*` - конвертировать все изображения в формат jpeg (исходный не-jpeg можно удалять)
2. `find ./assets/img/ -name "*.jpg" -type f -exec jpegtran -copy none -optimize -progressive -outfile {} {} \;` - оптимизировать jpeg изображения
3. `mogrify -format webp *.*` - конвертировать все изображения в формат webp

> jpeg = для браузеров, без поддержки webp
