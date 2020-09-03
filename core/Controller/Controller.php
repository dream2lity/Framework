<?php


namespace core\Controller;


class Controller implements ControllerInterface
{
    function __construct()
    {
    }

    public function index()
    {
        return sprintf('Controller: [%s], Function: [%s]', __CLASS__, __FUNCTION__);
    }
}