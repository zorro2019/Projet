<?php
namespace App\Controller;

use App\Entity\Abonnes;
use App\Entity\Rechercher;
use App\Form\LoginAbonnesType;
use App\Form\RechercherType;
use App\Repository\MessagesRepository;
use App\Repository\VoyageRepository;
use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class HomeController extends AbstractController
{
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var MessagesRepository
     */
    private $messagesRepository;
    private $paginator ;

    /**
     * HomeController constructor.
     * @param Environment $twig
     * @param MessagesRepository $messagesRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(Environment $twig,MessagesRepository $messagesRepository, PaginatorInterface $paginator)
    {
        $this->twig = $twig;
        $this->messagesRepository = $messagesRepository;
        $this->paginator = $paginator;
    }

    /**
     * @Route(path="/",name="home")
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index():Response
    {
        return new Response($this->twig->render('pages/essai.html.twig'));
    }

    /**
     * @Route(path="/comment_ça_marche",name="faq")
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function comment():Response
    {
        return new Response($this->twig->render('pages/faq.html.twig'));
    }

    /**
     * @Route(path="/fret-en-direct", name="fret.direct")
     * @param Request $request
     * @param VoyageRepository $repository
     * @return Response
     */
    public function fret(Request $request, VoyageRepository $repository)
    {
        $search = new  Rechercher();
        $voyage = $repository->findListVoyageActif();
        $form = $this->createForm(RechercherType::class , $search);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){

        }
        return $this->render('pages/fret-en-direct.html.twig',[
            'form' => $form->createView(),
            'voyages' => $voyage
        ]);
    }

    /**
     * @Route(path="/alert-fret",name="alert.fret")
     * @param Request $request
     * @return Response
     */
    public function Alert(Request $request)
    {
        $user = $this->getUser();
        $listPagine = $this->paginator($request);
        return $this->render('pages/alert-fret.html.twig',[
            'listePagine' => $listPagine,
            'user' => $user
        ]);
    }

    /**
     * @param Request $request
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    private function paginator(Request $request)
    {
        $pagination = $this->paginator->paginate(
            $this->messagesRepository->findLastMessageQuery(),
            $request->query->getInt('page',1),
            4
        );
        return $pagination;
    }
}