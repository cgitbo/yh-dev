<?php

class test extends IController
{
  function index()
  {
    $user = Team::getFixUserArr();

    var_dump($user);
  }
}
