# UltimateBravery Dofus 🥚

![SYMFONY](https://img.shields.io/badge/Framework-Symfony-purple)

![PHP](https://img.shields.io/badge/Langage-Php-blue)

This project is an api made for dofus player who are looking for a new challenge.

## Environnement 

Create a file .env.local and fill it with the data from .env
Replace the informations inside with your owns.

Create a directory called 'jwt' inside the config one, then run the command :

``php bin/console lexik:jwt:generate-keypair``


## Installation 

Clone the repository

Move inside it

Run the following commands :

``composer install``

``npm install``

``php bin/console make:database``

``php bin/console d:s:u``

``php bin/console d:f:l``

``symfony serve``

``npm run build``
