<?php

namespace Grixu\Synchronizer\Process\Contracts;

interface ProcessConfigInterface
{
    public function getLoaderClass(): string;
    public function getParserClass(): string;
    public function getCurrentJob(): string;
    public function getNextJob(): string;
    public function setCurrentJob(int $currentJob): void;
    public function getSyncHandler(): string;
    public function setSyncHandler(string $syncHandler): void;
    public function getErrorHandler(): string;
    public function setErrorHandler(string $errorHandler): void;
}
