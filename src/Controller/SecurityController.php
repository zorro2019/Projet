<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route(path="/login",name="login")
     * @param AuthenticationUtils $utils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $lastEmail = $utils->getLastUsername();
        $error = $utils->getLastAuthenticationError();
        return $this->render("security/login.html.twig", [
            'email' => $lastEmail,
            'error' => $error
        ]);
    }

    /**
     * @Route(path="/logout",name="logout")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logout()
    {
        return $this->render('pages/index.html.twig');
    }
}
