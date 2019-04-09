# Guild "Ясный Лес" website [![Build Status](https://gitlab.com/rakshazi/forestguild/badges/master/build.svg)](https://gitlab.com/rakshazi/forestguild/pipelines)

URL: [forestguild.club](https://forestguild.club)

### Image optimization

Requirements: imagemagick, jpegtran

1. `mogrify -format jpg *.*` - convert all to jpeg (original non-jpeg may be deleted)
2. `find ./assets/img/ -name "*.jpg" -type f -exec jpegtran -copy none -optimize -progressive -outfile {} {} \;` - optimize jpeg
3. `mogrify -format webp *.*` - convert to webp

> jpeg is required for browsers without webp support. [Check it here](https://caniuse.com/#feat=webp)
