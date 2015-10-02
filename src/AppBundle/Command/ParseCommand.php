<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
                ->setName('app:parse')
                ->setDescription('Parse the html pages')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get("barriere.service.parser")->parseHtmlContent();
        $output->writeln('Parsing ended!');
    }

}