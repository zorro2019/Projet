<?php

namespace App\Controller;

use App\Entity\Abonnes;
use App\Entity\Entreprise;
use App\Entity\GetAuthentificateUser;
use App\Form\FormAbonneType;
use App\Form\FormEntrepriseType;
use App\Repository\AbonnesRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\MessagesRepository;
use App\Repository\ReadingMessagesRepository;
use App\Repository\ZoneInterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\User;

class AbonneController extends AbstractController
{
    private $logged;
    /**
     * @var ObjectManager
     */
    private $manager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    /**
     * @var AbonnesRepository $repository
     */
    private $repository;
    /**
     * @var EntrepriseRepository
     */
    private $entrepriseRepo;
    /**
     * @var
     */
    private $messageRepo;

    private $readRepository;

    public function __construct(ObjectManager $manager, UserPasswordEncoderInterface $encoder,
                                AbonnesRepository $repository, EntrepriseRepository $entrepriseRepo,
                                MessagesRepository $messageRepo, \App\Controller\GetAuthentificateUser $logged,
            ReadingMessagesRepository $readRepository
    )
    {
        $this->manager = $manager;
        $this->encoder = $encoder;
        $this->repository = $repository;
        $this->entrepriseRepo = $entrepriseRepo;
        $this->messageRepo = $messageRepo;
        $this->logged = $logged;
        $this->readRepository = $readRepository;
    }

    /**
     * @Route(path="/mon_espace",name="user_espace")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        if ($this->logged->getLoggedUser() == null)
            return $this->redirectToRoute('login');
        $abonnes = $this->logged->getLoggedUser();
        $ab = new Abonnes();
        $abonnes->setNbreMessageInread(count($this->readRepository->findMessagesInread($abonnes->getId())));
        $lastMessage = $this->readRepository->findMessagesInread($abonnes->getId());
        dump($lastMessage);
        return $this->render('abonne/index.html.twig', [
            'user' => $abonnes,
            'messages' => $lastMessage,
            'abonnes' => $ab
        ]);
    }

    /**
     * @Route(path="/creer_compte",name="create_compte")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request,\Swift_Mailer $mailer)
    {
        $ab = new Abonnes();
        $form = $this->createForm(FormAbonneType::class, $ab);
        $form->handleRequest($request);
        $sub_error = array();
        $sub_error['tel'] = "";
        $sub_error['password'] = "";
        if ($form->isSubmitted()) {
            if ($ab->getPassword() != $ab->getPwd()){
                $sub_error['password'] = "erreur mot de passe";
            }
            if ($this->validateTel($ab->getTelephone() ) == false){
                $sub_error['tel'] = "Numero incorrect";
            }
            if ($sub_error['password'] == "" &&  $sub_error['tel'] == "") {
                if ($form->isValid()) {
                    $message = (new \Swift_Message('Hello Email'))
                        ->setFrom('yaranagoresekou@gmail.com')
                        ->setTo($ab->getEmail())
                        ->setBody("hello world")
                    ;
                    $mailer->send($message);
                    $ab->setPassword($this->encoder->encodePassword($ab, $ab->getPassword()));
                    $ab->setCreatedAt(new \DateTime('now'));
                    $this->manager->persist($ab);
                    $this->manager->flush();
                    $this->addFlash('success', 'votre compte a été créé avec succes');
                    $token = new UsernamePasswordToken($ab, null, 'main', ['ROLE_USER']);
                    $this->get('security.token_storage')->setToken($token);
                   if ($ab->getTypeAbonne() == 2)
                       return $this->redirectToRoute('abonne.create.entreprise');
                   else
                       return $this->redirectToRoute('user_espace');
                }
            }
        }
        return $this->render("abonne/create.html.twig", [
            'form' => $form->createView(),
            'sub_error' => $sub_error
        ]);
    }

    /**
     * @Route(path="/edit",name="abonnes.edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request)
    {
        if ($this->logged->getLoggedUser() == null)
            return $this->redirectToRoute('login');
        $abonnes = $this->logged->getLoggedUser();
        $form = $this->createForm(FormAbonneType::class, $abonnes);
        $form->handleRequest($request);
        $sub_error = array();
        $sub_error['image'] = "";
        $sub_error['tel'] = "";
        $sub_error['email'] = "";
        $sub_error['password'] = "";
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->validateImage($abonnes->getFile()) == false)
            {
                $sub_error['image'] = "type d'image non autorisé";
            }
            if ($this->validateTel($abonnes->getTelephone(),1) == false) {
                $sub_error['tel'] = "Veuillez choisir un bon numero";
            }
            if (empty($sub_error['tel']) && empty($sub_error['image'])) {
                $abonnes->setPassword($this->encoder->encodePassword($abonnes, $abonnes->getPassword()));
                $this->manager->persist($abonnes);
                $this->manager->flush();
                $this->addFlash('succes', 'Modification effectuée aves succès');
                return $this->redirectToRoute('user_espace');
            }
        }
        return $this->render("abonne/edit.html.twig", [
            'form' => $form->createView(),
            'sub_error' => $sub_error
        ]);
    }

    /**
     * @Route(path="/create_entreprise",name="abonne.create.entreprise")
     * @param ZoneInterventionRepository $repository
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create_entreprise(ZoneInterventionRepository $repository, Request $request)
    {
        if ($this->logged->getLoggedUser() == null)
            return $this->redirectToRoute('login');
        $ent = new Entreprise();
        $form = $this->createForm(FormEntrepriseType::class, $ent);
        $form->handleRequest($request);
        $errors = array();
        $errors['ninea'] = "";
        $errors['logo'] = "";
        $errors['tel'] = "";
        $errors['email'] = "";
        if ($form->isSubmitted()) {
            $last = $this->entrepriseRepo->findOneBy([
                'ninea'=> $ent->getNinea()
            ]);

            if ($this->validateTel($ent->getTel()) == false) {
                $errors['tel'] = "Ce format est incorrect";
            }
            if ($last != null && 10 >= $last->getNbreAbonne()) {
                $errors['ninea'] = "Cette entreprise a plus de 10 utilisateurs";
            }

            if (!empty($ent->getImageLogo()) && $this->validateImage($ent->getImageLogo()->getMimeType()) == false) {
                $errors['logo'] = "Format d'image non autorisé";
            }

            if (empty($errors['tel']) && empty($errors['logo']))
            {
                if ($form->isValid()){
                    /** @var TYPE_NAME $ninea */
                    if ($last == null){
                        $this->manager->persist($ent);
                        $ent->setCreateAt(new \DateTime('now'));
                        $user = $this->getUser();
                        $user->setIdEntreprise($ent);
                        $this->manager->persist($user);
                        foreach ($_POST['zone'] as $value)
                        {
                            $ent->addListeZone($repository->find(($value)));
                        }
                        $ent->setNbreAbonne(1);
                        $this->manager->persist($ent);
                        $this->manager->flush();
                        return $this->redirectToRoute("user_espace");
                    }
                    if ($ninea != null && $ninea->getNbreAbonne() < 10){
                        $user = $this->getUser();
                        $user->setIdEntreprise($last);
                        $last->setNbreAbonne($last->getNbreAbonne()+1);
                        $this->manager->persist($last);
                        $this->manager->persist($user);
                        $this->manager->flush();
                        return $this->redirectToRoute("user_espace");
                    }
                }
            }
        }
        return $this->render('abonne/create_entreprise.html.twig', [
            'form' => $form->createView(),
            'zones' => $repository->findAll(),
            'sub_error' =>$errors
        ]);
    }

    /**
     * @param $tel
     * @return bool
     */
    private function validateTel($tel)
    {
        if ($tel[0] == '7'){
            if ($tel[1] = '7' || $tel[1] == '8' ||$tel[1] == '0' || $tel[1] == '6'){
                return true;
            }
        }
        else
            return false;
    }

}