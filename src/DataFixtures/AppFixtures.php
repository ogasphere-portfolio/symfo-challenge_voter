<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Tag;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // On crée une instance de Faker en français
        $generator = Faker\Factory::create('fr_FR');

        // Roles
        $roles = [
            'admin' => 'Administrateur',
            'moderator' => 'Modérateur',
            'user' => 'Utilisateur',
        ];

        // Users : seront créés en dur pour pouvoir les manipuler en attendant le module de sécurité
        $userGroup = array(
            'admin' => ['claire', 'jc'],
            'moderator' => ['micheline', 'jeanette'],
            'user' => ['gertrude', 'roland', 'marc'],
        );
        $usersEntities = array();

        foreach($userGroup as $roleGroup => $users) {
            // Role
            $role = new Role();
            $role->setRoleString('ROLE_'.mb_strtoupper($roleGroup));
            $role->setName($roles[$roleGroup]);
            $manager->persist($role);

            print 'Adding role '.$role->getName().PHP_EOL;
            
            foreach($users as $u) {
                // New user based on list
                $user = new User();
                $user->setUsername(\ucfirst($u));
                $user->setPassword($this->encoder->encodePassword($user, $u)); // Le mot de passe est le nom de l'utilisateur
                $user->setEmail($u.'@faq.oclock.io');
                $user->setRole($role);
                // Add it to the list of entities
                $usersEntities[] = $user;
                // Persist
                $manager->persist($user);

                print 'Adding user '.$user->getUsername().PHP_EOL;
            }
        }

        // Tags
        $tagsEntities = array();

        for($i = 1; $i < 10; $i++) {

            $tag = new Tag();
            $tag->setName($generator->unique()->word());

            $manager->persist($tag);

            $tagsEntities[] = $tag;
        }

        // Questions
        $questionsEntities = array();

        for ($i = 1; $i < 30; $i++) {
            $question = new Question();
            $question->setTitle(rtrim($generator->unique()->sentence($nbWords = 9, $variableNbWords = true), '.') . ' ?');
            $question->setBody($generator->unique()->paragraph($nbSentences = 6, $variableNbSentences = true));
            $question->setCreatedAt($generator->unique()->dateTime($max = 'now', $timezone = null));
            $question->setVotes(0);
            $question->setUser($generator->randomElement($usersEntities));

            $manager->persist($question);

            $questionsEntities[] = $question;
        }

        // Answers
        $answersEntities = array();

        for ($i = 1; $i < 30; $i++) {
            $answer = new Answer();
            $answer->setBody($generator->unique()->paragraph($nbSentences = 3, $variableNbSentences = true));
            $answer->setCreatedAt($generator->unique()->dateTime($max = 'now', $timezone = null));
            $answer->setVotes(0);
            $answer->setQuestion($generator->randomElement($questionsEntities));
            $answer->setUser($generator->randomElement($usersEntities));

            $manager->persist($answer);

            $answersEntities[] = $answer;
        }

        // Tags sur questions
        foreach ($questionsEntities as $question) {
            // On mélange les tags et on en récupère 1 à 3 au hasard
            shuffle($tagsEntities);
            $tagCount = mt_rand(1, 3);
            for ($i = 1; $i <= $tagCount; $i++) {
                $question->addTag($tagsEntities[$i]);
            }
        }
        // Flush
        $manager->flush();
    }
}
