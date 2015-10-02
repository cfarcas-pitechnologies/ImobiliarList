<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class XMLParseCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('app:xml-parse')
            ->setDescription('Parse the xml page');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get("barriere.service.xml_parser")->parseXmlContent();
        $output->writeln('XML Parsing ended!');
    }

}