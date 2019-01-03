<?php

declare(strict_types=1);

namespace Keboola\DeveloperPortal\Cli\Command;

use Keboola\DeveloperPortal\Cli\Command;
use Keboola\DeveloperPortal\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateAppPropertyCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('update-app-property')
            ->addArgument('vendor', InputArgument::REQUIRED, 'Vendor ID')
            ->addArgument('app', InputArgument::REQUIRED, 'App ID')
            ->addArgument('property', InputArgument::REQUIRED, 'Name of the property to update')
            ->addOption('value', null, InputOption::VALUE_REQUIRED, 'Value of the property')
            ->addOption(
                'value-from-file',
                null,
                InputOption::VALUE_REQUIRED,
                'Value of the property will be set to the contents of the provided file'
            )
            ->setDescription('Update arbitrary application property')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('value') && $input->getOption('value-from-file')) {
            throw new Exception('Use only one of --value or --value-from-file options.');
        }
        if (!$input->getOption('value') && !$input->getOption('value-from-file')) {
            throw new Exception('Provide property value using either --value or --value-from-file option.');
        }

        if ($input->getOption('value-from-file')) {
            $fileName = $input->getOption('value-from-file');
            $value = @file_get_contents($fileName);
            if ($value === false) {
                throw new Exception(sprintf('Cannot read file "%s".', $fileName));
            }
        } else {
            $value = $input->getOption('value');
        }
        $name = (string) $input->getArgument('property');
        $params = [$name => $value];

        $output->writeln(sprintf(
            'Updating application %s / %s:',
            $input->getArgument('vendor'),
            $input->getArgument('app')
        ));
        $output->writeln(\json_encode($params, JSON_PRETTY_PRINT));

        $client = $this->login();
        $client->updateApp($input->getArgument('vendor'), $input->getArgument('app'), $params);
    }
}