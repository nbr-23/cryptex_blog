<?php

namespace App\Controller;


use App\Entity\Article;
use App\Entity\Comment;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ArticleType;
use App\Form\CommentType;
use Knp\Component\Pager\PaginatorInterface;

class DefaultController extends AbstractController
{
    /**
     * @Route("/article_list", name="default")
     */
    public function index(ArticleRepository $repo, Request $request, PaginatorInterface $paginator)
    {

        $articles = $repo->findAll();
        $articles = $paginator->paginate(
            $articles,
            $request->query->getInt('page', 1),
            4
        );
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'articles' => $articles
        ]);
        
    }

    /**
     * @Route("/", name="home")
     */
    public function home(ArticleRepository $repo)
    {
        $articles = $repo->setMax();
        return $this->render('default/home.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/blog/new", name="blog_create")
     * @ROute("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request)
    {

        $entityManager = $this->getDoctrine()->getManager();

        if (!$article) {
            $article = new Article();
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            if (!$article->getId()) {
                $article->setCreatedAt(new \DateTime());
            }
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        return $this->render('default/create.html.twig', [
            'formArticle' => $form->createView(),
            'editMode' => $article->getId() !== null
        ]);
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTime())
                ->setArticle($article);
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        return $this->render('default/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView()
        ]);
    }
}
