<?php

namespace Tests\Feature;

use App\Services\ModulesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\WithAuthentication;

class UploadModuleTest extends TestCase
{
    use WithFaker, WithAuthentication;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_module_system()
    {
        $this->attemptAuthenticate();

        /**
         * Step 1: Generate the module
         * @var ModulesService
         */
        $moduleService  =   app()->make( ModulesService::class );

        $name           =   str_replace( '.', '', $this->faker->text(10) );
        $config         =   [
            'namespace'     =>  ucwords( Str::camel( $name ) ),
            'name'          =>  $name,
            'author'        =>  'NexoPOS',
            'description'   =>  'Generated from a test',
            'version'       =>  '1.0'
        ];

        $moduleService->generateModule( $config );

        /**
         * Step 2: Test if the module was created
         */
        $moduleService->load();
        $module     =   $moduleService->get( $config[ 'namespace' ] );

        $this->assertTrue( $module[ 'namespace' ] === $config[ 'namespace' ], 'The module as created' );

        /**
         * Step 3: We'll zip the module
         * and reupload that once we've finish the tests
         */
        $result     =   $moduleService->extract( $config[ 'namespace' ] );

        /**
         * Step 4 : We'll delete the generated module
         */
        $moduleService->delete( $config[ 'namespace' ] );
        $moduleService->load();
        $module     =   $moduleService->get( $config[ 'namespace' ] );

        $this->assertTrue( $module === null, 'The module wasn\'t deleted' );

        /**
         * Step 5: We'll reupload the module
         */
        $response   =   $this->withSession( $this->app[ 'session' ]->all() )
            ->json( 'POST', '/api/nexopos/v4/modules', [
                'module'    =>  UploadedFile::fake()->createWithContent( 'module.zip', file_get_contents( $result[ 'path' ] ) )
            ]);

        $response->assertRedirect( ns()->route( 'ns.dashboard.modules-list' ) );

        /**
         * Step 6 : We'll re-delete the uploaded module
         */
        $moduleService->delete( $config[ 'namespace' ] );
        $moduleService->load();
        $module     =   $moduleService->get( $config[ 'namespace' ] );

        $this->assertTrue( $module === null, 'The uploaded module wasn\'t deleted' );
    }
}
