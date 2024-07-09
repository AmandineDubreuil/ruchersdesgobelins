<?php

namespace App\Controller\Admin;

use App\Entity\Blog;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BlogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Blog::class;
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fas fa-plus')
                    ->setLabel('Ajouter un article');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fas fa-edit text-warning')
                    ->setLabel('Modifier');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fas fa-trash text-danger')
                    ->setLabel('Supprimer');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fas fa-eye text-info')
                    ->setLabel('Consulter');
                //  ->addCssClass('text-dark')
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fas fa-edit text-warning')
                    ->setLabel('Modifier');
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fas fa-trash text-danger')
                    ->setLabel('Supprimer');
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                return $action
                    ->setIcon('fa-solid fa-arrow-left')
                    ->setLabel('Retour');
            })
            ->reorder(Crud::PAGE_DETAIL, [Action::INDEX, Action::EDIT, Action::DELETE])

            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action
                    // ->setIcon('fa-solid fa-arrow-left')
                    ->setLabel('Enregistrer et continuer');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action
                    // ->setIcon('fa-solid fa-arrow-left')
                    ->setLabel('Enregistrer');
            })
            ->update(Crud::PAGE_EDIT, Action::INDEX, function (Action $action) {
                return $action
                    ->setIcon('fa-solid fa-arrow-left')
                    ->setLabel('Retour');
            })

            ->add(Crud::PAGE_NEW, Action::INDEX)

            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action
                    // ->setIcon('fa-solid fa-arrow-left')
                    ->setLabel('Créer et ajouter un nouveau');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action
                    // ->setIcon('fa-solid fa-arrow-left')
                    ->setLabel('Créer');
            })
            ->update(Crud::PAGE_NEW, Action::INDEX, function (Action $action) {
                return $action
                    ->setIcon('fa-solid fa-arrow-left')
                    ->setLabel('Retour');
            });
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Articles')
            ->setEntityLabelInSingular('Article')
            ->setPageTitle('index', 'Administration du Blog')
            ->setPageTitle('new', 'Nouvel Article')
            ->setPageTitle('edit', 'Modification d\'un Article');
    }

    public function configureFields(string $pageName): iterable
    {
        yield   IdField::new('id')
            ->hideOnForm()
            ->hideOnIndex()
            ->setFormTypeOption('disabled', 'disabled');
        yield TextField::new('titre');
        yield TextField::new('sousTitre')
            ->hideOnIndex();
        yield TextEditorField::new('article')
            ->hideOnIndex();
        yield ImageField::new('image')
            ->setBasePath('uploads/blog/')
            ->setUploadDir('public/uploads/blog/')
            ->setUploadedFileNamePattern('[year].[month].[day].[name]-[uuid].[extension]')
            ->setHelp('Pour la rapidité du site, privilégiez une image de taille inférieure à 500ko')
            // ->setFormTypeOptions(['required' => true])
        ;

        yield DateTimeField::new('createdAt')
            ->setLabel('Date de création')
            ->setTimezone('Europe/Paris')
            ->hideOnForm();

        /*
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    */
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Blog) return;
        $entityInstance->setCreatedAt(new \DateTimeImmutable);
        //   dd($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }
}
