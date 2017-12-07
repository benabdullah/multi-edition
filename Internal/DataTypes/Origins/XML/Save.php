<?php namespace ZN\DataTypes\XML;

class Save
{
    //--------------------------------------------------------------------------------------------------------
    // Extension
    //--------------------------------------------------------------------------------------------------------
    //
    // @var string
    //
    //--------------------------------------------------------------------------------------------------------
    protected static $extension = '.xml';

    //--------------------------------------------------------------------------------------------------------
    // Save
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $file
    // @param string $data
    //
    //--------------------------------------------------------------------------------------------------------
    public static function do(String $file, String $data) : Bool
    {
        $file = suffix($file, self::$extension);

        return file_put_contents($file, $data);
    }
}