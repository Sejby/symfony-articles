<?php

namespace App\Controller;
use App\Entity\Article;
use App\Form\ArticleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticlesController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        $articles = $this->em->getRepository(Article::class)->findAll();

        return $this->render('base.html.twig', [
            'articles' => $articles,
        ]);
    }


    #[Route('/articles', name: 'articles')]
    public function index(): Response
    {
        $articles = $this->em->getRepository(Article::class)->findAll();

        return $this->render('articles/articles.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/pridat-clanek', name: 'pridat_clanek')]
    public function createArticle(Request $request, #[Autowire('%photo_dir%')] string $photo_dir): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            if ($photo = $form['image']->getData()) {
                $filename = uniqid() . '.' . $photo->guessExtension();
                $photo->move($photo_dir, $filename);

                $imagine = new Imagine();
                $size = new Box(120, 90); // Nastavení požadované velikosti perexu
                $imagePath = $photo_dir . '/' . $filename;
                $perexFilename = 'thumb_' . $filename;

                // Vytvoření perexu (zmenšeného obrázku)
                $imagine->open($imagePath)
                        ->resize($size)
                        ->save($photo_dir . '/' . $perexFilename);

                // Nastavení cesty k obrázku a perexu v článku
                $article->setImage($filename);
                $article->setPerex($perexFilename);
            }

            $this->em->persist($article);
            $this->em->flush();

            $this->addFlash('message', 'Vložení článku úspěšné!');

            return $this->redirectToRoute('articles');
        }

        return $this->render('admin/add_post.html.twig', [
            'form' => $form->createView()
        ]);
    }


    // UDĚLAT POUZE JEDEN FORM PRO ADD A EDIT CLANKU

    #[Route('/upravit-clanek/{id}', name: 'upravit_clanek')]
    public function editArticle(Request $request, $id){
        $article = $this->em->getRepository(Article::class)->find($id);

        $form = $this->createForm(ArticleFormType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($article);
            $this->em->flush();

            $this->addFlash('message', 'Upravení článku úspěšné!');

            return $this->redirectToRoute('upravit_clanek', ['id' => $id]);
        }
        
        return $this->render('admin/edit_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/smazat-clanek/{id}', name: 'smazat_clanek')]
    public function deleteArticle(Request $request, $id){
        $article = $this->em->getRepository(Article::class)->find($id);

        $this->em->remove($article);
        $this->em->flush();

        $this->addFlash('message', 'Článek úspěšně smazán!');
        return $this->redirectToRoute('articles');
    }

}
