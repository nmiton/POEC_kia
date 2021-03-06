<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Repository\ActionCaracteristicRepository;
use App\Repository\ActionObjectsRepository;
use App\Repository\ActionRepository;
use App\Repository\AnimalCaracteristicRepository;
use App\Repository\CaracteristicRepository;
use App\Repository\InventoryRepository;
use App\Repository\UserRepository;
use App\Service\PayDay;
use App\Service\UpdateCaracteristic;
use App\Service\UpdateCaracteristicAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayController extends AbstractController
{
    //root de la page principale du jeu
    #[Route('/jouer/{id}', name: 'app_play', methods: ['GET'])]
    public function play(Animal $animal,AnimalCaracteristicRepository $animalStatsRepo,UpdateCaracteristic $updateCaracteristic, ActionRepository $repoAction,UserRepository $repoUser): Response
    {   
        //si user est non connecté
        if(!$this->getUser()){
            return $this->render('home/home.html.twig');   
        }else{
            //si l'animal (id URL) n'appartient pas au user actuel
            if($animal->getUser()->getId() != $this->getUser()->getId() || !$animal->getIsAlive() ){
                return $this->redirectToRoute('app_play_preload');
            }else{
                //Maj des stats de l'animal en fonction de lastAction
                $animalsMorts = $updateCaracteristic->updateCaract($this->getUser());
                if($animalsMorts != []){
                    return $this->redirectToRoute('app_new_animal',[
                        'animalsMorts' => $animalsMorts
                    ]);
                }
                //on gère la paye de l'utilisateur
                $payDay = new PayDay();
                //on récupère la somme de la paye
                $value_paye = $payDay->jourDePaye($this->getUser(), $repoUser);
                if ($value_paye!="-1") {
                    //on gère la notification de paye
                    $msg_paye = "Vous venez de recevoir votre paye quotidienne!";
                }else{
                    //on mets nos variables a null pr le traitement twig
                    $msg_paye= null;
                    $value_paye = null;
                }
                //affichage du tableau des animaux vivant du joueur
                $typesActionTypeAnimal = $repoAction->findTypeActionByAnimalType($animal->getAnimalType()->getId());
                //récupération des stats des animaux
                $stats = $animalStatsRepo->findBy(['animal'=>$animal->getId()]);
                
                return $this->render('play/main.html.twig', [
                    'animal' => $animal,
                    'stats' => $stats,
                    'typesAction' => $typesActionTypeAnimal,
                    'actions' => null,
                    'msg_danger' => null,
                    'msg_paye' => $msg_paye,
                    'value_paye'=> $value_paye,
                ]);   
            }
        }
    }

    #[Route('/jouer/{id}/{typeAction}/' , name: 'app_play_type_action', methods : ['POST'])]
    public function selectTypeAction(Animal $animal, $typeAction,UpdateCaracteristic $updateCaracteristic,AnimalCaracteristicRepository $animalStatsRepo, ActionRepository $repoAction):Response
    {   
        //récupération des stats de l'animal
        $stats = $animalStatsRepo->findBy(['animal'=>$animal->getId()]);
        //récupération de tous les types d'action par type d'animaux
        $typesActionTypeAnimal = $repoAction->findTypeActionByAnimalType($animal->getAnimalType()->getId());
        //récupération des toutes les actions par type d'action et par type d'animaux
        $actions_type_action_type_animal = $repoAction->findByActionsAnimalTypeAndActionTypeWhereObjectsInInventory($animal->getAnimalType()->getId(),$typeAction,$this->getUser()->getId());
        //récupération des stats de chaq action par type d'action et type d'animaux
        $stats_actions_type_action_type_animal = $repoAction->findByStatsActionsByAnimalTypeAndActionType($animal->getAnimalType()->getId(),$typeAction);
        // dd($actions_type_action_type_animal);
        return $this->render('play/main.html.twig', [
            'animal' => $animal,
            'stats' => $stats,
            'typeActionChoisi'=>$typeAction,
            'typesAction' => $typesActionTypeAnimal,
            'actions' => $actions_type_action_type_animal,
            'stats_actions' => $stats_actions_type_action_type_animal,
            'msg_danger' => null,
            'msg_paye' => null,
            'value_paye'=> null,
        ]);           
    }

    #[Route('/jouer/{id}/action/{idAction}/' , name: 'app_play_make_action', methods : ['POST'])]
    public function makeAction(
        $idAction,
        Animal $animal,
        ActionCaracteristicRepository $actionCaracRepo,
        AnimalCaracteristicRepository $animalCaracRepo,
        ActionObjectsRepository $actionObjetRepo,
        CaracteristicRepository $caracRepo,
        ActionRepository $actionRepo,
        UpdateCaracteristic $updateCaracteristic,
        UpdateCaracteristicAction $updateCaracteristicAction,
        InventoryRepository $inventoryRepo):Response
    {   
        //Maj des stats de l'animal en fonction de lastAction
        $animalsMorts = $updateCaracteristic->updateCaract($this->getUser());
        if($animalsMorts != []){
            return $this->redirectToRoute('app_new_animal',[
                'animalsMorts' => $animalsMorts
            ]);
        }
        //on récupère l'action grâce à son id
        $action = $actionRepo->find(['id' => $idAction]);
        //MAJ Console 
        //on récupère le console log de l'action 
        $console = $action->getConsoleLog();
        //MAJ STATS
        //stats de l'animal avant l'action 
        $stats = $animalCaracRepo->findBy(['animal'=>$animal->getId()]);           
        //récupération de tous les types d'action par type d'animaux
        $typesActionTypeAnimal = $actionRepo->findTypeActionByAnimalType($animal->getAnimalType()->getId());
        //on récupère l'entity nourriture
        $entity_stat_nourriture = $caracRepo->findOneBy(['name'=> "Nourriture"]);
        //on récupère l'entity énergie
        $entity_stat_energie = $caracRepo->findOneBy(['name'=> "Énergie"]);
        //on récupère l'entity hydratation
        $entity_stat_eau = $caracRepo->findOneBy(['name'=> "Hydratation"]);
        //on cherche l'hydratation de l'animal
        $stat_eau_animal = $animalCaracRepo->findOneBy(["animal" => $animal, "caracteristic" => $entity_stat_eau ]);
        //on cherche la nourriture de l'animal
        $stat_nouriture_animal = $animalCaracRepo->findOneBy(["animal" => $animal, "caracteristic" => $entity_stat_nourriture ]);
        //on cherche l'energie de l'animal
        $stat_energie_animal = $animalCaracRepo->findOneBy(["caracteristic"=> $entity_stat_energie, "animal"=> $animal ]);
        //condition pour les actions sortir et jouer
        if($action->getType() == "Jouer" || $action->getType() == "Sortir"){
            //erreur si énergie est <= 10 (pas de promenade, ni de jeu) 
            if($stat_energie_animal->getValue()<=10){
                return $this->render('play/main.html.twig', [
                'animal' => $animal,
                'stats' => $stats,
                'typesAction' => $typesActionTypeAnimal,
                'actions' => null,
                'msg_danger' => "Attention l'énergie de votre animal est trop faible 
                                    pour pouvoir : ".$action->getName().". Veuillez 
                                    faire le nécessaire afin d'augmenter 
                                    cette statistique!",
                ]); 
            }
        }
        //Gestion des stats de l'animal en fonction de l'action choisie
        //récupération des stats de l'action 
        $statsSelectedAction = $actionCaracRepo->findBy(["action"=>$idAction]);
        //récupération des stats de l'animal 
        $valueStatAnimal = $animalCaracRepo->findBy(["animal" => $animal->getId()]);
        //on maj les stats de l'animal en fct de l'action choisie
        $updateCaracteristicAction->setCaractAnimalWithStatsSelectedAction($statsSelectedAction,$valueStatAnimal);
        
        //Calcul de l'énergie
        //on gère ici l'énergie de l'animal après avoir gérer les stats qui ont été affectées par l'action choisie        
        //on cherche la nouvelle valeur d'hydratation de l'animal
        $stat_eau_animal = $animalCaracRepo->findOneBy(["animal" => $animal, "caracteristic" => $entity_stat_eau ]);
        //on cherche la nouvelle valeur de la nourriture de l'animal
        $stat_nouriture_animal = $animalCaracRepo->findOneBy(["animal" => $animal, "caracteristic" => $entity_stat_nourriture ]);
        //on set la nouvelle valeur de energie avec (nourriture+hydaration)/2 
        $stat_energie_animal->setValue(($stat_nouriture_animal->getValue()+$stat_eau_animal->getValue())/2);
        //on persite dans la bdd
        $animalCaracRepo->add($stat_energie_animal);
        
        //Calcul de proba de perte de l'object utilisé pr l'action
        //on initialise la notification d'objet perdu 
        $objetPerdu = null;
        //on récupère la proba de l'objet lié à l'action
        $objet = $actionObjetRepo->findByActionIdLossPercentage($idAction);
        $proba = $objet[0]["loss_percentage"];
        $idObjet = $objet[0]["id"];
        //on génère un int random entre 0 et 100 
        $random_proba_perte = random_int(0, 100);
        //on regarde l'inventaire de l'utilisateur en fonction de l'objet de l'action sélectionné
        $inventory = $inventoryRepo ->findOneBy(['objet' => $idObjet,'user' => $this->getUser()->getId()]);
        //si le random est inf a la proba de l'objet
        if($random_proba_perte<=$proba){
            $inventoryRepo->remove($inventory);
            if($proba < 100){
                $objetPerdu = "Vous venez de perdre votre ".$objet[0]["name"].".";
            }
        }
        //récuperation des stats de l'animal
        $stats = $animalCaracRepo->findBy(['animal'=>$animal->getId()]);    
        return $this->render('play/main.html.twig', [
            'animal' => $animal,
            'stats' => $stats,
            'console' => $console,
            'typesAction' => $typesActionTypeAnimal,
            'actions' => null,
            'stats_actions' => null,
            'msg_danger' => $objetPerdu,
        ]);           
    }
}
