REMOTE=git@github.com:rakshazi/forestguild.club.git

deploy: build push clean

run:
	bundler exec jekyll serve

build:
	mkdir -p _data
	bundler exec jekyll wowdaily
	bundler exec jekyll updateprogress --region eu --realm галакронд --realm-id 607 --guild "Ясный Лес"
	bundler exec jekyll build
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
