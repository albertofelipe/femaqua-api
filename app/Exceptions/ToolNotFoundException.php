<?php

namespace App\Exceptions;

use Exception;

class ToolNotFoundException extends Exception
{
    protected $message = 'Tool not found';

    public function render() 
    {
        return response()->json([
            'message' => $this->message
        ], 404);
    }
}
