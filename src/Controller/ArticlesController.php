<?php
namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticlesController extends AbstractController
{
    private $em;
    private $imageService;

    public function __construct(EntityManagerInterface $em, ImageService $imageService)
    {
        $this->em = $em;
        $this->imageService = $imageService;
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        $articles = $this->em->getRepository(Article::class)->findAll();

        return $this->render('base.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/clanky', name: 'clanky')]
    public function index(): Response
    {
        $articles = $this->em->getRepository(Article::class)->findAll();

        return $this->render('articles/articles.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/pridat-clanek', name: 'pridat_clanek')]
    public function createArticle(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            if ($photo = $form['image']->getData()) {
                $imageData = $this->imageService->processImage($photo);
                $article->setImage($imageData['filename']);
                $article->setPerex($imageData['perexFilename']);
            }

            $this->em->persist($article);
            $this->em->flush();

            $this->addFlash('message', 'Vložení článku úspěšné!');

            return $this->redirectToRoute('clanky');
        }

        return $this->render('admin/add_post.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/upravit-clanek/{id}', name: 'upravit_clanek')]
    public function editArticle(Request $request, $id): Response
    {
        $article = $this->em->getRepository(Article::class)->find($id);

        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            if ($photo = $form['image']->getData()) {
                $imageData = $this->imageService->processImage($photo);
                $article->setImage($imageData['filename']);
                $article->setPerex($imageData['perexFilename']);
            }

            $this->em->persist($article);
            $this->em->flush();

            $this->addFlash('message', 'Úprava článku úspěšná!');

            return $this->redirectToRoute('clanky');
        }

        return $this->render('admin/add_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/smazat-clanek/{id}', name: 'smazat_clanek')]
    public function deleteArticle($id): Response
    {
        $article = $this->em->getRepository(Article::class)->find($id);

        if ($article) {
            if ($article->getImage() && $article->getPerex()){
                $this->imageService->deleteImage($article->getImage());
                $this->imageService->deleteImage($article->getPerex());
        
            }
            
            $this->em->remove($article);
            $this->em->flush();

            $this->addFlash('message', 'Článek úspěšně smazán!');
        }

        return $this->redirectToRoute('clanky');
    }
}
