# Projet Work Together

![Logo](assets/images/logo_WorkTogether.webp)

# MCD
![MCD_MLD](assets/images/MCD_MLD.png)


# Installation du projet
- composer install 
- yarn install

# Créer la base de données
- php bin/console doctrine:database:create

# Exécuter les migrations
- php bin/console doctrine:migrations:migrate 

# Compiler les assets avec Webpack Encore
- yarn encore dev --watch

# Démarrer le serveur 
- symfony server:start
