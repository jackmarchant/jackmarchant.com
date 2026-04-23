install: 
	composer install

up:
	composer start

build:
	composer install
	php build.php
	