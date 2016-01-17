<?php
namespace Codeception\Test\Feature;

trait Dependencies
{
    protected $dependencies;
    protected $dependencyInput = [];

    abstract public function getTestResultObject();
    abstract public function getTestClass();
    abstract public function getName();
    abstract public function getSignature();
    abstract public function setDependencyInput($input);

}
