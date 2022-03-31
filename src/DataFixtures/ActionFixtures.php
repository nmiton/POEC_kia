<?php

namespace App\DataFixtures;

use App\Entity\Action;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class ActionFixtures extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $actionSortirParc = new Action();
        $actionSortirParc->setName("Sortir le chien au parc");
        $actionSortirParc->setType("SORTIR");
        $actionSortirParc->setConsoleLog("Vous promenez votre chienne, et elle adore ça!");
        $actionSortirParc->setAnimalType($this->getReference('typeAnimalChien'));
        $manager->persist($actionSortirParc);
        $this->addReference('actionSortirParc', $actionSortirParc);

        $jouerBalleTennis = new Action();
        $jouerBalleTennis->setName("Jouer à la balle de tennis");
        $jouerBalleTennis->setType("JOUER");
        $jouerBalleTennis->setConsoleLog(" vous ramène la balle");
        $jouerBalleTennis->setAnimalType($this->getReference('typeAnimalChien'));
        $manager->persist($jouerBalleTennis);
        $this->addReference('jouerBalleTennis', $jouerBalleTennis);

        $nourrirCroquette = new Action();
        $nourrirCroquette->setName("Nourrir avec des croquettes");
        $nourrirCroquette->setType("NOURRIR");
        $nourrirCroquette->setConsoleLog(" se régale");
        $nourrirCroquette->setAnimalType($this->getReference('typeAnimalChien'));
        $manager->persist($nourrirCroquette);
        $this->addReference('nourrirCroquette', $nourrirCroquette);

        $remplirGamelle = new Action();
        $remplirGamelle->setName("Remplir la gamelle d'eau");
        $remplirGamelle->setType("BOIRE");
        $remplirGamelle->setConsoleLog("Vous en avez mis partout");
        $remplirGamelle->setAnimalType($this->getReference('typeAnimalChien'));
        $manager->persist($remplirGamelle);
        $this->addReference('remplirGamelle', $remplirGamelle);

        $soinNiv1 = new Action();
        $soinNiv1->setName("Soigner des petits bobos");
        $soinNiv1->setType("SOIN");
        $soinNiv1->setConsoleLog("Vous faites vos meilleurs pansements");
        $soinNiv1->setAnimalType($this->getReference('typeAnimalChien'));
        $manager->persist($soinNiv1);
        $this->addReference('soinNiv1', $soinNiv1);

        $manager->flush();
    }

    public function getOrder(){         
        return 9;     
    }
}
