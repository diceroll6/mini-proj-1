<?php

function test()
{
    echo 'lel';
}

function clean_utf8(string $string): string
{
    return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
}