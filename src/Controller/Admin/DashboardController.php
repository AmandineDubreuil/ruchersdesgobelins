<?php

namespace App\Controller\Admin;

use App\Entity\Blog;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    #[Route('/admin', name: 'admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
       // return $this->render('admin/dashboard.html.twig');
  
       $url = $this->adminUrlGenerator
       ->setController(BlogCrudController::class)
       ->generateUrl();

       return $this->redirect($url);
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration du site Les Ruchers des Gobelins');
    }

    public function configureMenuItems(): iterable
    {
        //yield MenuItem::linkToDashboard('Accueil Admin', 'fa fa-home');
        yield MenuItem::linktoRoute('Les Ruchers des Gobelins', 'fas fa-home', 'app_home');
        yield MenuItem::section('Blog');
        yield MenuItem::subMenu('Actions', 'fas fa-bars')->setSubItems([
             MenuItem::linkToCrud('Ajout Article', 'fas fa-plus', Blog::class)->setAction(Crud::PAGE_NEW),
             MenuItem::linkToCrud('Consulter Articles', 'fas fa-eye', Blog::class)
        ]);
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
