<?php

/**
 * @author   Laurent Jouanneau
 * @copyright 2022-2023 Laurent Jouanneau
 * @link     https://jelix.org
 * @license  MIT
 */

namespace Jelix\Authentication\Core\Workflow;


class AccessValidationStep extends AbstractStep
{
    protected $name = 'access_validation';

    protected $transition = 'validation';
}