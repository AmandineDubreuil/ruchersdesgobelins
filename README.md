Licence : MIT

## Config 
PHP : 8.2
Symfony : 7.1.1

### Bundles
- easyadmin 4


## Tokens

Tokens générés via JWT pour la vérification de l'adresse e-mail et la modification du mot de passe

## User

### Vérification adresse e-mail user

- Services JWTService.php et SendMailService.php
- dans config/package/messenger commenter # Symfony\Component\Mailer\Messenger\SendEmailMessage: async
- dans parameters de config/services.yaml mettre : app.jwtsecret: '%env(JWT_SECRET)%'
- dans env.local mettre le secret JWT
- dans env.local modifier le mailer : MAILER_DSN=smtp://localhost:1025

- créer les templates d'e-mails dans templates/emails
- créer les routes 'app_verify_user' et 'app_resend_verif' dans SecurityController


### Modification mdp et mdp oublié user

- Services JWTService.php et SendMailService.php


## Contact Form

- création d'un DTO contact dans src/DTO/ContactDTO.php
- création d'un form dans src/Form/ContactType.php
- création d'un controller dans src/Controller/ContactController.php
- création d'un template dans template/contact/contact.html.twig
- création d'un template mail dans template/emails/contact_email.html.twig