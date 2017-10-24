<?php
namespace Poirot\Storage\Redis;

class CountScriptCommand extends \Predis\Command\ScriptCommand
{
    public function getKeysCount()
    {
        return 0;
    }

    public function getScript()
    {
        return <<<LUA
return #redis.call('keys', ARGV[1])
LUA;
    }
}