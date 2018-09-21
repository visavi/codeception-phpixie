<?php

namespace Codeception\Module;

use Codeception\Lib\Connector\Phpixie as PhpixieConnector;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\ActiveRecord;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\TestInterface;

class Phpixie extends Framework implements ActiveRecord, PartedModule
{
    public $app;

    public $orm;

    public $database;

    public $route;

    public $client;

    /**
     * @var array
     */
    public $config = [
        'cleanup' => true,
    ];

    public function _initialize()
    {
        $this->app     = new \Project\Framework();
        $this->builder = new \Project\App\AppBuilder($this->app->builder());

        $this->orm      = $this->app->builder()->components()->orm();
        $this->database = $this->app->builder()->components()->database();
        $this->route    = $this->app->builder()->components()->route();
    }

    /**
     * Before hook.
     *
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        if ($this->database && $this->config['cleanup']) {
            $this->database->get()->beginTransaction();
        }

        $this->client = new PhpixieConnector($this);
    }

    /**
     * After hook.
     *
     * @param TestInterface $test
     */
    public function _after(TestInterface $test)
    {
        if ($this->database && $this->config['cleanup']) {
            $this->database->get()->rollbackTransaction();
        }

        if ($this->database) {
            $this->database->get()->disconnect();
        }

        parent::_after($test);
    }

    /**
     * Opens web page using route name and parameters.
     *
     * ``` php
     * <?php
     * $I->amOnRoute('posts.create');
     * ?>
     * ```
     *
     * @param $routeName
     * @param array $params
     */
    public function amOnRoute($routeName, $params = [])
    {

    }

    /**
     * Inserts record into the database.
     *
     * <?php
     * $user = $I->haveRecord('user', ['name' => 'Davert']);
     * ?>
     *
     * @param string $entityName
     * @param array  $attributes
     * @return Entity
     * @part orm
     */
    public function haveRecord($entityName, $attributes = [])
    {
        $entity = $this->orm->createEntity($entityName, $attributes)->save();

        if (! $entity) {
            $this->fail("Couldn't insert record into entity '$entityName'");
        }

        return $entity;
    }

    /**
     * Checks that record exists in database.
     *
     * <?php
     * $I->seeRecord('user', ['name' => 'davert']);
     * ?>
     *
     * @param string $entityName
     * @param array $attributes
     * @part orm
     */
    public function seeRecord($entityName, $attributes = [])
    {
        if (! $this->findRecord($entityName, $attributes)) {
            $this->fail("Could not find matching record in entity '$entityName'");
        }

        $this->assertTrue(true);
    }


    public function dontSeeRecord($model, $attributes = [])
    {

    }

    public function grabRecord($model, $attributes = [])
    {

    }

    public function _parts()
    {
        return ['orm'];
    }

    /**
     * @param string $table
     * @param array $attributes
     * @return array
     */
    protected function findRecord($entityName, $attributes = [])
    {
        $query = $this->orm->query($entityName);

        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }


        return $query->findOne();
    }

    /**
     * @param $routeName
     * @return mixed
     */
    protected function getRouteByName($routeName)
    {

    }
}
