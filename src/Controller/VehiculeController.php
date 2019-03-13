<?php

namespace App\Controller;

use App\Entity\Abonnes;
use App\Entity\DetailsVoyage;
use App\Entity\Vehicule;
use App\Entity\Voyage;
use App\Form\DetailsVoyageType;
use App\Form\VehiculeFormType;
use App\Form\VoyageType;
use App\Repository\VehiculeRepository;
use App\Repository\VoyageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VehiculeController extends AbstractController
{
    /**
     * @var ObjectManager
     */
    private $manager;
    /**
     * @var VehiculeRepository
     */
    private $repo;
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    private $voyageRepo;
    private $logged;
    public function __construct(ObjectManager $manager, VehiculeRepository $repo, PaginatorInterface $paginator, GetAuthentificateUser $logged, VoyageRepository $voyageRepository)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->paginator = $paginator;
        $this->logged = $logged;
        $this->voyageRepo = $voyageRepository;
    }

    /**
     * @Route("/vehicule", name="create.vehicule")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        if ($this->logged->getLoggedUser() == null)
            return $this->redirectToRoute('login');
        $vh = new Vehicule();
        $dtVg = new DetailsVoyage();
        $ab = $this->getUser();
        $abonne = new Abonnes();
        $nbre = count($ab->getListeVehicule());
        $form = $this->createForm(VehiculeFormType::class,$vh);
        $vg = new Voyage();
        $formVehicule = $this->createForm(VoyageType::class,$vg);
        $formDetail = $this->createForm(DetailsVoyageType::class,$dtVg);
        $form->handleRequest($request);
        $formVehicule->handleRequest($request);
        $sub_error = array();
        $sub_error['matricule'] = "";
        if ($form->isSubmitted()){
            $last = $this->repo->findBy([
                'matricule' => $vh->getMatricule()
            ]);
            if ($last != null){
                $sub_error['matricule'] = "Ce matricule existe dejà";
            }
            if ($form->isValid()){
                $nbre = \count($ab->getListeVehicule());
                if ($nbre < 3)
                {
                    $vh->setIdAbonne($this->getUser());
                    $vh->setCreatAt(new \DateTime('now'));
                    $this->manager->persist($vh);
                    $this->manager->flush();
                    return $this->redirectToRoute('create.vehicule');
                }
                else{
                    $this->addFlash('danger','Vous avez atteint le nombre maximun de vehicule gratuit');
                    return $this->redirectToRoute('abonnement');
                }
            }
        }
        if ($formVehicule->isSubmitted() && $formVehicule->isValid()){
            $lastVoyage = $this->voyageRepo->findVoyageActif($this->repo->find($_POST['idVehicule']));
            foreach ($lastVoyage as $vyg){
                if ($vyg != null){
                    $vyg->setStatus(0);
                    $this->manager->persist($vyg);
                    $this->manager->flush();
                }
            }
            $dt = new DetailsVoyage();
            $dt->setCharge(0);
            $dt->setDateDepart(new \DateTime("now"));
            $dt->setDecharge(0);
            $dt->setIdVoyage($vg);
            $dt->setPosition(true);
            $dt->setVille($vg->getVilledepart());
            $this->manager->persist($dt);
            $vg->setIdVehicule($this->repo->find($_POST['idVehicule']));
            $this->manager->persist($vg);
            $this->manager->flush();
        }
        $listPagine = $this->paginator($request,$ab->getId());
        return $this->render('vehicule/create.html.twig',[
            'form' => $form->createView(),
            'user' => $ab,
            'abonne' => $abonne,
            'listePagine' => $listPagine,
            'formVehicule' =>$formVehicule->createView(),
            'formDetail' =>$formDetail->createView(),
            'nombre' => $nbre
        ]);
    }

    /**
     * @Route("/vehicule.edit/{id}", name="edit.vehicule")
     * @param Request $request
     * @param Vehicule $vehicule
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request , Vehicule $vehicule)
    {
        if ($this->logged->getLoggedUser() == null)
            return $this->redirectToRoute('login');
        $ab = $this->getUser();
        $form = $this->createForm(VehiculeFormType::class,$vehicule);
        $vg = new Voyage();
        $formVehicule = $this->createForm(VoyageType::class,$vg);
        $vg = new Voyage();
        $dtVg = new DetailsVoyage();
        $formVehicule = $this->createForm(VoyageType::class,$vg);
        $formDetail = $this->createForm(DetailsVoyageType::class,$dtVg);
        $form->handleRequest($request);
        if ($form->isSubmitted()){
            if ($form->isValid()){
                $vehicule->setIdAbonne($this->getUser());
                $this->manager->persist($vehicule);
                $this->manager->flush();
                return $this->redirectToRoute('create.vehicule');
            }
        }
        $listPagine = $this->paginator($request,$ab->getId());
        return $this->render('vehicule/create.html.twig',[
            'form' => $form->createView(),
            'user' => $ab,
            'listePagine'=> $listPagine,
            'formVehicule' => $formVehicule->createView(),
            'formDetail' => $formDetail->createView(),

        ]);
    }
    /**
     * @Route("/gestion/vehicule", name="index.vehicule")
     */
    public function index()
    {
        if ($this->logged->getLoggedUser() == null)
            return $this->redirectToRoute('login');
        return $this->render('vehicule/index.html.twig');
    }

    /**
     * @Route("/vehicule.delete/{id}", name="delete.vehicule")
     * @param Vehicule $vehicule
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Vehicule $vehicule)
    {
        if ($this->logged->getLoggedUser() == null)
            return $this->redirectToRoute('login');
        $this->manager->remove($vehicule);
        $this->manager->flush();
        return $this->redirectToRoute('create.vehicule');
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    private function paginator(Request $request,$id)
    {
        $pagination = $this->paginator->paginate(
            $this->repo->findAllVehiculeQuery($id),
            $request->query->getInt('page',1),
            5
            );
        return $pagination;
    }

}
