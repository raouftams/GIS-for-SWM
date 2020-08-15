<?php

namespace App\Controller\User;

use App\Controller\AppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController{

    protected $encoder;

  public function __construct(UserPasswordEncoderInterface $encoder){
   // parent::__construct();
    $this->app = new AppController();
    $this->app->loadModel('Equipe');
    $this->encoder = $encoder;
    
  }


    /**
    * @Route("/admin/users/", name="addUser")
    */
    public function addUser(){
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        $equipes = $this->app->Equipe->all();
    	if (!empty($_POST)) {
            $manager = $this->getDoctrine()->getManager();
            $username = $_POST['username'];
    		$password = $_POST['password'];
            $role = $_POST['role'];
            $equipe =  intval($_POST['id_equipe']);
            
            if ($role == 'Administrateur') {
                $roles = ['ROLE_ADMIN','ROLE_SUPERUSER','ROLE_USER'];
            }else{
                if ($role == 'Planificateur') {
                    $roles = ['ROLE_SUPERUSER','ROLE_USER'];
                }else{
                    $roles = ['ROLE_USER'];
                }
            }

            $user = new User();
            $user->setUsername($username);
            $user->setPassword($this->encoder->encodePassword($user, $password));
            $user->setRoles($roles);
            $user->setIdEquipe($equipe);
            $manager->persist($user);
            $manager->flush();
            $users = $this->getDoctrine()->getRepository(User::class)->findAll();
            return $this->render('admin/addUser.html.twig', [
                "result" => "Utilisateur ajouté avec succes.",
                "users" => $users,
                "equipes" => $equipes
            ]);
        }
        
        return $this->render('admin/addUser.html.twig', [
            "result" => null,
            "users" => $users,
            "equipes" => $equipes
        ]);
    }

    /**
     * @Route("admin/users/deleteUser/{id}", methods={"POST","GET"}, name="deleteUser")
     */
    public function deleteUser($id){
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($user);
        $manager->flush();

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        $equipes = $this->app->Equipe->all();
        
        return $this->render('admin/addUser.html.twig', [
            "result" => "Utilisateur supprimé avec succes",
            "users" => $users,
            "equipes" => $equipes
        ]);
        
    }
}
?>