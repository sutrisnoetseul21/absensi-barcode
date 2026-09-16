<?php

namespace App\Exceptions\Cbt;

class CbtServerException extends CbtException
{
    // Ditrigger saat terjadi kesalahan server internal di bridge atau database (HTTP 500)
}
