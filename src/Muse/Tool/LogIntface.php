<?php
namespace Muse\Tool;

interface LogIntface
{
    static function write($name,$data);
    static function read($name);
    static function delete($name);
}