<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\Modules;
use App\Services\Setup;
use App\Services\Helper;
use App\Services\ModulesService;

class ModuleModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:model {namespace} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a module model';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
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
        $modules    =   app()->make( ModulesService::class );

        /**
         * Check if module is defined
         */
        if ( $module = $modules->get( $this->argument( 'namespace' ) ) ) {
            /**
             * Define Variables
             */
            $modelsPath     =   $module[ 'namespace' ] . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR;
            $name           =   ucwords( Str::camel( $this->argument( 'name' ) ) );
            $fileName       =   $modelsPath . $name;
            $namespace      =   $this->argument( 'namespace' );

            if ( ! Storage::disk( 'ns-modules' )->exists( 
                $fileName 
            ) ) {
                Storage::disk( 'ns-modules' )->put( $fileName . '.php', view( 'generate.modules.model', compact(
                    'modules', 'module', 'name', 'namespace'
                ) ) );
                return $this->info( 'The model has been created !' );
            }      
            return $this->error( 'The model already exists !' );
        }
        return $this->error( 'Unable to locate the module !' );
    }
}
