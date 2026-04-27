<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Category;
use App\Form\CategoryType;

use App\Entity\PropertySearch;
use App\Entity\CategorySearch;
use App\Entity\PriceSearch;

use App\Form\PropertySearchType;
use App\Form\CategorySearchType;
use App\Form\PriceSearchType;

class IndexController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    // Liste des articles + recherche par nom
    #[Route('/', name: 'article_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $search = new PropertySearch();
        $form = $this->createForm(PropertySearchType::class, $search);
        $form->handleRequest($request);

        $repo = $this->entityManager->getRepository(Article::class);

        // si recherche envoyée
        if ($form->isSubmitted() && $form->isValid()) {
            $nom = $search->getNom();
            $articles = $repo->findBy(['nom' => $nom]);
        } else {
            $articles = $repo->findAll();
        }

        return $this->render('articles/index.html.twig', [
            'articles' => $articles,
            'form' => $form->createView(),
        ]);
    }

    // Ajouter un article
    #[Route('/article/new', name: 'article_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($article);
            $this->entityManager->flush();

            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form,
        ]);
    }

    // Afficher un article
    #[Route('/article/{id}', name: 'article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }

    // Modifier un article
    #[Route('/article/edit/{id}', name: 'article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }

        return $this->render('articles/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    // Supprimer un article
    #[Route('/article/delete/{id}', name: 'article_delete', methods: ['DELETE'])]
    public function delete(Request $request, Article $article): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($article);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('article_index');
    }

    // Ajouter une catégorie
    #[Route('/category/newCat', name: 'new_category')]
    public function newCategory(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('articles/newCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Recherche par catégorie
    #[Route('/article/search/category', name: 'article_search_category')]
    public function searchByCategory(Request $request): Response
    {
        $search = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class, $search);
        $form->handleRequest($request);

        $articles = [];

        // si recherche envoyée
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $search->getCategory();

            if ($category) {
                $articles = $this->entityManager
                    ->getRepository(Article::class)
                    ->findBy(['category' => $category]);
            }
        }

        return $this->render('articles/searchCategory.html.twig', [
            'articles' => $articles,
            'form' => $form->createView(),
        ]);
    }

    // Recherche par prix
    #[Route('/article/search/price', name: 'article_search_price')]
    public function searchByPrice(Request $request): Response
    {
        $search = new PriceSearch();
        $form = $this->createForm(PriceSearchType::class, $search);
        $form->handleRequest($request);

        $articles = $this->entityManager->getRepository(Article::class)->findAll();
        $filtered = [];

        // si recherche envoyée
        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($articles as $article) {
                $prix = $article->getPrix();

                $min = $search->getMinPrice();
                $max = $search->getMaxPrice();

                if (($min === null || $prix >= $min) &&
                    ($max === null || $prix <= $max)) {
                    $filtered[] = $article;
                }
            }

            $articles = $filtered;
        }

        return $this->render('articles/searchPrice.html.twig', [
            'articles' => $articles,
            'form' => $form->createView(),
        ]);
    }
}