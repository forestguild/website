REMOTE=git@github.com:rakshazi/forestguild.club.git

deploy: build push clean

run:
	find ./assets/img/ -name "*.jpg" -type f -exec jpegtran -copy none -optimize -progressive -outfile {} {} \;
	bundler exec jekyll serve

build:
	mkdir -p _data
	php updater.php update
	php updater.php collect
	find ./assets/img/ -name "*.jpg" -type f -exec jpegtran -copy none -optimize -progressive -outfile {} {} \;
	bundler exec jekyll build -q
	cp CNAME _site/CNAME

.ONESHELL:
push:
	cd _site
	rm -rf .git
	git init
	git remote add origin $(REMOTE)
	git add --all
	git commit -a -q -m Update
	git push --no-thin --force origin HEAD:gh-pages

clean:
	php updater.php cache
