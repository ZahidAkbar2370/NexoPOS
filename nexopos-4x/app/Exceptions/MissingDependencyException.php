<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class MissingDependencyException extends Exception
{
    public function __construct( $message = null ) 
    {
        $this->message  =   $message ?: __('There is a missing dependency issue.' );
    }

    public function render( $request )
    {
        if ( ! $request->expectsJson() ) {
            return response()->view( 'pages.errors.missing-dependency', [
                'title'         =>  __( 'Missing Dependency' ),
                'message'       =>  $this->getMessage()
            ]);
        }

        return response()->json([ 
            'status'  =>  'failed',
            'message' => $this->getMessage()
        ], 401);
    }
}
