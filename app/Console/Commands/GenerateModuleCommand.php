<?php

namespace App\Console\Commands;

use App\Exceptions\NotAllowedException;
use Illuminate\Console\Command;
use App\Services\ModulesService;

class GenerateModuleCommand extends Command
{
    /**
     * module description
     * @var array
     */
    private $module     =   [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new NexoPOS 4.x module';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected ModulesService $moduleService
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ( ns()->installed() ) {
            $this->askInformations();
        } else {
            $this->info( 'NexoPOS 4.x is not yet installed.' );
        }
    }

    /**
     * ask for module information
     * @return void
     */
    public function askInformations()
    {
        $this->module[ 'namespace' ]        =   ucwords( $this->ask( 'Define the module namespace' ) );
        $this->module[ 'name' ]             =   $this->ask( 'Define the module name' );
        $this->module[ 'author' ]           =   $this->ask( 'Define the Author Name' );
        $this->module[ 'description' ]      =   $this->ask( 'Define a short description' );
        $this->module[ 'version' ]          =   '1.0';

        $table          =   [ 'Namespace', 'Name', 'Author', 'Description', 'Version' ];
        $this->table( $table, [ $this->module ] );

        if ( ! $this->confirm( 'Would you confirm theses informations \n' ) ) {
            $this->askInformations();
        }

        /**
         * let's try to create and if something
         * happens, we can still suggest the user to restart.
         */
        try {
            
            $response   =   $this->moduleService->generateModule( $this->module );
            $this->info( $response[ 'message' ] );

        } catch( NotAllowedException $exception ) {
            $this->error( 'A similar module has been found' );

            if (  $this->confirm( 'Would you like to restart ?' ) ) {
                $this->askInformations();
            }
        }
    }    
}
