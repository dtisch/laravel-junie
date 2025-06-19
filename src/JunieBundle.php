<?php

namespace Dcblogdev\Junie;

use Dcblogdev\Junie\Symfony\Command\InstallGuidelinesCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JunieBundle extends Bundle
{
    public function registerCommands(Application $application): void
    {
        parent::registerCommands($application);
        $application->add(new InstallGuidelinesCommand);
    }
}
