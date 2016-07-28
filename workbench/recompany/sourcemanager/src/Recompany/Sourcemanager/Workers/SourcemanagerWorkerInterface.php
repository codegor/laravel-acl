<?php
namespace Recompany\Sourcemanager\Workers;

interface SourcemanagerWorkerInterface {
    public function checkUri($uri, $method);
    public function getIds();
}
