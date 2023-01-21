<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Step;

class CreateAccountStep extends AbstractStep
{
    protected $name = 'create_account';

    protected $transition = 'account_created';

}