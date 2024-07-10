<?php

namespace App\Controller;

use App\Repository\BlogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        BlogRepository $blogRepository
    ): Response
    {

// blog
$blogs = $blogRepository->findlastXArticles(4);
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'blogs' => $blogs,
        ]);
    }
}
