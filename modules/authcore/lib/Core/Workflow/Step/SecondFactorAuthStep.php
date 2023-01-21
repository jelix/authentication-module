<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow\Step;

class SecondFactorAuthStep extends AbstractStep
{
    protected $name = 'second_factor';

    protected $transition = 'second_factor_success';

}