<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\WithAuthentication;

class CrudTest extends TestCase
{
    use WithAuthentication;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCrudComponents()
    {
        $this->attemptAuthenticate();

        $files  =   Storage::disk( 'ns' )->allFiles( 'app/Crud' );

        foreach( $files as $file ) {
            
            $path       =   pathinfo( $file );
            $class      =   'App\Crud\\' . $path[ 'filename' ];
            $object     =   new $class;
            $entries    =   $object->getEntries();
            
            $apiRoutes  =   [
                [
                    'slug'  =>  'crud/{namespace}',
                    'verb'  =>  'get',
                ], [
                    'slug'  =>  'crud/{namespace}/columns',
                    'verb'  =>  'get',
                ], [
                    'slug'  =>  'crud/{namespace}/config/{id?}',
                    'verb'  =>  'get',
                ], [
                    'slug'  =>  'crud/{namespace}/form-config/{id?}',
                    'verb'  =>  'get',
                ], [
                    // 'slug'  =>  'crud/{namespace}/{id}',
                    // 'verb'  =>  'put',
                ], [
                    // 'slug'  =>  'crud/{namespace}',
                    // 'verb'  =>  'post',
                ], [
                    'slug'  =>  'crud/{namespace}/export',
                    'verb'  =>  'post',
                ], [
                    'slug'  =>  'crud/{namespace}/bulk-actions',
                    'verb'  =>  'post',
                ], [
                    'slug'  =>  'crud/{namespace}/can-access',
                    'verb'  =>  'post',
                ], [
                    'slug'  =>  'crud/{namespace}/{id}',
                    'verb'  =>  'delete',
                ], 
            ];

            foreach( $apiRoutes as $config ) {
                if ( isset( $config[ 'slug' ] ) ) {
                    $slug       =   str_replace( '{namespace}', $object->getNamespace(), $config[ 'slug' ] );
                    
                    /**
                     * In case we have an {id} on the slug
                     * we'll replace that with the existing id
                     */
                    if ( count( $entries[ 'data' ] ) > 0 ) {
                        $slug       =   str_replace( '{id}', $entries[ 'data' ][0]->{'$id'}, $slug );
                        $slug       =   str_replace( '{id?}', $entries[ 'data' ][0]->{'$id'}, $slug );
                    }

                    /**
                     * We shouldn't have any {id} or {id?} on
                     * the URL to prevent deleting CRUD with no records.
                     */
                    if ( preg_match( '/\{id\?\}/', $slug ) ) {
                        $response   =   $this
                            ->withSession( $this->app[ 'session' ]->all() )
                            ->json( strtoupper( $config[ 'verb' ] ), '/api/nexopos/v4/' . $slug, [
                                'entries'   =>  [ 1 ],
                                'action'    =>  'unknown'
                            ]);
                        
                        $response->assertOk();
                    }
                }
            }

            $this->assertArrayHasKey( 'data', $entries, 'Crud Response' );
        }
    }
}
