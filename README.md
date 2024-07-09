Licence : MIT

## Config 
PHP : 8.2
Symfony : 7.1.1

### Bundles
- stimulus-bundle
- easyadmin 4
- vich_uploader 2.4


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

### Photos du catalogue et des articles du blog

#### utilisation du bundle VichUploader pour le téléchargement des images

- dans parameters de config/services.yaml mettre :  
   - blog_images: '/assets/img/uploads/blog'
- dans config/packages/vich_uploader.yaml mettre les mappings :

mappings:
        blog_images:
            uri_prefix: '%blog_images%'
            upload_destination: '%kernel.project_dir%/public%blog_images%'
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer

            # sécurise la suppression des fichiers, à adapter au client
            delete_on_update: true
            delete_on_remove: true

- paramétrer les entity avec :
 use Vich\UploaderBundle\Mapping\Annotation as Vich; 
 
 #[ORM\Entity(repositoryClass: EquipesRepository::class)] 
 #[Vich\Uploadable]

//.... #[ORM\Column(length: 255, type:'string', nullable: true)]
  private ?string $image = null;

#[Vich\UploadableField(mapping: 'equipe_images', fileNameProperty: 'attachment')]
private ?File $attachmentFile = null;
           //...
- mettre les getter et setter :
 
 /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }


- dans les crud easyadmin, mettre dans configureFields :
yield TextField::new('imageFile')
            ->setLabel('Photo')
            ->setHelp('Attention, pour ne pas alourdir le site, l\'image ne doit pas dépasser 2MB, sinon elle n\'apparait pas.')
            ->setFormType(VichImageType::class)
            ->onlyOnForms();
        yield ImageField::new('image')
            ->hideOnForm()
            ->setLabel('Photo')
            ->setBasePath('assets/img/uploads/blog')
            ->setUploadDir('public/assets/img/uploads/blog');