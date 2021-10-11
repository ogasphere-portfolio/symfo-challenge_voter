<?php

namespace App\Command;

use App\Repository\QuestionRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QuestionsDesactivateCommand extends Command
{
    protected static $defaultName = 'app:questions:desactivate';
    
    private $questionRepository;
    private $entityManager;
    
    public function __construct(QuestionRepository $questionRepository, EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->questionRepository = $questionRepository;
        $this->entityManager = $entityManager;
    }


    protected function configure()
    {
        $this
            ->setDescription('Desactivate questions')
           /*  ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description') */
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

       /*  if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        } */
        $nbDays = 7;
        // récupérer les questions de plus de nbDays jours
        $questionsToUpdate = $this->questionRepository->findByOlderUpdateDate($nbDays);

        // dd($questionsToUpdate);
        // mettre à jour le champ active de toutes les questions 
        foreach ($questionsToUpdate as $currentQuestion) {
            $currentQuestion->setActive(false);
        }

        $this->entityManager->flush();

        $io->success('Question is dasactivate !');

        return 0;
    }
}
