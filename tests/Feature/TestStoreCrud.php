<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Modules\NsMultiStore\Models\Store;
use Tests\TestCase;

class TestStoreCrud extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        Sanctum::actingAs(
            Role::namespace( 'admin' )->users->first(),
            ['*']
        );

        $store          =   Store::first();
        ns()->store->setStore( $store );

        $files  =   Storage::disk( 'ns' )->allFiles( 'app/Crud' );

        foreach( $files as $file ) {
            
            $path       =   pathinfo( $file );
            $class      =   'App\Crud\\' . $path[ 'filename' ];
            $object     =   new $class;
            $columns    =   $object->getColumns();
            $entries    =   $object->getEntries();
            $form       =   $object->getForm();

            $this->assertIsArray( $columns, 'Crud Columns' );
            $this->assertIsArray( $form, 'Crud Form' );
            $this->assertArrayHasKey( 'data', $entries, 'Crud Response' );
        }
    }
}
