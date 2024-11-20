## url of project http://dev.symfony-6-testing-project.com:84/
##mysql version=8.0.28
##docker composer=1.29.2
##docker=27.3.1
##apache=2.4.58
##ubuntu=24.04 LTS
##php=8.3.6(cli)
##http://dev.symfony-6-testing-project.com:84
##localhost:8080,supposed to use docker's base
##entry directory of project then docker-compose exec db bash ,after
##mysql, adn create database ...
##route is /var/www/html/projekat3, then depends on what you are doing 
##going into db part of docker container to make base, using docker-compose exec db bash 
##and then mysql and then u are inside, create database project3... exit; , exit;
##rules:everything in English, after uploading on github you need to delete source branch,in this case dd4
##when you want to do migrations you need to write down docker-compose exec engine sh,
##(Here is must bin/console its not possible with symfony console)bin/console doctrine:migrations:migrate --no-interaction( This version of the command automatically executes ##all migrations without requiring additional input, such as confirmation with yes/no)
##/var/www/html/projekat3$ ls -ld public/uploads
##drwxrwxr-x 2 danilo-dasic danilo-dasic 4096 Nov 18 14:15 public/##uploads
##danilo-dasic@danilo-dasic-Latitude-6430U:/var/www/html/projekat3$ 
##when you are changing docker container and you are using build do ##that with  build --no-cache, cause docker uses cache to speed up ##things
##i put permission 777 on public/uploads/profile_pictures (thats permission which allows to everyone everything)