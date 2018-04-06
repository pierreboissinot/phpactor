<?php

namespace Phpactor\Application;

use Phpactor\ClassFileConverter\PathFinder;
use RuntimeException;

class Navigator
{
    /**
     * @var PathFinder
     */
    private $pathFinder;

    /**
     * @var ClassNew
     */
    private $classNew;

    /**
     * @var array
     */
    private $autoCreateConfig;

    public function __construct(
        PathFinder $pathFinder,
        ClassNew $classNew,
        array $autoCreateConfig
    ) {
        $this->pathFinder = $pathFinder;
        $this->classNew = $classNew;
        $this->autoCreateConfig = $autoCreateConfig;
    }

    public function destinationsFor(string $path)
    {
        return $this->pathFinder->destinationsFor($path);
    }

    public function canCreateNew(string $path, string $destinationName)
    {
        $destination = $this->destination($path, $destinationName);

        if (file_exists($destination)) {
            return false;
        }

        return isset($this->autoCreateConfig[$destinationName]);
    }

    public function createNew(string $path, string $destinationName)
    {
        $destination = $this->destination($path, $destinationName);
        $variant = $this->variant($destinationName);
        $this->classNew->generate($destination, $variant);
    }

    private function destination(string $path, string $destinationName)
    {
        $destinations = $this->destinationsFor($path);

        if (false === isset($destinations[$destinationName])) {
            throw new RuntimeException(sprintf(
                'Destination "%s" does not exist, known destinations: "%s"',
                $destinationName,
                implode('", "', array_keys($destinations))
            ));
        }

        return $destinations[$destinationName];
    }

    private function variant(string $destinationName)
    {
        if (!isset($this->autoCreateConfig[$destinationName])) {
            throw new RuntimeException(sprintf(
                'Destination "%s" has no new class variant set',
                $destinationName
            ));
        }

        return $this->autoCreateConfig[$destinationName];
    }
}