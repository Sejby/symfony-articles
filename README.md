Před spuštěním:
- verze PHP 8.1.2
- composer install
- vytvořit např. v xamppu databázi symfony (nebo jinou, ale uvést ji v .env souboru)
- symfony console make:migration
- symfony console doctrine:migrations:migrate
- symfony server:start
- Emailová adresa při registraci musí být validní se @
- Heslo alespoň 6 znaků dlouhé
